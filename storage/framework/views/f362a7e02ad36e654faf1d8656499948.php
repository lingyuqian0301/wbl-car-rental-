<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HASTA Travel - Car Rental System</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
    html {
        font-size: 12px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        line-height: 1.6;
        color: #333;
    }

    :root {
        --primary-orange: #dc2626;
        --primary-dark-orange: #991b1b;
        --success-green: #059669;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --bg-light: #f8fafc;
        --error-red: #dc2626;
    }

    /* Hero Section */
    .hero {
        background: linear-gradient(to right, var(--primary-orange), var(--primary-dark-orange));
        color: white;
        padding-bottom: 3rem;
    }

    .hero-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
    }

    .hero h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .hero p {
        font-size: 1.125rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero-btn {
        display: inline-block;
        padding: 0.75rem 2rem;
        background-color: #ffffff;
        color: var(--primary-orange);
        font-weight: 700;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: background-color 0.3s;
    }

    .hero-btn:hover {
        background-color: #f3f4f6;
    }

    /* Section Styles */
    section {
        padding: 2rem 2rem 0.5rem 2rem;
    }

    section h3 {
        font-size: 1.875rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    /* Filter Capsule */
    .filter-capsule-wrapper {
        max-width: 1200px;
        margin: 0 auto 3rem auto;
        padding: 0 2rem;
        position: relative;
        z-index: 999;
        transition: box-shadow 0.3s ease;
    }

    .filter-capsule-form {
        background: #ffffff;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 999px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        flex-wrap: wrap;
    }

    .capsule-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 160px;
    }

    .capsule-field label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        padding-left: 0.25rem;
    }

    .capsule-field input,
    .capsule-field select {
        border: none;
        background: #f9fafb;
        padding: 0.65rem 0.9rem;
        border-radius: 999px;
        font-size: 0.9rem;
        min-height: 42px;
    }

    .capsule-field input:focus,
    .capsule-field select:focus {
        outline: none;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
    }

    .capsule-btn {
        background: linear-gradient(135deg, var(--primary-orange), var(--primary-dark-orange));
        color: white;
        border: none;
        padding: 0 2.25rem;
        height: 42px;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.25s ease;
    }

    .capsule-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
    }

    .capsule-actions {
        display: flex;
        gap: 0.75rem;
    }

    .capsule-clear {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        padding: 0 1.5rem;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .capsule-clear:hover {
        background: #e5e7eb;
    }

    /* Cars Grid - List Style */
    .cars-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .car-card {
        width: 100%;
        display: flex;
        flex-direction: row;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .car-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }

    .car-image {
        height: 100px;
        width: 140px;
        min-width: 140px;
        background-color: #f3f4f6;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .car-card.green .car-image {
        background-color: #d1fae5;
    }

    .car-card.yellow .car-image {
        background-color: #fef3c7;
    }

    .car-image img {
        width: 100%;
        height: 100%;
        transform: scale(1.1);
        object-fit: contain;
        object-position: center;
    }

    .car-content {
        padding: 1rem 1.5rem;
        display: flex;
        flex-direction: row;
        flex: 1;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .car-info-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .car-info-right {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .car-content h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #1f2937;
        line-height: 1.3;
    }

    .car-type {
        color: #6b7280;
        margin-bottom: 0.25rem;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .car-id {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .car-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .car-details-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .car-datetime {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 180px;
    }

    .datetime-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .datetime-icon {
        font-size: 1rem;
        color: #9ca3af;
    }

    .spec-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .spec-badge.transmission {
        background-color: #eef2ff;
        color: #3730a3;
        border-color: #c7d2fe;
    }

    .spec-badge.seat {
        background-color: #ecfeff;
        color: #155e75;
        border-color: #a5f3fc;
    }

    .spec-badge.color {
        background-color: #f8fafc;
    }

    .spec-badge .dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1.5px solid #d1d5db;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
    }

    .car-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-orange);
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 120px;
    }

    .car-price span {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 400;
    }

    .payment-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #d1fae5;
        color: #065f46;
    }

    .payment-status.unpaid {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .car-btn {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        background-color: transparent;
        color: #3b82f6;
        text-align: center;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: background-color 0.3s;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .car-btn:hover {
        background-color: #eff6ff;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .filter-capsule-form {
            border-radius: 20px;
        }

        .capsule-field {
            flex: 1 1 100%;
        }

        .capsule-btn {
            width: 100%;
        }

        .car-card {
            flex-direction: column;
        }

        .car-image {
            width: 100%;
            height: 150px;
        }

        .car-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .car-info-right {
            width: 100%;
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .car-price {
            text-align: left;
        }
    }
    </style>
</head>

<body>
    <?php echo $__env->make('components.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="hero">
        <div class="hero-container"><!-- <a href="<?php echo e(route('home')); ?>" class="hero-btn">View all cars</a> -->
            <h2>Your Loyalty, Rewarded</h2>
            <p>For every 5 bookings you complete, receive a voucher toward your next rental.</p>
            <a href="<?php echo e(auth()->check() ? route('loyalty.show') : route('login')); ?>" class="hero-btn">Loyalty Rewards</a>

        </div>
    </section>

    <section>
        <div class="filter-capsule-wrapper">
            <form method="GET" action="<?php echo e(route('home')); ?>#carsGrid" class="filter-capsule-form" id="filterForm">

                <div class="capsule-field">
                    <label>Pick-up Date</label>
                    <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" autocomplete="off">
                </div>

                <div class="capsule-field">
                    <label>Return Date</label>
                    <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" autocomplete="off">
                </div>

                <div class="capsule-field">
                    <label>Vehicle</label>
                    <select name="vehicleType">
                        <option value="">All Vehicles</option>
                        <?php $__currentLoopData = $vehicleTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type); ?>" <?php echo e(request('vehicleType') == $type ? 'selected' : ''); ?>>
                            <?php echo e($type); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="capsule-field">
                    <label>Brand</label>
                    <select name="brand">
                        <option value="">All Brands</option>
                        <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($brand); ?>" <?php echo e(request('brand') == $brand ? 'selected' : ''); ?>>
                            <?php echo e($brand); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <a href="<?php echo e(route('home')); ?>#carsGrid" class="capsule-clear"
                    onclick="sessionStorage.removeItem('filterScrollY')">
                    Clear
                </a>

                <button type="submit" class="capsule-btn">
                    Filter
                </button>

            </form>
        </div>

        <div id="carsGrid">
            <div class="cars-grid">
                <?php $__empty_1 = true; $__currentLoopData = $cars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="car-card">
                    <?php
                    $imageName = strtolower($car->vehicle_brand . '-' . $car->vehicle_model);
                    $imageName = preg_replace('/[^a-z0-9]+/i', '-', $imageName);
                    $imageName = trim($imageName, '-');
                    $imageName .= '.png';
                    $imagePath = public_path('images/cars/browse/' . $imageName);
                    ?>

                    <div class="car-image">
                        <?php if(file_exists($imagePath)): ?>
                        <img src="<?php echo e(asset('images/cars/browse/' . $imageName)); ?>"
                            alt="<?php echo e($car->vehicle_brand); ?> <?php echo e($car->vehicle_model); ?>">
                        <?php else: ?>
                        <img src="<?php echo e(asset('images/cars/browse/default.png')); ?>" alt="Car">
                        <?php endif; ?>
                    </div>

                    <div class="car-content">
                        <div class="car-info-left">
                            <div>
                                <h4><?php echo e($car->vehicle_brand); ?> <?php echo e($car->vehicle_model); ?></h4>
                                <p class="car-type"><?php echo e($car->vehicleType); ?></p>
                            </div>

                            <div class="car-specs">
                                <?php if($car->car): ?>
                                <span class="spec-badge transmission">
                                    <?php echo e($car->car->transmission); ?>

                                </span>
                                <span class="spec-badge seat">
                                    <?php echo e($car->car->seating_capacity); ?> seats
                                </span>
                                <?php endif; ?>

                                <span class="spec-badge color">
                                    <span class="dot" style="background-color: <?php echo e($car->color ?? '#ccc'); ?>"></span>
                                    <?php echo e($car->color ?? 'N/A'); ?>

                                </span>
                            </div>
                        </div>

                        <div class="car-info-right">
                            <?php if(request('start_date') && request('end_date')): ?>
                            <div class="car-datetime">
                                <div class="datetime-item">
                                    <span class="datetime-icon">📅</span>
                                    <div>
                                        <div style="font-weight: 600; color: #374151;">Pickup</div>
                                        <div><?php echo e(\Carbon\Carbon::parse(request('start_date'))->format('d M Y')); ?></div>
                                    </div>
                                </div>
                                <div class="datetime-item">
                                    <span class="datetime-icon">📅</span>
                                    <div>
                                        <div style="font-weight: 600; color: #374151;">Return</div>
                                        <div><?php echo e(\Carbon\Carbon::parse(request('end_date'))->format('d M Y')); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="car-price">
                                <span class="payment-status">Available</span>
                                <div>
                                    MYR <?php echo e(number_format($car->rental_price, 2)); ?>


                                </div>
                            </div>

                            <a href="<?php echo e(route('vehicles.show', [
                                        'id' => $car->vehicleID, 
                                        'start_date' => request('start_date'), 
                                        'end_date' => request('end_date')
                                    ])); ?>" class="car-btn">
                                View
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="text-align:center; padding: 2rem;">No cars available.</p>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('input[name="start_date"]', {
            minDate: 'today',
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        flatpickr('input[name="end_date"]', {
            minDate: 'today',
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });
    </script>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>

</html><?php /**PATH C:\xampp\htdocs\wbl-car-rental-\resources\views/welcome.blade.php ENDPATH**/ ?>