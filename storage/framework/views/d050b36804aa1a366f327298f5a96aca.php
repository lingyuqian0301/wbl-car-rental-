<?php $__env->startSection('title', 'Booking Details #' . $booking->bookingID); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="booking-detail-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1>Booking Details #<?php echo e($booking->bookingID); ?></h1>
                <div class="booking-meta">
                    <div class="meta-item">
                        <i class="bi bi-person"></i>
                        <span><?php echo e($booking->customer->user->name ?? 'Unknown Customer'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-car-front"></i>
                        <span><?php echo e($booking->vehicle->vehicle_brand ?? ''); ?> <?php echo e($booking->vehicle->vehicle_model ?? ''); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-calendar-check"></i>
                        <span><?php echo e(\Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y')); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="badge-status <?php echo e($booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary')); ?>">
                            <?php echo e($booking->booking_status); ?>

                        </span>
                    </div>
                </div>
            </div>
            <a href="<?php echo e(route('admin.bookings.reservations')); ?>" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Reservations
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Dynamic Tabs -->
    <div class="dynamic-tabs">
        <ul class="nav nav-tabs" id="bookingDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'booking-detail' ? 'active' : ''); ?>" 
                        id="booking-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#booking-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="booking-detail"
                        aria-selected="<?php echo e($activeTab === 'booking-detail' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('booking-detail')">
                    <i class="bi bi-info-circle"></i> Booking Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'vehicle-detail' ? 'active' : ''); ?>" 
                        id="vehicle-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#vehicle-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="vehicle-detail"
                        aria-selected="<?php echo e($activeTab === 'vehicle-detail' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('vehicle-detail')">
                    <i class="bi bi-car-front"></i> Vehicle Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'customer-detail' ? 'active' : ''); ?>" 
                        id="customer-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#customer-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="customer-detail"
                        aria-selected="<?php echo e($activeTab === 'customer-detail' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('customer-detail')">
                    <i class="bi bi-person"></i> Customer Detail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'pickup-condition' ? 'active' : ''); ?>" 
                        id="pickup-condition-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#pickup-condition" 
                        type="button" 
                        role="tab"
                        aria-controls="pickup-condition"
                        aria-selected="<?php echo e($activeTab === 'pickup-condition' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('pickup-condition')">
                    <i class="bi bi-calendar-check"></i> Pickup Condition Form
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'return-condition' ? 'active' : ''); ?>" 
                        id="return-condition-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#return-condition" 
                        type="button" 
                        role="tab"
                        aria-controls="return-condition"
                        aria-selected="<?php echo e($activeTab === 'return-condition' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('return-condition')">
                    <i class="bi bi-calendar-x"></i> Return Condition Form
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo e($activeTab === 'transaction-detail' ? 'active' : ''); ?>" 
                        id="transaction-detail-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#transaction-detail" 
                        type="button" 
                        role="tab"
                        aria-controls="transaction-detail"
                        aria-selected="<?php echo e($activeTab === 'transaction-detail' ? 'true' : 'false'); ?>"
                        onclick="updateUrl('transaction-detail')">
                    <i class="bi bi-receipt"></i> Transaction Detail
                </button>
            </li>
        </ul>

        <div class="tab-content" id="bookingDetailTabContent">
            <!-- Booking Detail Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'booking-detail' ? 'show active' : ''); ?>" 
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
                                    <dd class="d-inline ms-2"><strong>#<?php echo e($booking->bookingID); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking Status:</dt>
                                    <dd class="d-inline ms-2">
                                <span class="badge-status <?php echo e($booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-secondary'))); ?>">
                                    <?php echo e($booking->booking_status); ?>

                                </span>
                                    </dd>
                            </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Served By:</dt>
                                    <dd class="d-inline ms-2" id="servedByDisplay"><?php echo e($staffServed->name ?? 'Not Assigned'); ?></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Last Updated:</dt>
                                    <dd class="d-inline ms-2">
                                <?php echo e($booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y, H:i') : 'N/A'); ?>

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
                                        <?php
                                            $runnerUser = null;
                                            if ($booking->staff_served) {
                                                $user = \App\Models\User::find($booking->staff_served);
                                                if ($user && $user->isRunner()) {
                                                    $runnerUser = $user;
                                                }
                                            }
                                        ?>
                                        <?php echo e($runnerUser->name ?? 'Not Assigned'); ?>

                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Pickup Point:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->pickup_point ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Return Point:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->return_point ?? 'N/A'); ?></dd>
                                </div>
                                <?php
                                    $needsRunner = (!empty($booking->pickup_point) && $booking->pickup_point !== 'HASTA HQ Office') ||
                                                   (!empty($booking->return_point) && $booking->return_point !== 'HASTA HQ Office');
                                ?>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Runner Required:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge <?php echo e($needsRunner ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                            <?php echo e($needsRunner ? 'Yes' : 'No'); ?>

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
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A'); ?></strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental End Date:</dt>
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A'); ?></strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Duration:</dt>
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->duration ?? 0); ?> days</strong></dd>
                        </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Pickup Location:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->pickup_point ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Return Location:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->return_point ?? 'N/A'); ?></dd>
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
                                    <dd class="d-inline ms-2"><strong>RM <?php echo e(number_format($booking->rental_amount ?? 0, 2)); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Deposit Amount:</dt>
                                    <dd class="d-inline ms-2"><strong>RM <?php echo e(number_format($booking->deposit_amount ?? 0, 2)); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Total Amount:</dt>
                                    <dd class="d-inline ms-2"><strong>RM <?php echo e(number_format($totalRequired, 2)); ?></strong></dd>
                                </div>
                                <?php if($booking->additionalCharges): ?>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Additional Charges:</dt>
                                    <dd class="d-inline ms-2"><strong>RM <?php echo e(number_format($booking->additionalCharges->total_extra_charge ?? 0, 2)); ?></strong></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>

            <!-- Vehicle Detail Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'vehicle-detail' ? 'show active' : ''); ?>" 
                 id="vehicle-detail" 
                 role="tabpanel" 
                 aria-labelledby="vehicle-detail-tab">
                <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h5>
                            <a href="<?php echo e(route('admin.vehicles.show', $booking->vehicle->vehicleID)); ?>" class="btn btn-sm btn-light" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> View Full Vehicle Details
                            </a>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle ID:</dt>
                                    <dd class="d-inline ms-2"><strong>#<?php echo e($booking->vehicle->vehicleID ?? 'N/A'); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Brand:</dt>
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->vehicle->vehicle_brand ?? 'N/A'); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Model:</dt>
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->vehicle->vehicle_model ?? 'N/A'); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Plate Number:</dt>
                                    <dd class="d-inline ms-2"><strong><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Type:</dt>
                                    <dd class="d-inline ms-2"><?php echo e(ucfirst($booking->vehicle->vehicleType ?? 'N/A')); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental Price (per day):</dt>
                                    <dd class="d-inline ms-2"><strong>RM <?php echo e(number_format($booking->vehicle->rental_price ?? 0, 2)); ?></strong></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Manufacturing Year:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->manufacturing_year ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Color:</dt>
                                    <dd class="d-inline ms-2"><?php echo e(ucfirst($booking->vehicle->color ?? 'N/A')); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Engine Capacity:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->engineCapacity ?? 'N/A'); ?> L</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge-status <?php echo e($booking->vehicle->availability_status === 'available' ? 'bg-success' : ($booking->vehicle->availability_status === 'rented' ? 'bg-info' : ($booking->vehicle->availability_status === 'maintenance' ? 'bg-warning text-dark' : 'bg-secondary'))); ?>">
                                            <?php echo e(ucfirst($booking->vehicle->availability_status ?? 'N/A')); ?>

                                        </span>
                                    </dd>
                                </div>
                                <?php if($booking->vehicle->car): ?>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Car Type:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->car->car_type ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Seating Capacity:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->car->seating_capacity ?? 'N/A'); ?> seats</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Transmission:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->car->transmission ?? 'N/A'); ?></dd>
                                </div>
                                <?php endif; ?>
                                <?php if($booking->vehicle->motorcycle): ?>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Motor Type:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->vehicle->motorcycle->motor_type ?? 'N/A'); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>
                <?php if($booking->vehicle->owner): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
                    </div>
                        <div class="card-body">
                            <?php if($booking->vehicle->owner): ?>
                            <div class="row">
                                <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Owner ID:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->ownerID ?? 'N/A'); ?></dd>
                                            
                                            <dt class="col-5">Owner Name:</dt>
                                            <dd class="col-7">
                                                <?php
                                                    $ownerName = 'N/A';
                                                    if ($booking->vehicle->owner && $booking->vehicle->owner->personDetails) {
                                                        $ownerName = $booking->vehicle->owner->personDetails->fullname ?? 'N/A';
                                                    }
                                                ?>
                                                <?php echo e($ownerName); ?>

                                            </dd>
                                            
                                            <dt class="col-5">IC No:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->ic_no ?? 'N/A'); ?></dd>
                                            
                                            <dt class="col-5">Contact:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->contact_number ?? 'N/A'); ?></dd>
                                            
                                            <dt class="col-5">Email:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->email ?? 'N/A'); ?></dd>
                                            
                                            <dt class="col-5">Bank Name:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->bankname ?? 'N/A'); ?></dd>
                                            
                                            <dt class="col-5">Bank Account No:</dt>
                                            <dd class="col-7"><?php echo e($booking->vehicle->owner->bank_acc_number ?? 'N/A'); ?></dd>
                                        </dl>
                </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Registration Date:</dt>
                                            <dd class="col-7">
                                                <?php if($booking->vehicle->owner->registration_date): ?>
                                                    @try
                                                        <?php echo e(\Carbon\Carbon::parse($booking->vehicle->owner->registration_date)->format('d M Y')); ?>

                                                    @catch(\Exception $e)
                                                        <?php echo e($booking->vehicle->owner->registration_date); ?>

                                                    @endtry
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Leasing Price:</dt>
                                            <dd class="col-7">RM <?php echo e(number_format($booking->vehicle->owner->leasing_price ?? 0, 2)); ?></dd>
                                            
                                            <dt class="col-5">Leasing Due Date:</dt>
                                            <dd class="col-7">
                                                <?php if($booking->vehicle->owner->leasing_due_date): ?>
                                                    @try
                                                        <?php echo e(\Carbon\Carbon::parse($booking->vehicle->owner->leasing_due_date)->format('d M Y')); ?>

                                                    @catch(\Exception $e)
                                                        <?php echo e($booking->vehicle->owner->leasing_due_date); ?>

                                                    @endtry
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">License Expiry Date:</dt>
                                            <dd class="col-7">
                                                <?php if($booking->vehicle->owner->license_expirydate): ?>
                                                    @try
                                                        <?php echo e(\Carbon\Carbon::parse($booking->vehicle->owner->license_expirydate)->format('d M Y')); ?>

                                                    @catch(\Exception $e)
                                                        <?php echo e($booking->vehicle->owner->license_expirydate); ?>

                                                    @endtry
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Status:</dt>
                                            <dd class="col-7">
                                                <span class="badge <?php echo e(($booking->vehicle->owner->isActive ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                                                    <?php echo e(($booking->vehicle->owner->isActive ?? false) ? 'Active' : 'Inactive'); ?>

                                                </span>
                                            </dd>
                                        </dl>
                        </div>
                        </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No owner information available for this vehicle.
                        </div>
                            <?php endif; ?>
                        </div>
                    </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            <!-- Customer Detail Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'customer-detail' ? 'show active' : ''); ?>" 
                 id="customer-detail" 
                 role="tabpanel" 
                 aria-labelledby="customer-detail-tab">
                <!-- User Info Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Info</h5>
                            <a href="<?php echo e(route('admin.customers.show', ['customer' => $booking->customer->customerID])); ?>" class="btn btn-sm btn-light" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> View Full Customer Details
                            </a>
            </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">User ID:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->userID ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Username:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->username ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Email:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->email ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Phone:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->phone ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Name:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->name ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Last Login:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->lastLogin ? \Carbon\Carbon::parse($booking->customer->user->lastLogin)->format('d M Y H:i') : 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date Registered:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->dateRegistered ? \Carbon\Carbon::parse($booking->customer->user->dateRegistered)->format('d M Y') : 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date of Birth:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->user->DOB ? \Carbon\Carbon::parse($booking->customer->user->DOB)->format('d M Y') : 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Age:</dt>
                                    <dd class="d-inline ms-2">
                                        <?php if($booking->customer->user->DOB): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->customer->user->DOB)->age); ?> years
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge <?php echo e(($booking->customer->user->isActive ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                                            <?php echo e(($booking->customer->user->isActive ?? false) ? 'Active' : 'Inactive'); ?>

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
                                        <?php if($booking->customer->local): ?>
                                            Local
                                        <?php elseif($booking->customer->international): ?>
                                            International
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                        <?php if($booking->customer->localStudent || $booking->customer->internationalStudent): ?>
                                            / Student
                                        <?php elseif($booking->customer->localUtmStaff || $booking->customer->internationalUtmStaff): ?>
                                            / Staff
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold"><?php echo e($booking->customer->local ? 'IC No' : 'Passport No'); ?>:</dt>
                                    <dd class="d-inline ms-2">
                                        <?php echo e($booking->customer->local->ic_no ?? ($booking->customer->international->passport_no ?? 'N/A')); ?>

                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold"><?php echo e($booking->customer->local ? 'State of Origin' : 'Country of Origin'); ?>:</dt>
                                    <dd class="d-inline ms-2">
                                        <?php echo e($booking->customer->local->stateOfOrigin ?? ($booking->customer->international->countryOfOrigin ?? 'N/A')); ?>

                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Address:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->address ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">License Expiry Date:</dt>
                                    <dd class="d-inline ms-2">
                                        <?php if($booking->customer->customer_license): ?>
                                            <?php echo e(\Carbon\Carbon::parse($booking->customer->customer_license)->format('d M Y')); ?>

                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Emergency Contact:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->emergency_contact ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Bank Name:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->default_bank_name ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Account No:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->default_account_no ?? 'N/A'); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking Times:</dt>
                                    <dd class="d-inline ms-2"><?php echo e($booking->customer->bookings->count() ?? 0); ?></dd>
                                </div>
                                <?php if($booking->customer->localStudent || $booking->customer->internationalStudent): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Student Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Matric Number:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localStudent->matric_number ?? ($booking->customer->internationalStudent->matric_number ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localStudent->studentDetails->college ?? ($booking->customer->internationalStudent->studentDetails->college ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Faculty:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localStudent->studentDetails->faculty ?? ($booking->customer->internationalStudent->studentDetails->faculty ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Programme:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localStudent->studentDetails->programme ?? ($booking->customer->internationalStudent->studentDetails->programme ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Year of Study:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localStudent->studentDetails->yearOfStudy ?? ($booking->customer->internationalStudent->studentDetails->yearOfStudy ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                </div>
                                <?php elseif($booking->customer->localUtmStaff || $booking->customer->internationalUtmStaff): ?>
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Staff Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Staff No:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localUtmStaff->staffID ?? ($booking->customer->internationalUtmStaff->staffID ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Position:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localUtmStaff->staffDetails->position ?? ($booking->customer->internationalUtmStaff->staffDetails->position ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            <?php echo e($booking->customer->localUtmStaff->staffDetails->college ?? ($booking->customer->internationalUtmStaff->staffDetails->college ?? 'N/A')); ?>

                                        </dd>
                                    </div>
                                </div>
                                <?php endif; ?>
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
                                        <?php
                                            $licenseImg = $booking->customer->customer_license_img ?? null;
                                        ?>
                                        <?php if($licenseImg): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo e(getFileUrl($licenseImg)); ?>" 
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
                                                            <img src="<?php echo e(getFileUrl($licenseImg)); ?>" 
                                                                 alt="License Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="<?php echo e(getFileUrl($licenseImg)); ?>" 
                                                               target="_blank" 
                                                               class="btn btn-primary">
                                                                <i class="bi bi-download"></i> Open in New Tab
                                                            </a>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="small text-muted mb-2">No license image uploaded</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- IC/Passport Pic -->
                            <div class="col-md-6">
                                <div class="card document-cell h-100" style="border: 1px solid #e5e7eb;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--admin-red);"></i>
                                        <h6 class="fw-semibold"><?php echo e($booking->customer->local ? 'IC' : 'Passport'); ?></h6>
                                        <?php
                                            $icImg = $booking->customer->customer_ic_img ?? null;
                                        ?>
                                        <?php if($icImg): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo e(getFileUrl($icImg)); ?>" 
                                                     alt="<?php echo e($booking->customer->local ? 'IC' : 'Passport'); ?>" 
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
                                                            <h5 class="modal-title">Customer <?php echo e($booking->customer->local ? 'IC' : 'Passport'); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center" style="min-height: 400px;">
                                                            <img src="<?php echo e(getFileUrl($icImg)); ?>" 
                                                                 alt="<?php echo e($booking->customer->local ? 'IC' : 'Passport'); ?> Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="<?php echo e(getFileUrl($icImg)); ?>" 
                                                               target="_blank" 
                                                               class="btn btn-primary">
                                                                <i class="bi bi-download"></i> Open in New Tab
                                                            </a>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="small text-muted mb-2">No <?php echo e($booking->customer->local ? 'IC' : 'Passport'); ?> image uploaded</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>


            <!-- Pickup Condition Form Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'pickup-condition' ? 'show active' : ''); ?>" 
                 id="pickup-condition" 
                 role="tabpanel" 
                 aria-labelledby="pickup-condition-tab">
                
                
                <div class="alert alert-info mb-3">
                    <strong>DEBUG:</strong> Pickup Condition Tab is rendering for Booking #<?php echo e($booking->bookingID); ?>. 
                    <?php $pickupFormCheck = $booking->vehicleConditionForms()->where('form_type', 'RECEIVE')->first(); ?>
                    Form exists: <?php echo e($pickupFormCheck ? 'Yes - Form ID #' . $pickupFormCheck->formID : 'No - No form found'); ?>

                </div>
                
                <?php
                    $pickupForm = $booking->vehicleConditionForms()->where('form_type', 'RECEIVE')->first();
                ?>
                
                <?php if($pickupForm): ?>
                <div class="row g-3">
                    <!-- Form Details Card -->
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Pickup Condition Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Form ID:</dt>
                                            <dd class="col-7">#<?php echo e($pickupForm->formID); ?></dd>
                                            
                                            <dt class="col-5">Form Type:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-success"><?php echo e($pickupForm->form_type); ?></span>
                                            </dd>
                                            
                                            <dt class="col-5">Odometer Reading:</dt>
                                            <dd class="col-7"><strong><?php echo e(number_format($pickupForm->odometer_reading ?? 0)); ?> km</strong></dd>
                                            
                                            <dt class="col-5">Fuel Level:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-info"><?php echo e($pickupForm->fuel_level ?? 'N/A'); ?></span>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Reported Date/Time:</dt>
                                            <dd class="col-7">
                                                <?php if($pickupForm->reported_dated_time): ?>
                                                    <?php echo e(\Carbon\Carbon::parse($pickupForm->reported_dated_time)->format('d M Y, H:i')); ?>

                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Created At:</dt>
                                            <dd class="col-7">
                                                <?php if($pickupForm->created_at): ?>
                                                    <?php echo e(\Carbon\Carbon::parse($pickupForm->created_at)->format('d M Y, H:i')); ?>

                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Scratches/Notes:</dt>
                                            <dd class="col-7"><?php echo e($pickupForm->scratches_notes ?? 'No notes'); ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Agreement Card -->
                    <div class="col-md-6">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none; min-height: 300px;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Rental Agreement</h5>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <?php if($pickupForm->rental_agreement): ?>
                                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 4rem; color: var(--admin-red);"></i>
                                    <p class="mt-3 mb-3">Rental Agreement Document</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm" style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);" data-bs-toggle="modal" data-bs-target="#viewRentalAgreementModal">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <a href="<?php echo e(getFileUrl($pickupForm->rental_agreement)); ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                    
                                    <!-- Rental Agreement Modal -->
                                    <div class="modal fade" id="viewRentalAgreementModal" tabindex="-1">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rental Agreement - Booking #<?php echo e($booking->bookingID); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body" style="height: 80vh;">
                                                    <?php
                                                        $agreementUrl = getFileUrl($pickupForm->rental_agreement);
                                                        $isPdf = str_contains(strtolower($pickupForm->rental_agreement), '.pdf');
                                                    ?>
                                                    <?php if($isPdf): ?>
                                                        <iframe src="<?php echo e($agreementUrl); ?>" width="100%" height="100%" style="border: none;"></iframe>
                                                    <?php else: ?>
                                                        <div class="text-center">
                                                            <img src="<?php echo e($agreementUrl); ?>" alt="Rental Agreement" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Document not found</p>';">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="<?php echo e($agreementUrl); ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No rental agreement uploaded</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Fuel Image Card -->
                    <div class="col-md-6">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none; min-height: 300px;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-fuel-pump"></i> Fuel Level Image</h5>
                            </div>
                            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                                <?php if($pickupForm->fuel_img): ?>
                                    <img src="<?php echo e(getFileUrl($pickupForm->fuel_img)); ?>" alt="Fuel Level" class="img-fluid mb-3" style="max-height: 150px; border-radius: 8px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <p class="text-muted" style="display: none;">Image not found</p>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-sm" style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);" data-bs-toggle="modal" data-bs-target="#viewFuelImgModal">
                                            <i class="bi bi-eye"></i> View Full Size
                                        </button>
                                    </div>
                                    
                                    <!-- Fuel Image Modal -->
                                    <div class="modal fade" id="viewFuelImgModal" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Fuel Level Image - Pickup</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center" style="min-height: 400px;">
                                                    <img src="<?php echo e(getFileUrl($pickupForm->fuel_img)); ?>" alt="Fuel Level" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="<?php echo e(getFileUrl($pickupForm->fuel_img)); ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No fuel image uploaded</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Condition Images -->
                    <?php if($pickupForm->images && $pickupForm->images->count() > 0): ?>
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-images"></i> Vehicle Condition Images (<?php echo e($pickupForm->images->count()); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php $__currentLoopData = $pickupForm->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="card h-100" style="border: 1px solid #e5e7eb;">
                                                <img src="<?php echo e(getFileUrl($image->image_path ?? $image->imagePath)); ?>" alt="Condition Image <?php echo e($index + 1); ?>" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#conditionImageModal<?php echo e($index); ?>" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2RlZTJlNiIvPjx0ZXh0IHg9IjEwMCIgeT0iNzUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3ZnPg==';">
                                                <div class="card-body p-2 text-center">
                                                    <small class="text-muted">Image <?php echo e($index + 1); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Modal -->
                                            <div class="modal fade" id="conditionImageModal<?php echo e($index); ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Condition Image <?php echo e($index + 1); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="<?php echo e(getFileUrl($image->image_path ?? $image->imagePath)); ?>" alt="Condition Image" class="img-fluid" style="max-height: 70vh;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Pickup Vehicle Condition Form</h5>
                            </div>
                            <div class="card-body text-center py-5">
                                <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #6c757d;"></i>
                                <h5 class="mt-3 text-muted">No Pickup Condition Form Submitted</h5>
                                <p class="text-muted mb-0">
                                    The pickup condition form has not been submitted yet.
                                    <?php if($booking->rental_start_date): ?>
                                        <br>Pickup Date: <strong><?php echo e(\Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y, H:i')); ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Return Condition Form Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'return-condition' ? 'show active' : ''); ?>" 
                 id="return-condition" 
                 role="tabpanel" 
                 aria-labelledby="return-condition-tab">
                <?php
                    $returnForm = $booking->vehicleConditionForms()->where('form_type', 'RETURN')->first();
                ?>
                
                <?php if($returnForm): ?>
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
                                            <dd class="col-7">#<?php echo e($returnForm->formID); ?></dd>
                                            
                                            <dt class="col-5">Form Type:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-warning text-dark"><?php echo e($returnForm->form_type); ?></span>
                                            </dd>
                                            
                                            <dt class="col-5">Odometer Reading:</dt>
                                            <dd class="col-7"><strong><?php echo e(number_format($returnForm->odometer_reading ?? 0)); ?> km</strong></dd>
                                            
                                            <dt class="col-5">Fuel Level:</dt>
                                            <dd class="col-7">
                                                <span class="badge bg-info"><?php echo e($returnForm->fuel_level ?? 'N/A'); ?></span>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Reported Date/Time:</dt>
                                            <dd class="col-7">
                                                <?php if($returnForm->reported_dated_time): ?>
                                                    <?php echo e(\Carbon\Carbon::parse($returnForm->reported_dated_time)->format('d M Y, H:i')); ?>

                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Created At:</dt>
                                            <dd class="col-7">
                                                <?php if($returnForm->created_at): ?>
                                                    <?php echo e(\Carbon\Carbon::parse($returnForm->created_at)->format('d M Y, H:i')); ?>

                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </dd>
                                            
                                            <dt class="col-5">Scratches/Notes:</dt>
                                            <dd class="col-7"><?php echo e($returnForm->scratches_notes ?? 'No notes'); ?></dd>
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
                                <?php if($returnForm->rental_agreement): ?>
                                    <i class="bi bi-file-earmark-pdf-fill" style="font-size: 4rem; color: var(--admin-red);"></i>
                                    <p class="mt-3 mb-3">Rental Agreement Document</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm" style="background: white; color: var(--admin-red); border: 1px solid var(--admin-red);" data-bs-toggle="modal" data-bs-target="#viewReturnRentalAgreementModal">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <a href="<?php echo e(getFileUrl($returnForm->rental_agreement)); ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                    
                                    <!-- Rental Agreement Modal -->
                                    <div class="modal fade" id="viewReturnRentalAgreementModal" tabindex="-1">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rental Agreement (Return) - Booking #<?php echo e($booking->bookingID); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body" style="height: 80vh;">
                                                    <?php
                                                        $returnAgreementUrl = getFileUrl($returnForm->rental_agreement);
                                                        $returnIsPdf = str_contains(strtolower($returnForm->rental_agreement), '.pdf');
                                                    ?>
                                                    <?php if($returnIsPdf): ?>
                                                        <iframe src="<?php echo e($returnAgreementUrl); ?>" width="100%" height="100%" style="border: none;"></iframe>
                                                    <?php else: ?>
                                                        <div class="text-center">
                                                            <img src="<?php echo e($returnAgreementUrl); ?>" alt="Rental Agreement" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Document not found</p>';">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="<?php echo e($returnAgreementUrl); ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No rental agreement uploaded</p>
                                <?php endif; ?>
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
                                <?php if($returnForm->fuel_img): ?>
                                    <img src="<?php echo e(getFileUrl($returnForm->fuel_img)); ?>" alt="Fuel Level" class="img-fluid mb-3" style="max-height: 150px; border-radius: 8px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
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
                                                    <img src="<?php echo e(getFileUrl($returnForm->fuel_img)); ?>" alt="Fuel Level" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="<?php echo e(getFileUrl($returnForm->fuel_img)); ?>" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-box-arrow-up-right"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="text-muted mt-3 mb-0">No fuel image uploaded</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Condition Images (Return) -->
                    <?php if($returnForm->images && $returnForm->images->count() > 0): ?>
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-images"></i> Vehicle Condition Images (<?php echo e($returnForm->images->count()); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php $__currentLoopData = $returnForm->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="card h-100" style="border: 1px solid #e5e7eb;">
                                                <img src="<?php echo e(getFileUrl($image->image_path ?? $image->imagePath)); ?>" alt="Condition Image <?php echo e($index + 1); ?>" class="card-img-top" style="height: 150px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#returnConditionImageModal<?php echo e($index); ?>" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2RlZTJlNiIvPjx0ZXh0IHg9IjEwMCIgeT0iNzUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+SW1hZ2UgTm90IEZvdW5kPC90ZXh0Pjwvc3ZnPg==';">
                                                <div class="card-body p-2 text-center">
                                                    <small class="text-muted">Image <?php echo e($index + 1); ?></small>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Modal -->
                                            <div class="modal fade" id="returnConditionImageModal<?php echo e($index); ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Condition Image <?php echo e($index + 1); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center">
                                                            <img src="<?php echo e(getFileUrl($image->image_path ?? $image->imagePath)); ?>" alt="Condition Image" class="img-fluid" style="max-height: 70vh;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
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
                                    <?php if($booking->rental_end_date): ?>
                                        <br>Return Date: <strong><?php echo e(\Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y, H:i')); ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Transaction Detail Tab -->
            <div class="tab-pane fade <?php echo e($activeTab === 'transaction-detail' ? 'show active' : ''); ?>" 
                 id="transaction-detail" 
                 role="tabpanel" 
                 aria-labelledby="transaction-detail-tab">
                
                
                <div class="alert alert-info mb-3">
                    <strong>DEBUG:</strong> Transaction Detail Tab is rendering for Booking #<?php echo e($booking->bookingID); ?>. 
                    Transactions count: <?php echo e($transactions ? $transactions->count() : 0); ?>

                </div>
                
                <div class="row g-3">
                    <!-- Payment History Table -->
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment List</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if($transactions && $transactions->count() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead style="background: #fee2e2;">
                                                <tr>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment ID</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Bank Name</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Bank Acc No</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Date</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Type</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Amount</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Transaction Ref</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Receipt</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Is Complete</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Payment Status</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Is Verify</th>
                                                    <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Verified By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $totalRequiredForPayment = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                                                        $paidAmountForPayment = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                                        $isFullPaymentForPayment = $paidAmountForPayment >= $totalRequiredForPayment;
                                                        $verifiedByUser = $transaction->verified_by ? \App\Models\User::find($transaction->verified_by) : null;
                                                    ?>
                                                    <tr>
                                                        <td><strong>#<?php echo e($transaction->paymentID); ?></strong></td>
                                                        <td><?php echo e($transaction->payment_bank_name ?? 'N/A'); ?></td>
                                                        <td><?php echo e($transaction->payment_bank_account_no ?? 'N/A'); ?></td>
                                                        <td><?php echo e($transaction->payment_date ? \Carbon\Carbon::parse($transaction->payment_date)->format('d M Y') : 'N/A'); ?></td>
                                                        <td><?php echo e($transaction->payment_type ?? 'N/A'); ?></td>
                                                        <td><strong>RM <?php echo e(number_format($transaction->total_amount ?? 0, 2)); ?></strong></td>
                                                        <td>
                                                            <?php if($transaction->transaction_reference && !str_contains($transaction->transaction_reference ?? '', '.')): ?>
                                                                <span class="text-muted small"><?php echo e(strlen($transaction->transaction_reference) > 15 ? substr($transaction->transaction_reference, 0, 15) . '...' : $transaction->transaction_reference); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                $receiptPath = $transaction->proof_of_payment ?? $transaction->transaction_reference ?? null;
                                                                $hasReceipt = $receiptPath && (str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf') || str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/'));
                                                            ?>
                                                            <?php if($hasReceipt): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#receiptModal<?php echo e($transaction->paymentID); ?>">
                                                                    <i class="bi bi-receipt"></i> View
                                                                </button>
                                                                <!-- Receipt Modal -->
                                                                <div class="modal fade" id="receiptModal<?php echo e($transaction->paymentID); ?>" tabindex="-1">
                                                                    <div class="modal-dialog modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title">Receipt - Payment #<?php echo e($transaction->paymentID); ?></h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <img src="<?php echo e(getFileUrl($receiptPath)); ?>" alt="Receipt" class="img-fluid" style="max-height: 70vh;" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <a href="<?php echo e(getFileUrl($receiptPath)); ?>" target="_blank" class="btn btn-primary">Open in New Tab</a>
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo e($isFullPaymentForPayment ? 'bg-success' : 'bg-warning text-dark'); ?>">
                                                                <?php echo e($isFullPaymentForPayment ? 'Yes' : 'No'); ?>

                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                $status = $transaction->payment_status ?? 'Pending';
                                                                $statusClass = match($status) {
                                                                    'Verified', 'Full' => 'bg-success',
                                                                    'Pending' => 'bg-warning text-dark',
                                                                    'Rejected' => 'bg-danger',
                                                                    default => 'bg-secondary'
                                                                };
                                                            ?>
                                                            <span class="badge <?php echo e($statusClass); ?>"><?php echo e($status); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php echo e(($transaction->payment_isVerify ?? false) ? 'bg-success' : 'bg-secondary'); ?>">
                                                                <?php echo e(($transaction->payment_isVerify ?? false) ? 'Yes' : 'No'); ?>

                                                            </span>
                                                        </td>
                                                        <td><?php echo e($verifiedByUser->name ?? 'N/A'); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-receipt-cutoff fs-1 text-muted d-block mb-3"></i>
                                        <p class="text-muted">No payment records found for this booking.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Deposit Details -->
                    <div class="col-12">
                        <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 12px; border: none;">
                            <div class="card-header bg-danger text-white" style="border-radius: 12px 12px 0 0;">
                                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Deposit Details</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead style="background: #fee2e2;">
                                            <tr>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Deposit Payment</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Vehicle Condition Form</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Customer Choice</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Fine Amount</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Originally</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Refund Amount</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Refund Status</th>
                                                <th style="border-bottom: 2px solid #b91c1c; color: #7f1d1d;">Handled By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>RM <?php echo e(number_format($booking->deposit_amount ?? 0, 2)); ?></strong></td>
                                                <td>
                                                    <?php
                                                        $hasReturnForm = $booking->vehicleConditionForms && $booking->vehicleConditionForms->where('form_type', 'RETURN')->first();
                                                    ?>
                                                    <?php if($hasReturnForm): ?>
                                                        <span class="badge bg-success">Submitted</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($booking->deposit_customer_choice): ?>
                                                        <span class="badge <?php echo e($booking->deposit_customer_choice === 'refund' ? 'bg-info' : 'bg-secondary'); ?>">
                                                            <?php echo e(ucfirst($booking->deposit_customer_choice)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong style="color: red;">RM <?php echo e(number_format($booking->deposit_fine_amount ?? 0, 2)); ?></strong></td>
                                                <td><strong>RM <?php echo e(number_format($booking->deposit_amount ?? 0, 2)); ?></strong></td>
                                                <td><strong style="color: green;">RM <?php echo e(number_format($booking->deposit_refund_amount ?? 0, 2)); ?></strong></td>
                                                <td>
                                                    <?php
                                                        $refundStatus = $booking->deposit_refund_status ?? 'pending';
                                                        $refundStatusClass = match($refundStatus) {
                                                            'refunded' => 'bg-success',
                                                            'pending' => 'bg-warning text-dark',
                                                            'rejected' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                    ?>
                                                    <span class="badge <?php echo e($refundStatusClass); ?>"><?php echo e(ucfirst($refundStatus)); ?></span>
                                                </td>
                                                <td>
                                                    <?php if($booking->deposit_handled_by): ?>
                                                        <?php
                                                            $handler = \App\Models\User::find($booking->deposit_handled_by);
                                                        ?>
                                                        <?php echo e($handler->name ?? 'N/A'); ?>

                                                    <?php else: ?>
                                                        <span class="text-muted">Not Assigned</span>
                                                    <?php endif; ?>
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
                        <?php $__currentLoopData = $staffUsers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($staff->userID); ?>" <?php echo e($booking->staff_served == $staff->userID ? 'selected' : ''); ?>>
                                <?php echo e($staff->name); ?> (<?php echo e($staff->isAdmin() ? 'Admin' : 'Staff IT'); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $runners ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $runner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isCurrentRunner = false;
                                if ($booking->staff_served) {
                                    $currentUser = \App\Models\User::find($booking->staff_served);
                                    if ($currentUser && $currentUser->isRunner() && $currentUser->userID == $runner->userID) {
                                        $isCurrentRunner = true;
                                    }
                                }
                            ?>
                            <option value="<?php echo e($runner->userID); ?>" <?php echo e($isCurrentRunner ? 'selected' : ''); ?>>
                                <?php echo e($runner->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    // Initialize active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'booking-detail';
        const tabButton = document.querySelector(`#${tab}-tab`);
        if (tabButton && '<?php echo e($activeTab); ?>' !== tab) {
            const tabInstance = new bootstrap.Tab(tabButton);
            tabInstance.show();
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
        
        fetch('<?php echo e(route('admin.bookings.reservations.update-status', $booking->bookingID)); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
        
        fetch('<?php echo e(route('admin.bookings.reservations.update-runner', $booking->bookingID)); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/reservations/show.blade.php ENDPATH**/ ?>