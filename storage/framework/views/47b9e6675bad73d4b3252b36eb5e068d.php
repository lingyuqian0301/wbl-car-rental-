<?php $__env->startSection('title', 'Invoices'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .invoice-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table-header {
        background: var(--admin-red);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
    }
    .table thead th {
        background: var(--admin-red-light);
        color: var(--admin-red-dark);
        font-weight: 600;
        border-bottom: 2px solid var(--admin-red);
        padding: 12px;
        font-size: 0.9rem;
    }
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .filter-row > div {
        min-width: 0;
    }
    .filter-row .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
    }
    .filter-row .form-control,
    .filter-row .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .filter-row .btn {
        font-size: 0.85rem;
        padding: 4px 12px;
    }
    .invoice-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .invoice-info-text div {
        margin-bottom: 2px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Invoices','description' => 'View and manage all invoices','stats' => [
            ['label' => 'Total Invoices', 'value' => $totalInvoices, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
            ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
        ],'date' => $today]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Invoices','description' => 'View and manage all invoices','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Total Invoices', 'value' => $totalInvoices, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
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

    <!-- Action Buttons - Right Top Corner -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light text-danger" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="<?php echo e(route('admin.invoices.export-pdf', request()->query())); ?>" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="<?php echo e(route('admin.invoices.export-excel', request()->query())); ?>" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="<?php echo e(route('admin.invoices.index')); ?>">
            <div class="row g-2 mb-2">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="Plate No">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="invoice_no_asc" <?php echo e(($sort ?? 'invoice_no_asc') === 'invoice_no_asc' ? 'selected' : ''); ?>>Asc Invoice No</option>
                        <option value="issue_date_desc" <?php echo e(($sort ?? '') === 'issue_date_desc' ? 'selected' : ''); ?>>Desc Issue Date</option>
                        <option value="pickup_date_desc" <?php echo e(($sort ?? '') === 'pickup_date_desc' ? 'selected' : ''); ?>>Desc Pickup Date</option>
                    </select>
                </div>
                
                <!-- Date Filter Type -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Filter By</label>
                    <select name="date_filter_type" class="form-select form-select-sm" id="dateFilterType">
                        <option value="issue_date" <?php echo e(($dateFilterType ?? 'issue_date') === 'issue_date' ? 'selected' : ''); ?>>Issue Date</option>
                        <option value="pickup_date" <?php echo e(($dateFilterType ?? '') === 'pickup_date' ? 'selected' : ''); ?>>Pickup Date</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e($dateFrom ?? ''); ?>">
                </div>
                
                <!-- Date To -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e($dateTo ?? ''); ?>">
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                </div>
                <?php if($search || $dateFrom || $dateTo || ($sort ?? '') !== 'invoice_no_asc'): ?>
                <div class="col-md-auto">
                    <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="invoice-table">
        <div class="table-header">
            <i class="bi bi-file-earmark-text"></i> All Invoices (<?php echo e($bookings->total()); ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Customer Name</th>
                        <th>Booking Date</th>
                        <th>Invoice Picture</th>
                        <th>Car Plate No</th>
                        <th>Total Payment Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $invoice = $booking->invoice;
                            $customer = $booking->customer;
                            $user = $customer->user ?? null;
                            $vehicle = $booking->vehicle;
                            $additionalCharges = $booking->additionalCharges;
                            
                            // Calculate total payment amount
                            $totalPaid = $booking->payments()
                                ->where('payment_status', 'Verified')
                                ->sum('total_amount');
                            
                            $depositAmount = $booking->deposit_amount ?? 0;
                            
                            // FIX: Added fallback to total_amount if rental_amount is missing
                            $rentalAmount = $booking->rental_amount ?? $booking->total_amount ?? 0;
                            
                            $additionalChargesTotal = $additionalCharges ? ($additionalCharges->total_extra_charge ?? 0) : 0;
                            $totalPaymentAmount = $depositAmount + $rentalAmount + $additionalChargesTotal;
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo e($invoice->invoiceID ?? 'N/A'); ?></strong>
                                <div class="invoice-info-text">
                                    Booking ID: #<?php echo e($booking->bookingID); ?>

                                </div>
                            </td>
                            <td>
                                <?php echo e($user->name ?? 'Unknown Customer'); ?>

                            </td>
                            <td>
                                
                                <?php if($booking->start_date): ?>
                                    <?php echo e(\Carbon\Carbon::parse($booking->start_date)->format('d M Y')); ?>

                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($invoice): ?>
                                    <a href="<?php echo e(route('invoices.generate', $booking->bookingID)); ?>" 
                                       class="btn btn-sm btn-danger" 
                                       target="_blank"
                                       title="View Invoice PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">No invoice</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($vehicle): ?>
                                    <strong><?php echo e($vehicle->plate_number ?? 'N/A'); ?></strong>
                                    <div class="invoice-info-text">
                                        <?php echo e(($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '')); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $totalRequired = $depositAmount + $rentalAmount;
                                    $outstandingBalance = max(0, $totalRequired - $totalPaid);
                                ?>
                                <strong>RM <?php echo e(number_format($totalRequired, 2)); ?></strong>
                                <div class="invoice-info-text">
                                    <div>Paid: RM <?php echo e(number_format($totalPaid, 2)); ?></div>
                                    <div>Outstanding: RM <?php echo e(number_format($outstandingBalance, 2)); ?></div>
                                    <?php if($additionalChargesTotal > 0): ?>
                                        <div class="mt-1">Additional Charges: RM <?php echo e(number_format($additionalChargesTotal, 2)); ?></div>
                                        <?php if($additionalCharges): ?>
                                            <div class="mt-1">
                                                <?php if($additionalCharges->addOns_charge > 0): ?>
                                                    <small>Add-ons: RM <?php echo e(number_format($additionalCharges->addOns_charge, 2)); ?></small><br>
                                                <?php endif; ?>
                                                <?php if($additionalCharges->late_return_fee > 0): ?>
                                                    <small>Late Return: RM <?php echo e(number_format($additionalCharges->late_return_fee, 2)); ?></small><br>
                                                <?php endif; ?>
                                                <?php if($additionalCharges->damage_fee > 0): ?>
                                                    <small>Damage: RM <?php echo e(number_format($additionalCharges->damage_fee, 2)); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No invoices available.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($bookings->hasPages()): ?>
            <div class="p-3 border-top">
                <?php echo e($bookings->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/invoices/index.blade.php ENDPATH**/ ?>