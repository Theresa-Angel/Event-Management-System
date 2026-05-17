<?php
session_start();

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_system');

// Create connection with optimized settings
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Set connection charset to utf8mb4 for better performance
    $conn->set_charset("utf8mb4");
    
    // Optimize connection settings
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    
} catch (Exception $e) {
    exit("Connection Error: " . $e->getMessage());
}

// Check connection
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

// Helper function to get setting
function get_setting($key, $default = '')
{
    global $system_settings;
    return $system_settings[$key] ?? $default;
}

// SMTP Configuration (Fetch from DB or use defaults)
define('SMTP_HOST', get_setting('smtp_host', 'smtp.gmail.com'));
define('SMTP_PORT', get_setting('smtp_port', 587));
define('SMTP_USER', get_setting('smtp_username', 'your-gmail@gmail.com'));
define('SMTP_PASS', get_setting('smtp_password', 'your-app-password'));
define('SMTP_FROM', get_setting('smtp_username', 'your-gmail@gmail.com')); // Default to username
define('SMTP_FROM_NAME', get_setting('site_name', 'Campus Connect'));

// Set timezone
date_default_timezone_set('UTC');

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Get user role
function getUserRole()
{
    return $_SESSION['role'] ?? 'guest';
}

// Check if user is admin
function isAdmin()
{
    return getUserRole() === 'admin';
}

// Check if user is organizer
function isOrganizer()
{
    return getUserRole() === 'organizer';
}
?>