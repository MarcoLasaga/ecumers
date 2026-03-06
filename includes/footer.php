<?php
?>
    </div>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col footer-brand">
            <div class="footer-brand-header">
                <span class="logo-mark footer-logo-mark">SS</span>
                <span class="logo-text footer-logo-text">Salsa Store</span>
            </div>
            <p class="footer-brand-copy">
                Minimal silhouettes, bold details. Salsa Store curates seasonless streetwear staples
                designed to move with you from first light to last train.
            </p>
        </div>

        <div class="footer-col">
            <h4>Shop</h4>
            <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
            <a href="<?php echo BASE_URL; ?>/shop.php">Shop</a>
            <a href="<?php echo BASE_URL; ?>/about.php">About</a>
            <a href="<?php echo BASE_URL; ?>/contact.php">Contact</a>
        </div>

        <div class="footer-col">
            <h4>Support</h4>
            <a href="<?php echo BASE_URL; ?>/faq.php">FAQ</a>
            <a href="#">Shipping &amp; Delivery</a>
            <a href="#">Returns &amp; Exchanges</a>
            <a href="#">Order Tracking</a>
        </div>

        <div class="footer-col">
            <h4>Legal</h4>
            <a href="<?php echo BASE_URL; ?>/terms.php">Terms &amp; Conditions</a>
            <a href="<?php echo BASE_URL; ?>/privacy.php">Privacy Policy</a>
            <a href="<?php echo BASE_URL; ?>/refund.php">Refund Policy</a>
            <div class="footer-newsletter">
                <p>Stay in the loop.</p>
                <form id="newsletter-form" autocomplete="off">
                    <div class="footer-newsletter-input">
                        <input type="email" id="newsletter-email" placeholder="Email address" aria-label="Email address">
                        <button type="submit">Join</button>
                    </div>
                    <div class="footer-newsletter-message" id="newsletter-message"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="container footer-divider"></div>
    <div class="container footer-bottom">
        <div class="footer-social">
            <a href="#" aria-label="Instagram">IG</a>
            <a href="#" aria-label="Facebook">FB</a>
            <a href="#" aria-label="Twitter">TW</a>
        </div>
        <p class="footer-copy">&copy; <?php echo date('Y'); ?> Salsa Store. All rights reserved.</p>
    </div>
</footer>

<!-- Product Image Modal -->
<div class="image-modal-overlay" id="image-modal" aria-hidden="true">
    <div class="image-modal">
        <button class="image-modal-close" id="image-modal-close" aria-label="Close image">×</button>
        <img src="" alt="Product image" id="image-modal-img">
    </div>
</div>

<div class="toast" role="status" aria-live="polite"></div>

<!-- Google Maps Places (placeholder key) -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_PLACES_API_KEY&libraries=places&callback=initSalsaAddressAutocomplete"
        async defer></script>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>