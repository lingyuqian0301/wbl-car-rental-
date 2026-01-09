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

/* ===== STEPPER (already working) ===== */
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
.text-maroon {
    color: var(--hasta-maroon);
}

/* ===== INPUTS ===== */
input,
select {
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

/* ===== QR ===== */
.qr-container {
    border: 2px solid var(--hasta-yellow);
    border-radius: 10px;
    padding: 12px;
    background: #fff;
    display: inline-block;
}

/* ===== BUTTONS ===== */
.btn-maroon {
    background: var(--hasta-maroon);
    color: #fff;
    border: none;
    padding: .45rem 1.3rem;
    border-radius: 5px;
}

/* New Green Button Style */
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

.btn-green:hover {
    background: #157347;
}

.payment-option {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.btn-maroon:hover {
    background: #5e0014;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* ===== STEPPER STYLES ===== */
.booking-stepper .step {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    white-space: nowrap;
}

.booking-stepper .circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.booking-stepper .step.active .circle {
    background: #800020;
    color: #fff;
}

.booking-stepper .label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #6b7280;
}

.booking-stepper .step.active .label {
    color: #800020;
}

.booking-stepper .line {
    flex: 1;
    height: 4px;
    background: #e5e7eb;
    margin: 0 1rem;
    border-radius: 10px;
}

.booking-stepper .line.active {
    background: #800020;
}

/* ===== RADIO BUTTON & AMOUNT STYLES ===== */
.radio-row {
    display: flex;
    align-items: center;
    gap: 1rem; /* More space between radio and text */
    margin-bottom: 0.8rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.2s;
}

.radio-row:hover {
    background: #f3f4f6;
}

/* MAKE RADIO BUTTON BIGGER */
.radio-row input[type="radio"] {
    width: 24px;   /* Specific width */
    height: 24px;  /* Specific height */
    accent-color: #800020;
    transform: scale(1.2); /* Scale up slightly more */
    cursor: pointer;
    margin-right: 0.5rem;
}

.amount-input {
    background: #f0fdf4 !important;
    border-color: #22c55e !important;
    color: #166534;
    font-weight: 600;
}

#amountInput {
    background: #ecfdf5;
    border-color: #22c55e;
    color: #065f46;
    font-weight: 700;
    font-size: 1.1rem; /* Make amount text slightly larger */
}

/* Style for the "Remaining" text */
.remaining-text {
    font-size: 0.85rem;
    color: #dc2626; /* Red color to indicate debt */
    font-weight: 600;
    margin-left: 0.5rem;
    background: #fee2e2;
    padding: 2px 8px;
    border-radius: 12px;
}

</style>

<x-booking-stepper /> {{-- Auto-detects Payment step --}}

<div class="payment-wrapper">
    <div class="payment-card">

        <div class="section-body text-center">
            <strong>HASTA TRAVEL & TOURS SDN. BHD.</strong><br>
            <small class="text-muted">Secure Payment Submission</small>
        </div>

        <div class="section-header-maroon">
            Booking Summary (ID: #{{ $booking->bookingID }})
        </div>
        <div class="section-body">
            <p><strong>Car:</strong> {{ $booking->vehicle->vehicle_model }}</p>
            <p><strong>Dates:</strong>
                {{ $booking->start_date->format('d M Y') }} –
                {{ $booking->end_date->format('d M Y') }}
            </p>
            <p><strong>Total Price:</strong>
                RM {{ number_format($booking->total_amount ?? $booking->rental_amount, 2) }}
            </p>
            <p class="text-maroon fw-bold">
                Required Deposit: RM {{ number_format($depositAmount, 2) }}
            </p>
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

                <div class="payment-option">
                    <label class="radio-row">
                        <input type="radio"
                               name="payment_type"
                               value="Deposit"
                               checked
                               onchange="updateAmount(this.value)">
                        <div>
                            <span style="font-size: 1.05rem; font-weight: 600;">Pay Deposit Only (RM {{ number_format($depositAmount, 2) }})</span>
                            {{-- Remaining Amount Badge (Calculated: Total - Deposit) --}}
                            @php
                                $total = $booking->total_amount ?? $booking->rental_amount;
                                $remaining = $total - $depositAmount;
                            @endphp
                            <span id="remainingBadge" class="remaining-text">
                                (Remaining: RM {{ number_format($remaining, 2) }})
                            </span>
                        </div>
                    </label>

                    <label class="radio-row">
                        <input type="radio"
                               name="payment_type"
                               value="Full Payment"
                               onchange="updateAmount(this.value)">
                        <div>
                            <span style="font-size: 1.05rem; font-weight: 600;">Pay Full Amount (RM {{ number_format($total, 2) }})</span>
                        </div>
                    </label>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Amount to Pay (RM)</label>
                    <input type="number" id="amountInput" name="amount" value="{{ $depositAmount }}" readonly>
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
                                // Get logged-in customer's default bank
                                $defaultBank = Auth::user()->customer->default_bank_name ?? '';
                                $banks = ['Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank', 'AmBank', 'UOB Bank', 'Bank Rakyat', 'OCBC Bank', 'HSBC Bank', 'Bank Islam'];
                            @endphp

                            <option value="" disabled {{ empty($defaultBank) ? 'selected' : '' }}>Select Bank</option>
                            
                            {{-- If default bank exists but isn't in our list, show it first --}}
                            @if($defaultBank && !in_array($defaultBank, $banks))
                                <option value="{{ $defaultBank }}" selected>{{ $defaultBank }} (Default)</option>
                            @endif

                            {{-- Loop through standard banks --}}
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

<script>
function updateAmount(type) {
    let deposit = {{ $depositAmount }};
    let full = {{ $booking->total_amount ?? $booking->rental_amount }};

    // Get the remaining badge element
    let badge = document.getElementById('remainingBadge');

    if (type === 'Full Payment') {
        document.getElementById('amountInput').value = Number(full).toFixed(2);
        // Hide remaining balance if paying full
        if(badge) badge.style.display = 'none';
    } else {
        document.getElementById('amountInput').value = Number(deposit).toFixed(2);
        // Show remaining balance if paying deposit
        if(badge) badge.style.display = 'inline-block';
    }
}
</script>

@endsection