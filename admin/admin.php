<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'Admin';
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'admin';
$stats = [
    'user_counts' => 0,
    'event_counts' => 0,
    'participant_counts' => 0,
    'organizer_counts' => $_SERVER['ORGANIZER_COUNTS'] ?? 0,
    'pending_organizers' => $_SERVER['PENDING_ORGANIZERS'] ?? 0,
    'total_events' => 0,
    'total_registrations' => 0,
    'total_feedback' => 0,
    'total_students' => 0,
    'total_organizers' => 0,
    'total_admins' => 0,
    'user_images' => [],
    'recent_users' => [],
    'recent_events' => [],
    'recent_feedback' => [],
    'total_users' => 0
];


// Get total counts from database
try {
    // Total students count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_students'] = $result->fetch_assoc()['count'] ?? 0;

    // Total organizers count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'organizer' AND status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_organizers'] = $result->fetch_assoc()['count'] ?? 0;

    // Total admins count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND status = 'active'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_admins'] = $result->fetch_assoc()['count'] ?? 0;

    // Pending organizers count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'organizer' AND status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['pending_organizers'] = $result->fetch_assoc()['count'] ?? 0;

    // Total events count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_events'] = $result->fetch_assoc()['count'] ?? 0;

    // Active events count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status = 'ongoing' OR status = 'upcoming'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['active_events'] = $result->fetch_assoc()['count'] ?? 0;

    // Upcoming events count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status = 'upcoming'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['upcoming_events'] = $result->fetch_assoc()['count'] ?? 0;

    // Total registrations count (excluding cancelled)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM registrations WHERE status = 'confirmed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_registrations'] = $result->fetch_assoc()['total'] ?? 0;

    // Total feedback count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM feedback");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_feedback'] = $result->fetch_assoc()['count'] ?? 0;

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

$page = $_GET['page'] ?? 'dashboard';


// Get recent users
$recent_users = [];
$stmt = $conn->prepare("
    SELECT id, username, email, role, created_at 
    FROM users 
    WHERE status = 'active'
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_users[] = $row;
}

// Get recent events
$recent_events = [];
$stmt = $conn->prepare("
    SELECT event_id, title, start_date,end_date, venue, status 
    FROM events 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_events[] = $row;
}

// Get recent feedback
$recent_feedback = [];
$stmt = $conn->prepare("
    SELECT id, email, message, submission_date, username 
    FROM feedback 
    ORDER BY submission_date DESC 
    LIMIT 5
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_feedback[] = $row;
}

// Intercept export/backup downloads before any HTML output
if (isset($_GET['page'])) {
    if ($_GET['page'] === 'export' && isset($_GET['type'])) {
        include 'export.php';
        exit();
    }
    if ($_GET['page'] === 'backup' && isset($_GET['action']) && $_GET['action'] === 'run') {
        include 'backup.php';
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CampusPulse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="admin.css?v=4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .event-card {
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .sidebar-link.active {
            background-color: #1e1b4b;
            color: white;
        }

        .sidebar-link:hover:not(.active) {
            background-color: #f1f5f9;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        /* Activity Section */
        .activity-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
            margin-top: 50px;
        }

        .activity-card {
            background: #fff;
            border: 1px solid #fff;
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
        }

        .view-all {
            color: #1e1b4b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .view-all:hover {
            color: #46cbae;
        }

        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            border-bottom: 1px solid #b3b3b3;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-content h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-content p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .feedback-comment {
            white-space: normal !important;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .activity-time {
            font-size: 11px;
            color: #64748b;
        }

        .activity-badge .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-admin {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-student {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-organizer {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-rating {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .empty-state {
            padding: 40px 24px;
            text-align: center;
            color: #64748b;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Quick Actions */
        .quick-actions {
            margin-top: 32px;
        }

        .quick-actions h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .action-btn {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            color: black;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
        }

        .action-btn:hover {
            background: #fff;
            border-color: #3b82f6;
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 24px;
        }

        .quick-action span {
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .admin-dropdown {
            position: relative;
        }

        .admin-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            transition: var(--transition);
        }

        .admin-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .username {
            font-weight: 500;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #ffffff;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            z-index: 1;
            overflow: hidden;
        }

        .admin-dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .dropdown-content a:hover {
            background-color: #f1f5f9;
        }

        .notifications-dropdown {
            width: 300px;
        }

        .profile-menu {
            width: 180px;
        }

        .admin-avatar-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding-left: 24px;
            border-left: 1px solid #e2e8f0;
        }

        .admin-avatar-btn:hover .username {
            color: #4f46e5;
        }

        .admin-role-badge {
            background: #e0e7ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
            border: 1px solid #c7d2fe;
        }

        .sidebar-admin-card {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 24px;
        }

        /* ===== ADMIN MOBILE RESPONSIVE ===== */
        .admin-mobile-sidebar-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            z-index: 40; backdrop-filter: blur(2px); display: none;
        }
        .admin-mobile-sidebar-overlay.open { display: block; }
        .admin-mobile-sidebar {
            position: fixed; top: 0; left: -280px; width: 280px; height: 100vh;
            background: white; z-index: 50; padding: 24px 20px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            transition: left 0.3s ease; overflow-y: auto;
        }
        .admin-mobile-sidebar.open { left: 0; }
        .admin-mobile-sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 10px;
            color: #475569; font-size: 14px; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .admin-mobile-sidebar-link:hover { background: #f1f5f9; color: #1e1b4b; }
        .admin-mobile-sidebar-active { background: #1e1b4b !important; color: white !important; }
        .admin-mobile-bottom-nav {
            display: none; position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #e2e8f0; z-index: 30;
            padding: 8px 0 env(safe-area-inset-bottom, 8px);
        }
        @media (max-width: 767px) {
            .admin-mobile-bottom-nav { display: block; }
            .p-8 { padding: 1rem !important; }
            .flex.h-screen > .w-64 { display: none !important; }
        }
    </style>

</head>

<body class="bg-slate-50">
    <!-- Admin Mobile Sidebar Overlay -->
    <div class="admin-mobile-sidebar-overlay" id="adminSidebarOverlay" onclick="closeAdminMobileSidebar()"></div>

    <!-- Admin Mobile Slide-in Sidebar -->
    <div class="admin-mobile-sidebar" id="adminMobileSidebar">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-3">
                <img src="../assets/clg-logo.png" alt="Logo" class="h-9 w-9 rounded-lg object-cover">
                <span class="text-lg font-bold text-indigo-900">Campus Connect</span>
            </div>
            <button onclick="closeAdminMobileSidebar()" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="space-y-1">
            <a href="?page=dashboard" class="admin-mobile-sidebar-link <?php echo $page==='dashboard'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-home"></i> Dashboard</a>
            <a href="?page=manage_users" class="admin-mobile-sidebar-link <?php echo $page==='manage_users'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-users"></i> Manage Users</a>
            <a href="?page=manage_events" class="admin-mobile-sidebar-link <?php echo $page==='manage_events'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-calendar-alt"></i> Events List</a>
            <a href="?page=registrations" class="admin-mobile-sidebar-link <?php echo $page==='registrations'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-ticket-alt"></i> Registrations</a>
            <a href="?page=feedback" class="admin-mobile-sidebar-link <?php echo $page==='feedback'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-comments"></i> Feedback</a>
            <a href="?page=notifications" class="admin-mobile-sidebar-link <?php echo $page==='notifications'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-bell"></i> Notifications</a>
            <a href="?page=profile" class="admin-mobile-sidebar-link <?php echo $page==='profile'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-user-cog"></i> Profile</a>
            <a href="?page=settings" class="admin-mobile-sidebar-link <?php echo $page==='settings'?'admin-mobile-sidebar-active':''; ?>"><i class="fas fa-cog"></i> Settings</a>
            <div class="border-t border-slate-200 my-3"></div>
            <a href="logout.php" class="admin-mobile-sidebar-link text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        <div class="w-64 bg-white border-r border-slate-200 min-h-screen p-6">
            <div class="flex items-center space-x-3 mb-8">
                <img src="../assets/clg-logo.png" alt="Logo" class="h-10 w-10 rounded-lg object-cover">
                <span class="text-xl font-bold text-indigo-900" style="white-space: nowrap;">Campus Connect</span>
            </div>

            <div class="space-y-2 mb-8">
                <a href="?page=dashboard"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'dashboard' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-home w-5 h-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="?page=manage_users"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'manage_users' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-users w-5 h-5"></i>
                    <span class="font-medium"> Manage Users</span>
                </a>
                <a href="?page=manage_events"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'manage_events' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-calendar-alt w-5 h-5"></i>
                    <span class="font-medium">Events List</span>
                </a>
                <a href="?page=registrations"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'registrations' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-ticket-alt w-5 h-5"></i>
                    <span class="font-medium">Registrations</span>
                </a>
                <a href="?page=feedback"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'feedback' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-comments w-5 h-5"></i>
                    <span class="font-medium">Feedback</span>
                </a>
                <a href="?page=notifications"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'notifications' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-bell w-5 h-5"></i>
                    <span class="font-medium">Notifications</span>
                </a>
                <a href="?page=profile"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'profile' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-user-cog w-5 h-5"></i>
                    <span class="font-medium">Profile</span>
                </a>
                <a href="?page=settings"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $page === 'settings' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-cog w-5 h-5"></i>
                    <span class="font-medium">Settings</span>
                </a>
            </div>
            <div class="pt-6 border-t border-slate-200">
                <a href="logout.php"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-5 h-5"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <div class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Hamburger (mobile only) -->
                    <button class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600" onclick="openAdminMobileSidebar()" aria-label="Open menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg md:text-2xl font-bold text-indigo-900 truncate">
                        <?php
                        switch ($page) {
                            case 'manage_users': echo "Manage Users"; break;
                            case 'manage_events': echo "Events List"; break;
                            case 'feedback': echo "Feedback"; break;
                            case 'notifications': echo "Notifications"; break;
                            case 'profile': echo "Profile"; break;
                            case 'settings': echo "Settings"; break;
                            default: echo "Dashboard";
                        }
                        ?>
                    </h1>
                </div>

                <div class="flex items-center space-x-6">
                    <!-- Current Time Display -->
                    <div class="flex items-center space-x-2 text-slate-600">
                        <i class="far fa-clock text-lg"></i>
                        <span id="currentTime" class="font-medium"></span>
                    </div>

                    <!-- Notifications Dropdown -->
                    <div class="admin-dropdown">
                        <button class="relative p-2 hover:bg-slate-100 rounded-full transition-colors"
                            id="notificationsBtn">
                            <i class="far fa-bell text-xl text-slate-600"></i>
                            <span class="notification-badge" style="display: none;">0</span>
                        </button>
                        <div class="dropdown-content notifications-dropdown">
                            <div class="p-3 border-b border-slate-100 font-semibold text-sm text-slate-700">
                                Notifications
                            </div>
                            <div class="max-h-64 overflow-y-auto" id="notificationsList">
                                <div class="p-4 text-center text-sm text-slate-500">Loading...</div>
                            </div>
                            <div class="p-2 border-t border-slate-100 text-center">
                                <a href="?page=notifications"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All
                                    Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="admin-dropdown">
                        <div class="admin-avatar-btn">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-slate-800 leading-tight">
                                    <?php echo htmlspecialchars($username); ?>
                                </p>
                                <div class="flex items-center space-x-1 justify-end mt-0.5">
                                    <i class="fas fa-user-shield text-indigo-600 text-[10px]"></i>
                                    <span class="admin-role-badge" style="font-size: 8px;">Admin</span>
                                </div>
                            </div>
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border border-indigo-200 overflow-hidden">
                                <?php if (isset($_SESSION['user_image']) && !empty($_SESSION['user_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['user_image']); ?>" alt="Admin"
                                        class="h-full w-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="dropdown-content profile-menu">
                            <div class="px-4 py-2 border-b border-slate-100 md:hidden">
                                <p class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($username); ?>
                                </p>
                                <p class="text-[10px] text-slate-500">Administrator</p>
                            </div>
                            <a href="?page=profile"><i class="fas fa-user"></i> My Profile</a>
                            <a href="?page=settings"><i class="fas fa-cog"></i> Settings</a>
                            <div class="border-t border-slate-100"></div>
                            <a href="logout.php" class="text-red-600 hover:bg-red-50"><i
                                    class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Dashboard Content -->
            <!-- Page Content -->
            <div class="p-8 flex-1 overflow-auto">
                <?php if ($page === 'dashboard'): ?>
                    <!-- Dashboard Page -->
                    <div class="space-y-6">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-900">Welcome
                                back,<?php echo htmlspecialchars($username); ?></h2>
                            <p class="text-slate-600 mt-1" id="currentDateTime"></p>
                        </div>

                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-white p-6 rounded-xl shadow border border-slate-200 stat-card">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-medium text-slate-600">Total Students</h3>
                                    <i class="fas fa-users text-indigo-900"></i>
                                </div>
                                <div class="text-3xl font-bold text-indigo-900" id="stat-students">
                                    <?php echo $stats['total_students']; ?>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-xl shadow border border-slate-200 stat-card">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-medium text-slate-600">Total Organizers</h3>
                                    <i class="fas fa-user-check text-indigo-900"></i>
                                </div>
                                <div class="text-3xl font-bold text-indigo-900" id="stat-organizers">
                                    <?php echo $stats['total_organizers']; ?>
                                </div>
                                <?php if ($stats['pending_organizers'] > 0): ?>
                                    <p class="text-sm text-yellow-600 mt-1" id="stat-pending">
                                        <?php echo $stats['pending_organizers']; ?> pending approval
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="bg-white p-6 rounded-xl shadow border border-slate-200 stat-card">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-medium text-slate-600">Total Events</h3>
                                    <i class="fas fa-calendar text-indigo-900"></i>
                                </div>
                                <div class="text-3xl font-bold text-indigo-900" id="stat-events">
                                    <?php echo $stats['total_events']; ?>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-xl shadow border border-slate-200 stat-card">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-sm font-medium text-slate-600">Total Registrations</h3>
                                    <i class="fas fa-chart-line text-indigo-900"></i>
                                </div>
                                <div class="text-3xl font-bold text-indigo-900" id="stat-registrations">
                                    <?php echo $stats['total_registrations']; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <section class="activity-section">
                        <div class="activity-card">
                            <div class="card-header">
                                <h3><i class="fas fa-users"></i> Recent Users</h3>
                                <a href="?page=manage_users" class="view-all">View All <i
                                        class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="activity-list">
                                <?php if (empty($recent_users)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <p>No users registered yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_users as $user): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <i class="fas fa-user-shield"></i>
                                                <?php elseif ($user['role'] === 'organizer'): ?>
                                                    <i class="fas fa-user-tie"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-user-graduate"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-content">
                                                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                                <span class="activity-time">Joined
                                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                                            </div>
                                            <div class="activity-badge flex items-center gap-2">
                                                <div class="flex space-x-1">
                                                    <button
                                                        onclick="window.location.href='?page=manage_users&search=<?php echo urlencode($user['username']); ?>'"
                                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"
                                                        title="View">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                    <button
                                                        onclick="window.location.href='?page=manage_users&id=<?php echo $user['id']; ?>&action=edit'"
                                                        class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition"
                                                        title="Edit">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                </div>
                                                <span class="badge badge-<?php echo $user['role']; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="activity-card">
                            <div class="card-header">
                                <h3><i class="fas fa-calendar-alt"></i> Recent Events</h3>
                                <a href="?page=manage_events" class="view-all">View All <i
                                        class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="activity-list">
                                <?php if (empty($recent_events)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-times"></i>
                                        <p>No events created yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_events as $event): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <?php if ($event['status'] === 'active'): ?>
                                                    <i class="fas fa-calendar-check"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-calendar-times"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-content">
                                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                                <p><?php echo htmlspecialchars($event['venue']); ?></p>
                                                <span
                                                    class="activity-time"><?php echo date('M j, Y', strtotime($event['start_date'])); ?></span>
                                            </div>
                                            <div class="activity-badge flex items-center gap-2">
                                                <div class="flex space-x-1">
                                                    <button
                                                        onclick="window.location.href='?page=manage_events&id=<?php echo $event['event_id']; ?>&action=view'"
                                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"
                                                        title="View">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                    <button
                                                        onclick="window.location.href='edit_events.php?id=<?php echo $event['event_id']; ?>'"
                                                        class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition"
                                                        title="Edit">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                </div>
                                                <span class="badge badge-<?php echo $event['status']; ?>">
                                                    <?php echo ucfirst($event['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="activity-card">
                            <div class="card-header">
                                <h3><i class="fas fa-comments"></i> Recent Feedback</h3>
                                <a href="?page=feedback" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                            </div>
                            <div class="activity-list">
                                <?php if (empty($recent_feedback)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-comment-slash"></i>
                                        <p>No feedback received yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_feedback as $feedback): ?>
                                        <div class="activity-item">
                                            <div class="activity-content">
                                                <h4><?php echo htmlspecialchars($feedback['username']); ?></h4>
                                                <p class="feedback-comment">
                                                    <?php echo htmlspecialchars(substr($feedback['message'], 0, 60)) . '...'; ?>
                                                </p>
                                                <span
                                                    class="activity-time"><?php echo date('M j, g:i A', strtotime($feedback['submission_date'])); ?></span>
                                            </div>
                                            <div class="activity-badge flex items-center gap-2">
                                                <div class="flex space-x-1">
                                                    <button
                                                        onclick="window.location.href='?page=feedback&search=<?php echo urlencode($feedback['username']); ?>'"
                                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"
                                                        title="View">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                    <button
                                                        onclick="window.location.href='?page=feedback&id=<?php echo $feedback['id']; ?>&action=respond'"
                                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded transition"
                                                        title="Respond">
                                                        <i class="fas fa-reply text-xs"></i>
                                                    </button>
                                                </div>
                                                <span class="badge badge-rating">
                                                    <?php echo $feedback['email']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Quick Actions -->
                    <section class="quick-actions">
                        <h3>Quick Actions</h3>
                        <div class="actions-grid">
                            <button class="action-btn" onclick="window.location.href='?page=add_user'">
                                <i class="fas fa-user-plus" style="color: #8b5cf6"></i>
                                <span>Add User</span>
                            </button>
                            <button class="action-btn" onclick="window.location.href='?page=create_event'">
                                <i class="fas fa-calendar-plus" style="color: #10b981"></i>
                                <span>Create Event</span>
                            </button>
                            <button class="action-btn" onclick="window.location.href='?page=manage_users'">
                                <i class="fas fa-users-cog" style="color: #ea580c"></i>
                                <span>Manage Users</span>
                            </button>
                            <button class="action-btn" onclick="window.location.href='?page=logs'">
                                <i class="fas fa-clipboard-list" style="color: #6b7280"></i>
                                <span>View Logs</span>
                            </button>
                            <button class="action-btn" onclick="window.location.href='?page=export'">
                                <i class="fas fa-file-export" style="color:#f59e0b"></i>
                                <span>Export Data</span>
                            </button>
                            <button class="action-btn" onclick="window.location.href='?page=backup'">
                                <i class="fas fa-database" style="color: #10b981"></i>
                                <span>Backup System</span>
                            </button>
                        </div>
                    </section>
                <?php endif; ?>



                <?php
                // Include other pages based on the 'page' parameter
                if ($page !== 'dashboard') {
                    $allowed_pages = [
                        'manage_users' => 'manage_users.php',
                        'create_event' => 'create_event.php',
                        'manage_events' => 'manage_events.php',
                        'edit_events' => 'edit_events.php',
                        'view_events' => 'view_events.php',
                        'add_user' => 'add_user.php',
                        'edit_user' => 'edit_user.php',
                        'registrations' => 'registrations.php',
                        'participants' => 'participants.php',
                        'feedback' => 'feedback.php',
                        'notifications' => 'notifications.php',
                        'profile' => 'profile.php',
                        'settings' => 'settings.php',
                        'logs' => 'logs.php',
                        'export' => 'export.php',
                        'backup' => 'backup.php'
                    ];
                    if (array_key_exists($page, $allowed_pages)) {
                        include $allowed_pages[$page];
                    } else {
                        echo "<p class='text-red-600'>Page not found.</p>";
                    }
                }
                ?>



            </div>
        </div>
    </div>
    <script>
        // Function to update current time every second
        function updateTime() {
            const now = new Date();
            const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const timeEl = document.getElementById('currentTime');
            if (timeEl) timeEl.textContent = now.toLocaleTimeString([], options);
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Function to display current date and time on dashboard
        function displayDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            const dateTimeEl = document.getElementById('currentDateTime');
            if (dateTimeEl) dateTimeEl.textContent = now.toLocaleDateString([], options);
        }
        displayDateTime();

        // Redirect to notifications page on click
        const mailElement = document.getElementById('mail');
        if (mailElement) {
            mailElement.addEventListener('click', function () {
                window.location.href = 'notifications.php';
            });
        }

        // Real-Time Stats (SSE Implementation)
        let statsEventSource = null;

        function initStatsStream() {
            if (statsEventSource) statsEventSource.close();

            statsEventSource = new EventSource('../api/stream_stats.php');

            statsEventSource.onmessage = function (event) {
                const data = JSON.parse(event.data);
                updateStatsUI(data);
            };

            statsEventSource.onerror = function () {
                console.warn('Stats SSE Connection failed. Reverting to manual fetch.');
                if (statsEventSource) statsEventSource.close();
                statsEventSource = null;
                fetchAdminStats(); // Fallback to one-time fetch or polling
            };
        }

        function updateStatsUI(data) {
            const els = {
                'stat-students': data.students,
                'stat-organizers': data.organizers,
                'stat-events': data.events,
                'stat-registrations': data.registrations
            };

            for (const [id, val] of Object.entries(els)) {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            }

            // Update pending badge
            const pendingEl = document.getElementById('stat-pending');
            if (pendingEl) {
                if (data.pending_organizers > 0) {
                    pendingEl.textContent = `${data.pending_organizers} pending approval`;
                    pendingEl.style.display = 'block';
                } else {
                    pendingEl.style.display = 'none';
                }
            } else if (data.pending_organizers > 0) {
                // If it doesn't exist but we have pending, we might want to create it or just ignore if not on dashboard
                // For now, assume it's only on dashboard
            }
        }

        function fetchAdminStats() {
            fetch('../api/dashboard_stats.php')
                .then(res => res.json())
                .then(data => updateStatsUI(data))
                .catch(err => console.error('Error fetching stats:', err));
        }

        // Initialize Stream
        initStatsStream();

        // Fallback Polling if SSE is down
        setInterval(() => {
            if (!statsEventSource) fetchAdminStats();
        }, 30000);

        // Fetch Notifications (Similar to Organizer)
        function fetchNotifications() {
            fetch('../api/fetch_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notification-badge');
                    const notificationsList = document.getElementById('notificationsList');

                    // Update Badge
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }

                    // Update List
                    if (data.notifications && data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(notif => {
                            const date = new Date(notif.created_at).toLocaleString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                hour12: true
                            });
                            html += `
                                <div class="p-3 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location.href='notifications.php'">
                                    <p class="text-sm text-slate-800 font-medium">${notif.title}</p>
                                    <p class="text-xs text-slate-500 mt-1">${notif.message}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">${date}</p>
                                </div>
                            `;
                        });
                        notificationsList.innerHTML = html;
                    } else {
                        notificationsList.innerHTML = '<div class="p-4 text-center text-sm text-slate-500">No new notifications</div>';
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
        }

        // Initialize Notification Polling
        setInterval(fetchNotifications, 30000);
        fetchNotifications();

        // Mobile sidebar functions
        function openAdminMobileSidebar() {
            document.getElementById('adminMobileSidebar').classList.add('open');
            document.getElementById('adminSidebarOverlay').classList.add('open');
        }
        function closeAdminMobileSidebar() {
            document.getElementById('adminMobileSidebar').classList.remove('open');
            document.getElementById('adminSidebarOverlay').classList.remove('open');
        }

        // Function to handle logout
        function logout() { window.location.href = 'logout.php'; }
    </script>

    <!-- Mobile Bottom Navigation -->
    <nav class="admin-mobile-bottom-nav">
        <div style="display:flex;justify-content:space-around;align-items:center;">
            <a href="?page=dashboard" style="display:flex;flex-direction:column;align-items:center;gap:3px;color:<?php echo $page==='dashboard'?'#4f46e5':'#94a3b8';?>;font-size:10px;font-weight:600;text-decoration:none;padding:4px 8px;min-width:52px;">
                <i class="fas fa-home" style="font-size:18px;"></i><span>Home</span>
            </a>
            <a href="?page=manage_users" style="display:flex;flex-direction:column;align-items:center;gap:3px;color:<?php echo $page==='manage_users'?'#4f46e5':'#94a3b8';?>;font-size:10px;font-weight:600;text-decoration:none;padding:4px 8px;min-width:52px;">
                <i class="fas fa-users" style="font-size:18px;"></i><span>Users</span>
            </a>
            <a href="?page=manage_events" style="display:flex;flex-direction:column;align-items:center;gap:3px;color:<?php echo $page==='manage_events'?'#4f46e5':'#94a3b8';?>;font-size:10px;font-weight:600;text-decoration:none;padding:4px 8px;min-width:52px;">
                <i class="fas fa-calendar-alt" style="font-size:18px;"></i><span>Events</span>
            </a>
            <a href="?page=feedback" style="display:flex;flex-direction:column;align-items:center;gap:3px;color:<?php echo $page==='feedback'?'#4f46e5':'#94a3b8';?>;font-size:10px;font-weight:600;text-decoration:none;padding:4px 8px;min-width:52px;">
                <i class="fas fa-comments" style="font-size:18px;"></i><span>Feedback</span>
            </a>
            <a href="?page=settings" style="display:flex;flex-direction:column;align-items:center;gap:3px;color:<?php echo $page==='settings'?'#4f46e5':'#94a3b8';?>;font-size:10px;font-weight:600;text-decoration:none;padding:4px 8px;min-width:52px;">
                <i class="fas fa-cog" style="font-size:18px;"></i><span>Settings</span>
            </a>
        </div>
    </nav>
</body>
</html>