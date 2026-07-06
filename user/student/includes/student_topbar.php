<?php
// user/student/includes/student_topbar.php
$username = $_SESSION['username'] ?? 'Student';
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!-- Top Bar -->
<div class="bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <!-- Hamburger (mobile only) -->
        <button class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600" onclick="openMobileSidebar()" aria-label="Open menu">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-lg md:text-2xl font-bold text-indigo-900 truncate">
            <?php echo $pageTitle; ?>
        </h1>
    </div>

    <div class="flex items-center space-x-3 md:space-x-6">
        <!-- Real-time Clock (hidden on small screens) -->
        <div class="hidden sm:flex items-center space-x-2 text-slate-600">
            <i class="far fa-clock text-lg text-indigo-500"></i>
            <span id="currentTime" class="font-medium text-sm"></span>
        </div>

        <!-- Notifications Icon -->
        <div class="relative group" id="notificationDropdown">
            <button class="p-2 hover:bg-slate-100 rounded-full transition-colors relative"
                onclick="toggleNotifications()">
                <i class="far fa-bell text-xl text-slate-600"></i>
                <span class="notification-badge hidden" id="notifBadge">0</span>
            </button>
            <div class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-xl border border-slate-200 hidden z-50 overflow-hidden"
                id="notifList">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-sm text-slate-800">Notifications</h3>
                </div>
                <div class="max-h-80 overflow-y-auto" id="notifContainer">
                    <div class="p-4 text-center text-slate-400 text-sm italic">Loading...</div>
                </div>
                <div class="p-3 text-center border-t border-slate-100">
                    <a href="notifications.php"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">View All</a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown (hidden on very small screens) -->
        <div class="profile-dropdown hidden sm:flex items-center space-x-3 border-l border-slate-200 pl-4 md:pl-6">
            <div class="text-right hidden md:block">
                <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($username); ?></p>
                <p class="text-xs text-slate-500">Student</p>
            </div>
            <div class="h-9 w-9 md:h-10 md:w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold border border-indigo-200 text-sm">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <div class="dropdown-content">
                <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                <a href="my_registrations.php"><i class="fas fa-list-check"></i> Registrations</a>
                <div class="border-t border-slate-100 my-1"></div>
                <a href="../../logout.php" class="text-red-600"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
<!-- Page Content (Scrollable) -->
<main class="flex-1 overflow-auto p-4 md:p-8">