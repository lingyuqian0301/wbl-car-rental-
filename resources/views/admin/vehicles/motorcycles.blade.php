@extends('layouts.admin')

@section('title', 'Motorcycles')

@push('styles')
<style>
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
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
        title="Motorcycles Management" 
        description="Manage all motorcycle vehicles"
        :stats="[
            ['label' => 'Total Motorcycles', 'value' => $totalMotorcycles, 'icon' => 'bi-bicycle'],
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
            <a href="{{ route('admin.vehicles.motorcycles.create') }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-plus-circle me-1"></i> Create New
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.motorcycles.export-pdf', request()->query()) }}">
                        <i class="bi bi-file-pdf me-2"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.vehicles.motorcycles.export-excel', request()->query()) }}">
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
            <form method="GET" action="{{ route('admin.vehicles.motorcycles') }}" class="row g-3">
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
                        <option value="vehicle_id_asc" {{ $sortBy === 'vehicle_id_asc' ? 'selected' : '' }}>Vehicle ID (Asc)</option>
                        <option value="vehicle_id_desc" {{ $sortBy === 'vehicle_id_desc' ? 'selected' : '' }}>Vehicle ID (Desc)</option>
                        <option value="brand_asc" {{ $sortBy === 'brand_asc' ? 'selected' : '' }}>Brand (A-Z)</option>
                        <option value="brand_desc" {{ $sortBy === 'brand_desc' ? 'selected' : '' }}>Brand (Z-A)</option>
                        <option value="model_asc" {{ $sortBy === 'model_asc' ? 'selected' : '' }}>Model (A-Z)</option>
                        <option value="model_desc" {{ $sortBy === 'model_desc' ? 'selected' : '' }}>Model (Z-A)</option>
                    </select>
                </div>
                
                <!-- Filters -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Brand</label>
                    <select name="filter_brand" class="form-select form-select-sm">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" {{ $filterBrand === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Model</label>
                    <select name="filter_model" class="form-select form-select-sm">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                            <option value="{{ $model }}" {{ $filterModel === $model ? 'selected' : '' }}>{{ $model }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Motor Type</label>
                    <select name="filter_motor_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach($motorTypes as $motorType)
                            <option value="{{ $motorType }}" {{ $filterMotorType === $motorType ? 'selected' : '' }}>{{ $motorType }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="filter_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ $filterStatus === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.vehicles.motorcycles') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Motorcycles List -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Motorcycles</h5>
            <span class="badge bg-light text-dark">{{ $motorcycles->total() }} total</span>
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
                        <th>Motor Type</th>
                        <th>Rental Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($motorcycles as $motorcycle)
                        <tr>
                            <td>
                                <input type="checkbox" class="motorcycle-checkbox" value="{{ $motorcycle->vehicleID }}">
                            </td>
                            <td><strong>#{{ $motorcycle->vehicleID }}</strong></td>
                            <td>{{ $motorcycle->vehicle_brand ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->vehicle_model ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->plate_number ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->motor_type ?? 'N/A' }}</td>
                            <td>RM {{ number_format($motorcycle->rental_price ?? 0, 2) }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm status-btn status-{{ $motorcycle->availability_status ?? 'unknown' }}"
                                        data-vehicle-id="{{ $motorcycle->vehicleID }}"
                                        data-current-status="{{ $motorcycle->availability_status ?? 'unknown' }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#statusChangeModal"
                                        onclick="openStatusModal(this)">
                                    {{ ucfirst($motorcycle->availability_status ?? 'Unknown') }}
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('admin.vehicles.motorcycles.edit', $motorcycle->vehicleID) }}" 
                                   class="btn btn-sm btn-outline-primary" title="Edit Motorcycle">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No motorcycles found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motorcycles->hasPages())
            <div class="card-footer">
                {{ $motorcycles->withQueryString()->links() }}
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
        document.querySelectorAll('.motorcycle-checkbox').forEach(cb => {
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
        let selected = Array.from(document.querySelectorAll('.motorcycle-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one motorcycle to delete.');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selected.length} motorcycle(s)?\n\nNote: Motorcycles with existing bookings cannot be deleted.`)) {
            return;
        }
        
        // Delete each selected motorcycle
        selected.forEach(vehicleId => {
            fetch(`/admin/vehicles/motorcycles/${vehicleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        });
        
        setTimeout(() => location.reload(), 500);
    }
</script>
@endpush
@endsection
