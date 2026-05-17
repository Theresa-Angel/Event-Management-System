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
    .about-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-title {
        font-size: 3rem;
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

    /* Mission Section */
    .mission-section {
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
        margin-bottom: 1.5rem;
        position: relative;
    }

    .section-content p {
        color: var(--text-light);
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 1.5rem;
    }

    /* Features Cards */
    .features-section {
        background: var(--bg-light);
        padding: 80px 0;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .feature-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 4px solid transparent;
        cursor: pointer;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
        border-bottom-color: #4f46e5;
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        background: #e0e7ff;
        color: #4f46e5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .feature-card:hover .feature-icon {
        background: #4f46e5;
        color: white;
        transform: rotateY(180deg);
    }

    .feature-card h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    /* Team Section */
    .team-section {
        padding: 80px 0;
        background: white;
    }

    .team-grid {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-top: 40px;
    }

    .team-member {
        text-align: center;
        width: 250px;
    }

    .member-img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .team-member:hover .member-img {
        transform: scale(1.1) rotate(5deg);
        border-color: #4f46e5;
    }

    .member-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .hero-title {
            font-size: 2.5rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    <div class="hero-content">
        <h1 class="hero-title animate-hidden" id="heroTitle">Revolutionizing Campus Events</h1>
        <p class="hero-subtitle animate-hidden" id="heroSubtitle">Streamlining organization, boosting participation, and
            bringing the campus community closer together with Campus Connect.</p>
    </div>
</section>

<!-- Vision & Mission -->
<section class="mission-section">
    <div class="grid-container">
        <div class="section-image animate-hidden observe-slide-right">
            <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                alt="Campus Event">
        </div>
        <div class="section-content animate-hidden observe-slide-left">
            <h2>Our Vision</h2>
            <p>At Campus Connect, we envision a fully digitized campus ecosystem where every event, workshop, and
                cultural fest is easily accessible to every student. We aim to eliminate the hassle of manual
                registrations and communication gaps.</p>
            <p>Our platform empowers organizers with powerful tools to manage events efficiently while giving students a
                one-stop hub to discover and engage with campus life.</p>
            <div style="margin-top: 30px;">
                <a href="about_college.php" class="btn-hero-primary"
                    style="background: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: 600; display: inline-block;">Learn
                    More About Our College &rarr;</a>
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="features-section">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <h2 style="text-align: center; margin-bottom: 50px; font-size: 2.5rem; color: #1f2937;">Why Choose Campus
            Connect?</h2>
        <div class="features-grid">
            <div class="feature-card animate-hidden observe-fade-up">
                <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                <h3>Real-time Updates</h3>
                <p>Never miss an event with instant notifications and live updates directly to your personalized
                    dashboard.</p>
            </div>

            <div class="feature-card animate-hidden observe-fade-up delay-100">
                <div class="feature-icon"><i class="fas fa-ticket-alt"></i></div>
                <h3>Seamless Booking</h3>
                <p>Register for events in seconds. No more long queues or paperwork - just click and attend.</p>
            </div>

            <div class="feature-card animate-hidden observe-fade-up delay-200">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Smart Analytics</h3>
                <p>Organizers get detailed insights into participation trends to help plan better future events.</p>
            </div>
        </div>
    </div>
</section>


<!-- Team Section (Optional) -->
<section class="team-section">
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: 2.5rem; margin-bottom: 2rem;">Meet The Team</h2>
        <p style="color: #6b7280; max-width: 600px; margin: 0 auto;">The creative minds behind Campus Connect, dedicated
            to improving your college experience.</p>

        <div class="team-grid">
            <div class="team-member animate-hidden observe-fade-up" onclick="showMemberDetails('theresa')">
                <div class="member-img">
                    <img src="assets/images/theresa.jpeg"
                        alt="Theresa Angel">
                </div>
                <h3>Theresa Angel</h3>
                <p>Lead Developer</p>
            </div>
            <div class="team-member animate-hidden observe-fade-up delay-100" onclick="showMemberDetails('atchaya')">
                <div class="member-img">
                    <img src="assets/images/atchaya.jpeg"
                        alt="Atchaya">
                </div>
                <h3>Atchaya</h3>
                <p>UI/UX Designer</p>
            </div>
            <div class="team-member animate-hidden observe-fade-up delay-200" onclick="showMemberDetails('sivakami')">
                <div class="member-img">
                    <img src="assets/images/sivakami.jpeg"
                        alt="Sivakami">
                </div>
                <h3>Sivakami</h3>
                <p>Team Member</p>
            </div>

            <div class="team-member animate-hidden observe-fade-up delay-200" onclick="showMemberDetails('karthiga')">
                <div class="member-img">
                    <img src="assets/images/karthiga.jpeg"
                        alt="Karthiga">
                </div>
                <h3>Karthiga</h3>
                <p>Team Member</p>
            </div>

            <div class="team-member animate-hidden observe-fade-up delay-200" onclick="showMemberDetails('ganimozhi')">
                <div class="member-img">
                    <img src="assets/images/ganimozhi.jpeg"
                        alt="Ganimozhi">
                </div>
                <h3>Ganimozhi</h3>
                <p>Team Member</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Member Details Modal -->
<div id="memberModal" class="member-modal">
    <div class="member-modal-content">
        <span class="member-modal-close" onclick="closeMemberModal()">&times;</span>
        <div class="member-modal-body">
            <div class="member-modal-photo">
                <img id="modalPhoto" src="" alt="Member Photo">
            </div>
            <div class="member-modal-details">
                <h2 id="modalName"></h2>
                <div class="member-detail-item">
                    <i class="fas fa-user-graduate"></i>
                    <span><strong>Class:</strong> <span id="modalClass"></span></span>
                </div>
                <div class="member-detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><strong>Batch:</strong> <span id="modalBatch"></span></span>
                </div>
                <div class="member-detail-item">
                    <i class="fas fa-university"></i>
                    <span><strong>College:</strong> <span id="modalCollege"></span></span>
                </div>
                <div class="member-detail-item">
                    <i class="fas fa-briefcase"></i>
                    <span><strong>Role:</strong> <span id="modalRole"></span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .team-member {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .team-member:hover {
        transform: translateY(-10px) scale(1.05);
    }
    
    .member-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .member-modal-content {
        background: white;
        margin: 8% auto;
        padding: 0;
        border-radius: 16px;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: zoomIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        overflow: hidden;
        position: relative;
    }
    
    @keyframes zoomIn {
        0% {
            transform: scale(0.5) rotate(-5deg);
            opacity: 0;
        }
        50% {
            transform: scale(1.05) rotate(2deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
            opacity: 1;
        }
    }
    
    .member-modal-close {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 28px;
        font-weight: bold;
        color: #6b7280;
        cursor: pointer;
        z-index: 10001;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .member-modal-close:hover {
        background: #ef4444;
        color: white;
        transform: rotate(90deg) scale(1.1);
    }
    
    .member-modal-body {
        display: flex;
        flex-direction: column;
    }
    
    .member-modal-photo {
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .member-modal-photo::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: transparent;
    }
    
    .member-modal-photo img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        animation: photoFloat 0.6s ease-out;
        position: relative;
        z-index: 1;
    }
    
    @keyframes photoFloat {
        0% {
            transform: translateY(50px) scale(0.5);
            opacity: 0;
        }
        100% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }
    
    .member-modal-details {
        padding: 20px;
    }
    
    .member-modal-details h2 {
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 20px;
        text-align: center;
        animation: slideInFromLeft 0.5s ease-out 0.2s both;
    }
    
    @keyframes slideInFromLeft {
        from {
            transform: translateX(-30px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .member-detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s;
        animation: slideInFromRight 0.5s ease-out both;
    }
    
    .member-detail-item:nth-child(1) { animation-delay: 0.3s; }
    .member-detail-item:nth-child(2) { animation-delay: 0.4s; }
    .member-detail-item:nth-child(3) { animation-delay: 0.5s; }
    .member-detail-item:nth-child(4) { animation-delay: 0.6s; }
    .member-detail-item:nth-child(5) { animation-delay: 0.7s; }
    
    @keyframes slideInFromRight {
        from {
            transform: translateX(30px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .member-detail-item:hover {
        background: #eef2ff;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
    }
    
    .member-detail-item i {
        font-size: 1.2rem;
        color: #4f46e5;
        width: 25px;
        text-align: center;
        animation: iconBounce 0.6s ease-out;
    }
    
    @keyframes iconBounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    .member-detail-item span {
        font-size: 0.9rem;
        color: #4b5563;
    }
    
    @media (max-width: 768px) {
        .member-modal-content {
            width: 95%;
            max-width: 380px;
            margin: 15% auto;
        }
        
        .member-modal-photo {
            height: 180px;
        }
        
        .member-modal-photo img {
            width: 100px;
            height: 100px;
        }
        
        .member-modal-details {
            padding: 15px;
        }
        
        .member-modal-details h2 {
            font-size: 1.3rem;
        }
        
        .member-detail-item {
            padding: 8px 10px;
        }
        
        .member-detail-item span {
            font-size: 0.85rem;
        }
    }
</style>

<script>
    // Team member data
    const teamMembers = {
        theresa: {
            name: 'Theresa Angel',
            photo: 'assets/images/theresa.jpeg',
            class: 'B.Sc Computer Science',
            batch: '2023-2026',
            college: 'Government Arts and Science College, Thirumayam',
            role: 'Lead Developer'
        },
        atchaya: {
            name: 'Atchaya',
            photo: 'assets/images/atchaya.jpeg',
            class: 'B.Sc Computer Science',
            batch: '2023-2026',
            college: 'Government Arts and Science College, Thirumayam',
            role: 'UI/UX Designer'
        },
        sivakami: {
            name: 'Sivakami',
            photo: 'assets/images/sivakami.jpeg',
            class: 'B.Sc Computer Science',
            batch: '2023-2026',
            college: 'Government Arts and Science College, Thirumayam',
            role: 'Team Member'
        },
        karthiga: {
            name: 'Karthiga',
            photo: 'assets/images/karthiga.jpeg',
            class: 'B.Sc Computer Science',
            batch: '2023-2026',
            college: 'Government Arts and Science College, Thirumayam',
            role: 'Team Member'
        },
        ganimozhi: {
            name: 'Ganimozhi',
            photo: 'assets/images/ganimozhi.jpeg',
            class: 'B.Sc Computer Science',
            batch: '2023-2026',
            college: 'Government Arts and Science College, Thirumayam',
            role: 'Team Member'
        }
    };
    
    function showMemberDetails(memberId) {
        const member = teamMembers[memberId];
        if (!member) return;
        
        document.getElementById('modalPhoto').src = member.photo;
        document.getElementById('modalName').textContent = member.name;
        document.getElementById('modalClass').textContent = member.class;
        document.getElementById('modalBatch').textContent = member.batch;
        document.getElementById('modalCollege').textContent = member.college;
        document.getElementById('modalRole').textContent = member.role;
        
        document.getElementById('memberModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    function closeMemberModal() {
        document.getElementById('memberModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('memberModal');
        if (event.target === modal) {
            closeMemberModal();
        }
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeMemberModal();
        }
    });
</script>

<!-- Animation Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Hero Animations
        setTimeout(() => {
            document.getElementById('heroTitle').classList.remove('animate-hidden');
            document.getElementById('heroTitle').classList.add('animate-fade-up');
        }, 300);

        setTimeout(() => {
            document.getElementById('heroSubtitle').classList.remove('animate-hidden');
            document.getElementById('heroSubtitle').classList.add('animate-fade-up');
        }, 600);

        // Scroll Observer for other elements
        const observerOptions = {
            threshold: 0.2, // Trigger when 20% visible
            rootMargin: "0px"
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;

                    if (el.classList.contains('observe-fade-up')) {
                        el.classList.add('animate-fade-up');
                    } else if (el.classList.contains('observe-slide-left')) {
                        el.classList.add('animate-slide-left');
                    } else if (el.classList.contains('observe-slide-right')) {
                        el.classList.add('animate-slide-right');
                    }

                    el.classList.remove('animate-hidden');
                    observer.unobserve(el); // Only animate once
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.observe-fade-up, .observe-slide-left, .observe-slide-right').forEach(el => {
            observer.observe(el);
        });
    });
</script>

<?php
require_once 'includes/footer.php';
?>