

<?php $__env->startSection('title', 'Staff IT Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        :root {
            --hasta-red: #b91c1c;
            --hasta-red-dark: #7f1d1d;
            --hasta-rose: #fee2e2;
            --hasta-amber: #f59e0b;
            --hasta-slate: #111827;
        }
        .page-header {
            background: white;
            color: var(--hasta-slate);
            border-radius: 20px;
            padding: 24px 28px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .metric-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.05);
            height: 100%;
        }
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--hasta-rose);
            color: var(--hasta-red);
            font-size: 1.35rem;
        }
        .card-header-red {
            background: var(--hasta-red);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }
        .progress-bar-red {
            background: linear-gradient(90deg, var(--hasta-red) 0%, var(--hasta-red-dark) 100%);
        }
        .table thead th {
            background: var(--hasta-rose);
            color: var(--hasta-slate);
            border-bottom: 2px solid #fca5a5;
        }
        .badge-soft {
            background: var(--hasta-rose);
            color: var(--hasta-red);
            border: 1px solid #fecdd3;
        }
        .pill-btn {
            border-radius: 999px;
            padding-inline: 16px;
        }
        .text-muted-small {
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>

    <div class="container-fluid py-2">
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h1 class="h3 mb-1 fw-bold">Hasta Staff IT Dashboard</h1>
                        </div>
                    </div>
                    <p class="mb-0 mt-3 fw-semibold">Snapshot for <?php echo e($today->format('d M Y')); ?></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('admin.payments.index')); ?>" class="btn btn-light text-danger pill-btn" style="border: 1px solid var(--hasta-red);">
                        <i class="bi bi-credit-card me-1"></i> Verify Payments
                    </a>
                    <a href="<?php echo e(route('admin.bookings.reservations')); ?>" class="btn btn-outline-danger text-danger pill-btn" style="border: 1px solid var(--hasta-red);">
                        <i class="bi bi-calendar-check me-1"></i> Verify Booking
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?php echo e(route('admin.bookings.reservations', ['filter_pickup_date' => $today->format('Y-m-d')])); ?>" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-car-front"></i></div>
                            <div>
                                <p class="text-muted mb-1">Active Bookings</p>
                                <h3 class="fw-bold mb-0"><?php echo e($metrics['newBookingsThisWeek']); ?></h3>
                                <small class="text-muted-small">New bookings this week</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?php echo e(route('admin.payments.index', ['payment_isVerify' => '0'])); ?>" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-credit-card-2-front"></i></div>
                            <div>
                                <p class="text-muted mb-1">Payments Queue</p>
                                <h3 class="fw-bold mb-0"><?php echo e($metrics['unverifiedPayments']); ?></h3>
                                <small class="text-muted-small">Unverified payments</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <a href="<?php echo e(route('admin.vehicles.others', ['tab' => 'vehicle', 'filter_date' => $today->format('Y-m-d')])); ?>" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-truck"></i></div>
                            <div>
                                <p class="text-muted mb-1">Fleet Status</p>
                                <h3 class="fw-bold mb-0"><?php echo e($metrics['currentDayAvailableFleet']); ?></h3>
                                <small class="text-muted-small">Today's available fleet</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card metric-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="metric-icon"><i class="bi bi-people"></i></div>
                        <div>
                            <p class="text-muted mb-1">Total Customers</p>
                            <h3 class="fw-bold mb-0"><?php echo e(\App\Models\Customer::count()); ?></h3>
                            <small class="text-muted-small">Registered customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Pickup and Return Bookings -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <a href="<?php echo e(route('admin.bookings.reservations', ['filter_pickup_date' => $today->format('Y-m-d')])); ?>" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-calendar-check"></i></div>
                            <div>
                                <p class="text-muted mb-1">Today's Pickup</p>
                                <h3 class="fw-bold mb-0"><?php echo e($metrics['todayPickupBookings']); ?></h3>
                                <small class="text-muted-small">Bookings to pickup today</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <a href="<?php echo e(route('admin.bookings.reservations', ['filter_return_date' => $today->format('Y-m-d')])); ?>" class="text-decoration-none text-dark">
                    <div class="card metric-card" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body d-flex gap-3 align-items-center">
                            <div class="metric-icon"><i class="bi bi-calendar-x"></i></div>
                            <div>
                                <p class="text-muted mb-1">Today's Return</p>
                                <h3 class="fw-bold mb-0"><?php echo e($metrics['todayReturnBookings']); ?></h3>
                                <small class="text-muted-small">Bookings to return today</small>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Fleet load only (no revenue/profit trend for StaffIT) -->
        <div class="row g-3 mb-4">
            <div class="col-lg-12">
                <a href="<?php echo e(route('admin.bookings.reservations', ['filter_pickup_date_from' => $startOfWeek->format('Y-m-d'), 'filter_pickup_date_to' => $endOfWeek->format('Y-m-d'), 'sort' => 'status_priority'])); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Fleet Booking</span>
                            <i class="bi bi-calendar-week"></i>
                        </div>
                    <div class="card-body">
                        <?php
                            $weeklyStats = $weeklyBookingStats ?? ['done' => 0, 'current' => 0, 'upcoming' => 0, 'total' => 0];
                            $total = max($weeklyStats['total'], 1);
                            $donePercent = ($weeklyStats['done'] / $total) * 100;
                            $currentPercent = ($weeklyStats['current'] / $total) * 100;
                            $upcomingPercent = ($weeklyStats['upcoming'] / $total) * 100;
                        ?>
                        <h4 class="fw-bold mb-1"><?php echo e($weeklyStats['total']); ?> bookings this week</h4>
                        <p class="text-muted mb-3">Weekly booking schedule (Mon - Sun)</p>
                        <div class="progress mb-3" style="height: 20px; border-radius: 8px; overflow: hidden;">
                            <?php if($weeklyStats['done'] > 0): ?>
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: <?php echo e($donePercent); ?>%;" title="Done: <?php echo e($weeklyStats['done']); ?> bookings"></div>
                            <?php endif; ?>
                            <?php if($weeklyStats['current'] > 0): ?>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo e($currentPercent); ?>%;" title="Current: <?php echo e($weeklyStats['current']); ?> bookings"></div>
                            <?php endif; ?>
                            <?php if($weeklyStats['upcoming'] > 0): ?>
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($upcomingPercent); ?>%;" title="Upcoming: <?php echo e($weeklyStats['upcoming']); ?> bookings"></div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-column gap-2 text-muted-small">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-secondary me-2">Done</span> <?php echo e($weeklyStats['done']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-danger me-2">Current</span> <?php echo e($weeklyStats['current']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><span class="badge bg-success me-2">Upcoming</span> <?php echo e($weeklyStats['upcoming']); ?></span>
                            </div>
                        </div>
                    </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Pending payments -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Payments awaiting verification</span>
                <a href="<?php echo e(route('admin.payments.index', ['payment_isVerify' => '0'])); ?>" class="btn btn-light btn-sm pill-btn">
                    Review all
                </a>
            </div>
            <div class="card-body">
                <?php if($pendingPayments->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Booking</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $pendingPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>#<?php echo e($payment->paymentID); ?></td>
                                        <td>#<?php echo e($payment->bookingID); ?></td>
                                        <td><?php echo e($payment->booking->customer->user->name ?? 'Unknown'); ?></td>
                                        <td><?php echo e($payment->booking->vehicle->plate_number ?? 'N/A'); ?></td>
                                        <td class="fw-semibold text-danger">RM <?php echo e(number_format($payment->total_amount ?? $payment->amount, 2)); ?></td>
                                        <td><?php echo e($payment->payment_date?->format('d M Y')); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('admin.payments.index', ['search' => $payment->bookingID, 'payment_isVerify' => '0'])); ?>" class="btn btn-outline-danger btn-sm pill-btn">
                                                Verify
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border-0 text-muted mb-0">
                        All caught up — no pending payments right now.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <a href="<?php echo e(route('admin.bookings.reservations', ['sort' => 'created_desc'])); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Recent Bookings</span>
                            <i class="bi bi-calendar2-week"></i>
                        </div>
                        <div class="card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $recentBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <a href="<?php echo e(route('admin.bookings.reservations.show', ['booking' => $booking->bookingID])); ?>" class="text-decoration-none text-dark">
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom hover-bg">
                                        <div>
                                            <p class="mb-0 fw-semibold">#<?php echo e($booking->bookingID); ?> · <?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></p>
                                            <small class="text-muted"><?php echo e($booking->customer->user->name ?? 'Unknown'); ?> · <?php echo e($booking->rental_start_date?->format('d M') ?? 'N/A'); ?> - <?php echo e($booking->rental_end_date?->format('d M Y') ?? 'N/A'); ?></small>
                                        </div>
                                        <span class="badge <?php echo e($booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info'))); ?>">
                                            <?php echo e($booking->booking_status); ?>

                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="mb-0 text-muted">No bookings yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="<?php echo e(route('admin.bookings.cancellation', ['refund_status' => 'false'])); ?>" class="text-decoration-none text-dark">
                    <div class="card h-100" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Cancellation Request</span>
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="card-body">
                            <?php $__empty_1 = true; $__currentLoopData = $cancellationRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <p class="mb-0 fw-semibold">#<?php echo e($booking->bookingID); ?> · <?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></p>
                                        <small class="text-muted"><?php echo e($booking->customer->user->name ?? 'Unknown'); ?> · <?php echo e($booking->rental_start_date?->format('d M Y') ?? 'N/A'); ?></small>
                                    </div>
                                    <span class="badge <?php echo e($booking->booking_status === 'Cancelled' || $booking->booking_status === 'cancelled' ? 'bg-danger' : ($booking->booking_status === 'refunding' ? 'bg-warning text-dark' : 'bg-info')); ?>">
                                        <?php echo e($booking->booking_status); ?>

                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="mb-0 text-muted">No cancellation requests.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Booking need runner -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Booking need runner</span>
                <a href="<?php echo e(route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc'])); ?>" class="btn btn-light btn-sm pill-btn">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if($bookingsNeedRunner->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Pickup Date</th>
                                    <th>Pickup Time</th>
                                    <th>Plate No</th>
                                    <th>Assigned Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $bookingsNeedRunner; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr style="cursor: pointer;" onclick="window.location.href='<?php echo e(route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc'])); ?>'">
                                        <td><?php echo e($booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A'); ?></td>
                                        <td><?php echo e($booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('H:i') : 'N/A'); ?></td>
                                        <td><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo e(($booking->assigned_status ?? 'unassigned') === 'assigned' ? 'bg-success' : 'bg-warning text-dark'); ?>">
                                                <?php echo e(ucfirst($booking->assigned_status ?? 'unassigned')); ?>

                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('admin.runner.tasks', ['filter_assigned' => 'unassigned', 'sort' => 'pickup_asc'])); ?>" class="btn btn-outline-danger btn-sm pill-btn" onclick="event.stopPropagation()">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border-0 text-muted mb-0">
                        No bookings need runner at the moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Bookings to Serve -->
        <div class="card mb-4">
            <div class="card-header card-header-red d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Upcoming Bookings to Serve (Next 3 Days)</span>
                <a href="<?php echo e(route('admin.bookings.reservations', ['filter_pickup_date_from' => $today->format('Y-m-d'), 'filter_pickup_date_to' => $today->copy()->addDays(3)->format('Y-m-d'), 'sort' => 'pickup_asc'])); ?>" class="btn btn-light btn-sm pill-btn">
                    View All
                </a>
            </div>
            <div class="card-body">
                <?php if($upcomingBookingsToServe->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Plate No</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $upcomingBookingsToServe; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>#<?php echo e($booking->bookingID); ?></td>
                                        <td><?php echo e($booking->customer->user->name ?? 'Unknown'); ?></td>
                                        <td><?php echo e($booking->vehicle->plate_number ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->rental_start_date?->format('d M Y') ?? 'N/A'); ?></td>
                                        <td><?php echo e($booking->rental_end_date?->format('d M Y') ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo e(($booking->payment_status_display ?? 'Deposit') === 'Full' ? 'bg-success' : 'bg-warning text-dark'); ?>">
                                                <?php echo e($booking->payment_status_display ?? 'Deposit'); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-soft">
                                                <?php echo e($booking->booking_status ?? 'N/A'); ?>

                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('admin.bookings.reservations.show', ['booking' => $booking->bookingID])); ?>" class="btn btn-outline-danger btn-sm pill-btn">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border-0 text-muted mb-0">
                        No upcoming bookings in the next 3 days.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center py-3 text-muted-small">
            <span>Hasta Travel Vehicle Rental System · Staff IT dashboard</span>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/staffit/dashboard.blade.php ENDPATH**/ ?>