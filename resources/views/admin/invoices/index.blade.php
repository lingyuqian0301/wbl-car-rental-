@extends('layouts.admin')

@section('title', 'Invoices')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .invoice-table {
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
    .table thead th {
        background: var(--admin-red-light);
        color: var(--admin-red-dark);
        font-weight: 600;
        border-bottom: 2px solid var(--admin-red);
        padding: 12px;
        font-size: 0.9rem;
    }
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
    .filter-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        align-items: end;
    }
    .filter-row > div {
        min-width: 0;
    }
    .filter-row .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
    }
    .filter-row .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .filter-row .btn {
        font-size: 0.85rem;
        padding: 4px 12px;
    }
    .btn-generate {
        padding: 4px 12px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Invoices" 
        description="View and generate invoices for fully paid bookings"
        :stats="[
            ['label' => 'Total Invoices', 'value' => $totalInvoices, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
            ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
        ]"
        :date="$today"
    />

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Booking Date</label>
                <select name="sort_booking_date" class="form-select form-select-sm">
                    <option value="desc" {{ $sortBookingDate === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ $sortBookingDate === 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Invoice ID</label>
                <select name="sort_invoice_id" class="form-select form-select-sm">
                    <option value="desc" {{ $sortInvoiceId === 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ $sortInvoiceId === 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
            @if($sortBookingDate !== 'desc' || $sortInvoiceId !== 'desc')
            <div>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="invoice-table">
        <div class="table-header">
            <i class="bi bi-file-earmark-text"></i> All Invoices ({{ $bookings->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Booking Date</th>
                        <th>Car Model</th>
                        <th>Car Type</th>
                        <th>Car Plate No</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $vehicle = $booking->vehicle;
                            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                            $isFullyPaid = $totalPaid >= $booking->total_price;
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $booking->bookingID ?? $booking->id }}</strong>
                            </td>
                            <td>
                                #{{ $booking->user->id ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $booking->user->name ?? 'Unknown Customer' }}
                            </td>
                            <td>
                                @if($booking->rental_start_date)
                                    @if($booking->rental_start_date instanceof \Carbon\Carbon)
                                        {{ $booking->rental_start_date->format('d M Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') }}
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($vehicle)
                                    {{ $vehicle->full_model ?? ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '') }}
                                @else
                                    <span class="text-muted">Vehicle not found</span>
                                @endif
                            </td>
                            <td>
                                @if($vehicle)
                                    @if($vehicle instanceof \App\Models\Car)
                                        {{ $vehicle->car_type ?? 'Car' }}
                                    @elseif($vehicle instanceof \App\Models\Motorcycle)
                                        {{ $vehicle->motor_type ?? 'Motorcycle' }}
                                    @else
                                        {{ $vehicle->vehicle_type ?? 'Vehicle' }}
                                    @endif
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
                                @if($isFullyPaid)
                                    <a href="{{ route('invoices.generate', $booking->bookingID ?? $booking->id) }}" 
                                       class="btn btn-sm btn-danger btn-generate" 
                                       target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> Generate Invoice
                                    </a>
                                @else
                                    <span class="text-muted small">Payment pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No invoices available. Invoices are only generated for bookings with full payment verified.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($bookings->hasPages())
            <div class="p-3 border-top">
                {{ $bookings->links() }}
            </div>
        @endif
        </div>
    </div>
</div>
@endsection






