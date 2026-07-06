<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$msg = $err = "";
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Pagination
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$per_page = 50; // Show 50 registrations per page
$offset = ($page - 1) * $per_page;

// Get event details if event_id is provided (optimized single query)
$event_details = null;
if ($event_id > 0) {
    $event_sql = "SELECT event_id, title, start_date, venue, category FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($event_sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event_result = $stmt->get_result();
    if ($event_result->num_rows > 0) {
        $event_details = $event_result->fetch_assoc();
    }
    $stmt->close();
}

// Handle status updates
if (isset($_POST['action']) && isset($_POST['reg_id'])) {
    $reg_id = intval($_POST['reg_id']);
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE registrations SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $reg_id);
        if ($stmt->execute())
            $msg = "Registration confirmed.";
        $stmt->close();
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE registrations SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $reg_id);
        if ($stmt->execute()) {
            $msg = "Registration cancelled.";
        }
        $stmt->close();
    }
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM registrations r";
if ($event_id > 0) {
    $count_sql .= " WHERE r.event_id = ?";
}

$count_stmt = $conn->prepare($count_sql);
if ($event_id > 0) {
    $count_stmt->bind_param("i", $event_id);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_registrations = $count_result->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_registrations / $per_page);

// Get statistics (optimized with single query)
$stats_sql = "SELECT 
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'waitlisted' THEN 1 ELSE 0 END) as waitlisted,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'registered' THEN 1 ELSE 0 END) as registered
              FROM registrations";
if ($event_id > 0) {
    $stats_sql .= " WHERE event_id = ?";
}

$stats_stmt = $conn->prepare($stats_sql);
if ($event_id > 0) {
    $stats_stmt->bind_param("i", $event_id);
}
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

$confirmed_count = $stats['confirmed'] ?? 0;
$waitlisted_count = $stats['waitlisted'] ?? 0;
$cancelled_count = $stats['cancelled'] ?? 0;
$registered_count = $stats['registered'] ?? 0;

// Optimized query - fetch only necessary columns with pagination
$registrations = [];
$sql = "SELECT r.id, r.user_id, r.event_id, r.status, r.registration_date, r.ticket_id,
               u.username, u.email, u.phone, u.department, 
               e.title as event_title, e.start_date 
        FROM registrations r
        INNER JOIN users u ON r.user_id = u.id
        INNER JOIN events e ON r.event_id = e.event_id";

if ($event_id > 0) {
    $sql .= " WHERE r.event_id = ?";
}

