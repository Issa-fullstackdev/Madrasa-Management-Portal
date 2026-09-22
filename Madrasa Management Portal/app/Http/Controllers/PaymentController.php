<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $students = Student::where('status', 'active')->orderBy('first_name')->get();
        $payments = Payment::where('month', $month)->get()->keyBy('student_id');

        return view('payments.index', [
            'students' => $students,
            'payments' => $payments,
            'month' => $month,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'month' => 'required|string',
            'amount' => 'required|integer|min:1',
            'paybill_reference' => 'nullable|string',
        ]);

        Payment::updateOrCreate(
            ['student_id' => $request->student_id, 'month' => $request->month],
            [
                'amount' => $request->amount,
                'paybill_reference' => $request->paybill_reference,
                'status' => 'paid',
                'paid_at' => now(),
            ]
        );

        return redirect('/payments?month=' . $request->month)->with('success', 'Payment recorded.');
    }
}
