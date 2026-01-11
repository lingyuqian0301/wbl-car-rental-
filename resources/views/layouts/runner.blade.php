<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Runner Dashboard') - HASTA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --runner-primary: #059669;
            --runner-primary-light: #d1fae5;
            --runner-primary-dark: #047857;
            --runner-secondary: #10b981;
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f3f4f6;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .runner-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--runner-primary) 0%, var(--runner-primary-dark) 100%);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand img {
            max-width: 150px;
            height: auto;
        }
        
        .sidebar-brand h4 {
            color: white;
            margin-top: 10px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }
        
        .menu-item i {
            font-size: 1.2rem;
        }
        
        /* Top Bar */
        .runner-topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
        }
        
        .topbar-title {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }
        
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--runner-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .user-role {
            color: #666;
            font-size: 0.8rem;
        }
        
        .btn-logout {
            background: var(--runner-primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: var(--runner-primary-dark);
            color: white;
        }
        
        /* Main Content */
        .runner-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
        }
        
        /* Header Box */
        .header-box {
            background: linear-gradient(135deg, var(--runner-primary) 0%, var(--runner-secondary) 100%);
            border-radius: 12px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
        }
        
        .header-box h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .header-box p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .header-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .header-stat {
            text-align: center;
        }
        
        .header-stat-value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .header-stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        /* Cards */
        .runner-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .card-header-green {
            background: var(--runner-primary);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        /* Table */
        .runner-table {
            width: 100%;
            margin-bottom: 0;
        }
        
        .runner-table thead th {
            background: var(--runner-primary-light);
            color: var(--runner-primary-dark);
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid var(--runner-primary);
            font-size: 0.9rem;
        }
        
        .runner-table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .runner-table tbody tr:hover {
            background: #f9fafb;
        }
        
        /* Badges */
        .badge-pickup {
            background: #3b82f6;
            color: white;
        }
        
        .badge-return {
            background: #8b5cf6;
            color: white;
        }
        
        .badge-done {
            background: #10b981;
            color: white;
        }
        
        .badge-upcoming {
            background: #f59e0b;
            color: white;
        }
        
        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        @stack('styles')
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="runner-sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo-white.png') }}" alt="HASTA Logo" onerror="this.style.display='none'">
            <h4>Runner Portal</h4>
        </div>
        <nav class="sidebar-menu">
            <a href="{{ route('runner.dashboard') }}" class="menu-item {{ request()->routeIs('runner.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('runner.tasks') }}" class="menu-item {{ request()->routeIs('runner.tasks') ? 'active' : '' }}">
                <i class="bi bi-list-task"></i>
                <span>Task List</span>
            </a>
        </nav>
    </aside>
    
    <!-- Top Bar -->
    <header class="runner-topbar">
        <div class="topbar-title">
            @yield('page-title', 'Dashboard')
        </div>
        <div class="topbar-user">
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name ?? 'Runner' }}</div>
                <div class="user-role">Runner</div>
            </div>
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'R', 0, 1)) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="runner-content">
        @yield('content')
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

