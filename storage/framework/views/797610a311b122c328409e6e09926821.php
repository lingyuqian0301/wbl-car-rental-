<?php $__env->startSection('title', 'Runner Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Header Box -->
    <div class="header-box">
        <h2><i class="bi bi-speedometer2"></i> Welcome, <?php echo e($user->name ?? 'Runner'); ?>!</h2>
        <p><?php echo e($today->format('l, d F Y')); ?></p>
        <div class="header-stats">
            <div class="header-stat">
                <div class="header-stat-value"><?php echo e($totalTasks); ?></div>
                <div class="header-stat-label">Total Tasks</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value"><?php echo e($upcomingTasks); ?></div>
                <div class="header-stat-label">Upcoming</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value"><?php echo e($doneTasks); ?></div>
                <div class="header-stat-label">Completed</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value">RM <?php echo e(number_format($monthlyCommission, 2)); ?></div>
                <div class="header-stat-label">This Month Commission</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Today's Tasks -->
        <div class="col-md-8">
            <div class="runner-card">
                <div class="card-header-green d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-check"></i> Today's Tasks</span>
                    <span class="badge bg-light text-dark"><?php echo e($todayTasks->count()); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="runner-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Type</th>
                                <th>Vehicle</th>
                                <th>Customer</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $todayTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $pickupDate = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                                    $returnDate = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                                    $isPickupToday = $pickupDate && $pickupDate->isToday();
                                    $isReturnToday = $returnDate && $returnDate->isToday();
                                ?>
                                <?php if($isPickupToday && !empty($booking->pickup_point) && $booking->pickup_point !== 'HASTA HQ Office'): ?>
                                    <tr>
                                        <td><strong>#<?php echo e($booking->bookingID); ?></strong></td>
                                        <td><span class="badge badge-pickup">Pickup</span></td>
                                        <td><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->customer->user->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->pickup_point); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if($isReturnToday && !empty($booking->return_point) && $booking->return_point !== 'HASTA HQ Office'): ?>
                                    <tr>
                                        <td><strong>#<?php echo e($booking->bookingID); ?></strong></td>
                                        <td><span class="badge badge-return">Return</span></td>
                                        <td><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->customer->user->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->return_point); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No tasks for today</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="runner-card mb-4">
                <div class="card-header-green">
                    <i class="bi bi-graph-up"></i> Quick Stats
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Total Tasks</span>
                        <strong><?php echo e($totalTasks); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Upcoming Tasks</span>
                        <span class="badge badge-upcoming"><?php echo e($upcomingTasks); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Completed Tasks</span>
                        <span class="badge badge-done"><?php echo e($doneTasks); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">This Month Commission</span>
                        <strong class="text-danger">RM <?php echo e(number_format($monthlyCommission, 2)); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="runner-card">
                <div class="card-header-green">
                    <i class="bi bi-lightning"></i> Quick Actions
                </div>
                <div class="card-body p-4">
                    <a href="<?php echo e(route('runner.tasks')); ?>" class="btn btn-danger w-100 mb-2">
                        <i class="bi bi-list-task"></i> View All Tasks
                    </a>
                    <a href="<?php echo e(route('runner.tasks', ['status' => 'upcoming'])); ?>" class="btn btn-outline-danger w-100">
                        <i class="bi bi-clock"></i> Upcoming Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.runner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/runner/dashboard.blade.php ENDPATH**/ ?>