<?php
// api/fetch_feedback.php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM feedback WHERE 1=1";
$params = [];
$types = "";

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

$sql .= " ORDER BY submission_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$feedback = [];
while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

// Get counts
$counts = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM feedback) as total,
        (SELECT COUNT(*) FROM feedback WHERE status = 'pending') as pending,
        (SELECT COUNT(*) FROM feedback WHERE status = 'responded') as responded
")->fetch_assoc();

echo json_encode([
    'feedback' => $feedback,
    'counts' => $counts
]);
?>