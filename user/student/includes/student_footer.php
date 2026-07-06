<?php
// user/student/includes/student_footer.php
$activePage = $activePage ?? 'dashboard';
?>
</main> <!-- End Main Content -->
</div> <!-- End Main Content Wrapper -->
</div> <!-- End Flex H-Screen Wrapper -->

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <div class="mobile-bottom-nav-inner">
        <a href="student.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i><span>Home</span>
        </a>
        <a href="event_catalog.php" class="<?php echo $activePage === 'events' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i><span>Events</span>
        </a>
        <a href="my_registrations.php" class="<?php echo $activePage === 'registrations' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-list"></i><span>My Events</span>
        </a>
        <a href="notifications.php" class="<?php echo $activePage === 'notifications' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i><span>Alerts</span>
        </a>
        <a href="profile.php" class="<?php echo $activePage === 'profile' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i><span>Profile</span>
        </a>
    </div>
</nav>

<!-- Scripts -->
<script>
    function updateTime() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const clock = document.getElementById('currentTime');
        if (clock) {
            clock.textContent = now.toLocaleTimeString([], options);
        }
    }

    // --- REAL-TIME ENGINE ---

    // 1. Toggle Dropdown
    function toggleNotifications() {
        const list = document.getElementById('notifList');
        list.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('notificationDropdown');
        const list = document.getElementById('notifList');
        if (dropdown && !dropdown.contains(e.target)) {
            list.classList.add('hidden');
        }
    });

    // 2. Fetch Notifications
    function fetchNotifications() {
        fetch('../../api/fetch_notifications.php')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notifBadge');
                const container = document.getElementById('notifContainer');

                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('hidden');
                    badge.style.display = 'flex'; // Ensure flex layout for centering

                    let html = '';
                    data.notifications.forEach(n => {
                        html += `
                            <div class="px-5 py-4 border-b border-slate-50 hover:bg-indigo-50/30 transition cursor-default">
                                <h4 class="text-xs font-bold text-slate-800">${n.title}</h4>
                                <p class="text-xs text-slate-500 mt-1">${n.message}</p>
                                <span class="text-[10px] text-slate-400 mt-2 block">${new Date(n.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })}</span>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    badge.classList.add('hidden');
                    container.innerHTML = '<div class="p-8 text-center"><i class="fas fa-bell-slash text-slate-200 text-3xl mb-2"></i><p class="text-xs text-slate-400 italic">No new notifications</p></div>';
                }
            })
            .catch(err => console.error('Poll Error:', err));
    }

    // 3. Real-Time Stats (SSE Implementation)
    let statsEventSource = null;

    function initStatsStream() {
        if (statsEventSource) statsEventSource.close();

        statsEventSource = new EventSource('../../api/stream_student_stats.php');

        statsEventSource.onmessage = function (event) {
            const data = JSON.parse(event.data);
            updateStatsUI(data);
        };

        statsEventSource.onerror = function () {
            console.warn('Student Stats SSE Connection failed. Reverting to manual fetch.');
            if (statsEventSource) statsEventSource.close();
            statsEventSource = null;
            fetchStats(); // Fallback to polling
        };
    }

    function updateStatsUI(data) {
        const regCountEl = document.getElementById('stat-registered');
        const upCountEl = document.getElementById('stat-upcoming');

        if (regCountEl) regCountEl.textContent = data.registered;
        if (upCountEl) upCountEl.textContent = data.upcoming;
    }

    function fetchStats() {
        const regCountEl = document.getElementById('stat-registered');
        const upCountEl = document.getElementById('stat-upcoming');

        if (!regCountEl && !upCountEl) return;

        fetch('../../api/dashboard_stats.php')
            .then(res => res.json())
            .then(data => updateStatsUI(data))
            .catch(err => console.error('Error fetching student stats:', err));
    }

    // Init & Polling
    setInterval(updateTime, 1000);
    setInterval(fetchNotifications, 60000); // Poll every 60s - reduced from 5s

    // Initial calls
    updateTime();
    fetchNotifications();

    // Only init SSE stats stream if stat elements exist on this page
    if (document.getElementById('stat-registered') || document.getElementById('stat-upcoming')) {
        initStatsStream();
    }

    // Fallback Polling for Stats if SSE is down
    setInterval(() => {
        if (!statsEventSource) fetchStats();
    }, 30000);
</script>
</body>

</html>