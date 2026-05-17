<?php
session_start();

// -------------------------------------------------------
// Database configuration
// Reads from environment variables (Railway/production)
// Falls back to local XAMPP defaults for development
// -------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'event_system');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
} catch (Exception $e) {
    exit("Connection Error: " . $e->getMessage());
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch System Settings (with caching)
$system_settings = [];
if (!isset($_SESSION['system_settings_cache']) || (time() - ($_SESSION['settings_cache_time'] ?? 0)) > 300) {
    $settings_query = "SELECT setting_key, setting_value FROM system_settings";
    if ($result = $conn->query($settings_query)) {
        while ($row = $result->fetch_assoc()) {
            $system_settings[$row['setting_key']] = $row['setting_value'];
        }
        $_SESSION['system_settings_cache'] = $system_settings;
        $_SESSION['settings_cache_time'] = time();
    }
} else {
    $system_settings = $_SESSION['system_settings_cache'];
}

function get_setting($key, $default = '')
{
    global $system_settings;
    return $system_settings[$key] ?? $default;
}

// SMTP (reads from DB settings or env vars)
define('SMTP_HOST',      getenv('SMTP_HOST')     ?: get_setting('smtp_host',     'smtp.gmail.com'));
define('SMTP_PORT',      getenv('SMTP_PORT')     ?: get_setting('smtp_port',     587));
define('SMTP_USER',      getenv('SMTP_USER')     ?: get_setting('smtp_username', 'your-gmail@gmail.com'));
define('SMTP_PASS',      getenv('SMTP_PASS')     ?: get_setting('smtp_password', 'your-app-password'));
define('SMTP_FROM',      getenv('SMTP_USER')     ?: get_setting('smtp_username', 'your-gmail@gmail.com'));
define('SMTP_FROM_NAME', getenv('SITE_NAME')     ?: get_setting('site_name',     'Campus Connect'));

date_default_timezone_set('Asia/Kolkata');

function isLoggedIn()   { return isset($_SESSION['user_id']); }
function getUserRole()  { return $_SESSION['role'] ?? 'guest'; }
function isAdmin()      { return getUserRole() === 'admin'; }
function isOrganizer()  { return getUserRole() === 'organizer'; }
?>
