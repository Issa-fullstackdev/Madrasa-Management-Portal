@extends('layout')
@section('content')
    <a href="{{ route('payments.index') }}" class="small">&larr; Back to payments</a>
    <h3 class="mb-1 mt-2">Statement check</h3>
    <p class="text-muted">Read {{ $result['rows'] }} rows from the statement.</p>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body">
            <div class="kpi-value text-success">{{ count($result['verified']) }}</div>
            <div class="kpi-label">Payments verified</div>
        </div></div></div>
        <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body">
            <div class="kpi-value text-danger">{{ count($result['mismatched']) }}</div>
            <div class="kpi-label">Amount mismatches</div>
        </div></div></div>
        <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body">
            <div class="kpi-value">{{ count($result['unrecorded']) }}</div>
            <div class="kpi-label">On statement, not recorded</div>
        </div></div></div>
    </div>

    @if (count($result['mismatched']))
        <h4>Amount mismatches</h4>
        <p class="text-muted small">The code is on the statement, but the amount recorded in the portal is different. Check with the parent, then delete the payment and re-enter it with the correct amount.</p>
        <div class="table-responsive mb-4">
            <table class="table table-bordered bg-white">
                <thead class="table-dark"><tr><th>Code</th><th>Student</th><th>Recorded amount</th><th>Amounts on statement row</th></tr></thead>
                <tbody>
                    @foreach ($result['mismatched'] as $row)
                        <tr>
                            <td>{{ $row['payment']->mpesa_code }}</td>
                            <td><a href="{{ route('payments.show', $row['payment']->student) }}">{{ $row['payment']->student->first_name }} {{ $row['payment']->student->last_name }}</a></td>
                            <td>{{ number_format($row['payment']->amount) }}</td>
                            <td>{{ collect($row['amounts'])->map(fn ($a) => number_format($a, 2))->join(', ') ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($result['unrecorded']))
        <h4>On the statement but not recorded in the portal</h4>
        <p class="text-muted small">These look like M-Pesa codes that nobody has recorded yet. If they are fee payments, record them on the payments page.</p>
        <div class="table-responsive mb-4">
            <table class="table table-bordered bg-white">
                <thead class="table-dark"><tr><th>Code</th><th>Statement row</th></tr></thead>
                <tbody>
                    @foreach ($result['unrecorded'] as $row)
                        <tr><td>{{ $row['code'] }}</td><td class="small">{{ $row['row'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($result['verified']))
        <h4>Verified</h4>
        <div class="table-responsive mb-4">
            <table class="table table-bordered bg-white">
                <thead class="table-dark"><tr><th>Code</th><th>Student</th><th>Amount</th></tr></thead>
                <tbody>
                    @foreach ($result['verified'] as $payment)
                        <tr><td>{{ $payment->mpesa_code }}</td><td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td><td>{{ number_format($payment->amount) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="text-muted small">Recorded M-Pesa payments whose code is not on this statement stay <span class="badge bg-warning text-dark">Unverified</span>. They may fall outside the statement dates, or the code may be wrong or fake.</p>
@endsection
