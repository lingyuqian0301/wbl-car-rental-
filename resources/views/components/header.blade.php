@auth
@php
$currentCustomer = \App\Models\Customer::where('userID', auth()->id())->first();

$wallet = $currentCustomer
? \Illuminate\Support\Facades\DB::table('walletaccount')
->where('customerID', $currentCustomer->customerID)
->first()
: null;

$loyalty = $currentCustomer
? \Illuminate\Support\Facades\DB::table('loyaltycard')
->where('customerID', $currentCustomer->customerID)
->first()
: null;

$wallet_balance = $wallet ? $wallet->wallet_balance : 0.00;
$stamps = $loyalty ? $loyalty->total_stamps : 0;
@endphp
@endauth


<header>
    <style>
    /* Header Styles */


    header {
        background-color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        position: sticky !important;
        top: 0 !important;
        z-index: 9999 !important;
    }

    .header-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 1rem 1.5rem; /* increased padding */
        display: flex;
        align-items: center;
    }

    .logo {
        display: flex;
        align-items: center;
    }

    .logo h1 {
        font-size: 1.75rem; /* increased from 1.5rem */
        font-weight: 700;
        color: var(--primary-orange);
    }

    .logo span {
        color: #6b7280;
        margin-left: 0.5rem;
        font-size: 1.25rem; /* added size for Travel text */
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
        padding: 0.6rem 1.25rem; /* increased padding */
        background-color: var(--primary-orange);
        color: white;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: background-color 0.3s;
        font-size: 1rem; /* added font size */
        font-weight: 500;
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

   .header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}


    .header-info {
        font-size: 0.875rem;
        color: #374151;
        text-decoration: none;
        font-weight: 500;
    }

    .header-info:hover {
        color: var(--primary-orange);
    }

    .icon {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 6px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }

    /* simple inline SVG icons */
    .wallet-icon {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-4h-6a2 2 0 100 4h6v-4z'/%3E%3C/svg%3E");
    }

    .loyalty-icon {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.95a1 1 0 00.95.69h4.15c.969 0 1.371 1.24.588 1.81l-3.36 2.44a1 1 0 00-.364 1.118l1.286 3.95c.3.921-.755 1.688-1.54 1.118l-3.36-2.44a1 1 0 00-1.175 0l-3.36 2.44c-.784.57-1.838-.197-1.539-1.118l1.285-3.95a1 1 0 00-.364-1.118l-3.36-2.44c-.783-.57-.38-1.81.588-1.81h4.15a1 1 0 00.95-.69l1.286-3.95z'/%3E%3C/svg%3E");
    }

    header {
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header-top {
        padding: 0.75rem 2rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .header-bottom {
        padding: 0.75rem 2rem;
    }

    .header-status {
        display: flex;
        gap: 1.5rem;
        font-size: 0.85rem;
        color: #475569;
    }

    .header-bottom nav a {
        margin-right: 1.5rem;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .header-status {
        font-size: 0.85rem;
        color: #475569;
        white-space: nowrap;
    }
    .header-link {
    text-decoration: none;
    color: #475569;
    cursor: pointer;
}

.header-link:hover {
    color: var(--primary-orange);
    text-decoration: underline;
}

    .header-left {
    margin-right: auto; /* pushes everything else to the right */
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.nav-link {
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    font-size: 1.05rem; /* increased */
}

.nav-link:hover {
    color: var(--primary-orange);
}
.header-metric {
    display: flex;
    flex-direction: column;
    align-items: center; /* center looks cleaner in nav */
    gap: 3px;            /* slightly increased gap */
    text-decoration: none;
}


.metric-label {
    font-size: 0.9rem; /* increased from 0.8rem */
    color: #94a3b8;
    letter-spacing: 0.02em; /* reduce spacing */
    line-height: 1;         /* IMPORTANT */
}

.metric-value {
    font-size: 1rem; /* increased from 0.85rem */
    font-weight: 600;
    color: #475569;
    line-height: 1.1;       /* IMPORTANT */
}


.header-metric:hover .metric-value {
    color: var(--primary-orange);
}
/* Remove underline in ALL states */
.header-metric,
.header-metric:visited,
.header-metric:hover,
.header-metric:active {
    text-decoration: none;
}

/* Keep hover color change, but no underline */
.header-metric:hover .metric-value {
    color: var(--primary-orange);
}
.header-left {
    margin-right: auto;
}

/* NEW: force logo + Home to be side-by-side */
.logo-group {
    display: flex;
    align-items: center;
    gap: 1.25rem; /* space between logo and Home */
}

/* ensure logo itself stays horizontal */
.logo {
    display: flex;
    align-items: center;
}

.home-link {
    font-weight: 500;
    color: #374151;
    font-size: 1.1rem; /* increased */
}

.home-link:hover {
    color: var(--primary-orange);
}
/* Remove underline from HASTA logo in all states */
.logo,
.logo:visited,
.logo:hover,
.logo:active {
    text-decoration: none;
}



    </style>
    <div class="header-container">
    <div class="header-left">
    <div class="logo-group">
        <a href="{{ route('home') }}" class="logo">
            <h1>HASTA</h1>
            <span>Travel</span>
        </a>

        <a href="{{ route('home') }}" class="nav-link home-link">
            Home
        </a>
    </div>
</div>



    <div class="header-right">
        @auth
            <a href="{{ route('bookings.index') }}" class="nav-link">
                View Bookings
            </a>

        <a href="{{ route('wallet.show') }}" class="header-status header-link header-metric">
    <span class="metric-label">Deposit Balance</span>
    <span class="metric-value">RM {{ number_format($wallet_balance, 2) }}</span>
</a>

<a href="{{ route('loyalty.show') }}" class="header-status header-link header-metric">
    <span class="metric-label">Stamps</span>
    <span class="metric-value">{{ $stamps }} / 5</span>
</a>


            <a href="{{ route('profile.edit') }}" class="header-btn">
                {{ Auth::user()->name }}
            </a>
        @else
            <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
               class="header-btn">Login</a>
        @endauth
    </div>
</div>

        </div>



    </div>

</header>