<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);

function active($page, $currentPage)
{
    return $page === $currentPage ? 'active' : '';
}
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Guest';
$userId = $_SESSION['user_id'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System </title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    <div class="header-container">
        <!-- Main header section -->
        <div class="main-header">
            <!-- Logo and College name -->
            <!-- Logo and College name -->
            <a href="index.php" class="college-brand" style="text-decoration: none;">
                <div class="logo-container">
                    <div class="logo">
                        <img src="assets/clg-logo.png" alt="College Logo" class="logo-image">
                    </div>
                </div>
                <div class="college-info">
                    <div class="college-name">CAMPUS CONNECT</div>
                    <div class="college-tagline"> Digital Event Management System</div>
                </div>
            </a>

            <!-- Combined Navigation with equal spacing -->
            <div class="nav-section">
                <nav class="main-nav">
                    <div class="nav-item">
                        <a href="index.php" class="nav-link <?= active('index.php', $currentPage); ?>">
                            <i class="fas fa-home nav-icon"></i>
                            <span class="nav-text">Home</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="about.php" class="nav-link <?= active('about.php', $currentPage); ?>">
                            <i class="fas fa-info-circle nav-icon"></i>
                            <span class="nav-text">About</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="events.php" class="nav-link <?= active('events.php', $currentPage); ?>">
                            <i class="fas fa-calendar-alt nav-icon"></i>
                            <span class="nav-text">Events</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="gallery.php" class="nav-link <?= active('gallery.php', $currentPage); ?>">
                            <i class="fas fa-images nav-icon"></i>
                            <span class="nav-text">Gallery</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="contact.php" class="nav-link <?= active('contact.php', $currentPage); ?>">
                            <i class="fas fa-envelope nav-icon"></i>
                            <span class="nav-text">Contact</span>
                        </a>
                    </div>
                    <div class="nav-item auth-nav-item">
                        <button class="nav-link auth-nav-link login-btn" id="loginButton">
                            <i class="fas fa-sign-in-alt nav-icon"></i>
                            <span class="nav-text">Login</span>
                        </button>
                    </div>
                    <div class="nav-item auth-nav-item">
                        <button class="nav-link auth-nav-link register-btn" id="regButton">
                            <i class="fas fa-user-plus nav-icon"></i>
                            <span class="nav-text">Register</span>
                        </button>
                    </div>
                </nav>
            </div>

            <!-- Hamburger Button (mobile only) -->
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>

        <!-- Mobile Nav Drawer -->
        <div class="mobile-nav" id="mobileNav">
            <a href="index.php" class="mobile-nav-link <?= active('index.php', $currentPage) ? 'mobile-active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="about.php" class="mobile-nav-link <?= active('about.php', $currentPage) ? 'mobile-active' : ''; ?>">
                <i class="fas fa-info-circle"></i> About
            </a>
            <a href="events.php" class="mobile-nav-link <?= active('events.php', $currentPage) ? 'mobile-active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="gallery.php" class="mobile-nav-link <?= active('gallery.php', $currentPage) ? 'mobile-active' : ''; ?>">
                <i class="fas fa-images"></i> Gallery
            </a>
            <a href="contact.php" class="mobile-nav-link <?= active('contact.php', $currentPage) ? 'mobile-active' : ''; ?>">
                <i class="fas fa-envelope"></i> Contact
            </a>
            <div class="mobile-nav-divider"></div>
            <a href="login.php" class="mobile-nav-link mobile-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="mobile-nav-link mobile-register">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>
    </div>

    <script>
        document.getElementById('loginButton').addEventListener('click', function () {
            window.location.href = 'login.php';
        });
        document.getElementById('regButton').addEventListener('click', function () {
            window.location.href = 'register.php';
        });

        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');
        hamburgerBtn.addEventListener('click', function () {
            const isOpen = mobileNav.classList.toggle('open');
            hamburgerBtn.classList.toggle('active', isOpen);
        });
        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!hamburgerBtn.contains(e.target) && !mobileNav.contains(e.target)) {
                mobileNav.classList.remove('open');
                hamburgerBtn.classList.remove('active');
            }
        });
    </script>

    <style>
        /* Header container */
        .header-container {
            width: 100%;
            background-color: #fff;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-top: 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;

        }

        /* Main header */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 40px;
            position: relative;
        }

        /* College brand with logo */
        .college-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }

        .logo-container {
            position: relative;
            width: 50px;
            height: 50px;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 50%;

        }


        .college-info {
            display: flex;
            flex-direction: column;
        }

        .college-name {
            font-size: 22px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .college-tagline {
            font-size: 11px;
            color: #666;
            font-style: italic;
            letter-spacing: 0.3px;
        }

        /* Combined Navigation Section */
        .nav-section {
            display: flex;
            justify-content: flex-end;
            flex-grow: 1;
        }

        .main-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            /* Equal spacing between all items */
            padding: 0;
            border-radius: 8px;
        }

        /* Navigation items - All equal size and spacing */
        .nav-item {
            position: relative;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
            min-width: 75px;
            height: 50px;
            background-color: transparent;
            border: none;
            cursor: pointer;
            position: relative;
            border-radius: 6px;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            color: #1a237e;
            background-color: rgba(26, 35, 126, 0.05);
            border-color: rgba(26, 35, 126, 0.1);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }

        .nav-icon {
            font-size: 14px;
            margin-bottom: 4px;
            transition: all 0.25s ease;
            color: #666;
        }

        .nav-link:hover .nav-icon {
            transform: translateY(-2px);
            color: #1a237e;
        }

        .nav-text {
            transition: all 0.25s ease;
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        .nav-link:hover .nav-text {
            font-weight: 700;
        }

        /* Specific styles for login button */
        .login-btn {
            color: #1a237e;
            border: 1px solid rgba(26, 35, 126, 0.2);
        }

        .login-btn .nav-icon {
            color: #1a237e;
        }

        .login-btn:hover {
            background-color: rgba(26, 35, 126, 0.08);
            color: #0d1b4c;
            border-color: rgba(26, 35, 126, 0.3);
        }

        /* Specific styles for register button */
        .register-btn {
            color: white;
            background-color: #1a237e;
            border: 1px solid #1a237e;
        }

        .register-btn .nav-icon {
            color: white;
        }

        .register-btn:hover {
            background-color: #283593;
            color: white;
            border-color: #283593;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(26, 35, 126, 0.2);
        }

        /* Active state for all nav items */
        .nav-link.active {
            color: #1a237e;
            background-color: rgba(26, 35, 126, 0.1);
            font-weight: 700;
            border-color: rgba(26, 35, 126, 0.2);
        }

        .nav-link.active .nav-icon {
            color: #1a237e;
        }

        /* Visual indicator for spacing */
        .spacing-demo {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #1a237e;
        }

        .spacing-demo h3 {
            color: #1a237e;
            margin-bottom: 10px;
        }

        /* Content container */
        .content-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            color: #1a237e;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, #1a237e, #283593);
            border-radius: 2px;
        }

        .content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            line-height: 1.7;
        }

        .content ul {
            margin-left: 20px;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .content li {
            margin-bottom: 8px;
            padding-left: 5px;
        }

        /* Spacing visualization */
        .spacing-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .spacing-item {
            width: 75px;
            height: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin: 0 10px;
            font-size: 11px;
            font-weight: 600;
            color: #555;
        }

        .spacing-gap {
            color: #999;
            font-size: 12px;
            font-weight: bold;
        }

        /* ===== MOBILE RESPONSIVE ===== */
        .hamburger-btn {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .hamburger-btn:hover { background: rgba(26,35,126,0.07); }
        .hamburger-btn span {
            display: block;
            width: 24px;
            height: 2px;
            background: #1a237e;
            border-radius: 2px;
            transition: all 0.3s ease;
            transform-origin: center;
        }
        .hamburger-btn.active span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger-btn.active span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .hamburger-btn.active span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        .mobile-nav {
            display: none;
            flex-direction: column;
            background: #fff;
            border-top: 1px solid #e8eaf6;
            padding: 8px 16px 16px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease, padding 0.2s ease;
        }
        .mobile-nav.open {
            display: flex;
            max-height: 500px;
        }
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #555;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .mobile-nav-link:hover, .mobile-nav-link.mobile-active {
            background: rgba(26,35,126,0.07);
            color: #1a237e;
        }
        .mobile-nav-divider {
            height: 1px;
            background: #e8eaf6;
            margin: 8px 0;
        }
        .mobile-login {
            color: #1a237e;
            border: 1px solid rgba(26,35,126,0.2);
            margin-bottom: 6px;
        }
        .mobile-register {
            background: #1a237e;
            color: #fff !important;
        }
        .mobile-register:hover { background: #283593 !important; color: #fff !important; }

        @media (max-width: 768px) {
            .main-header { padding: 10px 16px; }
            .nav-section { display: none; }
            .hamburger-btn { display: flex; }
            .college-name { font-size: 16px; }
            .college-tagline { display: none; }
            .logo-image { width: 38px; height: 38px; }
        }
    </style>