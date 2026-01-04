<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }} | HASTA Travel</title>

    <!-- Leaflet.js CSS (Open Source Map Library) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

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
        margin: 3rem auto;
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
    </style>
    @include('components.header')

</head>

<body>

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
        @if ($vehicle->vehicleType === 'Car' && $vehicle->car)
            <div class="spec">
                <div class="spec-label">Transmission</div>
                <div class="spec-value">{{ $vehicle->car->transmission }}</div>
            </div>

            <div class="spec">
                <div class="spec-label">Seating Capacity</div>
                <div class="spec-value">
                    {{ $vehicle->car->seating_capacity }} persons
                </div>
            </div>
        @endif

        {{-- Motorcycle --}}
        @if ($vehicle->vehicleType === 'Motorcycle' && $vehicle->motorcycle)
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

        <div class="spec">
            <div class="spec-label">Plate Number</div>
            <div class="spec-value">{{ $vehicle->plate_number }}</div>
        </div>

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

                <!-- 1️⃣ PICK-UP DATE -->
                <h4>📅 Pick-up Date</h4>
                <div class="form-group">
                    <input type="date" id="startDate" name="start_date" required>
                </div>

                <!-- 2️⃣ PICK-UP LOCATION -->
                <h4>📍 Pick-up Location</h4>
                <div class="form-group">
                    <input type="text" id="pickup_point" name="pickup_point"
                        placeholder="Enter address manually or click on map below" required>

                    <div id="pickup_map" style="height: 250px; width: 100%; margin-top: 10px;
        border-radius: 8px; border: 2px solid var(--border-color);">
                    </div>

                    <small style="color: var(--text-secondary); font-size: 0.85rem;">
                        You can type the address manually or click on the map
                    </small>
                </div>
                <!-- 3️⃣ RETURN DATE -->
                <h4>📅 Return Date</h4>
                <div class="form-group">
                    <input type="date" id="endDate" name="end_date" required>
                </div>

                <!-- 4️⃣ RETURN LOCATION -->
                <h4>📍 Return Location</h4>
                <div class="form-group">
                    <input type="text" id="return_point" name="return_point"
                        placeholder="Enter address manually or click on map below" required>

                    <div id="return_map" style="height: 250px; width: 100%; margin-top: 10px;
        border-radius: 8px; border: 2px solid var(--border-color);">
                    </div>

                    <small style="color: var(--text-secondary); font-size: 0.85rem;">
                        You can type the address manually or click on the map
                    </small>
                </div>
                <!-- Add-ons Options -->
                <div class="addons-section">
                    <h3>Add-on Options</h3>
                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="10" value="GPS|10">
                        <span class="addon-label">🗺 GPS Navigation</span>
                        <span class="addon-price">+RM10/day</span>
                    </label>

                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="15" value="Child Seat|15">
                        <span class="addon-label">🚼 Child Seat</span>
                        <span class="addon-price">+RM15/day</span>
                    </label>

                    <label class="addon-option">
                        <input type="checkbox" class="addon-checkbox" data-price="30" value="Insurance|30">
                        <span class="addon-label">🛡 Full Insurance Coverage</span>
                        <span class="addon-price">+RM30/day</span>
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

    <!-- Leaflet.js JS (Open Source Map Library) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    // Initialize Pickup Location Map using Leaflet (Open Source)
    let pickupMap, pickupMarker;
    const pickupInput = document.getElementById('pickup_point');
    const pickupMapDiv = document.getElementById('pickup_map');

    // Initialize pickup map centered on Malaysia (default)
    pickupMap = L.map(pickupMapDiv).setView([3.1390, 101.6869], 12); // Kuala Lumpur, Malaysia

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(pickupMap);

    // Create initial marker for pickup (not visible until clicked)
    pickupMarker = null;

    // Function to update pickup address from coordinates
    function updatePickupAddress(lat, lng) {
        // Update input with coordinates first (user can edit manually)
        pickupInput.value = lat.toFixed(6) + ', ' + lng.toFixed(6);

        // Try to get address using Nominatim (OpenStreetMap geocoding)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    pickupInput.value = data.display_name;
                }
            })
            .catch(err => console.log('Geocoding failed:', err));
    }

    // Click on map to set pickup location
    pickupMap.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        // Remove existing marker if any
        if (pickupMarker) {
            pickupMap.removeLayer(pickupMarker);
        }

        // Add new marker
        pickupMarker = L.marker([lat, lng], {
            draggable: true
        }).addTo(pickupMap);

        // Update address
        updatePickupAddress(lat, lng);

        // Drag marker to update location
        pickupMarker.on('dragend', function(e) {
            const newLat = e.target.getLatLng().lat;
            const newLng = e.target.getLatLng().lng;
            updatePickupAddress(newLat, newLng);
        });
    });

    // Allow manual input - user can type address freely
    // The input field is fully editable and doesn't require map interaction

    // Initialize Return Location Map using Leaflet (Open Source)
    let returnMap, returnMarker;
    const returnInput = document.getElementById('return_point');
    const returnMapDiv = document.getElementById('return_map');

    // Initialize return map centered on Malaysia (default)
    returnMap = L.map(returnMapDiv).setView([3.1390, 101.6869], 12); // Kuala Lumpur, Malaysia

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(returnMap);

    // Create initial marker for return (not visible until clicked)
    returnMarker = null;

    // Function to update return address from coordinates
    function updateReturnAddress(lat, lng) {
        // Update input with coordinates first (user can edit manually)
        returnInput.value = lat.toFixed(6) + ', ' + lng.toFixed(6);

        // Try to get address using Nominatim (OpenStreetMap geocoding)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    returnInput.value = data.display_name;
                }
            })
            .catch(err => console.log('Geocoding failed:', err));
    }

    // Click on map to set return location
    returnMap.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        // Remove existing marker if any
        if (returnMarker) {
            returnMap.removeLayer(returnMarker);
        }

        // Add new marker
        returnMarker = L.marker([lat, lng], {
            draggable: true
        }).addTo(returnMap);

        // Update address
        updateReturnAddress(lat, lng);

        // Drag marker to update location
        returnMarker.on('dragend', function(e) {
            const newLat = e.target.getLatLng().lat;
            const newLng = e.target.getLatLng().lng;
            updateReturnAddress(newLat, newLng);
        });
    });

    // Allow manual input - user can type address freely
    // The input field is fully editable and doesn't require map interaction

    // Vehicle rental price per day (for real-time display only)
     const dailyRate = {{ $vehicle->rental_price }};
     const depositAmount = 50;





    // Get all input elements
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const priceBreakdown = document.getElementById('priceBreakdown');
    const bookingForm = document.getElementById('bookingForm');

    // Addon configuration for display (must match controller prices)
    const addonConfig = {
        'GPS|10': {
            key: 'gps',
            price: 10
        },
        'Child Seat|15': {
            key: 'child_seat',
            price: 15
        },
        'Insurance|30': {
            key: 'insurance',
            price: 30
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

const totalPrice = basePrice + addonsTotal + depositAmount;

        // Update UI display

        document.getElementById('durationDays').textContent = durationDays > 0 ? durationDays : '-';
        document.getElementById('depositAmount').textContent = depositAmount.toFixed(2);

        document.getElementById('basePriceBreakdown').textContent = basePrice.toFixed(2);
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

    // Form submission - prepare addon data for controller
    bookingForm.addEventListener('submit', function(e) {
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

</html>