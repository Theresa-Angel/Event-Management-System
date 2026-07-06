<?php
require_once '../../config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$events = [];
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

// Get distinct categories for filter
$categories = [];
$cat_sql = "SELECT DISTINCT category FROM events WHERE category != ''";
if ($cat_res = $conn->query($cat_sql)) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Merge with default categories to ensure all options are visible
$default_categories = ['Workshop', 'Seminar', 'Competition', 'Cultural', 'Sports', 'Arts', 'Symposium', 'Hackathon', 'Webinar', 'Other'];
$categories = array_unique(array_merge($categories, $default_categories));
// Custom sort: Alphabetical, but 'Other' at the end
sort($categories);
if (($key = array_search('Other', $categories)) !== false) {
    unset($categories[$key]);
    $categories[] = 'Other';
}

try {
    // Build Query
    $query = "SELECT e.*, 
              (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as registered_count,
              (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id AND user_id = ?) as is_user_registered
              FROM events e 
              WHERE e.status = 'active'";

    $params = [$user_id];
    $types = "i";

    // Filters
    if (!empty($search_query)) {
        $query .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?)";
        $term = "%$search_query%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= "sss";
    }

    if (!empty($category_filter)) {
        $query .= " AND e.category = ?";
        $params[] = $category_filter;
        $types .= "s";
    }

    if (!empty($date_filter)) {
        $query .= " AND DATE(e.start_date) = ?";
        $params[] = $date_filter;
        $types .= "s";
    }

    // Pagination
    $limit = 6;
    $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
    if ($page < 1)
        $page = 1;
    $offset = ($page - 1) * $limit;

    // Count total results for pagination
    $count_query = "SELECT COUNT(*) as total FROM events e WHERE e.status = 'active'";
    $count_params = [];
    $count_types = "";

    if (!empty($search_query)) {
        $count_query .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?)";
        $term = "%$search_query%";
        $count_params[] = $term;
        $count_params[] = $term;
        $count_params[] = $term;
        $count_types .= "sss";
    }
    if (!empty($category_filter)) {
        $count_query .= " AND e.category = ?";
        $count_params[] = $category_filter;
        $count_types .= "s";
    }
    if (!empty($date_filter)) {
        $count_query .= " AND DATE(e.start_date) = ?";
        $count_params[] = $date_filter;
        $count_types .= "s";
    }

    $count_stmt = $conn->prepare($count_query);
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $total_results = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_results / $limit);

    $query .= " ORDER BY e.start_date ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

} catch (Exception $e) {
    $error = "Error fetching events: " . $e->getMessage();
}

$pageTitle = "Event Catalog";
$activePage = "events";

include 'includes/student_header.php';
include 'includes/student_sidebar.php';
include 'includes/student_topbar.php';
?>

<!-- Styling -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --text-dark: #1f2937;
        --text-light: #6b7280;
        --bg-light: #f9fafb;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    /* Search & Filter Bar */
    .search-container {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        margin-bottom: 30px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #4f46e5;
        ring: 2px solid rgba(79, 70, 229, 0.1);
    }

    .btn-search {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        height: 42px;
    }

    .btn-search:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    /* Events Grid */
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .event-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #f3f4f6;
        display: flex;
        flex-direction: column;
    }

    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .card-img-container {
        height: 180px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .event-card:hover .card-img-top {
        transform: scale(1.05);
    }

    .img-placeholder {
        height: 180px;
        width: 100%;
        background: linear-gradient(45deg, #f3f4f6, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 2.5rem;
    }

    .date-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: white;
        padding: 6px 10px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        font-weight: 700;
        color: #4f46e5;
        line-height: 1.2;
        z-index: 10;
    }

    .date-badge span {
        display: block;
        font-size: 0.7rem;
        font-weight: 500;
        text-transform: uppercase;
        color: var(--text-light);
    }

    .card-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .event-category {
        color: #4f46e5;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .card-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--text-dark);
        line-height: 1.4;
    }

    .card-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: var(--text-light);
        font-size: 0.85rem;
        margin-bottom: 20px;
    }

    .card-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-info-item i {
        color: #4f46e5;
        width: 16px;
    }

    .card-footer {
        margin-top: auto;
    }

    .btn-action {
        display: block;
        width: 100%;
        text-align: center;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 0.9rem;
    }

    .btn-register {
        background: transparent;
        color: #4f46e5;
        border: 2px solid #4f46e5;
    }

    .btn-register:hover {
        background: #4f46e5;
        color: white;
    }

    .btn-registered {
        background: #ecfdf5;
        color: #059669;
        border: 2px solid #10b981;
        cursor: default;
    }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
        padding: 60px;
        color: var(--text-light);
        background: white;
        border-radius: 16px;
        border: 2px dashed #e5e7eb;
    }

    @media (max-width: 640px) {
        .search-container {
            flex-direction: column;
            gap: 15px;
        }

        .btn-search {
            width: 100%;
        }
    }
