@extends('layouts.runner')

@section('title', 'Runner Dashboard')
@section('page-title', 'Dashboard')

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
            <div class="header-stat">
                <div class="header-stat-value">RM {{ number_format($monthlyCommission, 2) }}</div>
                <div class="header-stat-label">This Month Commission</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Today's Tasks -->
        <div class="col-md-8">
            <div class="runner-card">
                <div class="card-header-green d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-check"></i> Today's Tasks</span>
                    <span class="badge bg-light text-dark">{{ $todayTasks->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="runner-table">
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
                            @forelse($todayTasks as $booking)
                                @php
                                    $pickupDate = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                                    $returnDate = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                                    $isPickupToday = $pickupDate && $pickupDate->isToday();
                                    $isReturnToday = $returnDate && $returnDate->isToday();
                                @endphp
                                @if($isPickupToday && !empty($booking->pickup_point) && $booking->pickup_point !== 'HASTA HQ Office')
                                    <tr>
                                        <td><strong>#{{ $booking->bookingID }}</strong></td>
                                        <td><span class="badge badge-pickup">Pickup</span></td>
                                        <td>{{ $booking->vehicle->plate_number ?? 'N/A' }}</td>
                                        <td>{{ $booking->customer->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->pickup_point }}</td>
                                    </tr>
                                @endif
                                @if($isReturnToday && !empty($booking->return_point) && $booking->return_point !== 'HASTA HQ Office')
                                    <tr>
                                        <td><strong>#{{ $booking->bookingID }}</strong></td>
                                        <td><span class="badge badge-return">Return</span></td>
                                        <td>{{ $booking->vehicle->plate_number ?? 'N/A' }}</td>
                                        <td>{{ $booking->customer->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->return_point }}</td>
                                    </tr>
                                @endif
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
        
        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="runner-card mb-4">
                <div class="card-header-green">
                    <i class="bi bi-graph-up"></i> Quick Stats
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Total Tasks</span>
                        <strong>{{ $totalTasks }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Upcoming Tasks</span>
                        <span class="badge badge-upcoming">{{ $upcomingTasks }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Completed Tasks</span>
                        <span class="badge badge-done">{{ $doneTasks }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">This Month Commission</span>
                        <strong class="text-success">RM {{ number_format($monthlyCommission, 2) }}</strong>
                    </div>
                </div>
            </div>
            
            <div class="runner-card">
                <div class="card-header-green">
                    <i class="bi bi-lightning"></i> Quick Actions
                </div>
                <div class="card-body p-4">
                    <a href="{{ route('runner.tasks') }}" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-list-task"></i> View All Tasks
                    </a>
                    <a href="{{ route('runner.tasks', ['status' => 'upcoming']) }}" class="btn btn-outline-warning w-100">
                        <i class="bi bi-clock"></i> Upcoming Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

