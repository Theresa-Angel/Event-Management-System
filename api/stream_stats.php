<?php
// api/stream_stats.php
require_once '../config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (!isAdmin()) {
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

function getStats($conn)
{
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

    return [
        'students'          => (int)$row['students'],
        'organizers'        => (int)$row['organizers'],
        'pending_organizers'=> (int)$row['pending_organizers'],
        'events'            => (int)$row['events'],
        'registrations'     => (int)$row['registrations']
    ];
}

// Tell browser to wait 60s before reconnecting after stream ends
echo "retry: 60000\n\n";

$lastStats = null;
$counter = 0;
$maxIterations = 6; // 6 * 10s = 60s total then browser reconnects naturally

while ($counter < $maxIterations) {
    if (!@print (": heartbeat\n\n"))
        break;

    $currentStats = getStats($conn);

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
