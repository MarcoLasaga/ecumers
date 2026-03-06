<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Contact – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section contact-section" data-reveal>
    <div class="section-header">
        <h1>Contact</h1>
        <p>Questions about fits, drops, or orders? Reach out – we’ll get back fast.</p>
    </div>

    <div class="contact-grid">
        <div class="contact-form-card">
            <form class="contact-form" id="contact-form" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact-name">Name</label>
                        <input type="text" id="contact-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-email">Email</label>
                        <input type="email" id="contact-email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact-subject">Subject</label>
                    <input type="text" id="contact-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="contact-message">Message</label>
                    <textarea id="contact-message" name="message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send message</button>
                <div class="contact-message-result" id="contact-message-result"></div>
            </form>
        </div>

        <div class="contact-info">
            <div class="contact-card">
                <h3>Email</h3>
                <p><a href="mailto:support@salsastore.com">support@salsastore.com</a></p>
                <p>For order questions, add your order ID so we can move faster.</p>
            </div>
            <div class="contact-card">
                <h3>Phone</h3>
                <p>+1 (000) 000-0000</p>
                <p>Mon–Fri, 9:00–18:00 (local).</p>
            </div>
            <div class="contact-card">
                <h3>Studio</h3>
                <p>Urban district, City Name</p>
                <p>Where we test every silhouette in motion.</p>
            </div>
        </div>
    </div>

    <div class="contact-map" data-reveal>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.843152204829!2d144.9556513153161!3d-37.81732797975114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzfCsDQ5JzAyLjQiUyAxNDTCsDU3JzI2LjMiRQ!5e0!3m2!1sen!2s!4v0000000000000"
            width="100%"
            height="320"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>