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
            <button class="nav-link {{ $activeTab === 'staff-detail' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#staff-detail" type="button" role="tab">
                <i class="bi bi-person-circle"></i> Staff Detail
            </button>
        </li>
        @if($staff->staffIt)
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.settings.staff.show', ['staff' => $staff->staffID, 'tab' => 'tasks-handled']) }}" class="nav-link {{ $activeTab === 'tasks-handled' ? 'active' : '' }}">
                <i class="bi bi-list-task"></i> Tasks Handled
            </a>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.settings.staff.show', ['staff' => $staff->staffID, 'tab' => 'login-logs']) }}" class="nav-link {{ $activeTab === 'login-logs' ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Login Logs
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Staff Detail Tab -->
        <div class="tab-pane fade {{ $activeTab === 'staff-detail' ? 'show active' : '' }}" id="staff-detail" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Staff Info</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-auto">Staff ID:</dt>
                                <dd class="col">{{ $staff->staffID ?? 'N/A' }}</dd>

                                <dt class="col-auto">Username:</dt>
                                <dd class="col">{{ $staff->user->username ?? 'N/A' }}</dd>

                                <dt class="col-auto">Email:</dt>
                                <dd class="col">{{ $staff->user->email ?? 'N/A' }}</dd>

                                <dt class="col-auto">Phone:</dt>
                                <dd class="col">{{ $staff->user->phone ?? 'N/A' }}</dd>

                                <dt class="col-auto">Name:</dt>
                                <dd class="col">{{ $staff->user->name ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-auto">Last Login:</dt>
                                <dd class="col">{{ $staff->user->lastLogin ? \Carbon\Carbon::parse($staff->user->lastLogin)->format('d M Y H:i') : 'N/A' }}</dd>

                                <dt class="col-auto">Date Registered:</dt>
                                <dd class="col">{{ $staff->user->dateRegistered ? \Carbon\Carbon::parse($staff->user->dateRegistered)->format('d M Y') : 'N/A' }}</dd>

                                <dt class="col-auto">Date of Birth:</dt>
                                <dd class="col">{{ $staff->user->DOB ? \Carbon\Carbon::parse($staff->user->DOB)->format('d M Y') : 'N/A' }}</dd>

                                <dt class="col-auto">Age:</dt>
                                <dd class="col">
                                    @if($staff->user->DOB)
                                        {{ \Carbon\Carbon::parse($staff->user->DOB)->age }} years
                                    @else
                                        N/A
                                    @endif
                                </dd>

                                <dt class="col-auto">Status:</dt>
                                <dd class="col">
                                    <span class="badge {{ ($staff->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ($staff->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>

                                <dt class="col-auto">IC No:</dt>
                                <dd class="col">{{ $staff->ic_no ?? 'N/A' }}</dd>

                                <dt class="col-auto">Staff Type:</dt>
                                <dd class="col">
                                    <span class="badge bg-info">
                                        {{ $staff->staffIt ? 'Staff IT' : ($staff->runner ? 'Runner' : 'N/A') }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Handled Tab (Only for Staff IT) -->
        @if($staff->staffIt)
        <div class="tab-pane fade {{ $activeTab === 'tasks-handled' ? 'show active' : '' }}" id="tasks-handled" role="tabpanel">
            <x-admin-page-header 
                title="{{ $staff->user->name ?? 'Staff' }}" 
                description="Tasks handled by {{ $staff->user->username ?? 'N/A' }}"
                :stats="[
                    ['label' => 'No of Tasks', 'value' => $taskCount, 'icon' => 'bi-list-task'],
                    ['label' => 'Total Commission', 'value' => 'RM ' . number_format($totalCommission, 2), 'icon' => 'bi-currency-dollar']
                ]"
            >
                <x-slot name="actions">
                    <div class="btn-group">
                        <button type="button" class="btn btn-light text-danger dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.settings.staff.export-excel', ['staff' => $staff->staffID, 'month' => $filterMonth, 'year' => $filterYear, 'type' => $filterTaskType]) }}"><i class="bi bi-file-earmark-excel me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.staff.export-pdf', ['staff' => $staff->staffID, 'month' => $filterMonth, 'year' => $filterYear, 'type' => $filterTaskType]) }}"><i class="bi bi-file-earmark-pdf me-2"></i> PDF</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-light text-danger" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Task
                    </button>
                </x-slot>
            </x-admin-page-header>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.settings.staff.show', ['staff' => $staff->staffID, 'tab' => 'tasks-handled']) }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Month</label>
                            <select name="filter_month" class="form-select form-select-sm">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $filterMonth == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Year</label>
                            <select name="filter_year" class="form-select form-select-sm">
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $filterYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Task Type</label>
                            <select name="filter_task_type" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="maintenance" {{ $filterTaskType === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="fuel" {{ $filterTaskType === 'fuel' ? 'selected' : '' }}>Fuel</option>
                                <option value="reception" {{ $filterTaskType === 'reception' ? 'selected' : '' }}>Reception</option>
                                <option value="other" {{ $filterTaskType === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tasks Table -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-list-task"></i> Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Task Date</th>
                                    <th>Task Type</th>
                                    <th>Description</th>
                                    <th>Commission Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($task['task_date'])->format('d M Y') }}</td>
                                        <td><span class="badge bg-info">{{ $task['task_type'] }}</span></td>
                                        <td>{{ $task['description'] }}</td>
                                        <td><strong>RM {{ number_format($task['commission_amount'], 2) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No tasks found for the selected period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($tasks->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total Commission:</td>
                                    <td class="fw-bold">RM {{ number_format($totalCommission, 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Login Logs Tab -->
        <div class="tab-pane fade {{ $activeTab === 'login-logs' ? 'show active' : '' }}" id="login-logs" role="tabpanel">
            <x-admin-page-header 
                title="{{ $staff->user->name ?? 'Staff' }}" 
                description="Login activity logs"
                :stats="[
                    ['label' => 'Total Online Time', 'value' => gmdate('H:i:s', $totalOnlineTime), 'icon' => 'bi-clock']
                ]"
            />

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Login Logs</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Login Time</th>
                                    <th>Logout Time</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loginLogs as $log)
                                    <tr>
                                        <td>{{ $log['login_time'] ?? 'N/A' }}</td>
                                        <td>{{ $log['logout_time'] ?? 'N/A' }}</td>
                                        <td>{{ $log['duration'] ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No login logs found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($loginLogs->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Total Online Time:</td>
                                    <td class="fw-bold">{{ gmdate('H:i:s', $totalOnlineTime) }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal (for Staff IT) -->
@if($staff->staffIt)
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.staff.task.store', $staff->staffID) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Date <span class="text-danger">*</span></label>
                        <input type="date" name="task_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Task Type <span class="text-danger">*</span></label>
                        <select name="task_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="fuel">Fuel</option>
                            <option value="reception">Reception</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commission Amount (RM) <span class="text-danger">*</span></label>
                        <input type="number" name="commission_amount" step="0.01" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection




