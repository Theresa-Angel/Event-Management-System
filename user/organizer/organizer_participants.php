<?php
// user/organizer/organizer_participants.php
// Fetch detailed participants for a specific event
$eventId = $_GET['event_id'] ?? 0;
$participants = [];
$eventTitle = 'Event';

// Verify event belongs to this organizer
$checkSql = "SELECT title FROM events WHERE event_id = ? AND organizer_id = ?";
if ($checkStmt = $conn->prepare($checkSql)) {
    $checkStmt->bind_param("ii", $eventId, $_SESSION['user_id']);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    if ($row = $checkRes->fetch_assoc()) {
        $eventTitle = $row['title'];
    } else {
        // Not authorized or not found
        echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event not found or access denied.</div>";
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
        r.attended,
        r.checkin_time,
        r.ticket_id
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.attended ASC, u.username ASC
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

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <div class="flex items-center space-x-2 text-slate-500 mb-1">
                <a href="?action=dashboard" class="hover:text-indigo-600 transition">Dashboard</a>
                <span>/</span>
                <a href="?action=my-events" class="hover:text-indigo-600 transition">My Events</a>
                <span>/</span>
                <span class="text-slate-400">Participants</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">
                <?php echo htmlspecialchars($eventTitle); ?>
            </h2>
            <p class="text-slate-500">
                <?php echo count($participants); ?> Participants Registered
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="scanner.php?event_id=<?php echo $eventId; ?>"
                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 transition flex items-center">
                <i class="fas fa-qrcode mr-2"></i> Scan Ticket
            </a>
            <button onclick="openEmailModal()"
                class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition">
                <i class="fas fa-envelope mr-2"></i> Email All
            </button>
            <button onclick="exportParticipants()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-download mr-2"></i> Export
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
            <?php if (empty($participants)): ?>
                <div class="col-span-full text-center py-12 text-slate-400">
                    <i class="fas fa-user-friends text-4xl mb-3"></i>
                    <p>No participants registered for this event yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($participants as $p): ?>
                    <div
                        class="flex items-center p-4 bg-slate-50 rounded-xl border border-slate-200 hover:border-indigo-300 transition-colors">
                        <div
                            class="h-12 w-12 rounded-full bg-white flex items-center justify-center text-indigo-600 font-bold border border-slate-200 shadow-sm mr-4">
                            <?php echo strtoupper(substr($p['username'], 0, 1)); ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-900 truncate">
                                <?php echo htmlspecialchars($p['username']); ?>
                            </h4>
                            <p class="text-xs text-slate-500 truncate">
                                <?php echo htmlspecialchars($p['email']); ?>
                            </p>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="text-[10px] text-slate-400">
                                    <?php echo date('M d', strtotime($p['registration_date'])); ?>
                                </span>
                                <span class="h-1 w-1 bg-slate-300 rounded-full"></span>
                                <?php if ($p['attended']): ?>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                                        <i class="fas fa-check-circle mr-1"></i> Attended
                                    </span>
                                <?php else: ?>
                                    <button onclick="markAttended('<?php echo $p['ticket_id']; ?>')"
                                        class="text-[10px] font-bold text-slate-400 hover:text-indigo-600 transition">
                                        Mark Attended
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="relative group">
                            <button class="p-1.5 text-slate-300 hover:text-indigo-600 transition">
                                <i class="fas fa-ticket-alt"></i>
                            </button>
                            <div
                                class="absolute right-0 bottom-full mb-2 hidden group-hover:block bg-slate-900 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap">
                                Ticket: #<?php echo $p['ticket_id']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div id="emailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div
            class="p-6 border-b border-slate-200 flex justify-between items-center bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-t-xl">
            <div>
                <h3 class="text-xl font-bold">Send Email to Participants</h3>
                <p class="text-sm opacity-90 mt-1">Sending to <span id="participantCount">0</span> confirmed
                    participant(s)</p>
            </div>
            <button onclick="closeEmailModal()"
                class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="emailForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    <i class="fas fa-heading mr-2 text-indigo-600"></i>Subject
                </label>
                <input type="text" id="emailSubject"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="e.g., Important Update About <?php echo htmlspecialchars($eventTitle); ?>" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    <i class="fas fa-envelope-open-text mr-2 text-indigo-600"></i>Message
                </label>
                <textarea id="emailMessage" rows="8"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter your message to participants..." required></textarea>
                <p class="text-xs text-slate-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Event details (name, date, venue) will be automatically included in the email.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-lightbulb mr-2"></i>
                    <strong>Tip:</strong> Keep your message clear and concise. Participants will receive a
                    professionally formatted email with event details.
                </p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeEmailModal()"
                    class="px-6 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" id="sendEmailBtn" onclick="sendEmail()"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function markAttended(ticketId) {
        if (!confirm('Mark this student as attended?')) return;

        fetch(`../../api/checkin.php?ticket_id=${ticketId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function exportParticipants() {
        const eventId = <?php echo $eventId; ?>;
        window.location.href = `../../admin/export.php?type=registrations&event_id=${eventId}`;
    }

    // Email Modal Functions
    function openEmailModal() {
        const participantCount = <?php echo count($participants); ?>;
        if (participantCount === 0) {
            alert('No participants to email.');
            return;
        }
        document.getElementById('emailModal').classList.remove('hidden');
        document.getElementById('participantCount').textContent = participantCount;
    }

    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
        document.getElementById('emailForm').reset();
    }

    function sendEmail() {
        const subject = document.getElementById('emailSubject').value.trim();
        const message = document.getElementById('emailMessage').value.trim();
        const eventId = <?php echo $eventId; ?>;

        if (!subject || !message) {
            alert('Please fill in both subject and message.');
            return;
        }

        const sendBtn = document.getElementById('sendEmailBtn');
        const originalText = sendBtn.innerHTML;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('subject', subject);
        formData.append('message', message);

        fetch('../../api/send_participant_email.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeEmailModal();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Network error. Please try again.');
                console.error(err);
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;
            });
    }
</script>