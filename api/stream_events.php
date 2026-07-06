<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx

require_once '../config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "data: " . json_encode(['error' => 'Unauthorized']) . "\n\n";
    exit();
}

$organizerId = $_SESSION['user_id'];
$lastHash = '';

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

// Continuous loop for SSE
while ($counter < $maxIterations) {
    // Aggressive termination check
    if (!@print (": heartbeat\n\n"))
        break;

    // Fetch current state of events and registration counts
    $sql = "
        SELECT 
            e.event_id, 
            e.status,
            e.prizes,
            (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) as registered_count
        FROM events e 
        WHERE e.organizer_id = ? 
    ";

    $data = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Generate a hash to detect changes
    $currentHash = md5(json_encode($data));

    if ($currentHash !== $lastHash) {
        $full_sql = "
            SELECT 
                e.event_id, 
                e.title, 
                e.category, 
                e.start_date, 
                e.venue, 
                e.max_attendees, 
                e.cover_image,
                e.status,
                e.prizes,
                (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) as registered_count
            FROM events e 
            WHERE e.organizer_id = ? 
            ORDER BY e.start_date DESC
        ";

        $fullData = [];
        if ($stmt = $conn->prepare($full_sql)) {
            $stmt->bind_param("i", $organizerId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['formatted_date'] = date('M d, Y', strtotime($row['start_date']));
                $row['formatted_time'] = date('H:i', strtotime($row['start_date']));
                $row['image_url'] = $row['cover_image'] ?? 'https://via.placeholder.com/800x400';
                $fullData[] = $row;
            }
        }

        if (!@print ("data: " . json_encode(['events' => $fullData]) . "\n\n"))
            break;
        $lastHash = $currentHash;
    }

    // Flush output buffer
    if (ob_get_level() > 0)
        ob_flush();
    flush();

    $counter++;

    for ($i = 0; $i < 30; $i++) {
        if (connection_aborted() || connection_status() !== 0) break 2;
        sleep(1);
    }
}

$conn->close();
?>