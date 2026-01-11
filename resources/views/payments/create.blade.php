@extends('layouts.app')

@section('content')

<style>
:root {
    --hasta-maroon: #7a0019;
    --hasta-yellow: #f6c343;
    --border: #e5e7eb;
    --bg: #f5f5f5;
}

body {
    background: var(--bg);
}

/* ===== STEPPER ===== */
.booking-stepper {
    display: flex;
    align-items: center;
    max-width: 1200px;
    margin: 3rem auto 2rem;
    padding: 0 1.5rem;
}

/* ===== MAIN CARD ===== */
.payment-wrapper {
    max-width: 900px;
    margin: 0 auto 3rem;
    padding: 0 1.5rem;
}

.payment-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
    overflow: hidden;
}

/* ===== HEADERS ===== */
.section-header-maroon {
    background: var(--hasta-maroon);
    color: #fff;
    padding: .7rem 1rem;
    font-weight: 700;
    font-size: .9rem;
}

.section-header-yellow {
    background: var(--hasta-yellow);
    color: #000;
    padding: .7rem 1rem;
    font-weight: 700;
    font-size: .9rem;
}

/* ===== BODY ===== */
.section-body {
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.section-body:last-child {
    border-bottom: none;
}

/* ===== TEXT ===== */
.text-maroon { color: var(--hasta-maroon); }

/* ===== INPUTS ===== */
input, select {
    width: 100%;
    padding: .5rem .65rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    font-size: .9rem;
}

input:focus, select:focus {
    border-color: var(--hasta-maroon);
    box-shadow: 0 0 0 2px rgba(122, 0, 25, .15);
    outline: none;
}

input[readonly] {
    background: #eef2f7;
    font-weight: 600;
}

#amountInput {
    background: #ecfdf5;
    border-color: #22c55e;
    color: #065f46;
    font-weight: 700;
    font-size: 1.1rem;
}

/* ===== QR ===== */
.qr-container {
    border: 2px solid var(--hasta-yellow);
    border-radius: 10px;
    padding: 12px;
    background: #fff;
    display: inline-block;
}

/* ===== BUTTONS ===== */
.btn-green {
    background: #198754;
    color: #fff;
    border: none;
    padding: .45rem 1.3rem;
    border-radius: 5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-green:hover { background: #157347; }

.payment-option {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

/* ===== STATIC INFO ROW STYLE ===== */
.info-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem;
}

.remaining-text {
    font-size: 0.85rem;
    color: #dc2626;
    font-weight: 600;
    margin-left: 0.5rem;
    background: #fee2e2;
    padding: 2px 8px;
    border-radius: 12px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 600px) {
    .form-row { grid-template-columns: 1fr; }
}
</style>

<x-booking-stepper /> 

<div class="payment-wrapper">
    <div class="payment-card">

        <div class="section-body text-center">
            <strong>HASTA TRAVEL & TOURS SDN. BHD.</strong><br>
            <small class="text-muted">Secure Payment Submission</small>
        </div>

        <div class="section-header-maroon">
            Booking Summary (ID: #{{ $booking->bookingID }})
        </div>

        @php
            // 1. Calculate Totals
            $total = $booking->total_amount ?? $booking->rental_amount;
            
            // 2. Calculate Paid Amount (Verified Only)
            $paidAmount = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
            
            // 3. Determine Mode
            $isReserved = $booking->booking_status === 'Reserved';
            
            // 4. Determine Amount to Pay NOW
            if ($isReserved) {
                // Paying remaining balance
                $amountToPay = $total - $paidAmount;
                $paymentType = 'Balance Payment';
                $remainingAfterPayment = 0;
            } else {
                // Paying deposit (Initial)
                $amountToPay = $depositAmount;
                $paymentType = 'Deposit';
                $remainingAfterPayment = $total - $depositAmount;
            }
        @endphp

        <div class="section-body">
            <p><strong>Car:</strong> {{ $booking->vehicle->vehicle_model }}</p>
            <p><strong>Total Price:</strong> RM {{ number_format($total, 2) }}</p>
            
            @if($isReserved)
                <p class="text-success fw-bold">
                    Already Paid: RM {{ number_format($paidAmount, 2) }}
                </p>
                <p class="text-maroon fw-bold">
                    Balance Due: RM {{ number_format($amountToPay, 2) }}
                </p>
            @else
                <p class="text-maroon fw-bold">
                    Required Deposit: RM {{ number_format($depositAmount, 2) }}
                </p>
            @endif
        </div>

        <div class="section-header-yellow">
            Step 1: Scan DuitNow QR to Pay
        </div>
        <div class="section-body text-center">
            <div class="qr-container mb-2">
                <img src="{{ asset('images/qr.png') }}" width="180">
            </div>
            <div><strong>HASTA TRAVEL TOURS SDN. BHD.</strong></div>
            <small class="text-muted">Maybank: 5513 0654 1568</small>
        </div>

        <div class="section-header-maroon">
            Step 2: Upload Proof & Details
        </div>
        <div class="section-body">

            <form action="{{ route('payments.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="bookingID" value="{{ $booking->bookingID }}">
                <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                <div class="payment-option">
                    <div class="info-row">
                        <i class="bi bi-wallet2 text-maroon" style="font-size: 1.5rem;"></i>
                        <div>
                            @if($isReserved)
                                <span style="font-size: 1.05rem; font-weight: 600; display:block;">
                                    Payment Type: Remaining Balance
                                </span>
                                <small class="text-muted">Finalizing your payment.</small>
                            @else
                                <span style="font-size: 1.05rem; font-weight: 600; display:block;">
                                    Payment Type: Booking Deposit
                                </span>
                                <span class="remaining-text">
                                    (Balance of RM {{ number_format($remainingAfterPayment, 2) }} to be paid later)
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Amount to Pay (RM)</label>
                    <input type="number" 
                           id="amountInput" 
                           name="amount" 
                           value="{{ number_format($amountToPay, 2, '.', '') }}" 
                           readonly>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    <input type="hidden" name="transaction_reference" value="12345678">
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Upload Receipt</label>
                    <input type="file" name="receipt_image" required>
                </div>

                <div class="mt-4">
                    <strong class="text-maroon">Bank Details (For Refund)</strong>
                </div>

                <div class="form-row mt-2">
                    <div>
                        <label>Bank Name</label>
                        <select name="bank_name" required>
                            @php
                                $defaultBank = Auth::user()->customer->default_bank_name ?? '';
                                $banks = ['Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank', 'AmBank', 'UOB Bank', 'Bank Rakyat', 'OCBC Bank', 'HSBC Bank', 'Bank Islam'];
                            @endphp

                            <option value="" disabled {{ empty($defaultBank) ? 'selected' : '' }}>Select Bank</option>
                            
                            @if($defaultBank && !in_array($defaultBank, $banks))
                                <option value="{{ $defaultBank }}" selected>{{ $defaultBank }} (Default)</option>
                            @endif

                            @foreach($banks as $bank)
                                <option value="{{ $bank }}" {{ $defaultBank == $bank ? 'selected' : '' }}>
                                    {{ $bank }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Account Number</label>
                        <input type="text" name="bank_account_number" 
                               value="{{ Auth::user()->customer->default_account_no ?? '' }}" 
                               placeholder="e.g. 7654321098" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('bookings.show', $booking->bookingID) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button class="btn-green">
                        <i class="bi bi-upload"></i> Upload Receipt
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection