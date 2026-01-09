@if(!isset($showHeader) || $showHeader)
    <x-admin-page-header 
        title="Vehicle Management" 
        description="Manage all vehicles"
        :stats="[
            ['label' => 'Total Vehicles', 'value' => $totalVehicles, 'icon' => 'bi-car-front'],
            ['label' => 'Cars', 'value' => $totalCars, 'icon' => 'bi-car-front-fill'],
            ['label' => 'Motorcycles', 'value' => $totalMotors, 'icon' => 'bi-bicycle']
        ]"
        :date="$today"
    />
@endif

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
        <button type="button" class="btn btn-sm btn-light text-danger" data-bs-toggle="modal" data-bs-target="#createVehicleModal">
            <i class="bi bi-plus-circle me-1"></i> Create
        </button>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('admin.vehicles.export-all-pdf', request()->query()) }}">
                    <i class="bi bi-file-pdf me-2"></i> Export PDF
                </a></li>
                <li><a class="dropdown-item" href="{{ route('admin.vehicles.export-all-excel', request()->query()) }}">
                    <i class="bi bi-file-excel me-2"></i> Export Excel
                </a></li>
            </ul>
        </div>
        <button class="btn btn-sm btn-light text-danger" onclick="deleteSelected()">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.vehicles.others', ['tab' => 'vehicle']) }}" class="row g-3">
            <input type="hidden" name="tab" value="vehicle">
            
            <!-- Search -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       class="form-control form-control-sm" 
                       placeholder="Plate No">
            </div>
            
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Type</label>
                <select name="filter_type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="car" {{ ($filterType ?? '') === 'car' ? 'selected' : '' }}>Car</option>
                    <option value="motor" {{ ($filterType ?? '') === 'motor' ? 'selected' : '' }}>Motor</option>
                    <option value="other" {{ ($filterType ?? '') === 'other' ? 'selected' : '' }}>Other</option>
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
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-danger w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Vehicle List -->
<div class="card">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vehicles</h5>
        <span class="badge bg-light text-dark">{{ $vehicles->total() }} total</span>
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
                    <th>Plate No</th>
                    <th>Type</th>
                    <th>Created Date</th>
                    <th>Manufacturing Year</th>
                    <th>Engine Capacity</th>
                    <th>Rental Price</th>
                    <th>Is Active</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($vehicles as $vehicle)
                    @php
                        $vehicleType = 'Other';
                        if ($vehicle->car) {
                            $vehicleType = 'Car';
                        } elseif ($vehicle->motorcycle) {
                            $vehicleType = 'Motorcycle';
                        }
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="vehicle-checkbox" value="{{ $vehicle->vehicleID }}">
                        </td>
                        <td><strong>#{{ $vehicle->vehicleID }}</strong></td>
                        <td>{{ $vehicle->vehicle_brand ?? 'N/A' }}</td>
                        <td>{{ $vehicle->vehicle_model ?? 'N/A' }}</td>
                        <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $vehicleType === 'Car' ? 'bg-primary' : ($vehicleType === 'Motorcycle' ? 'bg-info' : 'bg-secondary') }}">
                                {{ $vehicleType }}
                            </span>
                        </td>
                        <td>{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $vehicle->manufacturing_year ?? 'N/A' }}</td>
                        <td>{{ $vehicle->engineCapacity ? number_format($vehicle->engineCapacity, 2) . 'L' : 'N/A' }}</td>
                        <td>RM {{ number_format($vehicle->rental_price ?? 0, 2) }}</td>
                        <td>
                            <span class="badge {{ ($vehicle->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                {{ ($vehicle->isActive ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($vehicleType === 'Car')
                                    <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-primary" title="View Vehicle">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.vehicles.cars.edit', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-secondary" title="Edit Vehicle">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteVehicle({{ $vehicle->vehicleID }}, '{{ $vehicle->plate_number }}', 'car')" 
                                            class="btn btn-outline-danger" title="Delete Vehicle">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                @elseif($vehicleType === 'Motorcycle')
                                    <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-primary" title="View Vehicle">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.vehicles.motorcycles.edit', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-secondary" title="Edit Vehicle">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteVehicle({{ $vehicle->vehicleID }}, '{{ $vehicle->plate_number }}', 'motorcycle')" 
                                            class="btn btn-outline-danger" title="Delete Vehicle">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                @else
                                    <a href="{{ route('admin.vehicles.show', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-primary" title="View Vehicle">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.vehicles.others.edit', $vehicle->vehicleID) }}" 
                                       class="btn btn-outline-secondary" title="Edit Vehicle">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button onclick="deleteVehicle({{ $vehicle->vehicleID }}, '{{ $vehicle->plate_number }}', 'other')" 
                                            class="btn btn-outline-danger" title="Delete Vehicle">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No vehicles found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($vehicles->hasPages())
        <div class="card-footer">
            {{ $vehicles->links() }}
        </div>
    @endif
