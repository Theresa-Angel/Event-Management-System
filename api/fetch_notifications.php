<?php
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include DB config and functions
$basePath = __DIR__ . '/../';
if (file_exists($basePath . 'config.php')) {
    require_once $basePath . 'config.php';
} else {
    // Fallback if structure is different
    require_once '../config.php';
}

$user_id = $_SESSION['user_id'];
$response = ['count' => 0, 'notifications' => []];

// Single query: fetch latest 10 unread notifications
$sql = "SELECT id, title, message, type, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['notifications'][] = $row;
    }
    $stmt->close();
    $response['count'] = count($response['notifications']);
}

echo json_encode($response);
?>