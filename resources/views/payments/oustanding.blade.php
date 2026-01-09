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

/* ===== MAIN CARD ===== */
.payment-wrapper {
    max-width: 900px;
    margin: 3rem auto 3rem; /* Adjusted top margin since stepper is gone */
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
</style>

{{-- STEPPER REMOVED AS REQUESTED --}}

<div class="payment-wrapper">
    <div class="payment-card">

        <div class="section-body text-center">
            <strong>HASTA TRAVEL & TOURS SDN. BHD.</strong><br>
            <small class="text-muted">Secure Payment Submission</small>
        </div>

        <div class="section-header-maroon">
            Outstanding Payment (Booking ID: #{{ $booking->bookingID }})
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
                        {{-- Changed options to single "Pay Base Price" --}}
                        <input type="radio"
                               name="payment_type"
                               value="Full Payment"
                               checked>
                        <div>
                            <span style="font-size: 1.05rem; font-weight: 600;">Pay Base Price</span>
                        </div>
                    </label>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Amount to Pay (RM)</label>
                    {{-- Pre-set to Full Amount (Base Price) --}}
                    <input type="number" id="amountInput" name="amount" value="{{ number_format($booking->total_amount ?? $booking->rental_amount, 2, '.', '') }}" readonly>
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
@endsection