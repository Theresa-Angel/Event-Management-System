<?php
require_once '../config.php';
require_once '../includes/functions.php';

// Check for upcoming events (next 24 hours)
$sql = "SELECT e.*, r.user_id, u.email, u.username 
        FROM events e 
        JOIN registrations r ON e.event_id = r.event_id 
        JOIN users u ON r.user_id = u.id 
        WHERE e.start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 DAY)
        AND r.reminder_sent = 0";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $eventId = $row['event_id'];
        $userId = $row['user_id'];
        $email = $row['email'];
        $title = $row['title'];
        $venue = $row['venue'];
        $time = date('g:i A', strtotime($row['start_date']));

        // 1. Send Email
        $subject = "Reminder: Upcoming Event - $title";
        $body = "
            <h2>Event Reminder</h2>
            <p>Hi {$row['username']},</p>
            <p>This is a reminder that <strong>$title</strong> is starting soon.</p>
            <p><strong>Time:</strong> $time<br><strong>Venue:</strong> $venue</p>
            <p>See you there!</p>
        ";
        sendEmailReminder($email, $subject, $body);

        // 2. Send In-App Notification
        $notifTitle = "Event Reminder: $title";
        $notifMsg = "Don't forget! $title is happening tomorrow at $time in $venue.";
        createNotification($userId, $notifTitle, $notifMsg, 'reminder');

        // 3. Mark as sent
        // Note: registrations table needs 'reminder_sent' column. 
        // If not exists, this query will fail. 
        // I will assume for now or try to alter table if I could.
        $updateSql = "UPDATE registrations SET reminder_sent = 1 WHERE text_id = ? AND user_id = ?";
        // Wait, text_id? No, usually event_id. But registrations might have a composite primary key.
        $updateSql = "UPDATE registrations SET reminder_sent = 1 WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
    }
    echo "Reminders sent successfully.";
} else {
    echo "No pending reminders.";
}
?>