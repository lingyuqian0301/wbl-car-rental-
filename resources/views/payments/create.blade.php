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

input:focus {
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

.booking-stepper {
    display: flex;
    align-items: center;
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

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

.step-header {
    background: #800020;
    color: #fff;
    padding: 0.75rem 1.25rem;
    font-weight: 600;
}

.payment-option {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
}

.radio-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.radio-row input[type="radio"] {
    accent-color: #800020;
    transform: scale(1.1);
}

.amount-input {
    background: #f0fdf4 !important;
    border-color: #22c55e !important;
    color: #166534;
    font-weight: 600;
}

.text-maroon {
    color: #800020;
}

.btn-maroon {
    background-color: #800020;
    border-color: #800020;
    color: #fff;
}

.btn-maroon:hover {
    background-color: #600018;
    border-color: #600018;
}
#amountInput {
    background: #ecfdf5;
    border-color: #22c55e;
    color: #065f46;
    font-weight: 700;
}
input[type="radio"] {
    width: auto !important;
}

</style>

<x-booking-stepper /> {{-- Auto-detects Payment step --}}

<div class="payment-wrapper">
    <div class="payment-card">

        <!-- HEADER -->
        <div class="section-body text-center">
            <strong>HASTA TRAVEL & TOURS SDN. BHD.</strong><br>
            <small class="text-muted">Secure Payment Submission</small>
        </div>

        <!-- BOOKING SUMMARY -->
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

        <!-- STEP 1 -->
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

        <!-- STEP 2 -->
        <div class="section-header-maroon">
            Step 2: Upload Proof & Details
        </div>
        <div class="section-body">

            <form action="{{ route('payments.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="bookingID" value="{{ $booking->bookingID }}">

                <!-- PAYMENT OPTION -->
                <div class="payment-option">
    <div class="radio-row">
        <input type="radio"
               name="payment_type"
               value="Deposit"
               checked
               onchange="updateAmount(this.value)">
        <span>Pay Deposit Only (RM {{ number_format($depositAmount, 2) }})</span>
    </div>

    <div class="radio-row">
        <input type="radio"
               name="payment_type"
               value="Full Payment"
               onchange="updateAmount(this.value)">
        <span>Pay Full Amount (RM {{ number_format($booking->total_amount ?? $booking->rental_amount, 2) }})</span>
    </div>
</div>



                <!-- AMOUNT -->
                <div class="mt-3">
                    <label class="fw-bold">Amount to Pay (RM)</label>
                    <input type="number" id="amountInput" name="amount" value="{{ $depositAmount }}" readonly>
                </div>

                <!-- DATE + REF -->
                <div class="form-row mt-3">
                    <div>
                        <label class="fw-bold">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="fw-bold">Transaction Reference No.</label>
                        <input type="text" name="transaction_reference" placeholder="e.g. 12345678" required>
                    </div>
                </div>

                <!-- RECEIPT -->
                <div class="mt-3">
                    <label class="fw-bold">Upload Receipt</label>
                    <input type="file" name="receipt_image" required>
                </div>

                <!-- BANK -->
                <div class="mt-4">
                    <strong class="text-maroon">Bank Details (Refund)</strong>
                </div>

                <div class="form-row mt-2">
                    <div>
                        <label>Bank Name</label>

                        <input type="text" name="bank_name" placeholder="e.g. CIMB" required>
                    </div>
                    <div>
                        <label>Account Number</label>

                        <input type="text" name="bank_account_number" placeholder="e.g. 7654321098" required>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('bookings.show', $booking->bookingID) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button class="btn-maroon">
                        Submit Payment
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

    document.getElementById('amountInput').value =
        type === 'Full Payment'
            ? Number(full).toFixed(2)
            : Number(deposit).toFixed(2);
}
</script>



@endsection