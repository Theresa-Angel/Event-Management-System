<?php
require_once 'config.php';
require_once 'includes/header.php';

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
              e.prizes
              FROM events e 
              WHERE e.status IN ('active', 'completed')";

    $params = [];
    $types = "";

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

    $query .= " ORDER BY e.start_date ASC LIMIT 500";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

} catch (Exception $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
?>

<!-- Styling -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --text-dark: #1f2937;
        --text-light: #6b7280;
        --bg-light: #f9fafb;
    }

    body {
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-family: 'Poppins', sans-serif;
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

    /* Hero */
    .events-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 80px 0;
        text-align: center;
        position: relative;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    /* Search & Filter Bar */
    .search-container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--text-light);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: border 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #4f46e5;
    }

    .btn-search {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
        height: 48px;
        /* Match input height */
    }

    .btn-search:hover {
        transform: translateY(-2px);
    }

    /* Events Grid */
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .event-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        border: 1px solid #f3f4f6;
    }

    .event-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .card-img-top {
        height: 200px;
        width: 100%;
        object-fit: cover;
        position: relative;
    }

    .img-placeholder {
        height: 200px;
        width: 100%;
        background: linear-gradient(45deg, #e0e7ff, #f3f4f6);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a5b4fc;
        font-size: 3rem;
    }

    .date-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: white;
        padding: 8px 12px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        font-weight: 700;
        color: #4f46e5;
        line-height: 1.2;
    }

    .date-badge span {
        display: block;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        color: var(--text-light);
    }

    .card-body {
        padding: 25px;
    }

    .event-category {
        color: #4f46e5;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-dark);
        line-height: 1.4;
    }

    .card-info {
        display: flex;
        align-items: center;
        gap: 15px;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 20px;
    }

    .card-info i {
        color: #4f46e5;
    }

    .btn-register {
        display: block;
        width: 100%;
        text-align: center;
        background: transparent;
        color: #4f46e5;
        border: 2px solid #4f46e5;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-register:hover {
        background: #4f46e5;
        color: white;
    }

    .btn-view-winners {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: 2px solid #f59e0b;
        color: white;
        animation: pulse 2s infinite;
    }

    .btn-view-winners:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        border-color: #d97706;
        transform: scale(1.05);
        animation: none;
    }

    .empty-state {
        text-align: center;
        grid-column: 1 / -1;
        padding: 60px;
        color: var(--text-light);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 0;
        border-radius: 20px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalFadeIn 0.3s ease-out;
        overflow: hidden;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .modal-close {
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 24px;
        cursor: pointer;
        color: white;
        transition: transform 0.2s;
    }

    .modal-close:hover {
        transform: scale(1.2);
    }

    .modal-body {
        padding: 30px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .winners-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .winner-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-radius: 12px;
        border: 2px solid #f59e0b;
        animation: winnerSlideIn 0.5s ease-out forwards;
        opacity: 0;
        transform: translateX(-20px);
    }

    .winner-item:nth-child(1) {
        animation-delay: 0.1s;
    }

    .winner-item:nth-child(2) {
        animation-delay: 0.2s;
    }

    .winner-item:nth-child(3) {
        animation-delay: 0.3s;
    }

    .winner-item:nth-child(4) {
        animation-delay: 0.4s;
    }

    .winner-item:nth-child(5) {
        animation-delay: 0.5s;
    }

    @keyframes winnerSlideIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .winner-icon {
        font-size: 2rem;
        color: #f59e0b;
        margin-right: 15px;
    }

    .winner-details h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #92400e;
    }

    .winner-name {
        margin: 5px 0 0 0;
        font-size: 1rem;
        font-weight: 600;
        color: #78350f;
    }

    .no-winners {
        text-align: center;
        padding: 40px;
        color: var(--text-light);
        font-style: italic;
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 2rem !important; }
        .events-hero { padding: 50px 0; }
        
        .search-container {
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }
        .form-group { min-width: 100%; }
        .btn-search { width: 100%; }

        .events-grid {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 0 16px;
            margin: 40px auto;
        }

        .modal-content {
            width: 95%;
            margin: 20% auto;
        }
        .modal-body { padding: 20px; }
        .modal-header h2 { font-size: 1.2rem; }
        
        .card-body { padding: 20px; }
        .card-title { font-size: 1.1rem; }
        .card-info { flex-direction: column; align-items: flex-start; gap: 8px; }
    }

    @media (max-width: 480px) {
        .hero-title { font-size: 1.6rem !important; }
        .card-img-top, .img-placeholder { height: 180px; }
        .date-badge { top: 10px; right: 10px; padding: 6px 10px; font-size: 0.9rem; }
    }
</style>

<!-- Hero -->
<section class="events-hero">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <h1 class="hero-title animate-fade-up" style="animation-delay: 0.1s;">Discover Campus Events</h1>
        <p class="animate-fade-up" style="animation-delay: 0.2s; opacity: 0.9; font-size: 1.1rem;">Explore academic
            seminars, cultural fests, and workshops.</p>
    </div>
</section>

