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
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
        background-color: var(--bg-light);
    }

    /* Animations */
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

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes float {
        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    .animate-hidden {
        opacity: 0;
    }

    .animate-fade-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    .animate-slide-left {
        animation: slideInLeft 0.8s ease-out forwards;
    }

    .animate-slide-right {
        animation: slideInRight 0.8s ease-out forwards;
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
    .college-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 100px 0;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 2rem;
    }

    /* Floating Shapes Background */
    .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 6s infinite ease-in-out;
    }

    .shape-1 {
        width: 100px;
        height: 100px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 150px;
        height: 150px;
        bottom: 10%;
        right: 10%;
        animation-delay: 1s;
    }

    .shape-3 {
        width: 60px;
        height: 60px;
        top: 40%;
        right: 20%;
        animation-delay: 2s;
    }

    /* College Content Section */
    .content-section {
        padding: 80px 0;
        background: white;
    }

    .grid-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .section-image img {
        width: 100%;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }

    .section-image img:hover {
        transform: scale(1.02);
    }

    .section-content h2 {
        font-size: 2.5rem;
        color: var(--text-dark);
        margin-bottom: 20px;
        font-weight: 700;
    }

    .section-content p {
        font-size: 1.05rem;
        line-height: 1.8;
        color: var(--text-light);
        margin-bottom: 15px;
        text-align: justify;
    }

    @media (max-width: 992px) {
        .grid-container {
            grid-template-columns: 1fr;
            gap: 40px;
            text-align: center;
        }

        .hero-title {
            font-size: 2.8rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="college-hero">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="hero-content container">
        <h1 class="hero-title animate-hidden" id="heroTitle">About the College</h1>
        <p class="hero-subtitle animate-hidden delay-100" id="heroSubtitle">Empowering Minds, Shaping Futures through
            Academic Excellence.</p>
    </div>
</section>

<!-- Main content section -->
<section class="content-section">
    <div class="grid-container">
        <!-- Photo on Left -->
        <div class="section-image animate-hidden" id="imageCol">
            <img src="assets/clg.jpeg" alt="Government Arts and Science College, Thirumayam">
        </div>

        <!-- Paragraph on Right -->
        <div class="section-content animate-hidden delay-200" id="textCol">
            <h2>Our Institution</h2>
            <p>The Government Arts and Science College, Thirumayam affiliated to Bharathidasan University,
                Tiruchirappalli, was established in the year 2022.</p>

            <p>The college was started with 5 UG degree courses in Tamil, English, Commerce, Computer Science and
                Mathematics with a total strength of 650 students.</p>

            <p>The campus, situated in the middle of Thirumayam Town with G + 2 Floor building with 14 classrooms,
                science labs, a well-equipped library with internet facility. The campus is Wi-Fi enabled with 150 Mbps
                fiber connected speed, Equipped with 24 Hrs CCTV Surveillance, separate parking facilities for the staff
                members and the students.</p>
        </div>
    </div>
    <!-- Additional Info Section -->
    <div class="content-section" style="padding-top: 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 10px;">
            <div class="section-content animate-hidden delay-300" id="bottomText">
                <p>Physical education, being the part of curriculum, the practice is also carried out in our college
                    campus.
                    Sports like Athletics, Skipping, Short Put, Disc Throw, Kabaadi, Foot Ball, Kho-Kho, Tennikoit,
                    Shuttlecock, Throw Ball, Volley Ball, Carom, Chess and Cricket are practiced.</p>

                <p>The college library is an asset for both budding scholars and students. Home to the vast collection
                    of
                    around 3000 books, covering almost all the aspects of Arts, Commerce, Humanities and Science,
                    general
                    studies, TNPSC, TRB, Banking Exams related books.</p>

                <p>The College have a good Computer Lab with highly configured system with the power support of UPS and
                    highspeed internet connection.</p>

                <p>Tamil Nadu Skill Development Corporation Provides the Naan Mudhalvan Training which aims to provide
                    dynamic information for college students on courses and relevant information about industry specific
                    skill offerings.</p>

                <p>The college run with highly qualified professors as per the UGC prescribed qualification such as
                    SET/NET/JRF/PhD.</p>

                <p>The college offers quality education by preparing the students to meet the ever - changing and
                    challenging workplace environment.</p>
            </div>
        </div>
    </div>
</section>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Simple entrance animations
        document.getElementById('heroTitle').classList.add('animate-fade-up');
        document.getElementById('heroSubtitle').classList.add('animate-fade-up');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        observer.observe(document.getElementById('imageCol'));
        observer.observe(document.getElementById('textCol'));
        observer.observe(document.getElementById('bottomText'));
    });
</script>

<?php
require_once 'includes/footer.php';
?>