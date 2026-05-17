<?php
// Ensure event_id is provided
$eventId = $_GET['event_id'] ?? 0;
if (!$eventId) {
    echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event ID is required.</div>";
    return;
}

// Fetch event details to ensure ownership and get existing prizes
$stmt = $conn->prepare("SELECT title, prizes, status FROM events WHERE event_id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $eventId, $_SESSION['user_id']);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<div class='p-4 bg-red-50 text-red-700 rounded-lg'>Event not found or access denied.</div>";
    return;
}

// Handle Form Submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_winners'])) {
    $prizesArray = [];
    if (isset($_POST['prize_titles']) && is_array($_POST['prize_titles'])) {
        foreach ($_POST['prize_titles'] as $index => $title) {
            $title = trim($title);
            if (!empty($title)) {
                $winner = trim($_POST['prize_winners'][$index] ?? '');
                $prizesArray[] = [
                    'title' => $title,
                    'winner' => $winner
                ];
            }
        }
    }

    $prizesJson = !empty($prizesArray) ? json_encode($prizesArray) : null;

    $updateStmt = $conn->prepare("UPDATE events SET prizes = ? WHERE event_id = ?");
    $updateStmt->bind_param("si", $prizesJson, $eventId);

    if ($updateStmt->execute()) {
        $success = "Winner details updated successfully!";
        // Refresh event data
        $event['prizes'] = $prizesJson;
    } else {
        $error = "Error updating winners: " . $updateStmt->error;
    }
}

$prizes = json_decode($event['prizes'] ?? '[]', true);
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Assign Winners</h2>
            <p class="text-slate-500">Event: <span class="font-semibold text-indigo-600">
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
        <?php if ($success): ?>
            <div class="p-4 bg-green-50 text-green-700 border-b border-green-100 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-4 bg-red-50 text-red-700 border-b border-red-100 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="p-6 sm:p-8 space-y-6">
            <input type="hidden" name="update_winners" value="1">

            <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3 border border-blue-100 mb-6">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                <p class="text-sm text-blue-700">
                    Assign names to the prizes listed below. These will be publicly visible to students on the events
                    page once saved.
                </p>
            </div>

            <div id="prizeList" class="space-y-4">
                <?php if (empty($prizes)): ?>
                    <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-lg">
                        <i class="fas fa-trophy text-3xl text-slate-200 mb-2"></i>
                        <p class="text-slate-500 text-sm">No prizes were defined for this event.</p>
                        <button type="button" id="addPrizeBtn"
                            class="mt-4 text-indigo-600 text-sm font-bold hover:underline">+ Add Prize Entry</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($prizes as $index => $prize): ?>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200 relative group">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Prize Title</label>
                                <input type="text" name="prize_titles[]"
                                    value="<?php echo htmlspecialchars($prize['title']); ?>"
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Winner Name / Team</label>
                                <input type="text" name="prize_winners[]"
                                    value="<?php echo htmlspecialchars($prize['winner'] ?? ''); ?>"
                                    placeholder="e.g. John Doe or Team Alpha"
                                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                            </div>
                            <button type="button"
                                class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm hidden group-hover:flex items-center justify-center transition">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-slate-100">
                <button type="button" id="addMorePrizeBtn"
                    class="text-indigo-600 text-sm font-semibold hover:text-indigo-800">
                    <i class="fas fa-plus mr-1"></i> Add Another Prize
                </button>
                <button type="submit"
                    class="bg-indigo-600 text-white px-8 py-2.5 rounded-lg hover:bg-indigo-700 transition font-medium shadow-sm flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Winners
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const prizeList = document.getElementById('prizeList');
        const addMorePrizeBtn = document.getElementById('addMorePrizeBtn');
        const emptyAddBtn = document.getElementById('addPrizeBtn');

        function addPrizeRow() {
            // Remove empty state if it exists
            const emptyState = prizeList.querySelector('.text-center');
            if (emptyState) emptyState.remove();

            const div = document.createElement('div');
            div.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200 relative group';
            div.innerHTML = `
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Prize Title</label>
                    <input type="text" name="prize_titles[]" placeholder="e.g. 1st Place" 
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Winner Name / Team</label>
                    <input type="text" name="prize_winners[]" placeholder="Winner Name" 
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                </div>
                <button type="button" class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm flex items-center justify-center transition">
                    <i class="fas fa-times text-xs"></i>
                </button>
            `;
            prizeList.appendChild(div);
        }

        if (addMorePrizeBtn) addMorePrizeBtn.addEventListener('click', addPrizeRow);
        if (emptyAddBtn) emptyAddBtn.addEventListener('click', addPrizeRow);

        prizeList.addEventListener('click', function (e) {
            if (e.target.closest('.remove-prize')) {
                const row = e.target.closest('.group');
                row.remove();
                if (prizeList.children.length === 0) {
                    location.reload(); // Quick hack to show empty state again
                }
            }
        });
    });
</script>