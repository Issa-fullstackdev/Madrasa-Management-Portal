<?php

namespace App\Http\Controllers;

use App\Models\Subject;

class SubjectController extends Controller
{
    public function index()
    {
        return view('subjects.index', ['subjects' => Subject::all()]);
    }
}