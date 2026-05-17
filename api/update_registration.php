<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

$reg_id = intval($_POST['reg_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$reg_id || !in_array($action, ['confirm', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Security: Check if the user owning the event associated with this registration is the one making the request
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

if (!$is_admin) {
    $stmt = $conn->prepare("
        SELECT e.organizer_id FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $reg_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || $result['organizer_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not own this event']);
        exit();
    }
}

$new_status = ($action === 'confirm') ? 'confirmed' : 'cancelled';

$stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $reg_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration status updated to ' . $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>