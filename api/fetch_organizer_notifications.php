<?php
header('Content-Type: application/json');

// Include DB config
require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$organizer_id = $_SESSION['user_id'];
$response = ['count' => 0, 'notifications' => []];

try {
    // 1. Fetch total unread count for organizer
    $count_sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0";
    $c_stmt = $conn->prepare($count_sql);
    $c_stmt->bind_param("i", $organizer_id);
    $c_stmt->execute();
    $response['count'] = $c_stmt->get_result()->fetch_assoc()['total'];

    // 2. Fetch latest 10 unread notifications for dropdown
    $sql = "SELECT id, title, message, type, created_at, related_event_id 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 10";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $organizer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Format time ago
            $time_ago = timeAgo($row['created_at']);
            $row['time_ago'] = $time_ago;
            $response['notifications'][] = $row;
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>