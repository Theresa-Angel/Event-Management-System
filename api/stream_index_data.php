<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable buffering for Nginx

require_once '../config.php';

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

// Caching setup
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
$cacheFile = $cacheDir . '/index_data_cache.json';
$cacheTTL = 60; // Cache for 60 seconds

$lastHash = '';
$counter = 0;
$maxIterations = 3; // Recycle process after 3 checks (~3 minutes at 60s interval)

// Continuous loop for SSE
while ($counter < $maxIterations) {
    // Aggressive termination check
    if (!@print (": heartbeat\n\n"))
        break;

    $payload = null;
    $useCache = false;

    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        if ($cachedData) {
            $payload = $cachedData;
            $useCache = true;
        }
    }

    if (!$useCache) {
        // 1. Fetch Stats (optimized queries)
        $stats = [
            'students' => 0,
            'events' => 0,
            'departments' => 5 // Default
        ];

        $student_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
        if ($res = $conn->query($student_query)) {
            $stats['students'] = $res->fetch_assoc()['count'];
        }

        $event_count_query = "SELECT COUNT(*) as count FROM events";
        if ($res = $conn->query($event_count_query)) {
            $stats['events'] = $res->fetch_assoc()['count'];
        }

        // 2. Fetch Featured Events (Top 10 active/upcoming, optimized)
        $events_sql = "SELECT id, title, description, start_date, venue, cover_image FROM events WHERE status = 'active' AND start_date >= CURDATE() ORDER BY start_date ASC LIMIT 10";
        $events_result = $conn->query($events_sql);

        $events = [];
        if ($events_result && $events_result->num_rows > 0) {
            while ($row = $events_result->fetch_assoc()) {
                $date = new DateTime($row['start_date']);
                $row['formatted_date'] = $date->format('M d');
                $row['formatted_time'] = $date->format('g:i A');
                $row['image_url'] = !empty($row['cover_image']) ? $row['cover_image'] : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';
                $row['short_description'] = substr($row['description'], 0, 80) . '...';
                $events[] = $row;
            }
        }

        $payload = [
            'stats' => $stats,
            'events' => $events
        ];

        // Save to cache
        file_put_contents($cacheFile, json_encode($payload));
    }

    // Generate a hash to detect changes
    $currentHash = md5(json_encode($payload));

    if ($currentHash !== $lastHash) {
        if (!@print ("data: " . json_encode($payload) . "\n\n"))
            break;
        $lastHash = $currentHash;
    }

    // Flush output buffer
    if (ob_get_level() > 0)
        ob_flush();
    flush();

    $counter++;

    // Sleep in small increments so we can detect disconnects quickly
    for ($i = 0; $i < 60; $i++) {
        if (connection_aborted() || connection_status() !== 0) break 2;
        sleep(1);
    }
}

$conn->close();
?>