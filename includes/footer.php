<!-- Page-specific content ends here -->
</main>
<style>
    :root {
        --footer-bg: #1a237e;
        /* Deep Indigo Theme */
        --footer-text: #ffffff;
        --footer-accent: #e0e7ff;
        --footer-bottom: #121858;
    }

    footer {
        background-color: var(--footer-bg);
        color: var(--footer-text);
        padding: 70px 0 0;
        font-family: 'Poppins', sans-serif;
        margin-top: 50px;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 25px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 50px;
        padding-bottom: 50px;
    }

    .footer-col h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--footer-accent);
    }

    .footer-col p {
        font-size: 0.95rem;
        line-height: 1.8;
        opacity: 0.85;
        margin-top: 0;
        text-align: justify;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .footer-links li a {
        color: white;
        text-decoration: none;
        font-size: 0.9rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .footer-links li a:hover {
        opacity: 1;
        padding-left: 5px;
        color: var(--footer-accent);
    }

    .footer-map {
        width: 100%;
        height: 180px;
        border-radius: 12px;
        border: none;
    }

    .footer-contact-info {
        list-style: none;
        padding: 0;
    }

    .footer-contact-info li {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        opacity: 0.85;
    }

    .footer-contact-info i {
        color: var(--footer-accent);
        width: 20px;
        text-align: center;
    }

    .footer-bottom {
        background-color: var(--footer-bottom);
        padding: 30px 20px;
        text-align: center;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .footer-copy-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .footer-bottom-logo {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 50%;
        background: white;
        padding: 5px;
        margin: 5px 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .footer-bottom p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.8;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 40px;
            text-align: center;
        }

        .footer-links {
            justify-content: center;
        }

        .footer-contact-info li {
            justify-content: center;
        }
    }
</style>

<footer id="contact">
    <div class="footer-container">
        <!-- Column 1: College Info -->
        <div class="footer-col">
            <h3>GASCTYM.IN</h3>
            <p>Government Arts and Science College,  Thirumayam is affiliated to Bharathidasan University, Tiruchirappalli. The college is located in Thirumayam. Government Arts College was started in the
                academic year 2022-2023.</p>
        </div>

        <!-- Column 2: Useful Links -->
        <div class="footer-col">
            <h3>USEFUL LINK</h3>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About us</a></li>
                <li><a href="about_college.php">About College</a></li>
                <li><a href="events.php">Events</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Column 3: Map -->
        <div class="footer-col">
            <h3>LOCATION</h3>
            <iframe class="footer-map"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15702.7345524345!2d78.749444!3d10.247222!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b00696969696969%3A0x6969696969696969!2sGovernment%20Arts%20and%20Science%20College%2C%20Thirumayam!5e0!3m2!1sen!2sin!4v1700000000000"
                allowfullscreen="" loading="lazy"></iframe>
        </div>

        <!-- Column 4: Contact -->
        <div class="footer-col">
            <h3>CONTACT</h3>
            <ul class="footer-contact-info">
                <li><i class="fas fa-map-marker-alt"></i> Thirumayam, 622 503</li>
                <li><i class="fas fa-phone"></i> (04333) 274 244</li>
                <li><i class="fas fa-envelope"></i> gasctym@gmail.com</li>
                <li><i class="fas fa-globe"></i> WWW.GASCTYM.IN</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <img src="assets/clg-logo.png" alt="College Logo" class="footer-bottom-logo">
        <p>Copyright &copy; <?php echo date('Y'); ?> All rights reserved.</p>
        <p>Developed by B.Sc Computer Science</p>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" title="Go to top">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
    #scrollToTopBtn {
        display: none;
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 99;
        border: none;
        outline: none;
        background: #1a237e;
        color: white;
        cursor: pointer;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        opacity: 0.9;
    }

    #scrollToTopBtn:hover {
        background-color: #4f46e5;
        transform: translateY(-5px);
        opacity: 1;
    }

    @media (max-width: 768px) {
        #scrollToTopBtn {
            bottom: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }
</style>

<script>
    // Get the button
    let mybutton = document.getElementById("scrollToTopBtn");

    // When the user scrolls down 300px from the top of the document, show the button
    window.onscroll = function () { scrollFunction() };

    function scrollFunction() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    mybutton.addEventListener("click", function () {
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>
</body>

</html>