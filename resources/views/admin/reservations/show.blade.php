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
    
    /* Pickup Condition Form Styles */
    .pickup-condition-wrapper .payment-table,
    .transaction-detail-wrapper .payment-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .condition-image-card {
        transition: all 0.3s ease;
    }
    .condition-image-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }
    .condition-image-card:hover img {
        opacity: 0.9;
    }
    
    /* Transaction Detail Styles */
    .transaction-detail-wrapper .table tbody tr:hover {
        background-color: rgba(185, 28, 28, 0.03);
    }
    
    /* Fuel Image Container */
    .fuel-image-container img {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .fuel-image-container img:hover {
        border-color: var(--admin-red);
        transform: scale(1.02);
    }
    
    /* Receipt Image in Table */
    .receipt-image {
        max-width: 60px;
        max-height: 60px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .receipt-image:hover {
        opacity: 0.8;
        transform: scale(1.05);
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
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Booking Information</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editBookingInfoModal">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
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
                                    <dd class="d-inline ms-2" id="servedByDisplay">{{ $staffServed->name ?? 'Not Assigned' }}</dd>
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

                <!-- Runner Assigned Card -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-truck"></i> Runner Assigned</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editRunnerModal">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Runner:</dt>
                                    <dd class="d-inline ms-2" id="runnerAssignedDisplay">
                                        @php
                                            $runnerUser = null;
                                            if ($booking->staff_served) {
                                                $user = \App\Models\User::find($booking->staff_served);
                                                if ($user && $user->isRunner()) {
                                                    $runnerUser = $user;
                                                }
                                            }
                                        @endphp
                                        {{ $runnerUser->name ?? 'Not Assigned' }}
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Pickup Point:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->pickup_point ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Return Point:</dt>
                                    <dd class="d-inline ms-2">{{ $booking->return_point ?? 'N/A' }}</dd>
                                </div>
                                @php
                                    $needsRunner = (!empty($booking->pickup_point) && $booking->pickup_point !== 'HASTA HQ Office') ||
                                                   (!empty($booking->return_point) && $booking->return_point !== 'HASTA HQ Office');
                                @endphp
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Runner Required:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge {{ $needsRunner ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            {{ $needsRunner ? 'Yes' : 'No' }}
                                        </span>
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


            <!-- Pickup Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'pickup-condition' ? 'show active' : '' }}" 
                 id="pickup-condition" 
                 role="tabpanel" 
                 aria-labelledby="pickup-condition-tab">
                
                @php
                    $pickupForm = $booking->vehicleConditionForms()->where('form_type', 'RECEIVE')->first();
                @endphp
                
                @if($pickupForm)
                <!-- Pickup Condition Form Details Section -->
                <div class="pickup-condition-wrapper">
                    
                    <!-- GROUPING BOX 1: Form Details Table -->
                    <div class="payment-table mb-4">
                        <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0;">
                            <i class="bi bi-clipboard-data"></i> Pickup Form Details (RECEIVE)
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: var(--admin-red-light);">
                                    <tr>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Form Type</th>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Odometer Reading</th>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Fuel Level</th>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Scratches / Notes</th>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Reported Date/Time</th>
                                        <th style="color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px;">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <span class="badge bg-success fs-6">{{ $pickupForm->form_type }}</span>
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <strong>{{ number_format($pickupForm->odometer_reading ?? 0) }} km</strong>
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @php
                                                $fuelLevel = $pickupForm->fuel_level ?? 'N/A';
                                                $fuelBadgeClass = match($fuelLevel) {
                                                    'FULL' => 'bg-success',
                                                    '3/4' => 'bg-primary',
                                                    '1/2' => 'bg-info',
                                                    '1/4' => 'bg-warning text-dark',
                                                    'EMPTY' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $fuelBadgeClass }}">{{ $fuelLevel }}</span>
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($pickupForm->scratches_notes)
                                                <span class="text-dark">{{ Str::limit($pickupForm->scratches_notes, 50) }}</span>
                                                @if(strlen($pickupForm->scratches_notes) > 50)
                                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="modal" data-bs-target="#scratchesNotesModal">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-muted fst-italic">No notes</span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($pickupForm->reported_dated_time)
                                                <div>{{ \Carbon\Carbon::parse($pickupForm->reported_dated_time)->format('d M Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($pickupForm->reported_dated_time)->format('H:i') }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($pickupForm->created_at)
                                                <div>{{ \Carbon\Carbon::parse($pickupForm->created_at)->format('d M Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($pickupForm->created_at)->format('H:i') }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Scratches Notes Modal -->
                    @if($pickupForm->scratches_notes && strlen($pickupForm->scratches_notes) > 50)
                    <div class="modal fade" id="scratchesNotesModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Scratches / Notes</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-0">{{ $pickupForm->scratches_notes }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row g-4">
                        <!-- GROUPING BOX 2: Rental Agreement PDF -->
                        <div class="col-lg-6">
                            <div class="payment-table h-100">
                                <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0;">
                                    <i class="bi bi-file-earmark-pdf"></i> Rental Agreement (PDF)
                                </div>
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4" style="min-height: 280px; background: white; border-radius: 0 0 12px 12px;">
                                    @if($pickupForm->rental_agreement)
                                        <div class="text-center">
                                            <div class="mb-3" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <i class="bi bi-file-earmark-pdf-fill" style="font-size: 3rem; color: var(--admin-red);"></i>
                                            </div>
                                            <h6 class="mb-2">Rental Agreement</h6>
                                            <p class="text-muted small mb-3">Customer uploaded document</p>
                                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#viewRentalAgreementModal">
                                                    <i class="bi bi-eye me-1"></i> Preview
                                                </button>
                                                <a href="{{ getFileUrl($pickupForm->rental_agreement) }}" target="_blank" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Rental Agreement Modal -->
                                        <div class="modal fade" id="viewRentalAgreementModal" tabindex="-1">
                                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-file-pdf me-2"></i>Rental Agreement - Booking #{{ $booking->bookingID }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-0" style="height: 80vh;">
                                                        @php
                                                            $agreementUrl = getFileUrl($pickupForm->rental_agreement);
                                                            $isPdf = str_contains(strtolower($pickupForm->rental_agreement), '.pdf');
                                                        @endphp
                                                        @if($isPdf)
                                                            <iframe src="{{ $agreementUrl }}" width="100%" height="100%" style="border: none;"></iframe>
                                                        @else
                                                            <div class="text-center p-4">
                                                                <img src="{{ $agreementUrl }}" alt="Rental Agreement" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<div class=\'py-5\'><i class=\'bi bi-exclamation-triangle fs-1 text-warning\'></i><p class=\'text-muted mt-3\'>Document not found</p></div>';">
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ $agreementUrl }}" target="_blank" class="btn btn-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <div class="mb-3" style="background: #f3f4f6; border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #9ca3af;"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">No Rental Agreement</h6>
                                            <p class="text-muted small mb-0">Customer has not uploaded a rental agreement yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- GROUPING BOX 3: Fuel Image -->
                        <div class="col-lg-6">
                            <div class="payment-table h-100">
                                <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0;">
                                    <i class="bi bi-fuel-pump"></i> Fuel Level Image
                                </div>
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4" style="min-height: 280px; background: white; border-radius: 0 0 12px 12px;">
                                    @if($pickupForm->fuel_img)
                                        <div class="text-center w-100">
                                            <div class="fuel-image-container mb-3" style="max-width: 300px; margin: 0 auto;">
                                                <img src="{{ getFileUrl($pickupForm->fuel_img) }}" alt="Fuel Level" class="img-fluid rounded shadow-sm" style="max-height: 180px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#viewFuelImgModal" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="placeholder-box" style="display: none; height: 180px; background: #f3f4f6; border-radius: 8px; align-items: center; justify-content: center;">
                                                    <span class="text-muted">Image not found</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#viewFuelImgModal">
                                                <i class="bi bi-arrows-fullscreen me-1"></i> View Full Size
                                            </button>
                                        </div>
                                        
                                        <!-- Fuel Image Modal -->
                                        <div class="modal fade" id="viewFuelImgModal" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-fuel-pump me-2"></i>Fuel Level Image - Pickup</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center p-4" style="min-height: 400px;">
                                                        <img src="{{ getFileUrl($pickupForm->fuel_img) }}" alt="Fuel Level" class="img-fluid rounded" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<div class=\'py-5\'><i class=\'bi bi-exclamation-triangle fs-1 text-warning\'></i><p class=\'text-muted mt-3\'>Image not found</p></div>';">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ getFileUrl($pickupForm->fuel_img) }}" target="_blank" class="btn btn-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <div class="mb-3" style="background: #f3f4f6; border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                <i class="bi bi-image" style="font-size: 3rem; color: #9ca3af;"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">No Fuel Image</h6>
                                            <p class="text-muted small mb-0">Customer has not uploaded a fuel image yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Condition Images -->
                    @if($pickupForm->images && $pickupForm->images->count() > 0)
                    <div class="payment-table mt-4">
                        <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0;">
                            <i class="bi bi-images"></i> Vehicle Condition Images ({{ $pickupForm->images->count() }})
                        </div>
                        <div class="card-body p-4" style="background: white; border-radius: 0 0 12px 12px;">
                            <div class="row g-3">
                                @foreach($pickupForm->images as $index => $image)
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <div class="card h-100 border-0 shadow-sm overflow-hidden condition-image-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#conditionImageModal{{ $index }}">
                                            <img src="{{ getFileUrl($image->image_path ?? $image->imagePath) }}" alt="Condition Image {{ $index + 1 }}" class="card-img-top" style="height: 120px; object-fit: cover;" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2RlZTJlNiIvPjx0ZXh0IHg9IjEwMCIgeT0iNjAiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';">
                                            <div class="card-footer bg-white border-0 py-2 text-center">
                                                <small class="text-muted fw-medium">Image {{ $index + 1 }}</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Image Modal -->
                                        <div class="modal fade" id="conditionImageModal{{ $index }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-image me-2"></i>Condition Image {{ $index + 1 }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center p-4">
                                                        <img src="{{ getFileUrl($image->image_path ?? $image->imagePath) }}" alt="Condition Image" class="img-fluid rounded" style="max-height: 70vh;">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ getFileUrl($image->image_path ?? $image->imagePath) }}" target="_blank" class="btn btn-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <!-- No Pickup Form State -->
                <div class="payment-table">
                    <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600; border-radius: 12px 12px 0 0;">
                        <i class="bi bi-clipboard-check"></i> Pickup Condition Form (RECEIVE)
                    </div>
                    <div class="card-body text-center py-5" style="background: white; border-radius: 0 0 12px 12px;">
                        <div class="mb-4" style="background: #f3f4f6; border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-clipboard-x" style="font-size: 3.5rem; color: #9ca3af;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No Pickup Condition Form Submitted</h5>
                        <p class="text-muted mb-3">
                            The customer has not yet submitted the pickup condition form.
                        </p>
                        @if($booking->rental_start_date)
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded" style="background: #fee2e2;">
                                <i class="bi bi-calendar-event me-2 text-danger"></i>
                                <span class="text-danger fw-medium">Pickup Date: {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y, H:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Return Condition Form Tab -->
            <div class="tab-pane fade {{ $activeTab === 'return-condition' ? 'show active' : '' }}" 
                 id="return-condition" 
                 role="tabpanel" 
                 aria-labelledby="return-condition-tab">
                @php
                    $returnForm = $booking->vehicleConditionForms()->where('form_type', 'RETURN')->first();
                @endphp
                
                @if($returnForm)
                <div class="row g-3">
                    <!-- Form Details Card -->
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Return Condition Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Form ID:</dt>
                                            <dd class="col-7">#{{ $returnForm->formID }}</dd>
                                            
                                            <dt class="col-5">Form Type:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-warning text-dark">{{ $returnForm->form_type }}</span>
                                            </dd>
                                            
                                            <dt class="col-5">Odometer Reading:</dt>
                                            <dd class="col-7"><strong>{{ number_format($returnForm->odometer_reading ?? 0) }} km</strong></dd>
                                            
                                            <dt class="col-5">Fuel Level:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-info">{{ $returnForm->fuel_level ?? 'N/A' }}</span>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Reported Date/Time:</dt>
                                            <dd class="col-7">
                                                @if($returnForm->reported_dated_time)
                                                    {{ \Carbon\Carbon::parse($returnForm->reported_dated_time)->format('d M Y, H:i') }}
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Created At:</dt>
                                            <dd class="col-7">
                                                @if($returnForm->created_at)
                                                    {{ \Carbon\Carbon::parse($returnForm->created_at)->format('d M Y, H:i') }}
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Scratches/Notes:</dt>
                                            <dd class="col-7">{{ $returnForm->scratches_notes ?? 'No notes' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Agreement Card (Return) -->
                    <div class="col-md-6">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none; min-height: 300px;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Rental Agreement</h5>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                @if($returnForm->rental_agreement)
                                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 4rem; color: var(--admin-red);"></i>
                                    <p class="mt-3 mb-3">Rental Agreement Document</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm" style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);" data-bs-toggle="modal" data-bs-target="#viewReturnRentalAgreementModal">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <a href="{{ getFileUrl($returnForm->rental_agreement) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                    
                                    <!-- Rental Agreement Modal -->
                                    <div class="modal fade" id="viewReturnRentalAgreementModal" tabindex="-1">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rental Agreement (Return) - Booking #{{ $booking->bookingID }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body" style="height: 80vh;">
                                                    @php
                                                        $returnAgreementUrl = getFileUrl($returnForm->rental_agreement);
                                                        $returnIsPdf = str_contains(strtolower($returnForm->rental_agreement), '.pdf');
                                                    @endphp
                                                    @if($returnIsPdf)
                                                        <iframe src="{{ $returnAgreementUrl }}" width="100%" height="100%" style="border: none;"></iframe>
                                                    @else
                                                        <div class="text-center">
                                                            <img src="{{ $returnAgreementUrl }}" alt="Rental Agreement" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Document not found</p>';">
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ $returnAgreementUrl }}" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No rental agreement uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Fuel Image Card (Return) -->
                    <div class="col-md-6">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none; min-height: 300px;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-fuel-pump"></i> Fuel Level Image</h5>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                @if($returnForm->fuel_img)
                                    <img src="{{ getFileUrl($returnForm->fuel_img) }}" alt="Fuel Level" class="img-fluid mb-3" style="max-height: 150px; border-radius: 8px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <p class="text-muted" style="display: none;">Image not found</p>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-sm" style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);" data-bs-toggle="modal" data-bs-target="#viewReturnFuelImgModal">
                                            <i class="bi bi-eye"></i> View Full Size
                                        </button>
                                    </div>
                                    
                                    <!-- Fuel Image Modal -->
                                    <div class="modal fade" id="viewReturnFuelImgModal" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Fuel Level Image - Return</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center" style="min-height: 400px;">
                                                    <img src="{{ getFileUrl($returnForm->fuel_img) }}" alt="Fuel Level" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ getFileUrl($returnForm->fuel_img) }}" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No fuel image uploaded</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Condition Images (Return) -->
                    @if($returnForm->images && $returnForm->images->count() > 0)
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-images"></i> Vehicle Condition Images ({{ $returnForm->images->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($returnForm->images as $index => $image)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="card h-100" style="border: 1px solid #e5e7eb;">
                                                <img src="{{ getFileUrl($image->image_path ?? $image->imagePath) }}" alt="Condition Image {{ $index + 1 }}" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#returnConditionImageModal{{ $index }}" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2RlZTJlNiIvPjx0ZXh0IHg9IjEwMCIgeT0iNzUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3ZnPg==';">
                                                <div class="card-body p-2 text-center">
                                                    <small class="text-muted">Image {{ $index + 1 }}</small>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Modal -->
                                            <div class="modal fade" id="returnConditionImageModal{{ $index }}" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Condition Image {{ $index + 1 }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="{{ getFileUrl($image->image_path ?? $image->imagePath) }}" alt="Condition Image" class="img-fluid" style="max-height: 70vh;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Return Vehicle Condition Form</h5>
                            </div>
                            <div class="card-body text-center py-5">
                                <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #6c757d;"></i>
                                <h5 class="mt-3 text-muted">No Return Condition Form Submitted</h5>
                                <p class="text-muted mb-0">
                                    The return condition form has not been submitted yet.
                                    @if($booking->rental_end_date)
                                        <br>Return Date: <strong>{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y, H:i') }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Transaction Detail Tab -->
            <div class="tab-pane fade {{ $activeTab === 'transaction-detail' ? 'show active' : '' }}" 
                 id="transaction-detail" 
                 role="tabpanel" 
                 aria-labelledby="transaction-detail-tab">
                
                <div class="transaction-detail-wrapper">
                    
                    <!-- GROUPING BOX 1: Payment List Table -->
                    <div class="payment-table mb-4" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
                        <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-credit-card me-2"></i>Payment List ({{ $transactions ? $transactions->count() : 0 }})</span>
                                @if($transactions && $transactions->count() > 0)
                                    @php
                                        $totalPaidAmount = $transactions->sum('total_amount');
                                        $totalVerified = $transactions->where('payment_status', 'Verified')->count();
                                    @endphp
                                    <span class="badge bg-light text-danger">Total: RM {{ number_format($totalPaidAmount, 2) }} | Verified: {{ $totalVerified }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive">
                            @if($transactions && $transactions->count() > 0)
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Booking ID</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment ID</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Bank Name</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Bank Account No</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Date</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Type</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Amount</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Transaction Reference</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Receipt</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Is Payment Complete</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment Status</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Payment is Verify</th>
                                            <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Verified By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $payment)
                                            @php
                                                // Calculate payment type based on amount
                                                $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                                                $depositAmount = $booking->deposit_amount ?? 50;
                                                $paidAmount = $payment->total_amount ?? 0;
                                                
                                                // Determine payment type
                                                $paymentType = 'Balance';
                                                if ($paidAmount <= $depositAmount && $depositAmount > 0) {
                                                    $paymentType = 'Deposit';
                                                } elseif ($paidAmount >= $totalRequired) {
                                                    $paymentType = 'Full Payment';
                                                }
                                                
                                                // Check if payment is complete
                                                $allVerifiedPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                                $isPaymentComplete = $allVerifiedPaid >= $totalRequired;
                                                
                                                // Receipt check
                                                $receiptPath = $payment->proof_of_payment ?? null;
                                                $hasReceipt = $receiptPath && (str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf') || str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/'));
                                            @endphp
                                            <tr>
                                                <!-- Booking ID -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <a href="{{ route('admin.bookings.reservations.show', $booking->bookingID) }}" class="text-decoration-none fw-bold text-primary">
                                                        #{{ $booking->bookingID }}
                                                    </a>
                                                </td>
                                                <!-- Payment ID -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <a href="{{ route('admin.payments.index', ['search' => $payment->paymentID]) }}" class="text-decoration-none fw-bold text-danger" target="_blank">
                                                        #{{ $payment->paymentID }}
                                                    </a>
                                                </td>
                                                <!-- Payment Bank Name -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    {{ $payment->payment_bank_name ?? 'N/A' }}
                                                </td>
                                                <!-- Payment Bank Account No -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    {{ $payment->payment_bank_account_no ?? 'N/A' }}
                                                </td>
                                                <!-- Payment Date -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    @if($payment->payment_date)
                                                        <div>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</div>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($payment->payment_date)->format('H:i') }}</small>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <!-- Payment Type -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    @php
                                                        $typeClass = match($paymentType) {
                                                            'Deposit' => 'bg-info',
                                                            'Balance' => 'bg-warning text-dark',
                                                            'Full Payment' => 'bg-success',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $typeClass }}">{{ $paymentType }}</span>
                                                </td>
                                                <!-- Payment Amount -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <strong class="text-dark">RM {{ number_format($payment->total_amount ?? 0, 2) }}</strong>
                                                </td>
                                                <!-- Transaction Reference -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    @if($payment->transaction_reference)
                                                        <span class="text-muted small" title="{{ $payment->transaction_reference }}">
                                                            {{ strlen($payment->transaction_reference) > 15 ? substr($payment->transaction_reference, 0, 15) . '...' : $payment->transaction_reference }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <!-- Payment Receipt -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    @if($hasReceipt)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#paymentReceiptModal{{ $payment->paymentID }}">
                                                            <i class="bi bi-receipt"></i> View
                                                        </button>
                                                        <!-- Receipt Modal -->
                                                        <div class="modal fade" id="paymentReceiptModal{{ $payment->paymentID }}" tabindex="-1">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header bg-danger text-white">
                                                                        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Payment Receipt - #{{ $payment->paymentID }}</h5>
                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body text-center p-4">
                                                                        @if(str_contains(strtolower($receiptPath ?? ''), '.pdf'))
                                                                            <iframe src="{{ getFileUrl($receiptPath) }}" style="width: 100%; height: 500px; border: none;"></iframe>
                                                                        @else
                                                                            <img src="{{ getFileUrl($receiptPath) }}" alt="Receipt" class="img-fluid rounded" style="max-height: 70vh;">
                                                                        @endif
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <a href="{{ getFileUrl($receiptPath) }}" target="_blank" class="btn btn-primary">
                                                                            <i class="bi bi-box-arrow-up-right me-1"></i>Open
                                                                        </a>
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">No receipt</span>
                                                    @endif
                                                </td>
                                                <!-- Is Payment Complete -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <span class="badge {{ $isPaymentComplete ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ $isPaymentComplete ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <!-- Payment Status -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    @php
                                                        $status = $payment->payment_status ?? 'Pending';
                                                        $statusClass = match($status) {
                                                            'Verified', 'Full' => 'bg-success',
                                                            'Pending' => 'bg-warning text-dark',
                                                            'Rejected' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">{{ $status }}</span>
                                                </td>
                                                <!-- Payment is Verify (Dropdown) -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <select class="form-select form-select-sm" style="min-width: 90px;" 
                                                            data-payment-id="{{ $payment->paymentID }}"
                                                            onchange="updatePaymentVerify(this, {{ $payment->paymentID }})">
                                                        <option value="0" {{ !($payment->payment_isVerify ?? false) ? 'selected' : '' }}>False</option>
                                                        <option value="1" {{ ($payment->payment_isVerify ?? false) ? 'selected' : '' }}>True</option>
                                                    </select>
                                                </td>
                                                <!-- Verified By (Dropdown) -->
                                                <td style="padding: 12px; vertical-align: middle;">
                                                    <select class="form-select form-select-sm" style="min-width: 130px;"
                                                            data-payment-id="{{ $payment->paymentID }}"
                                                            onchange="updatePaymentVerifiedBy(this, {{ $payment->paymentID }})">
                                                        <option value="">Not Set</option>
                                                        @foreach($verifyByUsers ?? [] as $staff)
                                                            <option value="{{ $staff->userID }}" {{ ($payment->verified_by ?? null) == $staff->userID ? 'selected' : '' }}>
                                                                {{ $staff->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center py-5" style="background: white;">
                                    <div class="mb-3" style="background: #f3f4f6; border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="bi bi-credit-card-2-front" style="font-size: 3rem; color: #9ca3af;"></i>
                                    </div>
                                    <h6 class="text-muted mb-1">No Payment Records</h6>
                                    <p class="text-muted small mb-0">No payment transactions found for this booking.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- GROUPING BOX 2: Deposit Details Table -->
                    <div class="payment-table" style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
                        <div class="table-header" style="background: var(--admin-red); color: white; padding: 15px 20px; font-weight: 600;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-wallet me-2"></i>Deposit Details</span>
                                <span class="badge bg-light text-danger">Original Deposit: RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Booking ID</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Customer Name</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Deposit Payment</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Vehicle Condition Form</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Customer Choice</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Fine Amount</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Originally</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Refund Amount</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Refund Status</th>
                                        <th style="background: var(--admin-red-light); color: var(--admin-red-dark); font-weight: 600; border-bottom: 2px solid var(--admin-red); padding: 12px; font-size: 0.85rem; white-space: nowrap;">Handled By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $hasReturnForm = $booking->vehicleConditionForms && $booking->vehicleConditionForms->where('form_type', 'RETURN')->first();
                                    @endphp
                                    <tr>
                                        <!-- Booking ID -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <a href="{{ route('admin.bookings.reservations.show', $booking->bookingID) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $booking->bookingID }}
                                            </a>
                                        </td>
                                        <!-- Customer Name -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <span class="fw-medium">{{ $booking->customer->user->name ?? 'N/A' }}</span>
                                        </td>
                                        <!-- Deposit Payment -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <strong class="text-dark fs-6">RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <!-- Vehicle Condition Form -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($hasReturnForm)
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Submitted</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                            @endif
                                        </td>
                                        <!-- Customer Choice -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($booking->deposit_customer_choice)
                                                @php
                                                    $choiceClass = match($booking->deposit_customer_choice) {
                                                        'hold', 'wallet' => 'bg-info',
                                                        'refund', 'bank_transfer' => 'bg-primary',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $choiceClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $booking->deposit_customer_choice)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <!-- Fine Amount -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($booking->deposit_fine_amount && $booking->deposit_fine_amount > 0)
                                                <strong class="text-danger">RM {{ number_format($booking->deposit_fine_amount, 2) }}</strong>
                                            @else
                                                <span class="text-muted">RM 0.00</span>
                                            @endif
                                        </td>
                                        <!-- Originally (Original Deposit) -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <strong class="text-dark">RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <!-- Refund Amount -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            @if($booking->deposit_refund_amount && $booking->deposit_refund_amount > 0)
                                                <strong class="text-success">RM {{ number_format($booking->deposit_refund_amount, 2) }}</strong>
                                            @else
                                                <span class="text-muted">RM 0.00</span>
                                            @endif
                                        </td>
                                        <!-- Refund Status (Dropdown) -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <select class="form-select form-select-sm" style="min-width: 110px;"
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    onchange="updateDepositRefundStatus(this, {{ $booking->bookingID }})">
                                                <option value="pending" {{ ($booking->deposit_refund_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="refunded" {{ ($booking->deposit_refund_status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                <option value="rejected" {{ ($booking->deposit_refund_status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </td>
                                        <!-- Handled By (Dropdown) -->
                                        <td style="padding: 12px; vertical-align: middle;">
                                            <select class="form-select form-select-sm" style="min-width: 130px;"
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    onchange="updateDepositHandledBy(this, {{ $booking->bookingID }})">
                                                <option value="">Not Assigned</option>
                                                @foreach($verifyByUsers ?? [] as $staff)
                                                    <option value="{{ $staff->userID }}" {{ ($booking->deposit_handled_by ?? null) == $staff->userID ? 'selected' : '' }}>
                                                        {{ $staff->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Information Modal -->
<div class="modal fade" id="editBookingInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Booking Information</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Served By</label>
                    <select id="editServedBy" class="form-select">
                        <option value="">Not Assigned</option>
                        @foreach($staffUsers ?? [] as $staff)
                            <option value="{{ $staff->userID }}" {{ $booking->staff_served == $staff->userID ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->isAdmin() ? 'Admin' : 'Staff IT' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="updateServedBy()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Runner Modal -->
<div class="modal fade" id="editRunnerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-truck"></i> Assign Runner</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Runner</label>
                    <select id="editRunnerAssigned" class="form-select">
                        <option value="">Not Assigned</option>
                        @foreach($runners ?? [] as $runner)
                            @php
                                $isCurrentRunner = false;
                                if ($booking->staff_served) {
                                    $currentUser = \App\Models\User::find($booking->staff_served);
                                    if ($currentUser && $currentUser->isRunner() && $currentUser->userID == $runner->userID) {
                                        $isCurrentRunner = true;
                                    }
                                }
                            @endphp
                            <option value="{{ $runner->userID }}" {{ $isCurrentRunner ? 'selected' : '' }}>
                                {{ $runner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="updateRunnerAssigned()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Notification -->
<div id="notificationToast" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; display: none;">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" id="toastHeader">
            <i class="bi bi-check-circle me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" onclick="hideNotification()"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Message here
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

    // Initialize active tab on page load - ensure correct tab is shown
    document.addEventListener('DOMContentLoaded', function() {
        // Get tab from URL or use server-side activeTab value
        const urlParams = new URLSearchParams(window.location.search);
        const tabFromUrl = urlParams.get('tab');
        const serverTab = '{{ $activeTab }}';
        const targetTab = tabFromUrl || serverTab || 'booking-detail';
        
        // Ensure the correct tab is visible
        const tabButton = document.querySelector(`#${targetTab}-tab`);
        const tabPane = document.querySelector(`#${targetTab}`);
        
        if (tabButton && tabPane) {
            // Force show the correct tab
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
            
            // Debug log
            console.log('Showing tab:', targetTab);
        }
    });

    // Show notification
    function showNotification(message, isSuccess = true) {
        const toast = document.getElementById('notificationToast');
        const header = document.getElementById('toastHeader');
        const icon = document.getElementById('toastIcon');
        const title = document.getElementById('toastTitle');
        const body = document.getElementById('toastMessage');

        header.className = 'toast-header ' + (isSuccess ? 'bg-success text-white' : 'bg-danger text-white');
        icon.className = 'bi me-2 ' + (isSuccess ? 'bi-check-circle' : 'bi-x-circle');
        title.textContent = isSuccess ? 'Success' : 'Error';
        body.textContent = message;
        toast.style.display = 'block';

        setTimeout(() => {
            hideNotification();
        }, 4000);
    }

    function hideNotification() {
        document.getElementById('notificationToast').style.display = 'none';
    }

    // Update Served By
    function updateServedBy() {
        const staffServed = document.getElementById('editServedBy').value;
        
        fetch('{{ route('admin.bookings.reservations.update-status', $booking->bookingID) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ staff_served: staffServed || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display
                const selectedOption = document.getElementById('editServedBy').selectedOptions[0];
                document.getElementById('servedByDisplay').textContent = selectedOption.text || 'Not Assigned';
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editBookingInfoModal')).hide();
                showNotification('Served by updated successfully.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }

    // Update Runner Assigned
    function updateRunnerAssigned() {
        const runnerId = document.getElementById('editRunnerAssigned').value;
        
        fetch('{{ route('admin.bookings.reservations.update-runner', $booking->bookingID) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ runner_id: runnerId || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display
                const selectedOption = document.getElementById('editRunnerAssigned').selectedOptions[0];
                document.getElementById('runnerAssignedDisplay').textContent = selectedOption.value ? selectedOption.text : 'Not Assigned';
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editRunnerModal')).hide();
                showNotification('Runner assignment updated successfully.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }

    // === TRANSACTION DETAIL TAB: Payment & Deposit Dropdown Handlers ===
    
    // Update Payment isVerify dropdown
    function updatePaymentVerify(select, paymentId) {
        const value = select.value;
        
        fetch('/admin/payments/' + paymentId + '/update-verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ payment_isVerify: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Payment verification updated.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }

    // Update Payment Verified By dropdown
    function updatePaymentVerifiedBy(select, paymentId) {
        const value = select.value;
        
        fetch('/admin/payments/' + paymentId + '/update-verified-by', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ verified_by: value || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Verified by updated.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }

    // Update Deposit Refund Status dropdown
    function updateDepositRefundStatus(select, bookingId) {
        const value = select.value;
        
        fetch('/admin/deposits/' + bookingId + '/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ deposit_refund_status: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Refund status updated.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }

    // Update Deposit Handled By dropdown
    function updateDepositHandledBy(select, bookingId) {
        const value = select.value;
        
        fetch('/admin/deposits/' + bookingId + '/update-handled-by', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ deposit_handled_by: value || null })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Handled by updated.');
            } else {
                showNotification(data.message || 'Failed to update.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred.', false);
        });
    }
</script>
@endsection

