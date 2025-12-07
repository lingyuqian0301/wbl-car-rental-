<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Make Payment - {{ config('app.name', 'Hasta Travel') }}</title>
    
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
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-maroon">HASTA TRAVEL & TOURS SDN. BHD.</h1>
                    <p class="text-muted">Payment Submission</p>
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

                <!-- Booking Summary Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header card-header-maroon">
                        <h5 class="mb-0">Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Car Model:</strong> {{ $booking->vehicle->full_model }}</p>
                                <p><strong>Registration:</strong> {{ $booking->vehicle->registration_number }}</p>
                                <p><strong>Start Date:</strong> {{ $booking->start_date->format('d M Y') }}</p>
                                <p><strong>End Date:</strong> {{ $booking->end_date->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Duration:</strong> {{ $booking->duration_days }} days</p>
                                <p><strong>Daily Rate:</strong> RM {{ number_format($booking->vehicle->daily_rate, 2) }}</p>
                                <p><strong>Total Price:</strong> RM {{ number_format($booking->total_price, 2) }}</p>
                                <p class="h5 text-maroon"><strong>Required Deposit:</strong> RM {{ number_format($depositAmount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details Card -->
                <div class="card mb-4 shadow-sm border-warning">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Bank Transfer Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Bank:</strong> Maybank</p>
                                <p><strong>Account Name:</strong> HASTA TRAVEL TOURS SDN. BHD.</p>
                                <p><strong>Account No:</strong> 5513 0654 1568</p>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <small><strong>Note:</strong> Please transfer the exact deposit amount and upload your payment receipt below.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form Card -->
                <div class="card shadow-sm">
                    <div class="card-header card-header-maroon">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('payment_date') is-invalid @enderror" 
                                           id="payment_date" 
                                           name="payment_date" 
                                           value="{{ old('payment_date', date('Y-m-d')) }}" 
                                           required>
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" 
                                            id="payment_method" 
                                            name="payment_method" 
                                            required>
                                        <option value="">Select Payment Method</option>
                                        <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="proof_of_payment" class="form-label">Proof of Payment <span class="text-danger">*</span></label>
                                <input type="file" 
                                       class="form-control @error('proof_of_payment') is-invalid @enderror" 
                                       id="proof_of_payment" 
                                       name="proof_of_payment" 
                                       accept="image/jpeg,image/png,application/pdf"
                                       required>
                                <div class="form-text">Accepted formats: JPG, PNG, PDF. Maximum file size: 2MB</div>
                                @error('proof_of_payment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-maroon">Submit Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

