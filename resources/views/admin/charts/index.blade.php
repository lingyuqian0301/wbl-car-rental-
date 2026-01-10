@extends('layouts.admin')

@section('title', 'Charts & Analytics')

@push('styles')
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
        <div>
            <button onclick="window.print()" class="btn btn-danger btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.reports.charts.export-pdf', array_merge(request()->all(), ['tab' => $activeTab])) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
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
                <i class="bi bi-pie-chart-fill"></i> Car Brand Rental
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'comparison' ? 'active' : '' }}" 
               href="{{ route('admin.reports.charts', array_merge(request()->except('tab'), ['tab' => 'comparison'])) }}">
                <i class="bi bi-bar-chart-line"></i> Comparison
            </a>
        </li>
    </ul>

    <!-- Weekly Rental Chart -->
    @if($activeTab === 'weekly')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-calendar-week"></i> Weekly Rental Line Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="weekly">
                <input type="week" name="selected_week" class="form-control form-control-sm" value="{{ $selectedWeek }}" onchange="this.form.submit()">
            </form>
        </div>
        <div class="chart-container">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Monthly Rental Chart -->
    @if($activeTab === 'monthly')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-calendar-month"></i> Monthly Rental Line Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="monthly">
                <input type="month" name="selected_month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
            </form>
        </div>
        <div class="chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Faculty Rental Pie Chart -->
    @if($activeTab === 'faculty')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-pie-chart"></i> Faculty Rental Pie Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="faculty">
                <input type="month" name="faculty_month" class="form-control form-control-sm" value="{{ $facultyMonth }}">
                <input type="number" name="faculty_year" class="form-control form-control-sm" value="{{ $facultyYear }}" min="2020" max="2100">
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <div class="chart-container">
            <canvas id="facultyChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Car Brand Rental Pie Chart -->
    @if($activeTab === 'brand')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h5><i class="bi bi-pie-chart-fill"></i> Car Brand Rental Pie Chart</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="hidden" name="tab" value="brand">
                <input type="month" name="brand_month" class="form-control form-control-sm" value="{{ $brandMonth }}">
                <select name="brand_vehicle_type" class="form-select form-select-sm">
                    <option value="all" {{ $brandVehicleType === 'all' ? 'selected' : '' }}>All</option>
                    <option value="car" {{ $brandVehicleType === 'car' ? 'selected' : '' }}>Car</option>
                    <option value="motorcycle" {{ $brandVehicleType === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                </select>
                <button type="submit" class="btn btn-sm btn-danger">Apply</button>
            </form>
        </div>
        <div class="chart-container">
            <canvas id="brandChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Comparison Bar Chart -->
    @if($activeTab === 'comparison')
    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i class="bi bi-bar-chart-line"></i> Comparison Bar Chart (Latest 4 Months)</h5>
        </div>
        <div class="chart-container">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Weekly Chart
    @if($activeTab === 'weekly')
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyLabels = {!! json_encode(array_column($weeklyData, 'date')) !!};
    const weeklyCounts = {!! json_encode(array_column($weeklyData, 'count')) !!};
    
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: weeklyLabels.length > 0 ? weeklyLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Number of Bookings',
                data: weeklyCounts.length > 0 ? weeklyCounts : [0, 0, 0, 0, 0, 0, 0],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
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
                        text: 'Day'
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: Math.max(...(weeklyCounts.length > 0 ? weeklyCounts : [0])) > 0 ? undefined : 10,
                    ticks: {
                        stepSize: 1,
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
    @endif

    // Monthly Chart
    @if($activeTab === 'monthly')
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyLabels = {!! json_encode(array_column($monthlyData, 'date')) !!};
    const monthlyCounts = {!! json_encode(array_column($monthlyData, 'count')) !!};
    
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels.length > 0 ? monthlyLabels : Array.from({length: 31}, (_, i) => i + 1),
            datasets: [{
                label: 'Number of Bookings',
                data: monthlyCounts.length > 0 ? monthlyCounts : Array(31).fill(0),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
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
                        text: 'Day of Month'
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: Math.max(...(monthlyCounts.length > 0 ? monthlyCounts : [0])) > 0 ? undefined : 10,
                    ticks: {
                        stepSize: 1,
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
    @endif

    // Faculty Chart
    @if($activeTab === 'faculty')
    const facultyCtx = document.getElementById('facultyChart').getContext('2d');
    const facultyLabels = {!! json_encode(array_keys($facultyData)) !!};
    const facultyValues = {!! json_encode(array_values($facultyData)) !!};
    
    new Chart(facultyCtx, {
        type: 'pie',
        data: {
            labels: facultyLabels.length > 0 ? facultyLabels : ['No Data'],
            datasets: [{
                data: facultyValues.length > 0 ? facultyValues : [1],
                backgroundColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1', '#e83e8c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (facultyValues.length === 0) {
                                return 'No Data';
                            }
                            return context.label + ': ' + context.parsed + ' bookings';
                        }
                    },
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        }
    });
    @endif

    // Brand Chart
    @if($activeTab === 'brand')
    const brandCtx = document.getElementById('brandChart').getContext('2d');
    const brandLabels = {!! json_encode(array_keys($brandData)) !!};
    const brandValues = {!! json_encode(array_values($brandData)) !!};
    
    new Chart(brandCtx, {
        type: 'pie',
        data: {
            labels: brandLabels.length > 0 ? brandLabels : ['No Data'],
            datasets: [{
                data: brandValues.length > 0 ? brandValues : [1],
                backgroundColor: [
                    '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1', '#e83e8c', '#198754'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (brandValues.length === 0) {
                                return 'No Data';
                            }
                            return context.label + ': ' + context.parsed + ' bookings';
                        }
                    },
                    legend: {
                        display: true,
                        position: 'right'
                    }
                }
            }
        }
    });
    @endif

    // Comparison Chart
    @if($activeTab === 'comparison')
    const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
    const comparisonLabels = {!! json_encode(array_column($comparisonData, 'month')) !!};
    const comparisonTotal = {!! json_encode(array_column($comparisonData, 'total')) !!};
    const comparisonCars = {!! json_encode(array_column($comparisonData, 'cars')) !!};
    const comparisonMotorcycles = {!! json_encode(array_column($comparisonData, 'motorcycles')) !!};
    
    new Chart(comparisonCtx, {
        type: 'bar',
        data: {
            labels: comparisonLabels.length > 0 ? comparisonLabels : ['Month 1', 'Month 2', 'Month 3', 'Month 4'],
            datasets: [
                {
                    label: 'Total',
                    data: comparisonTotal.length > 0 ? comparisonTotal : [0, 0, 0, 0],
                    backgroundColor: '#dc3545'
                },
                {
                    label: 'Cars',
                    data: comparisonCars.length > 0 ? comparisonCars : [0, 0, 0, 0],
                    backgroundColor: '#0d6efd'
                },
                {
                    label: 'Motorcycles',
                    data: comparisonMotorcycles.length > 0 ? comparisonMotorcycles : [0, 0, 0, 0],
                    backgroundColor: '#198754'
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
                    }
                },
                y: {
                    beginAtZero: true,
                    min: 0,
                    max: Math.max(...(comparisonTotal.length > 0 ? comparisonTotal : [0])) > 0 ? undefined : 10,
                    ticks: {
                        stepSize: 1,
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






