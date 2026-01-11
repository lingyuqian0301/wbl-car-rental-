<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Hasta Travel</title>
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
            padding-top: 0;
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
            display: none;
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
            cursor: pointer;
            position: relative;
        }

        .menu-item.has-submenu > a::after {
            content: '\f282';
            font-family: 'bootstrap-icons';
            position: absolute;
            right: 15px;
            transition: transform 0.3s ease;
        }

        .menu-item.has-submenu.open > a::after {
            transform: rotate(180deg);
        }

        .menu-item.has-submenu > a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item i {
            width: 18px;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .submenu {
            background: rgba(0, 0, 0, 0.2);
            padding: 3px 0;
            display: none;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }

        .menu-item.has-submenu.open .submenu {
            display: block;
            max-height: 500px;
            padding: 3px 0;
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
            z-index: 998;
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

        .topbar-logo {
            display: flex;
            align-items: center;
            margin-right: 30px;
        }

        .topbar-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-red);
            margin: 0;
        }

        .topbar-logo span {
            color: #6b7280;
            margin-left: 0.5rem;
            font-weight: 400;
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
        
        .notification-dropdown {
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .notification-dropdown .dropdown-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .notification-item {
            border-bottom: 1px solid #f1f1f1;
            transition: background 0.2s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background: #f8f9fa !important;
        }
        
        .notification-item.unread {
            background: #fff8e1 !important;
            border-left: 3px solid var(--admin-red);
        }
        
        .notification-item.unread:hover {
            background: #fff3cd !important;
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
    
    <!-- Pagination Arrow Size -->
    <style>
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .pagination .page-link svg,
        .pagination .page-link i {
            width: 0.875rem;
            height: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            <div class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Bookings -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.bookings.*') ? 'active open' : '' }}" data-menu="bookings">
                <a onclick="toggleMenu('bookings')">
                    <i class="bi bi-calendar-check"></i>
                    <span>Bookings</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.bookings.reservations') ? 'active' : '' }}">
                        <a href="{{ route('admin.bookings.reservations') }}"><i class="bi bi-list-ul"></i> Reservation</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.bookings.calendar') ? 'active' : '' }}">
                        <a href="{{ route('admin.bookings.calendar') }}"><i class="bi bi-calendar3"></i> Calendar</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.bookings.cancellation') ? 'active' : '' }}">
                        <a href="{{ route('admin.bookings.cancellation') }}"><i class="bi bi-x-circle"></i> Cancellation</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.runner.tasks') ? 'active' : '' }}">
                        <a href="{{ route('admin.runner.tasks') }}"><i class="bi bi-truck"></i> Runner task</a>
                    </div>
                    {{-- Review menu item hidden as per user request --}}
                    {{-- <div class="submenu-item {{ request()->routeIs('admin.bookings.reviews') ? 'active' : '' }}">
                        <a href="{{ route('admin.bookings.reviews') }}"><i class="bi bi-star"></i> Review</a>
                    </div> --}}
                </div>
            </div>

            <!-- Manage -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.manage.*') ? 'active open' : '' }}" data-menu="manage">
                <a onclick="toggleMenu('manage')">
                    <i class="bi bi-people"></i>
                    <span>Manage</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.manage.client') ? 'active' : '' }}">
                        <a href="{{ route('admin.manage.client') }}"><i class="bi bi-person"></i> Client</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.leasing.owner') ? 'active' : '' }}">
                        <a href="{{ route('admin.leasing.owner') }}"><i class="bi bi-building"></i> Owner</a>
                    </div>
                </div>
            </div>

            <!-- Fleet -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.vehicles.*') ? 'active open' : '' }}" data-menu="fleet">
                <a onclick="toggleMenu('fleet')">
                    <i class="bi bi-truck"></i>
                    <span>Fleet</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.cars') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.cars') }}"><i class="bi bi-car-front"></i> Car</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.motorcycles') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.motorcycles') }}"><i class="bi bi-bicycle"></i> Motorcycle</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.vehicles.others') ? 'active' : '' }}">
                        <a href="{{ route('admin.vehicles.others') }}"><i class="bi bi-box"></i> Others</a>
                    </div>
                </div>
            </div>

            <!-- Billing and Account -->
            <div class="menu-item has-submenu {{ request()->routeIs('admin.payments.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('admin.deposits.*') ? 'active open' : '' }}" data-menu="billing">
                <a onclick="toggleMenu('billing')">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Billing and Account</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.invoices.index') }}"><i class="bi bi-file-earmark-text"></i> Invoices</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.payments.index') }}"><i class="bi bi-credit-card"></i> Payment</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.deposits.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.deposits.index') }}"><i class="bi bi-wallet"></i> Deposit</a>
                    </div>
                </div>
            </div>

            <!-- Reports (Admin Only - Exclude StaffIT) -->
            @if(auth()->check() && auth()->user()->isAdmin() && !auth()->user()->isStaffIT())
            <div class="menu-item has-submenu {{ request()->routeIs('admin.reports.*') ? 'active open' : '' }}" data-menu="reports">
                <a onclick="toggleMenu('reports')">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
                <div class="submenu">
                    <div class="submenu-item {{ request()->routeIs('admin.reports.rentals') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.rentals') }}"><i class="bi bi-calendar-range"></i> Rentals</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.reports.charts') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.charts') }}"><i class="bi bi-bar-chart"></i> Charts</a>
                    </div>
                    <div class="submenu-item {{ request()->routeIs('admin.reports.finance') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.finance') }}"><i class="bi bi-cash-stack"></i> Finance</a>
                    </div>
                </div>
            </div>
            @endif
        </nav>
    </aside>

    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <div class="topbar-logo">
                <h1>HASTA</h1>
                <span>Travel</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="topbar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Latest
            </a>
            <a href="http://127.0.0.1:8000/" class="topbar-link">
                <i class="bi bi-person-circle"></i> Customer
            </a>
            <a href="{{ route('admin.topbar-calendar.index') }}" class="topbar-link {{ request()->routeIs('admin.topbar-calendar.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i> Calendar
            </a>
        </div>
        <div class="topbar-right">
            <div class="dropdown">
                <button class="notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 380px; max-height: 450px; overflow-y: auto;">
                    <li class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2">
                        <strong><i class="bi bi-bell"></i> Notifications</strong>
                        <span class="badge bg-danger" id="notificationCountHeader">0</span>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li id="notificationList">
                        <div class="px-3 py-3 text-muted text-center">
                            <i class="bi bi-hourglass-split"></i> Loading...
                        </div>
                    </li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li>
                        <a class="dropdown-item text-center py-2" href="{{ route('admin.notifications.index') }}">
                            <i class="bi bi-list-ul"></i> View All Notifications
                        </a>
                    </li>
                </ul>
            </div>
            <div class="profile-dropdown">
                <button class="profile-btn" type="button" id="profileDropdown" data-bs-toggle="dropdown">
                    <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <span class="profile-name">{{ Auth::user()->name }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i> Settings</a></li>
                    @endif
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

    <script>
        // Sidebar dropdown functionality
        function toggleMenu(menuName) {
            event.preventDefault();
            const menuItem = document.querySelector(`[data-menu="${menuName}"]`);
            const allMenuItems = document.querySelectorAll('.menu-item.has-submenu');
            
            // Close all other menus
            allMenuItems.forEach(item => {
                if (item !== menuItem) {
                    item.classList.remove('open');
                }
            });
            
            // Toggle current menu
            if (menuItem) {
                menuItem.classList.toggle('open');
            }
        }

        // Auto-open menu if current route matches
        document.addEventListener('DOMContentLoaded', function() {
            const activeMenuItems = document.querySelectorAll('.menu-item.has-submenu.active');
            activeMenuItems.forEach(item => {
                if (item.classList.contains('active')) {
                    item.classList.add('open');
                }
            });
            
            // If no menu is open but we're on a submenu page, open the parent menu
            const openMenus = document.querySelectorAll('.menu-item.has-submenu.open');
            if (openMenus.length === 0) {
                const activeSubmenu = document.querySelector('.submenu-item.active');
                if (activeSubmenu) {
                    const parentMenu = activeSubmenu.closest('.menu-item.has-submenu');
                    if (parentMenu) {
                        parentMenu.classList.add('open');
                    }
                }
            }
        });

        // Get notification icon based on type
        function getNotificationIcon(type) {
            const icons = {
                'new_booking': '<i class="bi bi-calendar-plus text-primary"></i>',
                'new_payment': '<i class="bi bi-credit-card text-success"></i>',
                'new_cancellation': '<i class="bi bi-x-circle text-danger"></i>',
                'booking_date_changed': '<i class="bi bi-calendar-event text-warning"></i>',
                'upcoming_booking_payment_incomplete': '<i class="bi bi-exclamation-triangle text-warning"></i>',
            };
            return icons[type] || '<i class="bi bi-bell text-secondary"></i>';
        }
        
        // Get notification type label
        function getNotificationTypeLabel(type) {
            const labels = {
                'new_booking': 'New Booking',
                'new_payment': 'New Payment',
                'new_cancellation': 'Cancellation',
                'booking_date_changed': 'Date Changed',
                'upcoming_booking_payment_incomplete': 'Payment Due',
            };
            return labels[type] || 'Notification';
        }

        // Load notification count and list
        function loadNotifications() {
            fetch('{{ route("admin.notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    const headerBadge = document.getElementById('notificationCountHeader');
                    const count = data.count || 0;
                    
                    if (badge) {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'flex' : 'none';
                    }
                    if (headerBadge) {
                        headerBadge.textContent = count;
                        headerBadge.style.display = count > 0 ? 'inline-block' : 'none';
                    }
                });

            fetch('{{ route("admin.notifications.dropdown-list") }}')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('notificationList');
                    
                    if (list) {
                        if (data.notifications && data.notifications.length > 0) {
                            list.innerHTML = data.notifications.map(notif => `
                                <a class="dropdown-item notification-item ${!notif.is_read ? 'unread' : ''}" href="{{ route('admin.notifications.index') }}" style="white-space: normal; padding: 10px 15px;">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="notification-icon" style="font-size: 1.2rem; margin-top: 2px;">
                                            ${getNotificationIcon(notif.type)}
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge ${!notif.is_read ? 'bg-danger' : 'bg-secondary'}" style="font-size: 0.65rem;">
                                                    ${getNotificationTypeLabel(notif.type)}
                                                </span>
                                                <small class="text-muted">${notif.created_at}</small>
                                            </div>
                                            <div class="notification-message" style="font-size: 0.85rem; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                                ${notif.message || notif.type}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            `).join('');
                        } else {
                            list.innerHTML = '<div class="px-3 py-4 text-muted text-center"><i class="bi bi-bell-slash" style="font-size: 1.5rem;"></i><p class="mb-0 mt-2">No notifications</p></div>';
                        }
                    }
                });
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadNotifications);

        // Refresh every 30 seconds
        setInterval(loadNotifications, 30000);
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
