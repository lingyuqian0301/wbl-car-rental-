<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Hasta Admin</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('image/favicon.jpg') }}">

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --admin-red: #dc2626;
            --admin-red-dark: #991b1b;
            --admin-red-light: #fee2e2;
            --sidebar-width: 200px;
            --topbar-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--admin-red-dark) 0%, var(--admin-red) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h4 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .sidebar-header p {
            font-size: 0.75rem;
            opacity: 0.9;
            margin: 5px 0 0 0;
        }

        .sidebar-menu {
            padding: 10px 0;
        }

        .menu-item {
            padding: 0;
        }

        .menu-item > a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 0.85rem;
        }

        .menu-item > a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .menu-item.active > a {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
            font-weight: 600;
        }

        .menu-item.has-submenu > a {
            cursor: default;
        }

        .menu-item.has-submenu > a:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .menu-item i {
            width: 18px;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .submenu {
            background: rgba(0, 0, 0, 0.2);
            padding: 3px 0;
            display: block;
        }

        .submenu-item {
            padding: 0;
        }

        .submenu-item a {
            display: flex;
            align-items: center;
            padding: 8px 15px 8px 40px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.8rem;
        }

        .submenu-item a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 45px;
        }

        .submenu-item.active a {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 500;
        }

        .submenu-item i {
            width: 16px;
            margin-right: 8px;
            font-size: 0.8rem;
        }

        /* Top Bar Styles */
        .admin-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 2px solid var(--admin-red);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-link {
            color: #333;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .topbar-link:hover {
            background: var(--admin-red-light);
            color: var(--admin-red);
        }

        .topbar-link.active {
            background: var(--admin-red);
            color: white;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: #666;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: var(--admin-red-light);
            color: var(--admin-red);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--admin-red);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            background: var(--admin-red-light);
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--admin-red);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .profile-name {
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        /* Main Content Area */
        .admin-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }

            .admin-topbar {
                left: 0;
            }

            .admin-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-shield-check"></i> Admin Panel</h4>
            <p>Hasta Car Rental</p>
        </div>
        <nav class="sidebar-menu">
            <!-- Main -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-speedometer2"></i>
                    <span>Main</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </div>
                </div>
            </div>

            <!-- Bookings -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-list-ul"></i> Reservation</a>
                    </div>
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-calendar3"></i> Calendar</a>
                    </div>
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-x-circle"></i> Cancellation</a>
                    </div>
                </div>
            </div>

            <!-- Manage -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.manage.*') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-people"></i>
                    <span>Manage</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-person"></i> Client</a>
                    </div>
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-building"></i> Leasing</a>
                    </div>
                </div>
            </div>

            <!-- Category -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.category.*') || request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-grid"></i>
                    <span>Category</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.cars') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.cars') }}"><i class="bi bi-car-front"></i> Car</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.motorcycles') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.motorcycles') }}"><i class="bi bi-bicycle"></i> Motorcycle</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.others') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.others') }}"><i class="bi bi-box"></i> Other</a>
                    </div>
                </div>
            </div>

            <!-- Billing and Account -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.billing.*') || request()->routeIs('admin.payments.*') || request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Billing and Account</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <a href="#"><i class="bi bi-file-earmark-text"></i> Invoices</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payments.index') }}"><i class="bi bi-credit-card"></i> Payment</a>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <a>
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-calendar-range"></i> Rentals</a>
                    </div>
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-bar-chart"></i> Charts</a>
                    </div>
                    <div class="submenu-item">
                        <a href="#"><i class="bi bi-cash-stack"></i> Finance</a>
                    </div>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <a href="{{ route('admin.dashboard') }}" class="topbar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Latest
            </a>
            <a href="{{ route('dashboard') }}" class="topbar-link">
                <i class="bi bi-person-circle"></i> Customer
            </a>
            <a href="#" class="topbar-link">
                <i class="bi bi-calendar-event"></i> Calendar
            </a>
        </div>
        <div class="topbar-right">
            <button class="notification-btn" type="button">
                <i class="bi bi-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <div class="profile-dropdown">
                <button class="profile-btn" type="button" id="profileDropdown" data-bs-toggle="dropdown">
                    <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <span class="profile-name">{{ Auth::user()->name }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-content">
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
