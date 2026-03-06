<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$name = '';
$slug = '';
$errors = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$cat) {
        set_flash('error', 'Category not found.');
        redirect('admin/categories.php');
    }

    $name = $cat['name'];
    $slug = $cat['slug'];
}

if (is_post()) {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($slug === '') {
            $errors['slug'] = 'Slug is required.';
        }

        if (empty($errors)) {
            if ($isEdit) {
                $stmt = $db->prepare("SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id <> ? LIMIT 1");
                $stmt->bind_param('ssi', $name, $slug, $id);
            } else {
                $stmt = $db->prepare("SELECT id FROM categories WHERE name = ? OR slug = ? LIMIT 1");
                $stmt->bind_param('ss', $name, $slug);
            }
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($exists) {
                $errors['general'] = 'Category name or slug already exists.';
            }
        }

        if (empty($errors)) {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
                $stmt->bind_param('ssi', $name, $slug, $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Category updated.');
            } else {
                $stmt = $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $slug);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Category created.');
            }
            redirect('admin/categories.php');
        }
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Add') . ' Category – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section" data-reveal>
    <div class="section-header">
        <h1><?php echo $isEdit ? 'Edit' : 'Add'; ?> Category</h1>
        <p>Keep Salsa Store’s catalog structured and clean.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error js-flash-inline" data-flash-type="error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" class="admin-form" novalidate>
        <?php echo csrf_input_field(); ?>

        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="error"><?php echo e($errors['name']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" value="<?php echo e($slug); ?>" required>
            <?php if (!empty($errors['slug'])): ?><div class="error"><?php echo e($errors['slug']); ?></div><?php endif; ?>
        </div>

        <button class="btn btn-primary" type="submit"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
        <a href="categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>