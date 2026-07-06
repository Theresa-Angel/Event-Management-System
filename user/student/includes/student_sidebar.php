<?php
// user/student/includes/student_sidebar.php
$activePage = $activePage ?? 'dashboard';
?>
<!-- Mobile Sidebar Overlay -->
<div class="mobile-sidebar-overlay hidden" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>

<!-- Sidebar (desktop: static, mobile: slide-in drawer) -->
<div class="w-72 bg-white border-r border-slate-200 min-h-screen p-6 hidden md:block" id="desktopSidebar">
    <div class="flex items-center space-x-3 mb-8">
        <img src="../../assets/clg-logo.png" alt="Logo" class="h-10 w-10 rounded-lg object-cover flex-shrink-0">
        <span class="text-xl font-bold text-indigo-900 whitespace-nowrap">Campus Connect</span>
    </div>
    <div class="space-y-2 mb-8">
        <a href="student.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $activePage === 'dashboard' ? 'active' : 'text-slate-700'; ?>">
            <i class="fas fa-th-large w-5 h-5"></i><span class="font-medium">Dashboard</span>
        </a>
        <a href="my_registrations.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $activePage === 'registrations' ? 'active' : 'text-slate-700'; ?>">
            <i class="fas fa-clipboard-list w-5 h-5"></i><span class="font-medium">Registrations</span>
        </a>
        <a href="event_catalog.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $activePage === 'events' ? 'active' : 'text-slate-700'; ?>">
            <i class="fas fa-calendar-alt w-5 h-5"></i><span class="font-medium">Events Catalog</span>
        </a>
        <a href="profile.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $activePage === 'profile' ? 'active' : 'text-slate-700'; ?>">
            <i class="fas fa-user-circle w-5 h-5"></i><span class="font-medium">My Profile</span>
        </a>
        <a href="feedback.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all sidebar-link <?php echo $activePage === 'feedback' ? 'active' : 'text-slate-700'; ?>">
            <i class="fas fa-comment-dots w-5 h-5"></i><span class="font-medium">Feedback</span>
        </a>
    </div>
    <div class="pt-6 border-t border-slate-200 mt-auto">
        <a href="../../logout.php" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50">
            <i class="fas fa-sign-out-alt w-5 h-5"></i><span class="font-medium">Logout</span>
        </a>
    </div>
</div>

<!-- Mobile Slide-in Sidebar -->
<div class="mobile-sidebar" id="mobileSidebar">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-3">
            <img src="../../assets/clg-logo.png" alt="Logo" class="h-9 w-9 rounded-lg object-cover">
            <span class="text-lg font-bold text-indigo-900">Campus Connect</span>
        </div>
        <button onclick="closeMobileSidebar()" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
    <div class="space-y-1">
        <a href="student.php" class="mobile-sidebar-link <?php echo $activePage === 'dashboard' ? 'mobile-sidebar-active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="my_registrations.php" class="mobile-sidebar-link <?php echo $activePage === 'registrations' ? 'mobile-sidebar-active' : ''; ?>">
            <i class="fas fa-clipboard-list"></i> Registrations
        </a>
        <a href="event_catalog.php" class="mobile-sidebar-link <?php echo $activePage === 'events' ? 'mobile-sidebar-active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> Events Catalog
        </a>
        <a href="profile.php" class="mobile-sidebar-link <?php echo $activePage === 'profile' ? 'mobile-sidebar-active' : ''; ?>">
            <i class="fas fa-user-circle"></i> My Profile
        </a>
        <a href="feedback.php" class="mobile-sidebar-link <?php echo $activePage === 'feedback' ? 'mobile-sidebar-active' : ''; ?>">
            <i class="fas fa-comment-dots"></i> Feedback
        </a>
        <div class="border-t border-slate-200 my-3"></div>
        <a href="../../logout.php" class="mobile-sidebar-link text-red-600 hover:bg-red-50">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<style>
    .mobile-sidebar-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 40; backdrop-filter: blur(2px);
    }
    .mobile-sidebar {
        position: fixed; top: 0; left: -280px; width: 280px; height: 100vh;
        background: white; z-index: 50; padding: 24px 20px;
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        transition: left 0.3s ease; overflow-y: auto;
        display: none;
    }
    .mobile-sidebar.open { left: 0; }
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
        display: none;
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white; border-top: 1px solid #e2e8f0;
        z-index: 30; padding: 8px 0 env(safe-area-inset-bottom, 8px);
    }
    .mobile-bottom-nav-inner {
        display: flex; justify-content: space-around; align-items: center;
    }
    .mobile-bottom-nav a {
        display: flex; flex-direction: column; align-items: center; gap: 3px;
        color: #94a3b8; font-size: 10px; font-weight: 600;
        text-decoration: none; padding: 4px 8px; border-radius: 8px;
        transition: color 0.2s; min-width: 52px;
    }
    .mobile-bottom-nav a i { font-size: 18px; }
    .mobile-bottom-nav a.active, .mobile-bottom-nav a:hover { color: #4f46e5; }

    @media (max-width: 767px) {
        .mobile-sidebar { display: block; }
        .mobile-bottom-nav { display: block; }
        /* Add bottom padding so content isn't hidden behind bottom nav */
        main { padding-bottom: 80px !important; }
    }
</style>

<script>
    function openMobileSidebar() {
        document.getElementById('mobileSidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.remove('hidden');
    }
    function closeMobileSidebar() {
        document.getElementById('mobileSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.add('hidden');
    }
</script>

<!-- Main Content Wrapper (Opened in header, closed in footer) -->
<div class="flex-1 flex flex-col overflow-hidden">