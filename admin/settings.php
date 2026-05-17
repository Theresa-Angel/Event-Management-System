<?php
// settings.php - System Settings Page
// Database connection is already handled by admin.php inclusion of config.php, but if accessed directly:
require_once '../config.php';

// Ensure system_settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
)");

// Handle Reset
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    $conn->query("TRUNCATE TABLE system_settings");
    header("Location: ?page=settings&status=reset");
    exit();
}

// Get system settings
$settings = [];
$result = $conn->query("SELECT * FROM system_settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $setting_key = str_replace('setting_', '', $key);
            $value = trim($value);

            // Upsert setting
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $setting_key, $value, $value);
            $stmt->execute();
        }
    }

    // Handle Logo Upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (in_array($_FILES['site_logo']['type'], $allowed_types) && $_FILES['site_logo']['size'] <= $max_size) {
            if (!is_dir('../uploads/logo'))
                mkdir('../uploads/logo', 0777, true);

            $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $ext;
            $upload_path = '../uploads/logo/' . $filename; // Relative to admin/
            $db_path = 'uploads/logo/' . $filename; // Store relative to root

            if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("ss", $db_path, $db_path);
                $stmt->execute();
                $settings['site_logo'] = $db_path;
            }
        }
    }

    $success_message = "Settings updated successfully!";

    // Refresh settings
    $result = $conn->query("SELECT * FROM system_settings");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">System Settings</h2>
            <p class="text-slate-600">Configure application settings and preferences</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.location.href='?page=dashboard'"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </button>
        </div>
    </div>

    <?php if (isset($success_message) || (isset($_GET['status']) && $_GET['status'] === 'reset')): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <div>
                    <p class="text-green-700 font-medium">Success</p>
                    <p class="text-green-600 text-sm">
                        <?php echo isset($success_message) ? $success_message : 'Settings reset to defaults.'; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Settings Sidebar -->
        <div class="lg:w-1/4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sticky top-6">
                <nav class="space-y-1">
                    <button onclick="showSettingsTab('general')"
                        class="settings-tab w-full text-left px-4 py-3 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 active:bg-indigo-50 active:text-indigo-600 active:border-l-4 active:border-indigo-600">
                        <i class="fas fa-cog mr-3"></i>General Settings
                    </button>
                    <button onclick="showSettingsTab('email')"
                        class="settings-tab w-full text-left px-4 py-3 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                        <i class="fas fa-envelope mr-3"></i>Email Settings
                    </button>
                    <button onclick="showSettingsTab('backup')"
                        class="settings-tab w-full text-left px-4 py-3 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600">
                        <i class="fas fa-database mr-3"></i>Backup & Restore
                    </button>
                </nav>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="lg:w-3/4">
            <form method="POST" enctype="multipart/form-data">
                <!-- General Settings -->
                <div id="general-tab" class="settings-tab-content active">
                    <div class="settings-section bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-cog mr-3 text-indigo-600"></i>General Settings
                        </h3>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Site Name *</label>
                                    <input type="text" name="setting_site_name"
                                        value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Campus Connect'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Site Tagline</label>
                                    <input type="text" name="setting_site_tagline"
                                        value="<?php echo htmlspecialchars($settings['site_tagline'] ?? 'Student Event Management System'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Admin Email *</label>
                                    <input type="email" name="setting_admin_email"
                                        value="<?php echo htmlspecialchars($settings['admin_email'] ?? 'admin@campusconnect.com'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Support Email</label>
                                    <input type="email" name="setting_support_email"
                                        value="<?php echo htmlspecialchars($settings['support_email'] ?? 'support@campusconnect.com'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Site Description</label>
                                <textarea name="setting_site_description" rows="3"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?php echo htmlspecialchars($settings['site_description'] ?? 'A comprehensive student event management system.'); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Site Logo</label>
                                <div class="flex items-center space-x-6">
                                    <div
                                        class="w-32 h-32 border-2 border-dashed border-slate-300 rounded-lg flex items-center justify-center bg-slate-50 overflow-hidden">
                                        <?php if (!empty($settings['site_logo'])): ?>
                                            <img src="<?php echo '../' . htmlspecialchars($settings['site_logo']); ?>"
                                                alt="Site Logo" class="max-w-full max-h-full object-contain">
                                        <?php else: ?>
                                            <i class="fas fa-image text-slate-400 text-3xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <input type="file" name="site_logo" accept="image/*"
                                            class="text-sm text-slate-600" onchange="previewLogo(event)">
                                        <p class="text-xs text-slate-500 mt-2">Recommended: 300x300px PNG or SVG. Max
                                            2MB.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Site Footer Text</label>
                                <input type="text" name="setting_footer_text"
                                    value="<?php echo htmlspecialchars($settings['footer_text'] ?? '© ' . date('Y') . ' Campus Connect. All rights reserved.'); ?>"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div id="email-tab" class="settings-tab-content hidden">
                    <div class="settings-section bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-envelope mr-3 text-indigo-600"></i>Email Settings
                        </h3>

                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        These settings are used for sending system emails (registration, updates, etc.).
                                        Please ensure your SMTP credentials are correct.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Host</label>
                                    <input type="text" name="setting_smtp_host"
                                        value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Port</label>
                                    <input type="number" name="setting_smtp_port"
                                        value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Username</label>
                                    <input type="text" name="setting_smtp_username"
                                        value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"
                                        placeholder="email@example.com"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">SMTP Password</label>
                                    <input type="password" name="setting_smtp_password"
                                        value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>"
                                        placeholder="App Password"
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup & Restore -->
                <div id="backup-tab" class="settings-tab-content hidden">
                    <div class="settings-section bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-database mr-3 text-indigo-600"></i>Backup & Restore
                        </h3>

                        <div class="text-center py-8">
                            <div class="mb-4">
                                <i class="fas fa-server text-6xl text-indigo-200"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Manage System Backups</h4>
                            <p class="text-slate-500 max-w-md mx-auto mb-6">
                                Create and download backups of your system data including users, events, and feedback.
                            </p>
                            <a href="?page=backup"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition">
                                <i class="fas fa-external-link-alt mr-2"></i> Open Backup Manager
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Save Settings Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-slate-200">
                    <button type="button" onclick="resetSettings()"
                        class="px-4 py-2 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 font-medium">
                        Reset to Defaults
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-bold shadow-lg shadow-indigo-100">
                        <i class="fas fa-save mr-2"></i>Save All Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Settings tab switching
    function showSettingsTab(tabName) {
        // Hide all tab content
        document.querySelectorAll('.settings-tab-content').forEach(tab => {
            tab.classList.remove('active');
            tab.classList.add('hidden');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.settings-tab').forEach(button => {
            button.classList.remove('active');
            button.classList.remove('bg-indigo-50', 'text-indigo-600', 'border-l-4', 'border-indigo-600');
        });

        // Show selected tab content
        const activeTab = document.getElementById(tabName + '-tab');
        if (activeTab) {
            activeTab.classList.add('active');
            activeTab.classList.remove('hidden');
        }

        // Add active class to clicked button
        const btn = event.currentTarget;
        btn.classList.add('active', 'bg-indigo-50', 'text-indigo-600', 'border-l-4', 'border-indigo-600');
    }

    // Logo preview
    function previewLogo(event) {
        const input = event.target;
        const previewContainer = input.parentElement.previousElementSibling;

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="Logo Preview" class="max-w-full max-h-full object-contain">`;
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Reset settings
    function resetSettings() {
        if (confirm('Are you sure you want to reset all settings to default values? This action cannot be undone.')) {
            window.location.href = '?page=settings&reset=true';
        }
    }

    // Initialize first tab as active
    document.addEventListener('DOMContentLoaded', () => {
        // Trigger click on first tab to set initial state correctly
        document.querySelector('.settings-tab').click();
    });
</script>

<style>
    .settings-tab-content.active {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>