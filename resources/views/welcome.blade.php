<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HASTA Travel - Car Rental System</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
    html {
        font-size: 12px;
    }

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
        padding-bottom: 3rem;
    }

    .hero-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem;
    }

    .hero-text {
        flex: 1;
        min-width: 280px;
    }

    .hero-loyalty-card {
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .hero-container {
            flex-direction: column !important;
            text-align: center;
        }
        .hero-loyalty-card {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }
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

    /* Filter Capsule */
    .filter-capsule-wrapper {
        max-width: 1200px;
        margin: 0 auto 3rem auto;
        padding: 0 2rem;
        position: relative;
        z-index: 999;
        transition: box-shadow 0.3s ease;
    }

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

    .capsule-btn {
        background: linear-gradient(135deg, var(--primary-orange), var(--primary-dark-orange));
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

    /* Cars Grid - List Style */
    .cars-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .car-card {
        width: 100%;
        display: flex;
        flex-direction: row;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .car-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }

    .car-image {
        height: 100px;
        width: 140px;
        min-width: 140px;
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

    .car-image img {
        width: 100%;
        height: 100%;
        transform: scale(1.1);
        object-fit: contain;
        object-position: center;
    }

    .car-content {
        padding: 1rem 1.5rem;
        display: flex;
        flex-direction: row;
        flex: 1;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
    }

    .car-info-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .car-info-right {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .car-content h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: #1f2937;
        line-height: 1.3;
    }

    .car-type {
        color: #6b7280;
        margin-bottom: 0.25rem;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .car-id {
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .car-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .car-details-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .car-datetime {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 180px;
    }

    .datetime-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .datetime-icon {
        font-size: 1rem;
        color: #9ca3af;
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

    .spec-badge.transmission {
        background-color: #eef2ff;
        color: #3730a3;
        border-color: #c7d2fe;
    }

    .spec-badge.seat {
        background-color: #ecfeff;
        color: #155e75;
        border-color: #a5f3fc;
    }

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

    .car-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-orange);
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 120px;
    }

    .car-price span {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 400;
    }

    .payment-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #d1fae5;
        color: #065f46;
    }

    .payment-status.unpaid {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .car-btn {
        display: inline-block;
        padding: 0.5rem 1.5rem;
        background-color: transparent;
        color: #3b82f6;
        text-align: center;
        text-decoration: none;
        border-radius: 0.375rem;
        transition: background-color 0.3s;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .car-btn:hover {
        background-color: #eff6ff;
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

        .car-card {
            flex-direction: column;
        }

        .car-image {
            width: 100%;
            height: 150px;
        }

        .car-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .car-info-right {
            width: 100%;
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .car-price {
            text-align: left;
        }
    }
    </style>
</head>

<body>
    @include('components.header')

    {{-- Display flash messages --}}
    @if(session('error'))
        <div style="max-width: 1280px; margin: 1rem auto; padding: 0 1rem;">
            <div style="background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; padding: 1rem; border-radius: 8px; font-weight: 500;">
                ⚠️ {{ session('error') }}
            </div>
        </div>
    @endif
    @if(session('success'))
        <div style="max-width: 1280px; margin: 1rem auto; padding: 0 1rem;">
            <div style="background: #d1fae5; border: 1px solid #059669; color: #065f46; padding: 1rem; border-radius: 8px; font-weight: 500;">
                ✓ {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div style="max-width: 1280px; margin: 1rem auto; padding: 0 1rem;">
            <div style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 1rem; border-radius: 8px; font-weight: 500;">
                ⚠️ {{ session('warning') }}
            </div>
        </div>
    @endif

    <section class="hero">
        <div class="hero-container" style="display: flex; justify-content: space-between; align-items: center; gap: 2rem; flex-wrap: wrap;">
            <div class="hero-text">
                <h2>Your Loyalty, Rewarded</h2>
                <p>Earn 1 stamp for every completed booking. Collect 5 stamps to claim a discount on your next rental.</p>
                @auth
                    @php
                        $customerForBtn = auth()->user()->customer;
                        $loyaltyCardForBtn = $customerForBtn ? \DB::table('loyaltycard')->where('customerID', $customerForBtn->customerID)->first() : null;
                        $stampsForBtn = $loyaltyCardForBtn->total_stamps ?? 0;
                        $canClaim = $stampsForBtn >= 5;
                    @endphp
                    @if($canClaim)
                        <a href="{{ route('loyalty.claim') }}" class="hero-btn" onclick="return confirm('Claim your 10% discount voucher? This will use 5 stamps.')"> Claim Discount</a>
                    @else
                        <span class="hero-btn" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;"> Claim Discount ({{ $stampsForBtn }}/5 stamps)</span>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="hero-btn">Login to Earn Stamps</a>
                @endauth
            </div>
            
            @auth
                @php
                    $customer = auth()->user()->customer;
                    $loyaltyCard = $customer ? \DB::table('loyaltycard')->where('customerID', $customer->customerID)->first() : null;
                    $stamps = $loyaltyCard->total_stamps ?? 0;
                    $percentage = min(($stamps / 5) * 100, 100);
                @endphp
                <div class="hero-loyalty-card">
                    <div style="background: linear-gradient(135deg, #b45309, #f59e0b, #fbbf24); border-radius: 15px; padding: 1.5rem; min-width: 280px; color: white; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span style="font-weight: 600; font-size: 0.9rem;">🏆 HASTA LOYALTY</span>
                            <span style="background: white; color: #b45309; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Gold Member</span>
                        </div>
                        <div style="text-align: center; margin: 1.5rem 0;">
                            <div style="font-size: 3rem; font-weight: 700; line-height: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $stamps }}</div>
                            <div style="font-size: 0.85rem; opacity: 0.95;">Total Stamps</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.4); border-radius: 10px; height: 10px; overflow: hidden;">
                            <div style="background: white; height: 100%; width: {{ $percentage }}%; border-radius: 10px; transition: width 0.5s ease;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.95;">
                            <span>0</span>
                            <span> 5 stamps = 10% discount</span>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </section>

    <section>
        <div class="filter-capsule-wrapper">
            <form method="GET" action="{{ route('home') }}#carsGrid" class="filter-capsule-form" id="filterForm">

                <div class="capsule-field">
                    <label>Pick-up Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" autocomplete="off">
                </div>

                <div class="capsule-field">
                    <label>Return Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" autocomplete="off">
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

                <a href="{{ route('home') }}#carsGrid" class="capsule-clear"
                    onclick="sessionStorage.removeItem('filterScrollY')">
                    Clear
                </a>

                <button type="submit" class="capsule-btn">
                    Filter
                </button>

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
                        <img src="{{ asset('images/cars/browse/default.png') }}" alt="Car">
                        @endif
                    </div>

                    <div class="car-content">
                        <div class="car-info-left">
                            <div>
                                <h4>{{ $car->vehicle_brand }} {{ $car->vehicle_model }}</h4>
                                <p class="car-type">{{ $car->vehicleType }}</p>
                            </div>

                            <div class="car-specs">
                                @if ($car->car)
                                <span class="spec-badge transmission">
                                    {{ $car->car->transmission }}
                                </span>
                                <span class="spec-badge seat">
                                    {{ $car->car->seating_capacity }} seats
                                </span>
                                @endif

                                <span class="spec-badge color">
                                    <span class="dot" style="background-color: {{ $car->color ?? '#ccc' }}"></span>
                                    {{ $car->color ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="car-info-right">
                            @if(request('start_date') && request('end_date'))
                            <div class="car-datetime">
                                <div class="datetime-item">
                                    <span class="datetime-icon">📅</span>
                                    <div>
                                        <div style="font-weight: 600; color: #374151;">Pickup</div>
                                        <div>{{ \Carbon\Carbon::parse(request('start_date'))->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="datetime-item">
                                    <span class="datetime-icon">📅</span>
                                    <div>
                                        <div style="font-weight: 600; color: #374151;">Return</div>
                                        <div>{{ \Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}</div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="car-price">
                                <span class="payment-status">Available</span>
                                <div>
                                    MYR {{ number_format($car->rental_price, 2) }}

                                </div>
                            </div>

                            <a href="{{ route('vehicles.show', [
                                        'id' => $car->vehicleID, 
                                        'start_date' => request('start_date'), 
                                        'end_date' => request('end_date')
                                    ]) }}" class="car-btn">
                                View
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <p style="text-align:center; padding: 2rem;">No cars available.</p>
                @endforelse
            </div>
        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('input[name="start_date"]', {
            minDate: 'today',
            dateFormat: 'Y-m-d',
            allowInput: true
        });

        flatpickr('input[name="end_date"]', {
            minDate: 'today',
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });
    </script>

    @include('components.footer')

</body>

</html>