</style>

<div class="container-fluid">
    <div class="mb-6">
        <a href="student.php"
            class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-2 w-fit">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Header Section -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Explore Events</h2>
            <p class="text-slate-500 text-sm">Find and register for upcoming campus activities.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="search-container animate-fade-up" style="animation-delay: 0.1s;">
        <form action="" method="GET" style="display: contents;">
            <!-- Search -->
            <div class="form-group">
                <label><i class="fas fa-search"></i> Search Keyword</label>
                <input type="text" name="search" class="form-control" placeholder="Event title, venue..."
                    value="<?php echo htmlspecialchars($search_query); ?>">
            </div>

            <!-- Category -->
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Category</label>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($cat)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Date</label>
                <input type="date" name="date" class="form-control"
                    value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>

            <!-- Actions -->
            <div class="form-group" style="flex: 0 0 auto;">
                <button type="submit" class="btn-search">Apply Filters</button>
            </div>

            <?php if (!empty($search_query) || !empty($category_filter) || !empty($date_filter)): ?>
                <div class="form-group" style="flex: 0 0 auto; align-self: center; padding-bottom: 5px;">
                    <a href="event_catalog.php" class="text-xs text-slate-400 hover:text-indigo-600 underline">Clear All</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Events Grid -->
    <div class="events-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php
            $delay = 200;
            while ($row = $result->fetch_assoc()):
                $dateObj = new DateTime($row['start_date']);
                $imgSrc = !empty($row['cover_image']) ? htmlspecialchars($row['cover_image']) : '';
                $isRegistered = $row['is_user_registered'] > 0;
                ?>
                <div class="event-card animate-fade-up" style="animation-delay: <?php echo $delay; ?>ms;">
                    <div class="card-img-container">
                        <?php if ($imgSrc):
                            $displayImg = $imgSrc;
                            if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '/') !== 0) {
                                $displayImg = "../../" . $imgSrc;
                            }
                            ?>
                            <img src="<?php echo $displayImg; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                                class="card-img-top">
                        <?php else: ?>
                            <div class="img-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>

                        <div class="date-badge">
                            <span><?php echo $dateObj->format('M'); ?></span>
                            <?php echo $dateObj->format('d'); ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <span class="event-category"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></span>
                        <h3 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h3>

                        <div class="card-info">
                            <div class="card-info-item">
                                <i class="far fa-clock"></i>
                                <span><?php echo $dateObj->format('g:i A'); ?></span>
                            </div>
                            <div class="card-info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($row['venue'] ?? 'TBA'); ?></span>
                            </div>
                            <div class="card-info-item">
                                <i class="fas fa-users"></i>
                                <span><?php echo $row['registered_count']; ?> registered</span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <?php if ($isRegistered): ?>
                                <span class="btn-action btn-registered">
                                    <i class="fas fa-check-circle mr-1"></i> Registered
                                </span>
                            <?php elseif ($dateObj < new DateTime()): ?>
                                <span class="btn-action btn-registered"
                                    style="background:#f1f5f9; color:#64748b; border-color:#cbd5e1;">
                                    Finished
                                </span>
                            <?php else: ?>
                                <a href="../../register_event.php?event_id=<?php echo $row['event_id']; ?>"
                                    class="btn-action btn-register">
                                    Register Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
                $delay += 50;
            endwhile;
            ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"
                    style="font-size: 3rem; color: #e5e7eb; margin-bottom: 15px; display: block;"></i>
                <h3 class="text-lg font-bold text-slate-700">No events found</h3>
                <p class="text-sm text-slate-500">Try adjusting your filters or search keywords.</p>
                <a href="event_catalog.php" class="mt-4 inline-block text-indigo-600 font-semibold text-sm">View all
                    events</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination UI -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center items-center gap-2 mb-12 animate-fade-up" style="animation-delay: 0.4s;">
            <?php if ($page > 1): ?>
                <a href="?page_num=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($category_filter); ?>&date=<?php echo urlencode($date_filter); ?>"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page_num=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($category_filter); ?>&date=<?php echo urlencode($date_filter); ?>"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border <?php echo $i === $page ? 'bg-indigo-600 border-indigo-600 text-white font-bold' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-600 hover:text-indigo-600'; ?> transition text-sm">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page_num=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo urlencode($category_filter); ?>&date=<?php echo urlencode($date_filter); ?>"
                    class="w-10 h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-indigo-600 hover:text-indigo-600 transition">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include 'includes/student_footer.php';
?>