@extends('layouts.admin')

@section('title', 'Staff Details')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .grouping-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .grouping-box-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--hasta-red-dark);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--hasta-rose);
    }
    .document-cell {
        min-height: 250px;
        transition: transform 0.2s;
        border: 1px solid #e5e7eb;
    }
    .document-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $staff->user->name ?? 'N/A' }}</h1>
            <div class="text-muted small">
                Staff ID: #{{ $staff->staffID }} · Status:
                <span class="badge {{ ($staff->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                    {{ ($staff->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.settings.index', ['tab' => 'staff']) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
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

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? 'staff-detail') === 'staff-detail' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#staff-detail" type="button" role="tab">
                <i class="bi bi-person-circle"></i> Staff Detail
            </button>
        </li>
        @if($staff->runner)
            <!-- Runner Task List Tab -->
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ ($activeTab ?? '') === 'task-list' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#task-list" type="button" role="tab">
                    <i class="bi bi-truck"></i> Task List
                </button>
            </li>
        @else
            <!-- StaffIT Tasks Handled Tab -->
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ ($activeTab ?? '') === 'tasks-handled' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#tasks-handled" type="button" role="tab">
                    <i class="bi bi-list-task"></i> Tasks Handled
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ ($activeTab ?? '') === 'commission' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#commission" type="button" role="tab">
                    <i class="bi bi-cash-coin"></i> Commission
                </button>
            </li>
        @endif
    </ul>

    <div class="tab-content">
        <!-- Runner Task List Tab -->
        @if($staff->runner)
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'task-list' ? 'show active' : '' }}" id="task-list" role="tabpanel">
            <!-- Header Box -->
            <x-admin-page-header 
                title="Runner Task List" 
                description="Tasks assigned to {{ $staff->user->name ?? 'Runner' }}"
                :stats="[
                    ['label' => 'Total Tasks', 'value' => $runnerTaskCount ?? 0, 'icon' => 'bi-list-check'],
                    ['label' => 'Total Commission', 'value' => 'RM ' . number_format($runnerTotalCommission ?? 0, 2), 'icon' => 'bi-cash-coin']
                ]"
            />

            <div class="card mt-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Task List</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.settings.staff.show', ['staff' => $staff->staffID]) }}" class="row g-3 mb-3">
                        <input type="hidden" name="tab" value="task-list">
                        <div class="col-md-3">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ ($filterMonth ?? date('m')) == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="{{ $filterYear ?? date('Y') }}" class="form-control form-control-sm" min="2020" max="{{ date('Y') + 1 }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">Filter</button>
                        </div>
                    </form>

                    <!-- Tasks Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Booking ID</th>
                                    <th>Task Type</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($runnerTasks ?? [] as $task)
                                    <tr>
                                        <td>{{ $task['num'] }}</td>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations.show', ['booking' => $task['booking_id']]) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $task['booking_id'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $task['task_type'] === 'Pickup' ? 'bg-success' : 'bg-info' }}">
                                                {{ $task['task_type'] }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($task['task_date'])->format('d M Y') }}<br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($task['task_date'])->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $task['location'] ?? 'N/A' }}</td>
                                        <td class="fw-semibold">RM {{ number_format($task['commission_amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No tasks found for the selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(($runnerTasks ?? collect())->count() > 0)
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="fw-semibold text-end">Total Tasks: {{ $runnerTaskCount ?? 0 }}</td>
                                        <td class="fw-bold text-danger">RM {{ number_format($runnerTotalCommission ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Staff Detail Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? 'staff-detail') === 'staff-detail' ? 'show active' : '' }}" id="staff-detail" role="tabpanel">
            <!-- Staff Info Card -->
            <div class="card mb-3">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Staff Info</h5>
                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editStaffModal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Staff ID:</dt>
                            <dd class="d-inline ms-2">{{ $staff->staffID ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Username:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->username ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Email:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->email ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Phone:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->phone ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Name:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->name ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Last Login:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->lastLogin ? \Carbon\Carbon::parse($staff->user->lastLogin)->format('d M Y H:i') : 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Date Registered:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->dateRegistered ? \Carbon\Carbon::parse($staff->user->dateRegistered)->format('d M Y') : 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Date of Birth:</dt>
                            <dd class="d-inline ms-2">{{ $staff->user->DOB ? \Carbon\Carbon::parse($staff->user->DOB)->format('d M Y') : 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Age:</dt>
                            <dd class="d-inline ms-2">
                                @if($staff->user->DOB)
                                    {{ \Carbon\Carbon::parse($staff->user->DOB)->age }} years
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Status:</dt>
                            <dd class="d-inline ms-2">
                                <span class="badge {{ ($staff->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ($staff->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">IC No:</dt>
                            <dd class="d-inline ms-2">{{ $staff->ic_no ?? 'N/A' }}</dd>
                        </div>

                        <div class="mb-2">
                            <dt class="d-inline fw-semibold">Staff Type:</dt>
                            <dd class="d-inline ms-2">
                                <span class="badge bg-info">
                                    {{ $staff->staffIt ? 'Staff IT' : ($staff->runner ? 'Runner' : 'N/A') }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Documentation Card -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- IC Image -->
                        <div class="col-md-6">
                            <div class="card document-cell h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                    <h6 class="fw-semibold">IC Document</h6>
                                    @php
                                        $icImg = $staff->ic_img ?? null;
                                    @endphp
                                    @if($icImg)
                                        <div class="mb-2">
                                            <img src="{{ getFileUrl($icImg) }}" 
                                                 alt="IC" 
                                                 class="img-fluid mb-2" 
                                                 style="max-height: 150px; border-radius: 6px;"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p class="text-muted small" style="display:none;">Image not found</p>
                                        </div>
                                        <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewIcModal">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadIcModal">
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        </div>
                                    @else
                                        <p class="small text-muted mb-2">No IC image uploaded</p>
                                        <button type="button" 
                                                class="btn btn-sm" 
                                                style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#uploadIcModal">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View IC Modal -->
        @if($staff->ic_img ?? false)
        <div class="modal fade" id="viewIcModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">IC Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center" style="min-height: 400px;">
                        <img src="{{ getFileUrl($staff->ic_img) }}" 
                             alt="IC Document" 
                             class="img-fluid" 
                             style="max-height: 70vh; width: auto; border-radius: 6px;"
                             onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                    </div>
                    <div class="modal-footer">
                        <a href="{{ getFileUrl($staff->ic_img) }}" 
                           target="_blank" 
                           class="btn btn-primary">
                            <i class="bi bi-download"></i> Open in New Tab
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Upload IC Modal -->
        <div class="modal fade" id="uploadIcModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload IC</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.staff.upload-ic', $staff->staffID) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">IC Image <span class="text-danger">*</span></label>
                                <input type="file" name="ic_img" class="form-control" accept="image/*,.pdf" required>
                                <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Upload IC</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Staff Modal -->
        <div class="modal fade" id="editStaffModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Staff</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.staff.update', $staff->staffID) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" value="{{ $staff->user->username ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $staff->user->name ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ $staff->user->email ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ $staff->user->phone ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="DOB" class="form-control" value="{{ $staff->user->DOB ? \Carbon\Carbon::parse($staff->user->DOB)->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IC No <span class="text-danger">*</span></label>
                                    <input type="text" name="ic_no" class="form-control" value="{{ $staff->ic_no ?? '' }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Staff Type <span class="text-danger">*</span></label>
                                    <select name="staff_type" class="form-select" required>
                                        <option value="staffit" {{ $staff->staffIt ? 'selected' : '' }}>Staff IT</option>
                                        <option value="runner" {{ $staff->runner ? 'selected' : '' }}>Runner</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" name="isActive" class="form-check-input" id="editIsActive" {{ ($staff->user->isActive ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="editIsActive">Active</label>
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

        <!-- Tasks Handled Tab (StaffIT Only) -->
        @if($staff->staffIt)
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'tasks-handled' ? 'show active' : '' }}" id="tasks-handled" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-task"></i> Tasks Handled</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.settings.staff.export-excel', $staff->staffID) }}?month={{ $filterMonth ?? date('m') }}&year={{ $filterYear ?? date('Y') }}&type={{ $filterTaskType ?? '' }}" class="btn btn-sm btn-light">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.settings.staff.export-pdf', $staff->staffID) }}?month={{ $filterMonth ?? date('m') }}&year={{ $filterYear ?? date('Y') }}&type={{ $filterTaskType ?? '' }}" class="btn btn-sm btn-light">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.settings.staff.show', ['staff' => $staff->staffID]) }}" class="row g-3 mb-3">
                        <input type="hidden" name="tab" value="tasks-handled">
                        <div class="col-md-3">
                            <label class="form-label small">Month</label>
                            <select name="month" class="form-select form-select-sm">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ ($filterMonth ?? date('m')) == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Year</label>
                            <input type="number" name="year" value="{{ $filterYear ?? date('Y') }}" class="form-control form-control-sm" min="2020" max="{{ date('Y') + 1 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Task Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="maintenance" {{ ($filterTaskType ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="fuel" {{ ($filterTaskType ?? '') === 'fuel' ? 'selected' : '' }}>Fuel</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">Filter</button>
                        </div>
                    </form>

                    <!-- Tasks Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Task Date</th>
                                    <th>Task Type</th>
                                    <th>Description</th>
                                    <th>Commission Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks ?? [] as $task)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($task['task_date'])->format('d M Y') }}</td>
                                        <td><span class="badge bg-info">{{ $task['task_type'] }}</span></td>
                                        <td>{{ $task['description'] }}</td>
                                        <td class="fw-semibold">RM {{ number_format($task['commission_amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No tasks found for the selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(($tasks ?? collect())->count() > 0)
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="fw-semibold text-end">Total Commission:</td>
                                        <td class="fw-bold text-danger">RM {{ number_format($totalCommission ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Commission Tab (StaffIT Only) -->
        @if($staff->staffIt)
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'commission' ? 'show active' : '' }}" id="commission" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Commission Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Tasks</h6>
                                    <h3 class="fw-bold mb-0">{{ $taskCount ?? 0 }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Commission</h6>
                                    <h3 class="fw-bold text-danger mb-0">RM {{ number_format($totalCommission ?? 0, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Task Count</th>
                                    <th>Total Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $monthlyStats = ($tasks ?? collect())->groupBy(function($task) {
                                        return \Carbon\Carbon::parse($task['task_date'])->format('Y-m');
                                    })->map(function($monthTasks, $yearMonth) {
                                        return [
                                            'month' => \Carbon\Carbon::parse($yearMonth . '-01')->format('F'),
                                            'year' => \Carbon\Carbon::parse($yearMonth . '-01')->format('Y'),
                                            'count' => $monthTasks->count(),
                                            'total' => $monthTasks->sum('commission_amount'),
                                        ];
                                    });
                                @endphp
                                @forelse($monthlyStats as $stat)
                                    <tr>
                                        <td>{{ $stat['month'] }}</td>
                                        <td>{{ $stat['year'] }}</td>
                                        <td>{{ $stat['count'] }}</td>
                                        <td class="fw-semibold">RM {{ number_format($stat['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No commission data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection





