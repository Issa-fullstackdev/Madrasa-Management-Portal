@extends('layout')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0">Attendance — {{ \Carbon\Carbon::parse($today)->format('d M Y') }}</h3>
        <div class="btn-group">
            @foreach ($subjects as $subject)
                <a href="/attendance?subject_id={{ $subject->id }}"
                   class="btn btn-sm {{ $subject->id == $selectedSubjectId ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ $subject->name }}
                </a>
            @endforeach
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card h-100">
                <div class="card-body kpi-card">
                    <div class="kpi-icon" style="background: var(--mosque-green);">&#10003;</div>
                    <div>
                        <div class="kpi-value">{{ $checkedIn->count() }} / {{ $enrolledStudents->count() }}</div>
                        <div class="kpi-label">Checked In Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->role === 'teacher')
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Check In a Student</h5>
                <form method="POST" action="/attendance">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <input class="form-control form-control-lg" name="admission_number" placeholder="Scan or type admission number" autofocus>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary btn-lg w-100">Check In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Checked In Today</h5>
            <table class="table table-bordered bg-white mb-0">
                <thead class="table-dark">
                    <tr><th>Admission No.</th><th>Name</th><th>Time</th></tr>
                </thead>
                <tbody>
                    @forelse ($checkedIn as $record)
                        <tr>
                            <td>{{ $record->student->admission_number }}</td>
                            <td>{{ $record->student->first_name }} {{ $record->student->last_name }}</td>
                            <td>{{ $record->created_at->format('h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">No check-ins yet today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->role === 'teacher')
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Juz Progress</h5>
                <p class="text-muted small">Track how many Juz each student has completed in this class (out of 30).</p>
                <form method="POST" action="/progress">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                    <table class="table table-bordered bg-white mb-3">
                        <thead class="table-dark">
                            <tr><th>Admission No.</th><th>Name</th><th style="width: 220px;">Juz Completed (/30)</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($enrolledStudents as $student)
                                <tr>
                                    <td>{{ $student->admission_number }}</td>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td>
                                        <input type="number" min="0" max="30" class="form-control form-control-sm"
                                               name="progress[{{ $student->id }}]"
                                               value="{{ $student->subjects->first()->pivot->juz_completed ?? 0 }}">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center">No students enrolled in this class.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($enrolledStudents->count())
                        <button class="btn btn-primary">Save Progress</button>
                    @endif
                </form>
            </div>
        </div>
    @endif
@endsection
