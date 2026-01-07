<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HASTA Travel - Car Rental System</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Figtree', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        line-height: 1.6;
        color: #333;
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


    /* Hero Section */
    .hero {
        background: linear-gradient(to right, var(--primary-orange), var(--primary-dark-orange));
        color: white;
        padding-bottom: 8rem;
    }

    .hero-container {
        max-width: 1280px;
        margin: 0 auto;
    }

    .hero h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .hero p {
        font-size: 1.125rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero-btn {
        display: inline-block;
        padding: 0.75rem 2rem;
        background-color: #ffffff;
        color: var(--primary-orange);
        font-weight: 700;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: background-color 0.3s;
    }

    .hero-btn:hover {
        background-color: #f3f4f6;
    }

    /* Section Styles */
    section {
        padding: 2rem 2rem 0.5rem 2rem;
    }

    section h3 {
        font-size: 1.875rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    /* Features Grid */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        max-width: 900px;
        margin: 0 auto;
    }

    .feature-card {
        background-color: #f9fafb;
        padding: 1.25rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .feature-icon {
        font-size: 2rem;
        margin-bottom: 0.75rem;
    }

    .feature-card h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .feature-card p {
        color: #6b7280;
        font-size: 0.9rem;
    }

    /* Cars Grid */
    /* Cars Grid - FIXED */
    .cars-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }

    /* Hide non-card elements in grid */
    .cars-grid> :not(.car-card) {
        display: none;
    }

    .car-card {
        width: 100%;
        display: flex;
        flex-direction: column;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }



    .car-image {
        height: 180px;
        width: 100%;
        background-color: #f3f4f6;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .car-card.green .car-image {
        background-color: #d1fae5;
    }

    .car-card.yellow .car-image {
        background-color: #fef3c7;
    }

    .car-content {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .car-content h4 {
        min-height: 48px;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.1rem;
        color: #1f2937;
        line-height: 1.3;
    }

    .car-type {
        color: #6b7280;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }

    /* .car-content h4 {
    margin-bottom: 0;
}

.car-type {
    margin-top: 0;
    margin-bottom: 0.4rem;
} */


    .car-image img {
        width: 100%;
        height: 100%;
        transform: scale(1.20);
        object-fit: contain;
        object-position: center;
    }

    .car-specs {
        margin-bottom: 1rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        min-height: 28px;
    }

    .car-specs p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .car-specs strong {
        font-weight: 600;
        color: #374151;
    }

    .spec-icon {
        font-size: 1.2rem;
    }

    .color-dot {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 1px solid #d1d5db;
    }

    .car-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-orange);
        margin-bottom: 1rem;
        min-height: 40px;
    }

    .car-price span {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .car-btn {
        display: block;
        width: 100%;
        padding: 0.5rem;
        background-color: var(--primary-orange);
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: background-color 0.3s;
        margin-top: auto;
    }

    .car-btn:hover {
        background-color: var(--primary-dark-orange);
    }

    /* Filter Styles */
    /* ===========================
   OPTION 3 – HORIZONTAL CAPSULE FILTER
=========================== */

    .filter-capsule-wrapper {
        max-width: 1200px;
        margin: 0.5rem auto 3rem auto;
        padding: 0 2rem;
        position: relative;
        z-index: 999;
        transition: box-shadow 0.3s ease;
    }

    /* Sticky capsule behavior */
    /* .filter-capsule-wrapper {
    
} */

    .filter-capsule-form {
        background: #ffffff;
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-radius: 999px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        flex-wrap: wrap;
    }

    .capsule-field {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 160px;
    }

    .capsule-field label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        padding-left: 0.25rem;
    }

    .capsule-field input,
    .capsule-field select {
        border: none;
        background: #f9fafb;
        padding: 0.65rem 0.9rem;
        border-radius: 999px;
        font-size: 0.9rem;
        min-height: 42px;
    }

    .capsule-field input:focus,
    .capsule-field select:focus {
        outline: none;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
    }

    /* Filter Button */
    .capsule-btn {
        background: linear-gradient(135deg,
                var(--primary-orange),
                var(--primary-dark-orange));
        color: white;
        border: none;
        padding: 0 2.25rem;
        height: 42px;
        border-radius: 999px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.25s ease;
    }

    .capsule-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
    }

    /* Responsive */
    @media (max-width: 900px) {
        .filter-capsule-form {
            border-radius: 20px;
        }

        .capsule-field {
            flex: 1 1 100%;
        }

        .capsule-btn {
            width: 100%;
        }
    }

    .capsule-actions {
        display: flex;
        gap: 0.75rem;
    }

    .capsule-clear {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        padding: 0 1.5rem;
        border-radius: 999px;
        background: #f3f4f6;
        color: #374151;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .capsule-clear:hover {
        background: #e5e7eb;
    }

    /* Add shadow when stuck */
    .filter-capsule-wrapper.is-sticky {
        box-shadow: 0 18px 35px rgba(0, 0, 0, 0.18);
    }



    /* CUSTOM DASHBOARD STYLES */
    .dashboard-link {
        text-decoration: none;
        color: inherit;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    /* ===== Spec Badges ===== */
    .car-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .spec-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    /* Transmission styles */
    .spec-badge.transmission {
        background-color: #eef2ff;
        color: #3730a3;
        border-color: #c7d2fe;
    }

    /* Seating styles */
    .spec-badge.seat {
        background-color: #ecfeff;
        color: #155e75;
        border-color: #a5f3fc;
    }

    /* Color badge */
    .spec-badge.color {
        background-color: #f8fafc;
    }

    .spec-badge .dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1.5px solid #d1d5db;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
    }
    </style>
</head>

<body>
    @include('components.header')

    @auth
    @php
    $currentCustomer = \App\Models\Customer::where('userID', auth()->id())->first();

    $wallet = $currentCustomer ? \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID',
    $currentCustomer->customerID)->first() : null;
    $loyalty = $currentCustomer ? \Illuminate\Support\Facades\DB::table('loyaltycard')->where('customerID',
    $currentCustomer->customerID)->first() : null;

    // Read columns directly
    $outstanding = $wallet ? $wallet->outstanding_amount : 0.00;
    $stamps = $loyalty ? $loyalty->total_stamps : 0;
    @endphp

    <section style="padding: 1.5rem 2rem; background-color: #fff1f2;">
        <div class="hero-container">
            <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">

                <a href="{{ route('wallet.show') }}" class="feature-card dashboard-link"
                    style="background: white; border-left: 5px solid var(--primary-orange); text-align: left; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="feature-icon" style="font-size: 1.5rem; margin-bottom: 0;">💳</div>
                        <h4 style="margin: 0; font-size: 1rem;">My Wallet</h4>
                        <p style="margin: 0; font-size: 0.85rem;">Click for details</p>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.85rem; color: #666;">Outstanding</span>
                        <h3
                            style="margin: 0; color: {{ $outstanding > 0 ? 'var(--primary-orange)' : 'var(--success-green)' }}; font-size: 1.5rem;">
                            RM {{ number_format($outstanding, 2) }}
                        </h3>
                    </div>
                </a>

                <a href="{{ route('loyalty.show') }}" class="feature-card dashboard-link"
                    style="background: white; border-left: 5px solid var(--success-green); text-align: left; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="feature-icon" style="font-size: 1.5rem; margin-bottom: 0;">🎁</div>
                        <h4 style="margin: 0; font-size: 1rem;">Loyalty Card</h4>
                        <p style="margin: 0; font-size: 0.85rem;">View Rewards</p>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 0.85rem; color: #666;">Stamps Earned</span>
                        <h3 style="margin: 0; color: #333; font-size: 1.5rem;">
                            {{ $stamps }} <span style="font-size: 1rem; color: #999;">/ 48</span>
                        </h3>
                    </div>
                </a>

            </div>
        </div>
    </section>
    @endauth
    <section class="hero">
        <div class="hero-container">
            <h2>Love your ride? Get rewarded</h2>
            <p>Leave a quick review and receive an exclusive rental voucher for your next trip</p>
            <!-- <a href="{{ route('home') }}" class="hero-btn">View all cars</a> -->
        </div>
    </section>

    <section>
        <h3>Why Choose HASTA?</h3>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h4>Availability</h4>
                <p>Wide selection of vehicles available 24/7 for your convenience</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✓</div>
                <h4>Comfort</h4>
                <p>Clean, well-maintained cars with modern amenities</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h4>Savings</h4>
                <p>Competitive pricing with special discounts for loyalty members</p>
            </div>
        </div>
    </section>

    <section>
<<<<<<< HEAD
        <div class="filter-capsule-wrapper">
            <form method="GET" action="{{ route('home') }}" class="filter-capsule-form" id="filterForm">

                <div class="capsule-field">
                    <label>Pick-up Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}">
=======
       <div class="filter-capsule-wrapper">
