<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    $sql = "DELETE FROM activity_logs";
    if ($conn->query($sql)) {
        // Log the clearing action itself
        $userId = $_SESSION['user_id'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, created_at) VALUES (?, 'LOGS_CLEARED', ?, NOW())");
        $stmt->bind_param("is", $userId, $ip);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>