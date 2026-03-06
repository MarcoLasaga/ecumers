<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

if (is_post() && ($_POST['action'] ?? '') === 'delete' && verify_csrf_token()) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        if (!empty($row['image']) && file_exists(UPLOAD_DIR . $row['image'])) {
            @unlink(UPLOAD_DIR . $row['image']);
        }

        set_flash('success', 'Product deleted.');
    } else {
        set_flash('error', 'Invalid product.');
    }
    redirect('products.php');
}

$res = $db->query("SELECT p.*, c.name AS category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   ORDER BY p.created_at DESC");
$products = $res->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Products – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section">
    <div class="section-header">
        <h1>Products</h1>
        <p>Manage Salsa Store’s product catalog.</p>
    </div>

    <a href="product_form.php" class="btn btn-primary">Add Product</a>

    <table class="admin-table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Active</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo (int)$p['id']; ?></td>
                <td><?php echo e($p['name']); ?></td>
                <td><?php echo e($p['category_name'] ?? 'Uncategorized'); ?></td>
                <td class="price-cell">$<?php echo number_format($p['price'], 2); ?></td>
                <td><?php echo (int)$p['stock']; ?></td>
                <td><?php echo $p['is_active'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <a href="product_form.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                    <form method="post" action="products.php" class="inline-form" onsubmit="return confirm('Delete this product?');">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>