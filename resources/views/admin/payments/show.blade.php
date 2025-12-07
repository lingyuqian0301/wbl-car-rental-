<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Details - {{ config('app.name', 'Hasta Travel') }}</title>
    
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
        .receipt-image {
            max-width: 100%;
            height: auto;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-maroon">Payment Details</h1>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Back to List</a>
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

                <div class="row">
                    <!-- Payment Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header card-header-maroon">
                                <h5 class="mb-0">Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Payment ID:</th>
                                        <td>#{{ $payment->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if($payment->status == 'Pending')
                                                <span class="badge bg-warning text-dark">{{ $payment->status }}</span>
                                            @elseif($payment->status == 'Verified')
                                                <span class="badge bg-success">{{ $payment->status }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ $payment->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Amount:</th>
                                        <td class="h5 text-maroon">RM {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Type:</th>
                                        <td>{{ $payment->payment_type }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Method:</th>
                                        <td>{{ $payment->payment_method }}</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Date:</th>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    </tr>
                                    @if($payment->verified_by)
                                        <tr>
                                            <th>Verified By:</th>
                                            <td>{{ $payment->verifier->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Verified At:</th>
                                            <td>{{ $payment->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @endif
                                    @if($payment->rejected_reason)
                                        <tr>
                                            <th>Rejection Reason:</th>
                                            <td class="text-danger">{{ $payment->rejected_reason }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Information -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header card-header-maroon">
                                <h5 class="mb-0">Booking Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Booking ID:</th>
                                        <td>#{{ $payment->booking_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Customer:</th>
                                        <td>{{ $payment->booking->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $payment->booking->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle:</th>
                                        <td>{{ $payment->booking->vehicle->full_model }}</td>
                                    </tr>
                                    <tr>
                                        <th>Registration:</th>
                                        <td>{{ $payment->booking->vehicle->registration_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Start Date:</th>
                                        <td>{{ $payment->booking->start_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>End Date:</th>
                                        <td>{{ $payment->booking->end_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Duration:</th>
                                        <td>{{ $payment->booking->duration_days }} days</td>
                                    </tr>
                                    <tr>
                                        <th>Total Price:</th>
                                        <td>RM {{ number_format($payment->booking->total_price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Booking Status:</th>
                                        <td>
                                            @if($payment->booking->status == 'Pending')
                                                <span class="badge bg-warning text-dark">{{ $payment->booking->status }}</span>
                                            @elseif($payment->booking->status == 'Confirmed')
                                                <span class="badge bg-success">{{ $payment->booking->status }}</span>
                                            @elseif($payment->booking->status == 'Cancelled')
                                                <span class="badge bg-danger">{{ $payment->booking->status }}</span>
                                            @else
                                                <span class="badge bg-info">{{ $payment->booking->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proof of Payment -->
                @if($payment->proof_of_payment)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header card-header-maroon">
                            <h5 class="mb-0">Proof of Payment</h5>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $fileExtension = strtolower(pathinfo($payment->proof_of_payment, PATHINFO_EXTENSION));
                            @endphp
                            
                            @if(in_array($fileExtension, ['jpg', 'jpeg', 'png']))
                                <img src="{{ Storage::url($payment->proof_of_payment) }}" 
                                     alt="Proof of Payment" 
                                     class="receipt-image mb-3"
                                     style="max-height: 600px;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23dc3545\'%3EError loading image%3C/text%3E%3C/svg%3E'; document.getElementById('image-error-message').style.display='block';">
                                <!-- US016: Exception flow - Error loading image -->
                                <div id="image-error-message" class="alert alert-danger mt-2" style="display: none;">
                                    <strong>Error loading image.</strong> Please refresh the page or contact support if the problem persists.
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="location.reload()">Refresh Page</button>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <p class="mb-2">PDF Document</p>
                                    <a href="{{ Storage::url($payment->proof_of_payment) }}" 
                                       target="_blank" 
                                       class="btn btn-primary">
                                        View PDF
                                    </a>
                                </div>
                            @endif
                            
                            <div>
                                <a href="{{ Storage::url($payment->proof_of_payment) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    Download Receipt
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons (Only show if status is Pending) -->
                @if($payment->status == 'Pending')
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                        <button type="submit" 
                                                class="btn btn-success btn-lg w-100" 
                                                onclick="return confirm('Are you sure you want to approve this payment? This will confirm the booking.');">
                                            ✓ Approve Payment
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" 
                                            class="btn btn-danger btn-lg w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal">
                                        ✗ Reject Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Payment Modal -->
                    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel">Reject Payment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                        <div class="mb-3">
                                            <label for="rejected_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                            <textarea class="form-control @error('rejected_reason') is-invalid @enderror" 
                                                      id="rejected_reason" 
                                                      name="rejected_reason" 
                                                      rows="4" 
                                                      required 
                                                      minlength="10" 
                                                      maxlength="500"
                                                      placeholder="Please provide a detailed reason for rejecting this payment...">{{ old('rejected_reason') }}</textarea>
                                            @error('rejected_reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Minimum 10 characters, maximum 500 characters.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

