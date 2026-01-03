<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Make Payment - {{ config('app.name', 'Hasta Travel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --hasta-maroon: #800020;
            --hasta-white: #ffffff;
        }
        .bg-maroon { background-color: var(--hasta-maroon); }
        .text-maroon { color: var(--hasta-maroon); }
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
        .qr-container {
            border: 2px solid #333;
            border-radius: 10px;
            padding: 10px;
            background: white;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <h1 class="text-maroon fw-bold">HASTA TRAVEL & TOURS SDN. BHD.</h1>
                    <p class="text-muted">Secure Payment Submission</p>
                </div>

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

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header card-header-maroon">
                        <h5 class="mb-0"><i class="fas fa-car me-2"></i>Booking Summary (ID: #{{ $booking->bookingID }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Car:</strong> {{ $booking->vehicle->full_model ?? $booking->vehicle->vehicle_model }}</p>
                                <p class="mb-1"><strong>Plate:</strong> {{ $booking->vehicle->registration_number ?? $booking->vehicle->plate_number }}</p>
                                <p class="mb-1"><strong>Dates:</strong> {{ $booking->start_date->format('d M Y') }} - {{ $booking->end_date->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1">Total Price: <strong>RM {{ number_format($booking->total_amount ?? $booking->rental_amount, 2) }}</strong></p>
                                <h4 class="text-maroon fw-bold mt-2">Required Deposit: RM {{ number_format($depositAmount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('payments.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bookingID" value="{{ $booking->bookingID }}">

                    <div class="card mb-4 shadow-sm border-2 border-warning">
                        <div class="card-header bg-warning text-dark fw-bold">
                            <i class="fas fa-qrcode me-2"></i>Step 1: Scan DuitNow QR to Pay
                        </div>
                        <div class="card-body text-center bg-white">
                            <div class="qr-container mb-3">
                                <img src="{{ asset('images/qr.png') }}" alt="DuitNow QR Code" style="width: 200px; height: 200px;">
                            </div>
                            <h5 class="fw-bold">HASTA TRAVEL TOURS SDN. BHD.</h5>
                            <p class="text-muted mb-0">Maybank: 5513 0654 1568</p>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-maroon">
                            <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>Step 2: Upload Proof & Details</h5>
                        </div>
                        <div class="card-body">

                            <div class="mb-4 p-3 bg-light rounded border">
                                <label class="form-label fw-bold">Choose Payment Option <span class="text-danger">*</span></label>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_type" id="payDeposit" value="Deposit" checked onchange="updateAmount(this.value)">
                                    <label class="form-check-label" for="payDeposit">
                                        Pay Deposit Only (RM {{ number_format($depositAmount, 2) }})
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_type" id="payFull" value="Full Payment" onchange="updateAmount(this.value)">
                                    <label class="form-check-label" for="payFull">
                                        Pay Full Amount (RM {{ number_format($booking->total_amount ?? $booking->rental_amount, 2) }})
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="form-label fw-bold">Amount to Pay (RM)</label>
                                    <input type="number" step="0.01" name="amount" id="amountInput" class="form-control fw-bold text-success fs-5" value="{{ $depositAmount }}" readonly>
                                    <div class="form-text">This updates automatically based on your selection above.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Transaction Reference No. <span class="text-danger">*</span></label>
        <input type="text" name="transaction_reference" class="form-control" placeholder="e.g. 12345678" required>
        <div class="form-text" style="font-size: 0.8rem;">Found on your banking receipt</div>
    </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Payment Method</label>
                                    <input type="text" class="form-control" value="DuitNow / Bank Transfer" readonly>
                                    <input type="hidden" name="payment_method" value="Bank Transfer">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Upload Receipt <span class="text-danger">*</span></label>
                                <input type="file" name="receipt_image" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="form-text">Please ensure the reference number is visible. Max 2MB.</div>
                            </div>

                            <hr>

                            <h6 class="text-maroon fw-bold mb-3">Your Bank Details (For Deposit Refund)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. CIMB" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control" placeholder="e.g. 7654321098" required>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="{{ route('bookings.show', $booking->bookingID) }}" class="btn btn-secondary px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-maroon px-5">Submit Payment</button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateAmount(type) {
            // Get PHP values into JS variables
            var deposit = {{ $depositAmount ?? 50 }};
            var full = {{ $booking->total_amount ?? $booking->rental_amount ?? 0 }};

            var input = document.getElementById('amountInput');

            if (type === 'Full Payment') {
                input.value = full.toFixed(2);
            } else {
                input.value = deposit.toFixed(2);
            }
        }
    </script>
</body>
</html>