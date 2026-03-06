<?php
require_once __DIR__ . '/../config.php';
require_admin();

$db = get_db_connection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$cats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$name = '';
$description = '';
$price = '';
$stock = '';
$category_id = 0;
$is_active = 1;
$image = '';

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        set_flash('error', 'Product not found.');
        redirect('admin/products.php');
    }

    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $stock = $product['stock'];
    $category_id = $product['category_id'];
    $is_active = $product['is_active'];
    $image = $product['image'];
}

$errors = [];

if (is_post()) {
    if (!verify_csrf_token()) {
        $errors['general'] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $required = [
            'name' => 'Name is required.',
            'price' => 'Price is required.',
            'stock' => 'Stock is required.',
        ];
        $errors = array_merge($errors, validate_required($required, $_POST));

        if ($price < 0) {
            $errors['price'] = 'Price must be positive.';
        }
        if ($stock < 0) {
            $errors['stock'] = 'Stock must be positive.';
        }

        $newImage = $image;
        if (empty($errors)) {
            try {
                $newImage = handle_image_upload('image', $image);
            } catch (RuntimeException $e) {
                $errors['image'] = $e->getMessage();
            }
        }

        if (empty($errors)) {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE products
                                      SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, is_active = ?, image = ?
                                      WHERE id = ?");
                $stmt->bind_param('ssdiissi', $name, $description, $price, $stock, $category_id, $is_active, $newImage, $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Product updated.');
            } else {
                $stmt = $db->prepare("INSERT INTO products
                    (name, description, price, stock, category_id, is_active, image, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('ssdiiss', $name, $description, $price, $stock, $category_id, $is_active, $newImage);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Product created.');
            }
            redirect('admin/products.php');
        }
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Add') . ' Product – Salsa Store Admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="section admin-section" data-reveal>
    <div class="section-header">
        <h1><?php echo $isEdit ? 'Edit' : 'Add'; ?> product</h1>
        <p>Fine-tune every detail before it hits the grid.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form" novalidate>
        <?php echo csrf_input_field(); ?>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" required>
            <?php if (!empty($errors['name'])): ?><div class="error"><?php echo e($errors['name']); ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo e($description); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo e($price); ?>" required>
                <?php if (!empty($errors['price'])): ?><div class="error"><?php echo e($errors['price']); ?></div><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?php echo e($stock); ?>" required>
                <?php if (!empty($errors['stock'])): ?><div class="error"><?php echo e($errors['stock']); ?></div><?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="0">Uncategorized</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php if ($category_id == $cat['id']) echo 'selected'; ?>>
                        <?php echo e($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" <?php if ($is_active) echo 'checked'; ?>>
                Active
            </label>
        </div>

        <div class="form-group">
            <label for="image">Image (jpg, png, gif)</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
            <?php if ($image): ?>
                <p>Current: <img src="../uploads/<?php echo e($image); ?>" alt="" style="max-height:60px;"></p>
            <?php endif; ?>
            <?php if (!empty($errors['image'])): ?><div class="error"><?php echo e($errors['image']); ?></div><?php endif; ?>
        </div>

        <button class="btn btn-primary" type="submit"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
        <a href="products.php" class="btn btn-secondary">Cancel</a>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>