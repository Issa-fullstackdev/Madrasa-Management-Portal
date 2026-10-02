<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_page_shows_capacity_and_enrolled_students(): void
    {
        $principal = User::factory()->create(['role' => 'principal']);
        $subject = Subject::create(['name' => 'Quran', 'capacity' => 18]);
        $firstStudent = $this->createStudent('001', 'Amina', 'Noor');
        $secondStudent = $this->createStudent('002', 'Idris', 'Ali');
        $subject->students()->attach([$firstStudent->id, $secondStudent->id]);

        $this->actingAs($principal)
            ->get('/subjects')
            ->assertSee('Quran')
            ->assertSee('18')
            ->assertSee('Amina Noor')
            ->assertSee('Idris Ali')
            ->assertSee('<span class="subject-enrollment-count">2 / 18</span>', false);
    }

    public function test_principal_can_update_subject_capacity(): void
    {
        $principal = User::factory()->create(['role' => 'principal']);
        $subject = Subject::create(['name' => 'Hifdh', 'capacity' => 25]);

        $this->actingAs($principal)
            ->patch("/subjects/{$subject->id}/capacity", ['capacity' => 32])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'capacity' => 32,
        ]);
    }

    public function test_teacher_cannot_update_subject_capacity(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $subject = Subject::create(['name' => 'Fiqh', 'capacity' => 20]);

        $this->actingAs($teacher)
            ->patch("/subjects/{$subject->id}/capacity", ['capacity' => 30])
            ->assertForbidden();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'capacity' => 20,
        ]);
    }

    public function test_teacher_only_sees_rosters_for_assigned_subjects(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $assignedSubject = Subject::create(['name' => 'Quran', 'capacity' => 18]);
        $unassignedSubject = Subject::create(['name' => 'Hifdh', 'capacity' => 20]);
        $assignedStudent = $this->createStudent('001', 'Amina', 'Noor');
        $unassignedStudent = $this->createStudent('002', 'Idris', 'Ali');

        $teacher->subjects()->attach($assignedSubject->id);
        $assignedSubject->students()->attach($assignedStudent->id);
        $unassignedSubject->students()->attach($unassignedStudent->id);

        $this->actingAs($teacher)
            ->get('/subjects')
            ->assertSee('Quran')
            ->assertSee('Amina Noor')
            ->assertDontSee('Hifdh')
            ->assertDontSee('Idris Ali');
    }

    public function test_capacity_must_be_within_the_supported_range(): void
    {
        $principal = User::factory()->create(['role' => 'principal']);
        $subject = Subject::create(['name' => 'Fiqh', 'capacity' => 20]);

        foreach ([0, 256] as $capacity) {
            $this->actingAs($principal)
                ->patch("/subjects/{$subject->id}/capacity", ['capacity' => $capacity])
                ->assertSessionHasErrors('capacity');
        }

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'capacity' => 20,
        ]);
    }

    private function createStudent(string $admissionNumber, string $firstName, string $lastName): Student
    {
        return Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'enrolled_at' => '2026-09-01',
            'status' => 'active',
        ]);
    }
}
