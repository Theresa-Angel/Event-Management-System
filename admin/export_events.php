<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';

$sql = "SELECT e.*, u.username as organizer_name, 
        COUNT(r.user_id) as registration_count
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.id
        LEFT JOIN registrations r ON e.event_id = r.event_id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.venue LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($status)) {
    if ($status === 'upcoming') {
        $sql .= " AND e.start_date > NOW() AND e.status = 'active'";
    } elseif ($status === 'ongoing') {
        $sql .= " AND e.start_date <= NOW() AND e.end_date >= NOW() AND e.status = 'active'";
    } elseif ($status === 'completed') {
        $sql .= " AND e.end_date < NOW() AND e.status = 'active'";
    } else {
        $sql .= " AND e.status = ?";
        $params[] = $status;
        $types .= "s";
    }
}

if (!empty($category)) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($date)) {
    $sql .= " AND DATE(e.start_date) = ?";
    $params[] = $date;
    $types .= "s";
}

$sql .= " GROUP BY e.event_id ORDER BY e.start_date DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="events_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['Event ID', 'Title', 'Category', 'Organizer', 'Start Date', 'End Date', 'Venue', 'Status', 'Registrations', 'Max Attendees']);

// CSV Data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['event_id'],
        $row['title'],
        $row['category'],
        $row['organizer_name'],
        $row['start_date'],
        $row['end_date'],
        $row['venue'],
        $row['status'],
        $row['registration_count'],
        $row['max_attendees'] ?? 'Unlimited'
    ]);
}

fclose($output);
$stmt->close();
$conn->close();
exit();
