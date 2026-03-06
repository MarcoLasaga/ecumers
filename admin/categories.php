<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

if (is_post() && ($_POST['action'] ?? '') === 'delete' && verify_csrf_token()) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("SELECT COUNT(*) AS c FROM products WHERE category_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt->close();

        if ($count > 0) {
            set_flash('error', 'Cannot delete category with products assigned.');
        } else {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Category deleted.');
        }
    } else {
        set_flash('error', 'Invalid category.');
    }
    redirect('admin/categories.php');
}

$res = $db->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
$categories = $res->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Categories – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section" data-reveal>
    <div class="section-header">
        <h1>Categories</h1>
        <p>Organize products into clean categories.</p>
    </div>

    <a href="category_form.php" class="btn btn-primary">Add Category</a>

    <table class="admin-table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Slug</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo (int)$cat['id']; ?></td>
                <td><?php echo e($cat['name']); ?></td>
                <td><?php echo e($cat['slug']); ?></td>
                <td>
                    <a href="category_form.php?id=<?php echo (int)$cat['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                    <form method="post" action="categories.php" class="inline-form"
                          onsubmit="return confirm('Delete this category?');">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>