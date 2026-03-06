<?php
require_once __DIR__ . '/config.php';

require_login();

$db = get_db_connection();
$userId = current_user()['id'];

$stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Order History – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="section-header">
        <h1>Order history</h1>
        <p>Every drop you’ve checked out from Salsa Store.</p>
    </div>

    <?php if (empty($orders)): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <table class="orders-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo (int)$order['id']; ?></td>
                    <td><?php echo e($order['created_at']); ?></td>
                    <td><?php echo e($order['status']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>