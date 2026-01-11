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

        /* Notification Styles */
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
            padding: 10px 15px;
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
            <a href="<?php echo e(route('runner.calendar')); ?>" class="topbar-link <?php echo e(request()->routeIs('runner.calendar') ? 'active' : ''); ?>">
                <i class="bi bi-calendar-event"></i> Calendar
            </a>
        </div>
        <div class="topbar-right">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="notification-btn" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
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
                        <a class="dropdown-item text-center py-2" href="<?php echo e(route('runner.notifications')); ?>">
                            <i class="bi bi-list-ul"></i> View All Notifications
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Profile Dropdown -->
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
    
    <script>
        // Get notification icon based on type
        function getNotificationIcon(type) {
            const icons = {
                'new_pickup_task': '<i class="bi bi-truck text-primary"></i>',
                'new_return_task': '<i class="bi bi-arrow-return-left text-purple" style="color: #8b5cf6;"></i>',
                'task_updated': '<i class="bi bi-pencil-square text-warning"></i>',
            };
            return icons[type] || '<i class="bi bi-bell text-secondary"></i>';
        }

        // Get notification type label
        function getNotificationTypeLabel(type) {
            const labels = {
                'new_pickup_task': 'Pickup Task',
                'new_return_task': 'Return Task',
                'task_updated': 'Task Updated',
            };
            return labels[type] || 'Notification';
        }

        // Load notifications
        function loadRunnerNotifications() {
            fetch('<?php echo e(route("runner.notifications.unread-count")); ?>')
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
                        if (count > 0) {
                            headerBadge.className = 'badge bg-danger';
                        } else {
                            headerBadge.className = 'badge bg-secondary';
                        }
                    }
                })
                .catch(error => console.error('Error loading notification count:', error));

            fetch('<?php echo e(route("runner.notifications.dropdown-list")); ?>')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('notificationList');
                    
                    if (list) {
                        if (data.notifications && data.notifications.length > 0) {
                            list.innerHTML = data.notifications.map(notif => `
                                <a class="dropdown-item notification-item ${!notif.is_read ? 'unread' : ''}" 
                                   href="<?php echo e(route('runner.notifications')); ?>" 
                                   style="white-space: normal;">
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
                                            <div class="notification-message" style="font-size: 0.85rem; line-height: 1.3;">
                                                ${notif.message}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            `).join('');
                        } else {
                            list.innerHTML = '<div class="px-3 py-4 text-muted text-center"><i class="bi bi-bell-slash" style="font-size: 1.5rem;"></i><p class="mb-0 mt-2">No notifications</p></div>';
                        }
                    }
                })
                .catch(error => console.error('Error loading notifications:', error));
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadRunnerNotifications);

        // Refresh every 30 seconds
        setInterval(loadRunnerNotifications, 30000);
    </script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\myportfolio\resources\views/layouts/runner.blade.php ENDPATH**/ ?>