<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch student details
$user_stmt = $conn->prepare("SELECT username, email, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $username = $user_data['username'];
    $email = $user_data['email'];
    $phone = $user_data['phone'];
    $role = 'student';

    $sql = "INSERT INTO feedback (user_id, username, email, phone, role, subject, message, status, submission_date) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $username, $email, $phone, $role, $subject, $message);

    if ($stmt->execute()) {
        $success_msg = "Thank you for your feedback! We will review it shortly.";
    } else {
        $error_msg = "Something went wrong. Please try again later.";
    }
}

// Fetch feedback history for this user
$history_sql = "SELECT subject, message, status, submission_date, response, response_date FROM feedback WHERE user_id = ?
ORDER BY submission_date DESC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$feedback_history = $history_stmt->get_result();

$pageTitle = "Help & Feedback";
$activePage = "feedback";

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

<div class="max-w-2xl">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Support & Feedback</h2>
        <p class="text-slate-500 text-sm">Have a question or want to share your thoughts? We're here to help.</p>
    </div>

    <?php if ($success_msg): ?>
        <div
            class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success_msg; ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_msg; ?></span>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-8">
        <!-- Feedback Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-fit">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Send New Message</h3>
            </div>
            <div class="p-6">
                <form method="POST" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-600 block">Category</label>
                        <select name="subject"
                            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all bg-white text-sm">
                            <option>General Inquiry</option>
                            <option>Bug Report</option>
                            <option>Feature Request</option>
                            <option>Event Complaint</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-600 block">Message</label>
                        <textarea name="message" rows="4"
                            class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-slate-400 text-sm"
                            placeholder="How can we help you?" required></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="submit_feedback"
                            class="w-full py-2.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                            <span>Send Feedback</span>
                            <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History -->
        <div class="space-y-4">
            <h3 class="font-bold text-slate-800 px-2">Recent Support History</h3>
            <?php if ($feedback_history->num_rows > 0): ?>
                <?php while ($row = $feedback_history->fetch_assoc()): ?>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($row['subject']); ?>
                                </h4>
                                <span class="text-[10px] text-slate-400 font-medium">
                                    <?php echo date('M d, Y', strtotime($row['submission_date'])); ?>
                                </span>
                            </div>
                            <?php $status = $row['status']; ?>
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase 
                                <?php echo $status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'; ?>">
                                <?php echo $status; ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3"><?php echo htmlspecialchars($row['message']); ?></p>

                        <?php if ($row['response']): ?>
                            <div class="bg-indigo-50 p-3 rounded-lg border-l-2 border-indigo-400">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-reply text-indigo-500 text-[10px]"></i>
                                    <span class="text-[10px] font-bold text-indigo-700 uppercase">Admin Response</span>
                                </div>
                                <p class="text-[11px] text-indigo-800 leading-relaxed">
                                    <?php echo htmlspecialchars($row['response']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-8 text-center">
                    <i class="fas fa-comments text-slate-300 text-3xl mb-3"></i>
                    <p class="text-sm text-slate-400">No support history found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/student_footer.php'; ?>