$sql .= " ORDER BY r.registration_date DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($event_id > 0) {
    $stmt->bind_param("iii", $event_id, $per_page, $offset);
} else {
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch all at once for better performance
if ($result) {
    $registrations = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Event Header (if specific event) -->
    <?php if ($event_details): ?>
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 mb-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($event_details['title']); ?></h2>
                    <div class="flex flex-wrap gap-4 text-sm opacity-90">
                        <span><i class="fas fa-calendar mr-2"></i><?php echo date('M d, Y g:i A', strtotime($event_details['start_date'])); ?></span>
                        <span><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($event_details['venue']); ?></span>
                        <span><i class="fas fa-tag mr-2"></i><?php echo htmlspecialchars($event_details['category']); ?></span>
                    </div>
                </div>
                <button onclick="window.location.href='?page=manage_events'"
                    class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Events
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total Registrations</p>
                    <h3 class="text-2xl font-bold text-indigo-900 mt-1"><?php echo $total_registrations; ?></h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-lg">
                    <i class="fas fa-users text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Confirmed</p>
                    <h3 class="text-2xl font-bold text-green-900 mt-1"><?php echo $confirmed_count + $registered_count; ?></h3>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Waitlisted</p>
                    <h3 class="text-2xl font-bold text-yellow-900 mt-1"><?php echo $waitlisted_count; ?></h3>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Cancelled</p>
                    <h3 class="text-2xl font-bold text-red-900 mt-1"><?php echo $cancelled_count; ?></h3>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                <?php echo $event_details ? 'Event Registrations' : 'All Registrations'; ?>
            </h2>
            <p class="text-slate-500 text-sm">Monitor and manage attendee sign-ups.</p>
        </div>
        <div class="flex space-x-3">
            <?php if (!$event_details): ?>
                <button onclick="window.location.href='?page=dashboard'"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </button>
            <?php endif; ?>
            <button onclick="window.location.href='admin/export.php?type=registrations<?php echo $event_id ? '&event_id=' . $event_id : ''; ?>'"
                class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                <i class="fas fa-download mr-2"></i>Export CSV
            </button>
            <button onclick="window.open('admin/export.php?type=registrations&format=print<?php echo $event_id ? '&event_id=' . $event_id : ''; ?>', '_blank')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>
</div>

<?php if ($msg): ?>
    <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Attendee</th>
                    <?php if (!$event_details): ?>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Event</th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Contact</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Registration Date</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Status</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Ticket ID</th>
                    <th class="px-6 py-4 text-sm font-semibold text-slate-600">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="<?php echo $event_details ? '6' : '7'; ?>" class="px-6 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-4xl text-slate-300 mb-2"></i>
                            <p>No registrations found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $reg): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3 font-bold text-indigo-600">
                                        <?php echo strtoupper(substr($reg['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">
                                            <?php echo htmlspecialchars($reg['username']); ?>
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            <?php echo htmlspecialchars($reg['department'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php if (!$event_details): ?>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-700">
                                        <?php echo htmlspecialchars($reg['event_title']); ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <?php echo date('M d, Y', strtotime($reg['start_date'])); ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700">
                                    <i class="fas fa-envelope text-slate-400 mr-1"></i>
                                    <?php echo htmlspecialchars($reg['email']); ?>
                                </div>
                                <?php if (!empty($reg['phone'])): ?>
                                    <div class="text-xs text-slate-500 mt-1">
                                        <i class="fas fa-phone text-slate-400 mr-1"></i>
                                        <?php echo htmlspecialchars($reg['phone']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?php echo date('M d, Y', strtotime($reg['registration_date'])); ?>
                                <div class="text-xs text-slate-400">
                                    <?php echo date('g:i A', strtotime($reg['registration_date'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColor = 'bg-gray-100 text-gray-700';
                                if ($reg['status'] === 'confirmed' || $reg['status'] === 'registered')
                                    $statusColor = 'bg-green-100 text-green-700';
                                if ($reg['status'] === 'waitlisted')
                                    $statusColor = 'bg-yellow-100 text-yellow-700';
                                if ($reg['status'] === 'cancelled')
                                    $statusColor = 'bg-red-100 text-red-700';
                                ?>
                                <span
                                    class="px-2 py-1 rounded-full text-[10px] font-bold uppercase <?php echo $statusColor; ?>">
                                    <?php echo $reg['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-slate-500">
                                <?php echo htmlspecialchars($reg['ticket_id']); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="reg_id" value="<?php echo $reg['id']; ?>">
                                    <?php if ($reg['status'] === 'waitlisted'): ?>
                                        <button type="submit" name="action" value="confirm"
                                            class="text-green-600 hover:text-green-800 text-sm font-semibold">Confirm</button>
                                    <?php endif; ?>
                                    <?php if ($reg['status'] !== 'cancelled'): ?>
                                        <button type="submit" name="action" value="cancel"
                                            class="ml-3 text-red-600 hover:text-red-800 text-sm font-semibold"
                                            onclick="return confirm('Cancel this registration?')">Cancel</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
            <div class="text-sm text-slate-600">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_registrations); ?> of <?php echo $total_registrations; ?> registrations
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=registrations<?php echo $event_id ? '&event_id=' . $event_id : ''; ?>&p=<?php echo $page - 1; ?>"
                       class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                // Show page numbers
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                for ($i = $start_page; $i <= $end_page; $i++):
                    if ($i == $page):
                ?>
                    <span class="px-3 py-1 bg-indigo-600 text-white rounded text-sm"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=registrations<?php echo $event_id ? '&event_id=' . $event_id : ''; ?>&p=<?php echo $i; ?>"
                       class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                        <?php echo $i; ?>
                    </a>
                <?php
                    endif;
                endfor;
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=registrations<?php echo $event_id ? '&event_id=' . $event_id : ''; ?>&p=<?php echo $page + 1; ?>"
                       class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</div>