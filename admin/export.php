<?php
// admin/export.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Authorization Check
if (!isLoggedIn()) {
    http_response_code(401);
    exit("Unauthorized - Please log in");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Show export type selection page if no type is specified
if (!isset($_GET['type'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Export Data - Campus Connect</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-slate-50">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="mb-6">
                <a href="admin.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2 w-fit">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-slate-800 mb-2">Export Data</h1>
                    <p class="text-slate-500">Select the type of data you want to export</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if ($role === 'admin'): ?>
                    <div class="border border-slate-200 rounded-xl p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Users</h3>
                                <p class="text-sm text-slate-500">Export all user accounts and details</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="window.location.href='export.php?type=users'" 
                                class="flex-1 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                                <i class="fas fa-download mr-2"></i>CSV
                            </button>
                            <button onclick="window.open('export.php?type=users&format=print', '_blank')" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-print mr-2"></i>Print
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="border border-slate-200 rounded-xl p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                                <i class="fas fa-calendar-alt text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Events</h3>
                                <p class="text-sm text-slate-500">Export all events data</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="window.location.href='export.php?type=events'" 
                                class="flex-1 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                                <i class="fas fa-download mr-2"></i>CSV
                            </button>
                            <button onclick="window.open('export.php?type=events&format=print', '_blank')" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-print mr-2"></i>Print
                            </button>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-xl p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="p-3 bg-green-50 rounded-lg text-green-600">
                                <i class="fas fa-user-check text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Registrations</h3>
                                <p class="text-sm text-slate-500">Export all event registrations</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="window.location.href='export.php?type=registrations'" 
                                class="flex-1 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                                <i class="fas fa-download mr-2"></i>CSV
                            </button>
                            <button onclick="window.open('export.php?type=registrations&format=print', '_blank')" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-print mr-2"></i>Print
                            </button>
                        </div>
                    </div>

                    <?php if ($role === 'admin'): ?>
                    <div class="border border-slate-200 rounded-xl p-6 hover:border-indigo-300 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
                                <i class="fas fa-comments text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Feedback</h3>
                                <p class="text-sm text-slate-500">Export all user feedback</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="window.location.href='export.php?type=feedback'" 
                                class="flex-1 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                                <i class="fas fa-download mr-2"></i>CSV
                            </button>
                            <button onclick="window.open('export.php?type=feedback&format=print', '_blank')" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                                <i class="fas fa-print mr-2"></i>Print
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Validate type parameter
$allowed_types = ['users', 'events', 'registrations', 'feedback'];
$type = trim($_GET['type']);

if (!in_array($type, $allowed_types)) {
    http_response_code(400);
    exit("Invalid export type: '" . htmlspecialchars($type) . "'. Allowed types: " . implode(', ', $allowed_types));
}

// Handle Print Preview
if (isset($_GET['format']) && $_GET['format'] === 'print') {
    $title = ucfirst($type) . " Report - " . date('Y-m-d');

    // Fetch data
    if ($type === 'users') {
        if ($role !== 'admin') {
            http_response_code(403);
            exit("Access Denied");
        }
        $headers = ['ID', 'Username', 'Email', 'Role', 'Status', 'Joined Date'];
        $sql = "SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 10000";
    } elseif ($type === 'events') {
        $headers = ['ID', 'Title', 'Organizer', 'Venue', 'Status', 'Start Date'];
        if ($role === 'admin') {
            $sql = "SELECT e.event_id, e.title, u.username, e.venue, e.status, e.start_date FROM events e LEFT JOIN users u ON e.organizer_id = u.id ORDER BY e.start_date DESC LIMIT 10000";
        } else {
            $sql = "SELECT e.event_id, e.title, u.username, e.venue, e.status, e.start_date FROM events e LEFT JOIN users u ON e.organizer_id = u.id WHERE e.organizer_id = ? ORDER BY e.start_date DESC LIMIT 10000";
        }
    } elseif ($type === 'registrations') {
        $headers = ['ID', 'Event', 'User', 'Status', 'Date'];
        $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

        if ($role === 'admin') {
            $sql = "SELECT r.id, e.title, u.username, r.status, r.registration_date FROM registrations r JOIN events e ON r.event_id = e.event_id JOIN users u ON r.user_id = u.id";
            if ($event_id) {
                $sql .= " WHERE r.event_id = ?";
            }
            $sql .= " ORDER BY r.registration_date DESC";
        } else {
            $sql = "SELECT r.id, e.title, u.username, r.status, r.registration_date FROM registrations r JOIN events e ON r.event_id = e.event_id JOIN users u ON r.user_id = u.id WHERE e.organizer_id = ?";
            if ($event_id) {
                $sql .= " AND r.event_id = ?";
            }
            $sql .= " ORDER BY r.registration_date DESC";
        }
    } elseif ($type === 'feedback') {
        if ($role !== 'admin') {
            http_response_code(403);
            exit("Access Denied");
        }
        $headers = ['ID', 'Username', 'Email', 'Message', 'Status', 'Date'];
        $sql = "SELECT id, username, email, message, status, submission_date FROM feedback ORDER BY submission_date DESC LIMIT 10000";
    }

    $data = [];
    if (isset($sql)) {
        if ($type === 'events' && $role !== 'admin') {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } elseif ($type === 'registrations' && $role !== 'admin') {
            $stmt = $conn->prepare($sql);
            if ($event_id) {
                $stmt->bind_param("ii", $user_id, $event_id);
            } else {
                $stmt->bind_param("i", $user_id);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } elseif ($type === 'registrations' && $event_id) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title><?php echo $title; ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @media print {
                .no-print {
                    display: none;
                }

                body {
                    padding: 0;
                    background: white;
                }

                .print-container {
                    border: none;
                    box-shadow: none;
                }
            }
        </style>
    </head>

    <body class="bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg print-container">
            <div class="flex justify-between items-center mb-8 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Campus Connect</h1>
                    <p class="text-slate-500 font-semibold uppercase tracking-wider text-sm"><?php echo $title; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
                    <div class="mt-4 no-print flex gap-3 justify-end">
                        <button onclick="window.print()"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">
                            <i class="fas fa-print mr-2"></i>Print Report
                        </button>
                        <button onclick="window.close();"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200">
                        <?php foreach ($headers as $h): ?>
                            <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider"><?php echo $h; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="<?php echo count($headers); ?>" class="py-10 text-center text-slate-400">No data found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                    <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($val); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="mt-12 text-center text-xs text-slate-400">
                <p>© <?php echo date('Y'); ?> Campus Connect Event Management System. All rights reserved.</p>
                <p>This is a system-generated document.</p>
            </div>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </body>

    </html>
    <?php
    exit();
}

// Handle CSV export request
if (!isset($_GET['format'])) {
    $filename = $type . "_export_" . date('Y-m-d_His') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    if ($type === 'users') {
        if ($role !== 'admin') {
            http_response_code(403);
            exit("Access Denied");
        }
        fputcsv($output, ['ID', 'Username', 'Email', 'Role', 'Status', 'Joined Date']);
        $sql = "SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 10000";
        $result = $conn->query($sql);
    } elseif ($type === 'events') {
        fputcsv($output, ['ID', 'Title', 'Venue', 'Status', 'Start Date']);
        if ($role === 'admin') {
            $sql = "SELECT event_id, title, venue, status, start_date FROM events ORDER BY start_date DESC LIMIT 10000";
            $result = $conn->query($sql);
        } else {
            $sql = "SELECT event_id, title, venue, status, start_date FROM events WHERE organizer_id = ? ORDER BY start_date DESC LIMIT 10000";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } elseif ($type === 'registrations') {
        fputcsv($output, ['ID', 'Event Title', 'Student Name', 'Status', 'Registration Date']);
        $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

        if ($role === 'admin') {
            $sql = "SELECT r.id, e.title, u.username, r.status, r.registration_date FROM registrations r JOIN events e ON r.event_id = e.event_id JOIN users u ON r.user_id = u.id";
            if ($event_id) {
                $sql .= " WHERE r.event_id = ?";
                $sql .= " ORDER BY r.registration_date DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $event_id);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql .= " ORDER BY r.registration_date DESC";
                $result = $conn->query($sql);
            }
        } else {
            $sql = "SELECT r.id, e.title, u.username, r.status, r.registration_date FROM registrations r JOIN events e ON r.event_id = e.event_id JOIN users u ON r.user_id = u.id WHERE e.organizer_id = ?";
            if ($event_id) {
                $sql .= " AND r.event_id = ?";
                $sql .= " ORDER BY r.registration_date DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $event_id);
            } else {
                $sql .= " ORDER BY r.registration_date DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } elseif ($type === 'feedback') {
        if ($role !== 'admin') {
            http_response_code(403);
            exit("Access Denied");
        }
        fputcsv($output, ['ID', 'Username', 'Email', 'Message', 'Status', 'Submission Date']);
        $sql = "SELECT id, username, email, message, status, submission_date FROM feedback ORDER BY submission_date DESC LIMIT 10000";
        $result = $conn->query($sql);
    }

    if (isset($result) && $result) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit();
}
?>