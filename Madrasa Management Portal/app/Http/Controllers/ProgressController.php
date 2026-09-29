<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'progress' => 'array',
            'progress.*' => 'nullable|integer|min:0|max:30',
        ]);

        foreach ($request->input('progress', []) as $studentId => $juz) {
            $student = Student::find($studentId);

            if ($student && $student->subjects()->where('subjects.id', $request->subject_id)->exists()) {
                $student->subjects()->updateExistingPivot($request->subject_id, ['juz_completed' => $juz ?? 0]);
            }
        }

        return redirect('/attendance?subject_id=' . $request->subject_id)
            ->with('success', 'Progress updated.');
    }
}
