@extends('layouts.admin')

@section('title', 'Rental Reports')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .report-table {
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
    .summary-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 25px;
    }
    .summary-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .summary-item:last-child {
        border-bottom: none;
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
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Rental Reports" 
        description="Generate comprehensive rental reports with filters and summaries"
        :stats="[
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
            ['label' => 'Completed', 'value' => $completedBookings, 'icon' => 'bi-check-circle'],
            ['label' => 'Bookings Today', 'value' => $bookingsToday, 'icon' => 'bi-calendar-day'],
            ['label' => 'Total Revenue', 'value' => 'RM ' . number_format($totalRevenue, 2), 'icon' => 'bi-currency-dollar']
        ]"
        :date="$today"
    >
        <x-slot name="actions">
            <button onclick="window.print()" class="btn btn-light text-danger pill-btn">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.reports.rentals.export-pdf', request()->all()) }}" class="btn btn-outline-light text-white pill-btn" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
        </x-slot>
    </x-admin-page-header>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.reports.rentals') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Date Range</label>
                <select name="date_range" class="form-select form-select-sm" id="date_range_select">
                    <option value="all" {{ $dateRange === 'all' ? 'selected' : '' }}>All</option>
                    <option value="daily" {{ $dateRange === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $dateRange === 'weekly' ? 'selected' : '' }}>Weekly Range</option>
                    <option value="monthly" {{ $dateRange === 'monthly' ? 'selected' : '' }}>Monthly Range</option>
                    <option value="custom" {{ ($dateRange === 'custom' || ($dateFrom && $dateTo && $dateRange !== 'daily' && $dateRange !== 'weekly' && $dateRange !== 'monthly')) ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" id="date_from_input">
            </div>
            <div>
                <label class="form-label small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" id="date_to_input">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle Type</label>
                <select name="vehicle_type" class="form-select form-select-sm">
                    <option value="all" {{ $vehicleType === 'all' ? 'selected' : '' }}>All</option>
                    <option value="car" {{ $vehicleType === 'car' ? 'selected' : '' }}>Car</option>
                    <option value="motor" {{ $vehicleType === 'motor' ? 'selected' : '' }}>Motor</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Booking Status</label>
                <select name="booking_status" class="form-select form-select-sm">
                    <option value="all" {{ $bookingStatus === 'all' ? 'selected' : '' }}>All</option>
                    <option value="done" {{ $bookingStatus === 'done' ? 'selected' : '' }}>Done</option>
                    <option value="upcoming" {{ $bookingStatus === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="cancelled" {{ $bookingStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Payment Status</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>All</option>
                    <option value="deposit" {{ $paymentStatus === 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="fully" {{ $paymentStatus === 'fully' ? 'selected' : '' }}>Fully</option>
                    <option value="refunded" {{ $paymentStatus === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Customer ID</label>
                <input type="number" name="customer_id" class="form-control form-control-sm" value="{{ $customerId }}" placeholder="Customer ID">
            </div>
            <div>
                <label class="form-label small fw-semibold">Customer Name</label>
                <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ $customerName }}" placeholder="Customer Name">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle ID</label>
                <input type="number" name="vehicle_id" class="form-control form-control-sm" value="{{ $vehicleId }}" placeholder="Vehicle ID">
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
                <label class="form-label small fw-semibold">Plate No</label>
                <input type="text" name="plate_no" class="form-control form-control-sm" value="{{ $plateNo }}" placeholder="Plate No">
            </div>
            <div>
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
            @if(request()->anyFilled(['date_range', 'date_from', 'date_to', 'vehicle_type', 'booking_status', 'payment_status', 'customer_id', 'customer_name', 'vehicle_id', 'vehicle_brand', 'vehicle_model', 'plate_no']))
            <div>
                <a href="{{ route('admin.reports.rentals') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Summary -->
    <div class="summary-card">
        <h5 class="mb-3"><i class="bi bi-bar-chart"></i> Summary</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="summary-item">
                    <strong>Total No. of Bookings:</strong> {{ $summaries['totalBookings'] }}
                </div>
                <div class="summary-item">
                    <strong>Total Revenue:</strong> RM {{ number_format($summaries['totalRevenue'], 2) }}
                </div>
                <div class="summary-item">
                    <strong>No. of Cancelled Bookings:</strong> {{ $summaries['cancelledBookings'] }}
                </div>
                <div class="summary-item">
                    <strong>Most Frequently Booked Vehicle (Overall):</strong> {{ $summaries['mostFrequentVehicle'] }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-item">
                    <strong>Most Frequently Booked Car:</strong> {{ $summaries['mostFrequentCar'] }}
                </div>
                <div class="summary-item">
                    <strong>Most Frequently Booked Motorcycle:</strong> {{ $summaries['mostFrequentMotorcycle'] }}
                </div>
                <div class="summary-item">
                    <strong>Peak Booked Period:</strong> {{ $summaries['peakPeriod'] }}
                </div>
                <div class="summary-item">
                    <strong>Most Active Faculty:</strong> {{ $summaries['mostActiveFaculty'] }} ({{ $summaries['facultyBookingCount'] }} bookings)
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="report-table">
        <div class="table-header">
            <i class="bi bi-calendar-range"></i> Rental Report ({{ $bookings->count() }} bookings)
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Vehicle Brand & Model</th>
                        <th>Plate No</th>
                        <th>Booking Date</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Duration</th>
                        <th>Payment Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $vehicle = $booking->vehicle;
                            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                        @endphp
                        <tr>
                            <td><strong>#{{ $booking->bookingID ?? $booking->id }}</strong></td>
                            <td>{{ $booking->user->name ?? 'Unknown' }}</td>
                            <td>
                                @if($vehicle)
                                    {{ $vehicle->vehicle_brand ?? '' }} {{ $vehicle->vehicle_model ?? '' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($vehicle)
                                    {{ $vehicle->plate_number ?? $vehicle->plate_no ?? 'N/A' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->rental_start_date)
                                    {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->rental_start_date)
                                    {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->rental_end_date)
                                    {{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $booking->duration_days ?? 0 }} days</td>
                            <td><strong>RM {{ number_format($totalPaid, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No bookings found with the selected filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Print function
    function printReport() {
        window.print();
    }
    
    // Date range selector handling
    document.addEventListener('DOMContentLoaded', function() {
        const dateRangeSelect = document.getElementById('date_range_select');
        const dateFromInput = document.getElementById('date_from_input');
        const dateToInput = document.getElementById('date_to_input');
        
        function updateDateInputs() {
            const selectedRange = dateRangeSelect.value;
            const dateFromDiv = dateFromInput ? dateFromInput.closest('div') : null;
            const dateToDiv = dateToInput ? dateToInput.closest('div') : null;
            
            if (selectedRange === 'custom') {
                if (dateFromDiv) dateFromDiv.style.display = 'block';
                if (dateToDiv) dateToDiv.style.display = 'block';
            } else if (selectedRange === 'all') {
                if (dateFromDiv) dateFromDiv.style.display = 'none';
                if (dateToDiv) dateToDiv.style.display = 'none';
            } else {
                if (dateFromDiv) dateFromDiv.style.display = 'none';
                if (dateToDiv) dateToDiv.style.display = 'none';
            }
        }
        
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', updateDateInputs);
            updateDateInputs(); // Initial call
        }
    });
</script>
@endpush
@endsection






