<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hasta Staff Dashboard</title>

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --hasta-red: #b91c1c;
            --hasta-red-dark: #7f1d1d;
            --hasta-rose: #fee2e2;
            --hasta-slate: #111827;
        }
        body {
            background: linear-gradient(135deg, #fff7f7 0%, #fff 35%, #fff7f7 100%);
            color: #1f2937;
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
</head>
<body>
    <div class="container py-4">
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-icon bg-white text-danger" style="background: #fff; color: var(--hasta-red); box-shadow: inset 0 0 0 2px rgba(255,255,255,0.25);">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold">Hasta Staff Control</h1>
                            <p class="mb-0 opacity-75">Car rental booking management</p>
                        </div>
                    </div>
                    <p class="mb-0 mt-3 fw-semibold">Snapshot for {{ $today->format('d M Y') }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('staff.payments.index') }}" class="btn btn-light text-danger pill-btn">
                        <i class="bi bi-shield-check me-1"></i> Verify Payments
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light text-white pill-btn">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back to user view
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
        </div>

        <!-- Pending payments -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Payments awaiting verification</span>
                <a href="{{ route('staff.payments.index') }}" class="btn btn-light btn-sm pill-btn">
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
                                            <a href="{{ route('staff.payments.show', $payment->paymentID) }}" class="btn btn-outline-danger btn-sm pill-btn">
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

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center py-3 text-muted-small">
            <span>Hasta Staff · Redline dashboard</span>
            <span>Need help? Visit Payment Verification to action items.</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
