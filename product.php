<?php
require_once __DIR__ . '/config.php';

$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    redirect('shop.php');
}

$stmt = $db->prepare("SELECT p.*, c.name AS category_name
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.id = ? AND p.is_active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    redirect('shop.php');
}

$pageTitle = e($product['name']) . ' – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section product-detail-wrapper" data-reveal>
    <article class="product-detail">
        <div class="product-detail-image js-product-image">
            <?php if (!empty($product['image'])): ?>
                <img src="uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>">
            <?php else: ?>
                <div class="placeholder-image">Salsa Studio</div>
            <?php endif; ?>
        </div>
        <div class="product-detail-info">
            <p class="product-detail-kicker"><?php echo e($product['category_name'] ?? 'Salsa Essentials'); ?></p>
            <h1><?php echo e($product['name']); ?></h1>
            <p class="product-detail-price">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="product-detail-description"><?php echo nl2br(e($product['description'])); ?></p>
            <p class="product-detail-stock">
                Stock: <?php echo (int)$product['stock'] > 0 ? (int)$product['stock'] : 'Out of stock'; ?>
            </p>
            <?php if ((int)$product['stock'] > 0): ?>
                <div class="product-detail-actions">
                    <label class="qty-label">
                        Qty
                        <input type="number" id="product-qty" value="1" min="1" max="<?php echo (int)$product['stock']; ?>">
                    </label>
                    <button class="btn btn-primary add-to-cart-btn"
                            data-product-id="<?php echo (int)$product['id']; ?>"
                            data-qty-input-id="product-qty">
                        Add to Cart
                    </button>
                </div>
            <?php else: ?>
                <p class="out-of-stock">Currently unavailable.</p>
            <?php endif; ?>
        </div>
    </article>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>