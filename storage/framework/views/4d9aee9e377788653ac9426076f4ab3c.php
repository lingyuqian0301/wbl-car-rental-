<?php $__env->startSection('title', 'Deposit Management'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .payment-table {
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
        white-space: nowrap;
    }
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-2">
    <?php if (isset($component)) { $__componentOriginal8e6ccc94deb46dfd1314097afabe5570 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e6ccc94deb46dfd1314097afabe5570 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-page-header','data' => ['title' => 'Deposit Management','description' => 'Manage deposit return requests','stats' => [
            ['label' => 'Deposit Hold', 'value' => 'RM ' . number_format($depositHold ?? 0, 2), 'icon' => 'bi-wallet'],
            ['label' => 'Deposit Not Yet Process', 'value' => $depositNotYetProcess ?? 0, 'icon' => 'bi-clock-history']
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Deposit Management','description' => 'Manage deposit return requests','stats' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Deposit Hold', 'value' => 'RM ' . number_format($depositHold ?? 0, 2), 'icon' => 'bi-wallet'],
            ['label' => 'Deposit Not Yet Process', 'value' => $depositNotYetProcess ?? 0, 'icon' => 'bi-clock-history']
        ])]); ?>
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

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light text-danger" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.deposits.index')); ?>" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="<?php echo e($search ?? ''); ?>" 
                           class="form-control form-control-sm" 
                           placeholder="Booking ID, Customer Name, Plate No">
                </div>
                
                <!-- Refund Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Refund Status</label>
                    <select name="filter_refund_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" <?php echo e(($filterRefundStatus ?? '') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="refunded" <?php echo e(($filterRefundStatus ?? '') === 'refunded' ? 'selected' : ''); ?>>Refunded</option>
                    </select>
                </div>
                
                <!-- Customer Choice Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Choice</label>
                    <select name="filter_customer_choice" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="hold" <?php echo e(($filterCustomerChoice ?? '') === 'hold' ? 'selected' : ''); ?>>Hold</option>
                        <option value="refund" <?php echo e(($filterCustomerChoice ?? '') === 'refund' ? 'selected' : ''); ?>>Refund</option>
                    </select>
                </div>
                
                <!-- Handled By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Handled By</label>
                    <select name="filter_handled_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php $__currentLoopData = $staffUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->userID); ?>" <?php echo e(($filterHandledBy ?? '') == $user->userID ? 'selected' : ''); ?>><?php echo e($user->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <?php if($search || $filterRefundStatus || $filterHandledBy || $filterCustomerChoice): ?>
                        <a href="<?php echo e(route('admin.deposits.index')); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="payment-table">
        <div class="table-header">
            <i class="bi bi-wallet"></i> All Deposits (<?php echo e($bookings->total()); ?>)
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Deposit Payment</th>
                        <th>Vehicle Condition Form</th>
                        <th>Customer Choice</th>
                        <th>Fine Amount</th>
                        <th>Originally</th>
                        <th>Refund Amount</th>
                        <th>Refund Status</th>
                        <th>Handled By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $customer = $booking->customer;
                            $user = $customer->user ?? null;
                            $refundStatus = $booking->deposit_refund_status ?? 'no_action';
                            $handledBy = $booking->deposit_handled_by ? \App\Models\User::find($booking->deposit_handled_by) : null;
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('admin.bookings.reservations', ['search' => $booking->bookingID])); ?>" class="text-decoration-none fw-bold text-primary">
                                    #<?php echo e($booking->bookingID); ?>

                                </a>
                            </td>
                            <td>
                                <?php echo e($user->name ?? 'N/A'); ?>

                            </td>
                            <td>
                                <strong>RM <?php echo e(number_format($booking->deposit_amount ?? 0, 2)); ?></strong>
                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.deposits.show', $booking->bookingID)); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> View Form
                                </a>
                            </td>
                            <td>
                                <?php if($booking->deposit_customer_choice): ?>
                                    <span class="badge <?php echo e($booking->deposit_customer_choice === 'hold' ? 'bg-info' : 'bg-warning text-dark'); ?>">
                                        <?php echo e(ucfirst($booking->deposit_customer_choice)); ?>

                                    </span>
                                    <div class="small text-muted mt-1">
                                        <?php echo e($booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y H:i') : 'N/A'); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($booking->deposit_fine_amount): ?>
                                    <strong>RM <?php echo e(number_format($booking->deposit_fine_amount, 2)); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.deposits.show', $booking->bookingID)); ?>?tab=deposit-detail" class="text-muted text-decoration-none">-</a>
                            </td>
                            <td>
                                <?php if($booking->deposit_refund_amount): ?>
                                    <strong>RM <?php echo e(number_format($booking->deposit_refund_amount, 2)); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $statusText = 'Pending';
                                    $statusClass = 'bg-warning text-dark';
                                    if ($booking->deposit_refund_status === 'refunded') {
                                        $statusText = 'Refunded';
                                        $statusClass = 'bg-success';
                                    } elseif ($booking->deposit_refund_status === 'pending' || ($booking->deposit_customer_choice === 'refund' && !$booking->deposit_refund_status)) {
                                        $statusText = 'Pending';
                                        $statusClass = 'bg-warning text-dark';
                                    }
                                ?>
                                <span class="badge <?php echo e($statusClass); ?>">
                                    <?php echo e($statusText); ?>

                                </span>
                            </td>
                            <td>
                                <?php echo e($handledBy->name ?? 'N/A'); ?>

                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.bookings.reservations.show', $booking->bookingID)); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No deposits found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <?php echo e($bookings->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/deposits/index.blade.php ENDPATH**/ ?>