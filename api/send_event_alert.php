<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is authorized
if (!isLoggedIn() || (getUserRole() !== 'organizer' && !isAdmin())) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$eventId = $data['event_id'] ?? 0;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required.']);
    exit();
}

// 1. Verify access to this event
$check_sql = "SELECT title, venue, start_date, organizer_id FROM events WHERE event_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event || (getUserRole() === 'organizer' && $event['organizer_id'] != $_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event not found or access denied.']);
    exit();
}

$title = $event['title'];
$venue = $event['venue'];
$time = date('g:i A', strtotime($event['start_date']));

// 2. Fetch all confirmed participants who haven't received a reminder
// Fetching from registrations table (Standardized)
$reg_sql = "SELECT u.id as user_id, u.email, u.username 
            FROM registrations r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = ? AND r.status = 'confirmed' AND r.reminder_sent = 0";

$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$sentCount = 0;
while ($row = $result->fetch_assoc()) {
    $userId = $row['user_id'];
    $email = $row['email'];
    $username = $row['username'];

    // Send Gmail Alert (Using functions.php helper)
    $subject = "Campus Connect: Event Starting Soon - $title";
    $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
            <h2 style='color: #1a237e;'>Event Alert!</h2>
            <p>Hi <strong>$username</strong>,</p>
            <p>Get ready! The event <strong>$title</strong> is about to start.</p>
            <p><strong>Time:</strong> $time<br><strong>Venue:</strong> $venue</p>
            <p>Please make sure to arrive on time. See you there!</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 12px; color: #666;'>This is an automated notification from Campus Connect.</p>
        </div>
    ";

    // Attempt to send email
    sendEmailReminder($email, $subject, $body);

    // Send In-App Notification
    $notifTitle = "Event Starting Soon: $title";
    $notifMsg = "Your registered event '$title' is starting today at $time. Location: $venue.";
    createNotification($userId, $notifTitle, $notifMsg, 'event', $eventId);

    // Mark as sent in DB
    $updateSql = "UPDATE registrations SET reminder_sent = 1 WHERE event_id = ? AND user_id = ?";
    $upStmt = $conn->prepare($updateSql);
    $upStmt->bind_param("ii", $eventId, $userId);
    $upStmt->execute();

    $sentCount++;
}

$message = ($sentCount > 0)
    ? "Successfully sent alerts to $sentCount registered students."
    : "No new reminders found for this event (either no participants or all have already been notified).";

echo json_encode([
    'success' => true,
    'message' => $message,
    'sent_count' => $sentCount
]);
?>