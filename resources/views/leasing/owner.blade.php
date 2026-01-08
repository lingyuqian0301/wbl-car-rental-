@extends('layouts.admin')

@section('title', 'Owner')

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Owner" 
        description="Manage owner information"
        :stats="[
            ['label' => 'Total Owners', 'value' => $totalOwners, 'icon' => 'bi-building'],
            ['label' => 'Active Owners', 'value' => $activeOwners, 'icon' => 'bi-check-circle'],
            ['label' => 'Total Cars', 'value' => $totalCars, 'icon' => 'bi-car-front']
        ]"
        :date="$today"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.leasing.owner.create') }}" class="btn btn-light text-danger pill-btn">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>
        </x-slot>
    </x-admin-page-header>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.leasing.owner') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $request->get('search') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Owner name, Vehicle plate no, Contact no">
                </div>
                
                <!-- Filter: isActive -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="filter_isactive" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ $request->get('filter_isactive') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $request->get('filter_isactive') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($owners->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Owner ID</th>
                                <th>IC No</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($owners as $owner)
                                <tr>
                                    <td>#{{ $owner->ownerID }}</td>
                                    <td>{{ $owner->ic_no ?? 'N/A' }}</td>
                                    <td>{{ $owner->contact_number ?? 'N/A' }}</td>
                                    <td>{{ $owner->email ?? 'N/A' }}</td>
                                    <td>{{ $owner->registration_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $owner->isActive ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $owner->isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @php
                                                // Find first vehicle owned by this owner
                                                $ownerVehicle = \App\Models\Vehicle::where('ownerID', $owner->ownerID)->first();
                                            @endphp
                                            @if($ownerVehicle)
                                                <a href="{{ route('admin.vehicles.show', $ownerVehicle->vehicleID) }}?tab=owner-info" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            @else
                                                <a href="{{ route('admin.leasing.owner.show', $owner) }}" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.leasing.owner.edit', $owner) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.leasing.owner.destroy', $owner) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this owner?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $owners->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No owners found. <a href="{{ route('admin.leasing.owner.create') }}">Create your first owner</a>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

