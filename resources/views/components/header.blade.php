    <header>
        <style>
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
            color: var(--primary-orange);
        }

        .logo span {
            color: #6b7280;
            margin-left: 0.5rem;
        }

        nav {
            display: block;
        }

        nav a {
            color: #374151;
            text-decoration: none;
            margin: 0 1.5rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: var(--primary-orange);
        }

        .header-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--primary-orange);
            color: white;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: background-color 0.3s;
        }

        .header-btn:hover {
            background-color: var(--primary-dark-orange);
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

        </style>
        <div class="header-container">
            <div class="logo">
                <h1>HASTA</h1>
                <span>Travel</span>
            </div>
            <nav>
                <a href="{{ route('home') }}">Home</a>
                @auth
                    <a href="{{ route('bookings.index') }}">View Bookings</a>
                    <a href="#">Wallet Transaction</a>
                    <a href="#">Loyalty Card</a>
                @endauth
                <a href="#">Contact Us</a>
            </nav>
            <div>
                @auth
                    <a href="{{ route('profile.edit') }}" class="header-btn">{{ Auth::user()->name }}</a>
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="header-btn">Login</a>
                @endauth
            </div>
        </div>

    </header>
