<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">My Events</h2>
                <span
                    class="flex items-center gap-1.5 px-2 py-1 bg-green-50 text-green-600 rounded text-[10px] font-bold uppercase tracking-wider border border-green-100">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                    Live
                </span>
            </div>
            <p class="text-slate-500">Manage your organized events (Real-Time Updates)</p>
        </div>
        <div class="flex gap-3">
            <a href="?action=dashboard"
                class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <a href="?action=create-event"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Create Event
            </a>
        </div>
    </div>

    <!-- Stats Row (Dynamic) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="eventStats">
        <!-- Loaded via JS -->
        <div class="animate-pulse bg-white p-6 rounded-xl h-32"></div>
        <div class="animate-pulse bg-white p-6 rounded-xl h-32"></div>
        <div class="animate-pulse bg-white p-6 rounded-xl h-32"></div>
        <div class="animate-pulse bg-white p-6 rounded-xl h-32"></div>
    </div>

    <!-- Events Grid -->
    <div id="eventsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Loading State -->
        <div class="col-span-full text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
            <p class="mt-2 text-slate-500">Loading your events...</p>
        </div>
    </div>
</div>

<script>
    function loadEvents() {
        console.log('Polling events...');
        fetch('../../api/fetch_organizer_events.php')
            .then(response => response.json())
            .then(data => {
                renderEvents(data.events);
                updateStats(data.events);
            })
            .catch(err => console.error('Error loading events:', err));
    }

    function renderEvents(events) {
        const grid = document.getElementById('eventsGrid');
        if (events.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full text-center py-12 bg-white rounded-xl border border-dashed border-slate-300">
                    <i class="fas fa-calendar-times text-4xl text-slate-300 mb-4"></i>
                    <p class="text-slate-500">No events found. Create your first event!</p>
                </div>
            `;
            return;
        }

        let html = '';
        events.forEach(event => {
            const statusColors = {
                'active': 'bg-green-100 text-green-800',
                'pending': 'bg-yellow-100 text-yellow-800',
                'cancelled': 'bg-red-100 text-red-800',
                'draft': 'bg-gray-100 text-gray-800',
                'completed': 'bg-indigo-100 text-indigo-800'
            };
            const statusClass = statusColors[event.status] || statusColors['draft'];

            html += `
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative h-48">
                        <img src="${event.image_url}" alt="${event.title}" class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">
                                ${event.status.charAt(0).toUpperCase() + event.status.slice(1)}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">${event.category}</span>
                                <h3 class="text-lg font-bold text-slate-900 mt-1 line-clamp-1">${event.title}</h3>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm text-slate-500">
                                <i class="fas fa-calendar-alt w-5 text-slate-400"></i>
                                <span>${event.formatted_date} • ${event.formatted_time}</span>
                            </div>
                            <div class="flex items-center text-sm text-slate-500">
                                <i class="fas fa-map-marker-alt w-5 text-slate-400"></i>
                                <span class="line-clamp-1">${event.venue}</span>
                            </div>
                            <div class="flex items-center text-sm text-slate-500">
                                <i class="fas fa-users w-5 text-slate-400"></i>
                                <span>${event.registered_count} / ${event.max_attendees} Registered</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t border-slate-100 flex-wrap">
                             <a href="?action=view-participants&event_id=${event.event_id}" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                                <i class="fas fa-users mr-1"></i> Students
                             </a>

                             ${event.status === 'active' ? `
                                <button onclick="completeEvent(${event.event_id}, '${event.title.replace(/'/g, "\\'")}')" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-100 transition">
                                    <i class="fas fa-check-circle mr-1"></i> Finish
                                </button>
                             ` : ''}

                             ${event.status === 'completed' ? `
                                <a href="?action=winners&event_id=${event.event_id}" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition">
                                    <i class="fas fa-trophy mr-1"></i> Results
                                </a>
                             ` : ''}

                             <button onclick="sendEventAlert(event, ${event.event_id}, '${event.title.replace(/'/g, "\\'")}')" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition" title="Notify Students">
                                <i class="fas fa-paper-plane mr-1"></i> Alert
                            </button>
                            <a href="?action=edit-event&event_id=${event.event_id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmDelete(${event.event_id}, '${event.title.replace(/'/g, "\\'")}')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        grid.innerHTML = html;
    }

    function completeEvent(eventId, eventTitle) {
        if (!confirm(`Mark "${eventTitle}" as completed? This will allow you to assign winners.`)) {
            return;
        }

        fetch('../../api/complete_event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadEvents();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error('Error:', err));
    }

    function sendEventAlert(e, eventId, eventTitle) {
        if (!confirm(`Are you sure you want to send starting alerts (Gmail & In-App) to all registered students for "${eventTitle}"?`)) {
            return;
        }

        const btn = e.currentTarget;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        fetch('../../api/send_event_alert.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ event_id: eventId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadEvents(); // Refresh to update any local counts if needed
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error sending alerts:', err);
                alert('An unexpected error occurred while sending alerts.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    }

    function confirmDelete(eventId, eventTitle) {
        if (!confirm(`Are you sure you want to delete "${eventTitle}"? This action cannot be undone.`)) {
            return;
        }

        fetch('../../api/event_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&event_id=${eventId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadEvents();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('An unexpected error occurred.');
            });
    }

    function updateStats(events) {
        const total = events.length;
        const active = events.reduce((sum, e) => sum + (e.status === 'active' ? 1 : 0), 0);
        const registrations = events.reduce((sum, e) => sum + e.registered_count, 0);
        const capacity = events.reduce((sum, e) => sum + e.max_attendees, 0);

        const statsHtml = `
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Events</p>
                <div class="flex items-baseline mt-2">
                    <span class="text-2xl font-bold text-slate-900">${total}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Active Events</p>
                <div class="flex items-baseline mt-2">
                    <span class="text-2xl font-bold text-green-600">${active}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Total Registrations</p>
                <div class="flex items-baseline mt-2">
                    <span class="text-2xl font-bold text-indigo-600">${registrations}</span>
                    <span class="ml-2 text-xs text-slate-400">across all events</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <p class="text-sm font-medium text-slate-500">Capacity Utilization</p>
                <div class="flex items-baseline mt-2">
                    <span class="text-2xl font-bold text-slate-900">${capacity > 0 ? Math.round((registrations / capacity) * 100) : 0}%</span>
                    <span class="ml-2 text-xs text-slate-400">${registrations}/${capacity} seats</span>
                </div>
            </div>
        `;
        document.getElementById('eventStats').innerHTML = statsHtml;
    }

    let eventSource = null;

    function initRealTimeEvents() {
        if (eventSource) eventSource.close();

        console.log('Connecting to real-time events stream...');
        eventSource = new EventSource('../../api/stream_events.php');

        eventSource.onmessage = function (e) {
            const data = json_parse_safe(e.data);
            if (data && data.events) {
                renderEvents(data.events);
                updateStats(data.events);
            }
        };

        eventSource.onerror = function (e) {
            console.error("SSE connection lost. Falling back to manual load.");
            eventSource.close();
            eventSource = null;
            // Fallback: just load once, no reconnect loop
            loadEvents();
        };
    }

    function json_parse_safe(str) {
        try { return JSON.parse(str); }
        catch (e) { return null; }
    }

    // Keep renderEvents and updateStats exactly as they are since 
    // my previous implementations were already robust.

    // Load immediately and initialize SSE
    initRealTimeEvents();
</script>