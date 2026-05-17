<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = $err = "";

// Handle Cancellation
if (isset($_POST['cancel_reg_id'])) {
    $event_id = intval($_POST['cancel_reg_id']);
    // Check if event is in the future
    $check_sql = "SELECT start_date FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (strtotime($row['start_date']) > time()) {
            // Get status before removal
            $status_stmt = $conn->prepare("SELECT status FROM registrations WHERE user_id = ? AND event_id = ?");
            $status_stmt->bind_param("ii", $user_id, $event_id);
            $status_stmt->execute();
            $curr_status = $status_stmt->get_result()->fetch_assoc()['status'] ?? '';

            $del_sql = "DELETE FROM registrations WHERE user_id = ? AND event_id = ?";
            $del_stmt = $conn->prepare($del_sql);
            $del_stmt->bind_param("ii", $user_id, $event_id);

            if ($del_stmt->execute()) {
                $msg = "Registration cancelled successfully.";

                // Promote from waitlist if the cancelled one was confirmed
                if ($curr_status === 'confirmed') {
                    $promote_stmt = $conn->prepare("SELECT user_id FROM registrations WHERE event_id = ? AND status = 'waitlisted' ORDER BY registration_date ASC LIMIT 1");
                    $promote_stmt->bind_param("i", $event_id);
                    $promote_stmt->execute();
                    $waitlisted = $promote_stmt->get_result()->fetch_assoc();

                    if ($waitlisted) {
                        $upd_stmt = $conn->prepare("UPDATE registrations SET status = 'confirmed' WHERE user_id = ? AND event_id = ?");
                        $upd_stmt->bind_param("ii", $waitlisted['user_id'], $event_id);
                        $upd_stmt->execute();
                        createNotification($waitlisted['user_id'], "Waitlist Promotion", "You have been promoted to confirmed status for an event!", "success", $event_id);
                    }
                }
            }
        } else {
            $err = "Cannot cancel past or ongoing events.";
        }
    }
}

$pageTitle = "My Registrations";
$activePage = "registrations";

include 'includes/student_header.php';
?>
<style>
    .event-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: transform 0.3s;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
    }

    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .card-header-banner {
        height: 100px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        padding: 20px;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .btn-cancel {
        width: 100%;
        border: 1px solid #ef4444;
        color: #ef4444;
        background: white;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #fee2e2;
    }
</style>

<?php
include 'includes/student_sidebar.php';
include 'includes/student_topbar.php';
?>

<div class="mb-6">
    <a href="student.php"
        class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2 w-fit">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">My Registered Events</h2>
        <p class="text-slate-500 text-sm">Manage your upcoming and past registrations.</p>
    </div>
    <a href="event_catalog.php"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Browse
        Catalog</a>
</div>

<?php if ($msg): ?>
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg flex items-center gap-3">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $msg; ?></span>
    </div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $err; ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $sql = "SELECT e.*, r.user_id, r.status, r.ticket_id, r.qr_code FROM registrations r 
            JOIN events e ON r.event_id = e.event_id 
            WHERE r.user_id = ? 
            ORDER BY e.start_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = new DateTime($row['start_date']);
            $isPast = $date->getTimestamp() < time();
            ?>
            <div class="event-card <?php echo $isPast ? 'opacity-70' : ''; ?>">
                <div class="card-header-banner <?php echo $isPast ? 'grayscale' : ''; ?>"
                    style="<?php echo $isPast ? 'background:#64748b;' : ''; ?>">
                    <h3 class="text-lg font-bold truncate">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>
                    <div class="flex items-center gap-2 text-sm opacity-90">
                        <i class="far fa-calendar"></i>
                        <?php echo $date->format('M d, Y'); ?>
                    </div>
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400"><i class="far fa-clock mr-2"></i>Time</span>
                            <span class="text-slate-700 font-medium"><?php echo $date->format('h:i A'); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400"><i class="fas fa-map-marker-alt mr-2"></i>Venue</span>
                            <span
                                class="text-slate-700 font-medium truncate ml-4"><?php echo htmlspecialchars($row['venue']); ?></span>
                        </div>
                        <div class="flex justify-between text-sm items-center">
                            <span class="text-slate-400">Status</span>
                            <?php
                            $status = $row['status'] ?? ($isPast ? 'Completed' : 'Upcoming');
                            $statusColor = 'bg-green-100 text-green-700';
                            if ($status === 'waitlisted')
                                $statusColor = 'bg-yellow-100 text-yellow-700';
                            if ($isPast)
                                $statusColor = 'bg-slate-100 text-slate-600';
                            ?>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase <?php echo $statusColor; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </div>
                        <?php if ($status === 'confirmed'): ?>
                            <div class="pt-4 mt-4 border-t border-dashed border-slate-200">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Ticket ID</p>
                                        <p class="text-xs font-mono text-slate-700">
                                            <?php echo htmlspecialchars($row['ticket_id']); ?>
                                        </p>
                                    </div>
                                    <div class="h-20 w-20 bg-white border-2 border-slate-300 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0"
                                       title="Scan for Check-in">
                                        <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=<?php echo urlencode($row['ticket_id']); ?>&choe=UTF-8"
                                            alt="QR Code" 
                                            onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($row['ticket_id']); ?>';"
                                            class="w-full h-full object-contain p-1">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isPast): ?>
                        <div class="mt-auto">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this registration?');">
                                <input type="hidden" name="cancel_reg_id" value="<?php echo $row['event_id']; ?>">
                                <button type="submit" class="btn-cancel">Cancel Registration</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mt-auto">
                            <a href="view_event.php?id=<?php echo $row['event_id']; ?>"
                                class="block text-center w-full bg-slate-50 text-slate-500 py-2 rounded-lg text-sm font-semibold border border-slate-200">View
                                Event details</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="col-span-full py-12 text-center bg-white rounded-xl border border-slate-200 shadow-sm">
            <i class="fas fa-calendar-alt text-5xl text-slate-100 mb-4"></i>
            <p class="text-slate-500">You haven't registered for any events yet.</p>
            <a href="event_catalog.php" class="mt-4 inline-block text-indigo-600 font-semibold">Start exploring events →</a>
        </div>
        <?php
    }
    ?>
</div>

<?php include 'includes/student_footer.php'; ?>