<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard') - Hasta Travel</title>

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            /* Primary Colors */
            --admin-red: #dc2626;
            --admin-red-dark: #991b1b;
            --admin-red-light: #fee2e2;
            --admin-red-lighter: #fef2f2;
            
            /* Neutral Colors */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            /* Status Colors */
            --success: #059669;
            --success-light: #d1fae5;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0284c7;
            --info-light: #e0f2fe;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            
            /* Typography */
            --font-sans: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            
            /* Legacy aliases */
            --hasta-red: #dc2626;
            --hasta-red-dark: #991b1b;
            --hasta-rose: #fee2e2;
            --hasta-slate: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            font-size: 13px; /* Zoom out effect */
        }
        
        body {
            font-family: var(--font-sans);
            font-size: var(--font-size-sm);
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.5;
        }
        
        .page-header {
            background: linear-gradient(120deg, var(--admin-red) 0%, var(--admin-red-dark) 100%);
            color: #fff;
            border-radius: var(--radius-xl);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.2);
            padding: 1.5rem 1.75rem;
        }
        
        /* ===== CARD STYLES ===== */
        .card {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            background: white;
        }
        
        .metric-card {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-lg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-red-light);
            color: var(--admin-red);
            font-size: 1.25rem;
        }
        
        .card-header-red {
            background: var(--admin-red);
            color: #fff;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            padding: 0.875rem 1.25rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* ===== TABLE STYLES ===== */
        .table {
            font-size: var(--font-size-sm);
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--admin-red-light);
            color: var(--admin-red-dark);
            font-weight: 600;
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--admin-red);
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .table tbody tr:hover {
            background-color: var(--gray-50);
        }
        
        /* ===== BUTTON STYLES ===== */
        .btn {
            font-size: var(--font-size-sm);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: all 0.15s ease;
        }
        
        .btn-sm {
            font-size: var(--font-size-xs);
            padding: 0.375rem 0.75rem;
        }
        
        .btn-danger {
            background: var(--admin-red);
            border-color: var(--admin-red);
        }
        
        .btn-danger:hover {
            background: var(--admin-red-dark);
            border-color: var(--admin-red-dark);
        }
        
        .btn-outline-danger {
            color: var(--admin-red);
            border-color: var(--admin-red);
        }
        
        .btn-outline-danger:hover {
            background: var(--admin-red);
            color: white;
        }
        
        .pill-btn {
            border-radius: 999px;
            padding-inline: 1rem;
        }
        
        /* ===== BADGE STYLES ===== */
        .badge {
            font-size: var(--font-size-xs);
            font-weight: 500;
            padding: 0.35em 0.65em;
            border-radius: var(--radius-sm);
        }
        
        .badge-soft {
            background: var(--admin-red-light);
            color: var(--admin-red);
            border: 1px solid #fecdd3;
        }
        
        /* ===== ALERT STYLES ===== */
        .alert {
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--font-size-sm);
            padding: 0.875rem 1rem;
        }
        
        .alert-success { background: var(--success-light); color: var(--success); }
        .alert-danger { background: var(--danger-light); color: var(--danger); }
        .alert-warning { background: var(--warning-light); color: var(--warning); }
        .alert-info { background: var(--info-light); color: var(--info); }
        
        /* ===== UTILITY ===== */
        .text-muted-small {
            color: var(--gray-500);
            font-size: var(--font-size-sm);
        }
        
        .text-danger { color: var(--admin-red) !important; }
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
                            <h1 class="h3 mb-1 fw-bold">
                                <span style="color: #fff; font-weight: 700;">HASTA</span>
                                <span style="color: rgba(255,255,255,0.8); font-weight: 400; margin-left: 0.5rem;">Travel</span>
                            </h1>
                            <p class="mb-0 opacity-75">Staff Dashboard - Car rental booking management</p>
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
            <span>Hasta Travel · Staff Dashboard</span>
            <span>Need help? Visit Payment Verification to action items.</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
