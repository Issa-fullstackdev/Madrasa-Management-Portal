<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_principal_can_view_reports(): void
    {
        $this->travelTo('2026-09-20 12:00:00');
        $principal = User::factory()->create(['role' => 'principal']);
        $subject = Subject::create(['name' => 'Quran']);
        $firstStudent = $this->createStudent('001', 'Amina', 'Noor', '2026-09-03');
        $secondStudent = $this->createStudent('002', 'Idris', 'Ali', '2026-09-04');
        $inactiveStudent = $this->createStudent('003', 'Maryam', 'Amin', '2026-09-05', 'inactive');

        Attendance::create([
            'student_id' => $firstStudent->id,
            'subject_id' => $subject->id,
            'date' => '2026-09-10',
            'status' => 'present',
        ]);
        Attendance::create([
            'student_id' => $secondStudent->id,
            'subject_id' => $subject->id,
            'date' => '2026-09-10',
            'status' => 'absent',
        ]);
        Payment::create([
            'student_id' => $firstStudent->id,
            'amount' => 2500,
            'method' => 'mpesa',
            'mpesa_code' => 'SJK4H7XQ2P',
            'paid_at' => '2026-09-11 10:00:00',
        ]);
        Payment::create([
            'student_id' => $inactiveStudent->id,
            'amount' => 500,
            'method' => 'cash',
            'paid_at' => '2026-09-12 10:00:00',
        ]);

        $this->actingAs($principal)
            ->get('/reports?from=2026-09-01&to=2026-09-30&month=2026-09')
            ->assertSee('Attendance report')
            ->assertSee('Active students')
            ->assertSee('New enrollments in selected dates')
            ->assertSee('Payments received')
            ->assertSee('Students in arrears (today)')
            ->assertSee('Idris Ali')
            ->assertSee('17,500')
            ->assertSee('3,000')
            ->assertSee('Amina Noor')
            ->assertSee('Maryam Amin')
            ->assertSee('Quran')
            ->assertSee('<div class="kpi-value">2</div>', false)
            ->assertSee('<div class="kpi-value">3</div>', false)
            ->assertSee('<div class="kpi-value">2</div>', false);
    }

    public function test_teacher_cannot_view_principal_reports(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get('/reports')
            ->assertForbidden();
    }

    private function createStudent(string $admissionNumber, string $firstName, string $lastName, string $enrolledAt, string $status = 'active'): Student
    {
        return Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'enrolled_at' => $enrolledAt,
            'status' => $status,
        ]);
    }
}
