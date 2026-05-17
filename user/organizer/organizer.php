<?php
// Relative path to config
require_once '../../config.php';

// Include functions
if (file_exists('../../includes/functions.php')) {
    require_once '../../includes/functions.php';
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get organizer data from session
$organizerData = [
    'id' => $_SESSION['user_id'] ?? 1,
    'name' => $_SESSION['username'] ?? 'Organizer User',
    'email' => $_SESSION['email'] ?? 'organizer@example.com',
    'status' => $_SESSION['role'] === 'organizer' ? 'active' : 'pending'
];

// Check if organizer is blocked
if (isset($_SESSION['status']) && $_SESSION['status'] === 'blocked') {
    session_destroy();
    header("Location: ../../login.php?error=Your account has been blocked");
    exit();
}

$action = $_GET['action'] ?? 'dashboard'; // Default to dashboard

// --- DATABASE FETCHING LOGIC ---

// Fetch organizer's events from database with registration counts
$myEvents = [];
if (isset($conn)) {
    $stmt = $conn->prepare("
        SELECT e.*, 
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as registered_count
        FROM events e 
        WHERE e.organizer_id = ? 
        ORDER BY e.start_date DESC 
        LIMIT 50
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $myEvents[] = [
            'id' => $row['event_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category'],
            'date' => $row['start_date'],
            'time' => date('H:i', strtotime($row['start_date'])),
            'venue' => $row['venue'],
            'capacity' => $row['max_attendees'],
            'registered_count' => $row['registered_count'],
            'image_url' => $row['banner_image'] ?? 'https://via.placeholder.com/800x400',
            'status' => $row['status']
        ];
    }
}

// --- END DATABASE FETCHING LOGIC ---

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - Campus Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom Overrides matching Admin */
        .sidebar-link.active {
            background-color: #1e1b4b;
            color: white;
        }
        .sidebar-link:hover:not(.active) { background-color: #f1f5f9; }
        .notification-badge {
            position: absolute; top: -5px; right: -5px;
            background-color: #ef4444; color: white; border-radius: 50%;
            width: 18px; height: 18px; font-size: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .dropdown-container { position: relative; }
        .dropdown-menu {
            display: none; position: absolute; right: 0; top: 100%;
            margin-top: 5px; background: white; border: 1px solid #e2e8f0;
            border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            width: 300px; z-index: 50;
        }
        .dropdown-container:hover .dropdown-menu,
        .dropdown-container.show .dropdown-menu { display: block; }
        .profile-menu { width: 180px; }

        /* Mobile sidebar */
        .mobile-sidebar-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            z-index: 40; backdrop-filter: blur(2px); display: none;
        }
        .mobile-sidebar-overlay.open { display: block; }
        .org-mobile-sidebar {
            position: fixed; top: 0; left: -280px; width: 280px; height: 100vh;
            background: white; z-index: 50; padding: 24px 20px;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            transition: left 0.3s ease; overflow-y: auto;
        }
        .org-mobile-sidebar.open { left: 0; }
        .mobile-sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 10px;
            color: #475569; font-size: 14px; font-weight: 600;
            text-decoration: none; transition: all 0.2s;
        }
        .mobile-sidebar-link:hover { background: #f1f5f9; color: #1e1b4b; }
        .mobile-sidebar-active { background: #1e1b4b !important; color: white !important; }

        /* Mobile bottom nav */
        .mobile-bottom-nav {
            display: none; position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #e2e8f0; z-index: 30;
            padding: 8px 0 env(safe-area-inset-bottom, 8px);
        }
        .mobile-bottom-nav-inner { display: flex; justify-content: space-around; align-items: center; }
        .mobile-bottom-nav a {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            color: #94a3b8; font-size: 10px; font-weight: 600;
            text-decoration: none; padding: 4px 8px; border-radius: 8px;
            transition: color 0.2s; min-width: 52px;
        }
        .mobile-bottom-nav a i { font-size: 18px; }
        .mobile-bottom-nav a.active, .mobile-bottom-nav a:hover { color: #4f46e5; }

        @media (max-width: 767px) {
            .mobile-bottom-nav { display: block; }
            main { padding-bottom: 80px !important; }
        }
    </style>
</head>

<body class="bg-slate-50">
    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-sidebar-overlay" id="orgSidebarOverlay" onclick="closeOrgMobileSidebar()"></div>

    <!-- Mobile Slide-in Sidebar -->
    <div class="org-mobile-sidebar" id="orgMobileSidebar">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-3">
                <img src="../../assets/clg-logo.png" alt="Logo" class="h-9 w-9 rounded-lg object-cover">
                <span class="text-lg font-bold text-indigo-900">Campus Connect</span>
            </div>
            <button onclick="closeOrgMobileSidebar()" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="space-y-1">
            <a href="?action=dashboard" class="mobile-sidebar-link <?php echo $action === 'dashboard' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="?action=my-events" class="mobile-sidebar-link <?php echo $action === 'my-events' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-calendar-alt"></i> My Events</a>
            <a href="?action=registrations" class="mobile-sidebar-link <?php echo $action === 'registrations' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-user-check"></i> Registrations</a>
            <a href="?action=profile" class="mobile-sidebar-link <?php echo $action === 'profile' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-user"></i> Profile</a>
            <a href="?action=settings" class="mobile-sidebar-link <?php echo $action === 'settings' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a>
            <a href="?action=feedback" class="mobile-sidebar-link <?php echo $action === 'feedback' ? 'mobile-sidebar-active' : ''; ?>"><i class="fas fa-headset"></i> Support</a>
            <div class="border-t border-slate-200 my-3"></div>
            <a href="logout.php" class="mobile-sidebar-link text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden">

        <!-- Desktop Sidebar -->
        <div class="w-64 bg-white border-r border-slate-200 min-h-screen p-6 hidden md:block">
            <div class="flex items-center space-x-3 mb-8">
                <img src="../../assets/clg-logo.png" alt="Logo" class="h-10 w-10 rounded-lg object-cover">
                <span class="text-xl font-bold text-indigo-900" style="white-space: nowrap;">Campus Connect</span>
            </div>
            <div class="space-y-2 mb-8">
                <a href="?action=dashboard" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'dashboard' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-th-large w-5 h-5"></i><span class="font-medium">Dashboard</span>
                </a>
                <a href="?action=my-events" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'my-events' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-calendar-alt w-5 h-5"></i><span class="font-medium">My Events</span>
                </a>
                <a href="?action=registrations" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'registrations' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-user-check w-5 h-5"></i><span class="font-medium">Registrations</span>
                </a>
                <a href="?action=profile" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'profile' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-user w-5 h-5"></i><span class="font-medium">Profile</span>
                </a>
                <a href="?action=settings" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'settings' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-cog w-5 h-5"></i><span class="font-medium">Settings</span>
                </a>
                <a href="?action=feedback" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $action === 'feedback' ? 'active' : 'text-slate-700'; ?>">
                    <i class="fas fa-headset w-5 h-5"></i><span class="font-medium">Support</span>
                </a>
            </div>
            <div class="pt-6 border-t border-slate-200">
                <a href="logout.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-5 h-5"></i><span class="font-medium">Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Bar -->
            <div class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Hamburger (mobile only) -->
                    <button class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600" onclick="openOrgMobileSidebar()" aria-label="Open menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg md:text-2xl font-bold text-indigo-900 truncate">
                        <?php echo ucfirst(str_replace('-', ' ', $action)); ?>
                    </h1>
                </div>

                <div class="flex items-center space-x-3 md:space-x-6">
                    <!-- Time -->
                    <div class="hidden sm:flex items-center space-x-2 text-slate-600">
                        <i class="far fa-clock text-lg"></i>
                        <span id="currentTime" class="font-medium text-sm"></span>
                    </div>

                    <!-- Notifications Button -->
                    <button onclick="openNotificationsModal()" class="relative p-2 hover:bg-slate-100 rounded-full transition-colors">
                        <i class="far fa-bell text-xl text-slate-600"></i>
                        <?php
                        $unreadCount = 0;
                        if (function_exists('getUnreadNotifications')) {
                            $res = getUnreadNotifications($_SESSION['user_id']);
                            if ($res) $unreadCount = $res->num_rows;
                        }
                        ?>
                        <span class="notification-badge" style="<?php echo $unreadCount > 0 ? '' : 'display:none;'; ?>"><?php echo $unreadCount; ?></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="dropdown-container hidden sm:block pl-4 md:pl-6 border-l border-slate-200">
                        <div class="flex items-center space-x-3 cursor-pointer">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($organizerData['name']); ?>
                                </p>
                                <p class="text-xs text-slate-500">Organizer</p>
                            </div>
                            <div
                                class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border border-indigo-200">
                                <?php echo strtoupper(substr($organizerData['name'], 0, 1)); ?>
                            </div>
                        </div>

                        <div class="dropdown-menu profile-menu">
                            <a href="?action=profile" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-user mr-2 text-slate-400"></i> Profile
                            </a>
                            <a href="?action=settings"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                <i class="fas fa-cog mr-2 text-slate-400"></i> Settings
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-8">

                <?php if ($action === 'dashboard'): ?>
                    <!-- Organizer Dashboard Stats -->
                    <?php
                    $totalEvents = count($myEvents);
                    $activeRegistrations = array_reduce($myEvents, function ($carry, $event) {
                        return $carry + $event['registered_count'];
                    }, 0);

                    // Count today's events
                    $todaysEvents = 0;
                    $today = date('Y-m-d');
                    foreach ($myEvents as $ev) {
                        if (date('Y-m-d', strtotime($ev['date'])) === $today) {
                            $todaysEvents++;
                        }
                    }
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-slate-500 text-sm">Total Events</p>
                                    <h3 class="text-3xl font-bold text-indigo-900 mt-2" id="organizer-stat-events">
                                        <?php echo $totalEvents; ?>
                                    </h3>
                                </div>
                                <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                                    <i class="fas fa-calendar-alt text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-slate-500 text-sm">Active Registrations</p>
                                    <h3 class="text-3xl font-bold text-indigo-900 mt-2" id="organizer-stat-registrations">
                                        <?php echo $activeRegistrations; ?>
                                    </h3>
                                </div>
                                <div class="p-3 bg-green-50 rounded-lg text-green-600">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-slate-500 text-sm">Today's Events</p>
                                    <h3 class="text-3xl font-bold text-indigo-900 mt-2" id="organizer-stat-today">
                                        <?php echo $todaysEvents; ?>
                                    </h3>
                                </div>
                                <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Dynamic Content Include -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 min-h-[400px]">
                    <?php
                    switch ($action) {
                        case 'registrations':
                            if (file_exists('registrations.php'))
                                include 'registrations.php';
                            else
                                echo "<div class='p-4 bg-red-100 text-red-700 rounded-lg'>Registrations module not found.</div>";
                            break;
                        case 'view-participants':
                            if (file_exists('organizer_participants.php'))
                                include 'organizer_participants.php';
                            else
                                echo "<div class='p-4 bg-red-100 text-red-700 rounded-lg'>Participants module not found.</div>";
                            break;
                        case 'profile':
                            if (file_exists('organizer_profile.php'))
                                include 'organizer_profile.php';
                            else
                                echo "<p class='text-red-500'>Profile module not found.</p>";
                            break;
                        case 'create-event':
                            if (file_exists('create_event.php'))
                                include 'create_event.php';
                            else
                                echo "Create Event page not found.";
                            break;
                        case 'edit-event':
                            if (file_exists('organizer_edit_event.php'))
                                include 'organizer_edit_event.php';
                            break;
                        case 'delete-event':
                            if (file_exists('organizer_delete_event.php'))
                                include 'organizer_delete_event.php';
                            break;
                        case 'winners':
                            if (file_exists('organizer_winners.php'))
                                include 'organizer_winners.php';
                            else
                                echo "<div class='p-4 bg-red-100 text-red-700 rounded-lg'>Winners module not found.</div>";
                            break;
                        case 'my-events':
                            if (file_exists('my_event.php'))
                                include 'my_event.php';
                            else
                                echo "My Events page not found.";
                            break;
                        case 'settings':
                            if (file_exists('organizer_settings.php'))
                                include 'organizer_settings.php';
                            else
                                echo "<p class='text-red-500'>Settings module not found.</p>";
                            break;
                        case 'feedback':
                            if (file_exists('feedback.php'))
                                include 'feedback.php';
                            else
                                echo "<p class='text-red-500'>Feedback module not found.</p>";
                            break;
                        case 'dashboard':
                        default:
                            ?>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-slate-800">Recent Events</h3>
                                    <a href="?action=my-events"
                                        class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View All</a>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-200">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase">Event</th>
                                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase">Date</th>
                                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase">
                                                    Registrations</th>
                                                <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php if (empty($myEvents)): ?>
                                                <tr>
                                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-sm italic">
                                                        No events created yet.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php
                                                // Show only latest 5 events on dashboard
                                                $recentEvents = array_slice($myEvents, 0, 5);
                                                foreach ($recentEvents as $event):
                                                    ?>
                                                    <tr class="hover:bg-slate-50 transition-colors">
                                                        <td class="px-4 py-4">
                                                            <span
                                                                class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($event['title']); ?></span>
                                                        </td>
                                                        <td class="px-4 py-4">
                                                            <span
                                                                class="text-sm text-slate-600"><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                                                        </td>
                                                        <td class="px-4 py-4">
                                                            <div class="flex items-center space-x-2">
                                                                <span
                                                                    class="text-sm text-slate-900"><?php echo $event['registered_count']; ?>/<?php echo $event['capacity']; ?></span>
                                                                <div class="w-20 bg-slate-100 rounded-full h-1.5 hidden sm:block">
                                                                    <?php $percent = ($event['capacity'] > 0) ? ($event['registered_count'] / $event['capacity'] * 100) : 0; ?>
                                                                    <div class="bg-indigo-600 h-1.5 rounded-full"
                                                                        style="width: <?php echo min(100, $percent); ?>%"></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-4">
                                                            <?php
                                                            $statusClass = 'bg-gray-100 text-gray-700';
                                                            if ($event['status'] === 'active')
                                                                $statusClass = 'bg-green-100 text-green-700';
                                                            if ($event['status'] === 'pending')
                                                                $statusClass = 'bg-yellow-100 text-yellow-700';
                                                            if ($event['status'] === 'cancelled')
                                                                $statusClass = 'bg-red-100 text-red-700';
                                                            ?>
                                                            <span
                                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $statusClass; ?>">
                                                                <?php echo $event['status']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php
                            break;
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notificationsModal"
        class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div
            class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6 border-b border-slate-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800">Notifications</h3>
                    <div class="flex items-center gap-2">
                        <button onclick="clearAllNotifications()" 
                            class="text-red-600 hover:text-red-700 px-3 py-1 rounded-lg hover:bg-red-50 transition text-sm font-semibold"
                            title="Clear All Notifications">
                            <i class="fas fa-trash-alt mr-1"></i> Clear All
                        </button>
                        <button onclick="closeNotificationsModal()" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="max-h-96 overflow-y-auto p-6">
                <div id="notificationsList" class="space-y-4">
                    <!-- Notifications will be populated here -->
                    <div class="text-center text-slate-500">Loading notifications...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function updateTime() {
            const now = new Date();
            const timeEl = document.getElementById('currentTime');
            if (timeEl) timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Modal functions
        function openNotificationsModal() {
            const modal = document.getElementById('notificationsModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.children[0].classList.remove('scale-95'), 10);
            fetchNotificationsForModal();
        }

        function closeNotificationsModal() {
            const modal = document.getElementById('notificationsModal');
            modal.children[0].classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function clearAllNotifications() {
            if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
                return;
            }

            fetch('../../api/clear_notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh notification list
                    fetchNotificationsForModal();
                    // Update badge count to 0
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.style.display = 'none';
                } else {
                    alert('Failed to clear notifications: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error clearing notifications:', err);
                alert('Error clearing notifications. Please try again.');
            });
        }

        // Real-Time Stats (SSE Implementation)
        let statsEventSource = null;

        function initStatsStream() {
            if (statsEventSource) statsEventSource.close();

            statsEventSource = new EventSource('../../api/stream_organizer_stats.php');

            statsEventSource.onmessage = function (event) {
                const data = JSON.parse(event.data);
                updateStatsUI(data);
            };

            statsEventSource.onerror = function () {
                console.warn('Organizer Stats SSE Connection failed. Reverting to manual fetch.');
                if (statsEventSource) statsEventSource.close();
                statsEventSource = null;
                fetchOrganizerStats();
            };
        }

        function updateStatsUI(data) {
            const els = {
                'organizer-stat-events': data.totalEvents,
                'organizer-stat-registrations': data.activeRegistrations,
                'organizer-stat-today': data.todaysEvents
            };

            for (const [id, val] of Object.entries(els)) {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            }
        }

        function fetchOrganizerStats() {
            fetch('../../api/fetch_organizer_stats.php')
                .then(res => res.json())
                .then(data => updateStatsUI(data))
                .catch(err => console.error('Error fetching organizer stats:', err));
        }

        // Initialize Stats Stream
        initStatsStream();

        // Fallback Polling for Stats
        setInterval(() => {
            if (!statsEventSource) fetchOrganizerStats();
        }, 30000);

        // Real-Time Notifications
        function fetchNotifications() {
            fetch('../../api/fetch_organizer_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notification-badge');

                    // Update Badge
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        } else {
                            // Create badge if not exists
                            const btn = document.querySelector('.far.fa-bell').parentNode;
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count;
                            btn.appendChild(newBadge);
                        }
                    } else {
                        if (badge) badge.style.display = 'none';
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
        }

        function fetchNotificationsForModal() {
            const notificationsList = document.getElementById('notificationsList');
            notificationsList.innerHTML = '<div class="text-center text-slate-500">Loading notifications...</div>';

            fetch('../../api/fetch_organizer_notifications.php')
                .then(response => response.json())
                .then(data => {
                    // Update List for Modal
                    if (data.notifications.length > 0) {
                        let html = '';
                        data.notifications.forEach(notif => {
                            const date = new Date(notif.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });
                            const typeClass = getNotificationTypeClass(notif.type);
                            const typeIcon = getNotificationTypeIcon(notif.type);

                            html += `
                                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-start gap-4 hover:border-indigo-200 transition-all">
                                    <div class="p-3 rounded-lg ${typeClass}">
                                        <i class="fas ${typeIcon} text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-1">
                                            <h4 class="font-bold text-slate-800">${notif.title}</h4>
                                            <span class="text-xs text-slate-400 font-medium">${date}</span>
                                        </div>
                                        <p class="text-slate-500 text-sm leading-relaxed mb-3">${notif.message}</p>
                                    </div>
                                </div>
                            `;
                        });
                        notificationsList.innerHTML = html;
                    } else {
                        notificationsList.innerHTML = '<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 flex flex-col items-center justify-center text-center"><div class="p-6 bg-slate-50 rounded-full text-slate-200 mb-4"><i class="fas fa-bell-slash text-6xl"></i></div><h3 class="text-xl font-bold text-slate-800 mb-2">No notifications yet</h3><p class="text-slate-500 max-w-xs">We\'ll alert you here as soon as there\'s something new for you!</p></div>';
                    }
                })
                .catch(err => {
                    console.error('Error fetching notifications:', err);
                    notificationsList.innerHTML = '<div class="text-center text-red-500">Error loading notifications</div>';
                });
        }

        function getNotificationTypeClass(type) {
            switch (type) {
                case 'event': return 'bg-blue-50 text-blue-600';
                case 'reminder': return 'bg-orange-50 text-orange-600';
                case 'system': return 'bg-slate-50 text-slate-600';
                case 'alert': return 'bg-red-50 text-red-600';
                default: return 'bg-slate-50 text-slate-600';
            }
        }

        function getNotificationTypeIcon(type) {
            switch (type) {
                case 'event': return 'fa-calendar-alt';
                case 'reminder': return 'fa-clock';
                case 'system': return 'fa-cog';
                case 'alert': return 'fa-exclamation-triangle';
                default: return 'fa-bell';
            }
        }

        // Dropdown functions
        function showDropdown(element) { element.classList.add('show'); }
        function hideDropdown(element) { element.classList.remove('show'); }

        // Mobile sidebar functions
        function openOrgMobileSidebar() {
            document.getElementById('orgMobileSidebar').classList.add('open');
            document.getElementById('orgSidebarOverlay').classList.add('open');
        }
        function closeOrgMobileSidebar() {
            document.getElementById('orgMobileSidebar').classList.remove('open');
            document.getElementById('orgSidebarOverlay').classList.remove('open');
        }

        // Poll every 30 seconds for badge updates
        setInterval(fetchNotifications, 30000);
        fetchNotifications();
    </script>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <div class="mobile-bottom-nav-inner">
            <a href="?action=dashboard" class="<?php echo $action === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i><span>Home</span>
            </a>
            <a href="?action=my-events" class="<?php echo $action === 'my-events' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i><span>Events</span>
            </a>
            <a href="?action=registrations" class="<?php echo $action === 'registrations' ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i><span>Registrations</span>
            </a>
            <a href="?action=profile" class="<?php echo $action === 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i><span>Profile</span>
            </a>
            <a href="?action=settings" class="<?php echo $action === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
        </div>
    </nav>
</body>
</html>