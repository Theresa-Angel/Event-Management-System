<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = $err = '';

// Update Profile Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $conn->begin_transaction();
    try {
        // 1. Update Username if changed
        if (!empty($new_username) && $new_username !== $_SESSION['username']) {
            // Check if username already exists
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->bind_param("si", $new_username, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Username already exists. Please choose another.");
            }

            $upd = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $upd->bind_param("si", $new_username, $user_id);
            $upd->execute();
            $_SESSION['username'] = $new_username; // Sync session
        }

        // 2. Update Phone
        $upd_phone = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $upd_phone->bind_param("si", $phone, $user_id);
        $upd_phone->execute();

        // 3. Update Password if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd_pass->bind_param("si", $hash, $user_id);
            $upd_pass->execute();
        }

        $conn->commit();
        $msg = "Profile updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $err = $e->getMessage();
    }
}

// Fetch Latest User Data
$sql = "SELECT username, email, phone, roll_number, department, year FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pageTitle = "My Profile";
$activePage = "profile";

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

<div class="max-w-3xl">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800">Account Settings</h2>
        <p class="text-slate-500 text-sm">Update your personal information and security settings.</p>
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

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8">
            <form method="POST" class="space-y-8">
                <!-- Academic Information (Read Only) -->
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Academic Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-500">Email Address</label>
                            <p class="text-sm text-slate-800 font-medium">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-500">Department</label>
                            <p class="text-sm text-slate-800 font-medium">
                                <?php echo htmlspecialchars($user['department']); ?>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-slate-500">Roll Number</label>
                            <p class="text-sm text-slate-800 font-medium">
                                <?php echo htmlspecialchars($user['roll_number']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-amber-50 rounded-lg flex items-start gap-3 border border-amber-100">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <p class="text-xs text-amber-700 leading-relaxed">
                            Academic details are managed by the administration. Please contact the registrar's office if
                            your department or roll number is incorrect.
                        </p>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Editable Fields -->
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 block">Username / Display Name</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>"
                                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700"
                                    required>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600 block">Phone Number</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                    class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Security</h3>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-600 block">New Password</label>
                        <div class="relative max-w-md">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" placeholder="Leave blank to keep current"
                                class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Must be at least 6 characters long.</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                    <button type="reset" class="px-6 py-2 text-slate-600 hover:text-slate-800 font-semibold transition">
                        Reset Changes
                    </button>
                    <button type="submit"
                        class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 flex items-center gap-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>Save All Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/student_footer.php'; ?>