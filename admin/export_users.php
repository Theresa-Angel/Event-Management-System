<?php
// admin/export_users.php
require_once '../config.php';
require_once '../includes/functions.php';

// Authorization Check
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit("Access Denied");
}

// Get filter parameters
$role_filter = isset($_GET['role']) ? trim($_GET['role']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build SQL query with filters
$sql = "SELECT id, username, email, role, status, created_at FROM users WHERE 1=1";
$params = [];
$types = '';

if (!empty($role_filter) && in_array($role_filter, ['student', 'organizer', 'admin'])) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if (!empty($status_filter) && in_array($status_filter, ['active', 'inactive', 'pending'])) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$sql .= " ORDER BY created_at DESC";

// Handle Print Preview
if (isset($_GET['format']) && $_GET['format'] === 'print') {
    $title = "Users Report - " . date('Y-m-d');
    
    // Add filter info to title
    $filter_info = [];
    if (!empty($role_filter)) {
        $filter_info[] = "Role: " . ucfirst($role_filter);
    }
    if (!empty($status_filter)) {
        $filter_info[] = "Status: " . ucfirst($status_filter);
    }
    if (!empty($filter_info)) {
        $title .= " (" . implode(", ", $filter_info) . ")";
    }

    // Fetch users data
    $data = [];
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
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
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($title); ?></title>
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
        <!-- Buttons outside print area -->
        <div class="max-w-6xl mx-auto mb-4 no-print flex gap-3 justify-end">
            <button onclick="window.print()"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
            <button onclick="window.close();"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                Close
            </button>
        </div>

        <div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg print-container">
            <div class="flex justify-between items-center mb-8 border-b pb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Campus Connect</h1>
                    <p class="text-slate-500 font-semibold uppercase tracking-wider text-sm"><?php echo htmlspecialchars($title); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-400">Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200">
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">ID</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Username</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Email</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Role</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Status</th>
                        <th class="py-3 px-4 text-xs font-bold text-slate-600 uppercase tracking-wider">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">No data found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['role']); ?></td>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['status']); ?></td>
                                <td class="py-4 px-4 text-sm text-slate-700"><?php echo htmlspecialchars($row['created_at']); ?></td>
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

// Handle CSV export (default)
$filename = "users_export_" . date('Y-m-d_His');
if (!empty($role_filter)) {
    $filename .= "_" . $role_filter;
}
if (!empty($status_filter)) {
    $filename .= "_" . $status_filter;
}
$filename .= ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, ['ID', 'Username', 'Email', 'Role', 'Status', 'Joined Date']);

// Fetch and write data
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
$conn->close();
exit();
?>
