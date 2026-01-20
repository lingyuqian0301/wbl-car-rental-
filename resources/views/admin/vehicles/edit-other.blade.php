@extends('layouts.admin')

@section('title', 'Edit Vehicle')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Edit Vehicle</h1>
        <a href="{{ route('admin.vehicles.others', ['tab' => 'vehicle']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.others.update', $vehicle->vehicleID) }}">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Brand <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_brand" class="form-control" 
                               value="{{ old('vehicle_brand', $vehicle->vehicle_brand) }}" required>
                        @error('vehicle_brand')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Model <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_model" class="form-control" 
                               value="{{ old('vehicle_model', $vehicle->vehicle_model) }}" required>
                        @error('vehicle_model')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                        <input type="text" name="plate_number" class="form-control" 
                               value="{{ old('plate_number', $vehicle->plate_number) }}" required>
                        @error('plate_number')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Manufacturing Year</label>
                        <input type="number" name="manufacturing_year" class="form-control" 
                               value="{{ old('manufacturing_year', $vehicle->manufacturing_year) }}" 
                               min="1900" max="{{ date('Y') }}">
                        @error('manufacturing_year')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" 
                               value="{{ old('color', $vehicle->color) }}">
                        @error('color')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Engine Capacity (L)</label>
                        <input type="number" name="engineCapacity" step="0.01" class="form-control" 
                               value="{{ old('engineCapacity', $vehicle->engineCapacity) }}">
                        @error('engineCapacity')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Rental Price (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="rental_price" step="0.01" class="form-control" 
                               value="{{ old('rental_price', $vehicle->rental_price) }}" required>
                        @error('rental_price')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Availability Status <span class="text-danger">*</span></label>
                        <select name="availability_status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="available" {{ old('availability_status', $vehicle->availability_status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="rented" {{ old('availability_status', $vehicle->availability_status) === 'rented' ? 'selected' : '' }}>Rented</option>
                            <option value="maintenance" {{ old('availability_status', $vehicle->availability_status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="unavailable" {{ old('availability_status', $vehicle->availability_status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                        @error('availability_status')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Is Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isActive" value="1" 
                                   id="isActive" {{ old('isActive', $vehicle->isActive) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>
                        @error('isActive')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Update Vehicle
                    </button>
                    <a href="{{ route('admin.vehicles.others', ['tab' => 'vehicle']) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection




