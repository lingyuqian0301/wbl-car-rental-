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
        description="Manage deposit payments and refunds"
        :stats="[
            ['label' => 'Total Deposits', 'value' => $totalDeposits, 'icon' => 'bi-wallet'],
            ['label' => 'Total Amount', 'value' => 'RM ' . number_format($totalDepositAmount, 2), 'icon' => 'bi-currency-dollar'],
            ['label' => 'Refunded', 'value' => $refundedCount, 'icon' => 'bi-check-circle'],
            ['label' => 'No Action', 'value' => $noActionCount, 'icon' => 'bi-clock'],
            ['label' => 'Today', 'value' => $today->format('d M Y'), 'icon' => 'bi-calendar-day']
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

    <!-- Action Buttons -->
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
                        <option value="refunded" {{ ($filterRefundStatus ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="no_action" {{ ($filterRefundStatus ?? '') === 'no_action' ? 'selected' : '' }}>No Action</option>
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
                                <a href="{{ route('admin.deposits.show', $booking->bookingID) }}" class="btn btn-sm btn-outline-primary">
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
                            <td>
                                @if($booking->deposit_fine_amount)
                                    <strong>RM {{ number_format($booking->deposit_fine_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.deposits.show', $booking->bookingID) }}?tab=deposit-detail" class="text-muted text-decoration-none">-</a>
                            </td>
                            <td>
                                @if($booking->deposit_refund_amount)
                                    <strong>RM {{ number_format($booking->deposit_refund_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $refundStatus === 'refunded' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $refundStatus === 'refunded' ? 'Refunded' : 'No Action' }}
                                </span>
                            </td>
                            <td>
                                {{ $handledBy->name ?? 'N/A' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.deposits.show', $booking->bookingID) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
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
@endsection

