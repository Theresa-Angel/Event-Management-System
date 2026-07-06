<?php
// api/submit_feedback.php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get input and sanitize
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Simple validation
if (empty($username) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Please fill in all required fields.']));
}

$sql = "INSERT INTO feedback (username, email, role, subject, message, status, submission_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $username, $email, $role, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thank you! Your feedback has been submitted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit feedback. Please try again later.']);
}

$stmt->close();
?>