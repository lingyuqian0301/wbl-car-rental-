@extends('layouts.staff')

@section('title', 'Reservations')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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
    .reservation-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table-header {
        background: var(--staff-orange);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-list-ul"></i> Reservations</h2>
            <p class="text-muted mb-0">Manage all booking reservations</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('staff.reservations.index') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Vehicle</label>
                <select name="vehicle_id" class="form-select form-select-sm">
                    <option value="all">All Vehicles</option>
                    @foreach($cars as $car)
                        <option value="car_{{ $car->vehicleID }}" {{ request('vehicle_id') === 'car_' . $car->vehicleID ? 'selected' : '' }}>
                            {{ $car->vehicle_brand }} {{ $car->vehicle_model }}
                        </option>
                    @endforeach
                    @foreach($motorcycles as $motorcycle)
                        <option value="motorcycle_{{ $motorcycle->vehicleID }}" {{ request('vehicle_id') === 'motorcycle_' . $motorcycle->vehicleID ? 'selected' : '' }}>
                            {{ $motorcycle->vehicle_brand }} {{ $motorcycle->vehicle_model }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold">Sort By</label>
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Latest Booking</option>
                    <option value="oldest" {{ $sortBy === 'oldest' ? 'selected' : '' }}>Oldest Booking</option>
                    <option value="start_date_asc" {{ $sortBy === 'start_date_asc' ? 'selected' : '' }}>Start Date (Asc)</option>
                    <option value="start_date_desc" {{ $sortBy === 'start_date_desc' ? 'selected' : '' }}>Start Date (Desc)</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-staff btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
            @if(request()->anyFilled(['date_from', 'date_to', 'vehicle_id', 'sort_by']))
            <div>
                <a href="{{ route('staff.reservations.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Reservations Table -->
    <div class="reservation-table">
        <div class="table-header">
            <i class="bi bi-calendar-check"></i> All Reservations
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Vehicle Booked</th>
                        <th>Plate No</th>
                        <th>Payment Price</th>
                        <th>Invoice</th>
                        <th>Payment Receipt</th>
                        <th>Payment Status</th>
                        <th>Pickup Detail</th>
                        <th>Return Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $vehicle = $booking->vehicle;
                            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                            $paymentStatus = $totalPaid >= $booking->total_price ? 'Fully Paid' : ($totalPaid > 0 ? 'Deposit Only' : 'Unpaid');
                            $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                        @endphp
                        <tr>
                            <td><strong>#{{ $booking->bookingID ?? $booking->id }}</strong></td>
                            <td>{{ $booking->user->name ?? 'Unknown Customer' }}</td>
                            <td>
                                @if($vehicle)
                                    {{ $vehicle->full_model ?? ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '') }}
                                @else
                                    <span class="text-muted">Vehicle not found</span>
                                @endif
                            </td>
                            <td>{{ $vehicle->plate_no ?? $vehicle->plate_number ?? 'N/A' }}</td>
                            <td>
                                <strong>RM {{ number_format($totalPaid, 2) }}</strong>
                                <div class="small text-muted">Total: RM {{ number_format($booking->total_price, 2) }}</div>
                            </td>
                            <td>
                                @if($totalPaid >= $booking->total_price)
                                    <a href="{{ route('staff.invoices.generate', $booking->bookingID ?? $booking->id) }}" class="btn btn-sm btn-staff" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i> View Invoice
                                    </a>
                                @else
                                    <span class="text-muted small">Payment pending</span>
                                @endif
                            </td>
                            <td>
                                @if($latestPayment && $latestPayment->proof_of_payment)
                                    <button onclick="showReceipt('{{ \Illuminate\Support\Facades\Storage::url($latestPayment->proof_of_payment) }}')" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-receipt"></i> View Receipt
                                    </button>
                                @else
                                    <span class="text-muted small">No receipt</span>
                                @endif
                            </td>
                            <td>
                                <select class="form-select form-select-sm payment-status-select"
                                        data-booking-id="{{ $booking->id }}"
                                        data-original-value="{{ $paymentStatus }}"
                                        style="min-width: 120px;">
                                    <option value="Unpaid" {{ $paymentStatus === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                    <option value="Deposit Only" {{ $paymentStatus === 'Deposit Only' ? 'selected' : '' }}>Deposit Only</option>
                                    <option value="Fully Paid" {{ $paymentStatus === 'Fully Paid' ? 'selected' : '' }}>Fully Paid</option>
                                </select>
                                <div class="small text-muted mt-1">{{ $latestPayment->payment_type ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="small">
                                    <strong>Date:</strong> {{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}<br>
                                    <strong>Time:</strong> {{ $booking->pickup_time ?? 'N/A' }}<br>
                                    <strong>Location:</strong> {{ $booking->pickup_location ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <strong>Date:</strong> {{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}<br>
                                    <strong>Time:</strong> {{ $booking->return_time ?? 'N/A' }}<br>
                                    <strong>Location:</strong> {{ $booking->return_location ?? 'N/A' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">No reservations found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
            <div class="p-3 border-top">{{ $bookings->links() }}</div>
        @endif
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="receiptImage" src="" alt="Receipt" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showReceipt(imageUrl) {
        document.getElementById('receiptImage').src = imageUrl;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }

    document.querySelectorAll('.payment-status-select').forEach(select => {
        select.dataset.originalValue = select.value;
        select.addEventListener('change', function() {
            const bookingId = this.dataset.bookingId;
            const newStatus = this.value;
            const originalValue = this.dataset.originalValue;
            if (confirm(`Update payment status display to "${newStatus}"?\n\nNote: This is a display status. Actual payment status is calculated from verified payments.`)) {
                this.dataset.originalValue = newStatus;
            } else {
                this.value = originalValue;
            }
        });
    });
</script>
@endpush
@endsection











