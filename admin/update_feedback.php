<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $response = trim($_POST['response']);

    if (empty($response)) {
        echo json_encode(['success' => false, 'message' => 'Response cannot be empty']);
        exit;
    }

    $sql = "UPDATE feedback SET response = ?, status = 'responded', response_date = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $response, $feedback_id);

    if ($stmt->execute()) {
        // Fetch user_id and subject for notification
        $info_stmt = $conn->prepare("SELECT user_id, subject FROM feedback WHERE id = ?");
        $info_stmt->bind_param("i", $feedback_id);
        $info_stmt->execute();
        $feedback_info = $info_stmt->get_result()->fetch_assoc();

        if ($feedback_info && $feedback_info['user_id']) {
            $notif_msg = "Your feedback regarding '" . $feedback_info['subject'] . "' has been responded to by an admin.";
            createNotification($feedback_info['user_id'], "Feedback Responded", $notif_msg, "info");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>