<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || (getUserRole() !== 'organizer' && !isAdmin())) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$eventId = $data['event_id'] ?? 0;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
    exit();
}

// Check ownership
$check_sql = "SELECT organizer_id FROM events WHERE event_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event || (getUserRole() === 'organizer' && $event['organizer_id'] != $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event not found or access denied.']);
    exit();
}

// Update status to completed
$update_sql = "UPDATE events SET status = 'completed' WHERE event_id = ?";
$upStmt = $conn->prepare($update_sql);
$upStmt->bind_param("i", $eventId);

if ($upStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event marked as completed successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating event status: ' . $upStmt->error]);
}
?>