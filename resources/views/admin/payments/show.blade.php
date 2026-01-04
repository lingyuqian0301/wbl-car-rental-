<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .card-header { background-color: #343a40; color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary mb-3">&larr; Back to List</a>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0">Payment Details #{{ $payment->paymentID }}</h5>
                    <div>
                        @if($payment->payment_status == 'Verified' || $payment->status == 'Verified')
                            <span class="badge bg-success fs-6">Verified</span>
                        @elseif($payment->payment_status == 'Pending' || $payment->status == 'Pending')
                            <span class="badge bg-warning text-dark fs-6">Pending Review</span>
                        @else
                            <span class="badge bg-danger fs-6">{{ $payment->payment_status ?? $payment->status }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <h6 class="text-uppercase text-muted fw-bold mb-3">Booking Information</h6>
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Customer:</span>
                                    <strong>{{ $payment->booking->customer->user->name ?? 'Unknown' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Vehicle:</span>
                                    <span class="text-end">
                                        {{ $payment->booking->vehicle->brand ?? '' }} {{ $payment->booking->vehicle->model ?? '' }}<br>
                                        <small class="text-muted">{{ $payment->booking->vehicle->registration_number ?? 'No Plate' }}</small>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span>Booking ID:</span>
                                    <span>#{{ $payment->bookingID }}</span>
                                </li>
                            </ul>

                            <h6 class="text-uppercase text-muted fw-bold mb-3">Financial Analysis</h6>
                            <div class="bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Booking Price:</span>
                                    <strong class="text-dark">RM {{ number_format($payment->booking->total_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Paid (This Receipt):</span>
                                    <strong class="text-success">- RM {{ number_format($payment->total_amount ?? $payment->amount, 2) }}</strong>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-danger">Outstanding Balance:</span>
                                    <strong class="text-danger fs-5">
                                        RM {{ number_format(($payment->booking->total_amount ?? $payment->booking->rental_amount) - ($payment->total_amount ?? $payment->amount), 2) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="small text-muted mb-1">Payment Method: <strong>{{ $payment->payment_type }}</strong></p>
                                <p class="small text-muted">Transaction Date: {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y, h:i A') : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6 text-center border-start">
                            <h6 class="text-uppercase text-muted fw-bold mb-3">Proof of Payment</h6>
                            
                            <div class="border p-2 rounded bg-white shadow-sm d-inline-block">
                                @if($payment->proof_of_payment)
                                    <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $payment->proof_of_payment) }}" 
                                             alt="Receipt" 
                                             class="img-fluid rounded" 
                                             style="max-height: 400px; object-fit: contain;">
                                    </a>
                                    <p class="mt-2 small text-muted">Click image to open full size</p>
                                @else
                                    <div class="py-5 text-muted fst-italic">
                                        No Receipt Uploaded
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white p-3 border-top">
                    <div class="d-flex justify-content-end gap-2">
                        
                        @if($payment->payment_status == 'Verified' || $payment->status == 'Verified')
                            <a href="{{ route('admin.payments.invoice', $payment->paymentID) }}" class="btn btn-primary">
                                📄 Download Invoice
                            </a>
                            <button class="btn btn-secondary" disabled>Already Verified</button>
                        
                        @elseif($payment->payment_status == 'Pending' || $payment->status == 'Pending')
                            <form action="{{ route('admin.payments.reject', $payment->paymentID) }}" method="POST" onsubmit="return confirm('Are you sure you want to REJECT this payment?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    &#10005; Reject
                                </button>
                            </form>

                            <form action="{{ route('admin.payments.approve', $payment->paymentID) }}" method="POST" onsubmit="return confirm('Confirm payment of RM {{ $payment->total_amount ?? $payment->amount }}? This will update the wallet.');">
                                @csrf
                                <button type="submit" class="btn btn-success px-4">
                                    &#10003; Verify & Approve
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>