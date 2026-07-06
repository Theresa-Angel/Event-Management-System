<?php
// manage_events.php
?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Event Management</h2>
        <div class="flex space-x-3">
            <button onclick="window.location.href='?page=dashboard'"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </button>
            <button onclick="window.location.href='?page=create_event'"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                <i class="fas fa-calendar-plus mr-2"></i>Create New Event
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-600">Total Events</h3>
                    <p class="text-2xl font-bold text-indigo-900 mt-2"><?php echo $stats['total_events'] ?? '0'; ?></p>
                </div>
                <i class="fas fa-calendar-alt text-2xl text-indigo-500"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-600">Active Events</h3>
                    <p class="text-2xl font-bold text-green-900 mt-2" id="activeEventsCount">0</p>
                </div>
                <i class="fas fa-calendar-check text-2xl text-green-500"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-600">Upcoming Events</h3>
                    <p class="text-2xl font-bold text-blue-900 mt-2" id="upcomingEventsCount">0</p>
                </div>
                <i class="fas fa-clock text-2xl text-blue-500"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-600">Total Registrations</h3>
                    <p class="text-2xl font-bold text-purple-900 mt-2">
                        <?php echo $stats['total_registrations'] ?? '0'; ?></p>
                </div>
                <i class="fas fa-users text-2xl text-purple-500"></i>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" id="searchEvents" placeholder="Search events by title, venue, or organizer..."
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <select id="statusFilter"
                    class="border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="categoryFilter"
                    class="border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Categories</option>
                    <option value="workshop">Workshop</option>
                    <option value="seminar">Seminar</option>
                    <option value="conference">Conference</option>
                    <option value="competition">Competition</option>
                    <option value="social">Social</option>
                    <option value="sports">Sports</option>
                    <option value="cultural">Cultural</option>
                    <option value="other">Other</option>
                </select>
                <input type="date" id="dateFilter"
                    class="border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <button onclick="resetFilters()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    Reset
                </button>
                <button onclick="exportEvents()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Event</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Date & Time</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Venue</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Registrations</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <?php
                    // Fetch events from database
                    require_once '../config.php';

                    $search = $_GET['search'] ?? '';
                    $status = $_GET['status'] ?? '';
                    $category = $_GET['category'] ?? '';
                    $date = $_GET['date'] ?? '';

                    $sql = "SELECT e.*, u.username as organizer_name, 
                                   COUNT(r.user_id) as registration_count
                            FROM events e
                            LEFT JOIN users u ON e.organizer_id = u.id
                            LEFT JOIN registrations r ON e.event_id = r.event_id
                            WHERE 1=1";
                    $params = [];
                    $types = "";

                    if (!empty($search)) {
                        $sql .= " AND (e.title LIKE ? OR e.venue LIKE ? OR u.username LIKE ?)";
                        $searchTerm = "%$search%";
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        $types .= "sss";
                    }

                    if (!empty($status)) {
                        if ($status === 'upcoming') {
                            $sql .= " AND e.start_date > NOW() AND e.status = 'active'";
                        } elseif ($status === 'ongoing') {
                            $sql .= " AND e.start_date <= NOW() AND e.end_date >= NOW() AND e.status = 'active'";
                        } elseif ($status === 'completed') {
                            $sql .= " AND e.end_date < NOW() AND e.status = 'active'";
                        } else {
                            $sql .= " AND e.status = ?";
                            $params[] = $status;
                            $types .= "s";
                        }
                    }

                    if (!empty($category)) {
                        $sql .= " AND e.category = ?";
                        $params[] = $category;
                        $types .= "s";
                    }

                    if (!empty($date)) {
                        $sql .= " AND DATE(e.start_date) = ?";
                        $params[] = $date;
                        $types .= "s";
                    }

                    $sql .= " GROUP BY e.event_id
                              ORDER BY e.start_date DESC
                              LIMIT 500";

                    $stmt = $conn->prepare($sql);

                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();

                    $activeCount = 0;
                    $upcomingCount = 0;

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $statusBadge = '';
                            $currentDate = date('Y-m-d H:i:s');

                            // Determine event status
                            if ($row['status'] === 'cancelled') {
                                $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Cancelled</span>';
                            } elseif ($row['status'] === 'draft') {
                                $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Draft</span>';
                            } else {
                                if ($row['start_date'] > $currentDate) {
                                    $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Upcoming</span>';
                                    $upcomingCount++;
                                    $activeCount++;
                                } elseif ($row['start_date'] <= $currentDate && $row['end_date'] >= $currentDate) {
                                    $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Ongoing</span>';
                                    $activeCount++;
                                } else {
                                    $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Completed</span>';
                                }
                            }

                            echo '
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-start">
                                        <div class="w-12 h-12 rounded-lg bg-slate-200 flex items-center justify-center mr-3 flex-shrink-0">
                                            ' . ($row['cover_image'] ?
                                '<img src="' . htmlspecialchars($row['cover_image']) . '" alt="' . htmlspecialchars($row['title']) . '" class="w-full h-full rounded-lg object-cover">' :
                                '<i class="fas fa-calendar-alt text-slate-400"></i>') . '
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">' . htmlspecialchars($row['title']) . '</div>
                                            <div class="text-sm text-slate-500 flex items-center mt-1">
                                                <i class="fas fa-user-tie text-xs mr-1"></i>
                                                ' . htmlspecialchars($row['organizer_name'] ?? 'Unknown') . '
                                            </div>
                                            <div class="text-xs text-slate-400 mt-1">
                                                ID: ' . htmlspecialchars($row['event_id']) . ' | 
                                                ' . htmlspecialchars($row['category'] ?? 'General') . '
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-sm font-medium text-gray-900">' . date('M j, Y', strtotime($row['start_date'])) . '</div>
                                    <div class="text-sm text-slate-600">' . date('g:i A', strtotime($row['start_date'])) . ' - ' . date('g:i A', strtotime($row['end_date'])) . '</div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-gray-700">' . htmlspecialchars($row['venue']) . '</div>
                                </td>
                                <td class="py-4 px-4">
                                    ' . $statusBadge . '
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center">
                                        <div class="text-center mr-3">
                                            <div class="text-lg font-bold text-indigo-900">' . $row['registration_count'] . '</div>
                                            <div class="text-xs text-slate-500">registered</div>
                                        </div>
                                        <div class="w-16">
                                            <div class="text-xs text-slate-500 mb-1">Capacity: ' . ($row['max_attendees'] ? $row['max_attendees'] : '∞') . '</div>
                                            <div class="w-full bg-slate-200 rounded-full h-2">
                                                <div class="bg-green-500 h-2 rounded-full" 
                                                     style="width: ' . ($row['max_attendees'] ? min(100, ($row['registration_count'] / $row['max_attendees'] * 100)) : '0') . '%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="viewEvent(' . $row['event_id'] . ')" 
                                                class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-sm transition-colors">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
                                        <button onclick="editEvent(' . $row['event_id'] . ')" 
                                                class="px-3 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <div class="relative">
                                            <button onclick="toggleDropdown(' . $row['event_id'] . ')" 
                                                    class="px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded text-sm transition-colors">
                                                <i class="fas fa-ellipsis-h mr-1"></i>More
                                            </button>
                                            <div id="dropdown-' . $row['event_id'] . '" 
                                                 class="absolute right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg hidden z-10 min-w-[150px]">
                                                <button onclick="viewRegistrations(' . $row['event_id'] . ')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-50">
                                                    <i class="fas fa-users mr-2"></i>Registrations
                                                </button>
                                                <button onclick="sendReminders(' . $row['event_id'] . ')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-50">
                                                    <i class="fas fa-bell mr-2"></i>Send Reminders
                                                </button>
                                                <div class="border-t"></div>
                                                ' . ($row['status'] === 'active' ?
                                '<button onclick="toggleEventStatus(' . $row['event_id'] . ', \'cancelled\')" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        <i class="fas fa-ban mr-2"></i>Cancel Event
                                                    </button>' :
                                '<button onclick="toggleEventStatus(' . $row['event_id'] . ', \'active\')" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50">
                                                        <i class="fas fa-check mr-2"></i>Reactivate
                                                    </button>') . '
                                                <button onclick="deleteEvent(' . $row['event_id'] . ')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-2"></i>Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo '
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center">
                                <div class="text-slate-500">
                                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                    <p class="text-lg">No events found</p>
                                    <p class="text-sm mt-1">Create your first event to get started</p>
                                    <button onclick="window.location.href=\'create_event.php\'" 
                                            class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        <i class="fas fa-plus mr-2"></i>Create Event
                                    </button>
                                </div>
                            </td>
                        </tr>';
                    }

                    // Update count displays
                    echo '<script>
                        document.getElementById("activeEventsCount").textContent = "' . $activeCount . '";
                        document.getElementById("upcomingEventsCount").textContent = "' . $upcomingCount . '";
                    </script>';

                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
            <div class="text-sm text-slate-600">
                Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span
                    id="totalEvents"><?php echo $result->num_rows; ?></span> events
            </div>
            <div class="flex space-x-1">
                <button class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">1</button>
                <button
                    class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">2</button>
                <button
                    class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">3</button>
                <button class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>


