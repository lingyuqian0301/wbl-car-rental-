@extends('layouts.admin')

@section('title', 'Cancellations')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .cancellation-table {
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
    .btn-update-cancellation {
        padding: 4px 12px;
        font-size: 0.85rem;
    }
    .btn-email {
        padding: 4px 12px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Cancellations" 
        description="Manage cancelled bookings and refunds"
        :stats="[
            ['label' => 'Total Cancellations', 'value' => $totalCancellations, 'icon' => 'bi-x-circle'],
            ['label' => 'Pending Refunds', 'value' => $pendingRefunds, 'icon' => 'bi-clock'],
            ['label' => 'Completed Refunds', 'value' => $completedRefunds, 'icon' => 'bi-check-circle'],
            ['label' => 'Cancellations Today', 'value' => $cancellationsToday, 'icon' => 'bi-calendar-day']
        ]"
        :date="$today"
    />

    <!-- Search and Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.bookings.cancellation') }}" class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search (Booking ID / Customer Name)</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                       value="{{ $search }}" placeholder="Enter booking ID or customer name">
            </div>
            
            <!-- Sort -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Sort By</label>
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="date_desc" {{ $sortBy === 'date_desc' ? 'selected' : '' }}>Request Date (Desc)</option>
                    <option value="booking_asc" {{ $sortBy === 'booking_asc' ? 'selected' : '' }}>Booking ID (Asc)</option>
                </select>
            </div>
            
            <!-- Refund Status Filter -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Refund Status</label>
                <select name="refund_status" class="form-select form-select-sm" id="refund_status_filter">
                    <option value="">All Status</option>
                    <option value="request" {{ $refundStatus === 'request' ? 'selected' : '' }}>Request</option>
                    <option value="refunding" {{ $refundStatus === 'refunding' ? 'selected' : '' }}>Refunding</option>
                    <option value="cancelled" {{ $refundStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="rejected" {{ $refundStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <!-- Handled By Filter -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Handled By</label>
                <select name="handled_by" class="form-select form-select-sm" id="handled_by_filter">
                    <option value="">All</option>
                    <option value="unassigned" {{ $handledBy === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    @foreach($staffUsers as $staffUser)
                        <option value="{{ $staffUser->userID }}" {{ $handledBy == $staffUser->userID ? 'selected' : '' }}>
                            {{ $staffUser->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger btn-sm flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if($search || $refundStatus || $handledBy)
                    <a href="{{ route('admin.bookings.cancellation') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <br>

    <!-- Cancellations Table -->
    <div class="cancellation-table">
        <div class="table-header">
            <i class="bi bi-x-circle"></i> All Cancellations ({{ $cancellations->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Vehicle Plate</th>
                        <th>Payment Price</th>
                        <th>Account No</th>
                        <th>Account Type</th>
                        <th>Receipts</th>
                        <th>Refund Status</th>
                        <th>Handled By</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cancellations as $booking)
                        @php
                            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                            $payments = $booking->payments()->where('payment_status', 'Verified')->get();
                            $vehiclePlate = $booking->vehicle->plate_number ?? 'N/A';
                            $customerEmail = $booking->customer->user->email ?? null;
                            
                            // Map booking_status to display status
                            $statusDisplay = [
                                'request cancelling' => 'Request',
                                'refunding' => 'Refunding',
                                'Cancelled' => 'Cancelled',
                                'cancelled' => 'Cancelled',
                            ];
                            $currentStatus = $statusDisplay[$booking->booking_status] ?? $booking->booking_status;
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $booking->bookingID }}</strong>
                            </td>
                            <td>
                                @if($booking->lastUpdateDate)
                                    {{ \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                {{ $booking->customer->user->name ?? 'Unknown Customer' }}
                                <div class="small text-muted">{{ $customerEmail ?? '' }}</div>
                            </td>
                            <td>
                                <strong>{{ $vehiclePlate }}</strong>
                            </td>
                            <td>
                                <strong>RM {{ number_format($totalPaid, 2) }}</strong>
                                <div class="small text-muted">Total: RM {{ number_format($booking->total_price, 2) }}</div>
                            </td>
                            <td>
                                {{ $booking->customer->user->phone ?? 'N/A' }}
                            </td>
                            <td>
                                N/A
                            </td>
                            <td>
                                @if($payments->count() > 0)
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($payments as $payment)
                                            @if($payment->transaction_reference)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success btn-update-cancellation" 
                                                        onclick="showReceipt('{{ $payment->transaction_reference }}')">
                                                    <i class="bi bi-receipt"></i> Receipt {{ $loop->iteration }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">No receipts</span>
                                @endif
                            </td>
                            <td>
                                <select class="form-select form-select-sm refund-status-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        onchange="updateRefundStatus({{ $booking->bookingID }}, this.value)">
                                    <option value="request" {{ $booking->booking_status === 'request cancelling' ? 'selected' : '' }}>Request</option>
                                    <option value="refunding" {{ $booking->booking_status === 'refunding' ? 'selected' : '' }}>Refunding</option>
                                    <option value="cancelled" {{ in_array($booking->booking_status, ['Cancelled', 'cancelled']) ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ $booking->booking_status === 'Cancelled' && $currentStatus === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm handled-by-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        onchange="updateHandledBy({{ $booking->bookingID }}, this.value)">
                                    <option value="">Unassigned</option>
                                    @foreach($staffUsers as $staffUser)
                                        <option value="{{ $staffUser->userID }}" {{ $booking->staff_served == $staffUser->userID ? 'selected' : '' }}>
                                            {{ $staffUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                @if($customerEmail)
                                    <button type="button" 
                                            class="btn btn-sm btn-primary btn-email" 
                                            onclick="sendEmail({{ $booking->bookingID }})"
                                            title="Send email to {{ $customerEmail }}">
                                        <i class="bi bi-envelope"></i> Email
                                    </button>
                                @else
                                    <span class="text-muted small">No email</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No cancellations found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($cancellations->hasPages())
            <div class="p-3 border-top">
                {{ $cancellations->links() }}
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
                <p id="receiptText"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    function updateRefundStatus(bookingId, status) {
        fetch(`{{ url('/admin/bookings/cancellation') }}/${bookingId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                refund_status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update status');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
            location.reload();
        });
    }

    function updateHandledBy(bookingId, staffId) {
        fetch(`{{ url('/admin/bookings/cancellation') }}/${bookingId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                handled_by: staffId || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - no need to reload, just show a subtle notification
            } else {
                alert(data.message || 'Failed to update handled by');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating handled by.');
            location.reload();
        });
    }

    function sendEmail(bookingId) {
        if (!confirm('Send cancellation notification email to customer?')) {
            return;
        }

        fetch(`{{ url('/admin/bookings/cancellation') }}/${bookingId}/send-email`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email sent successfully!');
            } else {
                alert(data.message || 'Failed to send email');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending email.');
        });
    }

    function showReceipt(reference) {
        document.getElementById('receiptText').textContent = 'Receipt Reference: ' + reference;
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }
</script>
@endpush
@endsection
