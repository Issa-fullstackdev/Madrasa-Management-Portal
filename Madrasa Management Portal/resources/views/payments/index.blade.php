@extends('layout')
@section('content')
    @php
        $monthLabel = fn ($month) => $month ? \Carbon\Carbon::parse($month . '-01')->format('M Y') : '-';
        $inArrears = $allAccounts->filter(fn ($a) => $a->standing() === 'arrears');
        $inCredit = $allAccounts->filter(fn ($a) => $a->standing() === 'credit');
    @endphp

    <h3 class="mb-3">Payments</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ $inArrears->count() }}</div>
            <div class="kpi-label">Students in arrears</div>
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">KES {{ number_format($inArrears->sum(fn ($a) => $a->arrears())) }}</div>
            <div class="kpi-label">Total outstanding</div>
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ $inCredit->count() }}</div>
            <div class="kpi-label">Students prepaid</div>
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">KES {{ number_format($inCredit->sum(fn ($a) => $a->credit())) }}</div>
            <div class="kpi-label">Total held as prepayment</div>
        </div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Record Payment</h5>
                    <p class="text-muted small">Any amount is accepted. It clears the oldest unpaid month first; anything extra is kept as credit for future months.</p>
                    <form method="POST" action="{{ route('payments.store') }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Student</label>
                                <select class="form-select" name="student_id" required>
                                    <option value="">Select student</option>
                                    @foreach ($allAccounts as $account)
                                        <option value="{{ $account->student->id }}" @selected(old('student_id') == $account->student->id)>
                                            {{ $account->student->admission_number }} — {{ $account->student->first_name }} {{ $account->student->last_name }}
                                            @if ($account->arrears()) (owes {{ number_format($account->arrears()) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Amount (KES)</label>
                                <input class="form-control" type="number" name="amount" min="1" value="{{ old('amount') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date paid</label>
                                <input class="form-control" type="date" name="paid_at" max="{{ now()->toDateString() }}" value="{{ old('paid_at', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Method</label>
                                <select class="form-select" name="method" id="payment-method">
                                    @foreach (\App\Models\Payment::METHODS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('method', 'mpesa') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5" id="mpesa-code-field">
                                <label class="form-label">M-Pesa transaction code</label>
                                <input class="form-control text-uppercase" name="mpesa_code" value="{{ old('mpesa_code') }}"
                                       placeholder="e.g. SJK4H7XQ2P" maxlength="10" pattern="[A-Za-z][A-Za-z0-9]{9}"
                                       title="10 letters/digits starting with a letter">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary w-100">Record Payment</button>
                            </div>
                        </div>
                    </form>
                    <p class="text-muted small mt-2 mb-0">Paybill <strong>985050</strong>, Account <strong>0800011902</strong>. Each M-Pesa code can only be recorded once.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Verify against bank statement</h5>
                    <p class="text-muted small">Upload the bank statement for paybill 985050 as a CSV. Codes and amounts that match are marked <span class="badge bg-success">Verified</span>; differences are flagged.</p>
                    <form method="POST" action="{{ route('payments.reconcile') }}" enctype="multipart/form-data">
                        @csrf
                        <input class="form-control mb-2" type="file" name="statement" accept=".csv,text/csv" required>
                        <button class="btn btn-outline-primary w-100">Check statement</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h4 class="mb-0">Student balances</h4>
        <div class="btn-group" role="group">
            @foreach (['all' => 'All', 'arrears' => 'In arrears', 'credit' => 'Prepaid'] as $value => $label)
                <a class="btn btn-sm {{ $filter === $value ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('payments.index', $value === 'all' ? [] : ['filter' => $value]) }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="table-responsive mb-4">
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr><th>Admission No.</th><th>Name</th><th>Monthly fee</th><th>Status</th><th>Balance (KES)</th><th>Paid until</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td>{{ $account->student->admission_number }}</td>
                        <td>{{ $account->student->first_name }} {{ $account->student->last_name }}</td>
                        <td>{{ number_format($account->student->monthly_fee) }}</td>
                        <td>
                            @if ($account->standing() === 'arrears')
                                <span class="badge bg-danger">{{ $account->arrearsMonths }} {{ Str::plural('month', $account->arrearsMonths) }} owing</span>
                            @elseif ($account->standing() === 'credit')
                                <span class="badge bg-info text-dark">Prepaid</span>
                            @else
                                <span class="badge bg-success">Up to date</span>
                            @endif
                        </td>
                        <td>
                            @if ($account->arrears())
                                <span class="text-danger fw-semibold">Owes {{ number_format($account->arrears()) }}</span>
                            @elseif ($account->credit())
                                <span class="text-success fw-semibold">Credit {{ number_format($account->credit()) }}</span>
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $monthLabel($account->paidUntil) }}</td>
                        <td><a href="{{ route('payments.show', $account->student) }}">Statement</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">No students match this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h4 class="mb-2">Recent payments</h4>
    <div class="table-responsive mb-4">
        <table class="table table-bordered bg-white">
            <thead class="table-dark">
                <tr><th>Date</th><th>Student</th><th>Amount</th><th>Method / Code</th><th>Verification</th><th>Recorded by</th></tr>
            </thead>
            <tbody>
                @forelse ($recentPayments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d M Y') }}</td>
                        <td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                        <td>{{ number_format($payment->amount) }}</td>
                        <td>{{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }} {{ $payment->mpesa_code }}</td>
                        <td>@include('payments.partials.verification', ['payment' => $payment])</td>
                        <td>{{ $payment->recorder?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">No payments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <h5 class="card-title">Months with no fees</h5>
            <p class="text-muted small">Mark holidays or breaks (e.g. Ramadan) so no fee is charged that month.</p>
            <form method="POST" action="{{ route('payments.non-billable.store') }}" class="row g-2 mb-3">
                @csrf
                <div class="col-sm-4"><input class="form-control" type="month" name="month" required></div>
                <div class="col-sm-5"><input class="form-control" name="reason" placeholder="Reason (optional)" maxlength="100"></div>
                <div class="col-sm-3"><button class="btn btn-outline-primary w-100">Add</button></div>
            </form>
            <ul class="list-group">
                @forelse ($nonBillableMonths as $nonBillable)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><strong>{{ $monthLabel($nonBillable->month) }}</strong> @if ($nonBillable->reason) — {{ $nonBillable->reason }} @endif</span>
                        <form method="POST" action="{{ route('payments.non-billable.destroy', $nonBillable) }}" onsubmit="return confirm('Start charging fees for this month again?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Every month is currently billed.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <script>
        (function () {
            const method = document.getElementById('payment-method');
            const codeField = document.getElementById('mpesa-code-field');
            const toggle = () => {
                const isMpesa = method.value === 'mpesa';
                codeField.style.display = isMpesa ? '' : 'none';
                codeField.querySelector('input').required = isMpesa;
            };
            method.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endsection
