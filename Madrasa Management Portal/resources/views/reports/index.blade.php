@extends('layout')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0">Principal reports</h3>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Print report</button>
    </div>

    <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label" for="from">Attendance and enrollment from</label>
            <input class="form-control" id="from" name="from" type="date" value="{{ $filters['from'] }}" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label" for="to">To</label>
            <input class="form-control" id="to" name="to" type="date" value="{{ $filters['to'] }}" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <label class="form-label" for="month">Fee month</label>
            <input class="form-control" id="month" name="month" type="month" value="{{ $filters['month'] }}" required>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <button class="btn btn-primary w-100" type="submit">Update report</button>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="mb-4" aria-labelledby="enrollment-heading">
        <h4 id="enrollment-heading" class="mb-3">Student enrollment</h4>
        <div class="row g-3">
            <div class="col-12 col-sm-6">
                <div class="card h-100"><div class="card-body">
                    <div class="kpi-value">{{ $activeStudentCount }}</div>
                    <div class="kpi-label">Active students</div>
                </div></div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="card h-100"><div class="card-body">
                    <div class="kpi-value">{{ $newEnrollmentCount }}</div>
                    <div class="kpi-label">New enrollments in selected dates</div>
                </div></div>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-bordered bg-white mb-0">
                <thead class="table-dark"><tr><th scope="col">Enrollment date</th><th scope="col">Students enrolled</th></tr></thead>
                <tbody>
                    @forelse ($enrollmentSummary as $day)
                        <tr><td>{{ \Carbon\Carbon::parse($day->enrolled_at)->format('d M Y') }}</td><td>{{ $day->student_count }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-muted text-center">No enrollments in this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-4" aria-labelledby="attendance-heading">
        <h4 id="attendance-heading" class="mb-3">Attendance report</h4>
        <div class="table-responsive">
            <table class="table table-bordered bg-white mb-0">
                <thead class="table-dark"><tr><th scope="col">Date</th><th scope="col">Class</th><th scope="col">Present</th><th scope="col">Absent</th></tr></thead>
                <tbody>
                    @forelse ($attendanceSummary as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                            <td>{{ $day->subject->name }}</td>
                            <td>{{ $day->present_count }}</td>
                            <td>{{ $day->absent_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">No attendance records in this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2 mb-0">Counts show saved attendance records only. The current check-in workflow records present students; absences are counted only when explicitly entered.</p>
    </section>

    <section aria-labelledby="fees-heading">
        <h4 id="fees-heading" class="mb-3">Fee payments for {{ \Carbon\Carbon::createFromFormat('Y-m', $filters['month'])->format('F Y') }}</h4>
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body"><div class="kpi-value">{{ $paidPaymentCount }}</div><div class="kpi-label">Fees paid</div></div></div></div>
            <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body"><div class="kpi-value">{{ $pendingPaymentCount }}</div><div class="kpi-label">Students without a paid fee record</div></div></div></div>
            <div class="col-12 col-sm-4"><div class="card h-100"><div class="card-body"><div class="kpi-value">{{ number_format($collectedAmount) }}</div><div class="kpi-label">Amount recorded as received</div></div></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered bg-white mb-0">
                <thead class="table-dark"><tr><th scope="col">Admission no.</th><th scope="col">Student</th><th scope="col">Amount</th><th scope="col">Payment date</th></tr></thead>
                <tbody>
                    @forelse ($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->student->admission_number }}</td>
                            <td>{{ $payment->student->first_name }} {{ $payment->student->last_name }}</td>
                            <td>{{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->paid_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">No paid fees recorded for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-2 mb-0">Showing the 10 most recent paid records. Amounts use the same unit as the payment register.</p>
    </section>

    <style>
        @media print {
            .app-sidebar, .app-sidebar-offcanvas, .app-topbar, form, button { display: none !important; }
            .app-main { width: 100% !important; }
            .container-fluid { margin: 0 !important; padding: 0 !important; }
        }
    </style>
@endsection