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

        /* Header Styles */
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
            color: #dc2626;
        }

        .logo span {
            color: #6b7280;
            margin-left: 0.5rem;
        }

        nav {
            display: none;
        }

        nav a {
            color: #374151;
            text-decoration: none;
            margin: 0 1.5rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #dc2626;
        }

        .header-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: background-color 0.3s;
        }

        .header-btn:hover {
            background-color: #b91c1c;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(to right, #dc2626, #b91c1c);
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
            background-color: white;
            color: #dc2626;
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
            max-width: 1280px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        section h3 {
            font-size: 1.875rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .feature-card {
            background-color: #f9fafb;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .feature-card h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            color: #6b7280;
        }

        /* Cars Grid */
        .cars-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .car-card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .car-image {
            background-color: #dbeafe;
            height: 12rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #9ca3af;
        }

        .car-card.green .car-image {
            background-color: #d1fae5;
        }

        .car-card.yellow .car-image {
            background-color: #fef3c7;
        }

        .car-content {
            padding: 1.5rem;
        }

        .car-content h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .car-type {
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .car-specs {
            margin-bottom: 1rem;
        }

        .car-specs p {
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .car-specs strong {
            font-weight: 600;
        }

        .car-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 1rem;
        }

        .car-price span {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .car-btn {
            display: block;
            width: 100%;
            padding: 0.5rem;
            background-color: #dc2626;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: background-color 0.3s;
        }

        .car-btn:hover {
            background-color: #b91c1c;
        }

        /* Responsive Design */
        @media (min-width: 768px) {
            nav {
                display: flex;
            }

            .hero h2 {
                font-size: 3rem;
            }

            .hero p {
                font-size: 1.25rem;
            }

            .features-grid,
            .cars-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .hero h2 {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>HASTA</h1>
                <span>Travel</span>
            </div>
            <nav>
                <a href="#">Home</a>
                <a href="#">View Bookings</a>
                <a href="#">Wallet Transaction</a>
                <a href="#">Loyalty Card</a>
                <a href="#">Contact Us</a>
            </nav>
            <div>
                <a href="#" class="header-btn">Login</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <h2>Experience the road like never before</h2>
            <p>Discover our premium car rental service with unbeatable rates and reliable vehicles</p>
            <a href="#" class="hero-btn">View all cars</a>
        </div>
    </section>

    <!-- Features Section -->
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

    <!-- Cars Section -->
    <section>
        <h3>Our Fleet</h3>
        <div class="cars-grid">
            <!-- Perodua Axia -->
            <div class="car-card blue">
                <div class="car-image">🚗</div>
                <div class="car-content">
                    <h4>Perodua Axia 2018</h4>
                    <p class="car-type">Hatchback</p>
                    <div class="car-specs">
                        <p><strong>Transmission:</strong> Automatic</p>
                        <p><strong>Fuel:</strong> RON 95/PB 95</p>
                        <p><strong>A/C:</strong> Yes</p>
                    </div>
                    <p class="car-price">RM120 <span>/day</span></p>
                    <a href="#" class="car-btn">Book Now</a>
                </div>
            </div>

            <!-- Perodua Bezza -->
            <div class="car-card green">
                <div class="car-image">🚗</div>
                <div class="car-content">
                    <h4>Perodua Bezza 2018</h4>
                    <p class="car-type">Sedan</p>
                    <div class="car-specs">
                        <p><strong>Transmission:</strong> Automatic</p>
                        <p><strong>Fuel:</strong> RON 95/PB 95</p>
                        <p><strong>A/C:</strong> Yes</p>
                    </div>
                    <p class="car-price">RM120 <span>/day</span></p>
                    <a href="#" class="car-btn">Book Now</a>
                </div>
            </div>

            <!-- Perodua Myvi -->
            <div class="car-card yellow">
                <div class="car-image">🚗</div>
                <div class="car-content">
                    <h4>Perodua Myvi 2015</h4>
                    <p class="car-type">Sport</p>
                    <div class="car-specs">
                        <p><strong>Transmission:</strong> Automatic</p>
                        <p><strong>Fuel:</strong> RON 95/PB 95</p>
                        <p><strong>A/C:</strong> Yes</p>
                    </div>
                    <p class="car-price">RM120 <span>/day</span></p>
                    <a href="#" class="car-btn">Book Now</a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>