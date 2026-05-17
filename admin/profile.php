<?php
/**
 * Campus Connect - Profile Page
 */
require_once '../config.php';
require_once '../includes/functions.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Handle profile picture upload
    $profile_picture = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (
            in_array($_FILES['avatar']['type'], $allowed_types) &&
            $_FILES['profile_picture']['size'] <= $max_size
        ) {

            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/profiles/' . $filename;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if ($profile_picture && file_exists($profile_picture)) {
                    unlink($profile_picture);
                }
                $profile_picture = $upload_path;
            }
        }
    }

    $update_sql = "UPDATE users SET username = ?, email = ?, phone = ?, avatar = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $username, $email, $phone, $profile_picture, $user_id);

    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['avatar'];
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_pass_stmt = $conn->prepare($update_pass_sql);
            $update_pass_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_pass_stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
        } else {
            $error_message = "New passwords do not match!";
        }
    } else {
        $error_message = "Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Campus Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-slate-900">Account Settings</h1>
            <a href="admin.php" class="text-slate-500 hover:text-slate-700 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <?php if ($success_message): ?>
            <div
                class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="font-medium"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span class="font-medium"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 text-center border-b border-slate-100">
                        <div class="relative inline-block group">
                            <div
                                class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl border-2 border-indigo-200 overflow-hidden">
                                <?php if ($user['avatar']): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profile"
                                        class="h-full w-full object-cover">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <label for="profile_pic_input"
                                class="absolute bottom-0 right-0 h-8 w-8 bg-indigo-600 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-indigo-700 transition shadow-lg">
                                <i class="fas fa-camera text-xs"></i>
                            </label>
                        </div>
                        <h2 class="mt-4 font-bold text-slate-800 text-lg">
                            <?php echo htmlspecialchars($user['username']); ?></h2>
                        <p class="text-slate-500 text-sm"><?php echo htmlspecialchars($user['role']); ?></p>
                        <div
                            class="mt-3 inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            <?php echo $user['status']; ?>
                        </div>
                    </div>
                </div>

                <!-- Account Management -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h4 class="font-semibold text-gray-800 mb-4">Account Management</h4>
                    <div class="space-y-3">
                        <button onclick="showDeleteModal()"
                            class="w-full text-left px-4 py-3 border border-red-200 rounded-lg hover:bg-red-50 transition-colors text-red-600">
                            <i class="fas fa-trash-alt mr-3"></i>
                            <span>Delete Account</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Forms -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Profile Information -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800">Profile Information</h3>
                        <p class="text-slate-500 text-sm">Update your account detail and contact information.</p>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                        <input type="file" name="avatar" id="profile_pic_input" class="hidden" accept="image/*">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Username</label>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                    required
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Phone Number</label>
                                <input type="text" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Role</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled
                                    class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-lg text-slate-500 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" name="update_profile"
                                class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800">Change Password</h3>
                        <p class="text-slate-500 text-sm">Ensure your account is using a long, random password to stay
                            secure.</p>
                    </div>
                    <form method="POST" class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Current Password</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">New Password</label>
                                <input type="password" name="new_password" required
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Confirm New Password</label>
                                <input type="password" name="confirm_password" required
                                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" name="change_password"
                                class="px-8 py-2.5 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl scale-95 transition-transform"
            id="deleteModalCard">
            <div class="text-center">
                <div
                    class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Delete Account?</h3>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    This action is permanent and cannot be undone. All your data including events and registrations will
                    be potentially affected.
                </p>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()"
                        class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button onclick="deleteAccount()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('profile_pic_input').onchange = function (evt) {
            const [file] = this.files;
            if (file) {
                // Self-submit form for simplicity in this demo
                this.form.submit();
            }
        }

        // Export data
        function exportData() {
            if (confirm('This will export all your account data. Continue?')) {
                alert('In a real application, this would trigger a data export process.');
                // In real app: window.location.href = 'export_data.php';
            }
        }

        // Delete account modal
        function showDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        function deleteAccount() {
            if (confirm('This action is irreversible. Are you absolutely sure?')) {
                alert('In a real application, this would delete your account.');
                // In real app: window.location.href = 'delete_account.php';
                closeDeleteModal();
            }
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>

</html>