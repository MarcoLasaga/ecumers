<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    redirect('orders.php');
}

$stmt = $db->prepare("SELECT o.*, u.name AS user_name, u.email
                      FROM orders o
                      JOIN users u ON o.user_id = u.id
                      WHERE o.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirect('orders.php');
}

$stmt = $db->prepare("SELECT oi.*, p.name
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Order #' . (int)$order['id'] . ' – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section">
    <div class="section-header">
        <h1>Order #<?php echo (int)$order['id']; ?></h1>
        <p>Detail view for this customer order.</p>
    </div>

    <p><strong>User:</strong> <?php echo e($order['user_name']); ?> (<?php echo e($order['email']); ?>)</p>
    <p><strong>Status:</strong> <?php echo e($order['status']); ?></p>
    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>

    <h2>Shipping</h2>
    <p>
        <?php echo nl2br(e($order['shipping_name'] . "\n" . $order['shipping_address'] . "\n" . $order['shipping_city'] . ' ' . $order['shipping_postal_code'] . "\n" . $order['shipping_country'])); ?>
    </p>

    <h2>Items</h2>
    <table class="admin-table">
        <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo e($item['name']); ?></td>
                <td><?php echo (int)$item['quantity']; ?></td>
                <td class="price-cell">$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="price-cell">$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Update Status</h2>
    <form method="post" action="orders.php" class="inline-form">
        <?php echo csrf_input_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
        <select name="status">
            <?php foreach (['Pending', 'Paid', 'Shipped'] as $status): ?>
                <option value="<?php echo e($status); ?>" <?php if ($order['status'] === $status) echo 'selected'; ?>>
                    <?php echo e($status); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit">Save</button>
    </form>

    <p><a href="orders.php" class="btn btn-secondary">Back to Orders</a></p>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>