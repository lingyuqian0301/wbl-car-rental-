@extends('layouts.admin')

@section('title', 'Cars')

@push('styles')
<style>
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.35rem 0.6rem;
        font-size: 1rem;
        font-weight: 600;
    }
    .status-btn {
        min-width: 100px;
        font-weight: 500;
    }
    .status-available {
        background-color: #28a745;
        color: white;
        border-color: #28a745;
    }
    .status-available:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
    }
    .status-rented {
        background-color: #ffc107;
        color: #000;
        border-color: #ffc107;
    }
    .status-rented:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #000;
    }
    .status-maintenance {
        background-color: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }
    .status-maintenance:hover {
        background-color: #138496;
        border-color: #117a8b;
        color: white;
    }
    .status-unavailable {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    .status-unavailable:hover {
        background-color: #5a6268;
        border-color: #545b62;
        color: white;
    }
    .status-unknown {
        background-color: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    .status-unknown:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Cars Management" 
        description="Manage all car vehicles"
        :stats="[
            ['label' => 'Total Cars', 'value' => $totalCars, 'icon' => 'bi-car-front'],
            ['label' => 'Available', 'value' => $totalAvailable, 'icon' => 'bi-check-circle'],
            ['label' => 'Rented', 'value' => $totalRented, 'icon' => 'bi-calendar-check']
        ]"
        :date="$today"
    />

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

    <!-- Action Buttons - Right Top Corner -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.vehicles.cars.create') }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-plus-circle me-1"></i> Create New
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.cars.export-pdf', request()->query()) }}">
                        <i class="bi bi-file-pdf me-2"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.cars.export-excel', request()->query()) }}">
                        <i class="bi bi-file-excel me-2"></i> Export Excel
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-light text-danger" onclick="deleteSelected()">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <!-- Search, Sort and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vehicles.cars') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="form-control form-control-sm" 
                           placeholder="Brand, Model, Plate Number">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="vehicle_id_asc" {{ $sortBy === 'vehicle_id_asc' ? 'selected' : '' }}>Asc Vehicle ID</option>
                        <option value="highest_rented" {{ $sortBy === 'highest_rented' ? 'selected' : '' }}>Highest Rented</option>
                        <option value="highest_rental_price" {{ $sortBy === 'highest_rental_price' ? 'selected' : '' }}>Highest Rental Price</option>
                        <option value="plate_no_asc" {{ $sortBy === 'plate_no_asc' ? 'selected' : '' }}>Asc Plate No</option>
                    </select>
                </div>
                
                <!-- Filters -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="filter_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ $filterStatus === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Is Active</label>
                    <select name="filter_isactive" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($filterIsActive ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($filterIsActive ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.vehicles.cars') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Cars List -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cars</h5>
            <span class="badge bg-light text-dark">{{ $cars->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="30">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>Vehicle ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Plate Number</th>
                        <th>Seating</th>
                        <th>Transmission</th>
                        <th>Car Type</th>
                        <th>Rental Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($cars as $car)
                        <tr>
                            <td>
                                <input type="checkbox" class="car-checkbox" value="{{ $car->vehicleID }}">
                            </td>
                            <td><strong>#{{ $car->vehicleID }}</strong></td>
                            <td>{{ $car->vehicle_brand ?? 'N/A' }}</td>
                            <td>{{ $car->vehicle_model ?? 'N/A' }}</td>
                            <td>{{ $car->plate_number ?? 'N/A' }}</td>
                            <td>{{ $car->seating_capacity ?? 'N/A' }}</td>
                            <td>{{ $car->transmission ?? 'N/A' }}</td>
                            <td>{{ $car->car_type ?? 'N/A' }}</td>
                            <td>RM {{ number_format($car->rental_price ?? 0, 2) }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm status-btn status-{{ $car->availability_status ?? 'unknown' }}"
                                        data-vehicle-id="{{ $car->vehicleID }}"
                                        data-current-status="{{ $car->availability_status ?? 'unknown' }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#statusChangeModal"
                                        onclick="openStatusModal(this)">
                                    {{ ucfirst($car->availability_status ?? 'Unknown') }}
                                </button>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.vehicles.show', $car->vehicleID) }}" 
                                       class="btn btn-sm btn-outline-info" title="View Car Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.vehicles.cars.edit', $car->vehicleID) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Car">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteCar({{ $car->vehicleID }}, '{{ $car->plate_number }}')" 
                                            class="btn btn-sm btn-outline-danger" title="Delete Car">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No cars found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($cars->hasPages())
            <div class="card-footer">
                {{ $cars->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Status Update Confirmation Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Current Status: <strong id="currentStatusText"></strong></p>
                <label class="form-label">Select New Status:</label>
                <select class="form-select" id="newStatusSelect">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmStatusChange">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Select All Checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.car-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Status Change with Confirmation
    let pendingStatusChange = null;

    function openStatusModal(button) {
        let vehicleId = button.dataset.vehicleId;
        let currentStatus = button.dataset.currentStatus;
        
        document.getElementById('currentStatusText').textContent = currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
        document.getElementById('newStatusSelect').value = currentStatus;
        
        pendingStatusChange = {
            button: button,
            vehicleId: vehicleId,
            oldStatus: currentStatus
        };
    }

    document.getElementById('confirmStatusChange')?.addEventListener('click', function() {
        if (!pendingStatusChange) return;
        
        let newStatus = document.getElementById('newStatusSelect').value;
        
        if (pendingStatusChange.oldStatus === newStatus) {
            bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
            return;
        }
        
        fetch(`/admin/vehicles/${pendingStatusChange.vehicleId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                availability_status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button
                let btn = pendingStatusChange.button;
                btn.dataset.currentStatus = newStatus;
                btn.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                btn.className = `btn btn-sm status-btn status-${newStatus}`;
                
                bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
            } else {
                alert('Failed to update status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status.');
        });
        
        pendingStatusChange = null;
    });

    // Delete Selected
    function deleteSelected() {
        let selected = Array.from(document.querySelectorAll('.car-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one car to delete.');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selected.length} car(s)?\n\nNote: Cars with existing bookings cannot be deleted.`)) {
            return;
        }
        
        // Delete each selected car
        let deletePromises = selected.map(vehicleId => {
            return fetch(`{{ url('/admin/vehicles/cars') }}/${vehicleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to delete car');
                    });
                }
                return response.json();
            });
        });
        
        Promise.all(deletePromises)
            .then(() => {
                alert('Selected cars deleted successfully.');
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                location.reload();
            });
    }

    // Delete Single Car
    function deleteCar(vehicleId, plateNumber) {
        if (!confirm(`Are you sure you want to delete car with plate number "${plateNumber}"?\n\nNote: Cars with existing bookings cannot be deleted.`)) {
            return;
        }
        
        fetch(`{{ url('/admin/vehicles/cars') }}/${vehicleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to delete car');
                });
            }
            return response.json();
        })
        .then(data => {
            alert('Car deleted successfully.');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }
</script>
@endpush
@endsection
