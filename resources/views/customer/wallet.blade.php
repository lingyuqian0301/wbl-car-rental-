<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - HASTA Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Figtree', sans-serif; background-color: #f8fafc; }
        .card { border-radius: 12px; }
        .badge { font-size: 0.8em; padding: 0.5em 0.8em; }
    </style>
</head>
<body>
    
    @include('components.header')

    <div class="container py-5">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary mb-4">
            &larr; Back to Dashboard
        </a>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center p-5">
                        <h5 class="text-muted text-uppercase mb-3 fw-bold">Outstanding Balance</h5>
                        
                        <h1 class="display-3 fw-bold my-3 {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                            RM {{ number_format($outstanding, 2) }}
                        </h1>
                        
                        <div class="mt-3">
                            @if($outstanding > 0)
                                <div class="alert alert-warning d-inline-block m-0">
                                    You have pending payments. Please verify your bookings.
                                </div>
                            @else
                                <div class="alert alert-success d-inline-block m-0">
                                    You are all clear! No outstanding debts.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <h4 class="mb-3 text-secondary">Payment History</h4>
                
                <div class="card shadow-sm border-0">
                    <div class="list-group list-group-flush">
                        @forelse($transactions as $t)
                            <div class="list-group-item p-4">
                                <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1 fw-bold">
                                            Payment for Booking #{{ $t->bookingID }}
                                        </h5>
                                        <p class="mb-0 text-muted small">
                                            Method: {{ $t->payment_type }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="d-block text-muted small">
                                            {{ \Carbon\Carbon::parse($t->payment_date)->format('d M Y, h:i A') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex w-100 justify-content-between align-items-center mt-3">
                                    <span class="fs-5 fw-bold text-dark">
                                        RM {{ number_format($t->amount, 2) }}
                                    </span>
                                    
                                    @if($t->status == 'Verified')
                                        <span class="badge bg-success rounded-pill">Verified</span>
                                    @elseif($t->status == 'Pending')
                                        <span class="badge bg-warning text-dark rounded-pill">Pending Review</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill">Rejected</span>
                                    @endif
                                </div>
                                
                                @if($t->status == 'Rejected' && !empty($t->rejected_reason))
                                    <div class="mt-2 p-2 bg-danger-subtle text-danger rounded small">
                                        Reason: {{ $t->rejected_reason }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <p class="mb-0">No payment history found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>