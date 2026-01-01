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
            padding: 5rem 2rem;
            text-align: center;
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
            margin-bottom: 0.25rem;
            color: #1f2937;
            line-height: 1.3;
        }

        .car-type {
            color: #6b7280;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .car-image img {
            width: 100%;
            height: 100%;
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
        /* Filter Section */
        .filter-section {
            background-color: #ffffff;
            padding: 0;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 2.5rem;
            overflow: hidden;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .filter-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            color: #6b7280;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }


        .filter-tab:hover {
            color: #374151;
        }

        .filter-form {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            padding: 1.5rem 2rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.95rem;
            background-color: #ffffff;
        }


        /* Time group (inline for pickup and return times) */
        .filter-time-group {
            display: flex;
            gap: 1rem;
            flex: 1;
            min-width: 300px;
        }

        .filter-time-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .filter-time-item label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        .filter-time-item input {
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.95rem;
        }

        .filter-time-item input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        /* Buttons */
        .filter-btn {
            height: 48px;
            min-width: 140px;
            padding: 0 2rem;
            background-color: var(--primary-orange);
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .filter-btn:hover {
            background-color: var(--primary-dark-orange);
        }

        .filter-tab.active {
            border-bottom-color: var(--primary-orange);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        /* Location input with icon */
        .filter-location-group {
            flex: 1.5;
            min-width: 250px;
        }

        .filter-location-group .location-input {
            display: flex;
            align-items: center;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #ffffff;
        }

        .filter-location-group .location-input::before {
            content: "📍";
            padding: 0 0.75rem;
        }

        .filter-location-group input {
            border: none !important;
            box-shadow: none !important;
            flex: 1;
            padding: 0.75rem 0.75rem 0.75rem 0 !important;
        }

        .filter-location-group input:focus {
            outline: none;
        }

        /* Checkbox for return location */
        .filter-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            width: 100%;
        }

        .filter-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .filter-checkbox label {
            margin: 0;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 400;
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
    </style>
</head>

<body>
    @include('components.header')

    @auth
        @php
            $currentCustomer = \App\Models\Customer::where('user_id', auth()->id())->first();
            
            $wallet = $currentCustomer ? \Illuminate\Support\Facades\DB::table('walletaccount')->where('customerID', $currentCustomer->customerID)->first() : null;
            $loyalty = $currentCustomer ? \Illuminate\Support\Facades\DB::table('loyaltycard')->where('customerID', $currentCustomer->customerID)->first() : null;
            
            // Read columns directly
            $outstanding = $wallet ? $wallet->outstanding_amount : 0.00;
            $stamps = $loyalty ? $loyalty->total_stamps : 0;
        @endphp

        <section style="padding: 1.5rem 2rem; background-color: #fff1f2;">
            <div class="hero-container">
                <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
                    
                    <a href="{{ route('wallet.show') }}" class="feature-card dashboard-link" style="background: white; border-left: 5px solid var(--primary-orange); text-align: left; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div class="feature-icon" style="font-size: 1.5rem; margin-bottom: 0;">💳</div>
                            <h4 style="margin: 0; font-size: 1rem;">My Wallet</h4>
                            <p style="margin: 0; font-size: 0.85rem;">Click for details</p>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.85rem; color: #666;">Outstanding</span>
                            <h3 style="margin: 0; color: {{ $outstanding > 0 ? 'var(--primary-orange)' : 'var(--success-green)' }}; font-size: 1.5rem;">
                                RM {{ number_format($outstanding, 2) }}
                            </h3>
                        </div>
                    </a>

                    <a href="{{ route('loyalty.show') }}" class="feature-card dashboard-link" style="background: white; border-left: 5px solid var(--success-green); text-align: left; display: flex; justify-content: space-between; align-items: center;">
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
            <h2>Experience the road like never before</h2>
            <p>Discover our premium car rental service with unbeatable rates and reliable vehicles</p>
            <a href="{{ route('home') }}" class="hero-btn">View all cars</a>
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
        <div class="filter-section">
            <form method="GET" action="{{ route('home') }}" class="filter-form">
                <div class="filter-group">
                    <label>Pick-up date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}">
                </div>

                <div class="filter-group">
                    <label>Return date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}">
                </div>

                <div class="filter-group">
                    <label>Brand</label>
                    <select name="brand">
                        <option value="">All Brands</option>
                        <option value="Perodua" {{ request('brand') == 'Perodua' ? 'selected' : '' }}>Perodua</option>
                        <option value="Proton" {{ request('brand') == 'Proton' ? 'selected' : '' }}>Proton</option>
                        <option value="Toyota" {{ request('brand') == 'Toyota' ? 'selected' : '' }}>Toyota</option>
                        <option value="Honda" {{ request('brand') == 'Honda' ? 'selected' : '' }}>Honda</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Car Type</label>
                    <select name="vehicle_type">
                        <option value="">All Types</option>
                        <option value="Hatchback" {{ request('vehicle_type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                        <option value="Sedan" {{ request('vehicle_type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                        <option value="SUV" {{ request('vehicle_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                        <option value="Compact" {{ request('vehicle_type') == 'Compact' ? 'selected' : '' }}>Compact</option>
                    </select>
                </div>

                <button type="submit" class="filter-btn">Filter</button>
            </form>
        </div>

        <div class="cars-grid">
            @forelse($cars as $car)
                <div class="car-card {{ $loop->iteration % 3 == 1 ? 'blue' : ($loop->iteration % 3 == 2 ? 'green' : 'yellow') }}">
                    @php
                        $imageName = strtolower(str_replace(' ', '-', $car->vehicle_brand . '-' . $car->vehicle_model)) . '.png';
                        $imagePath = public_path('images/cars/browse/' . $imageName);
                    @endphp

                    <div class="car-image">
                        @if(file_exists($imagePath))
                            <img src="{{ asset('images/cars/browse/' . $imageName) }}" alt="{{ $car->vehicle_brand }} {{ $car->vehicle_model }}">
                        @else
                            <img src="{{ asset('images/cars/browse/default.png') }}" alt="Default car">
                        @endif
                    </div>

                    <div class="car-content">
                        <h4>{{ $car->vehicle_brand }} {{ $car->vehicle_model }}</h4>
                        <p class="car-type">{{ $car->type }}</p>
                        <div class="car-specs">
                            <p><span class="spec-icon">⚙</span> {{ $car->transmission }}</p>
                            <p><span class="color-dot" style="background-color: {{ $car->color ?? '#cccccc' }};"></span> {{ $car->color ?? 'N/A' }}</p>
                        </div>
                        <p class="car-price">RM {{ $car->rental_price }} <span>/day</span></p>
                        <a href="{{ route('vehicles.show', $car->vehicleID) }}" class="car-btn">Book Now</a>
                    </div>
                </div>
            @empty
                <p style="text-align:center;">No cars available at the moment.</p>
            @endforelse
        </div>
    </section>

    @include('components.footer')
</body>
</html>