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
        grid-template-columns: repeat(3, 1fr);
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

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.bookings.cancellation') }}" class="filter-row">
            <div>
                <label class="form-label small fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
            </div>
            <div>
                <label class="form-label small fw-semibold">Refund Status</label>
                <select name="refund_status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Pending" {{ $refundStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Processing" {{ $refundStatus === 'Processing' ? 'selected' : '' }}>Processing</option>
                    <option value="Completed" {{ $refundStatus === 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Rejected" {{ $refundStatus === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
            </div>
            @if($dateFrom || $dateTo || $refundStatus)
            <div>
                <a href="{{ route('admin.cancellations.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
            @endif
        </form>
    </div>

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
                        <th>Payment Price</th>
                        <th>Account No</th>
                        <th>Account Type</th>
                        <th>Receipts</th>
                        <th>Refund Status</th>
                        <th>Handled By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cancellations as $booking)
                        @php
                            $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                            $payments = $booking->payments()->where('payment_status', 'Verified')->get();
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $booking->id }}</strong>
                            </td>
                            <td>
                                @if($booking->cancelled_at)
                                    @if($booking->cancelled_at instanceof \Carbon\Carbon)
                                        {{ $booking->cancelled_at->format('d M Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($booking->cancelled_at)->format('d M Y') }}
                                    @endif
                                @elseif($booking->updated_at)
                                    {{ \Carbon\Carbon::parse($booking->updated_at)->format('d M Y') }}
                                    <div class="small text-muted">(Updated)</div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                {{ $booking->user->name ?? 'Unknown Customer' }}
                                <div class="small text-muted">{{ $booking->user->email ?? '' }}</div>
                            </td>
                            <td>
                                <strong>RM {{ number_format($totalPaid, 2) }}</strong>
                                <div class="small text-muted">Total: RM {{ number_format($booking->total_price, 2) }}</div>
                            </td>
                            <td>
                                {{ $booking->user->account_no ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $booking->user->account_type ?? 'N/A' }}
                            </td>
                            <td>
                                @if($payments->count() > 0)
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($payments as $payment)
                                            @if($payment->proof_of_payment)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success btn-update-cancellation" 
                                                        onclick="showReceipt('{{ \Illuminate\Support\Facades\Storage::url($payment->proof_of_payment) }}')">
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
                                @if($booking->refund_status)
                                    <span class="badge badge-status {{ $booking->refund_status === 'Completed' ? 'bg-success' : ($booking->refund_status === 'Processing' ? 'bg-info' : ($booking->refund_status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark')) }}">
                                        {{ $booking->refund_status }}
                                    </span>
                                    @if($booking->refund_reason)
                                        <div class="small text-muted mt-1" title="{{ $booking->refund_reason }}">
                                            {{ Str::limit($booking->refund_reason, 30) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge badge-status bg-secondary">Not Set</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->refund_processed_by)
                                    <div>{{ $booking->refundProcessedByUser->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">
                                        @if($booking->refund_processed_at)
                                            {{ \Carbon\Carbon::parse($booking->refund_processed_at)->format('d M Y') }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">Not processed</span>
                                @endif
                                @if($booking->cancelled_by)
                                    <div class="small text-muted mt-1">
                                        Cancelled by: {{ $booking->cancelledByUser->name ?? 'Unknown' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-danger btn-update-cancellation" 
                                        onclick="openUpdateModal({{ $booking->id }}, '{{ $booking->refund_status ?? '' }}', '{{ addslashes($booking->refund_reason ?? '') }}')">
                                    <i class="bi bi-pencil"></i> Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
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

<!-- Update Cancellation Modal -->
<div class="modal fade" id="updateCancellationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Update Cancellation Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateCancellationForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Refund Status <span class="text-danger">*</span></label>
                        <select name="refund_status" id="refund_status" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="Processing">Processing</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason/Notes</label>
                        <textarea name="refund_reason" id="refund_reason" class="form-control" rows="3" placeholder="Enter reason for rejection or notes..."></textarea>
                        <small class="text-muted">Required if status is "Rejected"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Update Status</button>
                </div>
            </form>
        </div>
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
    function openUpdateModal(bookingId, currentStatus, currentReason) {
        const form = document.getElementById('updateCancellationForm');
        form.action = `{{ url('/admin/cancellations') }}/${bookingId}/update`;
        
        document.getElementById('refund_status').value = currentStatus || 'Pending';
        document.getElementById('refund_reason').value = currentReason || '';
        
        const modal = new bootstrap.Modal(document.getElementById('updateCancellationModal'));
        modal.show();
    }

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    function showReceipt(imageUrl) {
        document.getElementById('receiptImage').src = imageUrl;
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }

    // Form validation and AJAX submission
    document.getElementById('updateCancellationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const status = document.getElementById('refund_status').value;
        const reason = document.getElementById('refund_reason').value;
        
        if (status === 'Rejected' && !reason.trim()) {
            alert('Please provide a reason for rejection.');
            return false;
        }

        const form = this;
        const formData = new FormData(form);
        const url = form.action;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('updateCancellationModal')).hide();
                location.reload();
            } else {
                alert(data.message || 'An error occurred.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the cancellation status.');
        });
    });
</script>
@endpush
@endsection






