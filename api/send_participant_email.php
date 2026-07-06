<?php
// api/send_participant_email.php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// 1. Authorization Check
if (!isLoggedIn() || getUserRole() !== 'organizer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// 2. Input Validation
$event_id = $_POST['event_id'] ?? 0;
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($event_id) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Event ID, subject, and message are required.']);
    exit();
}

// 3. Verify Event Ownership
$check_sql = "SELECT title, start_date, venue, organizer_id FROM events WHERE event_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $event_id);
$check_stmt->execute();
$event = $check_stmt->get_result()->fetch_assoc();

if (!$event || $event['organizer_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Event not found or access denied.']);
    exit();
}

// 4. Fetch Confirmed Participants
$sql = "
    SELECT u.username, u.email 
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ? AND r.status = 'confirmed'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$participants = [];
while ($row = $result->fetch_assoc()) {
    $participants[] = $row;
}

if (empty($participants)) {
    echo json_encode(['success' => false, 'message' => 'No confirmed participants found for this event.']);
    exit();
}

// 5. Prepare Email Content
$organizer_name = $_SESSION['username'] ?? 'Event Organizer';
$event_date = date('F j, Y g:i A', strtotime($event['start_date']));

$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .message { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .event-details { background: #e8eaf6; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0;'>" . htmlspecialchars($event['title']) . "</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>Update from Organizer</p>
        </div>
        <div class='content'>
            <p>Dear Participant,</p>
            
            <div class='message'>
                " . nl2br(htmlspecialchars($message)) . "
            </div>
            
            <div class='event-details'>
                <h3 style='margin-top: 0; color: #5e35b1;'>Event Details</h3>
                <p><strong>Event:</strong> " . htmlspecialchars($event['title']) . "</p>
                <p><strong>Date:</strong> " . $event_date . "</p>
                <p><strong>Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>
            </div>
            
            <p>Best regards,<br>
            <strong>" . htmlspecialchars($organizer_name) . "</strong><br>
            Event Organizer</p>
            
            <div class='footer'>
                <p>This email was sent via Campus Connect Event Management System</p>
                <p>You received this email because you are registered for this event.</p>
            </div>
        </div>
    </div>
</body>
</html>
";

// 6. Send Emails
$sent_count = 0;
$failed_count = 0;

foreach ($participants as $participant) {
    $to = $participant['email'];
    $email_subject = htmlspecialchars($subject);

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Campus Connect <no-reply@campusconnect.edu>" . "\r\n";
    $headers .= "Reply-To: " . $_SESSION['email'] . "\r\n";

    if (mail($to, $email_subject, $email_body, $headers)) {
        $sent_count++;
    } else {
        $failed_count++;
    }
}

// 7. Return Response
if ($sent_count > 0) {
    echo json_encode([
        'success' => true,
        'message' => "Email sent successfully to $sent_count participant(s).",
        'sent' => $sent_count,
        'failed' => $failed_count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send emails. Please check your server email configuration.'
    ]);
}
?>