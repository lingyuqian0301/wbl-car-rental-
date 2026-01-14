@extends('layouts.admin')

@php
    use Carbon\Carbon;
@endphp

@section('title', 'Finance Report')

@push('styles')
<style>
    @media print {
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .btn, .nav-tabs, .card-header .btn, .d-flex.justify-content-end, .card-header, .filter-section, .form-select, .form-label, .input-group {
            display: none !important;
        }
        .card {
            border: none;
            box-shadow: none;
            background: white;
        }
        .card-body {
            padding: 0;
        }
        /* PDF-style header */
        /* PDF-style header */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            page-break-after: avoid;
        }
        .print-header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 24px;
        }
        .print-header p {
            margin: 5px 0;
        }
        /* PDF-style summary */
        .print-summary {
            display: block !important;
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            page-break-after: avoid;
        }
        .print-summary h3 {
            margin-top: 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        /* PDF-style table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-right {
            text-align: right;
        }
        tfoot tr {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .page-header {
            page-break-after: avoid;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }
</style>
@endpush

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
            <a class="nav-link {{ $activeTab === 'weekly-income' ? 'active' : '' }}" 
               href="{{ route('admin.reports.finance', ['tab' => 'weekly-income']) }}">
                <i class="bi bi-calendar-week me-1"></i> Weekly Income
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
        <!-- Print Header (only visible when printing) -->
        <div class="print-header" style="display: none;">
            <h1>Expenses and Profit Report</h1>
            <p>Period: {{ $selectedYear ?? date('Y') }}{{ isset($selectedMonth) ? ' - ' . \Carbon\Carbon::create($selectedYear ?? date('Y'), $selectedMonth, 1)->format('F') : '' }}</p>
            @if(isset($vehicleType) && $vehicleType !== 'all')
                <p>Vehicle Type: {{ ucfirst($vehicleType) }}</p>
            @endif
            <p>Generated: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
        </div>

        <!-- Print Summary (only visible when printing) -->
        <div class="print-summary" style="display: none;">
            <h3>Summary</h3>
            <div class="summary-row">
                <strong>Total Vehicles:</strong>
                <span>{{ $totalVehicles ?? 0 }}</span>
            </div>
            <div class="summary-row">
                <strong>Total Profit:</strong>
                <span class="{{ ($totalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">RM {{ number_format($totalProfit ?? 0, 2) }}</span>
            </div>
        </div>

        <x-admin-page-header 
            title="Expenses and Profit" 
            description="Vehicle-wise expenses and profit breakdown"
            :stats="[
                ['label' => 'Total Vehicles', 'value' => $totalVehicles ?? 0, 'icon' => 'bi-car-front'],
                ['label' => 'Total Profit', 'value' => 'RM ' . number_format($totalProfit ?? 0, 2), 'icon' => 'bi-cash-stack']
            ]"
        />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Print and Export Buttons -->
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.finance.export-pdf', ['tab' => 'expenses-profit', 'vehicle_type' => $vehicleType ?? 'all', 'month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')]) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.finance.export-excel', ['tab' => 'expenses-profit', 'vehicle_type' => $vehicleType ?? 'all', 'month' => $selectedMonth ?? date('m'), 'year' => $selectedYear ?? date('Y')]) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.finance', ['tab' => 'expenses-profit']) }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label small">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select form-select-sm">
                            <option value="all" {{ ($vehicleType ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="car" {{ ($vehicleType ?? 'all') === 'car' ? 'selected' : '' }}>Car</option>
                            <option value="motor" {{ ($vehicleType ?? 'all') === 'motor' ? 'selected' : '' }}>Motor</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Month</label>
                        <select name="month" class="form-select form-select-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ ($selectedMonth ?? date('m')) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Year</label>
                        <input type="number" name="year" class="form-control form-control-sm" value="{{ $selectedYear ?? date('Y') }}" min="2020" max="2100">
                    </div>
                    <div class="col-md-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calculator me-1"></i> Expenses and Profit</h5>
                <span class="badge bg-light text-dark">{{ count($vehicles ?? []) }} vehicles</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle ID</th>
                                <th>Vehicle</th>
                                <th>Plate Number</th>
                                <th class="text-end">Owner Leasing Price</th>
                                <th class="text-end">Expenses (Maintenance)</th>
                                <th class="text-end">Expenses (Staff)</th>
                                <th class="text-end">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles ?? [] as $vehicle)
                                <tr>
                                    <td><strong>#{{ $vehicle['vehicleID'] }}</strong></td>
                                    <td>{{ $vehicle['vehicle'] }}</td>
                                    <td>{{ $vehicle['plate_number'] ?? 'N/A' }}</td>
                                    <td class="text-end">
                                        <span class="editable-leasing-price" 
                                              data-owner-id="{{ $vehicle['ownerID'] ?? '' }}"
                                              data-current-value="{{ $vehicle['leasing_price'] ?? 0 }}"
                                              style="cursor: pointer; text-decoration: underline;">
                                            RM {{ number_format($vehicle['leasing_price'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">RM {{ number_format($vehicle['expenses'], 2) }}</td>
                                    <td class="text-end">RM {{ number_format($vehicle['staff_expenses'] ?? 0, 2) }}</td>
                                    <td class="text-end {{ $vehicle['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        <span class="fw-bold">
                                            RM {{ number_format($vehicle['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No vehicles found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr style="font-weight: bold;">
                                <td colspan="6" class="text-end">Total Profit:</td>
                                <td class="text-end {{ ($totalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    RM {{ number_format($totalProfit ?? 0, 2) }}
                                </td>
                            </tr>
                        </tfoot>
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

        <!-- Print and Export Buttons -->
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.finance.export-pdf', ['tab' => 'monthly-income', 'year' => $selectedYear ?? date('Y')]) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.finance.export-excel', ['tab' => 'monthly-income', 'year' => $selectedYear ?? date('Y')]) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>

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
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-month me-1"></i> Monthly Income</h5>
                <span class="badge bg-light text-dark">{{ $selectedYear ?? date('Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Total No. of Rental</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-end">Total Earning Amount</th>
                                <th class="text-end">Profit Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($months ?? [] as $month)
                                <tr>
                                    <td><strong>{{ $month['monthName'] }}</strong></td>
                                    <td>{{ $month['totalRentals'] }}</td>
                                    <td class="text-end">RM {{ number_format($month['totalExpenses'], 2) }}</td>
                                    <td class="text-end">RM {{ number_format($month['totalEarnings'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $month['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($month['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No data found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!-- Year Total Row -->
                        @if(isset($months) && count($months) > 0)
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td><strong>Total (Year {{ $selectedYear ?? date('Y') }})</strong></td>
                                    <td>{{ $yearTotalRentals ?? 0 }}</td>
                                    <td class="text-end">RM {{ number_format($yearTotalExpenses ?? 0, 2) }}</td>
                                    <td class="text-end">RM {{ number_format($yearTotalEarnings ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        <span class="{{ ($yearTotalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($yearTotalProfit ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
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

        <!-- Print and Export Buttons -->
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.finance.export-pdf', ['tab' => 'daily-income', 'year' => $selectedYear ?? date('Y'), 'month' => $selectedMonth ?? date('m')]) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.finance.export-excel', ['tab' => 'daily-income', 'year' => $selectedYear ?? date('Y'), 'month' => $selectedMonth ?? date('m')]) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>

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
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-day me-1"></i> Daily Income</h5>
                <span class="badge bg-light text-dark">{{ \Carbon\Carbon::create($selectedYear ?? date('Y'), $selectedMonth ?? date('m'), 1)->format('F Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Total Earning Amount</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-end">Total Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($days ?? [] as $day)
                                <tr>
                                    <td><strong>{{ $day['dateFormatted'] }}</strong></td>
                                    <td class="text-end">RM {{ number_format($day['totalEarnings'], 2) }}</td>
                                    <td class="text-end">RM {{ number_format($day['totalExpenses'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($day['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No data found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Weekly Income Tab -->
    @if($activeTab === 'weekly-income')
        <x-admin-page-header 
            title="Weekly Income" 
            description="Weekly income breakdown (7 days from selected start date)"
            :stats="[
                ['label' => 'Start Date', 'value' => \Carbon\Carbon::parse($startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'))->format('d M Y'), 'icon' => 'bi-calendar'],
                ['label' => 'Total Profit', 'value' => 'RM ' . number_format($weekTotalProfit ?? 0, 2), 'icon' => 'bi-cash-stack']
            ]"
        />

        <!-- Print and Export Buttons -->
        <div class="d-flex justify-content-end mb-3 gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.finance.export-pdf', ['tab' => 'weekly-income', 'start_date' => $startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d')]) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.finance.export-excel', ['tab' => 'weekly-income', 'start_date' => $startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d')]) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>

        <div class="mb-3">
            <form method="GET" action="{{ route('admin.reports.finance', ['tab' => 'weekly-income']) }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-calendar-week me-1"></i> Weekly Income</h5>
                <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'))->format('d M Y') }} - {{ \Carbon\Carbon::parse($startDate ?? Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'))->addDays(6)->format('d M Y') }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Total Earning Amount</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-end">Total Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($days ?? [] as $day)
                                <tr>
                                    <td><strong>{{ $day['dateFormatted'] }}</strong></td>
                                    <td class="text-end">RM {{ number_format($day['totalEarnings'], 2) }}</td>
                                    <td class="text-end">RM {{ number_format($day['totalExpenses'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($day['profit'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No data found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!-- Week Total Row -->
                        @if(isset($days) && count($days) > 0)
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td><strong>Total (Week)</strong></td>
                                    <td class="text-end">RM {{ number_format($weekTotalEarnings ?? 0, 2) }}</td>
                                    <td class="text-end">RM {{ number_format($weekTotalExpenses ?? 0, 2) }}</td>
                                    <td class="text-end">
                                        <span class="{{ ($weekTotalProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                            RM {{ number_format($weekTotalProfit ?? 0, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Make leasing price editable
    document.querySelectorAll('.editable-leasing-price').forEach(function(element) {
        element.addEventListener('click', function() {
            const ownerId = this.dataset.ownerId;
            const currentValue = parseFloat(this.dataset.currentValue);
            const newValue = prompt('Enter new leasing price:', currentValue);
            
            if (newValue !== null && !isNaN(newValue) && newValue >= 0) {
                fetch(`/admin/reports/finance/owner/${ownerId}/leasing-price`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        leasing_price: parseFloat(newValue)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update leasing price.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating leasing price.');
                });
            }
        });
    });
</script>
@endpush
@endsection
