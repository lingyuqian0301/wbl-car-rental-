@extends('layouts.admin')

@section('title', 'Payment Verification')

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
        padding: 12px 16px;
        font-weight: 600;
    }
    /* Compact table styles */
    .payment-table .table {
        font-size: 0.75rem;
        table-layout: fixed;
        width: 100%;
    }
    .payment-table .table thead th {
        background: var(--admin-red-light);
        color: var(--admin-red-dark);
        font-weight: 600;
        border-bottom: 2px solid var(--admin-red);
        padding: 8px 6px;
        font-size: 0.7rem;
        white-space: normal;
        word-wrap: break-word;
        text-align: center;
        vertical-align: middle;
    }
    .payment-table .table tbody td {
        padding: 8px 6px;
        vertical-align: middle;
        font-size: 0.75rem;
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Column widths */
    .payment-table .table th:nth-child(1),
    .payment-table .table td:nth-child(1) { width: 6%; } /* Booking ID */
    .payment-table .table th:nth-child(2),
    .payment-table .table td:nth-child(2) { width: 10%; } /* Customer Name */
    .payment-table .table th:nth-child(3),
    .payment-table .table td:nth-child(3) { width: 10%; } /* Bank */
    .payment-table .table th:nth-child(4),
    .payment-table .table td:nth-child(4) { width: 7%; } /* Date */
    .payment-table .table th:nth-child(5),
    .payment-table .table td:nth-child(5) { width: 6%; } /* Type */
    .payment-table .table th:nth-child(6),
    .payment-table .table td:nth-child(6) { width: 7%; } /* Amount */
    .payment-table .table th:nth-child(7),
    .payment-table .table td:nth-child(7) { width: 7%; } /* Receipt */
    .payment-table .table th:nth-child(8),
    .payment-table .table td:nth-child(8) { width: 6%; } /* Is Complete */
    .payment-table .table th:nth-child(9),
    .payment-table .table td:nth-child(9) { width: 6%; } /* Status */
    .payment-table .table th:nth-child(10),
    .payment-table .table td:nth-child(10) { width: 7%; } /* Is Verify */
    .payment-table .table th:nth-child(11),
    .payment-table .table td:nth-child(11) { width: 8%; } /* Verified By */
    .payment-table .table th:nth-child(12),
    .payment-table .table td:nth-child(12) { width: 10%; } /* Invoice */
    
    .receipt-image {
        max-width: 50px;
        max-height: 50px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    .receipt-image:hover {
        opacity: 0.8;
    }
    .verify-dropdown {
        min-width: 80px;
        font-size: 0.7rem;
        padding: 4px 6px;
    }
    .verified-by-dropdown {
        min-width: 90px;
        font-size: 0.7rem;
        padding: 4px 6px;
    }
    .payment-table .badge {
        font-size: 0.65rem;
        padding: 3px 6px;
    }
    .payment-table .btn-sm {
        font-size: 0.65rem;
        padding: 3px 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Payment Verification" 
        description="Verify and manage all payment transactions"
        :stats="[
            ['label' => 'Total Payments', 'value' => $totalPayments, 'icon' => 'bi-credit-card'],
            ['label' => 'Pending', 'value' => $totalPending, 'icon' => 'bi-clock'],
            ['label' => 'Verified', 'value' => $totalVerified, 'icon' => 'bi-check-circle'],
            ['label' => 'Full Payment', 'value' => $totalFullPayment, 'icon' => 'bi-currency-dollar'],
            ['label' => 'Payments Today', 'value' => $totalToday, 'icon' => 'bi-calendar-day']
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Action Buttons - Right Top Corner -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-light text-danger" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <a href="{{ route('admin.payments.export-pdf', request()->query()) }}" class="btn btn-sm btn-light text-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('admin.payments.export-excel', request()->query()) }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" 
                           class="form-control form-control-sm" 
                           placeholder="Plate No">
                </div>
                
                <!-- Payment Date Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Date</label>
                    <input type="date" name="filter_payment_date" value="{{ $filterPaymentDate ?? '' }}" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Payment Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Status</label>
                    <select name="filter_payment_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="Full" {{ ($filterPaymentStatus ?? '') === 'Full' ? 'selected' : '' }}>Full</option>
                        <option value="Deposit" {{ ($filterPaymentStatus ?? '') === 'Deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="Balance" {{ ($filterPaymentStatus ?? '') === 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Verified" {{ ($filterPaymentStatus ?? '') === 'Verified' ? 'selected' : '' }}>Verified</option>
                        <option value="Pending" {{ ($filterPaymentStatus ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                
                <!-- Payment IsComplete Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment IsComplete</label>
                    <select name="filter_payment_iscomplete" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($filterPaymentIsComplete ?? '') == '1' ? 'selected' : '' }}>Complete</option>
                        <option value="0" {{ ($filterPaymentIsComplete ?? '') == '0' ? 'selected' : '' }}>Incomplete</option>
                    </select>
                </div>
                
                <!-- Payment IsVerify Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment IsVerify</label>
                    <select name="payment_isVerify" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($filterPaymentIsVerify ?? '') == '1' ? 'selected' : '' }}>Verified</option>
                        <option value="0" {{ ($filterPaymentIsVerify ?? '') == '0' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>
                
                <!-- Verified By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Verified By</label>
                    <select name="filter_verify_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($staffUsers ?? [] as $staff)
                            <option value="{{ $staff->userID }}" {{ ($filterVerifyBy ?? '') == $staff->userID ? 'selected' : '' }}>
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    @if($search || $filterPaymentDate || $filterPaymentStatus || $filterPaymentIsComplete || $filterPaymentIsVerify || $filterVerifyBy)
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="payment-table">
        <div class="table-header">
            <i class="bi bi-credit-card"></i> All Payments ({{ $payments->total() }})
        </div>
        <div style="overflow-x: auto;">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer Name</th>
                        <th>Bank</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                        <th>Complete</th>
                        <th>Status</th>
                        <th>Verify</th>
                        <th>Verified By</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php
                            $booking = $payment->booking;
                            $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                            $paidAmount = $payment->total_amount ?? 0;
                            $isFullPayment = $paidAmount >= $totalRequired;
                            
                            // Determine payment type
                            $depositAmount = $booking->deposit_amount ?? 0;
                            $paymentType = 'Balance';
                            if ($paidAmount <= $depositAmount && $depositAmount > 0) {
                                $paymentType = 'Deposit';
                            } elseif ($paidAmount >= $totalRequired) {
                                $paymentType = 'Full Payment';
                            }
                            
                            // Calculate total paid so far for this booking
                            $totalPaidSoFar = $booking->payments()
                                ->where('paymentID', '<=', $payment->paymentID)
                                ->where('payment_status', 'Verified')
                                ->sum('total_amount');
                            
                            // If this is the first payment and it's less than or equal to deposit, it's deposit
                            if ($totalPaidSoFar <= $depositAmount && $depositAmount > 0) {
                                $paymentType = 'Deposit';
                            } elseif ($totalPaidSoFar > $depositAmount && $totalPaidSoFar < $totalRequired) {
                                $paymentType = 'Balance';
                            }
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.reservations.show', ['booking' => $booking->bookingID, 'tab' => 'booking-detail']) }}" 
                                   class="text-decoration-none fw-bold text-primary"
                                   target="_blank">
                                    #{{ $booking->bookingID ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                {{ $booking->customer->user->name ?? 'N/A' }}
                            </td>
                            <td>
                                <div>{{ $payment->payment_bank_name ?? 'N/A' }}</div>
                                @if($payment->payment_bank_account_no)
                                    <div class="text-muted small">{{ $payment->payment_bank_account_no }}</div>
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_date)
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $paymentType === 'Deposit' ? 'bg-info' : ($paymentType === 'Balance' ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $paymentType }}
                                </span>
                            </td>
                            <td>
                                <strong>RM {{ number_format($payment->total_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    // Get receipt from proof_of_payment field
                                    $receiptPath = $payment->proof_of_payment ?? null;
                                    if ($receiptPath) {
                                        // Check if it's a file path (image or PDF)
                                        $isImagePath = str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/') || str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png') || str_contains($receiptPath, '.pdf');
                                        
                                        if ($isImagePath) {
                                            $imageUrl = getFileUrl($receiptPath);
                                        } else {
                                            $imageUrl = null;
                                        }
                                    } else {
                                        $imageUrl = null;
                                    }
                                @endphp
                                @if($imageUrl)
                                    <a href="{{ $imageUrl }}" target="_blank" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $payment->paymentID }}">
                                        <img src="{{ $imageUrl }}" alt="Receipt" class="receipt-image" title="Click to view full size" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                        <span class="text-muted small" style="display:none;">{{ $receiptPath }}</span>
                                    </a>
                                    <!-- Receipt Modal -->
                                    <div class="modal fade" id="receiptModal{{ $payment->paymentID }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Receipt - Payment #{{ $payment->paymentID }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    @if(str_contains(strtolower($receiptPath ?? ''), '.pdf'))
                                                        <iframe src="{{ $imageUrl }}" style="width: 100%; height: 600px; border: none;"></iframe>
                                                    @else
                                                        <img src="{{ $imageUrl }}" alt="Receipt" class="img-fluid" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ $imageUrl }}" target="_blank" class="btn btn-primary">
                                                        <i class="bi bi-download"></i> Open in New Tab
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">No receipt</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isComplete = $payment->isPayment_complete ?? false;
                                    // Auto-determine if full payment based on amount
                                    if (!$isComplete && $isFullPayment) {
                                        $isComplete = true;
                                    }
                                @endphp
                                <span class="badge {{ $isComplete ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $isComplete ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $status = $payment->payment_status ?? 'Pending';
                                    $statusClass = match($status) {
                                        'Verified', 'Full' => 'bg-success',
                                        'Pending' => 'bg-warning text-dark',
                                        'Rejected' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <select class="form-select form-select-sm verify-dropdown" 
                                        data-payment-id="{{ $payment->paymentID }}"
                                        data-field="payment_isVerify"
                                        onchange="updatePaymentVerify(this)">
                                    <option value="0" {{ ($payment->payment_isVerify ?? false) ? '' : 'selected' }}>False</option>
                                    <option value="1" {{ ($payment->payment_isVerify ?? false) ? 'selected' : '' }}>True</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-select form-select-sm verified-by-dropdown" 
                                        data-payment-id="{{ $payment->paymentID }}"
                                        data-field="verified_by"
                                        onchange="updateVerifiedBy(this)">
                                    <option value="">Not Set</option>
                                    @foreach($staffUsers ?? [] as $staff)
                                        <option value="{{ $staff->userID }}" {{ ($payment->verified_by ?? null) == $staff->userID ? 'selected' : '' }}>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                @php
                                    $booking = $payment->booking;
                                    $invoice = $booking->invoice ?? null;
                                @endphp
                                @if($payment->payment_isVerify || $payment->payment_status === 'Verified' || $payment->payment_status === 'Full')
                                    <a href="{{ route('admin.payments.invoice', $payment->paymentID) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-file-pdf"></i> Generate Invoice
                                    </a>
                                @else
                                    <span class="text-muted small">Verify first</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No payments available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($payments->hasPages())
            <div class="p-3 border-top">
                {{ $payments->links() }}
            </div>
        @endif
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
    // Update Payment isVerify via AJAX
    function updatePaymentVerify(selectElement) {
        const paymentId = selectElement.dataset.paymentId;
        const newValue = selectElement.value;
        const oldValue = selectElement.dataset.currentValue || (newValue === '1' ? '0' : '1');
        
        fetch(`{{ url('admin/payments') }}/${paymentId}/update-verify`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_isVerify: newValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectElement.dataset.currentValue = newValue;
                showNotification(data.message || 'Payment verification updated successfully.', true);
                // Optionally refresh the page to update other fields
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message || 'Failed to update payment verification.', false);
                selectElement.value = oldValue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating.', false);
            selectElement.value = oldValue;
        });
    }

    // Update Verified By via AJAX
    function updateVerifiedBy(selectElement) {
        const paymentId = selectElement.dataset.paymentId;
        const newValue = selectElement.value;
        const oldValue = selectElement.dataset.currentValue || '';
        
        fetch(`{{ url('admin/payments') }}/${paymentId}/update-verified-by`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                verified_by: newValue || null
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                selectElement.dataset.currentValue = newValue;
                showNotification(data.message || 'Verified by updated successfully.', true);
            } else {
                showNotification(data.message || 'Failed to update verified by.', false);
                selectElement.value = oldValue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating.', false);
            selectElement.value = oldValue;
        });
    }

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
</script>
@endpush
@endsection
