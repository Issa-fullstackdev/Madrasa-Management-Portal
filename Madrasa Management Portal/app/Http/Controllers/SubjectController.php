<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjectsQuery = Subject::query();

        if ($request->user()->role === 'teacher') {
            $subjectsQuery->whereHas('teachers', fn (Builder $query) => $query->whereKey($request->user()->getAuthIdentifier()));
        }

        $subjects = $subjectsQuery
            ->with(['students' => fn (BelongsToMany $query) => $query
                ->select(['students.id', 'admission_number', 'first_name', 'middle_name', 'last_name', 'status'])
                ->orderBy('first_name')
                ->orderBy('last_name')])
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return view('subjects.index', ['subjects' => $subjects]);
    }

    public function updateCapacity(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'capacity' => ['required', 'integer', 'min:1', 'max:255'],
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject capacity updated.');
    }
}
