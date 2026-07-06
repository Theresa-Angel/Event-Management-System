<?php
// admin/backup.php

$backup_dir = 'backups/';

// Handle backup request
if (isset($_GET['action']) && $_GET['action'] === 'run') {
    $target_tables = [
        'users' => ['id', 'username', 'email', 'role', 'status', 'created_at'],
        'events' => ['event_id', 'title', 'organizer_id', 'venue', 'status', 'start_date', 'prizes'],
        'notifications' => ['id', 'user_id', 'title', 'message', 'type', 'created_at'],
        'feedback' => ['id', 'username', 'email', 'subject', 'message', 'status', 'submission_date']
    ];

    $backup_data = [
        'metadata' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'system' => 'Campus Connect'
        ],
        'data' => []
    ];

    foreach ($target_tables as $table => $columns) {
        $col_list = implode(', ', $columns);
        $result = $conn->query("SELECT $col_list FROM $table");

        $table_data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $table_data[] = $row;
            }
        }
        $backup_data['data'][$table] = $table_data;
    }

    $json_output = json_encode($backup_data, JSON_PRETTY_PRINT);
    $filename = "data_backup_" . date('Y-m-d_His') . ".json";

    if (!is_dir($backup_dir))
        mkdir($backup_dir, 0755, true);
    file_put_contents($backup_dir . $filename, $json_output);

    // Trigger download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($json_output));
    echo $json_output;
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    if (file_exists($backup_dir . $file)) {
        unlink($backup_dir . $file);
        header("Location: ?page=backup&status=deleted");
        exit();
    }
}

// Get existing backups
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && strpos($file, '.json') !== false) {
            $backups[] = [
                'name' => $file,
                'date' => filemtime($backup_dir . $file),
                'size' => filesize($backup_dir . $file)
            ];
        }
    }
}
usort($backups, function ($a, $b) {
    return $b['date'] - $a['date'];
});
$last_backup = !empty($backups) ? date('M j, Y H:i', $backups[0]['date']) : 'Never';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800">System Data Backup</h2>
        <p class="text-slate-500 text-sm">Download a secure archive of core system data records.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden sticky top-6">
                <div class="p-8 text-center">
                    <div
                        class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-database text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Back Up Core Data</h3>
                    <p class="text-slate-500 text-sm mb-6">Generates a structured JSON file containing records for
                        Users, Events, Notifications, and Feedback.</p>

                    <a href="?page=backup&action=run"
                        class="inline-flex items-center w-full justify-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition-all shadow-lg shadow-indigo-100">
                        <i class="fas fa-file-download mr-3"></i>Generate JSON Backup
                    </a>
                </div>

                <div class="bg-slate-50 border-t border-slate-200 p-6">
                    <div class="p-4 bg-white rounded-lg border border-slate-200 text-sm">
                        <span class="block text-slate-400 font-semibold text-[10px] uppercase mb-1">Last Data
                            Backup</span>
                        <span class="font-medium text-slate-700 text-xs"><?php echo $last_backup; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Backup History (JSON)</h3>
                    <span
                        class="px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded uppercase"><?php echo count($backups); ?>
                        Backups</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase">
                            <tr>
                                <th class="px-6 py-4 text-left">Record Name</th>
                                <th class="px-6 py-4 text-left">Date</th>
                                <th class="px-6 py-4 text-left">Size</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                        No data backups found on server.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $b): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-700"><?php echo $b['name']; ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            <?php echo date('M j, Y g:i A', $b['date']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500"><?php echo round($b['size'] / 1024, 2); ?>
                                            KB</td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="backups/<?php echo $b['name']; ?>" download
                                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                                                    title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="?page=backup&delete=<?php echo urlencode($b['name']); ?>"
                                                    onclick="return confirm('Delete this backup?')"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded transition" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start space-x-3">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                <div>
                    <h5 class="font-bold text-blue-800 text-sm">Targeted Data Format</h5>
                    <p class="text-sm text-blue-700">This backup is optimized to include only core system data (Users,
                        Events, Notifications, and Feedbacks) in a structured JSON format, making it lightweight and
                        easy to process.</p>
                </div>
            </div>
        </div>
    </div>
</div>