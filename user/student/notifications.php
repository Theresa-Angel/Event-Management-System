<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Mark All as Read
if (isset($_POST['mark_all_read'])) {
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Handle Delete Notification
if (isset($_POST['delete_notification'])) {
    $notif_id = $_POST['notif_id'];
    $delete_sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

$pageTitle = "Notifications";
$activePage = "notifications";

include 'includes/student_header.php';
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
        <h2 class="text-xl font-bold text-slate-800">Your Notifications 👋</h2>
        <p class="text-slate-500 text-sm">Stay updated with event alerts and system messages.</p>
    </div>
    <form method="POST">
        <button type="submit" name="mark_all_read"
            class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors">
            <i class="fas fa-check-double mr-2"></i> Mark all as read
        </button>
    </form>
</div>

<!-- Notification List -->
<div class="space-y-4">
    <?php
    $notif_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100";
    $stmt = $conn->prepare($notif_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $typeClass = '';
            $typeIcon = 'fa-bell';

            switch ($row['type']) {
                case 'event':
                    $typeClass = 'bg-blue-50 text-blue-600';
                    $typeIcon = 'fa-calendar-alt';
                    break;
                case 'reminder':
                    $typeClass = 'bg-orange-50 text-orange-600';
                    $typeIcon = 'fa-clock';
                    break;
                case 'system':
                    $typeClass = 'bg-slate-50 text-slate-600';
                    $typeIcon = 'fa-cog';
                    break;
                case 'alert':
                    $typeClass = 'bg-red-50 text-red-600';
                    $typeIcon = 'fa-exclamation-triangle';
                    break;
            }

            $readable_time = date('M d, Y h:i A', strtotime($row['created_at']));
            $is_unread = !$row['is_read'];
            ?>
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex items-start gap-4 hover:border-indigo-200 transition-all relative <?php echo $is_unread ? 'border-l-4 border-l-indigo-600' : ''; ?>">
                <div class="p-3 rounded-lg <?php echo $typeClass; ?>">
                    <i class="fas <?php echo $typeIcon; ?> text-lg"></i>
                </div>

                <div class="flex-1">
                    <div class="flex justify-between items-start mb-1">
                        <h4 class="font-bold text-slate-800 <?php echo $is_unread ? '' : 'opacity-80'; ?>">
                            <?php echo htmlspecialchars($row['title']); ?>
                            <?php if ($is_unread): ?>
                                <span class="ml-2 w-2 h-2 bg-indigo-600 rounded-full inline-block"></span>
                            <?php endif; ?>
                        </h4>
                        <span class="text-xs text-slate-400 font-medium">
                            <?php echo $readable_time; ?>
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed mb-3 <?php echo $is_unread ? '' : 'opacity-70'; ?>">
                        <?php echo htmlspecialchars($row['message']); ?>
                    </p>

                    <div class="flex gap-4">
                        <?php if ($row['related_event_id']): ?>
                            <a href="my_registrations.php?id=<?php echo $row['related_event_id']; ?>"
                                class="text-indigo-600 text-xs font-semibold hover:underline">View Event Details</a>
                        <?php endif; ?>
                        <form method="POST" class="inline">
                            <input type="hidden" name="notif_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_notification"
                                class="text-slate-400 hover:text-red-600 text-xs font-semibold transition-colors">
                                <i class="fas fa-trash-alt mr-1"></i> Dismiss
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div
            class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 flex flex-col items-center justify-center text-center">
            <div class="p-6 bg-slate-50 rounded-full text-slate-200 mb-4">
                <i class="fas fa-bell-slash text-6xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">No notifications yet</h3>
            <p class="text-slate-500 max-w-xs">We'll alert you here as soon as there's something new for you!</p>
        </div>
        <?php
    }
    ?>
</div>

<?php include 'includes/student_footer.php'; ?>