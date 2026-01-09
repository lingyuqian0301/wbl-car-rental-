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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Matric Number</label>
                        <input type="text" name="matric_number" class="form-control" value="{{ old('matric_number', $customer->matric_number) }}">
                        @error('matric_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" value="{{ old('fullname', $customer->fullname) }}" required>
                        @error('fullname')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number</label>
                        <input type="text" name="ic_number" class="form-control" value="{{ old('ic_number', $customer->ic_number) }}">
                        @error('ic_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                        @error('phone')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">College</label>
                        <input type="text" name="college" class="form-control" value="{{ old('college', $customer->college) }}">
                        @error('college')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Faculty</label>
                        <input type="text" name="faculty" class="form-control" value="{{ old('faculty', $customer->faculty) }}">
                        @error('faculty')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer Type</label>
                        <input type="text" name="customer_type" class="form-control" value="{{ old('customer_type', $customer->customer_type) }}">
                        @error('customer_type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Registration Date</label>
                        <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', $customer->registration_date ? \Carbon\Carbon::parse($customer->registration_date)->format('Y-m-d') : '') }}">
                        @error('registration_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $customer->emergency_contact) }}">
                        @error('emergency_contact')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $customer->country) }}">
                        @error('country')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Customer License</label>
                        <input type="text" name="customer_license" class="form-control" value="{{ old('customer_license', $customer->customer_license) }}">
                        @error('customer_license')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.manage.client') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
@endsection











