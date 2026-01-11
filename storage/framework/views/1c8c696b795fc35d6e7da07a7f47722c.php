<?php $__env->startSection('title', 'Charts & Analytics'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 25px;
    }
    .chart-container {
        position: relative;
        height: 400px;
        margin-top: 20px;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .nav-tabs .nav-link {
        color: var(--admin-red);
        border: none;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link.active {
        color: var(--admin-red-dark);
        background: transparent;
        border-bottom: 2px solid var(--admin-red);
        font-weight: 600;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .print-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: bold;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="mb-1"><i class="bi bi-bar-chart"></i> Charts & Analytics</h2>
            <p class="text-muted mb-0">Visualize rental data and trends</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-danger btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="<?php echo e(route('admin.reports.charts.export-pdf', array_merge(request()->all(), ['tab' => $activeTab]))); ?>" class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="print-title" style="display: none;">
        <h2>Charts & Analytics Report</h2>
        <p>Chart Type: <?php echo e(ucfirst($activeTab)); ?></p>
    </div>

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3 no-print" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo e($activeTab === 'weekly' ? 'active' : ''); ?>" 
               href="<?php echo e(route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'weekly']))); ?>">
                <i class="bi bi-calendar-week"></i> Weekly Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo e($activeTab === 'monthly' ? 'active' : ''); ?>" 
               href="<?php echo e(route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'monthly']))); ?>">
                <i class="bi bi-calendar-month"></i> Monthly Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo e($activeTab === 'faculty' ? 'active' : ''); ?>" 
               href="<?php echo e(route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'faculty']))); ?>">
                <i class="bi bi-pie-chart"></i> Faculty Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo e($activeTab === 'brand' ? 'active' : ''); ?>" 
               href="<?php echo e(route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'brand']))); ?>">
                <i class="bi bi-pie-chart-fill"></i> Car Brand Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?php echo e($activeTab === 'comparison' ? 'active' : ''); ?>" 
               href="<?php echo e(route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'comparison']))); ?>">
                <i class="bi bi-bar-chart-line"></i> Comparison
            </a>
        </li>
    </ul>

    <!-- Weekly Rental Chart -->
    <?php if($activeTab === 'weekly'): ?>
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart"></i> Weekly Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="weekly">
                <input type="week" name="selected_week" class="form-control form-control-sm" value="<?php echo e($selectedWeek); ?>" onchange="this.form.submit()">
            </form>
        </div>
        <p class="text-muted small mb-2">Fixed axis: X = 7 days (Mon-Sun), Y = 0-20 bookings</p>
        <div class="chart-container">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monthly Rental Chart -->
    <?php if($activeTab === 'monthly'): ?>
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart"></i> Monthly Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="monthly">
                <input type="month" name="selected_month" class="form-control form-control-sm" value="<?php echo e($selectedMonth); ?>" onchange="this.form.submit()">
            </form>
        </div>
        <p class="text-muted small mb-2">Fixed axis: X = 31 days, Y = 0-50 bookings</p>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Faculty Rental Bar Chart -->
    <?php if($activeTab === 'faculty'): ?>
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart-steps"></i> Faculty Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="faculty">
                <input type="month" name="faculty_month" class="form-control form-control-sm" value="<?php echo e($facultyMonth); ?>">
                <input type="number" name="faculty_year" class="form-control form-control-sm" value="<?php echo e($facultyYear); ?>" min="2020" max="2100">
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <p class="text-muted small mb-2">Fixed axis: X = 0-30 bookings, Y = Faculties</p>
        <div class="chart-container">
            <canvas id="facultyChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Car Brand Rental Bar Chart -->
    <?php if($activeTab === 'brand'): ?>
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart-steps"></i> Car Brand Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="brand">
                <input type="month" name="brand_month" class="form-control form-control-sm" value="<?php echo e($brandMonth); ?>">
                <select name="brand_vehicle_type" class="form-select form-select-sm">
                    <option value="all" <?php echo e($brandVehicleType === 'all' ? 'selected' : ''); ?>>All</option>
                    <option value="car" <?php echo e($brandVehicleType === 'car' ? 'selected' : ''); ?>>Car</option>
                    <option value="motorcycle" <?php echo e($brandVehicleType === 'motorcycle' ? 'selected' : ''); ?>>Motorcycle</option>
                </select>
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <p class="text-muted small mb-2">Fixed axis: X = 0-30 bookings, Y = Vehicle Brands</p>
        <div class="chart-container">
            <canvas id="brandChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comparison Bar Chart -->
    <?php if($activeTab === 'comparison'): ?>
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i class="bi bi-bar-chart-line"></i> Comparison Bar Chart (Latest 4 Months)</h5>
        </div>
        <p class="text-muted small mb-2">Fixed axis: X = 4 months, Y = 0-100 bookings</p>
        <div class="chart-container">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Weekly Chart - Bar Chart with Fixed X (7 days) and Y (20 max)
    <?php if($activeTab === 'weekly'): ?>
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyRawLabels = <?php echo json_encode(array_column($weeklyData, 'date')); ?>;
    const weeklyRawCounts = <?php echo json_encode(array_column($weeklyData, 'count')); ?>;
    
    // Fixed 7 days for the week (Mon-Sun)
    const fixedWeekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    
    // Map data to fixed days or use zeros if no data
    let weeklyMappedData = [0, 0, 0, 0, 0, 0, 0];
    if (weeklyRawLabels.length > 0) {
        weeklyRawLabels.forEach((label, index) => {
            // Try to match day abbreviation
            const dayIndex = fixedWeekDays.findIndex(d => label.includes(d) || label.toLowerCase().includes(d.toLowerCase()));
            if (dayIndex !== -1) {
                weeklyMappedData[dayIndex] = weeklyRawCounts[index] || 0;
            } else if (index < 7) {
                weeklyMappedData[index] = weeklyRawCounts[index] || 0;
            }
        });
    }
    
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: fixedWeekDays,
            datasets: [{
                label: 'Number of Bookings',
                data: weeklyMappedData,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: '#dc3545',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Bookings: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Day of Week'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: 20,
                    ticks: {
                        stepSize: 2,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Monthly Chart - Bar Chart with Fixed X (31 days) and Y (50 max)
    <?php if($activeTab === 'monthly'): ?>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyRawLabels = <?php echo json_encode(array_column($monthlyData, 'date')); ?>;
    const monthlyRawCounts = <?php echo json_encode(array_column($monthlyData, 'count')); ?>;
    
    // Fixed 31 days for the month
    const fixedMonthDays = Array.from({length: 31}, (_, i) => (i + 1).toString());
    
    // Map data to fixed days or use zeros if no data
    let monthlyMappedData = Array(31).fill(0);
    if (monthlyRawLabels.length > 0) {
        monthlyRawLabels.forEach((label, index) => {
            const dayNum = parseInt(label);
            if (dayNum >= 1 && dayNum <= 31) {
                monthlyMappedData[dayNum - 1] = monthlyRawCounts[index] || 0;
            } else if (index < 31) {
                monthlyMappedData[index] = monthlyRawCounts[index] || 0;
            }
        });
    }
    
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: fixedMonthDays,
            datasets: [{
                label: 'Number of Bookings',
                data: monthlyMappedData,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: '#dc3545',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Day ' + context.label + ': ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Day of Month'
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 15
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: 50,
                    ticks: {
                        stepSize: 5,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Faculty Chart - Bar Chart with Fixed Y (30 max)
    <?php if($activeTab === 'faculty'): ?>
    const facultyCtx = document.getElementById('facultyChart').getContext('2d');
    const facultyRawLabels = <?php echo json_encode(array_keys($facultyData)); ?>;
    const facultyRawValues = <?php echo json_encode(array_values($facultyData)); ?>;
    
    // Default faculties if no data
    const defaultFaculties = ['FKE', 'FKMP', 'FPTT', 'FPTV', 'FTK', 'FSKTM', 'Other'];
    
    // Use actual data if available, otherwise use defaults with zeros
    let facultyLabels = facultyRawLabels.length > 0 ? facultyRawLabels : defaultFaculties;
    let facultyValues = facultyRawLabels.length > 0 ? facultyRawValues : Array(defaultFaculties.length).fill(0);
    
    // Ensure we always have at least the default faculties shown
    if (facultyLabels.length < 3) {
        facultyLabels = defaultFaculties;
        facultyValues = Array(defaultFaculties.length).fill(0);
        // Map existing data to default faculties
        facultyRawLabels.forEach((label, idx) => {
            const matchIdx = defaultFaculties.findIndex(f => f.toLowerCase() === label.toLowerCase() || label.includes(f));
            if (matchIdx !== -1) {
                facultyValues[matchIdx] = facultyRawValues[idx];
            }
        });
    }
    
    new Chart(facultyCtx, {
        type: 'bar',
        data: {
            labels: facultyLabels,
            datasets: [{
                label: 'Number of Bookings',
                data: facultyValues,
                backgroundColor: [
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(253, 126, 20, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(32, 201, 151, 0.7)',
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(111, 66, 193, 0.7)',
                    'rgba(232, 62, 140, 0.7)'
                ],
                borderColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1', '#e83e8c'
                ],
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' bookings';
                        }
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    min: 0,
                    max: 30,
                    ticks: {
                        stepSize: 5,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Faculty'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Brand Chart - Bar Chart with Fixed Y (30 max)
    <?php if($activeTab === 'brand'): ?>
    const brandCtx = document.getElementById('brandChart').getContext('2d');
    const brandRawLabels = <?php echo json_encode(array_keys($brandData)); ?>;
    const brandRawValues = <?php echo json_encode(array_values($brandData)); ?>;
    
    // Default brands if no data
    const defaultBrands = ['Perodua', 'Proton', 'Honda', 'Toyota', 'Yamaha', 'Modenas', 'Other'];
    
    // Use actual data if available, otherwise use defaults with zeros
    let brandLabels = brandRawLabels.length > 0 ? brandRawLabels : defaultBrands;
    let brandValues = brandRawLabels.length > 0 ? brandRawValues : Array(defaultBrands.length).fill(0);
    
    // Ensure we always have at least some brands shown
    if (brandLabels.length < 3) {
        brandLabels = defaultBrands;
        brandValues = Array(defaultBrands.length).fill(0);
        // Map existing data to default brands
        brandRawLabels.forEach((label, idx) => {
            const matchIdx = defaultBrands.findIndex(b => b.toLowerCase() === label.toLowerCase());
            if (matchIdx !== -1) {
                brandValues[matchIdx] = brandRawValues[idx];
            } else {
                // Add to Other category
                brandValues[defaultBrands.length - 1] += brandRawValues[idx];
            }
        });
    }
    
    new Chart(brandCtx, {
        type: 'bar',
        data: {
            labels: brandLabels,
            datasets: [{
                label: 'Number of Bookings',
                data: brandValues,
                backgroundColor: [
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(253, 126, 20, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(32, 201, 151, 0.7)',
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(111, 66, 193, 0.7)',
                    'rgba(232, 62, 140, 0.7)',
                    'rgba(25, 135, 84, 0.7)'
                ],
                borderColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1', '#e83e8c', '#198754'
                ],
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' bookings';
                        }
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    min: 0,
                    max: 30,
                    ticks: {
                        stepSize: 5,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Vehicle Brand'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Comparison Chart - Bar Chart with Fixed X (4 months) and Y (100 max)
    <?php if($activeTab === 'comparison'): ?>
    const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
    const comparisonRawLabels = <?php echo json_encode(array_column($comparisonData, 'month')); ?>;
    const comparisonRawTotal = <?php echo json_encode(array_column($comparisonData, 'total')); ?>;
    const comparisonRawCars = <?php echo json_encode(array_column($comparisonData, 'cars')); ?>;
    const comparisonRawMotorcycles = <?php echo json_encode(array_column($comparisonData, 'motorcycles')); ?>;
    
    // Generate default labels for last 4 months if no data
    function getLastFourMonths() {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const now = new Date();
        let result = [];
        for (let i = 3; i >= 0; i--) {
            const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            result.push(months[d.getMonth()] + ' ' + d.getFullYear());
        }
        return result;
    }
    
    // Use actual data if available, otherwise use defaults
    const comparisonLabels = comparisonRawLabels.length > 0 ? comparisonRawLabels : getLastFourMonths();
    const comparisonTotal = comparisonRawTotal.length > 0 ? comparisonRawTotal : [0, 0, 0, 0];
    const comparisonCars = comparisonRawCars.length > 0 ? comparisonRawCars : [0, 0, 0, 0];
    const comparisonMotorcycles = comparisonRawMotorcycles.length > 0 ? comparisonRawMotorcycles : [0, 0, 0, 0];
    
    // Ensure we always have 4 data points
    while (comparisonLabels.length < 4) comparisonLabels.push('Month ' + (comparisonLabels.length + 1));
    while (comparisonTotal.length < 4) comparisonTotal.push(0);
    while (comparisonCars.length < 4) comparisonCars.push(0);
    while (comparisonMotorcycles.length < 4) comparisonMotorcycles.push(0);
    
    new Chart(comparisonCtx, {
        type: 'bar',
        data: {
            labels: comparisonLabels,
            datasets: [
                {
                    label: 'Total',
                    data: comparisonTotal,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Cars',
                    data: comparisonCars,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: 'Motorcycles',
                    data: comparisonMotorcycles,
                    backgroundColor: 'rgba(25, 135, 84, 0.8)',
                    borderColor: '#198754',
                    borderWidth: 1,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 10,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    window.addEventListener('beforeprint', function() {
        document.querySelector('.print-title').style.display = 'block';
    });

    window.addEventListener('afterprint', function() {
        document.querySelector('.print-title').style.display = 'none';
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/admin/charts/index.blade.php ENDPATH**/ ?>