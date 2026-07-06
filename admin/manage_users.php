<head>
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>

<div class="container mx-auto px-4 py-6">
    <?php if ($page === 'manage_users'): ?>
        <?php
        require_once '../config.php';
        $stats = [
            'total_users' => 0,
            'active_users' => 0,
            'pending_users' => 0,
            'inactive_users' => 0,
            'students' => 0,
            'organizers' => 0,
            'admins' => 0
        ];
        try {
            // Total users
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_users'] = $result->fetch_assoc()['count'] ?? 0;
            // Active users
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['active_users'] = $result->fetch_assoc()['count'] ?? 0;
            // Pending users
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['pending_users'] = $result->fetch_assoc()['count'] ?? 0;
            // Inactive users
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['inactive_users'] = $result->fetch_assoc()['count'] ?? 0;
            // Students
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['students'] = $result->fetch_assoc()['count'] ?? 0;
            // Organizers
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'organizer'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['organizers'] = $result->fetch_assoc()['count'] ?? 0;
            // Admins
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['admins'] = $result->fetch_assoc()['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Database error in manage_users: " . $e->getMessage());
        }
        ?>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
            <div class="flex space-x-3">
                <button onclick="window.location.href='?page=dashboard'"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </button>
                <button onclick="exportUsers('csv')"
                    class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
                <button onclick="exportUsers('print')"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition">
                    <i class="fas fa-print mr-2"></i>Print Report
                </button>
                <button onclick="openAddUserModal()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Add New User
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Users</span>
                    <i class="fas fa-users text-indigo-500"></i>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_users']); ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active</span>
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['active_users']); ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending</span>
                    <i class="fas fa-clock text-amber-500"></i>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['pending_users']); ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Students</span>
                    <i class="fas fa-user-graduate text-blue-500"></i>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['students']); ?></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Organizers</span>
                    <i class="fas fa-user-tie text-purple-500"></i>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['organizers']); ?></div>
            </div>
        </div>



        <!-- Search and Filter Section -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                        <input type="text" id="searchUsers" placeholder="Search users by name or email..."
                            class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition"
                            onkeyup="filterUsers()">
                    </div>
                </div>
                <div class="flex space-x-3">
                    <select id="roleFilter"
                        class="px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition"
                        onchange="filterUsers()">
                        <option value="">All Roles</option>
                        <option value="student">Student</option>
                        <option value="organizer">Organizer</option>
                        <option value="admin">Admin</option>
                    </select>
                    <select id="statusFilter"
                        class="px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition"
                        onchange="filterUsers()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                    <button onclick="resetFilters()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors font-semibold">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">User</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Email</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Role</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Joined</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php
                        // Fetch users from database
                        require_once '../config.php';
                        $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 500";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $roleBadge = '';
                                $statusBadge = '';

                                // Set role badge
                                switch ($row['role']) {
                                    case 'admin':
                                        $roleBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Admin</span>';
                                        break;
                                    case 'organizer':
                                        $roleBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Organizer</span>';
                                        break;
                                    case 'student':
                                    default:
                                        $roleBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Student</span>';
                                }

                                // Set status badge
                                switch ($row['status']) {
                                    case 'active':
                                        $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>';
                                        break;
                                    case 'inactive':
                                        $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Inactive</span>';
                                        break;
                                    case 'pending':
                                        $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>';
                                        break;
                                    default:
                                        $statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Unknown</span>';
                                }

                                echo '
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mr-3">
                                        ' . ($row['avatar'] ?
                                    '<img src="' . htmlspecialchars($row['avatar']) . '" alt="' . htmlspecialchars($row['username']) . '" class="w-full h-full rounded-full object-cover">' :
                                    '<i class="fas fa-user text-slate-400"></i>') . '
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">' . htmlspecialchars($row['username']) . '</div>
                                        <div class="text-sm text-slate-500">ID: ' . htmlspecialchars($row['id']) . '</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-gray-700">' . htmlspecialchars($row['email']) . '</div>
                            </td>
                            <td class="py-4 px-4">
                                ' . $roleBadge . '
                            </td>
                            <td class="py-4 px-4">
                                ' . $statusBadge . '
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm text-slate-600">' . date('M j, Y', strtotime($row['created_at'])) . '</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewUser(' . $row['id'] . ')" 
                                            class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-sm transition-colors">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </button>
                                    <button onclick="editUser(' . $row['id'] . ')" 
                                            class="px-3 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 rounded text-sm transition-colors">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    ' . ($row['status'] == 'active' ?
                                    '<button onclick="toggleUserStatus(' . $row['id'] . ', \'inactive\', \'' . addslashes(htmlspecialchars($row['username'])) . '\')" 
                                                class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded text-sm transition-colors">
                                            <i class="fas fa-ban mr-1"></i>Deactivate
                                        </button>' :
                                    '<button onclick="toggleUserStatus(' . $row['id'] . ', \'active\', \'' . addslashes(htmlspecialchars($row['username'])) . '\')" 
                                                class="px-3 py-1 bg-green-50 hover:bg-green-100 text-green-700 rounded text-sm transition-colors">
                                            <i class="fas fa-check mr-1"></i>Activate
                                        </button>') . '
                                </div>
                            </td>
                        </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center py-4">No users found.</td></tr>';
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Simulated for this demo) -->
            <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span
                        id="totalUsers"><?php echo number_format($stats['total_users']); ?></span> users
                </div>
                <div class="flex space-x-1">
                    <button
                        class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-400 cursor-not-allowed">Previous</button>
                    <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">1</button>
                    <button
                        class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">2</button>
                    <button
                        class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">3</button>
                    <button
                        class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">Next</button>
                </div>
            </div>
        </div>

        <!-- Statistics Summary -->

    </div>

    <!-- User Detail Modal -->
    <div id="userDetailModal"
        class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl scale-95 transition-transform duration-300 overflow-hidden"
            id="detailModalCard">
            <div class="p-8">
                <div id="userDetailContent">
                    <!-- User details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status Confirmation Modal -->
    <div id="statusConfirmModal"
        class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl scale-95 transition-all duration-300 overflow-hidden"
            id="statusModalCard">
            <div class="p-8 text-center">
                <div id="statusIconContainer" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                    <!-- Dynamic Icon -->
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2" id="statusModalTitle">Change Status</h3>
                <p class="text-slate-500 text-sm leading-relaxed" id="statusModalMsg">Are you sure you want to perform this
                    action?</p>

                <div class="mt-8 flex gap-3">
                    <button onclick="closeStatusModal()"
                        class="flex-1 px-6 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Cancel</button>
                    <button id="confirmStatusBtn"
                        class="flex-1 px-6 py-2.5 rounded-xl text-white font-bold transition shadow-lg">Confirm
                        Action</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl scale-95 transition-transform duration-300 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Add New User</h3>
                <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="addUserForm" class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Username *</label>
                        <input type="text" name="username" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Email *</label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Password *</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Role</label>
                        <select name="role" onchange="toggleModalFields('add')"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div id="addStudentFields" class="space-y-4 pt-4 border-t border-slate-50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Academic Details</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="roll_number" placeholder="Roll Number"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                        <select name="department"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Dept</option>
                            <option value="Tamil">Tamil</option>
                            <option value="English">English</option>
                            <option value="Commerce">Commerce</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                        <select name="year"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeAddModal()"
                        class="px-6 py-2 text-slate-500 font-semibold">Cancel</button>
                    <button type="submit"
                        class="px-8 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Create
                        User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl scale-95 transition-transform duration-300 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Edit User</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="editUserForm" class="p-8 space-y-6">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Username *</label>
                        <input type="text" name="username" id="edit_username" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Email *</label>
                        <input type="email" name="email" id="edit_email" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">New Password (optional)</label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Role</label>
                        <select name="role" id="edit_role" onchange="toggleModalFields('edit')"
                            class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="space-y-2 col-span-2">
                        <label class="text-sm font-semibold text-slate-700">Status</label>
                        <div class="flex gap-4 p-2 bg-slate-50 rounded-xl">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="status"
                                    value="active" id="edit_status_active"> <span class="text-xs">Active</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="status"
                                    value="pending" id="edit_status_pending"> <span class="text-xs">Pending</span></label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="status"
                                    value="inactive" id="edit_status_inactive"> <span
                                    class="text-xs">Inactive</span></label>
                        </div>
                    </div>
                </div>

                <div id="editStudentFields" class="space-y-4 pt-4 border-t border-slate-50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Academic Details</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="roll_number" id="edit_roll_number" placeholder="Roll Number"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                        <select name="department" id="edit_department"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Dept</option>
                            <option value="Tamil">Tamil</option>
                            <option value="English">English</option>
                            <option value="Commerce">Commerce</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                        <select name="year" id="edit_year"
                            class="px-4 py-2 border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Year</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                        </select>
                    </div>
                </div>

                <div class="pt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()"
                        class="px-6 py-2 text-slate-500 font-semibold">Cancel</button>
                    <button type="submit"
                        class="px-8 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Update
                        User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Export functionality with filters
        function exportUsers(format) {
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            let url = 'export_users.php';
            const params = [];
            
            if (roleFilter) params.push('role=' + roleFilter);
            if (statusFilter) params.push('status=' + statusFilter);
            if (format === 'print') params.push('format=print');
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            if (format === 'print') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        }

        // Filter functionality
        function filterUsers() {
            const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#usersTableBody tr');

            rows.forEach(row => {
                const username = row.querySelector('.font-medium').textContent.toLowerCase();
                const email = row.querySelector('.text-gray-700').textContent.toLowerCase();
                const role = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
                const status = row.querySelector('td:nth-child(4)').textContent.trim().toLowerCase();

                const matchesSearch = username.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = roleFilter === '' || role === roleFilter;
                const matchesStatus = statusFilter === '' || status === statusFilter;

                if (matchesSearch && matchesRole && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function resetFilters() {
            document.getElementById('searchUsers').value = '';
            document.getElementById('roleFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterUsers();
        }

        // User actions
        function viewUser(userId) {
            const modal = document.getElementById('userDetailModal');
            const content = document.getElementById('userDetailContent');

            // Fetch user details via new API
            fetch(`../api/user_management.php?action=fetch&id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const u = data.user;
                        content.innerHTML = `
                            <div class="flex items-center space-x-4 mb-6">
                                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xl">
                                    ${u.username.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-slate-800">${u.username}</h4>
                                    <p class="text-slate-500 text-sm">${u.email}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="p-3 bg-slate-50 rounded-lg"><p class="text-[10px] text-slate-400 uppercase font-bold">Role</p><p class="text-sm font-semibold">${u.role}</p></div>
                                <div class="p-3 bg-slate-50 rounded-lg"><p class="text-[10px] text-slate-400 uppercase font-bold">Status</p><p class="text-sm font-semibold">${u.status}</p></div>
                                ${u.role === 'student' ? `
                                    <div class="p-3 bg-slate-50 rounded-lg"><p class="text-[10px] text-slate-400 uppercase font-bold">Roll No</p><p class="text-sm font-semibold">${u.roll_number || 'N/A'}</p></div>
                                    <div class="p-3 bg-slate-50 rounded-lg"><p class="text-[10px] text-slate-400 uppercase font-bold">Department</p><p class="text-sm font-semibold">${u.department || 'N/A'}</p></div>
                                    <div class="p-3 bg-slate-50 rounded-lg"><p class="text-[10px] text-slate-400 uppercase font-bold">Year</p><p class="text-sm font-semibold">${u.year || 'N/A'}</p></div>
                                ` : ''}
                            </div>
                            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                                <button onclick="closeModal()" class="px-6 py-2 text-slate-500 font-semibold border border-slate-100 rounded-lg">Close</button>
                                <button onclick="editUser(${userId})" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold">Edit Profile</button>
                            </div>
                        `;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }
                });
        }

        // --- MODAL ENGINE ---

        function openAddUserModal() {
            const modal = document.getElementById('addUserModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.children[0].classList.remove('scale-95'), 10);
            toggleModalFields('add');
        }

        function closeAddModal() {
            const modal = document.getElementById('addUserModal');
            modal.classList.add('hidden');
            document.getElementById('addUserForm').reset();
        }

        function openEditUserModal(id) {
            fetch(`../api/user_management.php?action=fetch&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const u = data.user;
                        document.getElementById('edit_user_id').value = u.id;
                        document.getElementById('edit_username').value = u.username;
                        document.getElementById('edit_email').value = u.email;
                        document.getElementById('edit_role').value = u.role;
                        document.getElementById(`edit_status_${u.status}`).checked = true;
                        document.getElementById('edit_roll_number').value = u.roll_number || '';
                        document.getElementById('edit_department').value = u.department || '';
                        document.getElementById('edit_year').value = u.year || '';

                        const modal = document.getElementById('editUserModal');
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        toggleModalFields('edit');
                        closeModal(); // Close details modal if open
                    }
                });
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            document.getElementById('editUserForm').reset();
        }

        function editUser(userId) {
            openEditUserModal(userId);
        }

        function toggleModalFields(type) {
            const role = document.querySelector(`#${type}UserForm [name="role"]`).value;
            const studentFields = document.getElementById(`${type}StudentFields`);
            if (role === 'student') {
                studentFields.classList.remove('hidden');
            } else {
                studentFields.classList.add('hidden');
            }
        }

        // Form Submissions
        document.getElementById('addUserForm').onsubmit = function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');

            submitUserAction(formData);
        };

        document.getElementById('editUserForm').onsubmit = function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'edit');

            submitUserAction(formData);
        };

        function submitUserAction(formData) {
            fetch('../api/user_management.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        let pendingStatusChange = null;

        function toggleUserStatus(userId, status, username) {
            pendingStatusChange = { id: userId, status: status };
            const modal = document.getElementById('statusConfirmModal');
            const iconContainer = document.getElementById('statusIconContainer');
            const title = document.getElementById('statusModalTitle');
            const msg = document.getElementById('statusModalMsg');
            const btn = document.getElementById('confirmStatusBtn');

            msg.innerHTML = `Are you sure you want to <b>${status}</b> the account for <b>${username}</b>?`;

            if (status === 'active') {
                iconContainer.className = "w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6 text-green-600";
                iconContainer.innerHTML = '<i class="fas fa-check-circle text-3xl"></i>';
                title.textContent = "Activate User";
                btn.className = "flex-1 px-6 py-2.5 bg-green-600 rounded-xl text-white font-bold hover:bg-green-700 transition shadow-lg shadow-green-100";
                btn.textContent = "Yes, Activate";
            } else {
                iconContainer.className = "w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-6 text-red-600";
                iconContainer.innerHTML = '<i class="fas fa-ban text-3xl"></i>';
                title.textContent = "Deactivate User";
                btn.className = "flex-1 px-6 py-2.5 bg-red-600 rounded-xl text-white font-bold hover:bg-red-700 transition shadow-lg shadow-red-100";
                btn.textContent = "Yes, Deactivate";
            }

            btn.onclick = processStatusChange;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => document.getElementById('statusModalCard').classList.remove('scale-95'), 10);
        }

        function processStatusChange() {
            if (!pendingStatusChange) return;

            const fd = new FormData();
            fd.append('action', 'status');
            fd.append('user_id', pendingStatusChange.id);
            fd.append('status', pendingStatusChange.status);

            fetch('../api/user_management.php', {
                method: 'POST',
                body: fd
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function closeStatusModal() {
            const modal = document.getElementById('statusConfirmModal');
            document.getElementById('statusModalCard').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
            pendingStatusChange = null;
        }

        function closeModal() {
            document.getElementById('userDetailModal').classList.add('hidden');
        }

        // Close modals on backdrop click
        window.onclick = function (event) {
            const detailModal = document.getElementById('userDetailModal');
            const addModal = document.getElementById('addUserModal');
            const editModal = document.getElementById('editUserModal');
            const statusModal = document.getElementById('statusConfirmModal');

            if (event.target == detailModal) detailModal.classList.add('hidden');
            if (event.target == addModal) closeAddModal();
            if (event.target == editModal) closeEditModal();
            if (event.target == statusModal) closeStatusModal();
        };

        // Handle deep-linking from dashboard (edit/view actions)
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            const id = urlParams.get('id');

            if (id && action) {
                if (action === 'edit') {
                    editUser(id);
                } else if (action === 'view') {
                    viewUser(id);
                }
            }
        });
    </script>
<?php endif; ?>

</html>