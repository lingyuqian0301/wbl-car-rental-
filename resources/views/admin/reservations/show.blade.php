@extends('layouts.admin')

@section('title', 'Booking Details #' . $booking->bookingID)

@push('styles')
<style>
    .booking-detail-header {
        background: linear-gradient(135deg, var(--admin-red) 0%, var(--admin-red-dark) 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    .booking-detail-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }
    .booking-detail-header .booking-meta {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }
    .booking-detail-header .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .booking-detail-header .meta-item i {
        font-size: 1.2rem;
    }
    .dynamic-tabs {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .nav-tabs {
        border-bottom: 2px solid var(--admin-red);
        padding: 0 1rem;
        background: #f8f9fa;
    }
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: var(--admin-red);
        background: rgba(220, 53, 69, 0.05);
    }
    .nav-tabs .nav-link.active {
        background: white;
        color: var(--admin-red);
        border-bottom-color: var(--admin-red);
        font-weight: 600;
    }
    .tab-content {
        padding: 2rem;
    }
    .info-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
    }
    .info-card h5 {
        color: var(--admin-red);
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 0.5rem;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .info-value {
        color: #212529;
        font-size: 0.95rem;
    }
    .badge-status {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .condition-form-section {
        margin-bottom: 2rem;
    }
    .condition-form-section h6 {
        color: var(--admin-red);
        margin-bottom: 1rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="booking-detail-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>Booking Details #{{ $booking->bookingID }}</h1>
                <div class="booking-meta">
                    <div class="meta-item">
                        <i class="bi bi-person"></i>
                        <span>{{ $booking->customer->user->name ?? 'Unknown Customer' }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-car-front"></i>
                        <span>{{ $booking->vehicle->vehicle_brand ?? '' }} {{ $booking->vehicle->vehicle_model ?? '' }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-calendar-check"></i>
                        <span>{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="badge-status {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ $booking->booking_status }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Reservations
            </a>
        </div>
    </div>

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

    <!-- Dynamic Tabs -->
    <div class="dynamic-tabs">
        <ul class="nav nav-tabs" id="bookingDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'booking-info' ? 'active' : '' }}" 
                        id="booking-info-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#booking-info" 
                        type="button" 
                        role="tab"
                        aria-controls="booking-info"
                        aria-selected="{{ $activeTab === 'booking-info' ? 'true' : 'false' }}"
                        onclick="updateUrl('booking-info')">
                    <i class="bi bi-info-circle"></i> Booking Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'payment' ? 'active' : '' }}" 
                        id="payment-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#payment" 
                        type="button" 
                        role="tab"
                        aria-controls="payment"
                        aria-selected="{{ $activeTab === 'payment' ? 'true' : 'false' }}"
                        onclick="updateUrl('payment')">
                    <i class="bi bi-credit-card"></i> Payment
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'pickup-condition' ? 'active' : '' }}" 
                        id="pickup-condition-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#pickup-condition" 
                        type="button" 
                        role="tab"
                        aria-controls="pickup-condition"
                        aria-selected="{{ $activeTab === 'pickup-condition' ? 'true' : 'false' }}"
                        onclick="updateUrl('pickup-condition')">
                    <i class="bi bi-calendar-check"></i> Pickup Condition Form
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'return-condition' ? 'active' : '' }}" 
                        id="return-condition-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#return-condition" 
                        type="button" 
                        role="tab"
                        aria-controls="return-condition"
                        aria-selected="{{ $activeTab === 'return-condition' ? 'true' : 'false' }}"
                        onclick="updateUrl('return-condition')">
                    <i class="bi bi-calendar-x"></i> Return Condition Form
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'review' ? 'active' : '' }}" 
                        id="review-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#review" 
                        type="button" 
                        role="tab"
                        aria-controls="review"
                        aria-selected="{{ $activeTab === 'review' ? 'true' : 'false' }}"
                        onclick="updateUrl('review')">
                    <i class="bi bi-star"></i> Review
                </button>
            </li>
        </ul>

        <div class="tab-content" id="bookingDetailTabContent">
            <!-- Booking Info Tab -->
            <div class="tab-pane fade {{ $activeTab === 'booking-info' ? 'show active' : '' }}" 
                 id="booking-info" 
                 role="tabpanel" 
                 aria-labelledby="booking-info-tab">
                <div class="info-card">
                    <h5><i class="bi bi-info-circle"></i> Booking Information</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Booking ID</div>
                            <div class="info-value"><strong>#{{ $booking->bookingID }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Booking Status</div>
                            <div class="info-value">
                                <span class="badge-status {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="info-label">Served By</div>
                            <div class="info-value">{{ $staffServed->name ?? 'Not Assigned' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">
                                {{ $booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y, H:i') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-person"></i> Customer Information</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Customer Name</div>
                            <div class="info-value"><strong>{{ $booking->customer->user->name ?? 'Unknown' }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $booking->customer->user->email ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">{{ $booking->customer->phone_number ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Customer ID</div>
                            <div class="info-value">#{{ $booking->customerID }}</div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-car-front"></i> Vehicle Information</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Vehicle Brand</div>
                            <div class="info-value"><strong>{{ $booking->vehicle->vehicle_brand ?? 'N/A' }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Vehicle Model</div>
                            <div class="info-value">{{ $booking->vehicle->vehicle_model ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Plate Number</div>
                            <div class="info-value"><strong>{{ $booking->vehicle->plate_number ?? 'N/A' }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Vehicle Type</div>
                            <div class="info-value">{{ $booking->vehicle->vehicleType ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Rental Price (per day)</div>
                            <div class="info-value">RM {{ number_format($booking->vehicle->rental_price ?? 0, 2) }}</div>
                        </div>
                        <div>
                            <div class="info-label">Vehicle Status</div>
                            <div class="info-value">
                                <span class="badge-status {{ $booking->vehicle->availability_status === 'available' ? 'bg-success' : ($booking->vehicle->availability_status === 'rented' ? 'bg-info' : ($booking->vehicle->availability_status === 'maintenance' ? 'bg-warning text-dark' : 'bg-secondary')) }}">
                                    {{ ucfirst($booking->vehicle->availability_status ?? 'N/A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-calendar-range"></i> Rental Period</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Rental Start Date</div>
                            <div class="info-value">
                                <strong>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div>
                            <div class="info-label">Rental End Date</div>
                            <div class="info-value">
                                <strong>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div>
                            <div class="info-label">Duration</div>
                            <div class="info-value"><strong>{{ $booking->duration ?? 0 }} days</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Pickup Location</div>
                            <div class="info-value">{{ $booking->pickup_point ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="info-label">Return Location</div>
                            <div class="info-value">{{ $booking->return_point ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-cash-stack"></i> Pricing Information</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Rental Amount</div>
                            <div class="info-value"><strong>RM {{ number_format($booking->rental_amount ?? 0, 2) }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Deposit Amount</div>
                            <div class="info-value"><strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Total Amount</div>
                            <div class="info-value"><strong>RM {{ number_format($totalRequired, 2) }}</strong></div>
                        </div>
                        @if($booking->additionalCharges)
                        <div>
                            <div class="info-label">Additional Charges</div>
                            <div class="info-value"><strong>RM {{ number_format($booking->additionalCharges->total_extra_charge ?? 0, 2) }}</strong></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Tab -->
            <div class="tab-pane fade {{ $activeTab === 'payment' ? 'show active' : '' }}" 
                 id="payment" 
                 role="tabpanel" 
                 aria-labelledby="payment-tab">
                <div class="info-card">
                    <h5><i class="bi bi-credit-card"></i> Payment Summary</h5>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Total Required</div>
                            <div class="info-value"><strong>RM {{ number_format($totalRequired, 2) }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Total Paid</div>
                            <div class="info-value" style="color: green;"><strong>RM {{ number_format($totalPaid, 2) }}</strong></div>
                        </div>
                        <div>
                            <div class="info-label">Outstanding Balance</div>
                            <div class="info-value" style="color: {{ $outstandingBalance > 0 ? 'red' : 'green' }};">
                                <strong>RM {{ number_format($outstandingBalance, 2) }}</strong>
                            </div>
                        </div>
                        <div>
                            <div class="info-label">Payment Status</div>
                            <div class="info-value">
                                <span class="badge-status {{ $outstandingBalance == 0 ? 'bg-success' : ($totalPaid > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                    {{ $outstandingBalance == 0 ? 'Full Payment' : ($totalPaid > 0 ? 'Partial Payment' : 'Unpaid') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="bi bi-list-ul"></i> Payment History</h5>
                    @if($booking->payments && $booking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Verify By</th>
                                        <th>Transaction Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($booking->payments as $payment)
                                        <tr>
                                            <td><strong>#{{ $payment->paymentID }}</strong></td>
                                            <td>
                                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y, H:i') : 'N/A' }}
                                            </td>
                                            <td><strong>RM {{ number_format($payment->total_amount ?? 0, 2) }}</strong></td>
                                            <td>
                                                <span class="badge-status {{ $payment->payment_status === 'Verified' ? 'bg-success' : ($payment->payment_status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                    {{ $payment->payment_status ?? 'Pending' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($payment->verify_by)
                                                    {{ \App\Models\User::find($payment->verify_by)->name ?? 'N/A' }}
                                                @else
                                                    <span class="text-muted">Not Verified</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->transaction_reference)
                                                    @php
                                                        $receiptPath = $payment->transaction_reference;
                                                        $isImagePath = str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/') || str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf');
                                                        if ($isImagePath) {
                                                            $imageUrl = str_starts_with($receiptPath, 'uploads/') ? asset($receiptPath) : asset('storage/' . $receiptPath);
                                                        } else {
                                                            $imageUrl = null;
                                                        }
                                                    @endphp
                                                    @if($imageUrl)
                                                        <a href="{{ $imageUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-image"></i> View Receipt
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">{{ strlen($payment->transaction_reference) > 20 ? substr($payment->transaction_reference, 0, 20) . '...' : $payment->transaction_reference }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->payment_isVerify || $payment->payment_status === 'Verified')
                                                    <a href="{{ route('admin.payments.invoice', $payment->paymentID) }}" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="bi bi-file-pdf"></i> Invoice
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No payment records found for this booking.</p>
                    @endif
                </div>
            </div>

            <!-- Pickup Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'pickup-condition' ? 'show active' : '' }}" 
                 id="pickup-condition" 
                 role="tabpanel" 
                 aria-labelledby="pickup-condition-tab">
                <div class="condition-form-section">
                    <div class="info-card">
                        <h5><i class="bi bi-calendar-check"></i> Pickup Vehicle Condition Form</h5>
                        <p class="text-muted">This form records the vehicle condition at the time of pickup.</p>
                        
                        @if($booking->rental_start_date && \Carbon\Carbon::parse($booking->rental_start_date)->isPast())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Pickup date: {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') }}
                            </div>
                        @endif
                        
                        <!-- TODO: Display pickup condition form data when available -->
                        <p class="text-muted">Pickup condition form will be displayed here when available.</p>
                    </div>
                </div>
            </div>

            <!-- Return Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'return-condition' ? 'show active' : '' }}" 
                 id="return-condition" 
                 role="tabpanel" 
                 aria-labelledby="return-condition-tab">
                <div class="condition-form-section">
                    <div class="info-card">
                        <h5><i class="bi bi-calendar-x"></i> Return Vehicle Condition Form</h5>
                        <p class="text-muted">This form records the vehicle condition at the time of return.</p>
                        
                        @if($booking->rental_end_date && \Carbon\Carbon::parse($booking->rental_end_date)->isPast())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Return date: {{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') }}
                            </div>
                        @endif
                        
                        <!-- TODO: Display return condition form data when available -->
                        <p class="text-muted">Return condition form will be displayed here when available.</p>
                    </div>
                </div>
            </div>

            <!-- Review Tab -->
            <div class="tab-pane fade {{ $activeTab === 'review' ? 'show active' : '' }}" 
                 id="review" 
                 role="tabpanel" 
                 aria-labelledby="review-tab">
                <div class="info-card">
                    <h5><i class="bi bi-star"></i> Customer Review</h5>
                    @if($booking->review)
                        <div class="review-content">
                            <div class="info-row">
                                <div>
                                    <div class="info-label">Rating</div>
                                    <div class="info-value">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= ($booking->review->rating ?? 0) ? '-fill' : '' }} text-warning"></i>
                                        @endfor
                                        <span class="ms-2">({{ $booking->review->rating ?? 0 }}/5)</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="info-label">Review Date</div>
                                    <div class="info-value">
                                        {{ $booking->review->review_date ? \Carbon\Carbon::parse($booking->review->review_date)->format('d M Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="info-label">Comment</div>
                                <div class="info-value mt-2" style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                                    {{ $booking->review->comment ?? 'No comment provided.' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">No review has been submitted for this booking yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update URL without page reload when tab is clicked
    function updateUrl(tab) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'booking-info';
        const tabButton = document.querySelector(`#${tab}-tab`);
        if (tabButton) {
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
        }
    });

    // Initialize active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'booking-info';
        const tabButton = document.querySelector(`#${tab}-tab`);
        if (tabButton && '{{ $activeTab }}' !== tab) {
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
        }
    });
</script>
@endsection


@endsection

