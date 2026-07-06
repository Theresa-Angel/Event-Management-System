<?php
// api/stream_student_stats.php
require_once '../config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "data: " . json_encode(['error' => 'Unauthorized']) . "\n\n";
    exit();
}

// Optimization: Release session lock to prevent blocking other requests
session_write_close();

// Performance and safety settings
set_time_limit(0);
ignore_user_abort(true); // Keep running to detect abort via connection_aborted()

// Clear any existing output buffers
while (ob_get_level())
    ob_end_clean();

$user_id = $_SESSION['user_id'];

function getStudentStats($conn, $user_id)
{
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
    
    return [
        'registered' => $registered,
        'upcoming' => $upcoming
    ];
}

// Tell browser to wait 60s before reconnecting after stream ends
echo "retry: 60000\n\n";

$lastStats = null;
$counter = 0;
$maxIterations = 6; // 6 * 10s = 60s total

while ($counter < $maxIterations) {
    if (!@print (": heartbeat\n\n"))
        break;

    $currentStats = getStudentStats($conn, $user_id);

    if ($lastStats !== $currentStats) {
        if (!@print ("data: " . json_encode($currentStats) . "\n\n"))
            break;
        $lastStats = $currentStats;
    }

    if (ob_get_level() > 0)
        ob_flush();
    flush();

    $counter++;

    for ($i = 0; $i < 10; $i++) {
        if (connection_aborted() || connection_status() !== 0) break 2;
        sleep(1);
    }
}

$conn->close();
