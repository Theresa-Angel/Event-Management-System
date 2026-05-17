<?php
/**
 * Campus Connect - Edit Event Page (Admin)
 */
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    header("Location: admin.php?page=manage_events");
    exit();
}

$err = '';
$success = '';

// Fetch current event data
$fetch_sql = "SELECT * FROM events WHERE event_id = ?";
$f_stmt = $conn->prepare($fetch_sql);
$f_stmt->bind_param("i", $event_id);
$f_stmt->execute();
$event = $f_stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: admin.php?page=manage_events");
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $rules = trim($_POST['rules']);
    $category = trim($_POST['category']);
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $venue = trim($_POST['venue']);
    $maxAttendees = intval($_POST['max_attendees']);
    $coverImage = trim($_POST['cover_image']);
    $organizerId = intval($_POST['organizer_id']);
    $status = $_POST['status'];

    $isTeam = isset($_POST['participation_type']) && $_POST['participation_type'] === 'team';
    $minTeamSize = $isTeam ? intval($_POST['min_team_size'] ?? 1) : 1;
    $maxTeamSize = $isTeam ? intval($_POST['max_team_size'] ?? 1) : 1;

    $prizes = null;
    if ($category === 'Competition' && isset($_POST['prize_titles']) && is_array($_POST['prize_titles'])) {
        $prizesArray = [];
        foreach ($_POST['prize_titles'] as $index => $pTitle) {
            $pTitle = trim($pTitle);
            if (!empty($pTitle)) {
                $winner = trim($_POST['prize_winners'][$index] ?? '');
                $prizesArray[] = [
                    'title' => $pTitle,
                    'winner' => $winner
                ];
            }
        }
        if (!empty($prizesArray)) {
            $prizes = json_encode($prizesArray);
        }
    }

    if (empty($title) || empty($startDate) || empty($venue)) {
        $err = "Please fill in all required fields.";
    } else {
        $sql = "UPDATE events SET 
                title=?, description=?, rules=?, category=?, 
                start_date=?, end_date=?, venue=?, max_attendees=?, 
                cover_image=?, organizer_id=?, status=?, prizes=?, 
                min_team_size=?, max_team_size=? 
                WHERE event_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssisissiii", $title, $description, $rules, $category, $startDate, $endDate, $venue, $maxAttendees, $coverImage, $organizerId, $status, $prizes, $minTeamSize, $maxTeamSize, $event_id);

        if ($stmt->execute()) {
            $success = "Event details updated successfully!";
            // Re-fetch to show updated data
            $fetch_sql = "SELECT * FROM events WHERE event_id = ?";
            $f_stmt = $conn->prepare($fetch_sql);
            $f_stmt->bind_param("i", $event_id);
            $f_stmt->execute();
            $event = $f_stmt->get_result()->fetch_assoc();
        } else {
            $err = "Error updating event: " . $stmt->error;
        }
    }
}

$current_prizes = $event['prizes'] ? json_decode($event['prizes'], true) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Event</h1>
                <p class="text-slate-500 text-sm">Update event details and settings.</p>
            </div>
            <div class="flex gap-2">
                <a href="view_events.php?id=<?php echo $event['event_id']; ?>"
                    class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                    <i class="fas fa-eye text-sm"></i>
                    <span>View Public</span>
                </a>
                <a href="admin.php?page=manage_events"
                    class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left text-sm"></i>
                    <span>Back to Events</span>
                </a>
            </div>
        </div>

        <?php if ($err): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span class="font-medium">
                    <?php echo $err; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div
                class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-medium">
                    <?php echo $success; ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <form method="POST" class="p-8 space-y-8">
                <input type="hidden" name="update_event" value="1">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Event Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>"
                            required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Assign Organizer *</label>
                        <select name="organizer_id" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                            <?php
                            $org_sql = "SELECT id, username, email FROM users WHERE role IN ('organizer', 'admin') ORDER BY username ASC LIMIT 100";
                            $org_result = $conn->query($org_sql);
                            while ($org_row = $org_result->fetch_assoc()) {
                                $selected = ($org_row['id'] == $event['organizer_id']) ? 'selected' : '';
                                echo '<option value="' . $org_row['id'] . '" ' . $selected . '>' . htmlspecialchars($org_row['username']) . ' (' . htmlspecialchars($org_row['email']) . ')</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Display Status</label>
                        <select name="status" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                            <option value="draft" <?php echo $event['status'] === 'draft' ? 'selected' : ''; ?>>Draft
                            </option>
                            <option value="active" <?php echo $event['status'] === 'active' ? 'selected' : ''; ?>>Active
                            </option>
                            <option value="upcoming" <?php echo $event['status'] === 'upcoming' ? 'selected' : ''; ?>
                                >Upcoming</option>
                            <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'selected' : ''; ?>
                                >Ongoing</option>
                            <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>
                                >Completed</option>
                            <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'selected' : ''; ?>
                                >Cancelled</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Rules & Regulations</label>
                        <textarea name="rules" rows="4"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"><?php echo htmlspecialchars($event['rules']); ?></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Category</label>
                        <select name="category" id="eventCategory"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <?php
                            $cats = ['Workshop', 'Seminar', 'Competition', 'Cultural', 'Sports', 'Arts', 'Symposium', 'Hackathon', 'Webinar', 'Other'];
                            foreach ($cats as $cat) {
                                $selected = $event['category'] === $cat ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Venue *</label>
                        <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>"
                            required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Start Date *</label>
                        <input type="datetime-local" name="start_date"
                            value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_date'])); ?>" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">End Date</label>
                        <input type="datetime-local" name="end_date"
                            value="<?php echo $event['end_date'] ? date('Y-m-d\TH:i', strtotime($event['end_date'])) : ''; ?>"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Max Capacity</label>
                        <input type="number" name="max_attendees" value="<?php echo $event['max_attendees']; ?>" min="1"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Cover Image URL</label>
                        <input type="url" name="cover_image"
                            value="<?php echo htmlspecialchars($event['cover_image']); ?>"
                            class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>

                <div id="prizeSection" class="hidden pt-6 border-t border-slate-100 space-y-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Competition Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Participation Type</label>
                            <select name="participation_type" id="participationType"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                <option value="individual" <?php echo $event['min_team_size'] <= 1 ? 'selected' : ''; ?>
                                    >Individual</option>
                                <option value="team" <?php echo $event['min_team_size'] > 1 ? 'selected' : ''; ?>>Team
                                </option>
                            </select>
                        </div>
                        <div id="teamSizeSection" class="hidden grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Min Team</label>
                                <input type="number" name="min_team_size" value="<?php echo $event['min_team_size']; ?>"
                                    min="1"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Max Team</label>
                                <input type="number" name="max_team_size" value="<?php echo $event['max_team_size']; ?>"
                                    min="1"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-sm font-semibold text-slate-700">Prizes</label>
                            <button type="button" id="addPrizeBtn"
                                class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                                <i class="fas fa-plus mr-1"></i> Add Prize
                            </button>
                        </div>
                        <div id="prizeList" class="space-y-3">
                            <?php foreach ($current_prizes as $prize): ?>
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 relative group">
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase">Prize Title</label>
                                        <input type="text" name="prize_titles[]"
                                            value="<?php echo htmlspecialchars($prize['title']); ?>"
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase">Winner
                                            (Optional)</label>
                                        <input type="text" name="prize_winners[]"
                                            value="<?php echo htmlspecialchars($prize['winner']); ?>"
                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                                    </div>
                                    <button type="button"
                                        class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm flex items-center justify-center transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex items-center justify-end gap-4">
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Update Event</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('eventCategory');
            const prizeSection = document.getElementById('prizeSection');
            const prizeList = document.getElementById('prizeList');
            const addPrizeBtn = document.getElementById('addPrizeBtn');
            const participationType = document.getElementById('participationType');
            const teamSizeSection = document.getElementById('teamSizeSection');

            function togglePrizeSection() {
                if (categorySelect.value === 'Competition') {
                    prizeSection.classList.remove('hidden');
                    if (prizeList.children.length === 0) addPrizeRow();
                } else {
                    prizeSection.classList.add('hidden');
                }
            }

            function toggleTeamSize() {
                if (participationType.value === 'team') {
                    teamSizeSection.classList.remove('hidden');
                    teamSizeSection.classList.add('grid');
                } else {
                    teamSizeSection.classList.add('hidden');
                    teamSizeSection.classList.remove('grid');
                }
            }

            function addPrizeRow() {
                const div = document.createElement('div');
                div.className = 'grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 relative group';
                div.innerHTML = `
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Prize Title</label>
                        <input type="text" name="prize_titles[]" placeholder="e.g. 1st Prize" 
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Winner (Optional)</label>
                        <input type="text" name="prize_winners[]" placeholder="e.g. Winner Name" 
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                    </div>
                    <button type="button" class="remove-prize absolute -top-2 -right-2 bg-white text-red-500 hover:text-red-700 h-6 w-6 rounded-full border border-slate-200 shadow-sm flex items-center justify-center transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                prizeList.appendChild(div);
            }

            categorySelect.addEventListener('change', togglePrizeSection);
            participationType.addEventListener('change', toggleTeamSize);
            addPrizeBtn.addEventListener('click', addPrizeRow);

            prizeList.addEventListener('click', function (e) {
                if (e.target.closest('.remove-prize')) {
                    const row = e.target.closest('.group');
                    if (prizeList.children.length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input').forEach(input => input.value = '');
                    }
                }
            });

            togglePrizeSection();
            toggleTeamSize();
        });
    </script>
</body>

</html>