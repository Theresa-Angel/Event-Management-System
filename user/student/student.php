<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch Student Stats
$stats = ['registered' => 0, 'upcoming' => 0];

$reg_sql = "SELECT COUNT(*) as count FROM registrations WHERE user_id = ?";
$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    $stats['registered'] = $res->fetch_assoc()['count'];
}

// Fetch Upcoming Events Count
$up_sql = "SELECT COUNT(*) as count FROM registrations r 
           JOIN events e ON r.event_id = e.event_id 
           WHERE r.user_id = ? AND e.start_date >= NOW() AND e.status = 'active'";
$stmt = $conn->prepare($up_sql);
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    $stats['upcoming'] = $res->fetch_assoc()['count'];
}

$pageTitle = "Student Dashboard";
$activePage = "dashboard";

include 'includes/student_header.php';
include 'includes/student_sidebar.php';
include 'includes/student_topbar.php';
?>

<!-- Welcome Section -->
<div class="mb-8">
    <h2 class="text-xl font-bold text-slate-800">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        👋</h2>
    <p class="text-slate-500 text-sm">Here's what's happening with your events.</p>
</div>

<!-- Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
        <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
            <i class="fas fa-ticket-alt text-xl"></i>
        </div>
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Registrations</p>
            <h3 class="text-2xl font-bold text-slate-800" id="stat-registered"><?php echo $stats['registered']; ?></h3>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
        <div class="p-3 bg-green-50 rounded-lg text-green-600">
            <i class="fas fa-calendar-check text-xl"></i>
        </div>
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Upcoming</p>
            <h3 class="text-2xl font-bold text-slate-800" id="stat-upcoming"><?php echo $stats['upcoming']; ?></h3>
        </div>
    </div>
    <a href="event_catalog.php"
        class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4 hover:border-indigo-300 transition-colors">
        <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
            <i class="fas fa-search text-xl"></i>
        </div>
        <div>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Events</p>
            <h3 class="text-xl font-bold text-slate-800">Browse New</h3>
        </div>
    </a>
</div>

<!-- Upcoming List Table -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
        <h3 class="font-bold text-slate-800">Your Upcoming Events</h3>
        <a href="my_registrations.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-600 uppercase">Event</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-600 uppercase">Date</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-600 uppercase">Venue</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-600 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php
                $list_sql = "SELECT e.* FROM registrations r 
                             JOIN events e ON r.event_id = e.event_id 
                             WHERE r.user_id = ? AND e.start_date >= NOW() AND e.status = 'active'
                             ORDER BY e.start_date ASC LIMIT 5";
                $stmt = $conn->prepare($list_sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $list_res = $stmt->get_result();

                if ($list_res->num_rows > 0) {
                    while ($row = $list_res->fetch_assoc()) {
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <span
                                    class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($row['title']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="text-sm text-slate-500"><?php echo date('M d, Y h:i A', strtotime($row['start_date'])); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-sm text-slate-500">
                                    <i class="fas fa-location-dot mr-2 text-slate-400"></i>
                                    <?php echo htmlspecialchars($row['venue']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="view_event.php?id=<?php echo $row['event_id']; ?>"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold">View Details</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-calendar-xmark text-4xl text-slate-200 mb-3"></i>
                                <p class="text-slate-500 italic text-sm">No upcoming events found. Go explore!</p>
                                <a href="event_catalog.php"
                                    class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 transition">Browse
                                    Catalog</a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/student_footer.php'; ?>