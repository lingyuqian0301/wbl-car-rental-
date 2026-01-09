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
    .receipt-image {
        max-width: 80px;
        max-height: 80px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }
    .receipt-image:hover {
        opacity: 0.8;
    }
    .verify-dropdown {
        min-width: 120px;
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
                    <select name="filter_payment_isverify" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" {{ ($filterPaymentIsVerify ?? '') == '1' ? 'selected' : '' }}>Verified</option>
                        <option value="0" {{ ($filterPaymentIsVerify ?? '') == '0' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>
                
                <!-- Verify By Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Verify By</label>
                    <select name="filter_verify_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($verifyByUsers ?? [] as $user)
                            <option value="{{ $user->userID }}" {{ ($filterVerifyBy ?? '') == $user->userID ? 'selected' : '' }}>{{ $user->name }}</option>
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
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Payment Bank Name</th>
                        <th>Payment Bank Account No</th>
                        <th>Payment Date</th>
                        <th>Payment Status</th>
                        <th>Payment Amount</th>
                        <th>Transaction Reference (Receipt)</th>
                        <th>Is Payment Complete</th>
                        <th>Payment is Verify</th>
                        <th>Generate Invoice</th>
                        <th>Verify By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        @php
                            $booking = $payment->booking;
                            $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                            $paidAmount = $payment->total_amount ?? 0;
                            $isFullPayment = $paidAmount >= $totalRequired;
                        @endphp
                        <tr>
                            <td>
                                <strong>#{{ $payment->paymentID }}</strong>
                            </td>
                            <td>
                                {{ $payment->payment_bank_name ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $payment->payment_bank_account_no ?? 'N/A' }}
                            </td>
                            <td>
                                @if($payment->payment_date)
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
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
                                <strong>RM {{ number_format($payment->total_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                @if($payment->transaction_reference)
                                    @php
                                        // Check if transaction_reference is a file path
                                        $receiptPath = $payment->transaction_reference;
                                        $isImagePath = str_contains($receiptPath, 'receipts/') || str_contains($receiptPath, 'uploads/') || str_contains($receiptPath, '.jpg') || str_contains($receiptPath, '.jpeg') || str_contains($receiptPath, '.png');
                                        
                                        if ($isImagePath) {
                                            if (str_starts_with($receiptPath, 'uploads/')) {
                                                $imageUrl = asset($receiptPath);
                                            } else {
                                                $imageUrl = asset('storage/' . $receiptPath);
                                            }
                                        } else {
                                            $imageUrl = null;
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <a href="{{ $imageUrl }}" target="_blank" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $payment->paymentID }}">
                                            <img src="{{ $imageUrl }}" alt="Receipt" class="receipt-image" title="Click to view full size" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                            <span class="text-muted small" style="display:none;">{{ $payment->transaction_reference }}</span>
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
                                                        <img src="{{ $imageUrl }}" alt="Receipt" class="img-fluid" onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small" title="Transaction Reference">{{ strlen($payment->transaction_reference) > 20 ? substr($payment->transaction_reference, 0, 20) . '...' : $payment->transaction_reference }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
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
                                <form action="{{ route('admin.payments.update-verify', $payment->paymentID) }}" method="POST" class="d-inline" id="verifyForm{{ $payment->paymentID }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="payment_isVerify" class="form-select form-select-sm verify-dropdown" onchange="this.form.submit();">
                                        <option value="0" {{ ($payment->payment_isVerify ?? false) ? '' : 'selected' }}>False</option>
                                        <option value="1" {{ ($payment->payment_isVerify ?? false) ? 'selected' : '' }}>True</option>
                                    </select>
                                </form>
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
                            <td>
                                <form action="{{ route('admin.payments.update-verify', $payment->paymentID) }}" method="POST" class="d-inline" id="verifyByForm{{ $payment->paymentID }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="payment_isVerify" value="{{ ($payment->payment_isVerify ?? false) ? '1' : '0' }}">
                                    <select name="verify_by" class="form-select form-select-sm verify-dropdown" onchange="document.getElementById('verifyByForm{{ $payment->paymentID }}').submit();" {{ !($payment->payment_isVerify ?? false) ? 'disabled' : '' }}>
                                        <option value="">Select Staff</option>
                                        @foreach($verifyByUsers ?? [] as $user)
                                            <option value="{{ $user->userID }}" {{ ($payment->verify_by ?? null) == $user->userID ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
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
@endsection
