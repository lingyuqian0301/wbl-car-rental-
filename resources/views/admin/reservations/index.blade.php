@extends('layouts.admin')

@section('title', 'Reservations')

@push('styles')
<style>
    .reservation-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .reservation-info-text div {
        margin-bottom: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? 'bookings') === 'bookings' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                <i class="bi bi-calendar-check"></i> Bookings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? '') === 'leasing' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#leasing" type="button" role="tab">
                <i class="bi bi-file-earmark-text"></i> Leasing
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Bookings Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? 'bookings') === 'bookings' ? 'show active' : '' }}" id="bookings" role="tabpanel">
            <x-admin-page-header 
                title="Reservations" 
                description="Manage all booking reservations"
                :stats="[
                    ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
                    ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
                    ['label' => 'Confirmed', 'value' => $totalConfirmed, 'icon' => 'bi-check-circle'],
                    ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
                ]"
                :date="$today"
            />

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings.reservations', ['tab' => 'bookings']) }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="form-control form-control-sm" 
                           placeholder="Booking ID, Plate No, Customer Name">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="">Default (Desc Booking ID)</option>
                        <option value="booking_date_desc" {{ ($sort ?? '') === 'booking_date_desc' ? 'selected' : '' }}>Desc Booking Date</option>
                        <option value="pickup_date_desc" {{ ($sort ?? '') === 'pickup_date_desc' ? 'selected' : '' }}>Desc Pickup Date</option>
                    </select>
                </div>
                
                <!-- Plate No Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Plate No</label>
                    <select name="filter_plate_no" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($plateNumbers as $plate)
                            <option value="{{ $plate }}" {{ $filterPlateNo === $plate ? 'selected' : '' }}>{{ $plate }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Pickup Date Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Pickup Date</label>
                    <input type="date" name="filter_pickup_date" value="{{ $filterPickupDate }}" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Return Date Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Return Date</label>
                    <input type="date" name="filter_return_date" value="{{ $filterReturnDate }}" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Served By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Served By</label>
                    <select name="filter_served_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($staffUsers as $staff)
                            <option value="{{ $staff->userID }}" {{ $filterServedBy == $staff->userID ? 'selected' : '' }}>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Booking Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Booking Status</label>
                    <select name="filter_booking_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($bookingStatuses as $status)
                            <option value="{{ $status }}" {{ $filterBookingStatus === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Payment Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Status</label>
                    <select name="filter_payment_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($paymentStatuses ?? [] as $status)
                            <option value="{{ $status }}" {{ $filterPaymentStatus === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.bookings.reservations', ['tab' => 'bookings']) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Reservations</h5>
            <span class="badge bg-light text-dark">{{ $bookings->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Vehicle Plate No</th>
                            <th>Payment Price</th>
                            <th>Invoice No</th>
                            <th>Payment ID</th>
                            <th>Payment Status</th>
                            <th>Pickup Detail</th>
                            <th>Return Detail</th>
                            <th>Booking Status</th>
                            <th>Served By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            @php
                                $customer = $booking->customer;
                                $user = $customer->user ?? null;
                                $vehicle = $booking->vehicle;
                                $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                                $invoice = $booking->invoice;
                                
                                // Calculate payment status
                                $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                $totalAmount = $booking->deposit_amount + $booking->rental_amount;
                                $paymentStatus = 'Deposit';
                                if ($totalPaid >= $totalAmount) {
                                    $paymentStatus = 'Full';
                                } elseif ($totalPaid > 0) {
                                    $paymentStatus = 'Deposit';
                                } else {
                                    $paymentStatus = 'Unpaid';
                                }
                                
                                // Check if refunded
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
                                    @if($latestPayment)
                                        <strong>{{ $latestPayment->paymentID ?? 'N/A' }}</strong>
                                        <div class="reservation-info-text">
                                            <div>
                                                @if($latestPayment->transaction_reference)
                                                    <a href="{{ asset('storage/' . $latestPayment->transaction_reference) }}" 
                                                       target="_blank" class="text-primary">
                                                        <i class="bi bi-image"></i> View Receipt
                                                    </a>
                                                @else
                                                    <span class="text-muted">No receipt</span>
                                                @endif
                                            </div>
                                            <div><strong>Date:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M Y') : 'N/A' }}</div>
                                            <div><strong>Time:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('H:i') : 'N/A' }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">No payment</span>
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
                                        <div><strong>Location:</strong> {{ $booking->pickup_point ?? $booking->pickup_location ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="reservation-info-text">
                                        <div><strong>Date:</strong> {{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</div>
                                        <div><strong>Time:</strong> {{ $booking->return_time ?? 'N/A' }}</div>
                                        <div><strong>Location:</strong> {{ $booking->return_point ?? $booking->return_location ?? 'N/A' }}</div>
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
                                    <div class="d-flex gap-1 flex-column">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewReview({{ $booking->bookingID }})" 
                                                title="View Review">
                                            <i class="bi bi-star"></i> Review
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewVehicleCondition({{ $booking->bookingID }})" 
                                                title="View Vehicle Condition">
                                            <i class="bi bi-clipboard-check"></i> Condition
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No reservations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bookings->hasPages())
            <div class="card-footer">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
        </div>

        <!-- Leasing Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'leasing' ? 'show active' : '' }}" id="leasing" role="tabpanel">
            <x-admin-page-header 
                title="Vehicle Leasing" 
                description="Manage vehicle leasing bookings (more than 15 days)"
                :stats="[
                    ['label' => 'Total Bookings', 'value' => $leasingStats['totalBookings'] ?? 0, 'icon' => 'bi-calendar'],
                    ['label' => 'Total Revenue', 'value' => 'RM ' . number_format($leasingStats['totalRevenue'] ?? 0, 2), 'icon' => 'bi-currency-dollar'],
                    ['label' => 'Total Paid', 'value' => 'RM ' . number_format($leasingStats['totalPaid'] ?? 0, 2), 'icon' => 'bi-check-circle'],
                    ['label' => 'Ongoing', 'value' => $leasingStats['ongoingBookings'] ?? 0, 'icon' => 'bi-clock']
                ]"
                :date="$today"
            />

            <!-- Filters for Leasing -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bookings.reservations', ['tab' => 'leasing']) }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                                <option value="future" {{ ($statusFilter ?? '') === 'future' ? 'selected' : '' }}>Future</option>
                                <option value="ongoing" {{ ($statusFilter ?? '') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="past" {{ ($statusFilter ?? '') === 'past' ? 'selected' : '' }}>Past</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                            @if($statusFilter && $statusFilter !== 'all')
                                <a href="{{ route('admin.bookings.reservations', ['tab' => 'leasing']) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leasing Bookings Table -->
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vehicle Leasing Bookings</h5>
                    <span class="badge bg-light text-dark">{{ $leasingBookings->total() }} total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer Name</th>
                                    <th>Vehicle Plate No</th>
                                    <th>Duration</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Amount</th>
                                    <th>Booking Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leasingBookings as $booking)
                                    @php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                        $vehicle = $booking->vehicle;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID, 'tab' => 'bookings']) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $booking->bookingID }}
                                            </a>
                                        </td>
                                        <td>{{ $user->name ?? 'Unknown' }}</td>
                                        <td><strong>{{ $vehicle->plate_number ?? 'N/A' }}</strong></td>
                                        <td><strong>{{ $booking->duration ?? 0 }} days</strong></td>
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</td>
                                        <td><strong>RM {{ number_format(($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0), 2) }}</strong></td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : 'bg-info') }}">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID, 'tab' => 'bookings']) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No leasing bookings found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($leasingBookings->hasPages())
                    <div class="card-footer">
                        {{ $leasingBookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewContent">
                Loading...
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Condition Modal -->
<div class="modal fade" id="vehicleConditionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Condition Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="vehicleConditionContent">
                Loading...
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle tab switching from URL parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab === 'leasing') {
            const leasingTab = document.querySelector('button[data-bs-target="#leasing"]');
            const bookingsTab = document.querySelector('button[data-bs-target="#bookings"]');
            if (leasingTab && bookingsTab) {
                bookingsTab.classList.remove('active');
                leasingTab.classList.add('active');
                document.getElementById('bookings').classList.remove('show', 'active');
                document.getElementById('leasing').classList.add('show', 'active');
            }
        }
    });

    // Update Booking Status
    document.querySelectorAll('.booking-status-select').forEach(select => {
        select.addEventListener('change', function() {
            let bookingId = this.dataset.bookingId;
            let newStatus = this.value;
            let oldStatus = this.dataset.currentStatus;
            
            if (oldStatus === newStatus) {
                return;
            }
            
            if (!confirm(`Are you sure you want to change booking status from "${oldStatus}" to "${newStatus}"?`)) {
                this.value = oldStatus;
                return;
            }
            
            fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    booking_status: newStatus
                })
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

    // Update Served By
    document.querySelectorAll('.served-by-select').forEach(select => {
        select.addEventListener('change', function() {
            let bookingId = this.dataset.bookingId;
            let newServedBy = this.value;
            let oldServedBy = this.dataset.currentServed;
            
            if (oldServedBy == newServedBy) {
                return;
            }
            
            fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    staff_served: newServedBy || null
                })
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

    // View Review
    function viewReview(bookingId) {
        // TODO: Implement review form view
        document.getElementById('reviewContent').innerHTML = '<p>Review form for booking #' + bookingId + ' will be displayed here.</p>';
        new bootstrap.Modal(document.getElementById('reviewModal')).show();
    }

    // View Vehicle Condition
    function viewVehicleCondition(bookingId) {
        // TODO: Implement vehicle condition form view
        document.getElementById('vehicleConditionContent').innerHTML = '<p>Pickup and return vehicle condition form for booking #' + bookingId + ' will be displayed here.</p>';
        new bootstrap.Modal(document.getElementById('vehicleConditionModal')).show();
    }
</script>
@endpush
@endsection
