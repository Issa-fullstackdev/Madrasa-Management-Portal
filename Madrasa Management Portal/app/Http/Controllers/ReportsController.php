<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $request->merge([
            'from' => $request->query('from', now()->startOfMonth()->toDateString()),
            'to' => $request->query('to', now()->toDateString()),
            'month' => $request->query('month', now()->format('Y-m')),
        ]);

        $filters = $request->validate([
            'from' => ['required', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $attendanceSummary = Attendance::query()
            ->select('subject_id', 'date')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
            ->with('subject:id,name')
            ->whereBetween('date', [$filters['from'], $filters['to']])
            ->groupBy('subject_id', 'date')
            ->orderBy('date')
            ->get();

        $enrollmentSummary = Student::query()
            ->select('enrolled_at')
            ->selectRaw('COUNT(*) as student_count')
            ->whereBetween('enrolled_at', [$filters['from'], $filters['to']])
            ->groupBy('enrolled_at')
            ->orderBy('enrolled_at')
            ->get();

        $activeStudentCount = Student::where('status', 'active')->count();

        $paidPayments = Payment::query()
            ->where('month', $filters['month'])
            ->where('status', 'paid');

        $paidPaymentCount = (clone $paidPayments)->count();
        $collectedAmount = (clone $paidPayments)->sum('amount');
        $pendingPaymentCount = Student::query()
            ->where('status', 'active')
            ->whereNotIn('id', Payment::query()
                ->select('student_id')
                ->where('month', $filters['month'])
                ->where('status', 'paid'))
            ->count();

        $recentPayments = Payment::query()
            ->with('student:id,admission_number,first_name,last_name')
            ->where('month', $filters['month'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'filters' => $filters,
            'attendanceSummary' => $attendanceSummary,
            'enrollmentSummary' => $enrollmentSummary,
            'newEnrollmentCount' => $enrollmentSummary->sum('student_count'),
            'activeStudentCount' => $activeStudentCount,
            'paidPaymentCount' => $paidPaymentCount,
            'pendingPaymentCount' => $pendingPaymentCount,
            'collectedAmount' => $collectedAmount,
            'recentPayments' => $recentPayments,
        ]);
    }
}
