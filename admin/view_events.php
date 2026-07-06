<?php
/**
 * Campus Connect - View Event Page (Admin)
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

// Fetch event data with organizer name
$sql = "SELECT e.*, u.username as organizer_name 
        FROM events e 
        LEFT JOIN users u ON e.organizer_id = u.id 
        WHERE e.event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header("Location: admin.php?page=manage_events");
    exit();
}

$prizes = $event['prizes'] ? JSON_decode($event['prizes'], true) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event -
        <?php echo htmlspecialchars($event['title']); ?>
    </title>
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
    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?php
                    echo match (strtolower($event['status'])) {
                        'active' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        'completed' => 'bg-blue-100 text-blue-700',
                        default => 'bg-slate-100 text-slate-700'
                    };
                    ?>">
                        <?php echo htmlspecialchars($event['status']); ?>
                    </span>
                    <span class="text-slate-400 text-xs font-medium">ID: #
                        <?php echo $event['event_id']; ?>
                    </span>
                </div>
                <h1 class="text-3xl font-bold text-slate-800">
                    <?php echo htmlspecialchars($event['title']); ?>
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="admin.php?page=manage_events"
                    class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
                <a href="edit_events.php?id=<?php echo $event['event_id']; ?>"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit Event</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Main Info -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Cover Image -->
                <?php if ($event['cover_image']): ?>
                    <div class="w-full h-80 rounded-2xl overflow-hidden shadow-lg border border-slate-200 bg-slate-100">
                        <img src="<?php echo htmlspecialchars($event['cover_image']); ?>" class="w-full h-full object-cover"
                            alt="Cover">
                    </div>
                <?php endif; ?>

                <!-- Description & Rules -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-align-left text-indigo-500 text-sm"></i>
                            Description
                        </h3>
                        <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">
                            <?php echo htmlspecialchars($event['description'] ?: 'No description provided.'); ?>
                        </p>
                    </div>

                    <?php if ($event['rules']): ?>
                        <div class="pt-6 border-t border-slate-100">
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-gavel text-indigo-500 text-sm"></i>
                                Rules & Regulations
                            </h3>
                            <div
                                class="text-slate-600 leading-relaxed whitespace-pre-wrap bg-slate-50 p-6 rounded-xl border border-slate-100">
                                <?php echo htmlspecialchars($event['rules']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Prize Section (Competitions) -->
                <?php if ($event['category'] === 'Competition' && !empty($prizes)): ?>
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-trophy text-yellow-500"></i>
                            Prizes & Awards
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($prizes as $prize): ?>
                                <div
                                    class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                            <?php echo htmlspecialchars($prize['title']); ?>
                                        </p>
                                        <p class="text-sm font-semibold text-slate-700">
                                            <?php echo htmlspecialchars($prize['winner'] ?: 'TBD'); ?>
                                        </p>
                                    </div>
                                    <i class="fas fa-award text-slate-200 text-2xl"></i>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: Logistics & Stats -->
            <div class="space-y-8">
                <!-- Logistics Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Event Information</h3>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Starts On</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <?php echo date('D, M j, Y @ g:i A', strtotime($event['start_date'])); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Ends On</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <?php echo date('D, M j, Y @ g:i A', strtotime($event['end_date'])); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Venue</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Category</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <?php echo htmlspecialchars($event['category']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Organizer</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <?php echo htmlspecialchars($event['organizer_name'] ?: 'System Admin'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Capacity Stats -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Registration Status</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-end mb-1">
                            <p class="text-2xl font-bold text-slate-800">
                                <?php
                                $reg_sql = "SELECT COUNT(*) as count FROM registrations WHERE event_id = ?";
                                $r_stmt = $conn->prepare($reg_sql);
                                $r_stmt->bind_param("i", $event_id);
                                $r_stmt->execute();
                                $reg_count = $r_stmt->get_result()->fetch_assoc()['count'];
                                echo $reg_count;
                                ?>
                            </p>
                            <p class="text-slate-400 text-xs font-medium">/
                                <?php echo $event['max_attendees']; ?> Max
                            </p>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full transition-all duration-1000"
                                style="width: <?php echo min(100, ($reg_count / $event['max_attendees']) * 100); ?>%">
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 italic">
                            <?php
                            if ($reg_count >= $event['max_attendees'])
                                echo "Registration is currently full.";
                            else
                                echo ($event['max_attendees'] - $reg_count) . " spots remaining.";
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Team Config (If applicable) -->
                <?php if ($event['min_team_size'] > 1 || $event['max_team_size'] > 1): ?>
                    <div class="bg-indigo-900 rounded-2xl shadow-lg p-6 text-white overflow-hidden relative">
                        <i class="fas fa-users absolute -right-4 -bottom-4 text-8xl text-indigo-800 opacity-50"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-4 opacity-70">Team Requirements</h3>
                        <div class="grid grid-cols-2 gap-4 relative z-10">
                            <div>
                                <p class="text-[10px] font-bold opacity-60 uppercase">Min Team Size</p>
                                <p class="text-lg font-bold">
                                    <?php echo $event['min_team_size']; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold opacity-60 uppercase">Max Team Size</p>
                                <p class="text-lg font-bold">
                                    <?php echo $event['max_team_size']; ?>
                                </p>
                            </div>
                        </div>
                        <p class="mt-4 text-xs opacity-60">This event requires team participation.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>