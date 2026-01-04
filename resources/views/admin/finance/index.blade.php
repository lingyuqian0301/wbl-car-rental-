@extends('layouts.admin')

@section('title', 'Finance Report')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .summary-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 25px;
    }
    .summary-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    .summary-item:last-child {
        border-bottom: none;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--admin-red);
    }
    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .filter-row .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
    }
    .filter-row .form-control,
    .filter-row .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .data-table {
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

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="mb-1"><i class="bi bi-cash-stack"></i> Finance Report</h2>
            <p class="text-muted mb-0">View earnings, expenses, and profit analysis</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-danger btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="{{ route('admin.reports.finance.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="print-title" style="display: none;">
        <h2>Finance Report</h2>
        <p>Period: {{ $dateFrom }} to {{ $dateTo }}</p>
    </div>

    <!-- Filters -->
    <div class="filter-card no-print">
        <form method="GET" action="{{ route('admin.reports.finance.index') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Period Type</label>
                <select name="period_type" id="period_type" class="form-select form-select-sm" onchange="updatePeriodFields()">
                    <option value="daily" {{ $periodType === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $periodType === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $periodType === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $periodType === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div id="daily_field" style="display: {{ $periodType === 'daily' ? 'block' : 'none' }};">
                <label class="form-label small fw-semibold">Date</label>
                <input type="date" name="selected_date" class="form-control form-control-sm" value="{{ $selectedDate }}">
            </div>
            <div id="weekly_fields" style="display: {{ $periodType === 'weekly' ? 'block' : 'none' }};">
                <label class="form-label small fw-semibold">Week From</label>
                <input type="date" name="week_from" class="form-control form-control-sm" value="{{ $weekFrom }}">
            </div>
            <div id="weekly_fields_to" style="display: {{ $periodType === 'weekly' ? 'block' : 'none' }};">
                <label class="form-label small fw-semibold">Week To</label>
                <input type="date" name="week_to" class="form-control form-control-sm" value="{{ $weekTo }}">
            </div>
            <div id="monthly_field" style="display: {{ $periodType === 'monthly' ? 'block' : 'none' }};">
                <label class="form-label small fw-semibold">Month</label>
                <input type="month" name="selected_month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
            </div>
            <div id="yearly_field" style="display: {{ $periodType === 'yearly' ? 'block' : 'none' }};">
                <label class="form-label small fw-semibold">Year</label>
                <input type="number" name="selected_year" class="form-control form-control-sm" value="{{ $selectedYear }}" min="2020" max="2100">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle Type</label>
                <select name="vehicle_type" class="form-select form-select-sm">
                    <option value="all" {{ $vehicleType === 'all' ? 'selected' : '' }}>All</option>
                    <option value="car" {{ $vehicleType === 'car' ? 'selected' : '' }}>Car</option>
                    <option value="motorcycle" {{ $vehicleType === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle Brand</label>
                <input type="text" name="vehicle_brand" class="form-control form-control-sm" value="{{ $vehicleBrand }}" placeholder="Brand">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle Model</label>
                <input type="text" name="vehicle_model" class="form-control form-control-sm" value="{{ $vehicleModel }}" placeholder="Model">
            </div>
            <div>
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Summary -->
    <div class="summary-card">
        <h5 class="mb-3"><i class="bi bi-calculator"></i> Financial Summary</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="summary-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Total Earnings</strong>
                            <div class="small text-muted">Deposit: RM {{ number_format($depositEarnings, 2) }}</div>
                            <div class="small text-muted">Balance: RM {{ number_format($balanceEarnings, 2) }}</div>
                            <div class="small text-muted">Full Payment: RM {{ number_format($fullPaymentEarnings, 2) }}</div>
                        </div>
                        <div class="summary-value text-success">RM {{ number_format($totalEarnings, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Total Expenses</strong>
                            <div class="small text-muted">Maintenance: RM {{ number_format($maintenanceExpenses ?? 0, 2) }}</div>
                        </div>
                        <div class="summary-value text-danger">RM {{ number_format($totalExpenses, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="summary-item border-top border-3">
            <div class="d-flex justify-content-between align-items-center">
                <strong class="fs-5">Net Profit</strong>
                <div class="summary-value {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    RM {{ number_format($totalProfit, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings List -->
    <div class="data-table mb-4">
        <div class="table-header">
            <i class="bi bi-arrow-up-circle"></i> Earnings List
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Vehicle</th>
                        <th>Payment Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($earningsList as $earning)
                        <tr>
                            <td>#{{ $earning['payment_id'] }}</td>
                            <td>#{{ $earning['booking_id'] ?? 'N/A' }}</td>
                            <td>{{ $earning['customer_name'] }}</td>
                            <td>{{ $earning['vehicle'] }}</td>
                            <td>
                                <span class="badge {{ $earning['payment_type'] === 'Full Payment' ? 'bg-success' : ($earning['payment_type'] === 'Deposit' ? 'bg-warning text-dark' : 'bg-info') }}">
                                    {{ $earning['payment_type'] }}
                                </span>
                            </td>
                            <td><strong>RM {{ number_format($earning['amount'], 2) }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($earning['payment_date'])->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No earnings found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expenses List -->
    <div class="data-table">
        <div class="table-header">
            <i class="bi bi-arrow-down-circle"></i> Expenses List
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expensesList as $expense)
                        <tr>
                            <td>
                                <span class="badge {{ $expense['type'] === 'Leasing' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                    {{ $expense['type'] }}
                                </span>
                            </td>
                            <td>{{ $expense['description'] }}</td>
                            <td><strong>RM {{ number_format($expense['amount'], 2) }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($expense['date'])->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No expenses found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updatePeriodFields() {
        const periodType = document.getElementById('period_type').value;
        document.getElementById('daily_field').style.display = periodType === 'daily' ? 'block' : 'none';
        document.getElementById('weekly_fields').style.display = periodType === 'weekly' ? 'block' : 'none';
        document.getElementById('weekly_fields_to').style.display = periodType === 'weekly' ? 'block' : 'none';
        document.getElementById('monthly_field').style.display = periodType === 'monthly' ? 'block' : 'none';
        document.getElementById('yearly_field').style.display = periodType === 'yearly' ? 'block' : 'none';
    }

    window.addEventListener('beforeprint', function() {
        document.querySelector('.print-title').style.display = 'block';
    });

    window.addEventListener('afterprint', function() {
        document.querySelector('.print-title').style.display = 'none';
    });
</script>
@endpush
@endsection
