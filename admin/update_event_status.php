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
$status = $data['status'] ?? '';

if (!$eventId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$sql = "UPDATE events SET status = ? WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $eventId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update event status']);
}

$stmt->close();
$conn->close();
