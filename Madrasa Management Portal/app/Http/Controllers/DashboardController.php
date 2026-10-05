<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\FeeLedger;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, FeeLedger $ledger)
    {
        $totalStudents = Student::where('status', 'active')->count();

        $today = now()->toDateString();
        $presentToday = Attendance::where('date', $today)->distinct('student_id')->count('student_id');
        $absentToday = max($totalStudents - $presentToday, 0);

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Attendance::where('date', $date->toDateString())->distinct('student_id')->count('student_id');
            $trend[] = ['label' => $date->format('D'), 'count' => $count];
        }

        $recentActivity = Attendance::with('student', 'subject')->latest('id')->take(5)->get();

        $studentProgress = Student::where('status', 'active')
            ->with('subjects')
            ->orderBy('first_name')
            ->get();

        $feeStanding = null;
        if ($request->user()->role === 'principal') {
            $ledger->syncCharges();
            $inArrears = $ledger->accounts()->filter(fn ($account) => $account->standing() === 'arrears')->count();
            $feeStanding = [
                'inArrears' => $inArrears,
                'upToDate' => $totalStudents - $inArrears,
            ];
        }

        return view('dashboard.index', [
            'totalStudents' => $totalStudents,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'trend' => $trend,
            'recentActivity' => $recentActivity,
            'feeStanding' => $feeStanding,
            'studentProgress' => $studentProgress,
        ]);
    }
}
