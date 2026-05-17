<?php
// Fetch latest data for the organizer to ensure it's up to date
$organizerId = $_SESSION['user_id'];
$orgSql = "SELECT * FROM users WHERE id = ?";
$organizerData = [];

if ($stmt = $conn->prepare($orgSql)) {
    $stmt->bind_param("i", $organizerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $organizerData = $result->fetch_assoc();
}

if (!$organizerData) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded-lg'>Error: Could not load profile data.</div>";
    return;
}
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">My Profile</h2>
            <p class="text-slate-500">View your account details and status</p>
        </div>
        <div class="flex gap-3">
            <a href="?action=dashboard"
                class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50 transition flex items-center">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
            <a href="?action=settings"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition flex items-center">
                <i class="fas fa-user-edit mr-2"></i> Edit Profile
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div
            class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col items-center text-center">
            <div
                class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-3xl font-bold border-4 border-indigo-50 shadow-sm mb-4">
                <?php echo strtoupper(substr($organizerData['username'], 0, 1)); ?>
            </div>
            <h3 class="text-xl font-bold text-slate-900">
                <?php echo htmlspecialchars($organizerData['username']); ?>
            </h3>
            <p class="text-sm text-slate-500 mb-4">
                <?php echo ucfirst($organizerData['role']); ?>
            </p>

            <span
                class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $organizerData['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                <?php echo ucfirst($organizerData['status']); ?> Account
            </span>

            <div class="w-full border-t border-slate-100 mt-6 pt-6 text-left space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Account ID</p>
                    <p class="text-sm font-medium text-slate-700">#
                        <?php echo str_pad($organizerData['id'], 5, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Member Since</p>
                    <p class="text-sm font-medium text-slate-700">
                        <?php echo date('F d, Y', strtotime($organizerData['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3
                class="text-lg font-bold text-slate-800 mb-6 pb-2 border-b border-slate-100 text-uppercase flex items-center">
                <i class="fas fa-info-circle mr-2 text-indigo-600"></i> Contact Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400">Full Name</label>
                    <p class="mt-1 text-slate-900 font-medium">
                        <?php echo htmlspecialchars($organizerData['username']); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Email Address</label>
                    <p class="mt-1 text-slate-900 font-medium">
                        <?php echo htmlspecialchars($organizerData['email']); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Phone Number</label>
                    <p class="mt-1 text-slate-900 font-medium">
                        <?php echo htmlspecialchars($organizerData['phone'] ?? 'Not provided'); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400">Department</label>
                    <p class="mt-1 text-slate-900 font-medium">
                        <?php echo htmlspecialchars($organizerData['department'] ?? 'General'); ?>
                    </p>
                </div>
            </div>

            <div class="mt-12 p-4 bg-indigo-50 rounded-lg border border-indigo-100 flex items-start gap-3">
                <i class="fas fa-shield-alt text-indigo-500 mt-1"></i>
                <div class="text-sm text-indigo-800">
                    <p class="font-bold">Security Tip</p>
                    <p>Keep your contact information up to date so you can receive important notifications about your
                        events.</p>
                </div>
            </div>
        </div>
    </div>
</div>