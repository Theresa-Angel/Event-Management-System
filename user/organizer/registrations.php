<?php
// user/organizer/registrations.php
// Fetch all registrations for events owned by this organizer
$registrations = [];
$organizerId = $_SESSION['user_id'];

$msg = $err = "";

// Handle status updates
if (isset($_POST['action']) && isset($_POST['reg_id'])) {
    $reg_id = intval($_POST['reg_id']);
    $action = $_POST['action'];

    // Security: Check if the user owns the event associated with this registration
    $stmt = $conn->prepare("
        SELECT e.organizer_id FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $reg_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || $result['organizer_id'] != $organizerId) {
        $err = "Unauthorized: You do not own this event.";
    } else {
        if ($action === 'confirm') {
            $stmt = $conn->prepare("UPDATE registrations SET status = 'confirmed' WHERE id = ?");
            $stmt->bind_param("i", $reg_id);
            if ($stmt->execute())
                $msg = "Registration confirmed.";
        } elseif ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE registrations SET status = 'cancelled' WHERE id = ?");
            $stmt->bind_param("i", $reg_id);
            if ($stmt->execute()) {
                $msg = "Registration cancelled.";
            }
        }
    }
}

// Using registrations as primary table
$sql = "
    SELECT
        r.id,
        r.registration_date,
        r.status,
        u.username as student_name,
        e.title as event_title,
        e.event_id
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.event_id
    WHERE e.organizer_id = ?
    ORDER BY r.registration_date DESC
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $organizerId);
    $stmt->execute();
    $regResult = $stmt->get_result();
    while ($row = $regResult->fetch_assoc()) {
        $registrations[] = $row;
    }
}
?>

<div class="space-y-6">
    <div class="mb-4">
        <a href="?action=dashboard" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Registration Management</h2>
            <p class="text-slate-500">View and manage sign-ups across all your events</p>
        </div>
        <div class="flex gap-2">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                <input type="text" id="searchReg" placeholder="Search students or events..."
                    class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-64">
            </div>
            <button onclick="exportRegistrationsCSV()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-download mr-2"></i> Export CSV
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Student Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Event</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Registration Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="regTableBody">
                    <?php if (empty($registrations)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users-slash text-3xl mb-3 text-slate-300"></i>
                                    <p>No registrations found for your events yet.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrations as $reg): ?>
                            <tr class="hover:bg-slate-50 transition-colors" data-reg-id="<?php echo $reg['id']; ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs uppercase">
                                            <?php echo substr($reg['student_name'] ?? 'U', 0, 1); ?>
                                        </div>
                                        <span class="text-sm font-medium text-slate-900">
                                            <?php echo htmlspecialchars($reg['student_name'] ?? 'Unknown'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600">
                                        <?php echo htmlspecialchars($reg['event_title'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-500">
                                        <?php echo date('M d, Y', strtotime($reg['registration_date'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusClass = 'bg-gray-100 text-gray-700';
                                    if ($reg['status'] === 'confirmed')
                                        $statusClass = 'bg-green-100 text-green-700';
                                    if ($reg['status'] === 'pending')
                                        $statusClass = 'bg-yellow-100 text-yellow-700';
                                    if ($reg['status'] === 'cancelled')
                                        $statusClass = 'bg-red-100 text-red-700';
                                    ?>
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $statusClass; ?>">
                                        <?php echo $reg['status'] ?? 'pending'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reg_id" value="<?php echo $reg['id']; ?>">
                                        <?php if ($reg['status'] === 'pending'): ?>
                                            <button type="submit" name="action" value="confirm"
                                                class="text-green-600 hover:text-green-800 text-sm font-semibold"
                                                onclick="return confirm('Confirm this registration?')">Confirm</button>
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
    </div>
</div>




<script>
    // Search functionality
    document.getElementById('searchReg').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#regTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Export to CSV
    function exportRegistrationsCSV() {
        window.location.href = '../../admin/export.php?type=registrations';
    }
</script>
