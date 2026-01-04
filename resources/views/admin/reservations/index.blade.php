@extends('layouts.admin')

@section('title', 'Reservations')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 25px;
    }
    .reservation-table {
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
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .btn-view-receipt {
        padding: 4px 12px;
        font-size: 0.85rem;
    }
    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Reservations" 
        description="Manage all booking reservations"
        :stats="[
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar'],
            ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
            ['label' => 'Confirmed', 'value' => $totalConfirmed, 'icon' => 'bi-check-circle'],
            ['label' => 'Bookings Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
        ]"
        :date="$today"
    />

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.bookings.reservations') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle</label>
                <select name="vehicle_id" class="form-select form-select-sm">
                    <option value="all" {{ $selectedVehicle === 'all' ? 'selected' : '' }}>All Vehicles</option>
                    <optgroup label="Cars">
                        @foreach($cars as $car)
                            <option value="car_{{ $car->vehicleID }}" {{ $selectedVehicle == 'car_' . $car->vehicleID ? 'selected' : '' }}>
                                {{ $car->full_model }} ({{ $car->plate_number ?? $car->plate_no ?? 'N/A' }})
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Motorcycles">
                        @foreach($motorcycles as $motorcycle)
                            <option value="motorcycle_{{ $motorcycle->id }}" {{ $selectedVehicle == 'motorcycle_' . $motorcycle->id ? 'selected' : '' }}>
                                {{ $motorcycle->full_model }} ({{ $motorcycle->plate_number ?? $motorcycle->plate_no ?? 'N/A' }})
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Sort By</label>
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Latest Booking</option>
                    <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Oldest Booking</option>
                    <option value="start_date_asc" {{ $sortBy === 'start_date_asc' ? 'selected' : '' }}>Start Date (Ascending)</option>
                    <option value="start_date_desc" {{ $sortBy === 'start_date_desc' ? 'selected' : '' }}>Start Date (Descending)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
            @if($dateFrom || $dateTo || $selectedVehicle !== 'all' || $sortBy !== 'latest')
            <div>
                <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Reservations Table -->
    <div class="reservation-table">
        <div class="table-header">
            <i class="bi bi-calendar-check"></i> All Reservations ({{ $bookings->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Vehicle</th>
                        <th>Plate No</th>
                        <th>Payment Price</th>
                        <th>Invoice</th>
                        <th>Payment Receipt</th>
                        <th>Payment Status</th>
                        <th>Pickup Detail</th>
                        <th>Return Detail</th>
                        <th>Booking Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $vehicle = $booking->vehicle;
                            $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                            $hasReceipt = $latestPayment && $latestPayment->proof_of_payment;
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $booking->id }}</strong>
                            </td>
                            <td>
                                {{ $booking->user->name ?? 'Unknown Customer' }}
                            </td>
                            <td>
                                @if($vehicle)
                                    <div>{{ $vehicle->full_model ?? ($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '') }}</div>
                                    <small class="text-muted">{{ $vehicle->vehicle_brand ?? '' }} {{ $vehicle->vehicle_model ?? '' }}</small>
                                @else
                                    <span class="text-muted">Vehicle not found</span>
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
                                @php
                                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                                @endphp
                                <strong>RM {{ number_format($totalPaid, 2) }}</strong>
                                <div class="small text-muted">Total: RM {{ number_format($booking->total_price, 2) }}</div>
                            </td>
                            <td>
                                <a href="{{ route('invoices.generate', $booking->bookingID) }}" 
                                   class="btn btn-sm btn-outline-primary btn-view-receipt" 
                                   target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                </a>
                            </td>
                            <td>
                                @if($hasReceipt)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-success btn-view-receipt" 
                                            onclick="showReceipt('{{ \Illuminate\Support\Facades\Storage::url($latestPayment->proof_of_payment) }}')">
                                        <i class="bi bi-receipt"></i> View Receipt
                                    </button>
                                @else
                                    <span class="text-muted small">No receipt</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                                    $paymentStatus = $totalPaid >= $booking->total_price ? 'Fully Paid' : ($totalPaid > 0 ? 'Deposit Only' : 'Unpaid');
                                @endphp
                                <select class="form-select form-select-sm payment-status-select" 
                                        data-booking-id="{{ $booking->id }}"
                                        data-original-value="{{ $paymentStatus }}"
                                        style="min-width: 120px;">
                                    <option value="Unpaid" {{ $paymentStatus === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="Deposit Only" {{ $paymentStatus === 'Deposit Only' ? 'selected' : '' }}>Deposit Only</option>
                                    <option value="Fully Paid" {{ $paymentStatus === 'Fully Paid' ? 'selected' : '' }}>Fully Paid</option>
                                </select>
                                <div class="small text-muted mt-1">
                                    {{ $latestPayment->payment_type ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div><strong>Date:</strong> 
                                        @if($booking->start_date)
                                            @if($booking->start_date instanceof \Carbon\Carbon)
                                                {{ $booking->start_date->format('d M Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    @if($booking->pickup_time)
                                        <div><strong>Time:</strong> {{ $booking->pickup_time }}</div>
                                    @endif
                                    @if($booking->pickup_location)
                                        <div><strong>Location:</strong> {{ $booking->pickup_location }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div><strong>Date:</strong> 
                                        @if($booking->end_date)
                                            @if($booking->end_date instanceof \Carbon\Carbon)
                                                {{ $booking->end_date->format('d M Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    @if($booking->return_time)
                                        <div><strong>Time:</strong> {{ $booking->return_time }}</div>
                                    @endif
                                    @if($booking->return_location)
                                        <div><strong>Location:</strong> {{ $booking->return_location }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-status {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                    {{ $booking->booking_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No reservations found</p>
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

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="receiptImage" src="" alt="Payment Receipt" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showReceipt(imageUrl) {
        document.getElementById('receiptImage').src = imageUrl;
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }

    // Handle payment status change
    document.querySelectorAll('.payment-status-select').forEach(select => {
        // Store original value on load
        select.dataset.originalValue = select.value;
        
        select.addEventListener('change', function() {
            const bookingId = this.dataset.bookingId;
            const newStatus = this.value;
            const originalValue = this.dataset.originalValue;
            
            // Show confirmation
            if (confirm(`Update payment status display to "${newStatus}"?\n\nNote: This is a display status. Actual payment status is calculated from verified payments.`)) {
                // Store the new value as original for next change
                this.dataset.originalValue = newStatus;
                
                // Optional: Make AJAX call to save preference if needed
                // fetch(`/admin/reservations/${bookingId}/update-payment-status`, {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                //     },
                //     body: JSON.stringify({ payment_status: newStatus })
                // });
            } else {
                // Revert selection
                this.value = originalValue;
            }
        });
    });
</script>
@endpush
@endsection
