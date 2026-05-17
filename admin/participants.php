<?php
// admin/participants.php

$eventId = $_GET['event_id'] ?? 0;
$participants = [];
$eventTitle = 'Event';

if (!$eventId) {
    echo "<div class='p-4 bg-yellow-50 text-yellow-700 rounded-lg'>No event selected. Please go back to Event List.</div>";
    return;
}

// Fetch event details
$checkSql = "SELECT title FROM events WHERE event_id = ?";
if ($checkStmt = $conn->prepare($checkSql)) {
    $checkStmt->bind_param("i", $eventId);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    if ($row = $checkRes->fetch_assoc()) {
        $eventTitle = $row['title'];
    } else {
        echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event not found.</div>";
        return;
    }
}

// Fetch participant details
$sql = "
    SELECT 
        u.username,
        u.email,
        r.registration_date,
        r.status,
        r.id as registration_id
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.registration_date DESC
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $partResult = $stmt->get_result();
    while ($row = $partResult->fetch_assoc()) {
        $participants[] = $row;
    }
}
?>

<div class="space-y-6 container mx-auto px-4 py-6">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-slate-500 mb-1">
                <a href="?page=manage_events" class="hover:text-indigo-600 transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Events List
                </a>
                <span>/</span>
                <span class="text-slate-400">Participants</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">
                <?php echo htmlspecialchars($eventTitle); ?>
            </h2>
            <p class="text-slate-500 text-sm">
                <?php echo count($participants); ?> Participants Registered
            </p>
        </div>
        <div class="flex space-x-3">
            <button onclick="sendReminders(<?php echo $eventId; ?>)"
                class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                <i class="fas fa-paper-plane mr-2"></i> Send All Reminders
            </button>
            <button onclick="window.location.href='export.php?type=registrations&event_id=<?php echo $eventId; ?>'"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-download mr-2"></i> Export List
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Participant</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Reg. Date</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-user-friends text-4xl mb-3 block"></i>
                                <p>No participants registered for this event yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $p): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold mr-3 border border-indigo-200">
                                            <?php echo strtoupper(substr($p['username'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">
                                                <?php echo htmlspecialchars($p['username']); ?>
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                <?php echo htmlspecialchars($p['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <?php echo date('M d, Y H:i', strtotime($p['registration_date'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusClass = 'bg-gray-100 text-gray-700';
                                    if ($p['status'] === 'confirmed')
                                        $statusClass = 'bg-green-100 text-green-700';
                                    if ($p['status'] === 'waitlisted')
                                        $statusClass = 'bg-yellow-100 text-yellow-700';
                                    if ($p['status'] === 'cancelled')
                                        $statusClass = 'bg-red-100 text-red-700';
                                    ?>
                                    <span
                                        class="px-2 py-1 rounded-full text-[10px] font-bold uppercase <?php echo $statusClass; ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-slate-400 hover:text-indigo-600 transition">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
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
    function sendReminders(eventId) {
        if (!confirm('Are you sure you want to send reminders to all confirmed participants?')) return;

        fetch('../api/send_event_alert.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ event_id: eventId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error sending reminders:', err);
                alert('Failed to send reminders.');
            });
    }
</script>