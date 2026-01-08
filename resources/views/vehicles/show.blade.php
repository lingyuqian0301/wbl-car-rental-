<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }} | HASTA Travel</title>

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
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

    body {
        font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        line-height: 1.6;
        color: var(--text-primary);
    }

    /* Header Navigation */
    header {
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .header-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        display: flex;
        align-items: center;
    }

    .logo h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-orange);
    }

    .logo span {
        color: #6b7280;
        margin-left: 0.5rem;
    }

    nav {
        display: flex;
        gap: 2rem;
    }

    nav a {
        color: var(--text-secondary);
        text-decoration: none;
        transition: color 0.3s;
        font-weight: 500;
    }

    nav a:hover {
        color: var(--primary-orange);
    }

    .header-btn {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        background-color: var(--primary-orange);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: background-color 0.3s;
        font-weight: 600;
    }

    .header-btn:hover {
        background-color: var(--primary-dark-orange);
    }

    /* Main Content */
    .container {
        max-width: 1200px;
        margin: 0 auto 3rem auto;
        padding: 0 1.5rem;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 3rem;
    }

    /* LEFT SECTION - VEHICLE DETAILS */
    .vehicle-main {
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

    .vehicle-image-container {
        position: relative;
        height: 450px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 2rem;
        border: 2px solid var(--border-color);
    }

    .vehicle-image-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 2rem;
    }

    .vehicle-title {
        margin-bottom: 1.5rem;
    }

    .vehicle-title h1 {
        font-size: 2.5rem;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .vehicle-title p {
        color: var(--text-secondary);
        font-size: 1.1rem;
    }

    .badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    /* Specifications Grid */
    .specs-section {
        margin: 2rem 0;
    }

    .specs-section h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .specs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.2rem;
    }

    .spec {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        padding: 1.2rem;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .spec:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-color: var(--primary-orange);
    }

    .spec-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 600;
        margin-bottom: 0.3rem;
    }

    .spec-value {
        font-size: 1.1rem;
        color: var(--text-primary);
        font-weight: 700;
    }

    /* Add-ons Section */
    .addons-section {
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 2px solid var(--border-color);
    }

    .addons-section h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .addon-option {
        display: flex;
        align-items: center;
        padding: 1rem;
        background: #f9fafb;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }

    .addon-option:hover {
        background: #f0f9ff;
        border-color: var(--primary-orange);
    }

    .addon-option input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 1rem;
        cursor: pointer;
        accent-color: var(--primary-orange);
    }

    .addon-label {
        flex: 1;
        font-weight: 500;
    }

    .addon-price {
        color: var(--primary-orange);
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* RIGHT SECTION - BOOKING BOX */
    .booking-box {
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

    .price-section {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .price {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .price span {
        font-size: 1rem;
        opacity: 0.9;
    }

    .booking-form h4 {
        font-size: 1.1rem;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        color: var(--text-primary);
        font-weight: 700;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .booking-form input,
    .booking-form select {
        width: 100%;
        padding: 0.8rem;
        margin-bottom: 0.8rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
        font-family: 'Figtree', sans-serif;
    }

    .booking-form input:focus,
    .booking-form select:focus {
        outline: none;
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .booking-form input::placeholder {
        color: var(--text-secondary);
    }

    .submit-btn {
        margin-top: 1.5rem;
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    /* Responsive Design */
    @media (max-width: 900px) {
        .container {
            grid-template-columns: 1fr;
        }

        .booking-box {
            position: relative;
            top: auto;
        }

        .specs {
            grid-template-columns: 1fr;
        }

        .vehicle-title h1 {
            font-size: 2rem;
        }

        nav {
            gap: 1rem;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 600px) {
        .header-container {
            padding: 1rem;
        }

        nav {
            display: none;
        }

        .vehicle-title h1 {
            font-size: 1.5rem;
        }

        .price {
            font-size: 2rem;
        }
    }

    /* Footer */
    footer {
        background: var(--primary-dark-orange);
        color: white;
        margin-top: 4rem;
        padding: 2rem;
        text-align: center;
    }

    /* Price Breakdown Section */
    .price-breakdown {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 2px solid var(--border-color);
        display: none;
    }

    .price-breakdown.active {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .breakdown-item {

        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
    }

    .deposit-item {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        margin-bottom: 1rem;
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

    .breakdown-item.total {
        font-size: 1.2rem;
        border-top: 2px solid var(--primary-orange);
        padding-top: 1rem;
        margin-top: 1rem;
        color: var(--primary-orange);
    }

    .breakdown-item.total .breakdown-label {
        color: var(--primary-orange);
        font-weight: 700;
    }

    .breakdown-item.total .breakdown-value {
        color: var(--primary-orange);
        font-weight: 700;
        font-size: 1.3rem;
    }

    /* Leaflet Map Styles */
    #pickup_map,
    #return_map {
        z-index: 1;
    }

    .leaflet-container {
        font-family: inherit;
    }

    /* =========================
   BOOKING STEPPER (HASTA)
========================= */

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
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .booking-stepper .step.active .circle {
        background: linear-gradient(135deg,
                var(--primary-orange),
                var(--primary-dark-orange));
        color: #fff;
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35);
    }

    .booking-stepper .label {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .booking-stepper .step.active .label {
        color: var(--primary-orange);
    }

    .booking-stepper .line {
        flex: 1;
        height: 4px;
        background: #e5e7eb;
        margin: 0 1rem;
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    .booking-stepper .line.active {
        background: linear-gradient(135deg,
                var(--primary-orange),
                var(--primary-dark-orange));
    }

    /* Flatpickr disabled dates styling */
    .flatpickr-calendar .flatpickr-day.disabled,
    .flatpickr-calendar .flatpickr-day.disabled:hover,
    .flatpickr-calendar .flatpickr-day.disabled.prevMonthDay,
    .flatpickr-calendar .flatpickr-day.disabled.nextMonthDay {
        background: #dc2626 !important;
        color: #fff !important;
        cursor: not-allowed !important;
        opacity: 0.7;
        text-decoration: line-through;
        font-weight: 700;
        pointer-events: none !important;
    }
    </style>
    <!-- @include('components.header') -->
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @extends('layouts.app')

</head>

<body>
    @section('content')
    <x-booking-stepper current="1" /> {{-- Booking --}}

    <div class="container">

        <!-- LEFT -->
        <div class="vehicle-main">

            @php
            $imageName = strtolower($vehicle->vehicle_brand . '-' . $vehicle->vehicle_model);
            $imageName = preg_replace('/[^a-z0-9]+/i', '-', $imageName);
            $imageName = trim($imageName, '-');
            $imageName .= '.png';
            $imagePath = public_path('images/cars/browse/' . $imageName);
            @endphp

            <div class="vehicle-title">
                <h1>{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }}</h1>
                <p>{{ $vehicle->vehicleType ?? '' }}</p>
            </div>


            <div class="vehicle-image-container">
                @if(file_exists($imagePath))
                <img src="{{ asset('images/cars/browse/' . $imageName) }}"
                    alt="{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }}">
                @else
                <img src="{{ asset('images/cars/browse/default.png') }}" alt="Default vehicle image">
                @endif
            </div>
            <!-- Vehicle Specifications -->
            <div class="specs-section">
                <h3>Vehicle Specifications</h3>

                <div class="specs">

                    {{-- Car --}}
                    {{-- Car specs --}}
                    @if ($vehicle->car)
                    <div class="spec">
                        <div class="spec-label">Transmission</div>
                        <div class="spec-value">
                            {{ $vehicle->car->transmission ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="spec">
                        <div class="spec-label">Seating Capacity</div>
                        <div class="spec-value">
                            {{ $vehicle->car->seating_capacity ?? 'N/A' }} persons
                        </div>
                    </div>
                    @endif

                    {{-- Motorcycle specs --}}
                    @if ($vehicle->motorcycle)
                    <div class="spec">
                        <div class="spec-label">Motor Type</div>
                        <div class="spec-value">
                            {{ $vehicle->motorcycle->motor_type }}
                        </div>
                    </div>

                    <div class="spec">
                        <div class="spec-label">Engine Capacity</div>
                        <div class="spec-value">
                            {{ $vehicle->engineCapacity }} cc
                        </div>
                    </div>
                    @endif


                    {{-- Common --}}
                    <div class="spec">
                        <div class="spec-label">Color</div>
                        <div class="spec-value">{{ $vehicle->color ?? 'N/A' }}</div>
                    </div>

                    <!-- <div class="spec">
            <div class="spec-label">Plate Number</div>
            <div class="spec-value">{{ $vehicle->plate_number }}</div>
        </div> -->

                </div>
            </div>



        </div>

        <!-- RIGHT - BOOKING SECTION -->
        <div class="booking-box">
            <div class="price-section">
                <div class="price">RM {{ $vehicle->rental_price }}<span>/day</span></div>
                <p style="margin: 0; font-size: 0.9rem;">Premium Rental Rate</p>
            </div>


            <form method="POST" action="{{ route('booking.store', $vehicle->vehicleID) }}" class="booking-form"
                id="bookingForm">
                @csrf

                <!-- Display validation errors -->
                @if ($errors->any())
                <div id="errorBox" style="background-color: #fee; border: 2px solid #dc2626; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <p style="color: #dc2626; font-weight: 600; margin: 0 0 0.5rem 0;">Booking Error:</p>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #dc2626;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @else
                <div id="errorBox" style="background-color: #fee; border: 2px solid #dc2626; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; display: none;">
                    <p style="color: #dc2626; font-weight: 600; margin: 0 0 0.5rem 0;">Booking Error:</p>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #dc2626;"></ul>
                </div>
                @endif

                <!-- PICK-UP DETAILS -->
                <h4>Pick-up Details</h4>

                <!-- Pick-up Point Type (Radio Buttons) -->
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.8rem; font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">Pick-up Point Type</label>
                    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                        <label style="display: flex; align-items: center; cursor: pointer; padding: 0.6rem; border: 2px solid var(--border-color); border-radius: 8px; transition: all 0.2s;">
                            <input type="radio" id="officeRadio" name="pickup_type_radio" value="office" style="width: 18px; height: 18px; margin-right: 0.8rem; cursor: pointer; accent-color: var(--primary-orange);">
                            <span>Office</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer; padding: 0.6rem; border: 2px solid var(--border-color); border-radius: 8px; transition: all 0.2s;">
                            <input type="radio" id="facultyRadio" name="pickup_type_radio" value="faculty" style="width: 18px; height: 18px; margin-right: 0.8rem; cursor: pointer; accent-color: var(--primary-orange);">
                            <span>Faculty</span>
                        </label>
                        <label style="display: flex; align-items: center; cursor: pointer; padding: 0.6rem; border: 2px solid var(--border-color); border-radius: 8px; transition: all 0.2s;">
                            <input type="radio" id="collegeRadio" name="pickup_type_radio" value="college" style="width: 18px; height: 18px; margin-right: 0.8rem; cursor: pointer; accent-color: var(--primary-orange);">
                            <span>College</span>
                        </label>
                    </div>
                </div>

                <!-- Pick-up Location (conditional) -->
                <div class="form-group">
                    <div id="officeLocation" style="display: none;">
                        <input type="text" value="HASTA HQ Office" disabled style="background-color: #f0f0f0; cursor: not-allowed; width: 100%; padding: 0.8rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
                    </div>
                    <div id="facultyLocation" style="display: none;">
                        <select id="facultySelect" style="width: 100%; padding: 0.8rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
                            <option value="">Select faculty</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Computing">Computing</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Science">Science</option>
                            <option value="Built Environment & Surveying">Built Environment & Surveying</option>
                            <option value="Social Sciences & Humanities">Social Sciences & Humanities</option>
                            <option value="Management">Management</option>
                            <option value="Malaysia-Japan International Institute of Technology">Malaysia-Japan International Institute of Technology</option>
                            <option value="Azman Hashim International Business School">Azman Hashim International Business School</option>
                        </select>
                    </div>
                    <div id="collegeLocation" style="display: none;">
                        <select id="collegeSelect" style="width: 100%; padding: 0.8rem; border: 2px solid var(--border-color); border-radius: 8px; font-size: 1rem;">
                            <option value="">Select college</option>
                            <option value="Kolej Rahman Putra">Kolej Rahman Putra</option>
                            <option value="Kolej Tun Fatimah">Kolej Tun Fatimah</option>
                            <option value="Kolej Tun Razak">Kolej Tun Razak</option>
                            <option value="Kolej Tun Hussein Onn">Kolej Tun Hussein Onn</option>
                            <option value="Kolej Tun Dr. Ismail">Kolej Tun Dr. Ismail</option>
                            <option value="Kolej Tuanku Canselor">Kolej Tuanku Canselor</option>
                            <option value="Kolej Perdana">Kolej Perdana</option>
                            <option value="Kolej 9">Kolej 9</option>
                            <option value="Kolej 10">Kolej 10</option>
                            <option value="Kolej Dato' Seri Endon">Kolej Dato' Seri Endon</option>
                            <option value="Kolej Dato' Onn Jaafar">Kolej Dato' Onn Jaafar</option>
                            <option value="Kolej Sri Jelai">Kolej Sri Jelai</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" id="pickup_point" name="pickup_point" value="">
                <input type="hidden" id="pickup_surcharge" name="pickup_surcharge" value="0">

                <!-- Date & Time -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                    <div class="form-group">
                        <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-bottom: 0.4rem;">Date</small>
                        <input type="date" id="startDate" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-bottom: 0.4rem;">Time</small>
                        <input type="time" id="startTime" name="start_time" required>
                    </div>
                </div>

                <!-- RETURN DETAILS -->
                <h4>Return Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
                    <div class="form-group">
                        <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-bottom: 0.4rem;">Date</small>
                        <input type="date" id="endDate" name="end_date" required>
                    </div>
                    <div class="form-group">
                        <small style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-bottom: 0.4rem;">Time</small>
                        <input type="time" id="endTime" name="end_time" required>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="return_point" name="return_point" placeholder="Location" required>
                </div>
                <!-- Add-ons Options -->
                <div class="addons-section">
                    <h3>Add-on Options</h3>
                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="5" value="Power Bank|5">
                        <span class="addon-label">Power Bank</span>
                        <span class="addon-price">+RM5/day</span>
                    </label>

                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="5" value="Phone Holder|5">
                        <span class="addon-label">Phone Holder</span>
                        <span class="addon-price">+RM5/day</span>
                    </label>

                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="3" value="USB Wire|3">
                        <span class="addon-label">USB Wire</span>
                        <span class="addon-price">+RM3/day</span>
                    </label>
                </div>

                <!-- Real-time Price Breakdown -->
                <div class="price-breakdown" id="priceBreakdown">
                    <div class="breakdown-item">
                        <span class="breakdown-label">Rental Duration:</span>
                        <span class="breakdown-value"><span id="durationDays">0</span> day(s)</span>
                    </div>

                    <div class="breakdown-item">
                        <span class="breakdown-label">Base Price:</span>
                        <span class="breakdown-value">RM <span id="basePriceBreakdown">0</span></span>
                    </div>

                    <div id="addonsBreakdown"></div>

                    <div id="surchargeBreakdown" class="breakdown-item" style="display: none;">
                        <span class="breakdown-label">Pick-up Surcharge:</span>
                        <span class="breakdown-value">RM <span id="surchargeAmount">10.00</span></span>
                    </div>

                    <div class="breakdown-item deposit-item">
                        <span class="breakdown-label">Deposit (Refundable):</span>
                        <span class="breakdown-value">RM <span id="depositAmount">50.00</span></span>
                    </div>


                    <div class="breakdown-item total">
                        <span class="breakdown-label">Total Price:</span>
                        <span class="breakdown-value">RM <span id="totalPriceBreakdown">0</span></span>
                    </div>
                </div>

                <!-- Hidden fields for addons will be added by JavaScript -->
                <button type="submit" class="submit-btn">Proceed to Booking</button>

            </form>

            <div
                style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--border-color); font-size: 0.85rem; color: var(--text-secondary); text-align: center;">
                ✓ Secure booking • ✓ Best price guarantee • ✓ 24/7 support
            </div>
        </div>

    </div>

    @include('components.footer')

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    // Vehicle rental price per day (for real-time display only)
    const dailyRate = {{ $vehicle->rental_price }};

    const depositAmount = 50;





    // Get all input elements
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const priceBreakdown = document.getElementById('priceBreakdown');
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.querySelector('.submit-btn');
    
    const officeRadio = document.getElementById('officeRadio');
    const facultyRadio = document.getElementById('facultyRadio');
    const collegeRadio = document.getElementById('collegeRadio');
    
    const officeLocation = document.getElementById('officeLocation');
    const facultyLocation = document.getElementById('facultyLocation');
    const collegeLocation = document.getElementById('collegeLocation');
    const facultySelect = document.getElementById('facultySelect');
    const collegeSelect = document.getElementById('collegeSelect');
    const pickupPointInput = document.getElementById('pickup_point');
    const pickupSurcharge = document.getElementById('pickup_surcharge');

    let currentPickupSurcharge = 0;

    // Radio button change handlers
    officeRadio.addEventListener('change', function() {
        officeLocation.style.display = 'block';
        facultyLocation.style.display = 'none';
        collegeLocation.style.display = 'none';
        pickupPointInput.value = 'HASTA HQ Office';
        pickupSurcharge.value = '0';
        currentPickupSurcharge = 0;
        facultySelect.value = '';
        collegeSelect.value = '';
        displayPriceBreakdown();
    });

    facultyRadio.addEventListener('change', function() {
        officeLocation.style.display = 'none';
        facultyLocation.style.display = 'block';
        collegeLocation.style.display = 'none';
        pickupPointInput.value = '';
        pickupSurcharge.value = '10';
        currentPickupSurcharge = 10;
        collegeSelect.value = '';
        displayPriceBreakdown();
    });

    collegeRadio.addEventListener('change', function() {
        officeLocation.style.display = 'none';
        facultyLocation.style.display = 'none';
        collegeLocation.style.display = 'block';
        pickupPointInput.value = '';
        pickupSurcharge.value = '10';
        currentPickupSurcharge = 10;
        facultySelect.value = '';
        displayPriceBreakdown();
    });

    // Faculty dropdown updates hidden pickup_point
    facultySelect.addEventListener('change', function() {
        if (this.value) {
            pickupPointInput.value = this.value;
        } else {
            pickupPointInput.value = '';
        }
    });

    // College dropdown updates hidden pickup_point
    collegeSelect.addEventListener('change', function() {
        if (this.value) {
            pickupPointInput.value = this.value;
        } else {
            pickupPointInput.value = '';
        }
    });

    // Real-time date availability checking
    const dateErrorBox = document.getElementById('errorBox');
    const errorHeading = dateErrorBox ? dateErrorBox.querySelector('p') : null;
    const errorList = dateErrorBox ? dateErrorBox.querySelector('ul') : null;
    const blockedDates = @json($blockedDates);

    // Initialize Flatpickr with blocked dates
    function initializeDatePickers() {
        flatpickr('#startDate', {
            minDate: 'today',
            disable: blockedDates,
            onChange: validateDates,
            dateFormat: 'Y-m-d'
        });

        flatpickr('#endDate', {
            minDate: 'today',
            disable: blockedDates,
            onChange: validateDates,
            dateFormat: 'Y-m-d'
        });
    }

    function validateDates() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (!startDate || !endDate) {
            hideAvailabilityError();
            return;
        }

        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) {
            showAvailabilityError('Return date must be after pick-up date.');
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            hideAvailabilityError();
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            displayPriceBreakdown();
        }
    }

    function showAvailabilityError(message) {
        if (!dateErrorBox) return;
        
        dateErrorBox.style.display = 'block';
        if (errorHeading) {
            errorHeading.textContent = 'Booking Error:';
        }
        if (errorList) {
            errorList.innerHTML = `<li>${message}</li>`;
        }
    }

    function hideAvailabilityError() {
        if (!dateErrorBox) return;
        dateErrorBox.style.display = 'none';
    }

    // Initialize date pickers when DOM is ready
    document.addEventListener('DOMContentLoaded', initializeDatePickers);

    // Addon configuration for display (must match controller prices)
    const addonConfig = {
        'Power Bank|5': {
            key: 'power_bank',
            price: 5
        },
        'Phone Holder|5': {
            key: 'phone_holder',
            price: 5
        },
        'USB Wire|3': {
            key: 'usb_wire',
            price: 3
        }
    };

    // Real-time price display (UI only - not for database)
    function displayPriceBreakdown() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        // Check if user has selected any addons
        const hasSelectedAddons = Array.from(addonCheckboxes).some(cb => cb.checked);

        // Show pricing if addons selected OR valid dates provided
        let shouldShowPricing = hasSelectedAddons;
        let durationDays = 0;

        if (startDateInput.value && endDateInput.value && endDate >= startDate) {
            // Calculate duration the same way as PHP: inclusive counting
            const durationMs = endDate - startDate;
            const diffDays = Math.floor(durationMs / (1000 * 60 * 60 * 24));
            durationDays = diffDays + 1; // +1 for inclusive counting (same as PHP)
            shouldShowPricing = true;
        }

        if (!shouldShowPricing) {
            priceBreakdown.classList.remove('active');
            return;
        }

        // Display calculations (real-time preview)
        const basePrice = durationDays > 0 ? dailyRate * durationDays : 0;
        let addonsTotal = 0;
        let addonsDetails = [];

        addonCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const addonValue = checkbox.value;
                const config = addonConfig[addonValue];
                if (config) {
                    const addonCharge = durationDays > 0 ? config.price * durationDays : config.price;
                    addonsTotal += addonCharge;
                    addonsDetails.push({
                        name: addonValue.split('|')[0],
                        total: addonCharge
                    });
                }
            }
        });

        const totalPrice = basePrice + addonsTotal + depositAmount + currentPickupSurcharge;

        // Update UI display
        document.getElementById('durationDays').textContent = durationDays > 0 ? durationDays : '-';
        document.getElementById('depositAmount').textContent = depositAmount.toFixed(2);
        document.getElementById('basePriceBreakdown').textContent = basePrice.toFixed(2);

        // Show/hide surcharge
        const surchargeBreakdown = document.getElementById('surchargeBreakdown');
        if (currentPickupSurcharge > 0) {
            surchargeBreakdown.style.display = 'flex';
            document.getElementById('surchargeAmount').textContent = currentPickupSurcharge.toFixed(2);
        } else {
            surchargeBreakdown.style.display = 'none';
        }

        document.getElementById('totalPriceBreakdown').textContent = totalPrice.toFixed(2);

        // Update addons breakdown
        const addonsBreakdownDiv = document.getElementById('addonsBreakdown');
        addonsBreakdownDiv.innerHTML = '';

        if (addonsDetails.length > 0) {
            addonsDetails.forEach(addon => {
                const addonDiv = document.createElement('div');
                addonDiv.className = 'breakdown-item';
                addonDiv.innerHTML = `
                        <span class="breakdown-label">${addon.name}:</span>
                        <span class="breakdown-value">RM ${addon.total.toFixed(2)}</span>
                    `;
                addonsBreakdownDiv.appendChild(addonDiv);
            });
        }

        priceBreakdown.classList.add('active');
    }

    // Event listeners for real-time display
    startDateInput.addEventListener('change', displayPriceBreakdown);
    endDateInput.addEventListener('change', displayPriceBreakdown);
    addonCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', displayPriceBreakdown);
    });

    // Form submission - prepare addon data and validate pickup selection
    bookingForm.addEventListener('submit', function(e) {
        // Validate pickup type is selected
        if (!officeRadio.checked && !facultyRadio.checked && !collegeRadio.checked) {
            e.preventDefault();
            alert('Please select a pick-up point type');
            return;
        }

        // Validate faculty selection if faculty selected
        if (facultyRadio.checked && !facultySelect.value) {
            e.preventDefault();
            alert('Please select a faculty');
            return;
        }

        // Validate college selection if college selected
        if (collegeRadio.checked && !collegeSelect.value) {
            e.preventDefault();
            alert('Please select a college');
            return;
        }

        // Validate pickup location is filled
        if (!pickupPointInput.value) {
            e.preventDefault();
            alert('Please complete the pick-up location');
            return;
        }

        // Remove any existing addon inputs
        const existingAddonInputs = bookingForm.querySelectorAll('input[name="addons[]"]');
        existingAddonInputs.forEach(input => input.remove());

        // Add addon array inputs in the format the controller expects
        addonCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const addonValue = checkbox.value;
                const config = addonConfig[addonValue];

                if (config) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'addons[]';
                    input.value = config.key;
                    bookingForm.appendChild(input);
                }
            }
        });
    });
    </script>

</body>
@endsection

</html>