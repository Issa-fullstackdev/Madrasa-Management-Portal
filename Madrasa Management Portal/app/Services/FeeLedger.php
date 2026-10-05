<?php

namespace App\Services;

use App\Models\FeeCharge;
use App\Models\NonBillableMonth;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Fees work like a bank statement: every billable month adds a charge, every payment
 * reduces the balance. Payments are applied to the oldest unpaid month first, so a
 * positive balance is arrears and a negative balance is a prepayment (credit).
 */
class FeeLedger
{
    /**
     * Create any missing monthly charges for active students, from the month they
     * enrolled up to the current month, skipping non-billable months.
     */
    public function syncCharges(): void
    {
        $currentMonth = now()->format('Y-m');
        $nonBillable = $this->nonBillableMonths();

        $students = Student::where('status', 'active')->get(['id', 'enrolled_at', 'monthly_fee']);
        $existing = FeeCharge::whereIn('student_id', $students->pluck('id'))
            ->get(['student_id', 'month'])
            ->groupBy('student_id')
            ->map(fn ($charges) => $charges->pluck('month')->flip());

        $rows = [];
        foreach ($students as $student) {
            $have = $existing->get($student->id, collect());
            foreach ($this->monthsBetween($student->enrolled_at->format('Y-m'), $currentMonth) as $month) {
                if (! $nonBillable->has($month) && ! $have->has($month)) {
                    $rows[] = [
                        'student_id' => $student->id,
                        'month' => $month,
                        'amount' => $student->monthly_fee,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            FeeCharge::insertOrIgnore($chunk);
        }
    }

    /**
     * Allocate a student's payments against their charges, oldest month first.
     * Uses eager-loaded feeCharges / payments_sum_amount when present (see accounts()).
     */
    public function account(Student $student, ?Collection $nonBillable = null): StudentAccount
    {
        $nonBillable ??= $this->nonBillableMonths();
        $charges = $student->relationLoaded('feeCharges') ? $student->feeCharges : $student->feeCharges()->get();
        $totalPaid = (int) (array_key_exists('payments_sum_amount', $student->getAttributes())
            ? $student->payments_sum_amount
            : $student->payments()->sum('amount'));

        $remaining = $totalPaid;
        $months = [];
        $paidUntil = null;
        foreach ($charges->sortBy('month') as $charge) {
            $applied = min($remaining, $charge->amount);
            $remaining -= $applied;
            $status = $applied >= $charge->amount ? 'paid' : ($applied > 0 ? 'partial' : 'unpaid');
            if ($status === 'paid') {
                $paidUntil = $charge->month;
            }
            $months[] = [
                'month' => $charge->month,
                'charged' => $charge->amount,
                'applied' => $applied,
                'outstanding' => $charge->amount - $applied,
                'status' => $status,
            ];
        }

        $totalCharged = (int) $charges->sum('amount');
        $credit = max($totalPaid - $totalCharged, 0);
        $arrearsMonths = collect($months)->where('status', '!=', 'paid')->count();

        // Project any credit forward over future billable months at the current fee.
        if ($arrearsMonths === 0 && $credit > 0 && $student->monthly_fee > 0) {
            $cursor = $paidUntil ?? CarbonImmutable::parse($student->enrolled_at)->subMonthNoOverflow()->format('Y-m');
            $covered = intdiv($credit, $student->monthly_fee);
            for ($i = 0; $i < $covered; $i++) {
                $cursor = $this->nextBillableMonth($cursor, $nonBillable);
            }
            if ($covered > 0) {
                $paidUntil = $cursor;
            }
        }

        return new StudentAccount(
            student: $student,
            totalCharged: $totalCharged,
            totalPaid: $totalPaid,
            months: $months,
            arrearsMonths: $arrearsMonths,
            paidUntil: $paidUntil,
        );
    }

    /**
     * @return Collection<int, StudentAccount>
     */
    public function accounts(Collection|null $students = null): Collection
    {
        $students ??= Student::where('status', 'active')->orderBy('first_name')->get();
        $students->load('feeCharges')->loadSum('payments', 'amount');
        $nonBillable = $this->nonBillableMonths();

        return $students->map(fn (Student $student) => $this->account($student, $nonBillable));
    }

    /**
     * @return Collection<string, int> months keyed for fast lookup
     */
    public function nonBillableMonths(): Collection
    {
        return NonBillableMonth::pluck('month')->flip();
    }

    private function nextBillableMonth(string $month, Collection $nonBillable): string
    {
        // Guard against a pathological "everything excluded" setup.
        for ($i = 0; $i < 120; $i++) {
            $month = CarbonImmutable::createFromFormat('Y-m-d', $month . '-01')->addMonthNoOverflow()->format('Y-m');
            if (! $nonBillable->has($month)) {
                return $month;
            }
        }

        return $month;
    }

    /**
     * @return list<string>
     */
    private function monthsBetween(string $from, string $to): array
    {
        $months = [];
        $cursor = CarbonImmutable::createFromFormat('Y-m-d', $from . '-01');
        $end = CarbonImmutable::createFromFormat('Y-m-d', $to . '-01');
        while ($cursor->lessThanOrEqualTo($end)) {
            $months[] = $cursor->format('Y-m');
            $cursor = $cursor->addMonthNoOverflow();
        }

        return $months;
    }
}