</div>

<!-- Create Vehicle Modal -->
<div class="modal fade" id="createVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Vehicle Type <span class="text-danger">*</span></label>
                    <select id="vehicleTypeSelect" class="form-select" onchange="handleVehicleTypeChange()">
                        <option value="">-- Choose Vehicle Type --</option>
                        <option value="car">Car</option>
                        <option value="motorcycle">Motorcycle</option>
                        <option value="other">Other</option>
                    </select>
                    <small class="text-muted">Choose the type of vehicle you want to register</small>
                </div>
                <div id="vehicleTypeActions" class="mt-3" style="display: none;">
                    <a id="createCarBtn" href="{{ route('admin.vehicles.cars.create') }}" class="btn btn-danger w-100" style="display: none;">
                        <i class="bi bi-car-front me-1"></i> Create New Car
                    </a>
                    <a id="createMotorcycleBtn" href="{{ route('admin.vehicles.motorcycles.create') }}" class="btn btn-danger w-100" style="display: none;">
                        <i class="bi bi-bicycle me-1"></i> Create New Motorcycle
                    </a>
                    <a id="createOtherBtn" href="{{ route('admin.vehicles.others.create') }}" class="btn btn-danger w-100" style="display: none;">
                        <i class="bi bi-plus-circle me-1"></i> Create New Vehicle (Other)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Base URLs for delete routes
    const deleteRoutes = {
        car: '{{ url("/admin/vehicles/cars") }}',
        motorcycle: '{{ url("/admin/vehicles/motorcycles") }}',
        other: '{{ url("/admin/vehicles") }}'
    };

    // Select All Checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.vehicle-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Delete Selected
    function deleteSelected() {
        let selected = Array.from(document.querySelectorAll('.vehicle-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one vehicle to delete.');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${selected.length} vehicle(s)?\n\nNote: Vehicles with existing bookings cannot be deleted.`)) {
            return;
        }
        
        // Delete each selected vehicle
        let deletePromises = selected.map(vehicleId => {
            let url = `${deleteRoutes.other}/${vehicleId}`;
            return fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to delete vehicle');
                    });
                }
                return response.json();
            });
        });
        
        Promise.all(deletePromises)
            .then(() => {
                alert('Selected vehicles deleted successfully.');
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
                location.reload();
            });
    }

    // Delete Single Vehicle
    function deleteVehicle(vehicleId, plateNumber, vehicleType) {
        let typeText = vehicleType === 'car' ? 'car' : (vehicleType === 'motorcycle' ? 'motorcycle' : 'vehicle');
        if (!confirm(`Are you sure you want to delete ${typeText} with plate number "${plateNumber}"?\n\nNote: Vehicles with existing bookings cannot be deleted.`)) {
            return;
        }
        
        // Use the correct route based on vehicle type
        let url;
        if (vehicleType === 'car') {
            url = `${deleteRoutes.car}/${vehicleId}`;
        } else if (vehicleType === 'motorcycle') {
            url = `${deleteRoutes.motorcycle}/${vehicleId}`;
        } else {
            url = `${deleteRoutes.other}/${vehicleId}`;
        }
        
        fetch(url, {
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
                    throw new Error(data.message || 'Failed to delete vehicle');
                });
            }
            return response.json();
        })
        .then(data => {
            alert(`${typeText.charAt(0).toUpperCase() + typeText.slice(1)} deleted successfully.`);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }

    // Handle Vehicle Type Selection in Modal
    function handleVehicleTypeChange() {
        const select = document.getElementById('vehicleTypeSelect');
        const actionsDiv = document.getElementById('vehicleTypeActions');
        const carBtn = document.getElementById('createCarBtn');
        const motorcycleBtn = document.getElementById('createMotorcycleBtn');
        const otherBtn = document.getElementById('createOtherBtn');
        
        // Hide all buttons first
        carBtn.style.display = 'none';
        motorcycleBtn.style.display = 'none';
        otherBtn.style.display = 'none';
        actionsDiv.style.display = 'none';
        
        // Show the appropriate button based on selection
        if (select.value === 'car') {
            actionsDiv.style.display = 'block';
            carBtn.style.display = 'block';
        } else if (select.value === 'motorcycle') {
            actionsDiv.style.display = 'block';
            motorcycleBtn.style.display = 'block';
        } else if (select.value === 'other') {
            actionsDiv.style.display = 'block';
            otherBtn.style.display = 'block';
        }
    }
</script>
@endpush

