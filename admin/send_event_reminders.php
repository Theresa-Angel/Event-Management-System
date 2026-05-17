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

// Get event details
$eventSql = "SELECT * FROM events WHERE event_id = ?";
$stmt = $conn->prepare($eventSql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit();
}

// Get registered users
$usersSql = "SELECT u.email, u.username FROM registrations r 
             JOIN users u ON r.user_id = u.id 
             WHERE r.event_id = ? AND r.status = 'registered'";
$stmt = $conn->prepare($usersSql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$emailsSent = 0;
$subject = "Reminder: " . $event['title'];
$message = "Hi,\n\nThis is a reminder about the upcoming event:\n\n";
$message .= "Event: " . $event['title'] . "\n";
$message .= "Date: " . date('M j, Y g:i A', strtotime($event['start_date'])) . "\n";
$message .= "Venue: " . $event['venue'] . "\n\n";
$message .= "We look forward to seeing you there!\n\nBest regards,\nCampus Connect Team";

while ($user = $result->fetch_assoc()) {
    if (mail($user['email'], $subject, $message)) {
        $emailsSent++;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true, 
    'message' => "Reminders sent to $emailsSent participants"
]);
