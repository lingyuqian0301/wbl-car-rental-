@extends('layouts.admin')

@section('title', 'Cars')

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
    >
        <x-slot name="actions">
            <a href="#" class="btn btn-light text-danger pill-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </a>
        </x-slot>
    </x-admin-page-header>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-10">
                    <label class="form-label small">Search</label>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           class="form-control form-control-sm"
                           placeholder="Search by brand / model / plate">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-danger w-100" type="submit">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Cars List ({{ $cars->total() }})</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Vehicle ID</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Plate Number</th>
                        <th>Seating</th>
                        <th>Transmission</th>
                        <th>Car Type</th>
                        <th>Rental Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($cars as $car)
                        <tr>
                            <td>{{ $car->vehicleID }}</td>
                            <td>{{ $car->vehicle_brand ?? 'N/A' }}</td>
                            <td>{{ $car->vehicle_model ?? 'N/A' }}</td>
                            <td>{{ $car->plate_number ?? 'N/A' }}</td>
                            <td>{{ $car->seating_capacity ?? 'N/A' }}</td>
                            <td>{{ $car->transmission ?? 'N/A' }}</td>
                            <td>{{ $car->car_type ?? 'N/A' }}</td>
                            <td>RM {{ number_format($car->rental_price ?? 0, 2) }}</td>
                            <td>
                                <span class="badge {{ $car->availability_status === 'available' ? 'bg-success' : ($car->availability_status === 'rented' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($car->availability_status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vehicles.show', $car->vehicleID) }}" class="btn btn-sm btn-outline-danger">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No cars found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $cars->withQueryString()->links() }}
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

