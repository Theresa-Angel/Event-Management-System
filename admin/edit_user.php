<?php
/**
 * Campus Connect - Edit User Page
 */
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = $err = '';
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: manage_users.php");
    exit();
}

// Fetch current user data
$fetch_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$user = $fetch_stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $status = $_POST['status'] ?? 'active';

    // Student specific fields
    $roll_number = trim($_POST['roll_number'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $year = trim($_POST['year'] ?? '');

    try {
        if (empty($username) || empty($email)) {
            throw new Exception("Username and Email are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check if email already exists for OTHER users
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Email already registered by another user.");
        }

        // Transaction for safety
        $conn->begin_transaction();

        $sql = "UPDATE users SET username = ?, email = ?, role = ?, status = ?, roll_number = ?, department = ?, year = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $username, $email, $role, $status, $roll_number, $department, $year, $user_id);
        $stmt->execute();

        // Update password if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $upd_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd_pass->bind_param("si", $hashed_password, $user_id);
            $upd_pass->execute();
        }

        $conn->commit();
        $_SESSION['success_message'] = "User $username updated successfully!";
        header("Location: manage_users.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $err = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Campus Connect</title>
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
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit User Account</h1>
                <p class="text-slate-500 text-sm">Managing account for: <span
                        class="text-indigo-600 font-semibold"><?php echo htmlspecialchars($user['email']); ?></span></p>
            </div>
            <a href="manage_users.php"
                class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>Back to List</span>
            </a>
        </div>

        <?php if ($err): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span class="font-medium"><?php echo $err; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <form method="POST" class="p-8 space-y-8">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Username *</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Email Address *</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">New Password (leave blank to keep
                            current)</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" placeholder="••••••••"
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Role</label>
                        <div class="relative">
                            <i class="fas fa-user-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select name="role" id="roleSelect" onchange="toggleStudentFields()"
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all appearance-none cursor-pointer font-medium text-slate-700">
                                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>
                                    Student</option>
                                <option value="organizer" <?php echo $user['role'] === 'organizer' ? 'selected' : ''; ?>>
                                    Organizer</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                </option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Account Status</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="active" <?php echo $user['status'] === 'active' ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-sm text-slate-600">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="pending" <?php echo $user['status'] === 'pending' ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-sm text-slate-600">Pending</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="status" value="inactive" <?php echo $user['status'] === 'inactive' ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-sm text-slate-600">Inactive</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Student Specific Info -->
                <div id="studentFields" class="pt-6 border-t border-slate-100 space-y-6">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Academic Details (Student
                        Only)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Roll Number</label>
                            <input type="text" name="roll_number"
                                value="<?php echo htmlspecialchars($user['roll_number'] ?? ''); ?>"
                                placeholder="e.g. 2024CS01"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all font-medium text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Department</label>
                            <select name="department"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all appearance-none cursor-pointer font-medium text-slate-700">
                                <option value="">Select Department</option>
                                <option value="Computer Science" <?php echo ($user['department'] ?? '') === 'Tamil' ? 'selected' : ''; ?>>Tamil/option>
                                <option value="Information Technology" <?php echo ($user['department'] ?? '') === 'English' ? 'selected' : ''; ?>>English</option>
                                <option value="Electronics" <?php echo ($user['department'] ?? '') === 'Commerce' ? 'selected' : ''; ?>></option>Commerce</option>
                                <option value="Mechanical" <?php echo ($user['department'] ?? '') === 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                                <option value="Civil" <?php echo ($user['department'] ?? '') === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Year</label>
                            <select name="year"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all appearance-none cursor-pointer font-medium text-slate-700">
                                <option value="">Select Year</option>
                                <option value="1st Year" <?php echo ($user['year'] ?? '') === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo ($user['year'] ?? '') === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo ($user['year'] ?? '') === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="pt-8 border-t border-slate-100 flex items-center justify-end gap-4 text-sm">
                    <a href="manage_users.php"
                        class="px-6 py-2.5 text-slate-500 hover:text-slate-800 font-semibold transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Update User Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleStudentFields() {
            const role = document.getElementById('roleSelect').value;
            const studentFields = document.getElementById('studentFields');
            if (role === 'student') {
                studentFields.classList.remove('hidden');
            } else {
                studentFields.classList.add('hidden');
            }
        }
        // Initial check
        window.onload = toggleStudentFields;
    </script>
</body>

</html>