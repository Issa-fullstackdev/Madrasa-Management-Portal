<?php

namespace App\Http\Controllers;

use App\Models\FeeCharge;
use App\Models\NonBillableMonth;
use App\Models\Payment;
use App\Models\Student;
use App\Services\FeeLedger;
use App\Services\StatementReconciler;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private FeeLedger $ledger) {}

    public function index(Request $request)
    {
        $filter = in_array($request->query('filter'), ['arrears', 'credit'], true) ? $request->query('filter') : 'all';

        $this->ledger->syncCharges();
        $accounts = $this->ledger->accounts();

        return view('payments.index', [
            'accounts' => $filter === 'all' ? $accounts : $accounts->filter(fn ($a) => $a->standing() === $filter),
            'allAccounts' => $accounts,
            'filter' => $filter,
            'recentPayments' => Payment::with('student', 'recorder')->latest('paid_at')->latest('id')->limit(15)->get(),
            'nonBillableMonths' => NonBillableMonth::orderByDesc('month')->get(),
        ]);
    }

    public function show(Student $student)
    {
        $this->ledger->syncCharges();

        return view('payments.show', [
            'account' => $this->ledger->account($student),
            'payments' => $student->payments()->with('recorder')->latest('paid_at')->latest('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'mpesa_code' => $request->filled('mpesa_code')
                ? strtoupper(preg_replace('/\s+/', '', $request->mpesa_code))
                : null,
        ]);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|integer|min:1',
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'mpesa_code' => [
                'nullable', 'required_if:method,mpesa', 'regex:/^[A-Z][A-Z0-9]{9}$/',
                Rule::unique('payments', 'mpesa_code'),
            ],
            'paid_at' => 'required|date|before_or_equal:today',
        ], [
            'mpesa_code.required_if' => 'Enter the M-Pesa transaction code.',
            'mpesa_code.regex' => 'An M-Pesa code is 10 letters/digits starting with a letter, e.g. SJK4H7XQ2P.',
            'mpesa_code.unique' => 'This M-Pesa code has already been recorded. Each transaction can only be used once.',
        ]);

        $payment = Payment::create([
            'student_id' => $data['student_id'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'mpesa_code' => $data['method'] === 'mpesa' ? $data['mpesa_code'] : null,
            'paid_at' => $data['paid_at'],
            'verification_status' => $data['method'] === 'mpesa' ? 'unverified' : null,
            'recorded_by' => $request->user()->id,
        ]);

        $account = $this->ledger->account($payment->student);

        return back()->with('success', sprintf(
            'Payment of KES %s recorded for %s %s. %s',
            number_format($payment->amount),
            $payment->student->first_name,
            $payment->student->last_name,
            $this->describeBalance($account->balance()),
        ));
    }

    public function destroy(Payment $payment)
    {
        if ($payment->isVerified()) {
            return back()->withErrors('Verified payments match the bank statement and cannot be deleted.');
        }

        $payment->delete();

        return back()->with('success', 'Payment deleted.');
    }

    public function updateFee(Request $request, Student $student)
    {
        $data = $request->validate(['monthly_fee' => 'required|integer|min:0|max:1000000']);

        $student->update($data);

        // The new fee applies from the current month onwards; past charges keep their amount.
        FeeCharge::where('student_id', $student->id)
            ->where('month', '>=', now()->format('Y-m'))
            ->update(['amount' => $data['monthly_fee']]);

        return back()->with('success', 'Monthly fee updated to KES ' . number_format($data['monthly_fee']) . ' from ' . now()->format('F Y') . '.');
    }

    public function reconcile(Request $request, StatementReconciler $reconciler)
    {
        $request->validate([
            'statement' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'statement.mimes' => 'Upload the statement as a CSV file (in Excel: File → Save As → CSV).',
        ]);

        return view('payments.reconcile', [
            'result' => $reconciler->reconcile($request->file('statement')->getRealPath()),
        ]);
    }

    public function storeNonBillableMonth(Request $request)
    {
        $data = $request->validate([
            'month' => 'required|date_format:Y-m|unique:non_billable_months,month',
            'reason' => 'nullable|string|max:100',
        ], ['month.unique' => 'That month is already marked as non-billable.']);

        NonBillableMonth::create($data);

        // Any charges already raised for that month are removed; payments simply move to other months.
        FeeCharge::where('month', $data['month'])->delete();

        return back()->with('success', 'No fees will be charged for ' . \Carbon\Carbon::parse($data['month'] . '-01')->format('F Y') . '.');
    }

    public function destroyNonBillableMonth(NonBillableMonth $nonBillableMonth)
    {
        $nonBillableMonth->delete();

        return back()->with('success', 'Fees will be charged for ' . \Carbon\Carbon::parse($nonBillableMonth->month . '-01')->format('F Y') . ' again.');
    }

    private function describeBalance(int $balance): string
    {
        return match (true) {
            $balance > 0 => 'Outstanding balance: KES ' . number_format($balance) . '.',
            $balance < 0 => 'Account in credit: KES ' . number_format(-$balance) . '.',
            default => 'Account is fully paid up.',
        };
    }
}
