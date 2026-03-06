<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

$res = $db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $res->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<h1>Users</h1>

<table class="admin-table">
    <thead>
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Joined</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?php echo (int)$u['id']; ?></td>
            <td><?php echo e($u['name']); ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><?php echo e($u['role']); ?></td>
            <td><?php echo e($u['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/footer.php'; ?>