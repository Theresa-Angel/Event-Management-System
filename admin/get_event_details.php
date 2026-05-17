<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$eventId = $_GET['id'] ?? 0;

if (!$eventId) {
    echo json_encode(['error' => 'Event ID required']);
    exit();
}

$sql = "SELECT e.*, u.username as organizer_name, 
        COUNT(DISTINCT r.user_id) as registration_count
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.id
        LEFT JOIN registrations r ON e.event_id = r.event_id
        WHERE e.event_id = ?
        GROUP BY e.event_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
    
    // Format dates
    $event['start_date'] = date('M j, Y g:i A', strtotime($event['start_date']));
    $event['end_date'] = date('M j, Y g:i A', strtotime($event['end_date']));
    
    // Calculate attendance rate (if applicable)
    $event['attendance_rate'] = 0;
    if ($event['registration_count'] > 0) {
        $attendedSql = "SELECT COUNT(*) as attended FROM registrations WHERE event_id = ? AND status = 'attended'";
        $attendedStmt = $conn->prepare($attendedSql);
        $attendedStmt->bind_param("i", $eventId);
        $attendedStmt->execute();
        $attendedResult = $attendedStmt->get_result();
        $attended = $attendedResult->fetch_assoc()['attended'];
        $event['attendance_rate'] = round(($attended / $event['registration_count']) * 100, 1);
    }
    
    echo json_encode($event);
} else {
    echo json_encode(['error' => 'Event not found']);
}

$stmt->close();
$conn->close();
