<?php
require_once __DIR__ . '/config.php';

$db = get_db_connection();

$stmt = $db->prepare("SELECT p.*, c.name AS category_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.is_active = 1
                      ORDER BY p.created_at DESC
                      LIMIT 8");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Salsa Store – Elevate Your Style';
include __DIR__ . '/includes/header.php';
?>

<section class="hero" data-reveal>
    <div class="hero-inner">
        <div class="hero-copy">
            <p class="hero-kicker">SALSA STORE</p>
            <h1 class="hero-title">Elevate Your Style.</h1>
            <p class="hero-subtitle">
                Minimal silhouettes, bold details. Curated pieces that move with you – from street to spotlight.
            </p>
            <div class="hero-actions">
                <a href="shop.php" class="btn btn-primary hero-cta">Shop Now</a>
                <a href="about.php" class="hero-link">Discover the story</a>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-card hero-card-primary">
                <span class="hero-tagline">New Drop</span>
                <p>Monochrome essentials tailored for everyday motion.</p>
            </div>
            <div class="hero-card hero-card-secondary">
                <span class="hero-tagline">Made to move</span>
                <p>Premium fabrics, clean cuts, seasonless attitude.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-featured" data-reveal>
    <div class="section-header">
        <h2>Featured Pieces</h2>
        <p>Handpicked staples to anchor your rotation.</p>
    </div>

    <div class="product-grid product-grid-main">
        <?php foreach ($products as $product): ?>
            <article class="product-card">
                <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="product-card-media">
                    <div class="product-image js-product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image">Salsa Studio</div>
                        <?php endif; ?>
                    </div>
                </a>
                <div class="product-card-body">
                    <div class="product-meta-top">
                        <p class="product-category">
                            <?php echo e($product['category_name'] ?? 'Salsa Essentials'); ?>
                        </p>
                    </div>
                    <h3 class="product-title">
                        <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                            <?php echo e($product['name']); ?>
                        </a>
                    </h3>
                    <div class="product-meta-bottom">
                        <p class="product-price">
                            $<?php echo number_format($product['price'], 2); ?>
                        </p>
                        <button class="btn btn-outline add-to-cart-btn"
                                data-product-id="<?php echo (int)$product['id']; ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <p>No products available yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>