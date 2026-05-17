<?php
// api/stream_organizer_stats.php
require_once '../config.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (!isOrganizer()) {
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

$organizer_id = $_SESSION['user_id'];

function getOrganizerStats($conn, $organizer_id)
{
    // Get total events count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = ?");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $totalEvents = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    // Get total registrations for organizer's events
    $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations r JOIN events e ON r.event_id = e.event_id WHERE e.organizer_id = ?");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $activeRegistrations = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    // Count today's events
    $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE organizer_id = ? AND DATE(start_date) = CURDATE()");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $todaysEvents = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    return [
        'totalEvents' => $totalEvents,
        'activeRegistrations' => $activeRegistrations,
        'todaysEvents' => $todaysEvents
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

    $currentStats = getOrganizerStats($conn, $organizer_id);

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
