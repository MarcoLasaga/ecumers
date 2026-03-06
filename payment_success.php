<?php
require_once __DIR__ . '/config.php';

require_login();

$orderId = $_SESSION['last_order_id'] ?? null;

$pageTitle = 'Payment Successful – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="section-header">
        <h1>Order confirmed</h1>
        <p>Thank you for moving with Salsa Store.</p>
    </div>

    <?php if ($orderId): ?>
        <p>Your order #<?php echo (int)$orderId; ?> has been placed and paid.</p>
        <p>You can view your orders on the <a href="order_history.php">Order History</a> page.</p>
    <?php else: ?>
        <p>Your payment has been processed.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>