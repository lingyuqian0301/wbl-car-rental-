<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vehicle Leasing - Hasta Travel</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('image/favicon.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
        }
        .page-header {
            background: white;
            padding: 24px 28px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .reservation-info-text {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.4;
        }
        .reservation-info-text div {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 fw-bold">Vehicle Leasing</h1>
                    <p class="mb-0 text-muted">Manage vehicle leasing bookings (more than 15 days)</p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-danger">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vehicle Leasing Bookings</h5>
                <span class="badge bg-light text-dark">{{ $bookings->total() ?? 0 }} total</span>
            </div>
            <div class="card-body p-0">
                @if(isset($bookings) && $bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer Name</th>
                                    <th>Vehicle Plate No</th>
                                    <th>Payment Price</th>
                                    <th>Invoice No</th>
                                    <th>Payment Receipt Pic</th>
                                    <th>Payment Status</th>
                                    <th>Pickup Detail</th>
                                    <th>Return Detail</th>
                                    <th>Booking Status</th>
                                    <th>Duration</th>
                                    <th>Served By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                    @php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                        $vehicle = $booking->vehicle;
                                        $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                                        $invoice = $booking->invoice;
                                        
                                        $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                        $totalAmount = ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
                                        $paymentStatus = 'Deposit';
                                        if ($totalPaid >= $totalAmount) {
                                            $paymentStatus = 'Full';
                                        } elseif ($totalPaid > 0) {
                                            $paymentStatus = 'Deposit';
                                        } else {
                                            $paymentStatus = 'Unpaid';
                                        }
                                        
                                        if ($booking->booking_status === 'Cancelled' || $booking->booking_status === 'Refunding') {
                                            $refundedPayments = $booking->payments()->where('payment_status', 'Refunded')->sum('total_amount');
                                            if ($refundedPayments > 0) {
                                                $paymentStatus = 'Refunded';
                                            }
                                        }
                                        
                                        $staffServed = $booking->staff_served ? \App\Models\User::find($booking->staff_served) : null;
                                    @endphp
                                    <tr>
                                        <td><strong>#{{ $booking->bookingID }}</strong></td>
                                        <td>{{ $user->name ?? 'Unknown' }}</td>
                                        <td>
                                            <strong>{{ $vehicle->plate_number ?? 'N/A' }}</strong>
                                            <div class="reservation-info-text">
                                                <div><strong>Model:</strong> {{ $vehicle->vehicle_model ?? 'N/A' }}</div>
                                                <div><strong>Brand:</strong> {{ $vehicle->vehicle_brand ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>RM {{ number_format($totalAmount, 2) }}</strong>
                                            <div class="reservation-info-text">
                                                <div>Paid: RM {{ number_format($totalPaid, 2) }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($invoice)
                                                <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                                <div class="reservation-info-text">
                                                    <div>
                                                        <a href="{{ route('invoices.generate', $booking->bookingID) }}" 
                                                           target="_blank" class="text-primary">
                                                            <i class="bi bi-file-pdf"></i> View Invoice
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No invoice</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($latestPayment && $latestPayment->transaction_reference)
                                                <a href="{{ asset('storage/' . $latestPayment->transaction_reference) }}" 
                                                   target="_blank" class="text-primary">
                                                    <i class="bi bi-image"></i> View Receipt
                                                </a>
                                                <div class="reservation-info-text">
                                                    <div><strong>Payment ID:</strong> {{ $latestPayment->paymentID ?? 'N/A' }}</div>
                                                    <div><strong>Date:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M Y') : 'N/A' }}</div>
                                                    <div><strong>Time:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('H:i') : 'N/A' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">No receipt</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $paymentStatus === 'Full' ? 'bg-success' : ($paymentStatus === 'Deposit' ? 'bg-warning text-dark' : ($paymentStatus === 'Refunded' ? 'bg-info' : 'bg-secondary')) }}">
                                                {{ $paymentStatus }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="reservation-info-text">
                                                <div><strong>Date:</strong> {{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</div>
                                                <div><strong>Time:</strong> {{ $booking->pickup_time ?? 'N/A' }}</div>
                                                <div><strong>Location:</strong> {{ $booking->pickup_point ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="reservation-info-text">
                                                <div><strong>Date:</strong> {{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</div>
                                                <div><strong>Time:</strong> {{ $booking->return_time ?? 'N/A' }}</div>
                                                <div><strong>Location:</strong> {{ $booking->return_point ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm booking-status-select" 
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    data-current-status="{{ $booking->booking_status }}">
                                                @foreach($bookingStatuses as $status)
                                                    <option value="{{ $status }}" {{ $booking->booking_status === $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            {{ $booking->duration ?? 0 }} days
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm served-by-select" 
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    data-current-served="{{ $booking->staff_served }}">
                                                <option value="">Not Assigned</option>
                                                @foreach($staffUsers as $staff)
                                                    <option value="{{ $staff->userID }}" {{ $booking->staff_served == $staff->userID ? 'selected' : '' }}>
                                                        {{ $staff->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @php
                                                $vehicle = $booking->vehicle;
                                                $contractDoc = $vehicle->documents->where('document_type', 'contract')->first() ?? null;
                                            @endphp
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.bookings.reservations') }}?search={{ $booking->bookingID }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                @if($contractDoc && $contractDoc->fileURL)
                                                    <a href="{{ asset('storage/' . $contractDoc->fileURL) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View Contract">
                                                        <i class="bi bi-file-pdf"></i> View Detail
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No contract available">
                                                        <i class="bi bi-file-pdf"></i> View Detail
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 p-3">
                        {{ $bookings->links() }}
                    </div>
                @else
                    <div class="alert alert-info m-3">
                        No vehicle leasing bookings found (bookings longer than 15 days).
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.booking-status-select').forEach(select => {
            select.addEventListener('change', function() {
                let bookingId = this.dataset.bookingId;
                let newStatus = this.value;
                let oldStatus = this.dataset.currentStatus;
                
                if (oldStatus == newStatus) return;
                
                fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ booking_status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.currentStatus = newStatus;
                        location.reload();
                    } else {
                        alert('Failed to update booking status.');
                        this.value = oldStatus;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating booking status.');
                    this.value = oldStatus;
                });
            });
        });

        document.querySelectorAll('.served-by-select').forEach(select => {
            select.addEventListener('change', function() {
                let bookingId = this.dataset.bookingId;
                let newServedBy = this.value;
                let oldServedBy = this.dataset.currentServed;
                
                if (oldServedBy == newServedBy) return;
                
                fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ staff_served: newServedBy || null })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.dataset.currentServed = newServedBy;
                        location.reload();
                    } else {
                        alert('Failed to update served by.');
                        this.value = oldServedBy;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating served by.');
                    this.value = oldServedBy;
                });
            });
        });
    </script>
</body>
</html>


