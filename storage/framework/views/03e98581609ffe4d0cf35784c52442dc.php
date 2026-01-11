<?php $__env->startSection('title', 'Reservations'); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($activeTab ?? 'bookings') === 'bookings' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                <i class="bi bi-calendar-check"></i> Bookings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($activeTab ?? '') === 'leasing' ? 'active' : ''); ?>" data-bs-toggle="tab" data-bs-target="#leasing" type="button" role="tab">
                <i class="bi bi-file-earmark-text"></i> Leasing
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Bookings Tab -->
        <div class="tab-pane fade <?php echo e(($activeTab ?? 'bookings') === 'bookings' ? 'show active' : ''); ?>" id="bookings" role="tabpanel">
            <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Reservations','description' => 'Manage all booking reservations','stats' => [
                    ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
                    ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
                    ['label' => 'Confirmed', 'value' => $totalConfirmed, 'icon' => 'bi-check-circle'],
                    ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
                ],'date' => $today]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reservations','description' => 'Manage all booking reservations','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
                    ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
                    ['label' => 'Confirmed', 'value' => $totalConfirmed, 'icon' => 'bi-check-circle'],
                    ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
                ]),'date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $attributes = $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $component = $__componentOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>

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

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.bookings.reservations', ['tab' => 'bookings'])); ?>" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="Booking ID, Plate No, Customer Name">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="">Default (Desc Booking ID)</option>
                        <option value="booking_date_desc" <?php echo e(($sort ?? '') === 'booking_date_desc' ? 'selected' : ''); ?>>Desc Booking Date</option>
                        <option value="pickup_date_desc" <?php echo e(($sort ?? '') === 'pickup_date_desc' ? 'selected' : ''); ?>>Desc Pickup Date</option>
                    </select>
                </div>
                
                <!-- Plate No Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Plate No</label>
                    <select name="filter_plate_no" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $plateNumbers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($plate); ?>" <?php echo e($filterPlateNo === $plate ? 'selected' : ''); ?>><?php echo e($plate); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Pickup Date Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Pickup Date</label>
                    <input type="date" name="filter_pickup_date" value="<?php echo e($filterPickupDate); ?>" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Return Date Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Return Date</label>
                    <input type="date" name="filter_return_date" value="<?php echo e($filterReturnDate); ?>" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Served By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Served By</label>
                    <select name="filter_served_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $staffUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($staff->userID); ?>" <?php echo e($filterServedBy == $staff->userID ? 'selected' : ''); ?>><?php echo e($staff->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Booking Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Booking Status</label>
                    <select name="filter_booking_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $bookingStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php echo e($filterBookingStatus === $status ? 'selected' : ''); ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Payment Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Status</label>
                    <select name="filter_payment_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $paymentStatuses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php echo e($filterPaymentStatus === $status ? 'selected' : ''); ?>><?php echo e($status); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="<?php echo e(route('admin.bookings.reservations', ['tab' => 'bookings'])); ?>" class="btn btn-sm btn-outline-secondary">
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
            <span class="badge bg-light text-dark"><?php echo e($bookings->total()); ?> total</span>
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
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
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
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('admin.bookings.reservations.show', $booking->bookingID)); ?>" class="text-decoration-none fw-bold text-primary">
                                        #<?php echo e($booking->bookingID); ?>

                                    </a>
                                </td>
                                <td><?php echo e($user->name ?? 'Unknown'); ?></td>
                                <td>
                                    <strong><?php echo e($vehicle->plate_number ?? ($vehicle->plate_no ?? 'N/A')); ?></strong>
                                </td>
                                <td>
                                    <strong>RM <?php echo e(number_format($totalAmount, 2)); ?></strong>
                                    <div class="reservation-info-text">
                                        <div>Paid: RM <?php echo e(number_format($totalPaid, 2)); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($invoice): ?>
                                        <strong><?php echo e($invoice->invoice_number ?? 'N/A'); ?></strong>
                                        <div class="reservation-info-text">
                                            <div>
                                                <?php
                                                    $firstPayment = $booking->payments->first();
                                                    $invoiceRoute = $firstPayment ? route('admin.payments.invoice', $firstPayment->paymentID) : route('invoices.generate', $booking->bookingID);
                                                ?>
                                                <a href="<?php echo e($invoiceRoute); ?>" 
                                                   target="_blank" class="text-primary">
                                                    <i class="bi bi-file-pdf"></i> View Invoice
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No invoice</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($latestPayment): ?>
                                        <a href="<?php echo e(route('admin.bookings.reservations.show', ['booking' => $booking->bookingID, 'tab' => 'transaction-detail'])); ?>" 
                                           class="text-decoration-none fw-bold text-primary"
                                           target="_blank">
                                            <strong>#<?php echo e($latestPayment->paymentID ?? 'N/A'); ?></strong>
                                        </a>
                                        <div class="reservation-info-text">
                                            <div>
                                                <?php if($latestPayment->transaction_reference): ?>
                                                    <?php
                                                        // Check if transaction_reference is a file path
                                                        $receiptPath = $latestPayment->transaction_reference;
                                                        $isImagePath = str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/') || str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf');
                                                        
                                                        if ($isImagePath) {
                                                            if (str_starts_with($receiptPath, 'uploads/')) {
                                                                $imageUrl = asset($receiptPath);
                                                            } else {
                                                                $imageUrl = asset('storage/' . $receiptPath);
                                                            }
                                                        } else {
                                                            $imageUrl = null;
                                                        }
                                                    ?>
                                                    <?php if($imageUrl): ?>
                                                        <a href="<?php echo e($imageUrl); ?>" 
                                                           target="_blank" 
                                                           class="text-primary"
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#receiptModal<?php echo e($latestPayment->paymentID); ?>">
                                                            <i class="bi bi-image"></i> View Receipt
                                                        </a>
                                                        <!-- Receipt Modal -->
                                                        <div class="modal fade" id="receiptModal<?php echo e($latestPayment->paymentID); ?>" tabindex="-1">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Receipt - Payment #<?php echo e($latestPayment->paymentID); ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body text-center">
                                                                        <?php if(str_contains(strtolower($receiptPath), '.pdf')): ?>
                                                                            <iframe src="<?php echo e($imageUrl); ?>" style="width: 100%; height: 600px;"></iframe>
                                                                        <?php else: ?>
                                                                            <img src="<?php echo e($imageUrl); ?>" alt="Receipt" class="img-fluid" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small" title="Transaction Reference"><?php echo e(strlen($latestPayment->transaction_reference) > 20 ? substr($latestPayment->transaction_reference, 0, 20) . '...' : $latestPayment->transaction_reference); ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No receipt</span>
                                                <?php endif; ?>
                                            </div>
                                            <div><strong>Date:</strong> <?php echo e($latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M Y') : 'N/A'); ?></div>
                                            <div><strong>Time:</strong> <?php echo e($latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('H:i') : 'N/A'); ?></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No payment</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo e($paymentStatus === 'Full' ? 'bg-success' : ($paymentStatus === 'Deposit' ? 'bg-warning text-dark' : ($paymentStatus === 'Refunded' ? 'bg-info' : 'bg-secondary'))); ?>">
                                        <?php echo e($paymentStatus); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="reservation-info-text">
                                        <?php
                                            $pickupDateTime = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                                        ?>
                                        <div><strong>Date:</strong> <?php echo e($pickupDateTime ? $pickupDateTime->format('d M Y') : 'N/A'); ?></div>
                                        <div><strong>Time:</strong> <?php echo e($pickupDateTime ? $pickupDateTime->format('H:i') : ($booking->pickup_time ?? 'N/A')); ?></div>
                                        <div><strong>Location:</strong> <?php echo e($booking->pickup_point ?? $booking->pickup_location ?? 'N/A'); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="reservation-info-text">
                                        <?php
                                            $returnDateTime = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                                        ?>
                                        <div><strong>Date:</strong> <?php echo e($returnDateTime ? $returnDateTime->format('d M Y') : 'N/A'); ?></div>
                                        <div><strong>Time:</strong> <?php echo e($returnDateTime ? $returnDateTime->format('H:i') : ($booking->return_time ?? 'N/A')); ?></div>
                                        <div><strong>Location:</strong> <?php echo e($booking->return_point ?? $booking->return_location ?? 'N/A'); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm booking-status-select" 
                                            data-booking-id="<?php echo e($booking->bookingID); ?>"
                                            data-current-status="<?php echo e($booking->booking_status); ?>">
                                        <?php $__currentLoopData = $bookingStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php echo e($booking->booking_status === $status ? 'selected' : ''); ?>>
                                                <?php echo e($status); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm served-by-select" 
                                            data-booking-id="<?php echo e($booking->bookingID); ?>"
                                            data-current-served="<?php echo e($booking->staff_served); ?>">
                                        <option value="">Not Assigned</option>
                                        <?php $__currentLoopData = $staffUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($staff->userID); ?>" <?php echo e($booking->staff_served == $staff->userID ? 'selected' : ''); ?>>
                                                <?php echo e($staff->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-column">
                                        <a href="<?php echo e(route('admin.bookings.reservations.show', ['booking' => $booking->bookingID, 'tab' => 'pickup-condition'])); ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           target="_blank"
                                           title="View Vehicle Condition">
                                            <i class="bi bi-clipboard-check"></i> Condition
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No reservations found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($bookings->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($bookings->links()); ?>

            </div>
        <?php endif; ?>
    </div>
        </div>

        <!-- Leasing Tab -->
        <div class="tab-pane fade <?php echo e(($activeTab ?? '') === 'leasing' ? 'show active' : ''); ?>" id="leasing" role="tabpanel">
            <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Vehicle Leasing','description' => 'Manage vehicle leasing bookings (more than 15 days)','stats' => [
                    ['label' => 'Total Bookings', 'value' => $leasingStats['totalBookings'] ?? 0, 'icon' => 'bi-calendar'],
                    ['label' => 'Total Revenue', 'value' => 'RM ' . number_format($leasingStats['totalRevenue'] ?? 0, 2), 'icon' => 'bi-currency-dollar'],
                    ['label' => 'Total Paid', 'value' => 'RM ' . number_format($leasingStats['totalPaid'] ?? 0, 2), 'icon' => 'bi-check-circle'],
                    ['label' => 'Ongoing', 'value' => $leasingStats['ongoingBookings'] ?? 0, 'icon' => 'bi-clock']
                ],'date' => $today]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Vehicle Leasing','description' => 'Manage vehicle leasing bookings (more than 15 days)','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                    ['label' => 'Total Bookings', 'value' => $leasingStats['totalBookings'] ?? 0, 'icon' => 'bi-calendar'],
                    ['label' => 'Total Revenue', 'value' => 'RM ' . number_format($leasingStats['totalRevenue'] ?? 0, 2), 'icon' => 'bi-currency-dollar'],
                    ['label' => 'Total Paid', 'value' => 'RM ' . number_format($leasingStats['totalPaid'] ?? 0, 2), 'icon' => 'bi-check-circle'],
                    ['label' => 'Ongoing', 'value' => $leasingStats['ongoingBookings'] ?? 0, 'icon' => 'bi-clock']
                ]),'date' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $attributes = $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__attributesOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570)): ?>
