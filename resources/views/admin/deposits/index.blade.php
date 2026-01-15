@extends('layouts.admin')

@section('title', 'Deposit Management')

@push('styles')
<style>
    .payment-table {
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
        white-space: nowrap;
    }
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Deposit Management" 
        description="Manage deposit return requests"
        :stats="[
            ['label' => 'Deposit Hold', 'value' => 'RM ' . number_format($depositHold ?? 0, 2), 'icon' => 'bi-wallet'],
            ['label' => 'Deposit Not Yet Process', 'value' => $depositNotYetProcess ?? 0, 'icon' => 'bi-clock-history']
        ]"
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

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light text-danger" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.deposits.export-pdf', request()->query()) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.deposits.export-excel', request()->query()) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.deposits.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" 
                           class="form-control form-control-sm" 
                           placeholder="Booking ID, Customer Name, Plate No">
                </div>
                
                <!-- Refund Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Refund Status</label>
                    <select name="filter_refund_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ ($filterRefundStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="hold" {{ ($filterRefundStatus ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                        <option value="refunded" {{ ($filterRefundStatus ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                
                <!-- Customer Choice Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Choice</label>
                    <select name="filter_customer_choice" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="hold" {{ ($filterCustomerChoice ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                        <option value="refund" {{ ($filterCustomerChoice ?? '') === 'refund' ? 'selected' : '' }}>Refund</option>
                    </select>
                </div>
                
                <!-- Handled By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Handled By</label>
                    <select name="filter_handled_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($staffUsers as $user)
                            <option value="{{ $user->userID }}" {{ ($filterHandledBy ?? '') == $user->userID ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    @if($search || $filterRefundStatus || $filterHandledBy || $filterCustomerChoice)
                        <a href="{{ route('admin.deposits.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="payment-table">
        <div class="table-header">
            <i class="bi bi-wallet"></i> All Deposits ({{ $bookings->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer Name</th>
                        <th>Deposit Payment</th>
                        <th>Vehicle Condition Form</th>
                        <th>Customer Choice</th>
                        <th>Fine Amount</th>
                        <th>Originally</th>
                        <th>Refund Amount</th>
                        <th>Refund Status</th>
                        <th>Handled By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $customer = $booking->customer;
                            $user = $customer->user ?? null;
                            $refundStatus = $booking->deposit_refund_status ?? 'no_action';
                            $handledBy = $booking->deposit_handled_by ? \App\Models\User::find($booking->deposit_handled_by) : null;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID]) }}" class="text-decoration-none fw-bold text-primary">
                                    #{{ $booking->bookingID }}
                                </a>
                            </td>
                            <td>
                                {{ $user->name ?? 'N/A' }}
                            </td>
                            <td>
                                <strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('admin.bookings.reservations.show', $booking->bookingID) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark-text"></i> View Form
                                </a>
                            </td>
                            <td>
                                @if($booking->deposit_customer_choice)
                                    <span class="badge {{ $booking->deposit_customer_choice === 'hold' ? 'bg-info' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($booking->deposit_customer_choice) }}
                                    </span>
                                    <div class="small text-muted mt-1">
                                        {{ $booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y H:i') : 'N/A' }}
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td id="fine-amount-display-{{ $booking->bookingID }}">
                                @if($booking->deposit_fine_amount && $booking->deposit_fine_amount > 0)
                                    <strong class="text-danger">RM {{ number_format($booking->deposit_fine_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong>
                            </td>
                            <td id="refund-amount-display-{{ $booking->bookingID }}">
                                @if($booking->deposit_refund_amount && $booking->deposit_refund_amount > 0)
                                    <strong class="text-success">RM {{ number_format($booking->deposit_refund_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    // Determine current status for dropdown
                                    $currentStatus = 'pending';
                                    if ($booking->deposit_customer_choice === 'hold') {
                                        $currentStatus = 'hold';
                                    } elseif ($booking->deposit_refund_status === 'refunded') {
                                        $currentStatus = 'refunded';
                                    } elseif ($booking->deposit_refund_status === 'pending' || ($booking->deposit_customer_choice === 'refund' && !$booking->deposit_refund_status)) {
                                        $currentStatus = 'pending';
                                    }
                                @endphp
                                <select class="form-select form-select-sm refund-status-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        onchange="updateRefundStatus(this, {{ $booking->bookingID }})">
                                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="hold" {{ $currentStatus === 'hold' ? 'selected' : '' }}>Hold</option>
                                    <option value="refunded" {{ $currentStatus === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm handled-by-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        onchange="updateHandledBy(this, {{ $booking->bookingID }})">
                                    <option value="">Not Assigned</option>
                                    @foreach($staffUsers as $user)
                                        <option value="{{ $user->userID }}" {{ $handledBy && $handledBy->userID == $user->userID ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- Edit Fine Amount Button -->
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editFineAmountModal{{ $booking->bookingID }}" title="Edit Fine Amount">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    
                                    @php
                                        $receiptPath = $booking->deposit_refund_receipt ?? null;
                                        $hasReceipt = $receiptPath && (str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf') || str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/'));
                                    @endphp
                                    @if($hasReceipt)
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#viewDepositReceiptModal{{ $booking->bookingID }}" title="View Receipt">
                                            <i class="bi bi-receipt"></i> View
                                        </button>
                                        <!-- View Receipt Modal -->
                                        <div class="modal fade" id="viewDepositReceiptModal{{ $booking->bookingID }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>Deposit Refund Receipt - Booking #{{ $booking->bookingID }}</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center p-4">
                                                        @if(str_contains(strtolower($receiptPath ?? ''), '.pdf'))
                                                            <iframe src="{{ getFileUrl($receiptPath) }}" style="width: 100%; height: 500px; border: none;"></iframe>
                                                        @else
                                                            <img src="{{ getFileUrl($receiptPath) }}" alt="Receipt" class="img-fluid rounded" style="max-height: 70vh;">
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ getFileUrl($receiptPath) }}" target="_blank" class="btn btn-primary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i>Open
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadDepositReceiptModal{{ $booking->bookingID }}" title="Upload Receipt">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                    @endif
                                </div>
                                
                                <!-- Edit Fine Amount Modal -->
                                <div class="modal fade" id="editFineAmountModal{{ $booking->bookingID }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Fine Amount - Booking #{{ $booking->bookingID }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form id="editFineAmountForm{{ $booking->bookingID }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Original Deposit Amount</label>
                                                        <input type="text" class="form-control" id="depositAmount{{ $booking->bookingID }}" value="RM {{ number_format($booking->deposit_amount ?? 0, 2) }}" readonly style="background-color: #f8f9fa;" data-deposit-amount="{{ $booking->deposit_amount ?? 0 }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="fineAmount{{ $booking->bookingID }}" class="form-label">Fine Amount (RM) <span class="text-danger">*</span></label>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="fineAmount{{ $booking->bookingID }}" 
                                                               name="deposit_fine_amount" 
                                                               step="0.01" 
                                                               min="0" 
                                                               max="{{ $booking->deposit_amount ?? 0 }}"
                                                               value="{{ $booking->deposit_fine_amount ?? 0 }}"
                                                               required
                                                               oninput="calculateRefundAmount({{ $booking->bookingID }}, {{ $booking->deposit_amount ?? 0 }})">
                                                        <div class="form-text">Enter the fine amount to be deducted from deposit. Refund amount will be auto-calculated.</div>
                                                    </div>
                                                                <div class="mb-3">
                                                                    <label for="refundAmount{{ $booking->bookingID }}" class="form-label">Refund Amount (RM)</label>
                                                                    <input type="number" 
                                                                           class="form-control" 
                                                                           id="refundAmount{{ $booking->bookingID }}" 
                                                                           name="deposit_refund_amount" 
                                                                           step="0.01" 
                                                                           min="0"
                                                                           max="{{ $booking->deposit_amount ?? 0 }}"
                                                                           value="{{ $booking->deposit_refund_amount ?? ($booking->deposit_amount ?? 0) }}"
                                                                           readonly
                                                                           style="background-color: #e7f3ff; font-weight: 600;">
                                                                    <div class="form-text text-success">Auto-calculated: Deposit Amount - Fine Amount</div>
                                                                </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-save me-1"></i>Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Upload Receipt Modal -->
                                <div class="modal fade" id="uploadDepositReceiptModal{{ $booking->bookingID }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Upload Deposit Refund Receipt</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form id="uploadDepositReceiptForm{{ $booking->bookingID }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="depositReceiptFile{{ $booking->bookingID }}" class="form-label">Select Receipt (JPG, PNG, PDF - Max 10MB)</label>
                                                        <input type="file" class="form-control" id="depositReceiptFile{{ $booking->bookingID }}" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required>
                                                        <div class="form-text">Upload the refund receipt image or PDF.</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="bi bi-upload me-1"></i>Upload Receipt
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No deposits found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-calculate refund amount based on fine amount
function calculateRefundAmount(bookingID, depositAmount) {
    const fineAmountInput = document.getElementById('fineAmount' + bookingID);
    const refundAmountInput = document.getElementById('refundAmount' + bookingID);
    
    if (fineAmountInput && refundAmountInput) {
        const fineAmount = parseFloat(fineAmountInput.value) || 0;
        const refundAmount = Math.max(0, depositAmount - fineAmount);
        refundAmountInput.value = refundAmount.toFixed(2);
    }
}

// Handle edit fine amount form submission
document.addEventListener('DOMContentLoaded', function() {
    const editForms = document.querySelectorAll('[id^="editFineAmountForm"]');
    editForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const bookingId = this.id.replace('editFineAmountForm', '');
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            const modalId = 'editFineAmountModal' + bookingId;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            
            // Get deposit amount from the form's data attribute
            const depositAmountField = document.getElementById('depositAmount' + bookingId);
            const depositAmount = parseFloat(depositAmountField?.getAttribute('data-deposit-amount') || depositAmountField?.value.replace(/[^0-9.]/g, '') || 0);
            const fineAmount = parseFloat(formData.get('deposit_fine_amount')) || 0;
            
            // Auto-calculate refund amount: deposit - fine
            const refundAmount = Math.max(0, depositAmount - fineAmount);
            
            // Convert FormData to JSON - refund amount is auto-calculated
            const data = {
                deposit_fine_amount: fineAmount,
                deposit_refund_amount: refundAmount
            };
            
            fetch(`{{ route('admin.deposits.update-fine-amount-ajax', ':id') }}`.replace(':id', bookingId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update fine amount display
                    const fineAmountDisplay = document.getElementById('fine-amount-display-' + bookingId);
                    if (fineAmountDisplay) {
                        if (fineAmount > 0) {
                            fineAmountDisplay.innerHTML = '<strong class="text-danger">RM ' + fineAmount.toFixed(2) + '</strong>';
                        } else {
                            fineAmountDisplay.innerHTML = '<span class="text-muted">-</span>';
                        }
                    }
                    
                    // Update refund amount display
                    const refundAmountDisplay = document.getElementById('refund-amount-display-' + bookingId);
                    if (refundAmountDisplay) {
                        if (refundAmount > 0) {
                            refundAmountDisplay.innerHTML = '<strong class="text-success">RM ' + refundAmount.toFixed(2) + '</strong>';
                        } else {
                            refundAmountDisplay.innerHTML = '<span class="text-muted">-</span>';
                        }
                    }
                    
                    // Show success message
                    alert('Fine amount and refund amount updated successfully.');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modal) {
                        modal.hide();
                    }
                } else {
                    alert(data.message || 'Failed to update fine amount.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating fine amount.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });
});

function updateRefundStatus(select, bookingID) {
    const status = select.value;
    const originalValue = select.getAttribute('data-original-value') || select.selectedOptions[0].text;
    
    // Store original value if not already stored
    if (!select.getAttribute('data-original-value')) {
        select.setAttribute('data-original-value', originalValue);
    }
    
    // Disable select during update
    select.disabled = true;
    
    fetch(`{{ route('admin.deposits.update-status-ajax', ':id') }}`.replace(':id', bookingID), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            deposit_refund_status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        select.disabled = false;
        if (data.success) {
            // Optionally show success message
            select.setAttribute('data-original-value', status);
        } else {
            // Revert on error
            select.value = select.getAttribute('data-original-value');
            alert('Failed to update refund status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        select.disabled = false;
        select.value = select.getAttribute('data-original-value');
        console.error('Error:', error);
        alert('An error occurred while updating refund status');
    });
}

function updateHandledBy(select, bookingID) {
    const handledBy = select.value;
    const originalValue = select.getAttribute('data-original-value') || select.value;
    
    // Store original value if not already stored
    if (!select.getAttribute('data-original-value')) {
        select.setAttribute('data-original-value', originalValue);
    }
    
    // Disable select during update
    select.disabled = true;
    
    fetch(`{{ route('admin.deposits.update-handled-by-ajax', ':id') }}`.replace(':id', bookingID), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            deposit_handled_by: handledBy || null
        })
    })
    .then(response => response.json())
    .then(data => {
        select.disabled = false;
        if (data.success) {
            select.setAttribute('data-original-value', handledBy);
        } else {
            select.value = select.getAttribute('data-original-value');
            alert('Failed to update handled by: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        select.disabled = false;
        select.value = select.getAttribute('data-original-value');
        console.error('Error:', error);
        alert('An error occurred while updating handled by');
    });
}

// Handle deposit refund receipt upload
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for all upload receipt forms
    const uploadForms = document.querySelectorAll('[id^="uploadDepositReceiptForm"]');
    uploadForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const bookingId = this.id.replace('uploadDepositReceiptForm', '');
            const modalId = 'uploadDepositReceiptModal' + bookingId;
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
            
            fetch(`{{ route('admin.deposits.upload-receipt', ':id') }}`.replace(':id', bookingId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Receipt uploaded successfully.');
                    // Close modal and reload page to show new receipt
                    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modal) {
                        modal.hide();
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to upload receipt.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading receipt.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });
});
</script>
@endpush
@endsection

