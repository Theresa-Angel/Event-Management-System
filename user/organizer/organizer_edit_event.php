<?php
// Fetch event data
$eventId = $_GET['event_id'] ?? 0;
if (!$eventId) {
    echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event ID is required.</div>";
    return;
}

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $eventId, $_SESSION['user_id']);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event not found or access denied.</div>";
    return;
}

// Handle Form Submission handled via AJAX/API for consistency with dynamic dash
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Edit Event</h2>
            <p class="text-slate-500">Update details for <span class="text-indigo-600 font-semibold">
                    <?php echo htmlspecialchars($event['title']); ?>
                </span></p>
        </div>
        <div class="flex gap-3">
            <a href="?action=dashboard" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-home mr-1"></i> Dashboard
            </a>
            <a href="?action=my-events" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Events
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <form id="editEventForm" class="p-6 sm:p-8 space-y-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Event Title <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($event['title']); ?>"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>

                <textarea name="description" rows="4"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Rules & Regulations</label>
                <textarea name="rules" rows="4"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                    placeholder="List any rules, guidelines, or requirements..."><?php echo htmlspecialchars($event['rules'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Category</label>
                <select name="category" id="eventCategory"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                    <?php
                    $cats = ['Workshop', 'Seminar', 'Competition', 'Cultural', 'Sports', 'Arts', 'Symposium', 'Hackathon', 'Webinar', 'Other'];
                    foreach ($cats as $c) {
                        $selected = ($event['category'] === $c) ? 'selected' : '';
                        echo "<option value='$c' $selected>$c</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Venue / Location <span
                        class="text-red-500">*</span></label>
                <input type="text" name="venue" required value="<?php echo htmlspecialchars($event['venue']); ?>"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Start Date & Time <span
                        class="text-red-500">*</span></label>
                <input type="datetime-local" name="start_date" required
                    value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">End Date & Time</label>
                <input type="datetime-local" name="end_date"
                    value="<?php echo !empty($event['end_date']) ? date('Y-m-d\TH:i', strtotime($event['end_date'])) : ''; ?>"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Max Capacity</label>
                <input type="number" name="max_attendees" min="1" value="<?php echo $event['max_attendees']; ?>"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Cover Image URL</label>
                <input type="url" name="cover_image"
                    value="<?php echo htmlspecialchars($event['cover_image'] ?? ''); ?>"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
            </div>
    </div>

    <div class="flex justify-end pt-6 border-t border-slate-100 mt-6">
        <button type="submit" id="saveEventBtn"
            class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg hover:bg-indigo-700 transition font-medium shadow-sm flex items-center">
            <i class="fas fa-save mr-2"></i> Save Changes
        </button>
    </div>
    </form>
</div>
</div>

<script>
    document.getElementById('editEventForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('saveEventBtn');
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

        const formData = new FormData(this);

        fetch('../../api/event_management.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '?action=my-events';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('An unexpected error occurred.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
    });
</script>