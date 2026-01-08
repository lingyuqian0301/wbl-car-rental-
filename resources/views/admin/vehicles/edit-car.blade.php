@extends('layouts.admin')

@section('title', 'Edit Car')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Edit Car</h1>
        <a href="{{ route('admin.vehicles.cars') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.vehicles.cars.update', $vehicle->vehicleID) }}">
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
                        <label class="form-label">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="seating_capacity" class="form-control" 
                               value="{{ old('seating_capacity', $car->seating_capacity) }}" 
                               min="1" max="50" required>
                        @error('seating_capacity')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Transmission <span class="text-danger">*</span></label>
                        <select name="transmission" class="form-select" required>
                            <option value="">Select Transmission</option>
                            <option value="Manual" {{ old('transmission', $car->transmission) === 'Manual' ? 'selected' : '' }}>Manual</option>
                            <option value="Automatic" {{ old('transmission', $car->transmission) === 'Automatic' ? 'selected' : '' }}>Automatic</option>
                        </select>
                        @error('transmission')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Car Type <span class="text-danger">*</span></label>
                        <input type="text" name="car_type" class="form-control" 
                               value="{{ old('car_type', $car->car_type) }}" required>
                        @error('car_type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-1"></i> Update Car
                    </button>
                    <a href="{{ route('admin.vehicles.cars') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



