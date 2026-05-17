<?php
require_once 'config.php';
require_once 'includes/header.php';
?>

<!-- Page Specific Styles -->
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

    /* Common Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-15px) rotate(2deg);
        }

        100% {
            transform: translateY(0px) rotate(0deg);
        }
    }

    @keyframes pulse-glow {
        0% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(79, 70, 229, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
        }
    }

    .animate-hidden {
        opacity: 0;
    }

    .animate-fade-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .delay-100 {
        animation-delay: 0.1s;
    }

    .delay-200 {
        animation-delay: 0.2s;
    }

    .delay-300 {
        animation-delay: 0.3s;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        color: white;
        padding: 120px 0 160px;
        text-align: center;
        overflow: hidden;
        clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
    }

    /* College image background */
    .hero-bg {
        position: absolute;
        inset: 0;
        background: url('assets/clg.jpeg') center center / cover no-repeat;
        transform: scale(1.08);
        animation: heroZoom 18s ease-in-out infinite alternate;
        z-index: 0;
    }

    /* Animated blur overlay */
    .hero-blur {
        position: absolute;
        inset: 0;
        backdrop-filter: blur(3px);
        animation: heroBreathe 6s ease-in-out infinite alternate;
        z-index: 1;
    }

    /* Dark gradient over image */
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg,
            rgba(79, 70, 229, 0.75) 0%,
            rgba(124, 58, 237, 0.70) 100%);
        z-index: 2;
    }

    @keyframes heroZoom {
        from { transform: scale(1.08); }
        to   { transform: scale(1.18); }
    }

    @keyframes heroBreathe {
        from { backdrop-filter: blur(1px); }
        to   { backdrop-filter: blur(3px); }
    }

    .hero-content {
        position: relative;
        z-index: 3;
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
        letter-spacing: -1px;
        text-shadow: 0 2px 20px rgba(0,0,0,0.4);
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.95;
        margin-bottom: 40px;
        line-height: 1.6;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        text-shadow: 0 1px 10px rgba(0,0,0,0.3);
    }

    .hero-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .btn-hero-primary {
        background: white;
        color: #4f46e5;
        padding: 15px 40px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-hero-secondary {
        border: 2px solid rgba(255, 255, 255, 0.8);
        color: white;
        padding: 13px 40px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
    }

    /* Floating Shapes */
    .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.07);
        border-radius: 20%;
        animation: float 8s infinite ease-in-out;
        z-index: 3;
    }

    .shape-1 {
        width: 120px;
        height: 120px;
        top: 10%;
        left: 5%;
        animation-delay: 0s;
        border-radius: 50%;
    }

    .shape-2 {
        width: 80px;
        height: 80px;
        bottom: 20%;
        right: 10%;
        animation-delay: 2s;
        border-radius: 30%;
    }

    .shape-3 {
        width: 60px;
        height: 60px;
        top: 20%;
        right: 20%;
        animation-delay: 4s;
    }

    /* Stats Bar */
    .stats-container {
        max-width: 1000px;
        margin: -60px auto 80px;
        background: white;
        border-radius: 20px;
        padding: 40px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        /* Soft shadow */
        position: relative;
        z-index: 10;
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 5px;
    }

    .stat-item p {
        color: var(--text-light);
        font-weight: 600;
        margin: 0;
    }

    /* Section Headers */
    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .badge {
        background: #e0e7ff;
        color: #4f46e5;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 15px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .section-subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* How It Works */
    .steps-section {
        padding: 0 20px 80px;
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .step-card {
        text-align: center;
        padding: 30px;
        position: relative;
    }

    .step-number {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        color: white;
        border-radius: 50%;
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
    }

    .step-icon {
        font-size: 3rem;
        color: #4f46e5;
        margin-bottom: 20px;
        height: 80px;
        /* Fixed height for alignment */
    }

    .step-card h3 {
        font-size: 1.25rem;
        margin-bottom: 10px;
        font-weight: 600;
    }

    /* Upcoming Events Preview */
    .events-preview-section {
        padding: 80px 20px;
        background: #f3f4f6;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .event-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .event-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    /* Event Ticker Styles */
    .ticker-wrap {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        overflow: hidden;
        height: 44px;
        /* Increased height */
        background: linear-gradient(90deg, #1a237e 0%, #283593 100%);
        color: white;
        z-index: 1010;
        display: flex;
        align-items: center;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .ticker-label {
        background: #ff9800;
        padding: 0 25px;
        height: 100%;
        display: flex;
        align-items: center;
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        position: relative;
        z-index: 10;
        box-shadow: 10px 0 20px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .ticker-label::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0% {
            left: -100%;
        }

        20% {
            left: 100%;
        }

        100% {
            left: 100%;
        }
    }

    .ticker-label i {
        animation: pulse-icon 2s infinite;
        margin-right: 12px;
    }

    @keyframes pulse-icon {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .ticker-content-container {
        flex: 1;
        overflow: hidden;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .ticker {
        display: inline-flex;
        white-space: nowrap;
        animation: ticker-move 35s linear infinite;
        padding-left: 20px;
    }

    .ticker:hover {
        animation-play-state: paused;
    }

    .ticker-item {
        display: flex;
        align-items: center;
        padding: 0 50px;
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: color 0.3s;
        cursor: pointer;
    }

    .ticker-item:hover {
        color: #ffca28;
    }

    .ticker-item i {
        margin-right: 12px;
        color: #ffca28;
        filter: drop-shadow(0 0 5px rgba(255, 202, 40, 0.4));
    }

    .ticker-date {
        margin-left: 8px;
        padding: 2px 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffca28;
    }

    @keyframes ticker-move {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    /* Adjust Fixed Header to move down for Ticker */
    .header-container {
        top: 44px !important;
    }

    /* Adjust Body Padding */
    body {
        padding-top: 44px;
    }

    .event-img {
        height: 200px;
        background-color: #ddd;
        position: relative;
    }

    .event-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .event-date {
        position: absolute;
        top: 20px;
        right: 20px;
        background: white;
        padding: 8px 15px;
        border-radius: 12px;
        font-weight: 700;
        color: #4f46e5;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .event-details {
        padding: 25px;
    }

    .event-details h3 {
        font-size: 1.25rem;
        margin-bottom: 10px;
        color: var(--text-dark);
    }

    .event-meta {
        display: flex;
        gap: 15px;
        color: var(--text-light);
        font-size: 0.9rem;
        margin-bottom: 20px;
    }

    .event-meta i {
        color: #4f46e5;
    }

    /* Testimonials */
    .testimonials-section {
        padding: 80px 20px;
    }

    .testimonial-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .quote-icon {
        font-size: 3rem;
        color: #e0e7ff;
        margin-bottom: 20px;
    }

    .testimonial-text {
        font-size: 1.2rem;
        font-style: italic;
        color: var(--text-dark);
        margin-bottom: 30px;
        line-height: 1.8;
    }

    .user-info img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 10px;
    }

    /* CTA Section */
    .cta-section {
        background: var(--text-dark);
        color: white;
        padding: 80px 20px;
        text-align: center;
        border-radius: 30px;
        margin: 80px 20px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        overflow: hidden;
    }

    .cta-content {
        position: relative;
        z-index: 2;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .stats-container {
            grid-template-columns: 1fr;
            gap: 40px;
            margin-top: 40px;
        }

        .steps-grid {
            grid-template-columns: 1fr;
        }

        .hero-buttons {
            flex-direction: column;
        }
    }

    /* Notification Bar */
    .notification-bar {
        background: #1f2937;
        color: white;
        padding: 10px 20px;
        font-size: 0.9rem;
        display: none;
        /* Hidden by default, shown by JS if content exists */
        justify-content: center;
        align-items: center;
        position: relative;
        z-index: 100;
        animation: slideDown 0.5s ease-out forwards;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .notification-icon {
        color: #fbbf24;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-100%);
        }

        to {
            transform: translateY(0);
        }
    }
</style>

<?php
// Fetch User Notifications if logged in, else nothing (or global announcements if table supports it)
$notifications = [];
if (isset($_SESSION['user_id'])) {
    require_once 'includes/functions.php';
    $notifResult = getUnreadNotifications($_SESSION['user_id']);
    if ($notifResult && $notifResult->num_rows > 0) {
        while ($row = $notifResult->fetch_assoc()) {
            $notifications[] = $row;
        }
    }
}
?>

<?php if (!empty($notifications)): ?>
    <div class="notification-bar" id="notificationBar" style="display: flex;">
        <div class="notification-content">
            <i class="fas fa-bell notification-icon"></i>
            <span id="notifMessage">
                <?php echo htmlspecialchars($notifications[0]['message']); ?>
                <?php if (count($notifications) > 1): ?>
                    <span style="opacity: 0.7; font-size: 0.8em; margin-left: 5px;">(+
                        <?php echo count($notifications) - 1; ?> more)
                    </span>
                <?php endif; ?>
            </span>
        </div>
        <button onclick="document.getElementById('notificationBar').style.display='none'"
            style="background:none; border:none; color:white; margin-left:20px; cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
<?php endif; ?>

<!-- Event Ticker -->
<?php
$ticker_sql = "SELECT title, start_date FROM events WHERE status = 'active' AND start_date >= CURDATE() ORDER BY start_date ASC LIMIT 10";
$ticker_result = $conn->query($ticker_sql);
?>
<div class="ticker-wrap">
    <div class="ticker-label">
        <i class="fas fa-bolt mr-2"></i> Upcoming
    </div>
    <div class="ticker-content-container">
        <div class="ticker">
            <?php
            if ($ticker_result && $ticker_result->num_rows > 0) {
                // Duplicate content for seamless loop
                $ticker_items_html = '';
                while ($row = $ticker_result->fetch_assoc()) {
                    $date = date('M d', strtotime($row['start_date']));
                    $ticker_items_html .= '<div class="ticker-item"><i class="fas fa-star"></i> ' . htmlspecialchars($row['title']) . '<span class="ticker-date">' . $date . '</span></div>';
                }
                echo $ticker_items_html;
                echo $ticker_items_html; // Duplicate for smooth loop
            } else {
                echo '<div class="ticker-item">No upcoming events. Stay tuned!</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Hero Section -->
<header class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-blur"></div>
    <div class="hero-overlay"></div>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="hero-content">
        <div class="college-name animate-hidden" id="collegeName" style="font-size: 1.2rem; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 15px; letter-spacing: 2px; text-transform: uppercase;">
            GOVERNMENT ARTS AND SCIENCE COLLEGE, THIRUMAYAM
        </div>
        <h1 class="hero-title animate-hidden" id="heroTitle">Experience Campus Life<br>Like Never Before.</h1>
        <p class="hero-subtitle animate-hidden" id="heroSubtitle">The all-in-one platform to discover, manage, and
            participate in events. Connect with your college community today.</p>

        <div class="hero-buttons animate-hidden" id="heroButtons">
            <a href="events.php" class="btn-hero-primary">Explore Events</a>
            <a href="register.php" class="btn-hero-secondary">Join Now</a>
        </div>
    </div>
</header>

<!-- Stats Bar -->
<?php
// Fetch Stats
$stats = [
    'students' => 0,
    'events' => 0,
    'departments' => 5 // Default
];

$student_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
if ($res = $conn->query($student_query)) {
    $stats['students'] = $res->fetch_assoc()['count'];
}

$event_query = "SELECT COUNT(*) as count FROM events";
if ($res = $conn->query($event_query)) {
    $stats['events'] = $res->fetch_assoc()['count'];
}
?>
<div class="stats-container animate-hidden observe-fade-up">
    <div class="stat-item">
        <h3 id="stat-students">
            <?php echo $stats['students']; ?>+
        </h3>
        <p>Active Students</p>
    </div>
    <div class="stat-item">
        <h3 id="stat-events">
            <?php echo $stats['events']; ?>+
        </h3>
        <p>Total Events</p>
    </div>
    <div class="stat-item">
        <h3>
            <?php echo $stats['departments']; ?>+
        </h3>
        <p>Departments</p>
    </div>
</div>

<!-- How It Works -->
<section class="steps-section">
    <div class="section-header observe-fade-up animate-hidden">
        <span class="badge">Simple Process</span>
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Get started with Campus Connect in three easy steps.</p>
    </div>

    <div class="steps-grid">
        <div class="step-card observe-fade-up animate-hidden delay-100">
            <div class="step-number">1</div>
            <div class="step-icon"><i class="fas fa-user-plus"></i></div>
            <h3>Create Account</h3>
            <p style="color: var(--text-light);">Sign up with your student email to access personalized dashboards and
                event recommendations.</p>
        </div>
        <div class="step-card observe-fade-up animate-hidden delay-200">
            <div class="step-number">2</div>
            <div class="step-icon"><i class="fas fa-search-location"></i></div>
            <h3>Discover Events</h3>
            <p style="color: var(--text-light);">Browse through academic seminars, cultural fests, and workshops
                happening on campus.</p>
        </div>
        <div class="step-card observe-fade-up animate-hidden delay-300">
            <div class="step-number">3</div>
            <div class="step-icon"><i class="fas fa-ticket-alt"></i></div>
            <h3>Register & Attend</h3>
            <p style="color: var(--text-light);">One-click registration. Get your digital pass and QR code for
                hassle-free entry.</p>
        </div>
    </div>
</section>

<!-- Upcoming Events Preview -->
<section class="events-preview-section">
    <div class="section-header observe-fade-up animate-hidden">
        <span class="badge">Don't Miss Out</span>
        <h2 class="section-title">Featured Events</h2>
    </div>

    <div class="events-grid" id="eventsGrid">
        <?php
        // Fetch upcoming 3 events for featured section
        $events_sql = "SELECT * FROM events WHERE status = 'active' AND start_date >= CURDATE() ORDER BY start_date ASC LIMIT 3";
        $events_result = $conn->query($events_sql);

        if ($events_result && $events_result->num_rows > 0) {
            $delay = 100;
            while ($row = $events_result->fetch_assoc()) {
                $date = new DateTime($row['start_date']);
                $formattedDate = $date->format('M d');
                $formattedTime = $date->format('g:i A');
                // Fallback image if none provided
                $bgImage = !empty($row['cover_image']) ? htmlspecialchars($row['cover_image']) : 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80';

                echo '
                <div class="event-card observe-fade-up animate-hidden" style="animation-delay: ' . $delay . 'ms">
                    <div class="event-img">
                        <img src="' . $bgImage . '" alt="' . htmlspecialchars($row['title']) . '">
                        <span class="event-date">' . $formattedDate . '</span>
                    </div>
                    <div class="event-details">
                        <h3>' . htmlspecialchars($row['title']) . '</h3>
                        <div class="event-meta">
                            <span><i class="fas fa-clock"></i> ' . $formattedTime . '</span>
                            <span><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['venue']) . '</span>
                        </div>
                        <p style="color: var(--text-light); margin-bottom: 20px;">' . htmlspecialchars(substr($row['description'], 0, 80)) . '...</p>
                        <a href="login.php" style="color: #4f46e5; font-weight: 600; text-decoration: none;">Register Now &rarr;</a>
                    </div>
                </div>';
                $delay += 100;
            }
        } else {
            echo '<p style="text-align:center; width:100%; grid-column: 1/-1;">No upcoming events found. Stay tuned!</p>';
        }
        ?>
    </div>

    <div style="text-align: center; margin-top: 50px;">
        <a href="events.php" class="btn-hero-primary"
            style="background: #4f46e5; color: white; padding: 12px 30px;">View All Events</a>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section">
    <div class="section-header observe-fade-up animate-hidden">
        <span class="badge">Community</span>
        <h2 class="section-title">Student Voices</h2>
    </div>

    <div class="testimonial-card observe-fade-up animate-hidden">
        <i class="fas fa-quote-left quote-icon"></i>
        <p class="testimonial-text">"Campus Connect has completely changed how I find out about events. I used to miss
            out on workshops simply because I didn't see the poster. Now, everything is on my phone!"</p>
        <div class="user-info">
            <img src="https://ui-avatars.com/api/?name=Ganimozhi+M&background=random" alt="User">
            <h4 style="margin: 0; color: var(--text-dark);">Ganimozhi</h4>
            <span style="color: var(--text-light); font-size: 0.9rem;">Computer Science Student</span>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section observe-fade-up animate-hidden">
    <div class="shape shape-1" style="background: rgba(255,255,255,0.05);"></div>
    <div class="shape shape-2" style="background: rgba(255,255,255,0.05);"></div>

    <div class="cta-content">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Ready to Get Started?</h2>
        <p
            style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 30px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Join thousands of students and organizers streamlining campus events today.</p>
        <a href="register.php"
            style="background: white; color: #1f2937; padding: 15px 40px; border-radius: 50px; font-weight: 700; text-decoration: none; display: inline-block;">Create
            Free Account</a>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Hero Animations on Load
        setTimeout(() => {
            document.getElementById('collegeName').classList.remove('animate-hidden');
            document.getElementById('collegeName').classList.add('animate-fade-up');
        }, 50);

        setTimeout(() => {
            document.getElementById('heroTitle').classList.remove('animate-hidden');
            document.getElementById('heroTitle').classList.add('animate-fade-up');
        }, 200);

        setTimeout(() => {
            document.getElementById('heroSubtitle').classList.remove('animate-hidden');
            document.getElementById('heroSubtitle').classList.add('animate-fade-up');
        }, 400);

        setTimeout(() => {
            document.getElementById('heroButtons').classList.remove('animate-hidden');
            document.getElementById('heroButtons').classList.add('animate-fade-up');
        }, 600);

        // Scroll Observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('animate-hidden');
                    entry.target.classList.add('animate-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.observe-fade-up').forEach(el => observer.observe(el));

        // Realtime Updates via SSE
        const eventSource = new EventSource('api/stream_index_data.php');


        eventSource.onmessage = function (event) {
            const data = JSON.parse(event.data);

            // Update Stats
            if (data.stats) {
                document.getElementById('stat-students').innerText = data.stats.students + '+';
                document.getElementById('stat-events').innerText = data.stats.events + '+';
            }

            // Update Events
            if (data.events) {
                const eventsGrid = document.getElementById('eventsGrid');
                let eventsHtml = '';
                let delay = 100;

                data.events.forEach(row => {
                    eventsHtml += `
                    <div class="event-card animate-fade-up" style="animation-delay: ${delay}ms">
                        <div class="event-img">
                            <img src="${row.image_url}" alt="${row.title}">
                            <span class="event-date">${row.formatted_date}</span>
                        </div>
                        <div class="event-details">
                            <h3>${row.title}</h3>
                            <div class="event-meta">
                                <span><i class="fas fa-clock"></i> ${row.formatted_time}</span>
                                <span><i class="fas fa-map-marker-alt"></i> ${row.venue}</span>
                            </div>
                            <p style="color: var(--text-light); margin-bottom: 20px;">${row.short_description}</p>
                            <a href="login.php" style="color: #4f46e5; font-weight: 600; text-decoration: none;">Register Now &rarr;</a>
                        </div>
                    </div>`;
                    delay += 100;
                });

                if (eventsHtml === '') {
                    eventsHtml = '<p style="text-align:center; width:100%; grid-column: 1/-1;">No upcoming events found. Stay tuned!</p>';
                }

                // Only update if content is different to avoid flicker
                if (eventsGrid.innerHTML !== eventsHtml) {
                    eventsGrid.innerHTML = eventsHtml;
                }
            }
        };

        eventSource.onerror = function (err) {
            console.error("EventSource failed:", err);
            eventSource.close();
        };
    });
</script>

<?php
require_once 'includes/footer.php';
?>