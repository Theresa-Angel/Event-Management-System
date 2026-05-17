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
        --accent-purple: #4f46e5;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--bg-light);
        color: var(--text-dark);
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

    /* Hero Section */
    .contact-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 80px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Contact Container */
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 20px;
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 40px;
    }

    /* Info Cards */
    .info-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        height: fit-content;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 30px;
    }

    .info-icon {
        width: 50px;
        height: 50px;
        background: #e0e7ff;
        color: var(--accent-purple);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .info-content h4 {
        margin-bottom: 5px;
        font-size: 1.1rem;
        color: var(--text-dark);
    }

    .info-content p {
        color: var(--text-light);
        font-size: 0.95rem;
        margin: 0;
    }

    /* Contact Form */
    .form-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dark);
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-family: inherit;
        transition: all 0.3s;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--accent-purple);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .btn-submit {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
    }

    /* FAQ Section */
    .faq-section {
        max-width: 800px;
        margin: 60px auto 0;
    }

    .faq-item {
        background: white;
        border-radius: 12px;
        margin-bottom: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .faq-question {
        padding: 20px;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-dark);
    }

    .faq-answer {
        padding: 0 20px;
        max-height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
        color: var(--text-light);
    }

    .faq-item.active .faq-answer {
        padding-bottom: 20px;
        max-height: 200px;
    }

    .faq-item.active .faq-question {
        color: var(--accent-purple);
    }

    .faq-item.active i {
        transform: rotate(180deg);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: white;
        padding: 40px;
        border-radius: 20px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        animation: fadeInUp 0.4s ease;
    }

    .modal-icon {
        font-size: 3rem;
        color: #10b981;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container animate-hidden" id="heroContent">
        <h1 class="hero-title">Get in Touch</h1>
        <p class="hero-subtitle">Have questions about Campus Connect? We're here to help you get the most out of your
            digital campus experience.</p>
    </div>
</section>

<!-- Main Content -->
<div class="contact-container">
    <!-- Contact Info Sidebar -->
    <div class="contact-info animate-hidden observe-fade-up">
        <div class="info-card">
            <h3 style="margin-bottom: 30px; font-size: 1.5rem;">Contact Information</h3>

            <div class="info-item">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-content">
                    <h4>Visit Us</h4>
                    <p>Computer Science Dept.<br>Govt. Arts & Science College<br>Thirumayam - 622507</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                <div class="info-content">
                    <h4>Call Us</h4>
                    <p>+91 88257 66907<br>Mon-Fri, 9am - 4pm</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-content">
                    <h4>Email Us</h4>
                    <p>theresathomas@gmail.com<br>atchayaatchu360@gmail.com</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon"><i class="fas fa-share-alt"></i></div>
                <div class="info-content">
                    <h4>Socials</h4>
                    <div style="margin-top: 5px; display: flex; gap: 10px;">
                        <a href="https://instagram.com/username"
                            style="color: var(--accent-purple); font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                        <a href="https://linkedin.com/in/theresaangel-thomas"
                            style="color: var(--accent-purple); font-size: 1.2rem;"><i class="fab fa-linkedin"></i></a>
                        <a href="https://wa.me/8825766907" style="color: var(--accent-purple); font-size: 1.2rem;"><i
                                class="fab fa-whatsapp"></i></a>
                        <a href="mailto:theresathomas096@gmail.com"
                            style="color: var(--accent-purple); font-size: 1.2rem;"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="contact-form-wrapper animate-hidden observe-fade-up delay-100">
        <div class="form-card">
            <h3 style="margin-bottom: 30px; font-size: 1.8rem;">Send a Message</h3>
            <form id="contactForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="username" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="organizer">Organizer</option>
                            <option value="faculty">Faculty</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject" required>
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="feedback">Feedback</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" required placeholder="How can we help you?"></textarea>
                </div>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="container animate-hidden observe-fade-up">
    <div class="faq-section">
        <h2 style="text-align: center; margin-bottom: 40px; font-size: 2rem;">Frequently Asked Questions</h2>

        <div class="faq-item">
            <button class="faq-question">
                How do I register for an event?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Log in to your dashboard, browse the 'Upcoming Events' section, and click the 'Register' button on
                    your desired event.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                Can I cancel my registration?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Yes, go to 'My Registrations' and select 'Cancel' at least 24 hours before the event starts.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                How do I become an organizer?
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="faq-answer">
                <p>Register with the 'Organizer' role. Your account will be pending approval until verified by the
                    college administration.</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon"><i class="fas fa-check-circle"></i></div>
        <h3>Message Sent!</h3>
        <p>Thank you for reaching out. We will get back to you shortly.</p>
        <button class="btn-submit" onclick="closeModal()" style="width: auto; padding: 10px 30px;">Close</button>
    </div>
</div>

<script>
    // Animations
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.getElementById('heroContent').classList.remove('animate-hidden');
            document.getElementById('heroContent').classList.add('animate-fade-up');
        }, 200);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('animate-hidden');
                    entry.target.classList.add('animate-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.observe-fade-up').forEach(el => observer.observe(el));
    });

    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.parentElement;

            // Close others
            document.querySelectorAll('.faq-item').forEach(other => {
                if (other !== item) other.classList.remove('active');
            });

            item.classList.toggle('active');
        });
    });

    // Detailed Form Handling
    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = this.querySelector('.btn-submit');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Sending...';

        const formData = new FormData(this);

        fetch('api/submit_feedback.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('successModal').style.display = 'flex';
                    this.reset();
                } else {
                    alert(data.message || 'Something went wrong. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = originalText;
            });
    });

    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }
</script>

<?php
require_once 'includes/footer.php';
?>