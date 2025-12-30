<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Details - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary mb-3">&larr; Back to List</a>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment Details #{{ $payment->paymentID }}</h5>
                    <span class="badge {{ $payment->status == 'Verified' ? 'bg-success' : 'bg-warning' }}">
                        {{ $payment->status }}
                    </span>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-muted mb-3">Booking Information</h6>

                            <p><strong>Customer:</strong> <br>
                                {{ $payment->booking->customer->fullname ?? 'Unknown' }}
                            </p>

                            <p><strong>Vehicle:</strong> <br>
                                {{ $payment->booking->vehicle->brand ?? '' }}
                                {{ $payment->booking->vehicle->model ?? '' }}
                                <br>
                                <small class="text-muted">{{ $payment->booking->vehicle->registration_number ?? 'No Plate' }}</small>
                            </p>

                            <hr>

                            <h6 class="text-uppercase text-muted mb-3">Payment Information</h6>
                            <p><strong>Amount:</strong> <span class="text-success fw-bold fs-5">RM {{ number_format($payment->amount, 2) }}</span></p>
                            <p><strong>Date:</strong> {{ $payment->payment_date ? $payment->payment_date->format('d M Y, h:i A') : 'N/A' }}</p>
                            <p><strong>Type:</strong> {{ $payment->payment_type }}</p>
                        </div>

                        <div class="col-md-6 text-center">
                            <h6 class="text-uppercase text-muted mb-3">Proof of Payment</h6>
                          <div class="border p-2 rounded bg-white">
                             @if($payment->receiptURL)

                            <img src="{{ asset($payment->receiptURL) }}"
                               alt="Receipt"
                                 class="img-fluid rounded"
                                 style="max-height: 400px;">

                                @else
                                      <div class="py-5 text-muted">No Receipt Uploaded</div>
                                 @endif
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Click image to enlarge if needed (right click -> open in new tab)</small>
                            </div>
                        </div>
                    </div>
                </div>
@if($payment->status == 'Verified')
    <a href="{{ route('admin.payments.invoice', $payment->paymentID) }}" class="btn btn-primary">
        📄 Download Invoice
    </a>
@endif
                <div class="card-footer bg-white p-3">
                    <div class="d-flex justify-content-end gap-2">
                        @if($payment->status === 'Pending')
                            <form action="{{ route('admin.payments.reject', $payment->paymentID) }}" method="POST" onsubmit="return confirm('Are you sure you want to REJECT this payment?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    &#10005; Reject Payment
                                </button>
                            </form>

                            <form action="{{ route('admin.payments.approve', $payment->paymentID) }}" method="POST" onsubmit="return confirm('Confirm payment of RM {{ $payment->amount }}?');">
                                @csrf
                                <button type="submit" class="btn btn-success px-4">
                                    &#10003; Approve & Verify
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary" disabled>Processed</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
