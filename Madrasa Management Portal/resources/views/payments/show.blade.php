@extends('layout')
@section('content')
    @php
        $student = $account->student;
        $monthLabel = fn ($month) => $month ? \Carbon\Carbon::parse($month . '-01')->format('F Y') : '-';
    @endphp

    <a href="{{ route('payments.index') }}" class="small">&larr; Back to payments</a>
    <h3 class="mb-1 mt-2">Fee statement — {{ $student->first_name }} {{ $student->last_name }}</h3>
    <p class="text-muted">Adm. No. {{ $student->admission_number }} · Enrolled {{ $student->enrolled_at->format('d M Y') }} · {{ ucfirst($student->status) }}</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ number_format($account->totalCharged) }}</div>
            <div class="kpi-label">Total fees charged (KES)</div>
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ number_format($account->totalPaid) }}</div>
            <div class="kpi-label">Total paid (KES)</div>
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            @if ($account->arrears())
                <div class="kpi-value text-danger">{{ number_format($account->arrears()) }}</div>
                <div class="kpi-label">Owing — {{ $account->arrearsMonths }} {{ Str::plural('month', $account->arrearsMonths) }}</div>
            @else
                <div class="kpi-value text-success">{{ number_format($account->credit()) }}</div>
                <div class="kpi-label">Credit (prepaid)</div>
            @endif
        </div></div></div>
        <div class="col-12 col-sm-6 col-lg-3"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ $monthLabel($account->paidUntil) }}</div>
            <div class="kpi-label">Paid until</div>
        </div></div></div>
    </div>

    <div class="card mb-4" style="max-width: 520px;">
        <div class="card-body">
            <h5 class="card-title">Monthly fee</h5>
            <form method="POST" action="{{ route('payments.fee.update', $student) }}" class="row g-2">
                @csrf @method('PATCH')
                <div class="col-7"><input class="form-control" type="number" name="monthly_fee" min="0" value="{{ $student->monthly_fee }}" required></div>
                <div class="col-5"><button class="btn btn-outline-primary w-100">Update fee</button></div>
            </form>
            <p class="text-muted small mt-2 mb-0">A change applies from {{ now()->format('F Y') }} onwards. Earlier months keep the fee charged at the time. Use 0 for a full scholarship.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <h4>Monthly charges</h4>
            <div class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead class="table-dark"><tr><th>Month</th><th>Fee</th><th>Paid</th><th>Outstanding</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse (array_reverse($account->months) as $month)
                            <tr>
                                <td>{{ $monthLabel($month['month']) }}</td>
                                <td>{{ number_format($month['charged']) }}</td>
                                <td>{{ number_format($month['applied']) }}</td>
                                <td>{{ number_format($month['outstanding']) }}</td>
                                <td>
                                    @if ($month['status'] === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif ($month['status'] === 'partial')
                                        <span class="badge bg-warning text-dark">Part paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center">No fees charged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-muted small">Payments are applied to the oldest unpaid month first.</p>
        </div>

        <div class="col-12 col-xl-6">
            <h4>Payments received</h4>
            <div class="table-responsive">
                <table class="table table-bordered bg-white">
                    <thead class="table-dark"><tr><th>Date</th><th>Amount</th><th>Method / Code</th><th>Verification</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at?->format('d M Y') }}</td>
                                <td>{{ number_format($payment->amount) }}</td>
                                <td>
                                    {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }} {{ $payment->mpesa_code }}
                                    <div class="small text-muted">by {{ $payment->recorder?->name ?? 'unknown' }}</div>
                                </td>
                                <td>@include('payments.partials.verification', ['payment' => $payment])</td>
                                <td>
                                    @unless ($payment->isVerified())
                                        <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment? Use this only for entries made in error.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
