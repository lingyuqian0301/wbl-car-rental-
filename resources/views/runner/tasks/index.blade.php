@extends('layouts.runner')

@section('title', 'Task List')
@section('page-title', 'Task List')

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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-table {
        width: 100%;
        border-collapse: collapse;
    }
    .task-table thead th {
        background: var(--hasta-rose);
        color: var(--hasta-red-dark);
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        border-bottom: 2px solid #fca5a5;
    }
    .task-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #f1f1f1;
        font-size: 0.9rem;
    }
    .task-table tbody tr:hover {
        background: #fafafa;
    }
    .task-table tfoot td {
        padding: 12px;
        background: #f8f9fa;
        font-weight: 600;
    }
    .badge-pickup {
        background: #3b82f6;
        color: white;
    }
    .badge-return {
        background: #8b5cf6;
        color: white;
    }
    .badge-upcoming {
        background: #fbbf24;
        color: #92400e;
    }
    .badge-done {
        background: #22c55e;
        color: white;
    }
    .filter-box {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
    <!-- Header Box -->
    <div class="header-box">
        <h2><i class="bi bi-list-task"></i> {{ $user->name ?? 'Runner' }}'s Task List</h2>
        <p>{{ $today->format('l, d F Y') }} - {{ \Carbon\Carbon::createFromDate($filterYear, $filterMonth, 1)->format('F Y') }}</p>
        <div class="header-stats">
            <div class="header-stat">
                <div class="header-stat-value">{{ $totalTasks }}</div>
                <div class="header-stat-label">Total Tasks</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value">{{ $upcomingTasks }}</div>
                <div class="header-stat-label">Upcoming</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value">{{ $doneTasks }}</div>
                <div class="header-stat-label">Completed</div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-box">
        <form method="GET" action="{{ route('runner.tasks') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>All</option>
                    <option value="upcoming" {{ $filterStatus === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="done" {{ $filterStatus === 'done' ? 'selected' : '' }}>Done</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Month</label>
                <select name="month" class="form-select form-select-sm">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $filterMonth == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Year</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="{{ route('runner.tasks') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Task List Table - Using Grouping Box UI -->
    <div class="grouping-box">
        <div class="grouping-box-header">
            <span><i class="bi bi-list-check"></i> Task List</span>
            <span class="badge bg-danger">{{ $totalTasks }} tasks</span>
        </div>
        <div class="table-responsive">
            <table class="task-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Booking ID</th>
                        <th>Type</th>
                        <th>Delivery Date</th>
                        <th>Task Date</th>
                        <th>Location</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task['num'] }}</td>
                            <td>
                                <strong>#{{ $task['booking_id'] }}</strong>
                            </td>
                            <td>
                                <span class="badge {{ $task['task_type'] === 'Pickup' ? 'badge-pickup' : 'badge-return' }}">
                                    {{ $task['task_type'] }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $task['delivery_date']->format('d M Y') }}</div>
                                <small class="text-muted">{{ $task['delivery_date']->format('l') }}</small>
                            </td>
                            <td>
                                <div>{{ $task['task_date']->format('d M Y') }}</div>
                                <small class="text-muted">{{ $task['task_date']->format('H:i') }}</small>
                            </td>
                            <td>{{ $task['location'] }}</td>
                            <td>{{ $task['plate_number'] }}</td>
                            <td>{{ $task['customer_name'] }}</td>
                            <td>
                                @if($task['is_done'])
                                    <span class="badge badge-done">Done</span>
                                @else
                                    <span class="badge badge-upcoming">Upcoming</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-3">No tasks found for this period</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($tasks->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="9" class="text-end">
                                Total Tasks: <strong>{{ $totalTasks }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
