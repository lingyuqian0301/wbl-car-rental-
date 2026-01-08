@extends('layouts.admin')

@section('title', 'Edit Owner Leasing')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-building"></i> Edit Owner Leasing</h1>
        <a href="{{ route('admin.leasing.index', ['tab' => 'owner']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Owner Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.leasing.owner.update', $owner) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IC Number <span class="text-danger">*</span></label>
                        <input type="text" name="ic_no" class="form-control" value="{{ old('ic_no', $owner->ic_no) }}" required>
                        @error('ic_no')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $owner->contact_number) }}">
                        @error('contact_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $owner->email) }}">
                        @error('email')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bankname" class="form-control" value="{{ old('bankname', $owner->bankname) }}">
                        @error('bankname')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="bank_acc_number" class="form-control" value="{{ old('bank_acc_number', $owner->bank_acc_number) }}">
                        @error('bank_acc_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Registration Date</label>
                        <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', $owner->registration_date ? $owner->registration_date->format('Y-m-d') : '') }}">
                        @error('registration_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Leasing Price</label>
                        <input type="number" name="leasing_price" class="form-control" step="0.01" value="{{ old('leasing_price', $owner->leasing_price) }}" placeholder="0.00">
                        @error('leasing_price')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Leasing Due Date</label>
                        <input type="date" name="leasing_due_date" class="form-control" value="{{ old('leasing_due_date', $owner->leasing_due_date ? $owner->leasing_due_date->format('Y-m-d') : '') }}">
                        @error('leasing_due_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="isActive" class="form-select">
                            <option value="1" {{ old('isActive', $owner->isActive) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('isActive', $owner->isActive) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('isActive')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.leasing.index', ['tab' => 'owner']) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Update Owner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection




