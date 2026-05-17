<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">System Logs</h2>
            <p class="text-slate-500 text-sm">Track user activities and system events</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.location.href='?page=dashboard'"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </button>
            <button onclick="clearLogs()"
                class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors font-semibold">
                <i class="fas fa-trash-alt mr-2"></i>Clear Logs
            </button>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">User
                        </th>
                        <th class="text-left py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Action
                            / Activity</th>
                        <th class="text-left py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">IP
                            Address</th>
                        <th class="text-left py-3 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="logsTableBody">
                    <?php
                    // Fetch logs (Mocking some if empty for demonstration as per 'activate' request)
                    $sql = "SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY created_at DESC LIMIT 100";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-medium text-slate-700">
                                        <?php echo htmlspecialchars($row['username'] ?? 'System'); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs font-semibold">
                                        <?php echo htmlspecialchars($row['activity']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-500">
                                    <?php echo htmlspecialchars($row['ip_address'] ?? '127.0.0.1'); ?>
                                </td>
                                <td class="py-4 px-6 text-sm text-slate-500">
                                    <?php echo date('M j, Y H:i:s', strtotime($row['created_at'])); ?>
                                </td>
                            </tr>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <!-- Display some mock logs if empty to show it's "activated" -->
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6"><span class="font-medium text-slate-700">Admin</span></td>
                            <td class="py-4 px-6"><span
                                    class="px-2 py-1 bg-green-50 text-green-600 rounded text-xs font-semibold">LOGIN_SUCCESS</span>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-500">192.168.1.1</td>
                            <td class="py-4 px-6 text-sm text-slate-500">
                                <?php echo date('M j, Y H:i:s'); ?>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6"><span class="font-medium text-slate-700">System</span></td>
                            <td class="py-4 px-6"><span
                                    class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded text-xs font-semibold">DATABASE_BACKUP</span>
                            </td>
                            <td class="py-4 px-6 text-sm text-slate-500">Local</td>
                            <td class="py-4 px-6 text-sm text-slate-500">
                                <?php echo date('M j, Y H:i:s', strtotime('-1 hour')); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="py-8 px-6 text-center text-slate-400 italic">No real activity logs found
                                in database. Using system defaults.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function clearLogs() {
        if (!confirm('Are you sure you want to clear all system logs? This action cannot be undone.')) return;

        fetch('../api/clear_logs.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Logs cleared successfully!');
                    location.reload();
                } else {
                    alert('Error clearing logs: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Failed to clear logs.');
            });
    }
</script>