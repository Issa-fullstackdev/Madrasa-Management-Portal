@extends('layout')
@section('content')
    <h3 class="mb-3">Students</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="mb-0">Darul Arqam Islamic Centre</h4>
                    <div class="text-muted">Madrassa Admission Form</div>
                </div>
                <div class="text-end small">
                    <div>Paybill No. <strong>985050</strong></div>
                    <div>Account: <strong>0800011902</strong></div>
                </div>
            </div>

            <form method="POST" action="/students">
                @csrf

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input class="form-control" value="{{ now()->format('d/m/Y') }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adm. No.</label>
                        <input class="form-control" value="(auto-assigned on save)" disabled>
                    </div>
                </div>

                <h6 class="fw-bold">Student Names</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><input class="form-control" name="first_name" placeholder="First" value="{{ old('first_name') }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="middle_name" placeholder="Middle" value="{{ old('middle_name') }}"></div>
                    <div class="col-md-4"><input class="form-control" name="last_name" placeholder="Last" value="{{ old('last_name') }}" required></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Birth</label>
                        <input class="form-control" name="place_of_birth" value="{{ old('place_of_birth') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label d-block">Gender</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_male" value="male" {{ old('gender') == 'male' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="gender_male">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_female" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender_female">Female</label>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold">Father's Names</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><input class="form-control" name="father_first_name" placeholder="First" value="{{ old('father_first_name') }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="father_middle_name" placeholder="Middle" value="{{ old('father_middle_name') }}"></div>
                    <div class="col-md-4"><input class="form-control" name="father_last_name" placeholder="Last" value="{{ old('father_last_name') }}" required></div>
                </div>

                <h6 class="fw-bold">Mother's Names</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><input class="form-control" name="mother_first_name" placeholder="First" value="{{ old('mother_first_name') }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="mother_middle_name" placeholder="Middle" value="{{ old('mother_middle_name') }}"></div>
                    <div class="col-md-4"><input class="form-control" name="mother_last_name" placeholder="Last" value="{{ old('mother_last_name') }}" required></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Father's Number</label>
                        <input class="form-control" name="father_phone" value="{{ old('father_phone') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mother's Number</label>
                        <input class="form-control" name="mother_phone" value="{{ old('mother_phone') }}" required>
                    </div>
                </div>

                <h6 class="fw-bold">Home Address</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-4"><input class="form-control" name="address_building" placeholder="Building/Estate" value="{{ old('address_building') }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="address_road" placeholder="Road/Street" value="{{ old('address_road') }}" required></div>
                    <div class="col-md-4"><input class="form-control" name="address_county" placeholder="County" value="{{ old('address_county') }}" required></div>
                </div>

                <h6 class="fw-bold">Contact in case of emergency</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><input class="form-control" name="emergency_contact_name" placeholder="Name" value="{{ old('emergency_contact_name') }}" required></div>
                    <div class="col-md-6"><input class="form-control" name="emergency_contact_phone" placeholder="Number" value="{{ old('emergency_contact_phone') }}" required></div>
                </div>

                <h6 class="fw-bold">Class / Subject</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <select class="form-control" name="subject_id" required>
                            <option value="">Select subject/class</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold">Declaration</h6>
                <p>I hereby declare that I will abide by the rules and regulations of the Institution.</p>
                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label">Signed by the Parent/Guardian</label>
                        <input class="form-control" name="guardian_signed_name" placeholder="Parent/Guardian name" value="{{ old('guardian_signed_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date</label>
                        <input class="form-control" value="{{ now()->format('d/m/Y') }}" disabled>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="declaration" id="declaration" value="1" {{ old('declaration') ? 'checked' : '' }} required>
                    <label class="form-check-label" for="declaration">I agree to the declaration above.</label>
                </div>

                <button class="btn btn-primary">Enroll Student</button>
            </form>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>Adm. No.</th><th>Name</th><th>Gender</th><th>Father's Phone</th><th>Mother's Phone</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr>
                    <td>{{ $student->admission_number }}</td>
                    <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
                    <td>{{ ucfirst($student->gender) }}</td>
                    <td>{{ $student->father_phone }}</td>
                    <td>{{ $student->mother_phone }}</td>
                    <td>{{ $student->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
