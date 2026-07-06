<?php
// api/fetch_organizer_stats.php
require_once '../config.php';

header('Content-Type: application/json');

if (!isOrganizer()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$organizer_id = $_SESSION['user_id'];

// Optimized single query to get all stats
$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT e.event_id) as totalEvents,
        COUNT(DISTINCT r.id) as activeRegistrations,
        SUM(CASE WHEN DATE(e.start_date) = CURDATE() THEN 1 ELSE 0 END) as todaysEvents
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    WHERE e.organizer_id = ?
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

echo json_encode([
    'totalEvents' => (int)$stats['totalEvents'],
    'activeRegistrations' => (int)$stats['activeRegistrations'],
    'todaysEvents' => (int)$stats['todaysEvents']
]);