<?php $component = $__componentOriginal8e6ccc94deb46dfd1314097afabe5570; ?>
<?php unset($__componentOriginal8e6ccc94deb46dfd1314097afabe5570); ?>
<?php endif; ?>

            <!-- Filters for Leasing -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('admin.bookings.reservations', ['tab' => 'leasing'])); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all" <?php echo e(($statusFilter ?? 'all') === 'all' ? 'selected' : ''); ?>>All</option>
                                <option value="future" <?php echo e(($statusFilter ?? '') === 'future' ? 'selected' : ''); ?>>Future</option>
                                <option value="ongoing" <?php echo e(($statusFilter ?? '') === 'ongoing' ? 'selected' : ''); ?>>Ongoing</option>
                                <option value="past" <?php echo e(($statusFilter ?? '') === 'past' ? 'selected' : ''); ?>>Past</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-funnel"></i> Apply Filters
                            </button>
                            <?php if($statusFilter && $statusFilter !== 'all'): ?>
                                <a href="<?php echo e(route('admin.bookings.reservations', ['tab' => 'leasing'])); ?>" class="btn btn-sm btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leasing Bookings Table -->
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vehicle Leasing Bookings</h5>
                    <span class="badge bg-light text-dark"><?php echo e($leasingBookings->total()); ?> total</span>
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
                                <?php $__empty_1 = true; $__currentLoopData = $leasingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                        $vehicle = $booking->vehicle;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('admin.bookings.reservations', ['search' => $booking->bookingID, 'tab' => 'bookings'])); ?>" class="text-decoration-none fw-bold text-primary">
                                                #<?php echo e($booking->bookingID); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($user->name ?? 'Unknown'); ?></td>
                                        <td><strong><?php echo e($vehicle->plate_number ?? 'N/A'); ?></strong></td>
                                        <td><strong><?php echo e($booking->duration ?? 0); ?> days</strong></td>
                                        <td><?php echo e($booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A'); ?></td>
                                        <td><?php echo e($booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A'); ?></td>
                                        <td><strong>RM <?php echo e(number_format(($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0), 2)); ?></strong></td>
                                        <td>
                                            <span class="badge <?php echo e($booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : 'bg-info')); ?>">
                                                <?php echo e($booking->booking_status ?? 'N/A'); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('admin.bookings.reservations', ['search' => $booking->bookingID, 'tab' => 'bookings'])); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No leasing bookings found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if($leasingBookings->hasPages()): ?>
                    <div class="card-footer">
                        <?php echo e($leasingBookings->links()); ?>

                    </div>
                <?php endif; ?>
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

<?php $__env->startPush('scripts'); ?>
<script>
    // Notification function
    function showNotification(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

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
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    booking_status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.currentStatus = newStatus;
                    // Show success message
                    showNotification('Booking status updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to update booking status.', 'error');
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
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    staff_served: newServedBy || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.currentServed = newServedBy;
                    // Show success message
                    showNotification('Served by updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to update served by.', 'error');
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
        // Fetch review data for the booking via API endpoint
        fetch(`<?php echo e(route('admin.bookings.reviews.get-by-booking')); ?>?booking_id=${bookingId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.review) {
                    const review = data.review;
                    const reviewDate = review.review_date ? new Date(review.review_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
                    let html = `
                        <div class="review-details">
                            <div class="mb-3">
                                <strong>Booking ID:</strong> #${bookingId}
                            </div>
                            <div class="mb-3">
                                <strong>Rating:</strong> 
                                <div class="d-flex align-items-center gap-1">
                                    ${Array.from({length: 5}, (_, i) => 
                                        `<i class="bi bi-star${i < (review.rating || 0) ? '-fill text-warning' : ''}"></i>`
                                    ).join('')}
                                    <span class="ms-2">${review.rating || 'N/A'}/5</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Review Date:</strong> ${reviewDate}
                            </div>
                            <div class="mb-3">
                                <strong>Comment:</strong>
                                <p class="mt-2 p-3 bg-light rounded">${review.comment || 'No comment provided.'}</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('reviewContent').innerHTML = html;
                } else {
                    document.getElementById('reviewContent').innerHTML = '<p class="text-muted">No review found for booking #' + bookingId + '.</p>';
                }
                new bootstrap.Modal(document.getElementById('reviewModal')).show();
            })
            .catch(error => {
                console.error('Error fetching review:', error);
                document.getElementById('reviewContent').innerHTML = '<p class="text-danger">Error loading review. Please try again.</p>';
                new bootstrap.Modal(document.getElementById('reviewModal')).show();
            });
    }

    // View Vehicle Condition
    function viewVehicleCondition(bookingId) {
        // TODO: Implement vehicle condition form view
        document.getElementById('vehicleConditionContent').innerHTML = '<p>Pickup and return vehicle condition form for booking #' + bookingId + ' will be displayed here.</p>';
        new bootstrap.Modal(document.getElementById('vehicleConditionModal')).show();
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/reservations/index.blade.php ENDPATH**/ ?>