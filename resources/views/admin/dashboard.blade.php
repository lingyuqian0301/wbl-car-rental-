@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <style>
        :root {
            --hasta-red: #b91c1c;
            --hasta-red-dark: #7f1d1d;
            --hasta-rose: #fee2e2;
            --hasta-amber: #f59e0b;
            --hasta-slate: #111827;
        }
        .page-header {
            background: white;
            color: var(--hasta-slate);
            border-radius: 20px;
            padding: 24px 28px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .metric-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.05);
            height: 100%;
        }
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--hasta-rose);
            color: var(--hasta-red);
            font-size: 1.35rem;
        }
        .card-header-red {
            background: var(--hasta-red);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }
        .progress-bar-red {
            background: linear-gradient(90deg, var(--hasta-red) 0%, var(--hasta-red-dark) 100%);
        }
        .table thead th {
            background: var(--hasta-rose);
            color: var(--hasta-slate);
            border-bottom: 2px solid #fca5a5;
        }
        .badge-soft {
            background: var(--hasta-rose);
            color: var(--hasta-red);
            border: 1px solid #fecdd3;
        }
        .pill-btn {
            border-radius: 999px;
            padding-inline: 16px;
        }
        .text-muted-small {
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid py-2">
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h1 class="h3 mb-1 fw-bold">Hasta {{ isset($isStaffIT) && $isStaffIT ? 'Staff IT' : (isset($isStaff) && $isStaff ? 'Staff' : 'Admin') }} Dashboard</h1>
                        </div>
                    </div>
                    <p class="mb-0 mt-3 fw-semibold">Snapshot for {{ $today->format('d M Y') }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-light text-danger pill-btn" style="border: 1px solid var(--hasta-red);">
                        <i class="bi bi-credit-card me-1"></i> Verify Payments
                    </a>
                    <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-outline-danger text-danger pill-btn" style="border: 1px solid var(--hasta-red);">
                        <i class="bi bi-calendar-check me-1"></i> Verify Booking
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <a href="{{ route('admin.bookings.reservations', ['filter_pickup_date' => $today->format('Y-m-d')]) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-car-front"></i></div>
                            <div>
                                <p class="text-muted mb-1">Active Bookings</p>
                                <h3 class="fw-bold mb-0">{{ $metrics['newBookingsThisWeek'] }}</h3>
                                <small class="text-muted-small">New bookings this week</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @if((!isset($isStaff) || !$isStaff) && (!auth()->check() || !auth()->user()->isStaffIT()))
            <div class="col-12 col-md-6 col-xl-3">
                <a href="{{ route('admin.reports.rentals', ['date_range' => 'monthly']) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-cash-coin"></i></div>
                            <div>
                                <p class="text-muted mb-1">Revenue (This Month)</p>
                                <h3 class="fw-bold mb-0">RM {{ number_format($metrics['revenueThisMonth'] ?? 0, 2) }}</h3>
                                <small class="text-muted-small">All-time RM {{ number_format($metrics['revenueAllTime'] ?? 0, 2) }}</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endif
            <div class="col-12 col-md-6 col-xl-3">
                <a href="{{ route('admin.payments.index', ['payment_isVerify' => '0']) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-credit-card-2-front"></i></div>
                            <div>
                                <p class="text-muted mb-1">Payments Queue</p>
                                <h3 class="fw-bold mb-0">{{ $metrics['unverifiedPayments'] }}</h3>
                                <small class="text-muted-small">Unverified payments</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <a href="{{ route('admin.vehicles.others', ['tab' => 'vehicle', 'filter_date' => $today->format('Y-m-d')]) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-truck"></i></div>
                            <div>
                                <p class="text-muted mb-1">Fleet Status</p>
                                <h3 class="fw-bold mb-0">{{ $metrics['currentDayAvailableFleet'] }}</h3>
                                <small class="text-muted-small">Today's available fleet</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Today's Pickup and Return Bookings -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <a href="{{ route('admin.bookings.reservations', ['filter_pickup_date' => $today->format('Y-m-d')]) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-calendar-check"></i></div>
                            <div>
                                <p class="text-muted mb-1">Today's Pickup</p>
                                <h3 class="fw-bold mb-0">{{ $metrics['todayPickupBookings'] }}</h3>
                                <small class="text-muted-small">Bookings to pickup today</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <a href="{{ route('admin.bookings.reservations', ['filter_return_date' => $today->format('Y-m-d')]) }}" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-calendar-x"></i></div>
                            <div>
                                <p class="text-muted mb-1">Today's Return</p>
                                <h3 class="fw-bold mb-0">{{ $metrics['todayReturnBookings'] }}</h3>
                                <small class="text-muted-small">Bookings to return today</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Revenue trend + Fleet load -->
        <div class="row g-3 mb-4">
            @if((!isset($isStaff) || !$isStaff) && (!auth()->check() || !auth()->user()->isStaffIT()))
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Profit Trend (last 3 months)</span>
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-3 g-3">
                            @foreach($monthlyRevenue ?? [] as $point)
                                <div class="col">
                                    <a href="{{ route('admin.reports.rentals', ['date_range' => 'custom', 'date_from' => $point['date_from'], 'date_to' => $point['date_to']]) }}" class="text-decoration-none text-dark">
                                        <div class="p-3 border rounded-3 text-center h-100" style="border-color: #fde2e2; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            <p class="mb-1 text-muted">{{ $point['label'] }}</p>
                                            <h5 class="mb-2 fw-bold">RM {{ number_format($point['total'], 2) }}</h5>
                                            <div class="progress" style="height: 6px;">
                                                @php
                                                    $max = max(1, collect($monthlyRevenue)->max('total'));
                                                    $percent = ($point['total'] / $max) * 100;
                                                @endphp
                                                <div class="progress-bar progress-bar-red" role="progressbar" style="width: {{ $percent }}%;"></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="{{ ((!isset($isStaff) || !$isStaff) && (!auth()->check() || !auth()->user()->isStaffIT())) ? 'col-lg-4' : 'col-lg-12' }}">
                <a href="{{ route('admin.bookings.reservations', ['filter_pickup_date_from' => $startOfWeek->format('Y-m-d'), 'filter_pickup_date_to' => $endOfWeek->format('Y-m-d'), 'sort' => 'status_priority']) }}" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Fleet Booking</span>
                            <i class="bi bi-calendar-week"></i>
                        </div>
                    <div class="card-body">
                        @php
                            $weeklyStats = $weeklyBookingStats ?? ['done' => 0, 'current' => 0, 'upcoming' => 0, 'total' => 0];
                            $total = max($weeklyStats['total'], 1);
                            $donePercent = ($weeklyStats['done'] / $total) * 100;
                            $currentPercent = ($weeklyStats['current'] / $total) * 100;
                            $upcomingPercent = ($weeklyStats['upcoming'] / $total) * 100;
                        @endphp
                        <h4 class="fw-bold mb-1">{{ $weeklyStats['total'] }} bookings this week</h4>
                        <p class="text-muted mb-3">Weekly booking schedule (Mon - Sun)</p>
                        <div class="progress mb-3" style="height: 20px; border-radius: 8px; overflow: hidden;">
                            @if($weeklyStats['done'] > 0)
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $donePercent }}%;" title="Done: {{ $weeklyStats['done'] }} bookings"></div>
                            @endif
                            @if($weeklyStats['current'] > 0)
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $currentPercent }}%;" title="Current: {{ $weeklyStats['current'] }} bookings"></div>
                            @endif
                            @if($weeklyStats['upcoming'] > 0)
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $upcomingPercent }}%;" title="Upcoming: {{ $weeklyStats['upcoming'] }} bookings"></div>
                            @endif
                        </div>
                        <div class="d-flex flex-column gap-2 text-muted-small">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-secondary me-2">Done</span> {{ $weeklyStats['done'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-danger me-2">Current</span> {{ $weeklyStats['current'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-success me-2">Upcoming</span> {{ $weeklyStats['upcoming'] }}</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Pending payments -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Payments awaiting verification</span>
                <a href="{{ route('admin.payments.index', ['payment_isVerify' => '0']) }}" class="btn btn-light btn-sm pill-btn">
                    Review all
                </a>
            </div>
            <div class="card-body">
                @if($pendingPayments->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPayments as $payment)
                                    <tr>
                                        <td>#{{ $payment->paymentID }}</td>
                                        <td>#{{ $payment->bookingID }}</td>
                                        <td>{{ $payment->booking->customer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $payment->booking->vehicle->plate_number ?? 'N/A' }}</td>
                                        <td class="fw-semibold text-danger">RM {{ number_format($payment->total_amount ?? $payment->amount, 2) }}</td>
                                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.payments.index', ['search' => $payment->bookingID, 'payment_isVerify' => '0']) }}" class="btn btn-outline-danger btn-sm pill-btn">
                                                Verify
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border-0 text-muted mb-0">
                        All caught up — no pending payments right now.
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent activity -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <a href="{{ route('admin.bookings.reservations', ['sort' => 'created_desc']) }}" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Recent Bookings</span>
                            <i class="bi bi-calendar2-week"></i>
                        </div>
                        <div class="card-body">
                            @forelse($recentBookings as $booking)
                                <a href="{{ route('admin.bookings.reservations.show', ['booking' => $booking->bookingID]) }}" class="text-decoration-none text-dark">
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg">
                                        <div>
                                            <p class="mb-0 fw-semibold">#{{ $booking->bookingID }} · {{ $booking->vehicle->plate_number ?? 'N/A' }}</p>
                                            <small class="text-muted">{{ $booking->customer->user->name ?? 'Unknown' }} · {{ $booking->rental_start_date?->format('d M') ?? 'N/A' }} - {{ $booking->rental_end_date?->format('d M Y') ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                            {{ $booking->booking_status }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p class="mb-0 text-muted">No bookings yet.</p>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('admin.bookings.cancellation', ['refund_status' => 'false']) }}" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Cancellation Request</span>
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="card-body">
                            @forelse($cancellationRequests as $booking)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <p class="mb-0 fw-semibold">#{{ $booking->bookingID }} · {{ $booking->vehicle->plate_number ?? 'N/A' }}</p>
                                        <small class="text-muted">{{ $booking->customer->user->name ?? 'Unknown' }} · {{ $booking->rental_start_date?->format('d M Y') ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge {{ $booking->booking_status === 'Cancelled' || $booking->booking_status === 'cancelled' ? 'bg-danger' : ($booking->booking_status === 'refunding' ? 'bg-warning text-dark' : 'bg-info') }}">
                                        {{ $booking->booking_status }}
                                    </span>
                                </div>
                            @empty
                                <p class="mb-0 text-muted">No cancellation requests.</p>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Booking need runner -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Booking need runner</span>
                <a href="{{ route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc']) }}" class="btn btn-light btn-sm pill-btn">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($bookingsNeedRunner->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Pickup Date</th>
                                    <th>Pickup Time</th>
                                    <th>Plate No</th>
                                    <th>Assigned Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookingsNeedRunner as $booking)
                                    <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc']) }}'">
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('H:i') : 'N/A' }}</td>
                                        <td>{{ $booking->vehicle->plate_number ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ ($booking->assigned_status ?? 'unassigned') === 'assigned' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ ucfirst($booking->assigned_status ?? 'unassigned') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc']) }}" class="btn btn-outline-danger btn-sm pill-btn" onclick="event.stopPropagation()">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border-0 text-muted mb-0">
                        No bookings need runner at the moment.
                    </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Bookings to Serve -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Upcoming Bookings to Serve (Next 3 Days)</span>
                <a href="{{ route('admin.bookings.reservations', ['filter_pickup_date_from' => $today->format('Y-m-d'), 'filter_pickup_date_to' => $today->copy()->addDays(3)->format('Y-m-d'), 'sort' => 'pickup_asc']) }}" class="btn btn-light btn-sm pill-btn">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($upcomingBookingsToServe->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Plate No</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingBookingsToServe as $booking)
                                    <tr>
                                        <td>#{{ $booking->bookingID }}</td>
                                        <td>{{ $booking->customer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $booking->vehicle->plate_number ?? 'N/A' }}</td>
                                        <td>{{ $booking->rental_start_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>{{ $booking->rental_end_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ ($booking->payment_status_display ?? 'Deposit') === 'Full' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $booking->payment_status_display ?? 'Deposit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.bookings.reservations.show', ['booking' => $booking->bookingID]) }}" class="btn btn-outline-danger btn-sm pill-btn">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border-0 text-muted mb-0">
                        No upcoming bookings in the next 3 days.
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center py-3 text-muted-small">
            <span>Hasta Travel Vehicle Rental System · Admin dashboard</span>
        </div>
    </div>
@endsection
