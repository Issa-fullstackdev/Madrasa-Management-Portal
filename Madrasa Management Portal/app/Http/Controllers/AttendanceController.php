<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::all();
        $selectedSubjectId = $request->query('subject_id', $subjects->first()->id ?? null);

        $today = now()->toDateString();

        $checkedIn = Attendance::with('student')
            ->where('subject_id', $selectedSubjectId)
            ->where('date', $today)
            ->latest('id')
            ->get();

        return view('attendance.index', [
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'checkedIn' => $checkedIn,
            'today' => $today,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'admission_number' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $student = Student::where('admission_number', $request->admission_number)
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return back()->withErrors(['admission_number' => 'No active student found with that admission number.'])
                ->with('subject_id', $request->subject_id);
        }

        $isEnrolled = $student->subjects()->where('subjects.id', $request->subject_id)->exists();
        if (!$isEnrolled) {
            return back()->withErrors(['admission_number' => $student->first_name . ' is not enrolled in this class.'])
                ->with('subject_id', $request->subject_id);
        }

        $today = now()->toDateString();

        $alreadyMarked = Attendance::where('student_id', $student->id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $today)
            ->exists();

        if ($alreadyMarked) {
            return back()->withErrors(['admission_number' => $student->first_name . ' is already checked in today.'])
                ->with('subject_id', $request->subject_id);
        }

        Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $request->subject_id,
            'date' => $today,
            'status' => 'present',
        ]);

        return redirect('/attendance?subject_id=' . $request->subject_id)
            ->with('success', $student->first_name . ' ' . $student->last_name . ' checked in.');
    }
}