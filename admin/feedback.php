<?php
// admin/feedback.php - Integrated Module

// Check if included or direct access
if (!isset($page)) {
    require_once '../config.php';
    require_once '../includes/functions.php';
    if (!isLoggedIn() || getUserRole() !== 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Feedback Management</h2>
        <div class="flex space-x-3">
            <button onclick="window.location.href='?page=dashboard'"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </button>
            <button onclick="window.location.href='admin/export.php?type=feedback'"
                class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition">
                <i class="fas fa-download mr-2"></i>Export CSV
            </button>
            <button onclick="window.open('admin/export.php?type=feedback&format=print', '_blank')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total Feedback</p>
                    <h3 class="text-2xl font-bold text-blue-600" id="totalFeedbackCount">0</h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                    <i class="fas fa-comments text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Pending Response</p>
                    <h3 class="text-2xl font-bold text-yellow-600" id="pendingFeedbackCount">0</h3>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Responded</p>
                    <h3 class="text-2xl font-bold text-green-600" id="respondedFeedbackCount">0</h3>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" id="searchInput" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search feedback by name, email, or subject..."
                        class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex space-x-3">
                <select id="statusFilter"
                    class="border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="responded" <?php echo $status_filter == 'responded' ? 'selected' : ''; ?>>Responded
                    </option>
                </select>
                <button onclick="resetFeedbackFilters()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Feedback Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">User</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Contact Info</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Subject</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Message Preview</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Date</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody id="feedbackTableBody">
                    <tr>
                        <td colspan="7" class="py-8 px-4 text-center text-slate-500">Loading feedback...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination (Static for now) -->
        <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
            <div class="text-sm text-slate-600" id="showingCount">
                Showing entries
            </div>
            <div class="flex space-x-1">
                <button class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">1</button>
                <button class="px-3 py-1 border border-slate-300 rounded text-sm text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Feedback Modal -->
<div id="feedbackDetailModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
        class="bg-white rounded-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Feedback Details</h3>
                <button onclick="closeFeedbackModals()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="feedbackDetailContent">
                <!-- Feedback details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Respond to Feedback Modal -->
<div id="respondModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div
        class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Respond to Feedback</h3>
                <button onclick="closeFeedbackModals()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="responseForm">
                <input type="hidden" id="feedback_id" name="feedback_id">

                <div id="feedbackSummary" class="mb-6 bg-slate-50 p-4 rounded-lg">
                    <!-- Feedback summary will be loaded here -->
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Your Response *</label>
                    <textarea id="response" name="response" rows="6"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Type your response to the student here..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeFeedbackModals()"
                        class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-semibold">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-bold shadow-lg shadow-indigo-100">
                        <i class="fas fa-paper-plane mr-2"></i>Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let feedbackEventSource = null;
    let lastFeedbackData = null;

    function initFeedbackStream() {
        if (feedbackEventSource) feedbackEventSource.close();

        feedbackEventSource = new EventSource('../api/stream_feedback.php');

        feedbackEventSource.onmessage = function (event) {
            const data = JSON.parse(event.data);
            updateFeedbackUI(data);
        };

        feedbackEventSource.onerror = function () {
            console.error('SSE Connection failed. Reverting to manual fetch.');
            if (feedbackEventSource) feedbackEventSource.close();
            feedbackEventSource = null;
            fetchFeedbackManual();
        };
    }

    function applyFilters() {
        // Re-apply filters to the last received data
        if (lastFeedbackData) {
            updateFeedbackUI(lastFeedbackData);
        }
    }

    function updateFeedbackUI(data) {
        // Store the data for filter re-application
        lastFeedbackData = data;
        
        const tbody = document.getElementById('feedbackTableBody');
        const totalEl = document.getElementById('totalFeedbackCount');
        const pendingEl = document.getElementById('pendingFeedbackCount');
        const respondedEl = document.getElementById('respondedFeedbackCount');
        const showingEl = document.getElementById('showingCount');

        const statusFilter = document.getElementById('statusFilter').value;
        const searchInput = document.getElementById('searchInput').value.toLowerCase();

        // Update counts
        totalEl.textContent = data.counts.total;
        pendingEl.textContent = data.counts.pending;
        respondedEl.textContent = data.counts.responded;

        // Local filtering
        let filtered = data.feedback;
        if (statusFilter !== 'all') {
            filtered = filtered.filter(row => row.status === statusFilter);
        }
        if (searchInput) {
            filtered = filtered.filter(row =>
                (row.username && row.username.toLowerCase().includes(searchInput)) ||
                (row.email && row.email.toLowerCase().includes(searchInput)) ||
                (row.subject && row.subject.toLowerCase().includes(searchInput)) ||
                (row.message && row.message.toLowerCase().includes(searchInput))
            );
        }

        showingEl.textContent = `Showing ${filtered.length} feedback entries`;

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="py-8 px-4 text-center text-slate-500">No feedback found</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(row => {
            const statusClass = row.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
            const date = new Date(row.submission_date);
            const formattedDate = !isNaN(date) ? date.toLocaleDateString() : 'N/A';

            return `
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <td class="py-4 px-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3 font-bold text-indigo-600 border border-indigo-200">
                                ${row.username ? row.username.charAt(0).toUpperCase() : '?'}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">${row.username || 'Anonymous'}</div>
                                <div class="text-xs text-slate-500 capitalize">${row.role || 'User'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="text-sm text-gray-700">${row.email}</div>
                        ${row.phone ? `<div class="text-[10px] text-slate-500">${row.phone}</div>` : ''}
                    </td>
                    <td class="py-4 px-4">
                        <div class="font-medium text-sm text-gray-800">${row.subject}</div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="text-xs text-slate-600 max-w-xs truncate" title="${row.message.replace(/"/g, '&quot;')}">
                            ${row.message}
                        </div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="text-xs text-slate-600">${formattedDate}</div>
                    </td>
                    <td class="py-4 px-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase ${statusClass}">${row.status}</span>
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex space-x-1">
                            <button onclick="viewFeedbackDetail(${row.id})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="View"><i class="fas fa-eye"></i></button>
                            ${row.status === 'pending' ? `<button onclick="openRespondModal(${row.id})" class="p-1.5 text-green-600 hover:bg-green-50 rounded transition" title="Respond"><i class="fas fa-reply"></i></button>` : ''}
                            <button onclick="deleteFeedback(${row.id})" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function fetchFeedbackManual() {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        fetch(`../api/fetch_feedback.php?status=${status}&search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => updateFeedbackUI(data));
    }

    function viewFeedbackDetail(id) {
        fetch(`get_feedback.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                const modal = document.getElementById('feedbackDetailModal');
                const content = document.getElementById('feedbackDetailContent');

                content.innerHTML = `
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 text-indigo-600 text-2xl font-bold border-2 border-indigo-200">
                                ${data.username ? data.username.charAt(0).toUpperCase() : '?'}
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-800">${data.username}</h4>
                                <div class="grid grid-cols-2 gap-4 mt-3 text-sm">
                                    <div><span class="text-slate-400 font-semibold uppercase text-[10px] block">Role</span><span class="font-medium capitalize">${data.role}</span></div>
                                    <div><span class="text-slate-400 font-semibold uppercase text-[10px] block">Email</span><span class="font-medium">${data.email}</span></div>
                                    ${data.department ? `<div><span class="text-slate-400 font-semibold uppercase text-[10px] block">Dept</span><span class="font-medium">${data.department}</span></div>` : ''}
                                    ${data.phone ? `<div><span class="text-slate-400 font-semibold uppercase text-[10px] block">Phone</span><span class="font-medium">${data.phone}</span></div>` : ''}
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-5">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-lg font-bold text-slate-800">${data.subject}</h5>
                                <span class="text-xs text-slate-400">${new Date(data.submission_date).toLocaleString()}</span>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <p class="text-slate-600 text-sm whitespace-pre-line leading-relaxed">${data.message}</p>
                            </div>
                            
                            ${data.response ? `
                            <div class="mt-6">
                                <h5 class="text-sm font-bold text-green-700 uppercase tracking-wider mb-2">System Response</h5>
                                <div class="bg-green-50 p-4 rounded-xl border border-green-100">
                                    <p class="text-green-800 text-sm whitespace-pre-line leading-relaxed">${data.response}</p>
                                    <div class="text-[10px] text-green-600 mt-2 text-right">Sent on ${new Date(data.response_date).toLocaleString()}</div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="border-t pt-6 flex justify-end gap-3">
                            <button onclick="closeFeedbackModals()" class="px-6 py-2 text-slate-500 font-semibold">Close</button>
                            ${data.status === 'pending' ? `<button onclick="openRespondModal(${data.id})" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold">Respond Now</button>` : ''}
                        </div>
                    </div>
                `;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modal.children[0].classList.remove('scale-95'), 10);
            });
    }

    function openRespondModal(id) {
        closeFeedbackModals();
        setTimeout(() => {
            fetch(`get_feedback.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('feedback_id').value = data.id;
                    document.getElementById('feedbackSummary').innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">${data.username.charAt(0)}</div>
                            <div>
                                <span class="font-bold block">${data.username}</span>
                                <span class="text-xs text-slate-500">${data.subject}</span>
                            </div>
                        </div>
                    `;
                    const modal = document.getElementById('respondModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    setTimeout(() => modal.children[0].classList.remove('scale-95'), 10);
                });
        }, 300);
    }

    function deleteFeedback(id) {
        if (!confirm('Are you sure you want to delete this feedback?')) return;

        const fd = new FormData();
        fd.append('id', id);

        fetch('delete_feedback.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (!feedbackEventSource) fetchFeedbackManual();
                } else alert('Error: ' + data.message);
            });
    }

    function closeFeedbackModals() {
        document.querySelectorAll('[id$="Modal"]').forEach(m => {
            if (m.id === 'feedbackDetailModal' || m.id === 'respondModal') {
                m.children[0].classList.add('scale-95');
                setTimeout(() => {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                }, 200);
            }
        });
    }

    document.getElementById('responseForm').onsubmit = function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        fetch('update_feedback.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Response sent successfully');
                    closeFeedbackModals();
                    if (!feedbackEventSource) fetchFeedbackManual();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    };

    function resetFeedbackFilters() {
        document.getElementById('statusFilter').value = 'all';
        document.getElementById('searchInput').value = '';
        if (!feedbackEventSource) fetchFeedbackManual();
        else applyFilters();
    }

    // Filters
    document.getElementById('statusFilter').onchange = () => {
        if (!feedbackEventSource) fetchFeedbackManual();
        else applyFilters();
    };
    document.getElementById('searchInput').oninput = debounce(() => {
        if (!feedbackEventSource) fetchFeedbackManual();
        else applyFilters();
    }, 500);

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    // Start 
    initFeedbackStream();

    // Fallback
    setInterval(() => {
        if (!feedbackEventSource) fetchFeedbackManual();
    }, 30000);

    // Handle URL parameters for direct actions (View/Respond)
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const feedbackId = urlParams.get('id');
        const action = urlParams.get('action');

        if (feedbackId) {
            if (action === 'respond') {
                openRespondModal(feedbackId);
            } else {
                viewFeedbackDetail(feedbackId);
            }
        }
    });
</script>