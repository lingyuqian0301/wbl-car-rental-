@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-pencil"></i> Edit Customer</h1>
        <a href="{{ route('admin.manage.client') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Customer Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                @csrf
                @method('PUT')
                @php
                    $user = $customer->user;
                    $local = $customer->local;
                    $international = $customer->international;
                    $localStudent = $customer->localStudent;
                    $internationalStudent = $customer->internationalStudent;
                    $studentDetails = $localStudent->studentDetails ?? $internationalStudent->studentDetails ?? null;
                @endphp

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}">
                        @error('email')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? $customer->phone_number ?? '') }}">
                        @error('phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DOB" class="form-control" value="{{ old('DOB', $user->DOB ? \Carbon\Carbon::parse($user->DOB)->format('Y-m-d') : '') }}">
                        @error('DOB')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
                        @error('address')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer License</label>
                        <input type="text" name="customer_license" class="form-control" value="{{ old('customer_license', $customer->customer_license ?? '') }}">
                        @error('customer_license')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $customer->emergency_contact ?? '') }}">
                        @error('emergency_contact')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($local)
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number</label>
                        <input type="text" name="ic_number" class="form-control" value="{{ old('ic_number', $local->ic_no ?? '') }}">
                        @error('ic_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">State of Origin</label>
                        <input type="text" name="stateOfOrigin" class="form-control" value="{{ old('stateOfOrigin', $local->stateOfOrigin ?? '') }}">
                        @error('stateOfOrigin')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    @elseif($international)
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Passport Number</label>
                        <input type="text" name="passport_no" class="form-control" value="{{ old('passport_no', $international->passport_no ?? '') }}">
                        @error('passport_no')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country of Origin</label>
                        <input type="text" name="countryOfOrigin" class="form-control" value="{{ old('countryOfOrigin', $international->countryOfOrigin ?? '') }}">
                        @error('countryOfOrigin')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    @if($localStudent || $internationalStudent)
                    <div class="col-12 mt-3 pt-3 border-top">
                        <h6 class="fw-semibold mb-3">Student Information</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Matric Number</label>
                        <input type="text" name="matric_number" class="form-control" value="{{ old('matric_number', $localStudent->matric_number ?? $internationalStudent->matric_number ?? '') }}">
                        @error('matric_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">College</label>
                        <input type="text" name="college" class="form-control" value="{{ old('college', $studentDetails->college ?? '') }}">
                        @error('college')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Faculty</label>
                        <input type="text" name="faculty" class="form-control" value="{{ old('faculty', $studentDetails->faculty ?? '') }}">
                        @error('faculty')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Programme</label>
                        <input type="text" name="programme" class="form-control" value="{{ old('programme', $studentDetails->programme ?? '') }}">
                        @error('programme')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Year of Study</label>
                        <input type="text" name="yearOfStudy" class="form-control" value="{{ old('yearOfStudy', $studentDetails->yearOfStudy ?? '') }}">
                        @error('yearOfStudy')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.manage.client') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
@endsection











