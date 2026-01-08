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
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
    .filter-row .form-control,
    .filter-row .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .filter-row .btn {
        font-size: 0.85rem;
        padding: 4px 12px;
    }
    .invoice-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .invoice-info-text div {
        margin-bottom: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Invoices" 
        description="View and manage all invoices"
        :stats="[
            ['label' => 'Total Invoices', 'value' => $totalInvoices, 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
            ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
        ]"
        :date="$today"
    />

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

    <div class="filter-card">
        <form method="GET" action="{{ route('admin.invoices.index') }}">
            <div class="row g-2 mb-2">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" 
                           class="form-control form-control-sm" 
                           placeholder="Plate No">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="invoice_no_asc" {{ ($sort ?? 'invoice_no_asc') === 'invoice_no_asc' ? 'selected' : '' }}>Asc Invoice No</option>
                        <option value="issue_date_desc" {{ ($sort ?? '') === 'issue_date_desc' ? 'selected' : '' }}>Desc Issue Date</option>
                        <option value="pickup_date_desc" {{ ($sort ?? '') === 'pickup_date_desc' ? 'selected' : '' }}>Desc Pickup Date</option>
                    </select>
                </div>
                
                <!-- Date Filter Type -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Filter By</label>
                    <select name="date_filter_type" class="form-select form-select-sm" id="dateFilterType">
                        <option value="issue_date" {{ ($dateFilterType ?? 'issue_date') === 'issue_date' ? 'selected' : '' }}>Issue Date</option>
                        <option value="pickup_date" {{ ($dateFilterType ?? '') === 'pickup_date' ? 'selected' : '' }}>Pickup Date</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}">
                </div>
                
                <!-- Date To -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo ?? '' }}">
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                </div>
                @if($search || $dateFrom || $dateTo || ($sort ?? '') !== 'invoice_no_asc')
                <div class="col-md-auto">
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>

    <div class="invoice-table">
        <div class="table-header">
            <i class="bi bi-file-earmark-text"></i> All Invoices ({{ $bookings->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Customer Name</th>
                        <th>Booking Date</th>
                        <th>Invoice Picture</th>
                        <th>Car Plate No</th>
                        <th>Total Payment Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $invoice = $booking->invoice;
                            $customer = $booking->customer;
                            $user = $customer->user ?? null;
                            $vehicle = $booking->vehicle;
                            $additionalCharges = $booking->additionalCharges;
                            
                            // Calculate total payment amount
                            $totalPaid = $booking->payments()
                                ->where('payment_status', 'Verified')
                                ->sum('total_amount');
                            
                            $depositAmount = $booking->deposit_amount ?? 0;
                            
                            // FIX: Added fallback to total_amount if rental_amount is missing
                            $rentalAmount = $booking->rental_amount ?? $booking->total_amount ?? 0;
                            
                            $additionalChargesTotal = $additionalCharges ? ($additionalCharges->total_extra_charge ?? 0) : 0;
                            $totalPaymentAmount = $depositAmount + $rentalAmount + $additionalChargesTotal;
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $invoice->invoiceID ?? 'N/A' }}</strong>
                                <div class="invoice-info-text">
                                    Booking ID: #{{ $booking->bookingID }}
                                </div>
                            </td>
                            <td>
                                {{ $user->name ?? 'Unknown Customer' }}
                            </td>
                            <td>
                                {{-- FIX: Changed rental_start_date to start_date --}}
                                @if($booking->start_date)
                                    {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice)
                                    <a href="{{ route('invoices.generate', $booking->bookingID) }}" 
                                       class="btn btn-sm btn-danger" 
                                       target="_blank"
                                       title="View Invoice PDF">
                                        <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                    </a>
                                @else
                                    <span class="text-muted small">No invoice</span>
                                @endif
                            </td>
                            <td>
                                @if($vehicle)
                                    <strong>{{ $vehicle->plate_number ?? 'N/A' }}</strong>
                                    <div class="invoice-info-text">
                                        {{ ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '') }}
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong>RM {{ number_format($totalPaymentAmount, 2) }}</strong>
                                <div class="invoice-info-text">
                                    <div>Deposit: RM {{ number_format($depositAmount, 2) }}</div>
                                    <div>Rental: RM {{ number_format($rentalAmount, 2) }}</div>
                                    @if($additionalChargesTotal > 0)
                                        <div>Additional Charges: RM {{ number_format($additionalChargesTotal, 2) }}</div>
                                        @if($additionalCharges)
                                            <div class="mt-1">
                                                @if($additionalCharges->addOns_charge > 0)
                                                    <small>Add-ons: RM {{ number_format($additionalCharges->addOns_charge, 2) }}</small><br>
                                                @endif
                                                @if($additionalCharges->late_return_fee > 0)
                                                    <small>Late Return: RM {{ number_format($additionalCharges->late_return_fee, 2) }}</small><br>
                                                @endif
                                                @if($additionalCharges->damage_fee > 0)
                                                    <small>Damage: RM {{ number_format($additionalCharges->damage_fee, 2) }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No invoices available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($bookings->hasPages())
            <div class="p-3 border-top">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection