@extends('layouts.admin')

@section('title', 'Motorcycles')

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
            <span class="fw-semibold">Motorcycles List ({{ $motorcycles->total() }})</span>
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
                        <th>Motor Type</th>
                        <th>Rental Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($motorcycles as $motorcycle)
                        <tr>
                            <td>{{ $motorcycle->vehicleID }}</td>
                            <td>{{ $motorcycle->vehicle_brand ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->vehicle_model ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->plate_number ?? 'N/A' }}</td>
                            <td>{{ $motorcycle->motor_type ?? 'N/A' }}</td>
                            <td>RM {{ number_format($motorcycle->rental_price ?? 0, 2) }}</td>
                            <td>
                                <span class="badge {{ $motorcycle->availability_status === 'available' ? 'bg-success' : ($motorcycle->availability_status === 'rented' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($motorcycle->availability_status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vehicles.show', $motorcycle->vehicleID) }}" class="btn btn-sm btn-outline-danger">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No motorcycles found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $motorcycles->withQueryString()->links() }}
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

