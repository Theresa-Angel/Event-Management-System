<?php
/**
 * Campus Connect - Unified Core Functions File
 * Unified from function.php and functions.php
 */

// Start session if not already started
function startSessionIfNeeded()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Generate CSRF Token
function generateCSRFToken()
{
    startSessionIfNeeded();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF Token
function validateCSRFToken($token)
{
    startSessionIfNeeded();
    return (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token));
}

// Get current user data
function getCurrentUser()
{
    startSessionIfNeeded();
    if (isset($_SESSION['user_id'])) {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            unset($user['password']); // Remove sensitive data
            return $user;
        }
    }
    return null;
}

// Redirect helper
function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    exit();
}

// Flash Message helpers
function setFlashMessage($message, $type = 'info')
{
    startSessionIfNeeded();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage()
{
    startSessionIfNeeded();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Date Formatting helpers
function formatDate($date, $format = 'F j, Y g:i A')
{
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $time_difference = time() - $time;
    if ($time_difference < 1)
        return 'just now';
    $condition = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
}

// Input Sanitization
function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validation helpers
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isStrongPassword($password)
{
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// NOTIFICATION FUNCTIONS
function createNotification($userId, $title, $message, $type = 'info', $eventId = null)
{
    global $conn;
    if (!$conn)
        return false;

    // Support 'all' for future global features
    if ($userId === 'all') {
        return false; // Placeholder for global notifications logic
    }

    $sql = "INSERT INTO notifications (user_id, title, message, type, related_event_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $userId, $title, $message, $type, $eventId);
    return $stmt->execute();
}

// Notify organizer about event activity
function notifyOrganizer($eventId, $title, $message, $type = 'event')
{
    global $conn;
    if (!$conn || !$eventId)
        return false;

    // Get organizer ID from event
    $sql = "SELECT organizer_id FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $organizer_id = $row['organizer_id'];
        return createNotification($organizer_id, $title, $message, $type, $eventId);
    }

    return false;
}

function getUnreadNotifications($userId, $limit = 5)
{
    global $conn;
    if (!$conn)
        return null;
    $sql = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function getUserNotifications($userId, $limit = 10, $unreadOnly = false)
{
    global $conn;
    $where = "user_id = ?";
    if ($unreadOnly)
        $where .= " AND is_read = 0";
    $sql = "SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function markNotificationRead($notificationId, $userId)
{
    global $conn;
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notificationId, $userId);
    return $stmt->execute();
}

// EMAIL FUNCTIONS
function sendEmailReminder($to, $subject, $body)
{
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Campus Connect <no-reply@campusconnect.edu>' . "\r\n";
    return mail($to, $subject, $body, $headers);
}

// DASHBOARD & STATS
function getDashboardStats($userId, $role)
{
    global $conn;
    $stats = [];
    $sql = "SELECT COUNT(*) as total FROM events";
    $stats['total_events'] = $conn->query($sql)->fetch_assoc()['total'];

    $sql = "SELECT COUNT(*) as total FROM registrations WHERE user_id = ? AND status = 'confirmed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stats['registered_events'] = $stmt->get_result()->fetch_assoc()['total'];

    return $stats;
}

// MISC
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


