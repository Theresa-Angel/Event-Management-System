<?php
// api/dashboard_stats.php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$stats = [];

if ($role === 'admin') {
    // Single combined query instead of 5 separate ones
    $row = $conn->query("
        SELECT
            SUM(role = 'student') AS students,
            SUM(role = 'organizer') AS organizers,
            SUM(role = 'organizer' AND status = 'pending') AS pending_organizers,
            (SELECT COUNT(*) FROM events) AS events,
            (SELECT COUNT(*) FROM registrations) AS registrations
        FROM users
    ")->fetch_assoc();

    $stats = [
        'students'           => (int)$row['students'],
        'organizers'         => (int)$row['organizers'],
        'pending_organizers' => (int)$row['pending_organizers'],
        'events'             => (int)$row['events'],
        'registrations'      => (int)$row['registrations']
    ];
} elseif ($role === 'organizer') {
    // Organizer Stats - Optimized with single query
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT e.event_id) as totalEvents,
            COUNT(DISTINCT r.id) as activeRegistrations,
            SUM(CASE WHEN DATE(e.start_date) = CURDATE() THEN 1 ELSE 0 END) as todaysEvents
        FROM events e
        LEFT JOIN registrations r ON e.event_id = r.event_id
        WHERE e.organizer_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stats = [
        'totalEvents' => (int)$row['totalEvents'],
        'activeRegistrations' => (int)$row['activeRegistrations'],
        'todaysEvents' => (int)$row['todaysEvents']
    ];
    $stmt->close();
} elseif ($role === 'student') {
    // Student Stats - use prepared statements
    $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $registered = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations r JOIN events e ON r.event_id = e.event_id WHERE r.user_id = ? AND e.start_date >= NOW() AND e.status = 'active'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $upcoming = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $stats = [
        'registered' => (int)$registered,
        'upcoming'   => (int)$upcoming
    ];
}

echo json_encode($stats);
