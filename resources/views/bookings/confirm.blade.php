@extends('layouts.app')

@section('content')
<style>

    .booking-stepper {
    display: flex;
    align-items: center;
    max-width: 1200px;
    margin: 3rem auto 2rem;
    padding: 0 1.5rem;
}

.booking-stepper .step {
    display: flex;
    align-items: center;
    gap: 0.6rem;
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
    background: linear-gradient(135deg, #dc2626, #991b1b);
    color: #fff;
}

.booking-stepper .label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #64748b;
}

.booking-stepper .step.active .label {
    color: #dc2626;
}

.booking-stepper .line {
    flex: 1;
    height: 4px;
    background: #e5e7eb;
    margin: 0 1rem;
    border-radius: 10px;
}

.booking-stepper .line.active {
    background: linear-gradient(135deg, #dc2626, #991b1b);
}
    :root {
        --primary-orange: #dc2626;
        --primary-dark-orange: #991b1b;
        --success-green: #059669;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --border-color: #e2e8f0;
        --bg-light: #f8fafc;
        --error-red: #dc2626;
    }

    /* Container Layout */
    .confirmation-container {
        max-width: 1200px;
        margin: 0 auto 3rem auto;
        padding: 0 1.5rem;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 3rem;
    }

    /* Left Section - Confirmation Details */
    .confirmation-main {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        animation: slideInLeft 0.5s ease-out;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .confirmation-title {
        margin-bottom: 1.2rem;
    }

    .confirmation-title h1 {
        font-size: 2rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .confirmation-title p {
        color: var(--text-secondary);
        font-size: 1rem;
    }

    .confirmation-section {
        margin-bottom: 1.2rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .confirmation-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .confirmation-section h3 {
        font-size: 1rem;
        color: var(--text-primary);
        margin-bottom: 0.6rem;
        font-weight: 700;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0;
        font-size: 0.95rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    .info-value {
        color: var(--text-primary);
        font-weight: 500;
        text-align: right;
    }

    .addon-list {
        list-style: none;
        padding: 0;
    }

    .addon-list li {
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.9rem;
    }

    .addon-list li:last-child {
        border-bottom: none;
    }

    .addon-name {
        color: var(--text-primary);
        font-weight: 500;
    }

    .addon-price {
        color: var(--primary-orange);
        font-weight: 600;
    }

    /* Right Section - Summary Box */
    .confirmation-summary {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        position: sticky;
        top: 100px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .price-display {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .total-price {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .total-price span {
        font-size: 1rem;
        opacity: 0.9;
    }

    .price-breakdown {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 2px solid var(--border-color);
    }

    .breakdown-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
    }

    .breakdown-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .breakdown-label {
        color: var(--text-secondary);
        font-weight: 500;
    }

    .breakdown-value {
        color: var(--text-primary);
        font-weight: 600;
    }

    .breakdown-total {
        font-size: 1.1rem;
        border-top: 2px solid var(--primary-orange);
        padding-top: 1rem;
        margin-top: 1rem;
        color: var(--primary-orange);
    }

    .breakdown-total .breakdown-label {
        color: var(--primary-orange);
        font-weight: 700;
    }

    .breakdown-total .breakdown-value {
        color: var(--primary-orange);
        font-weight: 700;
        font-size: 1.2rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .btn-back {
        flex: 1;
        padding: 0.8rem 1rem;
        background-color: #e5e7eb;
        color: var(--text-primary);
        text-decoration: none;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }

    .btn-back:hover {
        background-color: #d1d5db;
        transform: translateY(-2px);
    }

    .btn-confirm {
        flex: 1;
        padding: 0.8rem 1rem;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .btn-confirm:active {
        transform: translateY(0);
    }

    .error-message {
        background-color: #fee2e2;
        border: 1px solid var(--primary-orange);
        color: var(--primary-orange);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    /* Loading Overlay */
    #loadingOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    #loadingOverlay.active {
        display: flex;
    }

    .loading-content {
        background: white;
        border-radius: 15px;
        padding: 50px 40px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .spinner {
        width: 60px;
        height: 60px;
        margin: 0 auto 30px;
        border: 6px solid #f0f0f0;
        border-top: 6px solid var(--primary-orange);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text h2 {
        color: var(--text-primary);
        font-size: 1.3rem;
        margin-bottom: 10px;
    }

    .loading-text p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    /* Responsive Design */
    @media (max-width: 900px) {
        .confirmation-container {
            grid-template-columns: 1fr;
        }

        .confirmation-summary {
            position: relative;
            top: auto;
        }

        .confirmation-title h1 {
            font-size: 1.5rem;
        }

        .total-price {
            font-size: 2rem;
        }
    }

    @media (max-width: 600px) {
        .confirmation-container {
            margin: 1.5rem auto;
            padding: 0 1rem;
            gap: 1.5rem;
        }

        .confirmation-main,
        .confirmation-summary {
            padding: 1.5rem;
        }

        .confirmation-title h1 {
            font-size: 1.3rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-back,
        .btn-confirm {
            width: 100%;
        }
    }
</style>
<x-booking-stepper /> {{-- Auto-detects Booking Details step --}}

<div class="confirmation-container">
    <!-- LEFT SECTION - Confirmation Details -->
    <div class="confirmation-main">
        <div class="confirmation-title">
            <h1>Booking Confirmation</h1>
            <p>Please review and confirm your booking details</p>
        </div>

        @if(session('error'))
            <div class="error-message">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-message">
                <ul style="list-style-type: none; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Customer Information -->
        <div class="confirmation-section">
            <h3>Customer Information</h3>
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ auth()->user()->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ auth()->user()->email }}</span>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="confirmation-section">
            <h3>Vehicle Details</h3>
            <div class="info-item">
                <span class="info-label">Vehicle:</span>
                <span class="info-value">{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Type:</span>
<span class="info-value">
    @if ($vehicle->car)
        {{ $vehicle->car->vehicle_type }}
    @elseif ($vehicle->motorcycle)
        {{ $vehicle->motorcycle->motor_type }}
    @else
        N/A
    @endif
</span>
            </div>
            <div class="info-item">
                <span class="info-label">Color:</span>
                <span class="info-value">{{ $vehicle->color }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Daily Rate:</span>
                <span class="info-value" style="color: var(--primary-orange); font-weight: 700;">RM {{ number_format((float)$vehicle->rental_price, 2) }}</span>
            </div>
        </div>

        <!-- Rental Period -->
        <div class="confirmation-section">
            <h3>Rental Period</h3>
            <div class="info-item">
                <span class="info-label">Pick-up Date & Time:</span>
                <span class="info-value">{{ date('M d, Y H:i', strtotime($bookingData['rental_start_date'])) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Return Date & Time:</span>
                <span class="info-value">{{ date('M d, Y H:i', strtotime($bookingData['rental_end_date'])) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Duration:</span>
                <span class="info-value">{{ $bookingData['duration'] }} day(s)</span>
            </div>
        </div>

        <!-- Rental Locations -->
        <div class="confirmation-section">
            <h3> Rental Locations</h3>
            <div class="info-item">
                <span class="info-label">Pick-up:</span>
                <span class="info-value">{{ $bookingData['pickup_point'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Return:</span>
                <span class="info-value">{{ $bookingData['return_point'] }}</span>
            </div>
        </div>

        <!-- Add-ons -->
        @if(!empty($addons) && count($addons) > 0)
        <div class="confirmation-section">
            <h3> Add-ons Selected</h3>
            <ul class="addon-list">
                @foreach($addons as $addon)
                <li>
                    <span class="addon-name">{{ $addon['name'] }}</span>
                    <span class="addon-price">RM {{ number_format((float)$addon['total'], 2) }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- RIGHT SECTION - Summary & Actions -->
    <div class="confirmation-summary">
        <!-- Total Price Display -->
        <div class="price-display">
            <div class="total-price">RM {{ number_format((float)$bookingData['total_amount'], 2) }}<span>/total</span></div>
            <p style="margin: 0; font-size: 0.9rem;">Complete Rental Cost</p>
        </div>

        <!-- Price Breakdown -->
        <div class="price-breakdown">
            <div class="breakdown-item">
                <span class="breakdown-label">Duration:</span>
                <span class="breakdown-value">{{ $bookingData['duration'] }} day(s)</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label">Vehicle Rate:</span>
                <span class="breakdown-value">RM {{ number_format((float)$vehicle->rental_price, 2) }}/day × {{ $bookingData['duration'] }} days</span>
            </div>
            <div class="breakdown-item">
                <span class="breakdown-label" style="font-weight: 700; color: var(--text-primary);">Subtotal (Base):</span>
                <span class="breakdown-value" style="font-weight: 700;">RM {{ number_format((float)$vehicle->rental_price * $bookingData['duration'], 2) }}</span>
            </div>

            @if(!empty($addons) && count($addons) > 0)
                <div style="border-top: 1px solid var(--border-color); margin: 0.8rem 0; padding-top: 0.8rem;">
                    <div style="font-size: 0.9rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 0.6rem;">Add-ons:</div>
                    @php $addonTotal = 0; @endphp
                    @foreach($addons as $addon)
                        @php
                            $addonDailyPrice = $addon['total'] / $bookingData['duration'];
                            $addonTotal += $addon['total'];
                        @endphp
                        <div class="breakdown-item" style="padding: 0.5rem 0;">
                            <span class="breakdown-label">{{ $addon['name'] }}:</span>
                            <span class="breakdown-value">RM {{ number_format($addonDailyPrice, 2) }}/day × {{ $bookingData['duration'] }} = RM {{ number_format((float)$addon['total'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="breakdown-item" style="padding-top: 0.6rem; border-top: 1px solid var(--border-color);">
                        <span class="breakdown-label" style="font-weight: 600;">Total Add-ons:</span>
                        <span class="breakdown-value" style="font-weight: 600;">RM {{ number_format($addonTotal, 2) }}</span>
                    </div>
                </div>
            @endif

            @if(!empty($pickupSurcharge) && $pickupSurcharge > 0)
                <div class="breakdown-item">
                    <span class="breakdown-label">Pick-up Surcharge{{ !empty($pickupCustomLocation) ? ' (Others)' : '' }}:</span>
                    <span class="breakdown-value">RM {{ number_format((float)$pickupSurcharge, 2) }}</span>
                </div>
                @if(!empty($pickupCustomLocation))
                    <div class="breakdown-item" style="padding: 0.3rem 0; font-size: 0.85rem;">
                        <span class="breakdown-label" style="color: var(--text-secondary);">↳ Location:</span>
                        <span class="breakdown-value" style="font-weight: 500;">{{ $pickupCustomLocation }}</span>
                    </div>
                @endif
            @endif

            @if(!empty($returnSurcharge) && $returnSurcharge > 0)
                <div class="breakdown-item">
                    <span class="breakdown-label">Return Surcharge{{ !empty($returnCustomLocation) ? ' (Others)' : '' }}:</span>
                    <span class="breakdown-value">RM {{ number_format((float)$returnSurcharge, 2) }}</span>
                </div>
                @if(!empty($returnCustomLocation))
                    <div class="breakdown-item" style="padding: 0.3rem 0; font-size: 0.85rem;">
                        <span class="breakdown-label" style="color: var(--text-secondary);">↳ Location:</span>
                        <span class="breakdown-value" style="font-weight: 500;">{{ $returnCustomLocation }}</span>
                    </div>
                @endif
            @endif

            @php
                $subtotalBeforeDiscount = ($vehicle->rental_price * $bookingData['duration']) +
                    (isset($addons) ? array_sum(array_column($addons, 'total')) : 0) +
                    ($pickupSurcharge ?? 0) + ($returnSurcharge ?? 0);
            @endphp

            <div class="breakdown-item" style="border-top: 2px solid var(--border-color); margin-top: 0.8rem; padding-top: 0.8rem;">
                <span class="breakdown-label" style="font-weight: 700;">Subtotal:</span>
                <span class="breakdown-value" style="font-weight: 700;">RM {{ number_format($subtotalBeforeDiscount, 2) }}</span>
            </div>

            @if(isset($discountAmount) && $discountAmount > 0 && isset($activeVoucher) && $activeVoucher)
                <div class="breakdown-item" style="color: var(--success-green);">
                    <span class="breakdown-label" style="color: var(--success-green); font-weight: 600;">
                        <i class="bi bi-ticket-perforated"></i> Voucher Discount (10%):
                    </span>
                    <span class="breakdown-value" style="color: var(--success-green); font-weight: 700;">
                        - RM {{ number_format((float)$discountAmount, 2) }}
                    </span>
                </div>
            @endif

            <div class="breakdown-item">
                <span class="breakdown-label">Deposit (Refundable):</span>
                <span class="breakdown-value">RM {{ number_format((float)$depositAmount, 2) }}</span>
            </div>

            <div class="breakdown-item breakdown-total">
                <span class="breakdown-label">Total Amount Due:</span>
                <span class="breakdown-value">RM {{ number_format((float)$bookingData['total_amount'], 2) }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-back" type="button" onclick="history.back()">← Back</button>

            <form method="post" action="{{ route('booking.finalize') }}" style="flex: 1;" id="confirmForm">
                @csrf
                <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicleID }}">
                <input type="hidden" name="start_date" value="{{ \Carbon\Carbon::parse($bookingData['rental_start_date'])->format('Y-m-d') }}">
                <input type="hidden" name="start_time" value="{{ \Carbon\Carbon::parse($bookingData['rental_start_date'])->format('H:i') }}">
                <input type="hidden" name="end_date" value="{{ \Carbon\Carbon::parse($bookingData['rental_end_date'])->format('Y-m-d') }}">
                <input type="hidden" name="end_time" value="{{ \Carbon\Carbon::parse($bookingData['rental_end_date'])->format('H:i') }}">
                <input type="hidden" name="pickup_point" value="{{ $bookingData['pickup_point'] }}">
                <input type="hidden" name="return_point" value="{{ $bookingData['return_point'] }}">
                <input type="hidden" name="total_amount" value="{{ $bookingData['total_amount'] }}">
                <input type="hidden" name="pickup_surcharge" value="{{ $pickupSurcharge ?? 0 }}">
                <input type="hidden" name="return_surcharge" value="{{ $returnSurcharge ?? 0 }}">
                @if(!empty($pickupCustomLocation))
                <input type="hidden" name="pickup_custom_location" value="{{ $pickupCustomLocation }}">
                @endif
                @if(!empty($returnCustomLocation))
                <input type="hidden" name="return_custom_location" value="{{ $returnCustomLocation }}">
                @endif

                @if(!empty($addons) && count($addons) > 0)
                    @foreach($addons as $index => $addon)
                        <input type="hidden" name="addons[{{ $index }}][name]" value="{{ $addon['name'] }}">
                        <input type="hidden" name="addons[{{ $index }}][price]" value="{{ $addon['total'] }}">
                    @endforeach
                @endif
                <button type="submit" class="btn-confirm" id="submitBtn">Confirm & Proceed</button>
            </form>
        </div>

        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color); font-size: 0.85rem; color: var(--text-secondary); text-align: center;">
            ✓ Secure booking • ✓ Best price guarantee • ✓ 24/7 support
        </div>
    </div>
</div>


@endsection
