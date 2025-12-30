<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Verification - {{ config('app.name', 'Hasta Travel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --hasta-maroon: #800020;
            --hasta-white: #ffffff;
        }
        .bg-maroon {
            background-color: var(--hasta-maroon);
        }
        .text-maroon {
            color: var(--hasta-maroon);
        }
        .btn-maroon {
            background-color: var(--hasta-maroon);
            border-color: var(--hasta-maroon);
            color: var(--hasta-white);
        }
        .btn-maroon:hover {
            background-color: #600018;
            border-color: #600018;
            color: var(--hasta-white);
        }
        .card-header-maroon {
            background-color: var(--hasta-maroon);
            color: var(--hasta-white);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-maroon">Payment Verification</h1>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Payments Table -->
                <div class="card shadow-sm">
                    <div class="card-header card-header-maroon">
                        <h5 class="mb-0">Pending Payments (Awaiting Verification)</h5>
                    </div>
                    <div class="card-body">
                        @if($payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Vehicle</th>
                                            <th>Amount</th>
                                            <th>Payment Type</th>
                                            <th>Payment Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                            <tr>
    <td>#{{ $payment->paymentID }}</td>

    <td>#{{ $payment->bookingID }}</td>

    <td>
        {{ $payment->booking->customer->fullname ?? 'Unknown Customer' }}
    </td>

<td>
    <div class="fw-bold">
        {{ $payment->booking->vehicle->brand ?? $payment->booking->vehicle->vehicle_brand ?? 'Car' }}
        {{ $payment->booking->vehicle->model ?? $payment->booking->vehicle->vehicle_model ?? '' }}
    </div>
    <small class="text-muted">
        {{ $payment->booking->vehicle->registration_number ?? $payment->booking->vehicle->plate_number ?? 'Vehicle #'.$payment->booking->vehicleID }}
    </small>
</td>

    <td>RM {{ number_format($payment->amount, 2) }}</td>
    <td>{{ $payment->payment_type }}</td>

    <td>
        {{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}
    </td>

    <td>
        <span class="badge {{ $payment->status == 'Verified' ? 'bg-success' : 'bg-warning' }} text-dark">
            {{ $payment->status }}
        </span>
    </td>

    <td>
        <a href="{{ route('admin.payments.show', $payment->paymentID) }}"
           class="btn btn-sm btn-primary">
           View Details
        </a>
    </td>
</tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $payments->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <p class="mb-0">No pending payments at this time.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

