<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Runner Dashboard'); ?> - Hasta Travel</title>
    <link rel="icon" type="image/jpeg" href="<?php echo e(asset('image/favicon.jpg')); ?>">

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

        .menu-item i {
            width: 18px;
            margin-right: 10px;
            font-size: 0.9rem;
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

        /* Header Box - Same style as admin dashboard */
        .header-box {
            background: linear-gradient(135deg, var(--admin-red) 0%, var(--admin-red-dark) 100%);
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

        /* Cards - Same style as admin */
        .runner-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .card-header-green {
            background: var(--admin-red);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }

        /* Table - Same style as admin */
        .runner-table {
            width: 100%;
            margin-bottom: 0;
        }

        .runner-table thead th {
            background: var(--admin-red-light);
            color: var(--admin-red-dark);
            font-weight: 600;
            padding: 12px 15px;
            border-bottom: 2px solid var(--admin-red);
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

        <?php echo $__env->yieldPushContent('styles'); ?>
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <nav class="sidebar-menu">
            <!-- Dashboard -->
            <div class="menu-item <?php echo e(request()->routeIs('runner.dashboard') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('runner.dashboard')); ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            
            <!-- Task List -->
            <div class="menu-item <?php echo e(request()->routeIs('runner.tasks') ? 'active' : ''); ?>">
                <a href="<?php echo e(route('runner.tasks')); ?>">
                    <i class="bi bi-list-task"></i>
                    <span>Task List</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <div class="topbar-logo">
                <h1>HASTA</h1>
                <span>Travel</span>
            </div>
            <a href="<?php echo e(route('runner.dashboard')); ?>" class="topbar-link <?php echo e(request()->routeIs('runner.dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-graph-up-arrow"></i> Latest
            </a>
            <a href="<?php echo e(route('runner.tasks')); ?>" class="topbar-link <?php echo e(request()->routeIs('runner.tasks') ? 'active' : ''); ?>">
                <i class="bi bi-calendar-event"></i> Calendar
            </a>
        </div>
        <div class="topbar-right">
            <div class="profile-dropdown">
                <button class="profile-btn" type="button" id="profileDropdown" data-bs-toggle="dropdown">
                    <div class="profile-avatar"><?php echo e(strtoupper(substr(Auth::user()->name ?? 'R', 0, 1))); ?></div>
                    <span class="profile-name"><?php echo e(Auth::user()->name ?? 'Runner'); ?></span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i class="bi bi-person"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/layouts/runner.blade.php ENDPATH**/ ?>