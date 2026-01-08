@extends('layouts.admin')

@section('title', 'Owner Details')

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-building"></i> Owner Details</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.leasing.owner.edit', $owner) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.leasing.index', ['tab' => 'owner']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
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

    <!-- Owner Information -->
    <div class="card mb-3">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-5">Owner ID:</dt>
                        <dd class="col-7">#{{ $owner->ownerID }}</dd>
                        
                        <dt class="col-5">Owner Name:</dt>
                        <dd class="col-7">{{ $owner->personDetails->fullname ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">IC No:</dt>
                        <dd class="col-7">{{ $owner->ic_no ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">Contact Number:</dt>
                        <dd class="col-7">{{ $owner->contact_number ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">Email:</dt>
                        <dd class="col-7">{{ $owner->email ?? 'N/A' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-5">Bank Name:</dt>
                        <dd class="col-7">{{ $owner->bankname ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">Bank Account Number:</dt>
                        <dd class="col-7">{{ $owner->bank_acc_number ?? 'N/A' }}</dd>
                        
                        <dt class="col-5">Registration Date:</dt>
                        <dd class="col-7">{{ $owner->registration_date ? \Carbon\Carbon::parse($owner->registration_date)->format('d M Y') : 'N/A' }}</dd>
                        
                        <dt class="col-5">Leasing Price:</dt>
                        <dd class="col-7">RM {{ number_format($owner->leasing_price ?? 0, 2) }}</dd>
                        
                        <dt class="col-5">Leasing Due Date:</dt>
                        <dd class="col-7">{{ $owner->leasing_due_date ? \Carbon\Carbon::parse($owner->leasing_due_date)->format('d M Y') : 'N/A' }}</dd>
                        
                        <dt class="col-5">Status:</dt>
                        <dd class="col-7">
                            <span class="badge {{ ($owner->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                {{ ($owner->isActive ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles List -->
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicles ({{ $vehicles->count() }})</h5>
        </div>
        <div class="card-body">
            @if($vehicles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle ID</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Plate Number</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                                <tr>
                                    <td>#{{ $vehicle->vehicleID }}</td>
                                    <td>{{ $vehicle->vehicle_brand ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->vehicle_model ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                                    <td>{{ $vehicle->vehicleType ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ ($vehicle->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($vehicle->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-car-front" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No vehicles associated with this owner.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection




