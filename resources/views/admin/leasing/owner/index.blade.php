@extends('layouts.admin')

@section('title', 'Owner Leasing')

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Owner Leasing" 
        description="Manage owner leasing information"
        :stats="[
            ['label' => 'Total Owners', 'value' => $totalOwners, 'icon' => 'bi-building'],
            ['label' => 'Active Owners', 'value' => $activeOwners, 'icon' => 'bi-check-circle'],
            ['label' => 'Total Cars', 'value' => $totalCars, 'icon' => 'bi-car-front']
        ]"
        :date="$today"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.leasing.owner.create') }}" class="btn btn-light text-danger pill-btn">
                <i class="bi bi-plus-circle me-1"></i> Add New Owner
            </a>
        </x-slot>
    </x-admin-page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                                <th>Leasing Due Date</th>
                                <th>Leasing Price</th>
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
                                    <td>{{ $owner->leasing_due_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td>RM {{ number_format($owner->leasing_price ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $owner->isActive ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $owner->isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.leasing.owner.show', $owner) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
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








