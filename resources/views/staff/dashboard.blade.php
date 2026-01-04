@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Staff Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ Auth::user()->name }}</p>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="metric-icon" style="background: rgba(255, 73, 48, 0.1); color: var(--staff-orange);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Bookings</p>
                        <h3 class="fw-bold mb-0">{{ $metrics['totalBookings'] }}</h3>
                        <small class="text-muted-small">{{ $metrics['activeBookings'] }} active</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="metric-icon" style="background: rgba(255, 73, 48, 0.1); color: var(--staff-orange);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Completed</p>
                        <h3 class="fw-bold mb-0">{{ $metrics['completedBookings'] }}</h3>
                        <small class="text-muted-small">{{ $metrics['totalBookings'] }} total bookings</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="metric-icon" style="background: rgba(255, 73, 48, 0.1); color: var(--staff-orange);">
                        <i class="bi bi-credit-card-2-front"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Payments Queue</p>
                        <h3 class="fw-bold mb-0">{{ $metrics['pendingPayments'] }} pending</h3>
                        <small class="text-muted-small">{{ $metrics['verifiedPayments'] }} verified to date</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card metric-card">
                <div class="card-body d-flex gap-3 align-items-center">
                    <div class="metric-icon" style="background: rgba(255, 73, 48, 0.1); color: var(--staff-orange);">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Fleet Status</p>
                        <h3 class="fw-bold mb-0">{{ $metrics['availability_status'] }} available</h3>
                        <small class="text-muted-small">{{ $metrics['vehiclesRented'] }} rented · {{ $metrics['vehiclesMaintenance'] }} maintenance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Schedule -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header" style="background: var(--staff-orange); color: white;">
                    <span class="fw-semibold">Booking Schedule</span>
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Done</span>
                            <span class="fw-bold">{{ $metrics['doneBookings'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Today</span>
                            <span class="fw-bold text-danger">{{ $metrics['todayBookings'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Tomorrow</span>
                            <span class="fw-bold text-warning">{{ $metrics['tomorrowBookings'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">This Week</span>
                            <span class="fw-bold text-info">{{ $metrics['weekBookings'] }}</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">Bookings to serve in the next 7 days</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header" style="background: var(--staff-orange); color: white;">
                    <span class="fw-semibold">Recent Bookings</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentBookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->bookingID ?? $booking->id }}</td>
                                        <td>{{ $booking->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : 'bg-info') }}">
                                                {{ $booking->booking_status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No bookings found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection






