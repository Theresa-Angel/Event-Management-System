<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$event_id) {
    header("Location: student.php");
    exit();
}

// Fetch Event Details
$event = null;
$sql = "SELECT e.*, u.username as organizer_name,
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as registered_count,
        (SELECT status FROM registrations WHERE event_id = e.event_id AND user_id = ?) as registration_status,
        (SELECT ticket_id FROM registrations WHERE event_id = e.event_id AND user_id = ?) as ticket_id
        FROM events e 
        LEFT JOIN users u ON e.organizer_id = u.id 
        WHERE e.event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $user_id, $event_id);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();

if (!$event) {
    header("Location: student.php");
    exit();
}

$pageTitle = "Event Details - " . $event['title'];
$activePage = "events";

include 'includes/student_header.php';
include 'includes/student_sidebar.php';
include 'includes/student_topbar.php';
?>

<script>
    function shareOnFacebook() {
        const url = encodeURIComponent(window.location.href);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
    }
    function shareOnTwitter() {
        const text = encodeURIComponent("Check out this event: <?php echo addslashes($event['title']); ?>");
        const url = encodeURIComponent(window.location.href);
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=600,height=400');
    }
    function shareOnWhatsApp() {
        const text = encodeURIComponent("Check out this event: <?php echo addslashes($event['title']); ?> " + window.location.href);
        window.open(`https://wa.me/?text=${text}`, '_blank');
    }
</script>

<div class="mb-6">
    <a href="student.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header/Cover -->
    <div class="relative h-64 md:h-80 bg-slate-100">
        <?php if (!empty($event['cover_image'])):
            $imgSrc = $event['cover_image'];
            $displayImg = $imgSrc;
            if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '/') !== 0) {
                $displayImg = "../../" . $imgSrc;
            }
            ?>
            <img src="<?php echo $displayImg; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>"
                class="w-full h-full object-cover">
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-slate-300">
                <i class="fas fa-image text-6xl"></i>
            </div>
        <?php endif; ?>

        <div class="absolute top-4 right-4">
            <span class="px-4 py-2 bg-white/90 backdrop-blur rounded-full text-indigo-600 font-bold shadow-lg">
                <?php echo htmlspecialchars($event['category']); ?>
            </span>
        </div>
    </div>

    <div class="p-6 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start gap-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-slate-800 mb-4">
                    <?php echo htmlspecialchars($event['title']); ?>
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date & Time</p>
                            <p class="text-sm font-semibold">
                                <?php echo date('M d, Y', strtotime($event['start_date'])); ?>
                            </p>
                            <p class="text-xs text-slate-500">
                                <?php echo date('h:i A', strtotime($event['start_date'])); ?> onwards
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Venue</p>
                            <p class="text-sm font-semibold">
                                <?php echo htmlspecialchars($event['venue']); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Organizer</p>
                            <p class="text-sm font-semibold">
                                <?php echo htmlspecialchars($event['organizer_name'] ?? 'College Admin'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Attendance</p>
                            <p class="text-sm font-semibold">
                                <?php echo $event['registered_count']; ?> /
                                <?php echo $event['max_attendees'] ?: '∞'; ?> Registered
                            </p>
                        </div>
                    </div>
                </div>

                <div class="prose prose-slate max-w-none mb-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-3">About this Event</h3>
                    <p class="text-slate-600 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($event['description'] ?: 'No description provided.'); ?>
                    </p>
                </div>

                <?php if (!empty($event['rules'])): ?>
                    <div class="prose prose-slate max-w-none mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-3">Rules & Regulations</h3>
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                            <ul class="list-disc list-inside text-slate-700 space-y-2 marker:text-indigo-500">
                                <?php
                                $rules = explode("\n", $event['rules']);
                                foreach ($rules as $rule) {
                                    $rule = trim($rule);
                                    if (!empty($rule)) {
                                        echo "<li>" . htmlspecialchars($rule) . "</li>";
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $prizes = json_decode($event['prizes'] ?? '[]', true);
                if (!empty($prizes)):
                    ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800 mb-3">Prizes & Recognition</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <?php foreach ($prizes as $prize): ?>
                                <div
                                    class="flex justify-between items-center p-4 bg-yellow-50/50 rounded-xl border border-yellow-100">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-trophy text-yellow-600"></i>
                                        <span class="font-bold text-yellow-900">
                                            <?php echo htmlspecialchars($prize['title']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($prize['winner'])): ?>
                                        <span
                                            class="bg-yellow-200 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                            Winner:
                                            <?php echo htmlspecialchars($prize['winner']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Sidebar -->
            <div class="w-full md:w-72">
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 sticky top-6">
                    <?php if ($event['registration_status']): ?>
                        <div class="text-center mb-6">
                            <div
                                class="w-24 h-24 bg-white border border-slate-200 rounded-xl flex items-center justify-center mx-auto mb-4 overflow-hidden shadow-sm">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($event['ticket_id']); ?>&bgcolor=f8fafc"
                                    alt="Ticket QR" class="w-full h-full object-contain">
                            </div>
                            <h4 class="font-bold text-slate-800">You're Registered!</h4>
                            <p class="text-[10px] text-slate-400 font-mono mt-1 uppercase tracking-tighter">Ticket:
                                <?php echo htmlspecialchars($event['ticket_id']); ?>
                            </p>
                        </div>
                        <a href="my_registrations.php"
                            class="block w-full text-center py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition">
                            Manage Registration
                        </a>
                    <?php elseif ($event['status'] === 'cancelled'): ?>
                        <div class="text-center py-4 bg-red-50 rounded-xl text-red-600 font-bold border border-red-100">
                            Event Cancelled
                        </div>
                    <?php elseif (strtotime($event['start_date']) < time()): ?>
                        <div class="text-center py-4 bg-slate-200 rounded-xl text-slate-500 font-bold">
                            Event Finished
                        </div>
                    <?php elseif ($event['max_attendees'] > 0 && $event['registered_count'] >= $event['max_attendees']): ?>
                        <div class="text-center py-4 bg-red-50 rounded-xl text-red-600 font-bold border border-red-100">
                            Event Full
                        </div>
                    <?php else: ?>
                        <h4 class="font-bold text-slate-800 mb-4 text-center">Ready to join?</h4>
                        <a href="../../register_event.php?event_id=<?php echo $event['event_id']; ?>"
                            class="block w-full text-center py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            Register Now
                        </a>
                    <?php endif; ?>

                    <div class="mt-6 pt-6 border-t border-slate-200 text-center">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-3">Share Event</p>
                        <div class="flex justify-center gap-3">
                            <button onclick="shareOnFacebook()"
                                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs"><i
                                    class="fab fa-facebook-f"></i></button>
                            <button onclick="shareOnTwitter()"
                                class="w-8 h-8 rounded-full bg-blue-50 text-blue-400 flex items-center justify-center text-xs"><i
                                    class="fab fa-twitter"></i></button>
                            <button onclick="shareOnWhatsApp()"
                                class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs"><i
                                    class="fab fa-whatsapp"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/student_footer.php'; ?>