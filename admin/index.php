<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

$stats = [];

$res = $db->query("SELECT COUNT(*) AS c FROM users");
$stats['users'] = $res->fetch_assoc()['c'] ?? 0;

$res = $db->query("SELECT COUNT(*) AS c FROM products");
$stats['products'] = $res->fetch_assoc()['c'] ?? 0;

$res = $db->query("SELECT COUNT(*) AS c FROM orders");
$stats['orders'] = $res->fetch_assoc()['c'] ?? 0;

$res = $db->query("SELECT SUM(total_amount) AS s FROM orders WHERE status IN ('Paid', 'Shipped')");
$stats['revenue'] = $res->fetch_assoc()['s'] ?? 0.0;

$res = $db->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.name AS user_name
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   ORDER BY o.created_at DESC
                   LIMIT 5");
$latestOrders = $res->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Admin Dashboard – Salsa Store';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section" data-reveal>
    <div class="section-header">
        <h1>Dashboard</h1>
        <p>High-level view of Salsa Store performance.</p>
    </div>

    <div class="admin-grid">
        <div class="admin-card">
            <p class="admin-card-label">Total Revenue</p>
            <p class="admin-card-value highlighted">$<?php echo number_format($stats['revenue'], 2); ?></p>
        </div>
        <div class="admin-card">
            <p class="admin-card-label">Orders</p>
            <p class="admin-card-value"><?php echo (int)$stats['orders']; ?></p>
        </div>
        <div class="admin-card">
            <p class="admin-card-label">Products</p>
            <p class="admin-card-value"><?php echo (int)$stats['products']; ?></p>
        </div>
        <div class="admin-card">
            <p class="admin-card-label">Customers</p>
            <p class="admin-card-value"><?php echo (int)$stats['users']; ?></p>
        </div>
    </div>

    <nav class="admin-nav">
        <a href="products.php" class="btn btn-outline">Manage Products</a>
        <a href="categories.php" class="btn btn-outline">Manage Categories</a>
        <a href="orders.php" class="btn btn-outline">Manage Orders</a>
        <a href="users.php" class="btn btn-outline">View Users</a>
        <a href="../logout.php" class="btn btn-secondary">Logout</a>
    </nav>

    <section class="section admin-subsection">
        <div class="section-header">
            <h2>Recent orders</h2>
            <p>Last five orders coming through Salsa Store.</p>
        </div>
        <?php if (empty($latestOrders)): ?>
            <p>No orders yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latestOrders as $o): ?>
                    <tr>
                        <td><?php echo (int)$o['id']; ?></td>
                        <td><?php echo e($o['user_name']); ?></td>
                        <td>$<?php echo number_format($o['total_amount'], 2); ?></td>
                        <td><?php echo e($o['status']); ?></td>
                        <td><?php echo e($o['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>