<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'About – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section about-section" data-reveal>
    <div class="about-grid">
        <div class="about-copy">
            <p class="hero-kicker">ABOUT</p>
            <h1 class="about-title">Built for the city. <br>Designed for the long run.</h1>
            <p class="about-body">
                Salsa Store is a premium-first label for people who move through the city with intent.
                We design pieces that feel sharp without shouting – clean lines, monochrome palettes,
                and details you only notice up close.
            </p>
            <p class="about-body">
                From early commute to late-night trains, our silhouettes are made to hold their shape,
                sit comfortably on skin, and layer into any rotation without effort.
            </p>
            <div class="about-icons">
                <div class="about-icon-card">
                    <span class="about-icon-dot"></span>
                    <p>Seasonless drops</p>
                </div>
                <div class="about-icon-card">
                    <span class="about-icon-dot"></span>
                    <p>Considered fabrics</p>
                </div>
                <div class="about-icon-card">
                    <span class="about-icon-dot"></span>
                    <p>Monochrome first</p>
                </div>
            </div>
            <a href="shop.php" class="btn btn-primary">Explore the edit</a>
        </div>
        <div class="about-visual">
            <div class="about-card about-card-main">
                <span class="about-tagline">Salsa Studio</span>
                <p>Where silhouettes are tested in motion, not just on hangers.</p>
            </div>
            <div class="about-card about-card-secondary">
                <span class="about-tagline">Everyday ready</span>
                <p>Pieces that work hard in your wardrobe – not just your camera roll.</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>