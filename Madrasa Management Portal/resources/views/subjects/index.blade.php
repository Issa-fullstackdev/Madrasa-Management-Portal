@extends('layout')
@section('content')
    <h3 class="mb-3">Subjects</h3>
    <table class="table table-bordered bg-white">
        <thead class="table-dark">
            <tr><th>Name</th><th>Capacity</th></tr>
        </thead>
        <tbody>
            @foreach ($subjects as $subject)
                <tr>
                    <td>{{ $subject->name }}</td>
                    <td>{{ $subject->capacity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection