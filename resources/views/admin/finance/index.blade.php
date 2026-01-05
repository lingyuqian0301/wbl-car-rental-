@extends('layouts.admin')

@section('title', 'Finance Report')

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'expenses-profit' ? 'active' : '' }}" 
               href="{{ route('admin.reports.finance', ['tab' => 'expenses-profit']) }}">
                <i class="bi bi-calculator me-1"></i> Expenses and Profit
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'monthly-income' ? 'active' : '' }}" 
               href="{{ route('admin.reports.finance', ['tab' => 'monthly-income']) }}">
                <i class="bi bi-calendar-month me-1"></i> Monthly Income
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'daily-income' ? 'active' : '' }}" 
               href="{{ route('admin.reports.finance', ['tab' => 'daily-income']) }}">
                <i class="bi bi-calendar-day me-1"></i> Daily Income
            </a>
        </li>
    </ul>

    <!-- Expenses and Profit Tab -->
    @if($activeTab === 'expenses-profit')
        <x-admin-page-header 
            title="Expenses and Profit" 
            description="Vehicle-wise expenses and profit breakdown"
            :stats="[
                ['label' => 'Total Vehicles', 'value' => $vehicles->count() ?? 0, 'icon' => 'bi-car-front']
            ]"
        />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.finance', ['tab' => 'expenses-profit']) }}" class="d-flex gap-2 align-items-end">
                    <div>
                        <label class="form-label small">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select form-select-sm" style="width: 150px;">
                            <option value="all" {{ ($vehicleType ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="car" {{ ($vehicleType ?? 'all') === 'car' ? 'selected' : '' }}>Car</option>
                            <option value="motor" {{ ($vehicleType ?? 'all') === 'motor' ? 'selected' : '' }}>Motor</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Vehicle ID</th>
                                <th>Vehicle</th>
                                <th>Plate Number</th>
                                <th>Owner Leasing Price</th>
                                <th>Expenses (Maintenance)</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles ?? [] as $vehicle)
                                <tr>
                                    <td>#{{ $vehicle['vehicleID'] }}</td>
                                    <td>{{ $vehicle['vehicle'] }}</td>
                                    <td>{{ $vehicle['plate_number'] ?? 'N/A' }}</td>
                                    <td>RM {{ number_format($vehicle['leasing_price'], 2) }}</td>
                                    <td>RM {{ number_format($vehicle['expenses'], 2) }}</td>
                                    <td>
                                        <span class="fw-bold {{ $vehicle['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($vehicle['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No vehicles found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Monthly Income Tab -->
    @if($activeTab === 'monthly-income')
        <x-admin-page-header 
            title="Monthly Income" 
            description="Monthly income breakdown for {{ $selectedYear ?? date('Y') }}"
            :stats="[
                ['label' => 'Year', 'value' => $selectedYear ?? date('Y'), 'icon' => 'bi-calendar-year'],
                ['label' => 'Total Profit', 'value' => 'RM ' . number_format($yearTotalProfit ?? 0, 2), 'icon' => 'bi-cash-stack']
            ]"
        />

        <div class="mb-3">
            <form method="GET" action="{{ route('admin.reports.finance', ['tab' => 'monthly-income']) }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small">Year</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $selectedYear ?? date('Y') }}" min="2020" max="2100" style="width: 120px;">
                </div>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Total No. of Rental</th>
                                <th>Total Expenses</th>
                                <th>Total Earning Amount</th>
                                <th>Profit Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($months ?? [] as $month)
                                <tr>
                                    <td><strong>{{ $month['monthName'] }}</strong></td>
                                    <td>{{ $month['totalRentals'] }}</td>
                                    <td>RM {{ number_format($month['totalExpenses'], 2) }}</td>
                                    <td>RM {{ number_format($month['totalEarnings'], 2) }}</td>
                                    <td>
                                        <span class="fw-bold {{ $month['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($month['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No data found</td>
                                </tr>
                            @endforelse
                            <!-- Year Total Row -->
                            @if(isset($months) && count($months) > 0)
                                <tr class="table-info fw-bold">
                                    <td><strong>Total (Year {{ $selectedYear ?? date('Y') }})</strong></td>
                                    <td>{{ $yearTotalRentals ?? 0 }}</td>
                                    <td>RM {{ number_format($yearTotalExpenses ?? 0, 2) }}</td>
                                    <td>RM {{ number_format($yearTotalEarnings ?? 0, 2) }}</td>
                                    <td>
                                        <span class="{{ ($yearTotalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($yearTotalProfit ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Daily Income Tab -->
    @if($activeTab === 'daily-income')
        <x-admin-page-header 
            title="Daily Income" 
            description="Daily income breakdown for {{ \Carbon\Carbon::create($selectedYear ?? date('Y'), $selectedMonth ?? date('m'), 1)->format('F Y') }}"
            :stats="[
                ['label' => 'Month', 'value' => \Carbon\Carbon::create($selectedYear ?? date('Y'), $selectedMonth ?? date('m'), 1)->format('F Y'), 'icon' => 'bi-calendar-month']
            ]"
        />

        <div class="mb-3">
            <form method="GET" action="{{ route('admin.reports.finance', ['tab' => 'daily-income']) }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small">Year</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $selectedYear ?? date('Y') }}" min="2020" max="2100" style="width: 120px;">
                </div>
                <div>
                    <label class="form-label small">Month</label>
                    <select name="month" class="form-select form-select-sm" style="width: 150px;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ ($selectedMonth ?? date('m')) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Rental Amount</th>
                                <th>Total Expenses</th>
                                <th>Total Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($days ?? [] as $day)
                                <tr>
                                    <td>{{ $day['dateFormatted'] }}</td>
                                    <td>RM {{ number_format($day['totalRentalAmount'], 2) }}</td>
                                    <td>RM {{ number_format($day['totalExpenses'], 2) }}</td>
                                    <td>
                                        <span class="fw-bold {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($day['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
