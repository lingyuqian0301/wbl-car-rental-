@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'admin' ? 'active' : '' }}" 
               href="{{ route('admin.settings.index', ['tab' => 'admin']) }}">
                <i class="bi bi-shield-check me-1"></i> Admin
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'staff' ? 'active' : '' }}" 
               href="{{ route('admin.settings.index', ['tab' => 'staff']) }}">
                <i class="bi bi-people me-1"></i> Staff
            </a>
        </li>
    </ul>

    <!-- Admin Tab Content -->
    @if($activeTab === 'admin')
        <x-admin-page-header 
            title="Admin Management" 
            description="Manage admin accounts"
            :stats="[
                ['label' => 'Total Admins', 'value' => $totalAdmins ?? 0, 'icon' => 'bi-shield-check'],
                ['label' => 'Active Admins', 'value' => $activeAdmins ?? 0, 'icon' => 'bi-check-circle']
            ]"
            :date="$today"
        >
            <x-slot name="actions">
                <button type="button" class="btn btn-light text-danger pill-btn" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                    <i class="bi bi-plus-circle me-1"></i> Create New
                </button>
            </x-slot>
        </x-admin-page-header>

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

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Admin Username</th>
                                <th>Last Login</th>
                                <th>Date Registered</th>
                                <th>Date of Birth</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                                @php
                                    $user = $admin->user;
                                @endphp
                                <tr>
                                    <td>#{{ $admin->adminID }}</td>
                                    <td>
                                        <div><strong>{{ $user->username ?? 'N/A' }}</strong></div>
                                        <div class="text-muted small">
                                            <div><i class="bi bi-envelope me-1"></i>{{ $user->email ?? 'N/A' }}</div>
                                            <div><i class="bi bi-phone me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                                            <div><i class="bi bi-person me-1"></i>{{ $user->name ?? 'N/A' }}</div>
                                            <div><i class="bi bi-card-text me-1"></i>{{ $admin->ic_no ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $user->lastLogin ? $user->lastLogin->format('d M Y H:i') : 'Never' }}</td>
                                    <td>{{ $user->dateRegistered ? $user->dateRegistered->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <div>{{ $user->DOB ? $user->DOB->format('d M Y') : 'N/A' }}</div>
                                        @if($user->age)
                                            <div class="text-muted small">Age: {{ $user->age }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ ($user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editAdminModal{{ $admin->adminID }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No admins found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Staff Tab Content -->
    @if($activeTab === 'staff')
        <x-admin-page-header 
            title="Staff Management" 
            description="Manage staff accounts"
            :stats="[
                ['label' => 'Total Staff', 'value' => $totalStaffs ?? 0, 'icon' => 'bi-people'],
                ['label' => 'Active Staff', 'value' => $activeStaffs ?? 0, 'icon' => 'bi-check-circle']
            ]"
            :date="$today"
        >
            <x-slot name="actions">
                <button type="button" class="btn btn-light text-danger pill-btn" data-bs-toggle="modal" data-bs-target="#createStaffModal">
                    <i class="bi bi-plus-circle me-1"></i> Create New
                </button>
            </x-slot>
        </x-admin-page-header>

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

        <!-- Filters for Staff -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.settings.index', ['tab' => 'staff']) }}" class="row g-3">
                    <input type="hidden" name="tab" value="staff">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Staff Type</label>
                        <select name="filter_type" class="form-select form-select-sm">
                            <option value="all" {{ ($filterType ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="staffit" {{ ($filterType ?? '') === 'staffit' ? 'selected' : '' }}>Staff IT</option>
                            <option value="runner" {{ ($filterType ?? '') === 'runner' ? 'selected' : '' }}>Runner</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="filter_active" class="form-select form-select-sm">
                            <option value="all" {{ ($filterActive ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="active" {{ ($filterActive ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($filterActive ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-funnel"></i> Apply Filters
                        </button>
                        @if(($filterType ?? 'all') !== 'all' || ($filterActive ?? 'all') !== 'all')
                            <a href="{{ route('admin.settings.index', ['tab' => 'staff']) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Staff ID</th>
                                <th>Staff Username</th>
                                <th>Last Login</th>
                                <th>Date Registered</th>
                                <th>Date of Birth</th>
                                <th>Staff Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffs as $staff)
                                @php
                                    $user = $staff->user;
                                    $staffType = $staff->staffIt ? 'Staff IT' : ($staff->runner ? 'Runner' : 'N/A');
                                @endphp
                                <tr>
                                    <td>#{{ $staff->staffID }}</td>
                                    <td>
                                        <div><strong>{{ $user->username ?? 'N/A' }}</strong></div>
                                        <div class="text-muted small">
                                            <div><i class="bi bi-envelope me-1"></i>{{ $user->email ?? 'N/A' }}</div>
                                            <div><i class="bi bi-phone me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                                            <div><i class="bi bi-person me-1"></i>{{ $user->name ?? 'N/A' }}</div>
                                            <div><i class="bi bi-card-text me-1"></i>{{ $staff->ic_no ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $user->lastLogin ? $user->lastLogin->format('d M Y H:i') : 'Never' }}</td>
                                    <td>{{ $user->dateRegistered ? $user->dateRegistered->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                        <div>{{ $user->DOB ? $user->DOB->format('d M Y') : 'N/A' }}</div>
                                        @if($user->age)
                                            <div class="text-muted small">Age: {{ $user->age }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $staffType }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ ($user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.settings.staff.show', $staff->staffID) }}" class="btn btn-outline-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $staff->staffID }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No staff found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Create Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.admin.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                            @error('phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="DOB" class="form-control" value="{{ old('DOB') }}" required>
                            @error('DOB')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IC Number <span class="text-danger">*</span></label>
                            <input type="text" name="ic_no" class="form-control" value="{{ old('ic_no') }}" required>
                            @error('ic_no')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Staff Modal -->
<div class="modal fade" id="createStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.staff.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                            @error('phone')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="DOB" class="form-control" value="{{ old('DOB') }}" required>
                            @error('DOB')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IC Number <span class="text-danger">*</span></label>
                            <input type="text" name="ic_no" class="form-control" value="{{ old('ic_no') }}" required>
                            @error('ic_no')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                            <select name="staff_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="staffit" {{ old('staff_type') === 'staffit' ? 'selected' : '' }}>Staff IT</option>
                                <option value="runner" {{ old('staff_type') === 'runner' ? 'selected' : '' }}>Runner</option>
                            </select>
                            @error('staff_type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modals -->
@if($activeTab === 'admin')
    @foreach($admins as $admin)
        @php $user = $admin->user; @endphp
        <div class="modal fade" id="editAdminModal{{ $admin->adminID }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.admin.update', $admin->adminID) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                                    @error('username')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="DOB" class="form-control" value="{{ old('DOB', $user->DOB?->format('Y-m-d')) }}" required>
                                    @error('DOB')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IC Number <span class="text-danger">*</span></label>
                                    <input type="text" name="ic_no" class="form-control" value="{{ old('ic_no', $admin->ic_no) }}" required>
                                    @error('ic_no')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="isActive" value="1" id="isActive{{ $admin->adminID }}" {{ ($user->isActive ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isActive{{ $admin->adminID }}">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Update Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- Edit Staff Modals -->
@if($activeTab === 'staff')
    @foreach($staffs as $staff)
        @php 
            $user = $staff->user;
            $currentStaffType = $staff->staffIt ? 'staffit' : ($staff->runner ? 'runner' : '');
        @endphp
        <div class="modal fade" id="editStaffModal{{ $staff->staffID }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.staff.update', $staff->staffID) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                                    @error('username')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="DOB" class="form-control" value="{{ old('DOB', $user->DOB?->format('Y-m-d')) }}" required>
                                    @error('DOB')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IC Number <span class="text-danger">*</span></label>
                                    <input type="text" name="ic_no" class="form-control" value="{{ old('ic_no', $staff->ic_no) }}" required>
                                    @error('ic_no')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                                    <select name="staff_type" class="form-select" required>
                                        <option value="">Select Type</option>
                                        <option value="staffit" {{ old('staff_type', $currentStaffType) === 'staffit' ? 'selected' : '' }}>Staff IT</option>
                                        <option value="runner" {{ old('staff_type', $currentStaffType) === 'runner' ? 'selected' : '' }}>Runner</option>
                                    </select>
                                    @error('staff_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="isActive" value="1" id="isActive{{ $staff->staffID }}" {{ ($user->isActive ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isActive{{ $staff->staffID }}">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Update Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection

