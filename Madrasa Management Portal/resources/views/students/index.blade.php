@extends('layout')
@section('content')
    <h3 class="mb-3">Students</h3>

    @if (auth()->user()->role === 'teacher')
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Register New Student</h5>
                <form method="POST" action="/students">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-3"><input class="form-control" name="first_name" placeholder="First name" required></div>
                        <div class="col-md-3"><input class="form-control" name="last_name" placeholder="Last name" required></div>
                        <div class="col-md-3"><input class="form-control" name="guardian_name" placeholder="Guardian name" required></div>
                        <div class="col-md-3"><input class="form-control" name="guardian_phone" placeholder="Guardian phone" required></div>
                        <div class="col-md-3 mt-2">
                            <select class="form-control" name="subject_id" required>
                                <option value="">Select subject/class</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mt-2">
                            <button class="btn btn-primary w-100">Enroll Student</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>Admission No.</th><th>Name</th><th>Guardian</th><th>Phone</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->guardian_name }}</td>
                    <td>{{ $student->guardian_phone }}</td>
                    <td>{{ $student->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection