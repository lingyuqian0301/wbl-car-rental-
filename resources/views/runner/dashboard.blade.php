@extends('layouts.runner')

@section('title', 'Runner Dashboard')
@section('page-title', 'Dashboard')

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
    .grouping-box-header .badge {
        font-size: 0.85rem;
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
    .task-table tbody tr:last-child td {
        border-bottom: none;
    }
    .badge-pickup {
        background: #3b82f6;
        color: white;
    }
    .badge-return {
        background: #8b5cf6;
        color: white;
    }
    .stats-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .stats-box-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--hasta-red-dark);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--hasta-rose);
    }
    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f1f1;
    }
    .stat-row:last-child {
        border-bottom: none;
    }
    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
    }
    .stat-value {
        font-weight: 600;
        color: #333;
    }
    .badge-upcoming {
        background: #fbbf24;
        color: #92400e;
    }
    .badge-done {
        background: #22c55e;
        color: white;
    }
</style>
@endpush

@section('content')
    <!-- Header Box -->
    <div class="header-box">
        <h2><i class="bi bi-speedometer2"></i> Welcome, {{ $user->name ?? 'Runner' }}!</h2>
        <p>{{ $today->format('l, d F Y') }}</p>
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
    
    <div class="row">
        <!-- Today's Tasks - Using Grouping Box UI -->
        <div class="col-md-8">
            <div class="grouping-box">
                <div class="grouping-box-header">
                    <span><i class="bi bi-calendar-check"></i> Today's Tasks</span>
                    <span class="badge bg-danger">{{ $todayTasks->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="task-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Type</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayTasks as $task)
                                <tr>
                                    <td><strong>#{{ $task['booking']->bookingID }}</strong></td>
                                    <td>
                                        <span class="badge {{ $task['type'] === 'pickup' ? 'badge-pickup' : 'badge-return' }}">
                                            {{ ucfirst($task['type']) }}
                                        </span>
                                    </td>
                                    <td>{{ $task['booking']->vehicle->plate_number ?? 'N/A' }}</td>
                                    <td>{{ $task['booking']->customer->user->name ?? 'N/A' }}</td>
                                    <td>{{ $task['location'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No tasks for today</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats - Using Grouping Box UI (No Commission) -->
        <div class="col-md-4">
            <div class="grouping-box">
                <div class="grouping-box-header">
                    <span><i class="bi bi-graph-up"></i> Quick Stats</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Total Tasks</span>
                    <span class="stat-value">{{ $totalTasks }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Upcoming Tasks</span>
                    <span class="badge badge-upcoming">{{ $upcomingTasks }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Completed Tasks</span>
                    <span class="badge badge-done">{{ $doneTasks }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
