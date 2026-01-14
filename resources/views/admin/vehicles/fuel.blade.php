@extends('layouts.admin')

@section('title', 'Vehicle Fuel and Wash Records')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .receipt-img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    .receipt-img:hover {
        opacity: 0.8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}?tab=car-info" class="nav-link">
                <i class="bi bi-info-circle"></i> Car Info
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}?tab=owner-info" class="nav-link">
                <i class="bi bi-person"></i> Owner Info
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.maintenance', $vehicle->vehicleID) }}" class="nav-link">
                <i class="bi bi-tools"></i> Maintenance
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fuel" type="button" role="tab">
                <i class="bi bi-fuel-pump"></i> Fuel and Wash
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}?tab=booking-history" class="nav-link">
                <i class="bi bi-clock-history"></i> Booking History
            </a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $vehicle->vehicle_brand ?? 'N/A' }} {{ $vehicle->vehicle_model ?? 'N/A' }}</h1>
            <div class="text-muted small">
                Plate Number: {{ $vehicle->plate_number ?? 'N/A' }} · Vehicle ID: #{{ $vehicle->vehicleID }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vehicle
            </a>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addFuelModal">
                <i class="bi bi-plus-circle"></i> Add Fuel/Wash Record
            </button>
        </div>
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

    <!-- Fuel Records Table -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-fuel-pump"></i> Fuel and Wash Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Service Type</th>
                            <th>Cost (RM)</th>
                            <th>Receipt</th>
                            <th>Handled By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->fuels as $fuel)
                            <tr>
                                <td>{{ $fuel->fuel_date ? \Carbon\Carbon::parse($fuel->fuel_date)->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ ($fuel->service_type ?? 'fuel') === 'fuel' ? 'bg-primary' : 'bg-info' }}">
                                        {{ ucfirst($fuel->service_type ?? 'fuel') }}
                                    </span>
                                </td>
                                <td><strong>RM {{ number_format($fuel->cost ?? 0, 2) }}</strong></td>
                                <td>
                                    @if($fuel->receipt_img)
                                        <img src="{{ asset('storage/' . $fuel->receipt_img) }}" 
                                             alt="Receipt" 
                                             class="receipt-img"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#viewReceiptModal{{ $fuel->fuelID }}">
                                    @else
                                        <span class="text-muted">No receipt</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fuel->handledByUser)
                                        {{ $fuel->handledByUser->name ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFuelModal{{ $fuel->fuelID }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.vehicles.fuel.destroy', $fuel->fuelID) }}" 
                                              onsubmit="return confirm('Are you sure?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Receipt Modal -->
                            @if($fuel->receipt_img)
                            <div class="modal fade" id="viewReceiptModal{{ $fuel->fuelID }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Fuel Receipt</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center" style="min-height: 400px;">
                                            @php
                                                $isPdf = strtolower(pathinfo($fuel->receipt_img, PATHINFO_EXTENSION)) === 'pdf';
                                            @endphp
                                            @if($isPdf)
                                                <iframe src="{{ asset('storage/' . $fuel->receipt_img) }}" 
                                                        style="width: 100%; height: 70vh; border: none; border-radius: 6px;"
                                                        onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>PDF not found</p>';">
                                                </iframe>
                                            @else
                                                <img src="{{ asset('storage/' . $fuel->receipt_img) }}" 
                                                     alt="Fuel Receipt" 
                                                     class="img-fluid" 
                                                     style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                     onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <a href="{{ asset('storage/' . $fuel->receipt_img) }}" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="bi bi-download"></i> Open in New Tab
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Edit Fuel Modal -->
                            <div class="modal fade" id="editFuelModal{{ $fuel->fuelID }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Fuel/Wash Record</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.vehicles.fuel.update', $fuel->fuelID) }}" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="fuel_date" class="form-control" value="{{ old('fuel_date', $fuel->fuel_date ? \Carbon\Carbon::parse($fuel->fuel_date)->format('Y-m-d') : '') }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Service Type <span class="text-danger">*</span></label>
                                                    <select name="service_type" class="form-select" required>
                                                        <option value="fuel" {{ old('service_type', $fuel->service_type ?? 'fuel') === 'fuel' ? 'selected' : '' }}>Fuel</option>
                                                        <option value="wash" {{ old('service_type', $fuel->service_type ?? 'fuel') === 'wash' ? 'selected' : '' }}>Wash</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Cost (RM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="cost" step="0.01" class="form-control" value="{{ old('cost', $fuel->cost) }}" min="0" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Receipt Image/PDF</label>
                                                    <input type="file" name="receipt_img" class="form-control" accept="image/*,.pdf">
                                                    <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                                                    @if($fuel->receipt_img)
                                                        <div class="mt-2">
                                                            <small class="text-muted">Current: <a href="{{ asset('storage/' . $fuel->receipt_img) }}" target="_blank">View</a></small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Handled By</label>
                                                    <select name="handled_by" class="form-select">
                                                        <option value="">Select Staff</option>
                                                        @foreach($staffUsers as $staff)
                                                            <option value="{{ $staff->userID }}" {{ old('handled_by', $fuel->handled_by) == $staff->userID ? 'selected' : '' }}>
                                                                {{ $staff->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No fuel/wash records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Fuel Modal -->
<div class="modal fade" id="addFuelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Fuel/Wash Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.vehicles.fuel.store', $vehicle->vehicleID) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="fuel_date" class="form-control" value="{{ old('fuel_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type" class="form-select" required>
                            <option value="fuel" {{ old('service_type', 'fuel') === 'fuel' ? 'selected' : '' }}>Fuel</option>
                            <option value="wash" {{ old('service_type') === 'wash' ? 'selected' : '' }}>Wash</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cost (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="cost" step="0.01" class="form-control" value="{{ old('cost') }}" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Receipt Image/PDF</label>
                        <input type="file" name="receipt_img" class="form-control" accept="image/*,.pdf">
                        <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Handled By</label>
                        <select name="handled_by" class="form-select">
                            <option value="">Select Staff</option>
                            @foreach($staffUsers as $staff)
                                <option value="{{ $staff->userID }}" {{ old('handled_by') == $staff->userID ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection





