<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

if (is_post() && verify_csrf_token()) {
    $orderId = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $validStatuses = ['Pending', 'Paid', 'Shipped'];
    if ($orderId > 0 && in_array($status, $validStatuses, true)) {
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $orderId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Order status updated.');
    } else {
        set_flash('error', 'Invalid status or order.');
    }
    redirect('admin/orders.php');
}

$res = $db->query("SELECT o.*, u.name AS user_name, u.email
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   ORDER BY o.created_at DESC");
$orders = $res->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Orders – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section" data-reveal>
    <div class="section-header">
        <h1>Orders</h1>
        <p>Track and update orders flowing through Salsa Store.</p>
    </div>

    <table class="admin-table">
        <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?php echo (int)$o['id']; ?></td>
                <td><?php echo e($o['user_name']); ?> (<?php echo e($o['email']); ?>)</td>
                <td class="price-cell">$<?php echo number_format($o['total_amount'], 2); ?></td>
                <td><?php echo e($o['status']); ?></td>
                <td><?php echo e($o['created_at']); ?></td>
                <td>
                    <a href="order_view.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm btn-outline">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>