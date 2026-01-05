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
            background: linear-gradient(120deg, var(--hasta-red) 0%, var(--hasta-red-dark) 100%);
            color: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 35px rgba(185, 28, 28, 0.25);
            padding: 24px 28px;
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
                            <h1 class="h3 mb-1 fw-bold">Hasta {{ isset($isStaff) && $isStaff ? 'Staff' : 'Admin' }} Dashboard</h1>
                        </div>
                    </div>
                    <p class="mb-0 mt-3 fw-semibold">Snapshot for {{ $today->format('d M Y') }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-light text-danger pill-btn">
                        <i class="bi bi-credit-card me-1"></i> Verify Payments
                    </a>
                    <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-outline-light text-white pill-btn">
                        <i class="bi bi-calendar-check me-1"></i> Verify Booking
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="metric-icon"><i class="bi bi-car-front"></i></div>
                        <div>
                            <p class="text-muted mb-1">Active Bookings</p>
                            <h3 class="fw-bold mb-0">{{ $metrics['activeBookings'] }}</h3>
                            <small class="text-muted-small">{{ $metrics['totalBookings'] }} total bookings</small>
                        </div>
                    </div>
                </div>
            </div>
            @if(!isset($isStaff) || !$isStaff)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="metric-icon"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <p class="text-muted mb-1">Revenue (This Month)</p>
                            <h3 class="fw-bold mb-0">RM {{ number_format($metrics['revenueThisMonth'] ?? 0, 2) }}</h3>
                            <small class="text-muted-small">All-time RM {{ number_format($metrics['revenueAllTime'] ?? 0, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="metric-icon"><i class="bi bi-credit-card-2-front"></i></div>
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
                        <div class="metric-icon"><i class="bi bi-truck"></i></div>
                        <div>
                            <p class="text-muted mb-1">Fleet Status</p>
                            <h3 class="fw-bold mb-0">{{ $metrics['vehiclesAvailable'] }} available</h3>
                            <small class="text-muted-small">{{ $metrics['vehiclesRented'] }} rented · {{ $metrics['vehiclesMaintenance'] }} maintenance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue trend + Fleet load -->
        <div class="row g-3 mb-4">
            @if(!isset($isStaff) || !$isStaff)
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Revenue Trend (last 3 months)</span>
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-3 g-3">
                            @foreach($monthlyRevenue ?? [] as $point)
                                <div class="col">
                                    <div class="p-3 border rounded-3 text-center h-100" style="border-color: #fde2e2;">
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
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="{{ (!isset($isStaff) || !$isStaff) ? 'col-lg-4' : 'col-lg-12' }}">
                <div class="card h-100">
                    <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Fleet Load</span>
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="card-body">
                        @php
                            $fleetTotal = max($metrics['fleetTotal'], 1);
                            $utilization = round(($metrics['vehiclesRented'] / $fleetTotal) * 100, 1);
                        @endphp
                        <h4 class="fw-bold mb-1">{{ $utilization }}% utilization</h4>
                        <p class="text-muted mb-3">Tracking rented vs total fleet capacity</p>
                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar progress-bar-red" role="progressbar" style="width: {{ $utilization }}%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted-small">
                            <span><span class="badge badge-soft me-1">Available</span> {{ $metrics['vehiclesAvailable'] }}</span>
                            <span><span class="badge bg-danger-subtle text-danger me-1">Rented</span> {{ $metrics['vehiclesRented'] }}</span>
                            <span><span class="badge bg-warning-subtle text-warning me-1">Maint</span> {{ $metrics['vehiclesMaintenance'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending payments -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Payments awaiting verification</span>
                <a href="{{ route('admin.payments.index', ['payment_status' => 'Pending']) }}" class="btn btn-light btn-sm pill-btn">
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
                                        <td>{{ $payment->booking->vehicle->full_model ?? 'N/A' }}</td>
                                        <td class="fw-semibold text-danger">RM {{ number_format($payment->total_amount ?? $payment->amount, 2) }}</td>
                                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.payments.show', $payment->paymentID) }}" class="btn btn-outline-danger btn-sm pill-btn">
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
                <div class="card h-100">
                    <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Recent Bookings</span>
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                    <div class="card-body">
                        @forelse($recentBookings as $booking)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <p class="mb-0 fw-semibold">#{{ $booking->bookingID }} · {{ $booking->vehicle->full_model ?? 'N/A' }}</p>
                                    <small class="text-muted">{{ $booking->customer->user->name ?? 'Unknown' }} · {{ $booking->start_date->format('d M') }} - {{ $booking->end_date->format('d M Y') }}</small>
                                </div>
                                <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </div>
                        @empty
                            <p class="mb-0 text-muted">No bookings yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Latest Payments</span>
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div class="card-body">
                        @forelse($recentPayments as $payment)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <p class="mb-0 fw-semibold">RM {{ number_format($payment->total_amount ?? $payment->amount, 2) }} — {{ $payment->booking->vehicle->full_model ?? 'N/A' }}</p>
                                    <small class="text-muted">#{{ $payment->paymentID }} · {{ $payment->booking->customer->user->name ?? 'Unknown' }} · {{ $payment->payment_date?->format('d M Y') }}</small>
                                </div>
                                <span class="badge {{ ($payment->payment_status ?? $payment->status) === 'Verified' ? 'bg-success' : (($payment->payment_status ?? $payment->status) === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $payment->payment_status ?? $payment->status }}
                                </span>
                            </div>
                        @empty
                            <p class="mb-0 text-muted">No payments recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings to Serve -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Upcoming Bookings to Serve (Next 3 Days)</span>
                <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-light btn-sm pill-btn">
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
                                    <th>Vehicle</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingBookingsToServe as $booking)
                                    <tr>
                                        <td>#{{ $booking->bookingID }}</td>
                                        <td>{{ $booking->customer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $booking->vehicle->vehicle_brand ?? 'N/A' }} {{ $booking->vehicle->vehicle_model ?? '' }}</td>
                                        <td>{{ $booking->rental_start_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>{{ $booking->rental_end_date?->format('d M Y') ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-soft">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.bookings.reservations') }}?booking_id={{ $booking->bookingID }}" class="btn btn-outline-danger btn-sm pill-btn">
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