<form method="GET"
      action="{{ route('home') }}#carsGrid"
      class="filter-capsule-form"
      id="filterForm">

        <div class="capsule-field">
            <label>Pick-up Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>

        <div class="capsule-field">
            <label>Return Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>

        <div class="capsule-field">
            <label>Vehicle</label>
            <select name="vehicleType">
                <option value="">All Vehicles</option>
                @foreach ($vehicleTypes as $type)
                    <option value="{{ $type }}" {{ request('vehicleType') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="capsule-field">
            <label>Brand</label>
            <select name="brand">
                <option value="">All Brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                        {{ $brand }}
                    </option>
                @endforeach
            </select>
        </div>

<a href="{{ route('home') }}#carsGrid" class="capsule-clear" onclick="sessionStorage.removeItem('filterScrollY')">
    Clear
    </a>


    <button type="submit" class="capsule-btn">
    Filter
</button>


</div>


    </form>
</div>


    <div id="carsGrid">

        <div class="cars-grid">
            @forelse($cars as $car)
            <div class="car-card">
                @php
                $imageName = strtolower($car->vehicle_brand . '-' . $car->vehicle_model);
                $imageName = preg_replace('/[^a-z0-9]+/i', '-', $imageName);
                $imageName = trim($imageName, '-');
                $imageName .= '.png';

                $imagePath = public_path('images/cars/browse/' . $imageName);
                @endphp

            
                <div class="car-image">
                    @if(file_exists($imagePath))
                    <img src="{{ asset('images/cars/browse/' . $imageName) }}"
                        alt="{{ $car->vehicle_brand }} {{ $car->vehicle_model }}">
                    @else
                    <img src="{{ asset('images/cars/browse/default.png') }}" alt="Default car">
                    @endif
>>>>>>> 396c177df36263a5c474695b465ee7259e624388
                </div>

                <div class="capsule-field">
                    <label>Return Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}">
                </div>

                <div class="capsule-field">
                    <label>Vehicle</label>
                    <select name="vehicleType">
                        <option value="">All Vehicles</option>
                        @foreach ($vehicleTypes as $type)
                        <option value="{{ $type }}" {{ request('vehicleType') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="capsule-field">
                    <label>Brand</label>
                    <select name="brand">
                        <option value="">All Brands</option>
                        @foreach ($brands as $brand)
                        <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                            {{ $brand }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="capsule-actions">
                    <a href="{{ route('home') }}" class="capsule-clear"
                        onclick="sessionStorage.removeItem('filterScrollY')">
                        Clear
                    </a>


                    <button type="submit" class="capsule-btn">
                        Filter
                    </button>


                </div>


            </form>
        </div>


        <div id="carsGrid">

            <div class="cars-grid">
                @forelse($cars as $car)
                <div class="car-card">
                    @php
                    $imageName = strtolower($car->vehicle_brand . '-' . $car->vehicle_model);
                    $imageName = preg_replace('/[^a-z0-9]+/i', '-', $imageName);
                    $imageName = trim($imageName, '-');
                    $imageName .= '.png';

                    $imagePath = public_path('images/cars/browse/' . $imageName);
                    @endphp


                    <div class="car-image">
                        @if(file_exists($imagePath))
                        <img src="{{ asset('images/cars/browse/' . $imageName) }}"
                            alt="{{ $car->vehicle_brand }} {{ $car->vehicle_model }}">
                        @else
                        <img src="{{ asset('images/cars/browse/default.png') }}" alt="Default car">
                        @endif
                    </div>

                    <div class="car-content">
                        <h4>{{ $car->vehicle_brand }} {{ $car->vehicle_model }}</h4>
                        <p class="car-type">{{ $car->vehicleType }}</p>
                        <div class="car-specs">

                            {{-- Transmission + Seats (only if this vehicle has a car record) --}}
                            @if ($car->car)
                            <span class="spec-badge transmission">
                                {{ $car->car->transmission ?? 'N/A' }}
                            </span>

                            <span class="spec-badge seat">
                                {{ $car->car->seating_capacity ?? 'N/A' }} seats
                            </span>
                            @endif

                            {{-- Color (all vehicles) --}}
                            <span class="spec-badge color">
                                <span class="dot" style="background-color: {{ $car->color ?? '#cccccc' }}">
                                </span>
                                {{ $car->color ?? 'N/A' }}
                            </span>

                        </div>

                        <p class="car-price">RM {{ $car->rental_price }} <span>/day</span></p>
                        <a href="{{ route('vehicles.show', $car->vehicleID) }}" class="car-btn">Book Now</a>
                    </div>
                </div>
                @empty
                <p style="text-align:center;">No cars available at the moment.</p>
                @endforelse
            </div>
        </div>
    </section>

    @include('components.footer')






</body>

</html>