<!-- Filter Bar -->
<div class="container" style="padding: 0 20px;">
    <div class="search-container animate-fade-up" style="animation-delay: 0.3s;">
        <form action="" method="GET" style="display: contents;">
            <!-- Search -->
            <div class="form-group">
                <label><i class="fas fa-search"></i> Keyword</label>
                <input type="text" name="search" class="form-control" placeholder="Event name..."
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
                <button type="submit" class="btn-search">Filter Events</button>
            </div>

            <?php if (!empty($search_query) || !empty($category_filter) || !empty($date_filter)): ?>
                <div class="form-group" style="flex: 0 0 auto;">
                    <a href="events.php" style="color: #6b7280; text-decoration: underline;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Events Grid -->
<div class="events-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php
        $delay = 400;
        while ($row = $result->fetch_assoc()):
            $dateObj = new DateTime($row['start_date']);
            $imgSrc = !empty($row['cover_image']) ? htmlspecialchars($row['cover_image']) : '';
            ?>
            <div class="event-card animate-fade-up" style="animation-delay: <?php echo $delay; ?>ms;">
                <div class="card-img-top">
                    <?php if ($imgSrc): ?>
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                            style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <div class="img-placeholder"><i class="fas fa-image"></i></div>
                    <?php endif; ?>

                    <?php if ($row['status'] === 'completed'): ?>
                        <div class="date-badge"
                            style="background: #1e1b4b; color: white; width: auto; font-size: 0.7rem; left: 15px; right: auto; padding: 5px 10px;">
                            COMPLETED
                        </div>
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
                        <span><i class="far fa-clock"></i> <?php echo $dateObj->format('g:i A'); ?></span>
                        <span><i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($row['venue'] ?? 'TBA'); ?></span>
                    </div>


                    <div class="mt-6">
                        <?php if ($row['status'] === 'completed' && strtolower($row['category']) === 'competition'): ?>
                            <?php
                            // Handle prizes data - it might be JSON string or array
                            $prizesData = '[]';
                            if (!empty($row['prizes'])) {
                                if (is_string($row['prizes'])) {
                                    // Already a JSON string, use as is
                                    $prizesData = $row['prizes'];
                                } else {
                                    // Convert to JSON
                                    $prizesData = json_encode($row['prizes']);
                                }
                            }
                            ?>
                            <button class="btn-register btn-view-winners"
                                onclick='openWinnersModal(<?php echo $row['event_id']; ?>, "<?php echo addslashes($row['title']); ?>", <?php echo $prizesData; ?>)'>View
                                Winners</button>
                        <?php elseif ($row['status'] === 'completed'): ?>
                            <button class="btn-register" style="opacity: 0.6; cursor: not-allowed;" disabled>Event
                                Finished</button>
                        <?php elseif (isLoggedIn()): ?>
                            <a href="register_event.php?event_id=<?php echo $row['event_id']; ?>" class="btn-register">Register
                                Now</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-register">Login to Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            $delay += 100;
        endwhile;
        ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times" style="font-size: 4rem; color: #e5e7eb; margin-bottom: 20px;"></i>
            <h3>No events found</h3>
            <p>Try adjusting your search or filters to find what you're looking for.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Winners Modal -->
<div id="winnersModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-close" onclick="closeWinnersModal()">&times;</span>
            <h2 id="modalTitle">Competition Winners</h2>
        </div>
        <div class="modal-body">
            <div id="winnersContent">
                <!-- Winners will be populated here -->
            </div>
        </div>
    </div>
</div>

<script>
    function openWinnersModal(eventId, eventTitle, prizesJson) {
        console.log('Opening winners modal:', { eventId, eventTitle, prizesJson });
        
        const modal = document.getElementById('winnersModal');
        const modalTitle = document.getElementById('modalTitle');
        const winnersContent = document.getElementById('winnersContent');

        if (!modal || !modalTitle || !winnersContent) {
            console.error('Modal elements not found');
            return;
        }

        modalTitle.textContent = `Winners - ${eventTitle}`;

        let prizes = [];
        
        // Handle different data formats
        if (typeof prizesJson === 'string') {
            try {
                prizes = JSON.parse(prizesJson);
            } catch (e) {
                console.error('Failed to parse prizes JSON:', e, prizesJson);
                prizes = [];
            }
        } else if (Array.isArray(prizesJson)) {
            prizes = prizesJson;
        } else if (prizesJson && typeof prizesJson === 'object') {
            prizes = [prizesJson];
        }

        console.log('Parsed prizes:', prizes);

        if (prizes && prizes.length > 0) {
            const winnersList = prizes.map((prize, index) => {
                const winnerName = prize.winner || prize.winner_name || 'TBD';
                const prizeTitle = prize.title || prize.name || prize.prize_name || `Prize ${index + 1}`;
                return `
                <div class="winner-item">
                    <div class="winner-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="winner-details">
                        <h3>${prizeTitle}</h3>
                        <p class="winner-name">${winnerName}</p>
                    </div>
                </div>
            `;
            }).join('');

            winnersContent.innerHTML = `<div class="winners-list">${winnersList}</div>`;
        } else {
            winnersContent.innerHTML = '<div class="no-winners"><i class="fas fa-trophy" style="font-size: 3rem; color: #e5e7eb; margin-bottom: 10px;"></i><p>No winners announced yet.</p></div>';
        }

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeWinnersModal() {
        const modal = document.getElementById('winnersModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById('winnersModal');
        if (event.target === modal) {
            closeWinnersModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeWinnersModal();
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>