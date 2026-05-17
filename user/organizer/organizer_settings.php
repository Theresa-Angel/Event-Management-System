<?php
// Handle Settings Update
$msg = '';
$msgClass = '';

$organizerId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);

    // Update User
    $upSql = "UPDATE users SET username = ?, email = ?, phone = ?, department = ? WHERE id = ?";
    if ($upStmt = $conn->prepare($upSql)) {
        $upStmt->bind_param("ssssi", $username, $email, $phone, $department, $organizerId);
        if ($upStmt->execute()) {
            $_SESSION['username'] = $username; // Sync session
            $_SESSION['email'] = $email;
            $msg = "Profile updated successfully!";
            $msgClass = "bg-green-50 text-green-700 border-green-200";
        } else {
            $msg = "Error updating profile: " . $conn->error;
            $msgClass = "bg-red-50 text-red-700 border-red-200";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $currentPass = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    if ($newPass !== $confirmPass) {
        $msg = "New passwords do not match!";
        $msgClass = "bg-red-50 text-red-700 border-red-200";
    } else {
        // Verify current password
        $passSql = "SELECT password FROM users WHERE id = ?";
        if ($passStmt = $conn->prepare($passSql)) {
            $passStmt->bind_param("i", $organizerId);
            $passStmt->execute();
            $pResult = $passStmt->get_result();
            $user = $pResult->fetch_assoc();

            if (password_verify($currentPass, $user['password'])) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $upPassSql = "UPDATE users SET password = ? WHERE id = ?";
                $upPassStmt = $conn->prepare($upPassSql);
                $upPassStmt->bind_param("si", $hash, $organizerId);
                $upPassStmt->execute();

                $msg = "Password updated successfully!";
                $msgClass = "bg-green-50 text-green-700 border-green-200";
            } else {
                $msg = "Current password is incorrect.";
                $msgClass = "bg-red-50 text-red-700 border-red-200";
            }
        }
    }
}

// Fetch current data
$orgSql = "SELECT * FROM users WHERE id = ?";
$organizerData = [];
if ($stmt = $conn->prepare($orgSql)) {
    $stmt->bind_param("i", $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $organizerData = $result->fetch_assoc();
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Account Settings</h2>
            <p class="text-slate-500">Manage your profile and security</p>
        </div>
        <div class="flex gap-3">
            <a href="?action=dashboard" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-home mr-1"></i> Dashboard
            </a>
            <a href="?action=profile" class="text-slate-500 hover:text-slate-700 font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Back to Profile
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 border rounded-lg flex items-center <?php echo $msgClass; ?>">
            <i
                class="fas <?php echo strpos($msgClass, 'green') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Edit Profile -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200">
                <h3 class="font-bold text-slate-700 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-indigo-500"></i> Edit Profile Information
                </h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="update_profile" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Full Name</label>
                    <input type="text" name="username"
                        value="<?php echo htmlspecialchars($organizerData['username']); ?>" required
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($organizerData['email']); ?>"
                        required
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone"
                        value="<?php echo htmlspecialchars($organizerData['phone'] ?? ''); ?>"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Department /
                        Organization</label>
                    <input type="text" name="department"
                        value="<?php echo htmlspecialchars($organizerData['department'] ?? ''); ?>"
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border"
                        placeholder="e.g. Computer Science Dept">
                </div>
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition font-medium">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200">
                <h3 class="font-bold text-slate-700 flex items-center">
                    <i class="fas fa-key mr-2 text-indigo-500"></i> Update Password
                </h3>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="update_password" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">New Password</label>
                    <input type="password" name="new_password" required
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                        class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5 border">
                </div>
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition font-medium">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>