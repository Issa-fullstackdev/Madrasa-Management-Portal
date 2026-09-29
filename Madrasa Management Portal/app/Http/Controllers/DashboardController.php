<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
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

        $paymentsThisMonth = null;
        if ($request->user()->role === 'principal') {
            $month = now()->format('Y-m');
            $paidCount = Payment::where('month', $month)->where('status', 'paid')->count();
            $paymentsThisMonth = [
                'paid' => $paidCount,
                'pending' => max($totalStudents - $paidCount, 0),
            ];
        }

        return view('dashboard.index', [
            'totalStudents' => $totalStudents,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'trend' => $trend,
            'recentActivity' => $recentActivity,
            'paymentsThisMonth' => $paymentsThisMonth,
        ]);
    }
}
