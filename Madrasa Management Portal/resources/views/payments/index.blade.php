@extends('layout')
@section('content')
    <h3 class="mb-3">Payments — {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Pay via M-Pesa</h5>
            <p class="mb-1">Paybill Number: <strong>985050</strong></p>
            <p class="mb-0">Account Number: <strong>0800011902</strong></p>
        </div>
    </div>

    <form method="GET" action="/payments" class="mb-4" style="max-width: 220px;">
        <label class="form-label">Month</label>
        <input type="month" class="form-control" name="month" value="{{ $month }}" onchange="this.form.submit()">
    </form>

    @if (auth()->user()->role === 'teacher')
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Record Payment</h5>
                <form method="POST" action="/payments">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="form-control" name="student_id" required>
                                <option value="">Select student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->admission_number }} — {{ $student->first_name }} {{ $student->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" type="number" name="amount" value="10000" required>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" name="paybill_reference" placeholder="M-Pesa transaction code">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">Mark Paid</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>Admission No.</th><th>Name</th><th>Status</th><th>Amount</th><th>Reference</th><th>Paid At</th></tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                @php $payment = $payments->get($student->id); @endphp
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>
                        @if ($payment && $payment->status === 'paid')
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>{{ $payment->amount ?? '-' }}</td>
                    <td>{{ $payment->paybill_reference ?? '-' }}</td>
                    <td>{{ $payment?->paid_at?->format('d M Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
