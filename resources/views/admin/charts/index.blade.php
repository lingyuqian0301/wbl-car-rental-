@extends('layouts.admin')

@section('title', 'Charts & Analytics')

@push('styles')
<style>
    .chart-card {
        background: white;
        border-radius: var(--radius-lg, 12px);
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-200, #e5e7eb);
    }
    .chart-card h5 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800, #1f2937);
        margin: 0;
    }
    .chart-card h5 i {
        color: var(--admin-red, #dc2626);
        margin-right: 0.5rem;
    }
    .chart-container {
        position: relative;
        height: 320px;
        margin-top: 1rem;
        padding: 0.5rem;
    }
    /* Horizontal bar charts need more height */
    .chart-container.horizontal {
        height: 280px;
    }
    /* Vehicle rental chart needs more space - scalable based on content */
    .chart-container.horizontal.vehicle-rental {
        min-height: 600px;
        height: auto;
    }
    
    /* Make vehicle rental chart card full width and taller */
    .chart-card.vehicle-rental-card {
        min-height: 700px;
    }
    .chart-description {
        font-size: 0.75rem;
        color: var(--gray-500, #6b7280);
        background: var(--gray-50, #f9fafb);
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm, 6px);
        margin-bottom: 0;
        display: inline-block;
    }
    .filter-card {
        background: white;
        border-radius: var(--radius-lg, 12px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    .nav-tabs {
        border-bottom: 2px solid var(--gray-200, #e5e7eb);
        gap: 0.25rem;
    }
    .nav-tabs .nav-link {
        color: var(--gray-600, #4b5563);
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    .nav-tabs .nav-link:hover {
        color: var(--admin-red, #dc2626);
        border-bottom-color: var(--admin-red-light, #fee2e2);
    }
    .nav-tabs .nav-link.active {
        color: var(--admin-red, #dc2626);
        background: transparent;
        border-bottom: 3px solid var(--admin-red, #dc2626);
        font-weight: 600;
    }
    .nav-tabs .nav-link i {
        margin-right: 0.375rem;
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
        .chart-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
    @media (max-width: 768px) {
        .chart-container {
            height: 280px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="mb-1"><i class="bi bi-bar-chart"></i> Charts & Analytics</h2>
            <p class="text-muted mb-0">Visualize rental data and trends</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.charts.export-pdf', array_merge(request()->all(), ['tab' => $activeTab])) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.charts.export-excel', array_merge(request()->all(), ['tab' => $activeTab])) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="print-title" style="display: none;">
        <h2>Charts & Analytics Report</h2>
        <p>Chart Type: {{ ucfirst($activeTab) }}</p>
    </div>

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3 no-print" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'weekly' ? 'active' : '' }}" 
               href="{{ route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'weekly'])) }}">
                <i class="bi bi-calendar-week"></i> Weekly Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'monthly' ? 'active' : '' }}" 
               href="{{ route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'monthly'])) }}">
                <i class="bi bi-calendar-month"></i> Monthly Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'faculty' ? 'active' : '' }}" 
               href="{{ route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'faculty'])) }}">
                <i class="bi bi-pie-chart"></i> Faculty Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'brand' ? 'active' : '' }}" 
               href="{{ route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'brand'])) }}">
                <i class="bi bi-pie-chart-fill"></i> Vehicle Rental
            </a>
        </li>
    </ul>

    <!-- Weekly Rental Chart -->
    @if($activeTab === 'weekly')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart"></i> Weekly Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="weekly">
                <input type="week" name="selected_week" class="form-control form-control-sm" value="{{ $selectedWeek }}" onchange="this.form.submit()">
            </form>
        </div>
        <p class="chart-description">X-axis: Days of the week (Mon-Sun) | Y-axis: Number of bookings</p>
        <div class="chart-container">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Monthly Rental Chart -->
    @if($activeTab === 'monthly')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart"></i> Monthly Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="monthly">
                <input type="month" name="selected_month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </form>
        </div>
        <p class="chart-description">X-axis: Days of the month (1-31) | Y-axis: Number of bookings</p>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Faculty Rental Bar Chart -->
    @if($activeTab === 'faculty')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart-steps"></i> Faculty Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="faculty">
                <input type="month" name="faculty_month" class="form-control form-control-sm" value="{{ $facultyMonth }}">
                <input type="number" name="faculty_year" class="form-control form-control-sm" value="{{ $facultyYear }}" min="2020" max="2100">
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <p class="chart-description">Horizontal bar chart showing number of people from each faculty who booked from the system</p>
        <div class="chart-container horizontal">
            <canvas id="facultyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Vehicle Rental Bar Chart -->
    @if($activeTab === 'brand')
    <div class="chart-card vehicle-rental-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-bar-chart-steps"></i> Vehicle Rental Bar Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="brand">
                <input type="month" name="brand_month" class="form-control form-control-sm" value="{{ $brandMonth }}">
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <p class="chart-description">Horizontal bar chart showing number of bookings per vehicle (all vehicles) in the selected month. Y-axis shows vehicle plate numbers, X-axis shows booking count.</p>
        <div class="chart-container horizontal vehicle-rental" id="vehicleRentalChartContainer">
            <canvas id="brandChart"></canvas>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    // Weekly Chart - Bar Chart with Fixed X (7 days) and Y (20 max)
    @if($activeTab === 'weekly')
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyRawLabels = {!! json_encode(array_column($weeklyData, 'date')) !!};
    const weeklyRawCounts = {!! json_encode(array_column($weeklyData, 'count')) !!};
    
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
                label: 'Number of People',
                data: weeklyMappedData,
                backgroundColor: 'rgba(220, 38, 38, 0.8)',
                borderColor: '#dc2626',
                borderWidth: 0,
                borderRadius: 6,
                barThickness: 40,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 12 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: '500' },
                        color: '#6b7280'
                    }
                },
                y: {
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: { size: 11 },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false
                    }
                }
            }
        }
    });
    @endif

    // Monthly Chart - Bar Chart with Fixed X (31 days, showing every 7 days) and Y (52 max)
    @if($activeTab === 'monthly')
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyRawLabels = {!! json_encode(array_column($monthlyData, 'date')) !!};
    const monthlyRawCounts = {!! json_encode(array_column($monthlyData, 'count')) !!};
    
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
                label: 'Number of People',
                data: monthlyMappedData,
                backgroundColor: 'rgba(220, 38, 38, 0.8)',
                borderColor: '#dc2626',
                borderWidth: 0,
                borderRadius: 3,
                barPercentage: 0.85,
                categoryPercentage: 0.9
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            return 'Day ' + context[0].label;
                        },
                        label: function(context) {
                            return ' ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: false,
                        font: { size: 10 },
                        color: '#6b7280',
                        callback: function(value, index) {
                            const day = index + 1;
                            if (day === 1 || day === 7 || day === 14 || day === 21 || day === 28 || day === 31) {
                                return day;
                            }
                            return '';
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: { size: 11 },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false
                    }
                }
            }
        }
    });
    @endif

    // Faculty Chart - Bar Chart with Fixed Y (30 max)
    @if($activeTab === 'faculty')
    const facultyCtx = document.getElementById('facultyChart').getContext('2d');
    const facultyRawLabels = {!! json_encode(array_keys($facultyData)) !!};
    const facultyRawValues = {!! json_encode(array_values($facultyData)) !!};
    
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
                label: 'Number of People',
                data: facultyValues,
                backgroundColor: [
                    'rgba(220, 38, 38, 0.85)',
                    'rgba(234, 88, 12, 0.85)',
                    'rgba(202, 138, 4, 0.85)',
                    'rgba(22, 163, 74, 0.85)',
                    'rgba(37, 99, 235, 0.85)',
                    'rgba(124, 58, 237, 0.85)',
                    'rgba(219, 39, 119, 0.85)'
                ],
                borderWidth: 0,
                borderRadius: 4,
                barThickness: 28,
                maxBarThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.x + ' bookings';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        stepSize: 5,
                        precision: 0,
                        font: { size: 11 },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false
                    }
                },
                y: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: '500' },
                        color: '#374151'
                    }
                }
            }
        }
    });
    @endif

    // Vehicle Rental Chart - Bar Chart showing bookings per vehicle (all vehicles)
    @if($activeTab === 'brand')
    const brandCtx = document.getElementById('brandChart').getContext('2d');
    const brandRawLabels = {!! json_encode(array_keys($brandData)) !!};
    const brandRawValues = {!! json_encode(array_values($brandData)) !!};
    
    // Use actual data - plate numbers only
    let brandLabels = brandRawLabels.length > 0 ? brandRawLabels : [];
    let brandValues = brandRawLabels.length > 0 ? brandRawValues : [];
    
    // Calculate dynamic chart height based on number of vehicles
    const vehicleCount = brandLabels.length;
    const minBarHeight = 35; // Minimum height per bar
    const baseHeight = 200; // Base height for chart
    const calculatedHeight = Math.max(600, baseHeight + (vehicleCount * minBarHeight));
    
    // Update container height dynamically
    const chartContainer = document.getElementById('vehicleRentalChartContainer');
    if (chartContainer) {
        chartContainer.style.height = calculatedHeight + 'px';
    }
    
    // Calculate max value for better step size
    const maxValue = brandValues.length > 0 ? Math.max(...brandValues) : 0;
    const stepSize = maxValue <= 10 ? 1 : (maxValue <= 20 ? 2 : (maxValue <= 50 ? 5 : 10));
    
    // Use a single consistent color for all bars (red theme)
    const barColor = 'rgba(220, 38, 38, 0.85)'; // Hasta red
    
    new Chart(brandCtx, {
        type: 'bar',
        data: {
            labels: brandLabels,
            datasets: [{
                label: 'Number of Bookings',
                data: brandValues,
                backgroundColor: barColor,
                borderColor: 'rgba(220, 38, 38, 1)',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 'flex',
                maxBarThickness: 40,
                minBarLength: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                    padding: 14,
                    cornerRadius: 8,
                    titleFont: { size: 14, weight: '600' },
                    bodyFont: { size: 13 },
                    displayColors: false,
                    callbacks: {
                        title: function(context) {
                            return 'Plate Number: ' + context[0].label;
                        },
                        label: function(context) {
                            const count = context.parsed.x;
                            return 'Bookings: ' + count + ' booking' + (count !== 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grace: '5%',
                    title: {
                        display: true,
                        text: 'Number of Bookings',
                        font: { size: 13, weight: '600' },
                        color: '#374151',
                        padding: { top: 15, bottom: 10 }
                    },
                    ticks: {
                        stepSize: stepSize,
                        precision: 0,
                        font: { size: 12, weight: '500' },
                        color: '#6b7280',
                        padding: 8
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.08)',
                        drawBorder: true,
                        borderColor: '#e5e7eb',
                        lineWidth: 1
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Vehicle Plate Numbers',
                        font: { size: 13, weight: '600' },
                        color: '#374151',
                        padding: { left: 10, right: 15, top: 0, bottom: 0 }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 12, weight: '600' },
                        color: '#374151',
                        padding: 12,
                        callback: function(value, index) {
                            // Show full plate number - ensure all are visible
                            const label = this.getLabelForValue(value);
                            return label || '';
                        }
                    },
                    afterFit: function(scaleInstance) {
                        // Increase the width allocated to y-axis labels for better readability
                        scaleInstance.width = Math.max(scaleInstance.width, 140);
                    }
                }
            },
            layout: {
                padding: {
                    left: 15,
                    right: 15,
                    top: 15,
                    bottom: 15
                }
            },
            animation: {
                duration: 1000
            }
        }
    });
    @endif

    // Comparison Chart - REMOVED (tab hidden)
    @if(false && $activeTab === 'comparison')
    const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
    const comparisonRawLabels = {!! json_encode(array_column($comparisonData, 'month')) !!};
    const comparisonRawTotal = {!! json_encode(array_column($comparisonData, 'total')) !!};
    const comparisonRawCars = {!! json_encode(array_column($comparisonData, 'cars')) !!};
    const comparisonRawMotorcycles = {!! json_encode(array_column($comparisonData, 'motorcycles')) !!};
    
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
                    backgroundColor: 'rgba(220, 38, 38, 0.85)',
                    borderWidth: 0,
                    borderRadius: 6
                },
                {
                    label: 'Cars',
                    data: comparisonCars,
                    backgroundColor: 'rgba(37, 99, 235, 0.85)',
                    borderWidth: 0,
                    borderRadius: 6
                },
                {
                    label: 'Motorcycles',
                    data: comparisonMotorcycles,
                    backgroundColor: 'rgba(22, 163, 74, 0.85)',
                    borderWidth: 0,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: '500' },
                        color: '#374151'
                    }
                },
                y: {
                    beginAtZero: true,
                    grace: '10%',
                    ticks: {
                        precision: 0,
                        font: { size: 11 },
                        color: '#6b7280'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.06)',
                        drawBorder: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        padding: 20,
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.dataset.label + ': ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            }
        }
    });
    @endif

    window.addEventListener('beforeprint', function() {
        document.querySelector('.print-title').style.display = 'block';
    });

    window.addEventListener('afterprint', function() {
        document.querySelector('.print-title').style.display = 'none';
    });
</script>
@endpush
@endsection






