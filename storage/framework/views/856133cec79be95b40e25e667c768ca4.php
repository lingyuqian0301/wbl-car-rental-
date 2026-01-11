<?php $__env->startSection('title', 'Task List'); ?>
<?php $__env->startSection('page-title', 'Task List'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Header Box -->
    <div class="header-box">
        <h2><i class="bi bi-list-task"></i> <?php echo e($user->name ?? 'Runner'); ?>'s Task List</h2>
        <p><?php echo e($today->format('l, d F Y')); ?> - <?php echo e(\Carbon\Carbon::createFromDate($filterYear, $filterMonth, 1)->format('F Y')); ?></p>
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
                <div class="header-stat-value">RM <?php echo e(number_format($totalCommission, 2)); ?></div>
                <div class="header-stat-label">Total Commission</div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="<?php echo e(route('runner.tasks')); ?>" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" <?php echo e($filterStatus === 'all' ? 'selected' : ''); ?>>All</option>
                    <option value="upcoming" <?php echo e($filterStatus === 'upcoming' ? 'selected' : ''); ?>>Upcoming</option>
                    <option value="done" <?php echo e($filterStatus === 'done' ? 'selected' : ''); ?>>Done</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Month</label>
                <select name="month" class="form-select form-select-sm">
                    <?php for($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo e($i); ?>" <?php echo e($filterMonth == $i ? 'selected' : ''); ?>>
                            <?php echo e(\Carbon\Carbon::createFromDate(null, $i, 1)->format('F')); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Year</label>
                <select name="year" class="form-select form-select-sm">
                    <?php for($y = date('Y') - 1; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($filterYear == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="<?php echo e(route('runner.tasks')); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Task List Table -->
    <div class="runner-card">
        <div class="card-header-green d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-check"></i> Task List</span>
            <span class="badge bg-light text-dark"><?php echo e($totalTasks); ?> tasks</span>
        </div>
        <div class="table-responsive">
            <table class="runner-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Booking ID</th>
                        <th>Type</th>
                        <th>Delivery Date</th>
                        <th>Task Date</th>
                        <th>Location</th>
                        <th>Vehicle</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-end">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($task['num']); ?></td>
                            <td>
                                <strong>#<?php echo e($task['booking_id']); ?></strong>
                            </td>
                            <td>
                                <span class="badge <?php echo e($task['task_type'] === 'Pickup' ? 'badge-pickup' : 'badge-return'); ?>">
                                    <?php echo e($task['task_type']); ?>

                                </span>
                            </td>
                            <td>
                                <div><?php echo e($task['delivery_date']->format('d M Y')); ?></div>
                                <small class="text-muted"><?php echo e($task['delivery_date']->format('l')); ?></small>
                            </td>
                            <td>
                                <div><?php echo e($task['task_date']->format('d M Y')); ?></div>
                                <small class="text-muted"><?php echo e($task['task_date']->format('H:i')); ?></small>
                            </td>
                            <td><?php echo e($task['location']); ?></td>
                            <td><?php echo e($task['plate_number']); ?></td>
                            <td><?php echo e($task['customer_name']); ?></td>
                            <td>
                                <?php if($task['is_done']): ?>
                                    <span class="badge badge-done">Done</span>
                                <?php else: ?>
                                    <span class="badge badge-upcoming">Upcoming</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-semibold">RM <?php echo e(number_format($task['commission'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-3">No tasks found for this period</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if($tasks->count() > 0): ?>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="9" class="text-end fw-semibold">
                                Total Tasks: <?php echo e($totalTasks); ?>

                            </td>
                            <td class="text-end fw-bold text-danger">
                                RM <?php echo e(number_format($totalCommission, 2)); ?>

                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.runner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/runner/tasks/index.blade.php ENDPATH**/ ?>