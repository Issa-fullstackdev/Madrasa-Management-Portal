<?php

namespace Tests\Feature;

use App\Models\FeeCharge;
use App\Models\NonBillableMonth;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\FeeLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    private User $principal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-05 09:00:00');
        $this->principal = User::factory()->create(['role' => 'principal']);
    }

    public function test_unpaid_months_accrue_as_arrears(): void
    {
        $student = $this->createStudent('2026-08-15');

        $account = $this->account($student);

        $this->assertSame(['2026-08', '2026-09', '2026-10'], array_column($account->months, 'month'));
        $this->assertSame(30000, $account->arrears());
        $this->assertSame(3, $account->arrearsMonths);
        $this->assertNull($account->paidUntil);
    }

    public function test_partial_payment_clears_oldest_month_first(): void
    {
        $student = $this->createStudent('2026-08-01');
        $this->pay($student, 15000);

        $account = $this->account($student);

        $this->assertSame(['paid', 'partial', 'unpaid'], array_column($account->months, 'status'));
        $this->assertSame(15000, $account->arrears());
        $this->assertSame(2, $account->arrearsMonths);
        $this->assertSame('2026-08', $account->paidUntil);
    }

    public function test_prepayment_is_held_as_credit_and_covers_future_months(): void
    {
        $student = $this->createStudent('2026-10-01');
        $this->pay($student, 40000);

        $account = $this->account($student);

        $this->assertSame(30000, $account->credit());
        $this->assertSame('credit', $account->standing());
        $this->assertSame('2027-01', $account->paidUntil);

        // When November arrives, the credit pays for it automatically.
        $this->travelTo('2026-11-02 09:00:00');
        $account = $this->account($student->fresh());
        $this->assertSame(['paid', 'paid'], array_column($account->months, 'status'));
        $this->assertSame(20000, $account->credit());
    }

    public function test_non_billable_months_are_not_charged_and_are_skipped_when_projecting_credit(): void
    {
        $student = $this->createStudent('2026-09-01');
        $this->pay($student, 30000);

        $this->actingAs($this->principal)
            ->post('/payments/non-billable-months', ['month' => '2026-09', 'reason' => 'Holiday'])
            ->assertSessionHasNoErrors();
        NonBillableMonth::create(['month' => '2026-11']);

        $account = $this->account($student);

        $this->assertSame(['2026-10'], array_column($account->months, 'month'));
        $this->assertSame(20000, $account->credit());
        $this->assertSame('2027-01', $account->paidUntil); // Oct paid, Nov skipped, Dec + Jan from credit
    }

    public function test_fee_change_applies_from_current_month_only(): void
    {
        $student = $this->createStudent('2026-09-01');
        app(FeeLedger::class)->syncCharges();

        $this->actingAs($this->principal)
            ->patch("/payments/students/{$student->id}/fee", ['monthly_fee' => 6000])
            ->assertSessionHasNoErrors();

        $this->assertSame(10000, FeeCharge::where('month', '2026-09')->value('amount'));
        $this->assertSame(6000, FeeCharge::where('month', '2026-10')->value('amount'));
        $this->assertSame(6000, $student->fresh()->monthly_fee);
    }

    public function test_principal_records_payment_with_normalised_code(): void
    {
        $student = $this->createStudent('2026-10-01');

        $this->actingAs($this->principal)
            ->post('/payments', $this->paymentData($student, ['mpesa_code' => ' sjk4h7xq2p ']))
            ->assertSessionHasNoErrors();

        $payment = Payment::sole();
        $this->assertSame('SJK4H7XQ2P', $payment->mpesa_code);
        $this->assertSame('unverified', $payment->verification_status);
        $this->assertSame($this->principal->id, $payment->recorded_by);
    }

    public function test_mpesa_code_cannot_be_reused(): void
    {
        $first = $this->createStudent('2026-10-01');
        $second = $this->createStudent('2026-10-01', '002');

        $this->actingAs($this->principal)->post('/payments', $this->paymentData($first));
        $this->actingAs($this->principal)
            ->post('/payments', $this->paymentData($second, ['mpesa_code' => 'sjk4h7xq2p']))
            ->assertSessionHasErrors('mpesa_code');

        $this->assertSame(1, Payment::count());
    }

    public function test_invalid_or_missing_mpesa_codes_are_rejected(): void
    {
        $student = $this->createStudent('2026-10-01');

        foreach (['', '12345', 'SJK4H7XQ2P9', '1JK4H7XQ2P', 'SJK4H7-Q2P'] as $code) {
            $this->actingAs($this->principal)
                ->post('/payments', $this->paymentData($student, ['mpesa_code' => $code]))
                ->assertSessionHasErrors('mpesa_code');
        }

        $this->actingAs($this->principal)
            ->post('/payments', $this->paymentData($student, ['method' => 'cash', 'mpesa_code' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull(Payment::sole()->verification_status);
    }

    public function test_future_payment_dates_are_rejected(): void
    {
        $student = $this->createStudent('2026-10-01');

        $this->actingAs($this->principal)
            ->post('/payments', $this->paymentData($student, ['paid_at' => '2026-10-06']))
            ->assertSessionHasErrors('paid_at');
    }

    public function test_statement_upload_verifies_flags_mismatches_and_lists_unrecorded_codes(): void
    {
        $student = $this->createStudent('2026-10-01');
        $matching = $this->pay($student, 10000, 'SJK4H7XQ2P');
        $wrongAmount = $this->pay($student, 5000, 'SJK4H7XQ3Q');
        $missing = $this->pay($student, 2000, 'SJK4H7XQ4R');

        $csv = implode("\n", [
            'Date,Narration,Debit,Credit,Balance',
            '01/10/2026,MPESA C2B SJK4H7XQ2P 254712345678 AMINA,,"10,000.00","110,000.00"',
            '02/10/2026,MPESA C2B SJK4H7XQ3Q 254712345678 AMINA,,500.00,"110,500.00"',
            '03/10/2026,MPESA C2B TAB1C2D3E4 254700000000 UNKNOWN,,"10,000.00","120,500.00"',
            '04/10/2026,TRANSFERRED BANK CHARGES,30.00,,"120,470.00"',
        ]);

        $this->actingAs($this->principal)
            ->post('/payments/reconcile', ['statement' => UploadedFile::fake()->createWithContent('statement.csv', $csv)])
            ->assertOk()
            ->assertSee('TAB1C2D3E4')
            ->assertDontSee('TRANSFERRE');

        $this->assertSame('verified', $matching->fresh()->verification_status);
        $this->assertNotNull($matching->fresh()->verified_at);
        $this->assertSame('mismatch', $wrongAmount->fresh()->verification_status);
        $this->assertSame('unverified', $missing->fresh()->verification_status);
    }

    public function test_verified_payments_cannot_be_deleted(): void
    {
        $student = $this->createStudent('2026-10-01');
        $verified = $this->pay($student, 10000, 'SJK4H7XQ2P');
        $verified->update(['verification_status' => 'verified']);
        $unverified = $this->pay($student, 10000, 'SJK4H7XQ3Q');

        $this->actingAs($this->principal)->delete("/payments/{$verified->id}")->assertSessionHasErrors();
        $this->actingAs($this->principal)->delete("/payments/{$unverified->id}")->assertSessionHasNoErrors();

        $this->assertModelExists($verified);
        $this->assertModelMissing($unverified);
    }

    public function test_payments_page_and_statement_render(): void
    {
        $owing = $this->createStudent('2026-09-01', '001', 'Amina');
        $prepaid = $this->createStudent('2026-10-01', '002', 'Idris');
        $this->pay($prepaid, 30000, 'SJK4H7XQ2P');

        $this->actingAs($this->principal)->get('/payments')
            ->assertOk()
            ->assertSee('2 months owing')
            ->assertSee('Owes 20,000')
            ->assertSee('Credit 20,000')
            ->assertSee('Dec 2026');

        $this->actingAs($this->principal)->get('/payments?filter=arrears')
            ->assertSee('Amina')
            ->assertDontSee('Credit 20,000');

        $this->actingAs($this->principal)->get("/payments/students/{$prepaid->id}")
            ->assertOk()
            ->assertSee('SJK4H7XQ2P')
            ->assertSee('December 2026');

        $this->actingAs($this->principal)->get('/')
            ->assertOk()
            ->assertSee('<div class="kpi-value">1 / 1</div>', false)
            ->assertSee('Fees up to date / In arrears');
    }

    public function test_teacher_cannot_access_payments(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = $this->createStudent('2026-10-01');

        $this->actingAs($teacher)->get('/payments')->assertForbidden();
        $this->actingAs($teacher)->post('/payments', $this->paymentData($student))->assertForbidden();
        $this->actingAs($teacher)->get("/payments/students/{$student->id}")->assertForbidden();
    }

    private function account(Student $student)
    {
        $ledger = app(FeeLedger::class);
        $ledger->syncCharges();

        return $ledger->account($student->fresh());
    }

    private function pay(Student $student, int $amount, ?string $code = null): Payment
    {
        return Payment::create([
            'student_id' => $student->id,
            'amount' => $amount,
            'method' => 'mpesa',
            'mpesa_code' => $code,
            'verification_status' => 'unverified',
            'paid_at' => now(),
        ]);
    }

    private function paymentData(Student $student, array $overrides = []): array
    {
        return array_merge([
            'student_id' => $student->id,
            'amount' => 10000,
            'method' => 'mpesa',
            'mpesa_code' => 'SJK4H7XQ2P',
            'paid_at' => '2026-10-05',
        ], $overrides);
    }

    private function createStudent(string $enrolledAt, string $admissionNumber = '001', string $firstName = 'Amina'): Student
    {
        return Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Noor',
            'enrolled_at' => $enrolledAt,
            'status' => 'active',
        ]);
    }
}
