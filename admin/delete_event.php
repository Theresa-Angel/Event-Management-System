<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$eventId = $data['eventId'] ?? 0;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Event ID required']);
    exit();
}

// Delete registrations first (foreign key constraint)
$deleteSql = "DELETE FROM registrations WHERE event_id = ?";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->close();

// Delete event
$sql = "DELETE FROM events WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
}

$stmt->close();
$conn->close();
