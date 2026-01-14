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
                        @php
                            // Only show staffit and admin, exclude runner (already filtered in controller)
                            $isRunner = $staffUser->staff && $staffUser->staff->runner;
                        @endphp
                        @if(!$isRunner)
                            <option value="{{ $staffUser->userID }}" {{ $handledBy == $staffUser->userID ? 'selected' : '' }}>
                                {{ $staffUser->name }}
                            </option>
                        @endif
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
                        <th>Reject Reason</th>
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
                            $customer = $booking->customer;
                            
                            // Get account info from customer database
                            $accountNo = $customer->default_account_no ?? 'N/A';
                            $accountType = $customer->default_bank_name ?? 'N/A';
                            
                            // Check if booking is rejected (has cancellation_reject_reason and status is Cancelled)
                            $isRejected = $booking->booking_status === 'Cancelled' && !empty($booking->cancellation_reject_reason);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID]) }}" class="text-decoration-none fw-bold text-primary">
                                    #{{ $booking->bookingID }}
                                </a>
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
                                <div class="small text-muted">Paid amount</div>
                            </td>
                            <td>
                                {{ $accountNo }}
                            </td>
                            <td>
                                {{ $accountType }}
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($payments->count() > 0)
                                        @foreach($payments as $payment)
                                            @if($payment->proof_of_payment || $payment->transaction_reference)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success btn-update-cancellation" 
                                                        onclick="showReceipt('{{ $payment->proof_of_payment ?? $payment->transaction_reference }}', '{{ $payment->paymentID }}')">
                                                    <i class="bi bi-receipt"></i> Receipt {{ $loop->iteration }}
                                                </button>
                                            @endif
                                        @endforeach
                                    @endif
                                    @if($booking->cancellation_receipt)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary btn-update-cancellation" 
                                                onclick="showCancellationReceipt('{{ asset($booking->cancellation_receipt) }}', {{ $booking->bookingID }})">
                                            <i class="bi bi-file-earmark-image"></i> Refund Receipt
                                        </button>
                                    @else
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary btn-update-cancellation" 
                                                onclick="showUploadReceiptModal({{ $booking->bookingID }})">
                                            <i class="bi bi-cloud-upload"></i> Upload Receipt
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <select class="form-select form-select-sm refund-status-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        data-is-rejected="{{ $isRejected ? 'true' : 'false' }}"
                                        onchange="handleRefundStatusChange({{ $booking->bookingID }}, this.value, this)">
                                    <option value="request" {{ $booking->booking_status === 'request cancelling' ? 'selected' : '' }}>Request Cancel</option>
                                    <option value="refunding" {{ $booking->booking_status === 'refunding' ? 'selected' : '' }}>Refunding</option>
                                    <option value="cancelled" {{ in_array($booking->booking_status, ['Cancelled', 'cancelled']) && !$isRejected ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ $isRejected ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </td>
                            <td>
                                @if($booking->cancellation_reject_reason)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="showRejectReasonModal('{{ addslashes($booking->cancellation_reject_reason) }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <select class="form-select form-select-sm handled-by-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        onchange="updateHandledBy({{ $booking->bookingID }}, this.value)">
                                    <option value="">Unassigned</option>
                                    @foreach($staffUsers as $staffUser)
                                        @php
                                            // Only show staffit and admin, exclude runner (already filtered in controller)
                                            $isRunner = $staffUser->staff && $staffUser->staff->runner;
                                        @endphp
                                        @if(!$isRunner)
                                            <option value="{{ $staffUser->userID }}" {{ $booking->staff_served == $staffUser->userID ? 'selected' : '' }}>
                                                {{ $staffUser->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                @if($customerEmail)
                                    <a href="mailto:{{ $customerEmail }}?subject=Regarding Your Booking %23{{ $booking->bookingID }} Cancellation&body=Dear {{ $booking->customer->user->name ?? 'Customer' }},%0D%0A%0D%0AThis is regarding your booking %23{{ $booking->bookingID }} cancellation request.%0D%0A%0D%0ABooking Details:%0D%0A- Booking ID: %23{{ $booking->bookingID }}%0D%0A- Vehicle: {{ $vehiclePlate }}%0D%0A- Amount Paid: RM {{ number_format($totalPaid, 2) }}%0D%0A%0D%0A%0D%0ABest Regards,%0D%0AHASTA Travel %26 Tours Team" 
                                       class="btn btn-sm btn-primary btn-email"
                                       title="Draft email to {{ $customerEmail }}">
                                        <i class="bi bi-envelope"></i> Email
                                    </a>
                                @else
                                    <span class="text-muted small">No email</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i> Payment Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="receiptContent">
                    <!-- Receipt image or content will be loaded here -->
                </div>
                <p id="receiptPaymentInfo" class="mt-3 text-muted small mb-0"></p>
            </div>
            <div class="modal-footer">
                <a href="#" id="receiptDownloadLink" class="btn btn-outline-success" target="_blank" style="display: none;">
                    <i class="bi bi-download me-1"></i> Open in New Tab
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Reject Reason</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rejectReasonContent">
                    <!-- Reject reason will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Input Modal -->
<div class="modal fade" id="rejectReasonInputModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i> Reject Cancellation Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectReasonForm" onsubmit="submitRejectReason(event)">
                <div class="modal-body">
                    <input type="hidden" id="rejectBookingId" name="booking_id">
                    <div class="mb-3">
                        <label for="rejectReasonText" class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReasonText" name="reject_reason" rows="5" required placeholder="Please enter the reason for rejecting this cancellation request..."></textarea>
                        <small class="text-muted">This reason will be sent to the customer via email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Receipt Modal -->
<div class="modal fade" id="uploadReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i> Upload Refund Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadReceiptForm" enctype="multipart/form-data" onsubmit="submitReceiptUpload(event)">
                <div class="modal-body">
                    <input type="hidden" id="uploadBookingId" name="booking_id">
                    <div class="mb-3">
                        <label for="receiptFile" class="form-label fw-semibold">Receipt Image/PDF <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="receiptFile" name="receipt" accept="image/*,application/pdf" required>
                        <small class="text-muted">Accepted formats: JPEG, PNG, PDF (Max: 10MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Notification Toast (same style as booking detail page) -->
<div id="notificationToast" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; display: none;">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" id="toastHeader">
            <i class="bi bi-check-circle me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" onclick="hideNotification()"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Message here
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // Show notification (same style as booking detail page)
    function showNotification(message, isSuccess = true) {
        const toast = document.getElementById('notificationToast');
        const header = document.getElementById('toastHeader');
        const icon = document.getElementById('toastIcon');
        const title = document.getElementById('toastTitle');
        const body = document.getElementById('toastMessage');

        header.className = 'toast-header ' + (isSuccess ? 'bg-success text-white' : 'bg-danger text-white');
        icon.className = 'bi me-2 ' + (isSuccess ? 'bi-check-circle' : 'bi-x-circle');
        title.textContent = isSuccess ? 'Success' : 'Error';
        body.textContent = message;
        toast.style.display = 'block';

        setTimeout(() => {
            hideNotification();
        }, 4000);
    }

    function hideNotification() {
        document.getElementById('notificationToast').style.display = 'none';
    }

    function handleRefundStatusChange(bookingId, status, selectElement) {
        if (status === 'rejected') {
            // Show reject reason input modal
            document.getElementById('rejectBookingId').value = bookingId;
            document.getElementById('rejectReasonText').value = '';
            const modal = new bootstrap.Modal(document.getElementById('rejectReasonInputModal'));
            modal.show();
            
            // Store original value for cancellation
            selectElement.dataset.pendingReject = 'true';
            selectElement.dataset.originalValue = selectElement.value;
            
            // Reset select to original value (will be updated after modal submission)
            selectElement.value = selectElement.dataset.originalValue || 'request';
        } else {
            // For other statuses, update directly
            updateRefundStatus(bookingId, status, selectElement);
        }
    }

    function updateRefundStatus(bookingId, status, selectElement = null) {
        if (!selectElement) {
            selectElement = document.querySelector(`.refund-status-select[data-booking-id="${bookingId}"]`);
        }
        const originalValue = selectElement.dataset.originalValue || selectElement.value;
        
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
                showNotification('Refund status updated successfully.', true);
                selectElement.dataset.originalValue = status;
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to update refund status.', false);
                selectElement.value = originalValue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating the status.', false);
            selectElement.value = originalValue;
        });
    }

    function submitRejectReason(event) {
        event.preventDefault();
        const bookingId = document.getElementById('rejectBookingId').value;
        const reason = document.getElementById('rejectReasonText').value.trim();
        
        if (!reason) {
            showNotification('Please enter a rejection reason.', false);
            return;
        }

        const selectElement = document.querySelector(`.refund-status-select[data-booking-id="${bookingId}"]`);
        
        fetch(`{{ url('/admin/bookings/cancellation') }}/${bookingId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                refund_status: 'rejected',
                cancellation_reject_reason: reason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('rejectReasonInputModal'));
                modal.hide();
                showNotification('Cancellation request rejected. Email sent to customer.', true);
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to reject cancellation request.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while rejecting the cancellation request.', false);
        });
    }

    function showRejectReasonModal(reason) {
        document.getElementById('rejectReasonContent').innerHTML = `
            <div class="alert alert-warning">
                <strong>Rejection Reason:</strong>
                <p class="mb-0 mt-2" style="white-space: pre-wrap;">${reason}</p>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
        modal.show();
    }

    function showUploadReceiptModal(bookingId) {
        document.getElementById('uploadBookingId').value = bookingId;
        document.getElementById('receiptFile').value = '';
        const modal = new bootstrap.Modal(document.getElementById('uploadReceiptModal'));
        modal.show();
    }

    function submitReceiptUpload(event) {
        event.preventDefault();
        const bookingId = document.getElementById('uploadBookingId').value;
        const formData = new FormData();
        formData.append('receipt', document.getElementById('receiptFile').files[0]);
        formData.append('_token', csrfToken);

        fetch(`{{ url('/admin/bookings/cancellation') }}/${bookingId}/upload-receipt`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadReceiptModal'));
                modal.hide();
                showNotification('Receipt uploaded successfully.', true);
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to upload receipt.', false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while uploading the receipt.', false);
        });
    }

    function showCancellationReceipt(receiptUrl, bookingId) {
        const receiptContent = document.getElementById('receiptContent');
        const receiptPaymentInfo = document.getElementById('receiptPaymentInfo');
        const downloadLink = document.getElementById('receiptDownloadLink');
        
        receiptContent.innerHTML = '';
        receiptPaymentInfo.textContent = `Booking ID: #${bookingId} - Refund Receipt`;
        downloadLink.style.display = 'none';
        
        if (receiptUrl.match(/\.pdf$/i)) {
            receiptContent.innerHTML = `
                <div class="py-4">
                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                    <p class="mt-3">PDF Receipt</p>
                    <a href="${receiptUrl}" target="_blank" class="btn btn-danger">
                        <i class="bi bi-eye me-1"></i> View PDF
                    </a>
                </div>
            `;
            downloadLink.href = receiptUrl;
            downloadLink.style.display = 'inline-block';
        } else {
            receiptContent.innerHTML = `
                <div class="position-relative">
                    <div class="spinner-border text-primary mb-3" role="status" id="receiptSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <img src="${receiptUrl}" 
                         alt="Refund Receipt" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 70vh; object-fit: contain; display: none;"
                         onload="this.style.display='block'; document.getElementById('receiptSpinner').style.display='none';"
                         onerror="this.style.display='none'; document.getElementById('receiptSpinner').style.display='none'; document.getElementById('receiptContent').innerHTML='<div class=\\'py-4\\'><i class=\\'bi bi-exclamation-triangle text-warning\\' style=\\'font-size: 4rem;\\'></i><p class=\\'mt-3 text-muted\\'>Failed to load image</p><a href=\\'' + '${receiptUrl}' + '\\' target=\\'_blank\\' class=\\'btn btn-sm btn-outline-primary mt-2\\'>Open Link</a></div>';">
                </div>
            `;
            downloadLink.href = receiptUrl;
            downloadLink.style.display = 'inline-block';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }

    function updateHandledBy(bookingId, staffId) {
        const selectElement = document.querySelector(`.handled-by-select[data-booking-id="${bookingId}"]`);
        const originalValue = selectElement.dataset.originalValue || selectElement.value;
        
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
                showNotification('Handled by updated successfully.', true);
                selectElement.dataset.originalValue = staffId;
            } else {
                showNotification(data.message || 'Failed to update handled by.', false);
                selectElement.value = originalValue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating handled by.', false);
            selectElement.value = originalValue;
        });
    }

    function showReceipt(proofOfPayment, paymentId) {
        const receiptContent = document.getElementById('receiptContent');
        const receiptPaymentInfo = document.getElementById('receiptPaymentInfo');
        const downloadLink = document.getElementById('receiptDownloadLink');
        
        // Reset content
        receiptContent.innerHTML = '';
        receiptPaymentInfo.textContent = `Payment ID: #${paymentId}`;
        downloadLink.style.display = 'none';
        
        if (!proofOfPayment) {
            receiptContent.innerHTML = `
                <div class="py-4">
                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                    <p class="mt-3 text-muted">No receipt image available</p>
                </div>
            `;
        } else if (proofOfPayment.match(/\.(jpeg|jpg|png|gif|webp|bmp)$/i) || 
                   proofOfPayment.startsWith('http') || 
                   proofOfPayment.startsWith('/') ||
                   proofOfPayment.includes('drive.google.com')) {
            // It's an image URL - display it
            receiptContent.innerHTML = `
                <div class="position-relative">
                    <div class="spinner-border text-success mb-3" role="status" id="receiptSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <img src="${proofOfPayment}" 
                         alt="Proof of Payment" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 70vh; object-fit: contain; display: none;"
                         onload="this.style.display='block'; document.getElementById('receiptSpinner').style.display='none';"
                         onerror="this.style.display='none'; document.getElementById('receiptSpinner').style.display='none'; document.getElementById('receiptContent').innerHTML='<div class=\\'py-4\\'><i class=\\'bi bi-exclamation-triangle text-warning\\' style=\\'font-size: 4rem;\\'></i><p class=\\'mt-3 text-muted\\'>Failed to load image</p><a href=\\'' + '${proofOfPayment}' + '\\' target=\\'_blank\\' class=\\'btn btn-sm btn-outline-primary mt-2\\'>Open Link</a></div>';">
                </div>
            `;
            downloadLink.href = proofOfPayment;
            downloadLink.style.display = 'inline-block';
        } else if (proofOfPayment.match(/\.pdf$/i)) {
            // It's a PDF
            receiptContent.innerHTML = `
                <div class="py-4">
                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                    <p class="mt-3">PDF Receipt</p>
                    <a href="${proofOfPayment}" target="_blank" class="btn btn-danger">
                        <i class="bi bi-eye me-1"></i> View PDF
                    </a>
                </div>
            `;
            downloadLink.href = proofOfPayment;
            downloadLink.style.display = 'inline-block';
        } else {
            // Text reference
            receiptContent.innerHTML = `
                <div class="py-4">
                    <i class="bi bi-file-text text-secondary" style="font-size: 4rem;"></i>
                    <p class="mt-3">Transaction Reference:</p>
                    <code class="bg-light p-2 rounded d-inline-block">${proofOfPayment}</code>
                </div>
            `;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    }
</script>
@endpush
@endsection
