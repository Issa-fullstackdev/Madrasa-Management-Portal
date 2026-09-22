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
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'place_of_birth' => 'required|string',

            'father_first_name' => 'required|string',
            'father_middle_name' => 'nullable|string',
            'father_last_name' => 'required|string',
            'father_phone' => 'required|string',

            'mother_first_name' => 'required|string',
            'mother_middle_name' => 'nullable|string',
            'mother_last_name' => 'required|string',
            'mother_phone' => 'required|string',

            'address_building' => 'required|string',
            'address_road' => 'required|string',
            'address_county' => 'required|string',

            'emergency_contact_name' => 'required|string',
            'emergency_contact_phone' => 'required|string',

            'subject_id' => 'required|exists:subjects,id',

            'declaration' => 'required|accepted',
            'guardian_signed_name' => 'required|string',
        ]);

        $nextNumber = Student::count() + 1;
       $admissionNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        $student = Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'place_of_birth' => $request->place_of_birth,
            'father_first_name' => $request->father_first_name,
            'father_middle_name' => $request->father_middle_name,
            'father_last_name' => $request->father_last_name,
            'father_phone' => $request->father_phone,
            'mother_first_name' => $request->mother_first_name,
            'mother_middle_name' => $request->mother_middle_name,
            'mother_last_name' => $request->mother_last_name,
            'mother_phone' => $request->mother_phone,
            'address_building' => $request->address_building,
            'address_road' => $request->address_road,
            'address_county' => $request->address_county,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'guardian_signed_name' => $request->guardian_signed_name,
            'declaration_accepted_at' => now(),
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        $student->subjects()->syncWithoutDetaching($request->subject_id);

        return redirect('/students');
    }
}