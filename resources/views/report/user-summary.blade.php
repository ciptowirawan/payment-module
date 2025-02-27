@extends('layouts.dashboard')

@section('content')

<h1 class="h3 mb-2 text-gray-800">User Registration Summary</h1>

<form action="/reports/user-summary" method="get" class="d-sm-inline-block form-inline mr-auto ml-md-12 my-2 my-md-0 w-100">
    <div class="input-group">
        <input type="text" class="form-control bg-light border-1 small"
        name="search" aria-label="Search" aria-describedby="basic-addon2" value="{{ request('search') }}">
        <div class="input-group-append">
        <button class="btn" style="z-index: 0; background-color: #4c076b" type="submit">
            <i class="fas fa-search fa-sm" style="color: white"></i>
        </button>
        </div>
    </div>
</form>

@if ($registrations->count())
<div class="table-responsive mt-2">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Registrations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrations as $registration)
            <tr>
                <td>{{ $registration->date }}</td>
                <td>{{ $registration->total_registrations }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else 
    <p class="text-center my-4 fs-4">Peserta Tidak Ditemukan.</p>   
@endif

<div class="d-flex justify-content-center" >
    {{ $registrations->links() }}
</div>

@endsection