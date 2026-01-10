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
        padding: 0;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .info-card .card-header {
        background: var(--admin-red);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px 8px 0 0;
        border-bottom: none;
    }
    .info-card .card-header h5 {
        color: white;
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .info-card .card-body {
        padding: 1.5rem;
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
                <button class="nav-link {{ $activeTab === 'booking-detail' ? 'active' : '' }}" 
                        id="booking-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#booking-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="booking-detail"
                        aria-selected="{{ $activeTab === 'booking-detail' ? 'true' : 'false' }}"
                        onclick="updateUrl('booking-detail')">
                    <i class="bi bi-info-circle"></i> Booking Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'vehicle-detail' ? 'active' : '' }}" 
                        id="vehicle-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#vehicle-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="vehicle-detail"
                        aria-selected="{{ $activeTab === 'vehicle-detail' ? 'true' : 'false' }}"
                        onclick="updateUrl('vehicle-detail')">
                    <i class="bi bi-car-front"></i> Vehicle Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'customer-detail' ? 'active' : '' }}" 
                        id="customer-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#customer-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="customer-detail"
                        aria-selected="{{ $activeTab === 'customer-detail' ? 'true' : 'false' }}"
                        onclick="updateUrl('customer-detail')">
                    <i class="bi bi-person"></i> Customer Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'payment-detail' ? 'active' : '' }}" 
                        id="payment-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#payment-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="payment-detail"
                        aria-selected="{{ $activeTab === 'payment-detail' ? 'true' : 'false' }}"
                        onclick="updateUrl('payment-detail')">
                    <i class="bi bi-credit-card"></i> Payment Detail
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
                <button class="nav-link {{ $activeTab === 'transaction-detail' ? 'active' : '' }}" 
                        id="transaction-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#transaction-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="transaction-detail"
                        aria-selected="{{ $activeTab === 'transaction-detail' ? 'true' : 'false' }}"
                        onclick="updateUrl('transaction-detail')">
                    <i class="bi bi-receipt"></i> Transaction Detail
                </button>
            </li>
        </ul>

        <div class="tab-content" id="bookingDetailTabContent">
            <!-- Booking Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'booking-detail' ? 'show active' : '' }}" 
                 id="booking-detail" 
                 role="tabpanel" 
                 aria-labelledby="booking-detail-tab">
                <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Booking Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking ID:</dt>
                                    <dd class="d-inline ms-2"><strong>#{{ $booking->bookingID }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking Status:</dt>
                                    <dd class="d-inline ms-2">
                                <span class="badge-status {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                    {{ $booking->booking_status }}
                                </span>
                                    </dd>
                            </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Served By:</dt>
                                    <dd class="d-inline ms-2">{{ $staffServed->name ?? 'Not Assigned' }}</dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Last Updated:</dt>
                                    <dd class="d-inline ms-2">
                                {{ $booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y, H:i') : 'N/A' }}
                                    </dd>
                            </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Rental Period</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental Start Date:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental End Date:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Duration:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->duration ?? 0 }} days</strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Pickup Location:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->pickup_point ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Return Location:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->return_point ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Pricing Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental Amount:</dt>
                                    <dd class="d-inline ms-2"><strong>RM {{ number_format($booking->rental_amount ?? 0, 2) }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Deposit Amount:</dt>
                                    <dd class="d-inline ms-2"><strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Total Amount:</dt>
                                    <dd class="d-inline ms-2"><strong>RM {{ number_format($totalRequired, 2) }}</strong></dd>
                                </div>
                                @if($booking->additionalCharges)
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Additional Charges:</dt>
                                    <dd class="d-inline ms-2"><strong>RM {{ number_format($booking->additionalCharges->total_extra_charge ?? 0, 2) }}</strong></dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

            <!-- Vehicle Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'vehicle-detail' ? 'show active' : '' }}" 
                 id="vehicle-detail" 
                 role="tabpanel" 
                 aria-labelledby="vehicle-detail-tab">
                <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h5>
                            <a href="{{ route('admin.vehicles.show', $booking->vehicle->vehicleID) }}" class="btn btn-sm btn-light" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> View Full Vehicle Details
                            </a>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle ID:</dt>
                                    <dd class="d-inline ms-2"><strong>#{{ $booking->vehicle->vehicleID ?? 'N/A' }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Brand:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->vehicle->vehicle_brand ?? 'N/A' }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Model:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->vehicle->vehicle_model ?? 'N/A' }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Plate Number:</dt>
                                    <dd class="d-inline ms-2"><strong>{{ $booking->vehicle->plate_number ?? 'N/A' }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Type:</dt>
                                    <dd class="d-inline ms-2">{{ ucfirst($booking->vehicle->vehicleType ?? 'N/A') }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental Price (per day):</dt>
                                    <dd class="d-inline ms-2"><strong>RM {{ number_format($booking->vehicle->rental_price ?? 0, 2) }}</strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Manufacturing Year:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->manufacturing_year ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Color:</dt>
                                    <dd class="d-inline ms-2">{{ ucfirst($booking->vehicle->color ?? 'N/A') }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Engine Capacity:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->engineCapacity ?? 'N/A' }} L</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge-status {{ $booking->vehicle->availability_status === 'available' ? 'bg-success' : ($booking->vehicle->availability_status === 'rented' ? 'bg-info' : ($booking->vehicle->availability_status === 'maintenance' ? 'bg-warning text-dark' : 'bg-secondary')) }}">
                                            {{ ucfirst($booking->vehicle->availability_status ?? 'N/A') }}
                                        </span>
                                    </dd>
                                </div>
                                @if($booking->vehicle->car)
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Car Type:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->car->car_type ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Seating Capacity:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->car->seating_capacity ?? 'N/A' }} seats</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Transmission:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->car->transmission ?? 'N/A' }}</dd>
                                </div>
                                @endif
                                @if($booking->vehicle->motorcycle)
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Motor Type:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->vehicle->motorcycle->motor_type ?? 'N/A' }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
                @if($booking->vehicle->owner)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
                    </div>
                        <div class="card-body">
                            @if($booking->vehicle->owner)
                            <div class="row">
                                <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Owner ID:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->ownerID ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Owner Name:</dt>
                                            <dd class="col-7">
                                                @php
                                                    $ownerName = 'N/A';
                                                    if ($booking->vehicle->owner && $booking->vehicle->owner->personDetails) {
                                                        $ownerName = $booking->vehicle->owner->personDetails->fullname ?? 'N/A';
                                                    }
                                                @endphp
                                                {{ $ownerName }}
                                            </dd>
                                            
                                            <dt class="col-5">IC No:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->ic_no ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Contact:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->contact_number ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Email:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->email ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Bank Name:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->bankname ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Bank Account No:</dt>
                                            <dd class="col-7">{{ $booking->vehicle->owner->bank_acc_number ?? 'N/A' }}</dd>
                                        </dl>
                </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Registration Date:</dt>
                                            <dd class="col-7">
                                                @if($booking->vehicle->owner->registration_date)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($booking->vehicle->owner->registration_date)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $booking->vehicle->owner->registration_date }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Leasing Price:</dt>
                                            <dd class="col-7">RM {{ number_format($booking->vehicle->owner->leasing_price ?? 0, 2) }}</dd>
                                            
                                            <dt class="col-5">Leasing Due Date:</dt>
                                            <dd class="col-7">
                                                @if($booking->vehicle->owner->leasing_due_date)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($booking->vehicle->owner->leasing_due_date)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $booking->vehicle->owner->leasing_due_date }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">License Expiry Date:</dt>
                                            <dd class="col-7">
                                                @if($booking->vehicle->owner->license_expirydate)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($booking->vehicle->owner->license_expirydate)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $booking->vehicle->owner->license_expirydate }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Status:</dt>
                                            <dd class="col-7">
                                                <span class="badge {{ ($booking->vehicle->owner->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ($booking->vehicle->owner->isActive ?? false) ? 'Active' : 'Inactive' }}
                                                </span>
                                            </dd>
                                        </dl>
                        </div>
                        </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No owner information available for this vehicle.
                        </div>
                            @endif
                        </div>
                    </div>
                        </div>
                        @endif
                    </div>
                </div>

            <!-- Customer Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'customer-detail' ? 'show active' : '' }}" 
                 id="customer-detail" 
                 role="tabpanel" 
                 aria-labelledby="customer-detail-tab">
                <!-- User Info Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Info</h5>
                            <a href="{{ route('admin.customers.show', ['customer' => $booking->customer->customerID]) }}" class="btn btn-sm btn-light" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> View Full Customer Details
                            </a>
            </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">User ID:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->userID ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Username:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->username ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Email:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->email ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Phone:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->phone ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Name:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->name ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Last Login:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->lastLogin ? \Carbon\Carbon::parse($booking->customer->user->lastLogin)->format('d M Y H:i') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date Registered:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->dateRegistered ? \Carbon\Carbon::parse($booking->customer->user->dateRegistered)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date of Birth:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->user->DOB ? \Carbon\Carbon::parse($booking->customer->user->DOB)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Age:</dt>
                                    <dd class="d-inline ms-2">
                                        @if($booking->customer->user->DOB)
                                            {{ \Carbon\Carbon::parse($booking->customer->user->DOB)->age }} years
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge {{ ($booking->customer->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($booking->customer->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                </div>
            </div>

                <!-- Customer Detail Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Customer Detail</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Type:</dt>
                                    <dd class="d-inline ms-2">
                                        @if($booking->customer->local)
                                            Local
                                        @elseif($booking->customer->international)
                                            International
                                        @else
                                            N/A
                                        @endif
                                        @if($booking->customer->localStudent || $booking->customer->internationalStudent)
                                            / Student
                                        @elseif($booking->customer->localUtmStaff || $booking->customer->internationalUtmStaff)
                                            / Staff
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">{{ $booking->customer->local ? 'IC No' : 'Passport No' }}:</dt>
                                    <dd class="d-inline ms-2">
                                        {{ $booking->customer->local->ic_no ?? ($booking->customer->international->passport_no ?? 'N/A') }}
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">{{ $booking->customer->local ? 'State of Origin' : 'Country of Origin' }}:</dt>
                                    <dd class="d-inline ms-2">
                                        {{ $booking->customer->local->stateOfOrigin ?? ($booking->customer->international->countryOfOrigin ?? 'N/A') }}
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Address:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->address ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">License Expiry Date:</dt>
                                    <dd class="d-inline ms-2">
                                        @if($booking->customer->customer_license)
                                            {{ \Carbon\Carbon::parse($booking->customer->customer_license)->format('d M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Emergency Contact:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->emergency_contact ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Bank Name:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->default_bank_name ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Account No:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->default_account_no ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking Times:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->customer->bookings->count() ?? 0 }}</dd>
                                </div>
                                @if($booking->customer->localStudent || $booking->customer->internationalStudent)
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Student Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Matric Number:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localStudent->matric_number ?? ($booking->customer->internationalStudent->matric_number ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localStudent->studentDetails->college ?? ($booking->customer->internationalStudent->studentDetails->college ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Faculty:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localStudent->studentDetails->faculty ?? ($booking->customer->internationalStudent->studentDetails->faculty ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Programme:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localStudent->studentDetails->programme ?? ($booking->customer->internationalStudent->studentDetails->programme ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Year of Study:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localStudent->studentDetails->yearOfStudy ?? ($booking->customer->internationalStudent->studentDetails->yearOfStudy ?? 'N/A') }}
                                        </dd>
                                    </div>
                                </div>
                                @elseif($booking->customer->localUtmStaff || $booking->customer->internationalUtmStaff)
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Staff Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Staff No:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localUtmStaff->staffID ?? ($booking->customer->internationalUtmStaff->staffID ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Position:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localUtmStaff->staffDetails->position ?? ($booking->customer->internationalUtmStaff->staffDetails->position ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $booking->customer->localUtmStaff->staffDetails->college ?? ($booking->customer->internationalUtmStaff->staffDetails->college ?? 'N/A') }}
                                        </dd>
                                    </div>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
                <br>

                <!-- Documentation Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                        </div>
                        <div class="card-body">
                        <div class="row g-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- License Pic -->
                            <div class="col-md-6">
                                <div class="card document-cell h-100" style="border: 1px solid #e5e7eb;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-card-text fs-1 d-block mb-2" style="color: var(--admin-red);"></i>
                                        <h6 class="fw-semibold">License</h6>
                                        @php
                                            $licenseImg = $booking->customer->customer_license_img ?? null;
                                        @endphp
                                        @if($licenseImg)
                                            <div class="mb-2">
                                                <img src="{{ getFileUrl($licenseImg) }}" 
                                                     alt="License" 
                                                     class="img-fluid mb-2" 
                                                     style="max-height: 150px; border-radius: 6px;">
                                            </div>
                                            <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                <button type="button" 
                                                        class="btn btn-sm" 
                                                        style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewCustomerLicenseModal">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </div>
                                            
                                            <!-- View License Modal -->
                                            <div class="modal fade" id="viewCustomerLicenseModal" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Customer License</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center" style="min-height: 400px;">
                                                            <img src="{{ getFileUrl($licenseImg) }}" 
                                                                 alt="License Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="{{ getFileUrl($licenseImg) }}" 
                                                               target="_blank" 
                                                               class="btn btn-primary">
                                                                <i class="bi bi-download"></i> Open in New Tab
                                                            </a>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="small text-muted mb-2">No license image uploaded</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- IC/Passport Pic -->
                            <div class="col-md-6">
                                <div class="card document-cell h-100" style="border: 1px solid #e5e7eb;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--admin-red);"></i>
                                        <h6 class="fw-semibold">{{ $booking->customer->local ? 'IC' : 'Passport' }}</h6>
                                        @php
                                            $icImg = $booking->customer->customer_ic_img ?? null;
                                        @endphp
                                        @if($icImg)
                                            <div class="mb-2">
                                                <img src="{{ getFileUrl($icImg) }}" 
                                                     alt="{{ $booking->customer->local ? 'IC' : 'Passport' }}" 
                                                     class="img-fluid mb-2" 
                                                     style="max-height: 150px; border-radius: 6px;">
                                            </div>
                                            <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                <button type="button" 
                                                        class="btn btn-sm" 
                                                        style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewCustomerIcModal">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </div>
                                            
                                            <!-- View IC Modal -->
                                            <div class="modal fade" id="viewCustomerIcModal" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Customer {{ $booking->customer->local ? 'IC' : 'Passport' }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center" style="min-height: 400px;">
                                                            <img src="{{ getFileUrl($icImg) }}" 
                                                                 alt="{{ $booking->customer->local ? 'IC' : 'Passport' }} Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="{{ getFileUrl($icImg) }}" 
                                                               target="_blank" 
                                                               class="btn btn-primary">
                                                                <i class="bi bi-download"></i> Open in New Tab
                                                            </a>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <p class="small text-muted mb-2">No {{ $booking->customer->local ? 'IC' : 'Passport' }} image uploaded</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Payment Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'payment-detail' ? 'show active' : '' }}" 
                 id="payment-detail" 
                 role="tabpanel" 
                 aria-labelledby="payment-detail-tab">
                <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment Summary</h5>
                        </div>
                        <div class="card-body">
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
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Payment History</h5>
                        </div>
                        <div class="card-body">
                    @if($booking->payments && $booking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Payment Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
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
                </div>
                </div>
            </div>

            <!-- Pickup Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'pickup-condition' ? 'show active' : '' }}" 
                 id="pickup-condition" 
                 role="tabpanel" 
                 aria-labelledby="pickup-condition-tab">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Pickup Vehicle Condition Form</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">This form records the vehicle condition at the time of pickup.</p>
                        
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
                </div>
            </div>

            <!-- Return Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'return-condition' ? 'show active' : '' }}" 
                 id="return-condition" 
                 role="tabpanel" 
                 aria-labelledby="return-condition-tab">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Return Vehicle Condition Form</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">This form records the vehicle condition at the time of return.</p>
                        
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
                </div>
            </div>

            <!-- Transaction Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'transaction-detail' ? 'show active' : '' }}" 
                 id="transaction-detail" 
                 role="tabpanel" 
                 aria-labelledby="transaction-detail-tab">
                <div class="row g-3">
                    <!-- Payment History -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment History</h5>
                                    </div>
                            <div class="card-body">
                                @if($transactions && $transactions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Payment ID</th>
                                                    <th>Payment Bank Name</th>
                                                    <th>Payment Bank Account No</th>
                                                    <th>Payment Date</th>
                                                    <th>Payment Status</th>
                                                    <th>Payment Amount</th>
                                                    <th>Transaction Reference (Receipt)</th>
                                                    <th>Is Payment Complete</th>
                                                    <th>Payment is Verify</th>
                                                    <th>Generate Invoice</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($transactions as $transaction)
                                                    @php
                                                        $totalRequiredForPayment = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                                                        $paidAmountForPayment = $transaction->total_amount ?? 0;
                                                        $isFullPaymentForPayment = $paidAmountForPayment >= $totalRequiredForPayment;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <strong>#{{ $transaction->paymentID }}</strong>
                                                        </td>
                                                        <td>
                                                            {{ $transaction->payment_bank_name ?? 'N/A' }}
                                                        </td>
                                                        <td>
                                                            {{ $transaction->payment_bank_account_no ?? 'N/A' }}
                                                        </td>
                                                        <td>
                                                            @if($transaction->payment_date)
                                                                {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d M Y') }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $status = $transaction->payment_status ?? 'Pending';
                                                                $statusClass = match($status) {
                                                                    'Verified', 'Full' => 'bg-success',
                                                                    'Pending' => 'bg-warning text-dark',
                                                                    'Rejected' => 'bg-danger',
                                                                    default => 'bg-secondary'
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $statusClass }}">
                                                                {{ $status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <strong>RM {{ number_format($transaction->total_amount ?? 0, 2) }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($transaction->transaction_reference)
                                                                @php
                                                                    $receiptPath = $transaction->transaction_reference;
                                                                    $isImagePath = str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/') || str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png');
                                                                    
                                                                    if ($isImagePath) {
                                                                        if (str_starts_with($receiptPath, 'uploads/')) {
                                                                            $imageUrl = asset($receiptPath);
                                                                        } else {
                                                                            $imageUrl = getFileUrl($receiptPath);
                                                                        }
                                                                    } else {
                                                                        $imageUrl = null;
                                                                    }
                                                                @endphp
                                                                @if($imageUrl)
                                                                    <a href="{{ $imageUrl }}" target="_blank" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $transaction->paymentID }}">
                                                                        <img src="{{ $imageUrl }}" alt="Receipt" class="img-fluid" style="max-width: 80px; max-height: 80px; border-radius: 4px; cursor: pointer;" title="Click to view full size" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                                                        <span class="text-muted small" style="display:none;">{{ $transaction->transaction_reference }}</span>
                                                                    </a>
                                                                    <!-- Receipt Modal -->
                                                                    <div class="modal fade" id="receiptModal{{ $transaction->paymentID }}" tabindex="-1">
                                                                        <div class="modal-dialog modal-lg">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Receipt - Payment #{{ $transaction->paymentID }}</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                                                                <div class="modal-body text-center">
                                                                                    <img src="{{ $imageUrl }}" alt="Receipt" class="img-fluid" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                    </div>
                                </div>
                            </div>
                                </div>
                                                                @else
                                                                    <span class="text-muted small" title="Transaction Reference">{{ strlen($transaction->transaction_reference) > 20 ? substr($transaction->transaction_reference, 0, 20) . '...' : $transaction->transaction_reference }}</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $isComplete = $transaction->isPayment_complete ?? false;
                                                                if (!$isComplete && $isFullPaymentForPayment) {
                                                                    $isComplete = true;
                                                                }
                                                            @endphp
                                                            <span class="badge {{ $isComplete ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                {{ $isComplete ? 'Yes' : 'No' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ ($transaction->payment_isVerify ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ ($transaction->payment_isVerify ?? false) ? 'Verified' : 'Not Verified' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($transaction->payment_isVerify || $transaction->payment_status === 'Verified' || $transaction->payment_status === 'Full')
                                                                <a href="{{ route('admin.payments.invoice', $transaction->paymentID) }}" class="btn btn-sm btn-primary" target="_blank">
                                                                    <i class="bi bi-file-pdf"></i> Generate Invoice
                                                                </a>
                                                            @else
                                                                <span class="text-muted small">Verify first</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                            </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bi bi-receipt-cutoff fs-1 text-muted d-block mb-3"></i>
                                        <p class="text-muted">No payment records found for this booking.</p>
                        </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Refund History -->
                    @if($booking->deposit_refund_status || $booking->deposit_refund_amount || $booking->deposit_fine_amount)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-arrow-counterclockwise"></i> Refund History</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Deposit Amount</th>
                                                <th>Fine Amount</th>
                                                <th>Refund Amount</th>
                                                <th>Refund Status</th>
                                                <th>Customer Choice</th>
                                                <th>Handled By</th>
                                                <th>Last Update Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>#{{ $booking->bookingID }}</strong></td>
                                                <td><strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong></td>
                                                <td><strong style="color: red;">RM {{ number_format($booking->deposit_fine_amount ?? 0, 2) }}</strong></td>
                                                <td><strong style="color: green;">RM {{ number_format($booking->deposit_refund_amount ?? 0, 2) }}</strong></td>
                                                <td>
                                                    @php
                                                        $refundStatus = $booking->deposit_refund_status ?? 'pending';
                                                        $refundStatusClass = match($refundStatus) {
                                                            'refunded' => 'bg-success',
                                                            'pending' => 'bg-warning text-dark',
                                                            'rejected' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $refundStatusClass }}">
                                                        {{ ucfirst($refundStatus) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($booking->deposit_customer_choice)
                                                        <span class="badge {{ $booking->deposit_customer_choice === 'refund' ? 'bg-info' : 'bg-secondary' }}">
                                                            {{ ucfirst($booking->deposit_customer_choice) }}
                                                        </span>
                    @else
                                                        <span class="text-muted">N/A</span>
                    @endif
                                                </td>
                                                <td>
                                                    @if($booking->deposit_handled_by)
                                                        @php
                                                            $handler = \App\Models\User::find($booking->deposit_handled_by);
                                                        @endphp
                                                        {{ $handler->name ?? 'N/A' }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y, H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
        const tab = urlParams.get('tab') || 'booking-detail';
        const tabButton = document.querySelector(`#${tab}-tab`);
        if (tabButton) {
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
        }
    });

    // Initialize active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'booking-detail';
        const tabButton = document.querySelector(`#${tab}-tab`);
        if (tabButton && '{{ $activeTab }}' !== tab) {
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
        }
    });
</script>
@endsection

