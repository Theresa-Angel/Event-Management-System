<?php
// api/stream_feedback.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

require_once '../config.php';
require_once '../includes/functions.php';

// Check for admin session
if (!isLoggedIn() || getUserRole() !== 'admin') {
    exit();
}

// Optimization: Release session lock to prevent blocking other requests
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Performance and safety settings
set_time_limit(0);
ignore_user_abort(true); // Keep running to detect abort via connection_aborted()

// Clear any existing output buffers
while (ob_get_level())
    ob_end_clean();

$lastHash = '';
$counter = 0;
$maxIterations = 4; // 4 * 30s = 2 minutes total

// Tell browser to wait 60s before reconnecting after stream ends
echo "retry: 60000\n\n";

while ($counter < $maxIterations) {
    if (!@print (": heartbeat\n\n"))
        break;

    // Single combined query instead of 3 subqueries
    $counts = $conn->query("
        SELECT
            COUNT(*) AS total,
            SUM(status = 'pending') AS pending,
            SUM(status = 'responded') AS responded
        FROM feedback
    ")->fetch_assoc();

    $feedback_query = "SELECT * FROM feedback ORDER BY submission_date DESC LIMIT 50";
    $feedback_res = $conn->query($feedback_query);
    $feedback = [];
    while ($row = $feedback_res->fetch_assoc()) {
        $feedback[] = $row;
    }

    $payload = ['counts' => $counts, 'feedback' => $feedback];
    $currentHash = md5(json_encode($payload));

    if ($currentHash !== $lastHash) {
        if (!@print ("data: " . json_encode($payload) . "\n\n"))
            break;
        $lastHash = $currentHash;
    }

    if (ob_get_level() > 0) ob_flush();
    flush();

    $counter++;

    for ($i = 0; $i < 30; $i++) {
        if (connection_aborted() || connection_status() !== 0) break 2;
        sleep(1);
    }
}

$conn->close();
?>