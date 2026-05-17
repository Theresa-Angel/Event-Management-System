<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config.php';

$organizerId = $_SESSION['user_id'];
$response = ['events' => []];

// Query events with registration counts
$sql = "
    SELECT 
        e.event_id, 
        e.title, 
        e.description, 
        e.category, 
        e.start_date, 
        e.venue, 
        e.max_attendees, 
        e.banner_image,
        e.status,
        e.prizes,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) as registered_count
    FROM events e 
    WHERE e.organizer_id = ? 
    ORDER BY e.start_date DESC
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Format date and image
        $row['formatted_date'] = date('M d, Y', strtotime($row['start_date']));
        $row['formatted_time'] = date('H:i', strtotime($row['start_date']));
        $row['image_url'] = $row['banner_image'] ?? 'https://via.placeholder.com/800x400';
        $response['events'][] = $row;
    }
}

echo json_encode($response);
?>