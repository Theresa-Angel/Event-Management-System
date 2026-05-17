<?php
// user/organizer/feedback.php

// Fetch previous feedback
$feedbacks = [];
$stmt = $conn->prepare("SELECT * FROM feedback WHERE email = ? ORDER BY submission_date DESC LIMIT 50");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href=?page=dashboard class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors font-bold shadow-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Support & Feedback</h2>
                <p class="text-slate-500">Contact the admin team or report issues.</p>
            </div>
        </div>
        <button onclick="openFeedbackModal()"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors font-bold shadow-lg shadow-indigo-100 flex items-center">
            <i class="fas fa-plus mr-2"></i>New Feedback
        </button>
    </div>

    <!-- Feedback History -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Subject</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500 italic">No support feedback found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-slate-800">
                                        <?php echo htmlspecialchars($fb['subject']); ?>
                                    </span>
                                    <p class="text-xs text-slate-500 truncate mt-1 max-w-xs">
                                        <?php echo htmlspecialchars(substr($fb['message'], 0, 50)) . '...'; ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <?php echo date('M j, Y h:i A', strtotime($fb['submission_date'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $statusColor = 'bg-yellow-100 text-yellow-700';
                                    if ($fb['status'] === 'responded')
                                        $statusColor = 'bg-green-100 text-green-700';
                                    ?>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-bold uppercase <?php echo $statusColor; ?>">
                                        <?php echo $fb['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="viewFeedback(<?php echo htmlspecialchars(json_encode($fb)); ?>)"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                        View Details
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

<!-- New Feedback Modal -->
<div id="newFeedbackModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full transform scale-95 transition-transform duration-300">
        <form id="feedbackForm" class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-slate-800">Submit New Feedback</h3>
                <button type="button" onclick="closeFeedbackModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-4">
                <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
                <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
                <input type="hidden" name="role" value="organizer">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" required placeholder="e.g., Issue with Event Creation"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                    <textarea name="message" rows="4" required placeholder="Describe your issue or feedback..."
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeFeedbackModal()"
                    class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-medium">Cancel</button>
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold transition">
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Details Modal -->
<div id="viewFeedbackModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 class="text-xl font-bold text-slate-800" id="viewSubject">Subject</h3>
            <button onclick="document.getElementById('viewFeedbackModal').classList.add('hidden')"
                class="text-slate-400">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase">Your Message</span>
                <p class="text-slate-700 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed"
                    id="viewMessage"></p>
            </div>

            <div id="adminResponseSection" class="hidden">
                <span class="text-xs font-bold text-green-600 uppercase">Admin Response</span>
                <p class="text-slate-700 mt-1 bg-green-50 p-3 rounded-lg border border-green-100 leading-relaxed"
                    id="viewResponse"></p>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 text-right">
            <button onclick="document.getElementById('viewFeedbackModal').classList.add('hidden')"
                class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-medium">Close</button>
        </div>
    </div>
</div>

<script>
    function openFeedbackModal() {
        const modal = document.getElementById('newFeedbackModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => modal.children[0].classList.remove('scale-95'), 10);
    }

    function closeFeedbackModal() {
        const modal = document.getElementById('newFeedbackModal');
        modal.children[0].classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    document.getElementById('feedbackForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        fetch('../../api/submit_feedback.php', {
            method: 'POST',
            body: fd
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    });

    function viewFeedback(data) {
        document.getElementById('viewSubject').textContent = data.subject;
        document.getElementById('viewMessage').textContent = data.message;

        const respSec = document.getElementById('adminResponseSection');
        if (data.status === 'responded' || data.response) { // Assuming 'response' column exists or handled via status
            // Check local DB schema if response column exists. Based on admin feedback, it might not be standard
            // But let's assume if status is responded, there might be a separate response mechanism or column
            // Standard feedback table audit showed: id, username, email, subject, message, status, submission_date
            // Wait, did we add a response column? 'admin/feedback.php' shows response logic but not column explicitly in backup list
            // However, looking at 'admin/feedback.php' lines 343: data.response ? ...
            // So response column likely exists or is joined.

            if (data.response) {
                document.getElementById('viewResponse').textContent = data.response;
                respSec.classList.remove('hidden');
            } else {
                respSec.classList.add('hidden');
            }
        } else {
            respSec.classList.add('hidden');
        }

        document.getElementById('viewFeedbackModal').classList.remove('hidden');
        document.getElementById('viewFeedbackModal').classList.add('flex');
    }
</script>