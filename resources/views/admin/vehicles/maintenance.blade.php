@extends('layouts.admin')

@section('title', 'Vehicle Maintenance')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    /* Zoomed image modal styles */
    .maintenance-image-zoom {
        cursor: zoom-in;
        transition: transform 0.3s ease;
    }
    .maintenance-image-zoom:hover {
        transform: scale(1.02);
    }
    .maintenance-image-zoom.zoomed {
        cursor: zoom-out;
        transform: scale(1.5);
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
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                <i class="bi bi-tools"></i> Maintenance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.fuel', $vehicle->vehicleID) }}" class="nav-link">
                <i class="bi bi-fuel-pump"></i> Fuel
            </a>
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
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                <i class="bi bi-plus-circle"></i> Add Maintenance
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

    <!-- Maintenance List -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-tools"></i> Maintenance List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Service Type</th>
                            <th>Mileage</th>
                            <th>Cost</th>
                            <th>Commission</th>
                            <th>Next Due</th>
                            <th>Maintenance Image</th>
                            <th>Block Dates</th>
                            <th>Accompany Vehicle</th>
                            <th>Staff Handled</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->maintenances as $maintenance)
                            <tr>
                                <td>{{ $maintenance->service_date ? \Carbon\Carbon::parse($maintenance->service_date)->format('d M Y') : 'N/A' }}</td>
                                <td>{{ $maintenance->service_type ?? 'N/A' }}</td>
                                <td>{{ $maintenance->mileage ?? 'N/A' }}</td>
                                <td>RM {{ number_format($maintenance->cost ?? 0, 2) }}</td>
                                <td>RM {{ number_format($maintenance->commission_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($maintenance->next_due_date)
                                        @php
                                            $nextDue = \Carbon\Carbon::parse($maintenance->next_due_date);
                                            $isDue = $nextDue->isPast();
                                        @endphp
                                        <span class="{{ $isDue ? 'text-danger fw-bold' : '' }}">
                                            {{ $nextDue->format('d M Y') }}
                                        </span>
                                        @if($isDue)
                                            <span class="badge bg-danger">Due</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td style="width: 80px;">
                                    @if($maintenance->maintenance_img)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewMaintenanceImgModal{{ $maintenance->maintenanceID }}">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td>
                                    @if($maintenance->block_start_date && $maintenance->block_end_date)
                                        {{ \Carbon\Carbon::parse($maintenance->block_start_date)->format('d M Y') }} - 
                                        {{ \Carbon\Carbon::parse($maintenance->block_end_date)->format('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($maintenance->accompanyVehicle)
                                        {{ $maintenance->accompanyVehicle->plate_number ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($maintenance->staffID)
                                        @php
                                            $staffUser = \App\Models\User::find($maintenance->staffID);
                                        @endphp
                                        {{ $staffUser->name ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.vehicles.maintenance.destroy', $maintenance->maintenanceID) }}" 
                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- View Maintenance Image Modal -->
                            @if($maintenance->maintenance_img)
                            <div class="modal fade" id="viewMaintenanceImgModal{{ $maintenance->maintenanceID }}" tabindex="-1">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Maintenance Image</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center" style="min-height: 500px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; padding: 20px;">
                                            <img src="{{ getFileUrl($maintenance->maintenance_img) }}" 
                                                 alt="Maintenance Image" 
                                                 id="maintenanceImage{{ $maintenance->maintenanceID }}"
                                                 class="img-fluid maintenance-image-zoom" 
                                                 style="max-height: 75vh; max-width: 100%; width: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
                                                 onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" id="zoomBtn{{ $maintenance->maintenanceID }}">
                                                <i class="bi bi-zoom-in"></i> Zoom
                                            </button>
                                            <a href="{{ getFileUrl($maintenance->maintenance_img) }}" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No maintenance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Maintenance Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.vehicles.maintenance.store', $vehicle->vehicleID) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Service Date <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" value="{{ old('service_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}" required>
                            <small class="text-muted">e.g., Oil Change, Tire Replacement, Battery, General Service</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mileage</label>
                            <input type="number" name="mileage" class="form-control" value="{{ old('mileage') }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" step="0.01" class="form-control" value="{{ old('cost') }}" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Commission Amount (RM)</label>
                            <input type="number" name="commission_amount" step="0.01" class="form-control" value="{{ old('commission_amount', 0) }}" min="0">
                            <small class="text-muted">Commission for staff who served this maintenance</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control" value="{{ old('next_due_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Center</label>
                            <input type="text" name="service_center" class="form-control" value="{{ old('service_center') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Image</label>
                            <input type="file" name="maintenance_img" class="form-control" accept="image/*,.pdf">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block Start Date</label>
                            <input type="date" name="block_start_date" id="block_start_date" class="form-control" value="{{ old('block_start_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block End Date</label>
                            <input type="date" name="block_end_date" id="block_end_date" class="form-control" value="{{ old('block_end_date') }}">
                            <small class="text-muted">Vehicle will be unavailable between these dates</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accompany Vehicle</label>
                            <select name="accompany_vehicleID" id="accompany_vehicleID" class="form-select">
                                <option value="">Select Vehicle</option>
                            </select>
                            <small class="text-muted">Vehicle will be unavailable at start and end dates only</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Staff Handled</label>
                            <select name="staffID" class="form-select">
                                <option value="">Select Staff</option>
                                @foreach($staffUsers as $staff)
                                    <option value="{{ $staff->userID }}" {{ old('staffID') == $staff->userID ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update accompany vehicle dropdown based on block dates
    document.getElementById('block_start_date').addEventListener('change', updateAccompanyVehicles);
    document.getElementById('block_end_date').addEventListener('change', updateAccompanyVehicles);

    function updateAccompanyVehicles() {
        const startDate = document.getElementById('block_start_date').value;
        const endDate = document.getElementById('block_end_date').value;
        const dropdown = document.getElementById('accompany_vehicleID');
        
        if (!startDate || !endDate) {
            dropdown.innerHTML = '<option value="">Select Vehicle</option>';
            return;
        }

        // Fetch available vehicles for the date range
        fetch(`{{ route('admin.vehicles.available-vehicles') }}?start_date=${startDate}&end_date=${endDate}&exclude_vehicle={{ $vehicle->vehicleID }}`)
            .then(response => response.json())
            .then(data => {
                dropdown.innerHTML = '<option value="">Select Vehicle</option>';
                if (data.vehicles) {
                    data.vehicles.forEach(vehicle => {
                        const option = document.createElement('option');
                        option.value = vehicle.vehicleID;
                        option.textContent = vehicle.display || vehicle.plate_number;
                        dropdown.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching vehicles:', error);
            });
    }

    // Validate block end date is after start date
    document.getElementById('block_end_date').addEventListener('change', function() {
        const startDate = document.getElementById('block_start_date').value;
        const endDate = this.value;
        
        if (startDate && endDate && endDate < startDate) {
            alert('Block end date must be after or equal to block start date');
            this.value = '';
        }
    });

    // Image zoom functionality for maintenance images
    document.addEventListener('DOMContentLoaded', function() {
        // Handle all maintenance image modals
        const maintenanceModals = document.querySelectorAll('[id^="viewMaintenanceImgModal"]');
        
        maintenanceModals.forEach(function(modal) {
            const modalId = modal.id;
            const maintenanceId = modalId.replace('viewMaintenanceImgModal', '');
            const image = document.getElementById('maintenanceImage' + maintenanceId);
            const zoomBtn = document.getElementById('zoomBtn' + maintenanceId);
            
            if (image && zoomBtn) {
                let isZoomed = false;
                
                // Toggle zoom on image click
                image.addEventListener('click', function() {
                    toggleZoom();
                });
                
                // Toggle zoom on button click
                zoomBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleZoom();
                });
                
                function toggleZoom() {
                    isZoomed = !isZoomed;
                    if (isZoomed) {
                        image.classList.add('zoomed');
                        zoomBtn.innerHTML = '<i class="bi bi-zoom-out"></i> Zoom Out';
                    } else {
                        image.classList.remove('zoomed');
                        zoomBtn.innerHTML = '<i class="bi bi-zoom-in"></i> Zoom';
                    }
                }
                
                // Reset zoom when modal is closed
                modal.addEventListener('hidden.bs.modal', function() {
                    isZoomed = false;
                    image.classList.remove('zoomed');
                    zoomBtn.innerHTML = '<i class="bi bi-zoom-in"></i> Zoom';
                });
            }
        });
    });
</script>
@endpush
@endsection

