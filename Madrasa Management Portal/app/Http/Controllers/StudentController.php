<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return view('students.index', [
            'students' => Student::orderByDesc('id')->get(),
            'subjects' => Subject::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'guardian_name' => 'required|string',
            'guardian_phone' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $nextNumber = Student::count() + 1;
       $admissionNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $student = Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $student->subjects()->attach($request->subject_id);

        return redirect('/students');
    }
}