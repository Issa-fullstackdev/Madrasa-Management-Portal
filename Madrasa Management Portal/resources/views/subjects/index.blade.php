@extends('layout')
@section('content')
    <h3 class="mb-3">Subjects</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @forelse ($subjects as $subject)
        <section class="mb-4" aria-labelledby="subject-{{ $subject->id }}">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                <div>
                    <h4 id="subject-{{ $subject->id }}" class="mb-1">{{ $subject->name }}</h4>
                    <div class="text-muted">
                        <span class="subject-enrollment-count">{{ $subject->students_count }} / {{ $subject->capacity }}</span>
                        students enrolled / capacity
                    </div>
                </div>
                @if (auth()->user()->role === 'principal')
                    <form method="POST" action="{{ route('subjects.capacity.update', $subject) }}" class="d-flex align-items-end gap-2">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="form-label mb-1" for="capacity-{{ $subject->id }}">Capacity</label>
                            <input class="form-control form-control-sm" id="capacity-{{ $subject->id }}" name="capacity" type="number" min="1" max="255" value="{{ $subject->capacity }}" required>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">Save</button>
                    </form>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered bg-white mb-0">
                    <thead class="table-dark">
                        <tr><th scope="col">Admission no.</th><th scope="col">Student</th><th scope="col">Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($subject->students as $student)
                            <tr>
                                <td>{{ $student->admission_number }}</td>
                                <td>{{ collect([$student->first_name, $student->middle_name, $student->last_name])->filter()->join(' ') }}</td>
                                <td>{{ ucfirst($student->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center">No students enrolled in this subject.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <p class="text-muted">No subjects have been added yet.</p>
    @endforelse
@endsection