</div>

<!-- Event Detail Modal -->
<div id="eventDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Event Details</h3>
                <button onclick="closeEventModal()" class="text-slate-500 hover:text-slate-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="eventDetailContent">
                <!-- Event details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div id="calendarModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Event Details</h3>
                <button onclick="closeCalendarModal()" class="text-slate-500 hover:text-slate-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="calendarEventContent"></div>
        </div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

<script>
    // Update event count displays
    document.addEventListener('DOMContentLoaded', function () {
        updateEventCounts();
    });

    // Search functionality
    document.getElementById('searchEvents').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#eventsTableBody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
        updateEventCounts();
    });

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const status = document.getElementById('statusFilter').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value.toLowerCase();
        const date = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('#eventsTableBody tr');

        rows.forEach(row => {
            const statusCol = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const categoryCol = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const dateCol = row.querySelector('td:nth-child(2)').textContent;

            const statusMatch = !status || statusCol.includes(status);
            const categoryMatch = !category || categoryCol.includes(category);
            const dateMatch = !date || dateCol.includes(date);

            row.style.display = (statusMatch && categoryMatch && dateMatch) ? '' : 'none';
        });
        updateEventCounts();
    }

    function resetFilters() {
        document.getElementById('searchEvents').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('dateFilter').value = '';

        const rows = document.querySelectorAll('#eventsTableBody tr');
        rows.forEach(row => row.style.display = '');
        updateEventCounts();
    }

    function updateEventCounts() {
        const rows = document.querySelectorAll('#eventsTableBody tr');
        let activeCount = 0;
        let upcomingCount = 0;
        let totalCount = 0;

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                totalCount++;
                const status = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                if (status.includes('ongoing') || status.includes('upcoming')) {
                    activeCount++;
                }
                if (status.includes('upcoming')) {
                    upcomingCount++;
                }
            }
        });

        document.getElementById('activeEventsCount').textContent = activeCount;
        document.getElementById('upcomingEventsCount').textContent = upcomingCount;
        document.getElementById('totalEvents').textContent = totalCount;
    }

    // Dropdown toggle
    function toggleDropdown(eventId) {
        const dropdown = document.getElementById('dropdown-' + eventId);
        
        // Close all other dropdowns first
        document.querySelectorAll('[id^="dropdown-"]').forEach(other => {
            if (other.id !== 'dropdown-' + eventId) {
                other.classList.add('hidden');
            }
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('hidden');
        
        // Stop event propagation to prevent immediate closing
        event.stopPropagation();
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('button[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });

    // Event actions
    function viewEvent(eventId) {
        fetch('get_event_details.php?id=' + eventId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                const modal = document.getElementById('eventDetailModal');
                const content = document.getElementById('eventDetailContent');

                content.innerHTML = `
                <div class="space-y-6">
                    <div class="mb-6">
                        <h4 class="text-2xl font-bold text-gray-800 mb-2">${data.title}</h4>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                ${data.category || 'General'}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                ${data.status}
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4">${data.description || 'No description available'}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">Start Date & Time</p>
                            <p class="font-medium text-gray-800">${data.start_date}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">End Date & Time</p>
                            <p class="font-medium text-gray-800">${data.end_date}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">Venue</p>
                            <p class="font-medium text-gray-800">${data.venue}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">Organizer</p>
                            <p class="font-medium text-gray-800">${data.organizer_name || 'Unknown'}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">Max Capacity</p>
                            <p class="font-medium text-gray-800">${data.max_attendees || 'Unlimited'}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <p class="text-sm text-slate-500 mb-1">Total Registrations</p>
                            <p class="font-medium text-gray-800">${data.registration_count || 0}</p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h5 class="font-semibold text-blue-900 mb-3">Registration Statistics</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-blue-700">Total Registrations</p>
                                <p class="text-2xl font-bold text-blue-900">${data.registration_count || 0}</p>
                            </div>
                            <div>
                                <p class="text-sm text-blue-700">Attendance Rate</p>
                                <p class="text-2xl font-bold text-blue-900">${data.attendance_rate || '0'}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t pt-6 mt-6 flex justify-end space-x-3">
                    <button onclick="closeEventModal()" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-medium">
                        Close
                    </button>
                </div>
            `;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading event details');
            });
    }

    function editEvent(eventId) {
        // Open edit modal similar to view
        fetch('get_event_details.php?id=' + eventId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                // Redirect to edit page with event data
                window.location.href = '?page=edit_events&id=' + eventId;
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback: just redirect to edit page
                window.location.href = '?page=edit_events&id=' + eventId;
            });
    }

    function viewRegistrations(eventId) {
        window.location.href = '?page=registrations&event_id=' + eventId;
    }

    function sendReminders(eventId) {
        if (confirm('Send reminder emails to all registered participants?')) {
            fetch('send_event_reminders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ eventId: eventId })
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Reminders sent successfully!');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending reminders');
                });
        }
    }

    function toggleEventStatus(eventId, status) {
        const action = status === 'cancelled' ? 'cancel' : 'reactivate';
        if (confirm(`Are you sure you want to ${action} this event?`)) {
            fetch('update_event_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    eventId: eventId,
                    status: status
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event status updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating event status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating event status');
                });
        }
    }

    function deleteEvent(eventId) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            fetch('delete_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ eventId: eventId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error deleting event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting event');
                });
        }
    }

    function exportEvents() {
        // Get current filters
        const search = document.getElementById('searchEvents').value;
        const status = document.getElementById('statusFilter').value;
        const category = document.getElementById('categoryFilter').value;
        const date = document.getElementById('dateFilter').value;

        // Build export URL
        let exportUrl = 'export_events.php?';
        if (search) exportUrl += 'search=' + encodeURIComponent(search) + '&';
        if (status) exportUrl += 'status=' + encodeURIComponent(status) + '&';
        if (category) exportUrl += 'category=' + encodeURIComponent(category) + '&';
        if (date) exportUrl += 'date=' + encodeURIComponent(date);

        window.location.href = exportUrl;
    }

    function closeEventModal() {
        document.getElementById('eventDetailModal').classList.add('hidden');
        document.getElementById('eventDetailModal').classList.remove('flex');
    }

    // Close modals when clicking outside
    document.getElementById('eventDetailModal').addEventListener('click', function (e) {
        if (e.target === this) closeEventModal();
    });

    // Handle deep-linking from dashboard (edit/view actions)
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        const id = urlParams.get('id');

        if (id && action) {
            if (action === 'edit') {
                editEvent(id);
            } else if (action === 'view') {
                viewEvent(id);
            }
        }
    });

</script>