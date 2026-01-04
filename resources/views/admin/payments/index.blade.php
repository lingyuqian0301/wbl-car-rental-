@extends('layouts.admin')

@section('title', 'Payment Verification')

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Payment Verification" 
        description="Verify and manage all payment transactions"
        :stats="[
            ['label' => 'Total Payments', 'value' => $totalPayments, 'icon' => 'bi-credit-card'],
            ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
            ['label' => 'Verified', 'value' => $totalVerified, 'icon' => 'bi-check-circle'],
            ['label' => 'Full Payment', 'value' => $totalFullPayment, 'icon' => 'bi-currency-dollar'],
            ['label' => 'Payments Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
        ]"
        :date="$today"
    />

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
@endsection

