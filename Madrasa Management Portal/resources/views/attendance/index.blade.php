@extends('layout')
@section('content')
    <h3 class="mb-3">Attendance — {{ \Carbon\Carbon::parse($today)->format('d M Y') }}</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/attendance" class="mb-3">
                <label class="form-label">Class</label>
                <select class="form-control" name="subject_id" onchange="this.form.submit()">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected($subject->id == $selectedSubjectId)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </form>

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

    <h5>Checked in today ({{ $checkedIn->count() }})</h5>
    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>Admission No.</th><th>Name</th><th>Time</th></tr>
        </thead>
        <tbody>
            @foreach ($checkedIn as $record)
                <tr>
                    <td>{{ $record->student->admission_number }}</td>
                    <td>{{ $record->student->first_name }} {{ $record->student->last_name }}</td>
                    <td>{{ $record->created_at->format('h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection