<?php
require_once __DIR__ . '/config.php';

if (is_post()) {
    if (!verify_csrf_token()) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
        exit;
    }

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, stock FROM products WHERE id = ? AND is_active = 1");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    switch ($action) {
        case 'add':
            if ($quantity < 1) $quantity = 1;
            if ($quantity > (int)$product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock.']);
                exit;
            }
            add_to_cart($productId, $quantity);
            echo json_encode([
                'success' => true,
                'message' => 'Added to cart.',
                'cartCount' => cart_item_count()
            ]);
            break;
        case 'update':
            if ($quantity < 0) $quantity = 0;
            if ($quantity > (int)$product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock.']);
                exit;
            }
            update_cart_item($productId, $quantity);
            $totals = calculate_cart_totals();
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated.',
                'cartCount' => cart_item_count(),
                'total' => $totals['total']
            ]);
            break;
        case 'remove':
            remove_from_cart($productId);
            $totals = calculate_cart_totals();
            echo json_encode([
                'success' => true,
                'message' => 'Item removed.',
                'cartCount' => cart_item_count(),
                'total' => $totals['total']
            ]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    exit;
}

$totals = calculate_cart_totals();
$pageTitle = 'Cart – Salsa Store';

include __DIR__ . '/includes/header.php';
?>

<section class="section" data-reveal>
    <div class="section-header">
        <h1>Your Cart</h1>
        <p>Review your picks before you check out.</p>
    </div>

    <?php if (empty($totals['items'])): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th width="120">Qty</th>
                <th>Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($totals['items'] as $item): ?>
                <?php $p = $item['product']; ?>
                <tr data-product-id="<?php echo (int)$p['id']; ?>">
                    <td><?php echo e($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price'], 2); ?></td>
                    <td>
                        <input type="number" class="cart-qty-input" value="<?php echo (int)$item['quantity']; ?>"
                               min="0" max="<?php echo (int)$p['stock']; ?>">
                    </td>
                    <td class="line-total">$<?php echo number_format($item['line_total'], 2); ?></td>
                    <td>
                        <button class="btn btn-sm btn-danger cart-remove-btn">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <p><strong>Cart Total:</strong> $<span id="cart-total"><?php echo number_format($totals['total'], 2); ?></span></p>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>	