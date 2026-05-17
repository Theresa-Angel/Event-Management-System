<?php
// api/checkin.php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// 1. Authorization Check
if (!isLoggedIn() || (getUserRole() !== 'organizer' && !isAdmin())) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// 2. Input Validation
$ticket_id = $_GET['ticket_id'] ?? ($_POST['ticket_id'] ?? '');
if (str_starts_with($ticket_id, 'TICKET-')) {
    $ticket_id = str_replace('TICKET-', '', $ticket_id);
}

if (empty($ticket_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing Ticket ID.']);
    exit();
}

// 3. Fetch Registration and Event Details
$sql = "
    SELECT r.*, e.title as event_title, u.username, e.organizer_id
    FROM registrations r
    JOIN events e ON r.event_id = e.event_id
    JOIN users u ON r.user_id = u.id
    WHERE r.ticket_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$reg = $result->fetch_assoc();

if (!$reg) {
    echo json_encode(['success' => false, 'message' => 'Invalid Ticket ID.']);
    exit();
}

// 4. Permission Check (Organizer must own the event, unless admin)
if (getUserRole() === 'organizer' && $reg['organizer_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: You do not manage this event.']);
    exit();
}

// 5. Check-in Logic
if ($reg['attended'] == 1) {
    echo json_encode([
        'success' => false,
        'message' => "Student already checked in.",
        'student' => $reg['username'],
        'event' => $reg['event_title'],
        'time' => date('H:i A', strtotime($reg['checkin_time']))
    ]);
    exit();
}

if ($reg['status'] !== 'confirmed') {
    echo json_encode([
        'success' => false,
        'message' => "Registration status is '" . $reg['status'] . "'. Only confirmed tickets are valid for check-in."
    ]);
    exit();
}

// 6. Update Attendance
$update_sql = "UPDATE registrations SET attended = 1, checkin_time = NOW() WHERE ticket_id = ?";
$up_stmt = $conn->prepare($update_sql);
$up_stmt->bind_param("s", $ticket_id);

if ($up_stmt->execute()) {
    // Notify organizer about check-in
    $checkin_msg = $reg['username'] . " has checked in to: " . $reg['event_title'];
    notifyOrganizer($reg['event_id'], "Student Check-in", $checkin_msg, "event");

    echo json_encode([
        'success' => true,
        'message' => "Check-in Successful!",
        'student' => $reg['username'],
        'event' => $reg['event_title']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error during check-in.']);
}
?>