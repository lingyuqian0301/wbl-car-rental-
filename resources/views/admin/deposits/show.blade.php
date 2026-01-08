@extends('layouts.admin')

@section('title', 'Refund Detail')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Refund Detail</h1>
            <div class="text-muted small">
                Booking ID: #{{ $booking->bookingID }} · Customer: {{ $booking->customer->user->name ?? 'N/A' }}
            </div>
        </div>
        <a href="{{ route('admin.deposits.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Deposits
        </a>
    </div>

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

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ (request()->get('tab') ?? 'pickup-condition') === 'pickup-condition' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#pickup-condition" type="button" role="tab">
                <i class="bi bi-calendar-check"></i> Pickup Condition Form
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request()->get('tab') === 'return-condition' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#return-condition" type="button" role="tab">
                <i class="bi bi-calendar-x"></i> Return Condition Form
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request()->get('tab') === 'refund-detail' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#refund-detail" type="button" role="tab">
                <i class="bi bi-cash-stack"></i> Refund Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request()->get('tab') === 'deposit-detail' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#deposit-detail" type="button" role="tab">
                <i class="bi bi-wallet"></i> Deposit Detail
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Pickup Condition Form Tab -->
        <div class="tab-pane fade {{ (request()->get('tab') ?? 'pickup-condition') === 'pickup-condition' ? 'show active' : '' }}" id="pickup-condition" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Pickup Condition Form</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pickup condition form will be displayed here when available.</p>
                    <!-- Add form details display when form model is available -->
                </div>
            </div>
        </div>

        <!-- Return Condition Form Tab -->
        <div class="tab-pane fade {{ request()->get('tab') === 'return-condition' ? 'show active' : '' }}" id="return-condition" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-x"></i> Return Condition Form</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Return condition form will be displayed here when available.</p>
                    <!-- Add form details display when form model is available -->
                </div>
            </div>
        </div>

        <!-- Refund Detail Tab -->
        <div class="tab-pane fade {{ request()->get('tab') === 'refund-detail' ? 'show active' : '' }}" id="refund-detail" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Refund Detail</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.deposits.update', $booking->bookingID) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Choice</label>
                                <select name="customer_choice" class="form-select">
                                    <option value="">Select Choice</option>
                                    <option value="hold" {{ ($booking->deposit_customer_choice ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                                    <option value="refund" {{ ($booking->deposit_customer_choice ?? '') === 'refund' ? 'selected' : '' }}>Refund</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Fine Amount (RM)</label>
                                <input type="number" name="fine_amount" step="0.01" min="0" 
                                       class="form-control" 
                                       value="{{ $booking->deposit_fine_amount ?? 0 }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Refund Amount (RM)</label>
                                <input type="number" name="refund_amount" step="0.01" min="0" 
                                       class="form-control" 
                                       value="{{ $booking->deposit_refund_amount ?? 0 }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Refund Status</label>
                                <select name="refund_status" class="form-select" required>
                                    <option value="no_action" {{ ($booking->deposit_refund_status ?? 'no_action') === 'no_action' ? 'selected' : '' }}>No Action</option>
                                    <option value="refunded" {{ ($booking->deposit_refund_status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Handled By</label>
                                <select name="handled_by" class="form-select">
                                    <option value="">Select Staff</option>
                                    @foreach(\App\Models\User::where(function($q) { $q->whereHas('staff')->orWhereHas('admin'); })->orderBy('name')->get() as $staff)
                                        <option value="{{ $staff->userID }}" {{ ($booking->deposit_handled_by ?? '') == $staff->userID ? 'selected' : '' }}>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save"></i> Update Deposit Information
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Deposit Detail Tab -->
        <div class="tab-pane fade {{ request()->get('tab') === 'deposit-detail' ? 'show active' : '' }}" id="deposit-detail" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet"></i> Deposit Detail</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Booking ID:</dt>
                        <dd class="col-sm-8">#{{ $booking->bookingID }}</dd>
                        
                        <dt class="col-sm-4">Customer Name:</dt>
                        <dd class="col-sm-8">{{ $booking->customer->user->name ?? 'N/A' }}</dd>
                        
                        <dt class="col-sm-4">Deposit Amount:</dt>
                        <dd class="col-sm-8"><strong>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</strong></dd>
                        
                        <dt class="col-sm-4">Fine Amount:</dt>
                        <dd class="col-sm-8">RM {{ number_format($booking->deposit_fine_amount ?? 0, 2) }}</dd>
                        
                        <dt class="col-sm-4">Refund Amount:</dt>
                        <dd class="col-sm-8">RM {{ number_format($booking->deposit_refund_amount ?? 0, 2) }}</dd>
                        
                        <dt class="col-sm-4">Customer Choice:</dt>
                        <dd class="col-sm-8">
                            @if($booking->deposit_customer_choice)
                                <span class="badge {{ $booking->deposit_customer_choice === 'hold' ? 'bg-info' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($booking->deposit_customer_choice) }}
                                </span>
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Refund Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ ($booking->deposit_refund_status ?? 'no_action') === 'refunded' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ($booking->deposit_refund_status ?? 'no_action') === 'refunded' ? 'Refunded' : 'No Action' }}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Handled By:</dt>
                        <dd class="col-sm-8">
                            @if($booking->deposit_handled_by)
                                {{ \App\Models\User::find($booking->deposit_handled_by)->name ?? 'N/A' }}
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Activate tab from URL parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab) {
            const tabButton = document.querySelector(`button[data-bs-target="#${tab}"]`);
            if (tabButton) {
                const tabInstance = new bootstrap.Tab(tabButton);
                tabInstance.show();
            }
        }
    });
</script>
@endpush
@endsection

