<?php
/**
 * Campus Connect - User Management API
 * Actions: fetch, add, edit
 */
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    if ($action === 'fetch' && isset($_GET['id'])) {
        $user_id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT id, username, email, role, status, roll_number, department, year FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } elseif ($action === 'status' && isset($_POST['user_id']) && isset($_POST['status'])) {
        $user_id = intval($_POST['user_id']);
        $new_status = $_POST['status'];

        $upd = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $upd->bind_param("si", $new_status, $user_id);

        if ($upd->execute()) {
            echo json_encode(['success' => true, 'message' => "User status updated to $new_status."]);
        } else {
            throw new Exception("Error updating status.");
        }
    } elseif ($action === 'add' || $action === 'edit') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'student';
        $status = $_POST['status'] ?? 'active';
        $password = $_POST['password'] ?? '';

        // Student fields
        $roll_number = trim($_POST['roll_number'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $year = trim($_POST['year'] ?? '');

        if (empty($username) || empty($email)) {
            throw new Exception("Username and Email are required.");
        }

        if ($action === 'add') {
            if (empty($password))
                throw new Exception("Password is required for new users.");

            // Check email exist
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0)
                throw new Exception("Email already registered.");

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role, status, roll_number, department, year, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $username, $email, $hashed, $role, $status, $roll_number, $department, $year);
        } else {
            $user_id = intval($_POST['user_id']);

            // Check email exist for others
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0)
                throw new Exception("Email already registered by another user.");

            $sql = "UPDATE users SET username = ?, email = ?, role = ?, status = ?, roll_number = ?, department = ?, year = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $username, $email, $role, $status, $roll_number, $department, $year, $user_id);
            $stmt->execute();

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upd_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd_pass->bind_param("si", $hashed, $user_id);
                $upd_pass->execute();
            }
        }

        if ($stmt->execute() || $action === 'edit') { // edit stmt might have no changes but still success
            echo json_encode(['success' => true, 'message' => "User " . ($action === 'add' ? 'added' : 'updated') . " successfully."]);
        } else {
            throw new Exception("Database error: " . $conn->error);
        }
    } else {
        throw new Exception("Invalid action.");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>