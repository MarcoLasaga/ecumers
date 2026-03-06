<?php

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path)
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function validate_required(array $fields, array $data): array
{
    $errors = [];
    foreach ($fields as $field => $message) {
        if (empty(trim($data[$field] ?? ''))) {
            $errors[$field] = $message;
        }
    }
    return $errors;
}

function get_flash(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function is_admin(): bool
{
    return !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        set_flash('error', 'You must be logged in.');
        redirect('login.php');
    }
}

function require_admin()
{
    if (!is_admin()) {
        set_flash('error', 'You are not authorized.');
        redirect('index.php');
    }
}

function get_cart(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    return $_SESSION['cart'];
}

function add_to_cart(int $product_id, int $quantity = 1): void
{
    $cart = get_cart();
    if (isset($cart[$product_id])) {
        $cart[$product_id] += $quantity;
    } else {
        $cart[$product_id] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function update_cart_item(int $product_id, int $quantity): void
{
    $cart = get_cart();
    if ($quantity <= 0) {
        unset($cart[$product_id]);
    } else {
        $cart[$product_id] = $quantity;
    }
    $_SESSION['cart'] = $cart;
}

function remove_from_cart(int $product_id): void
{
    $cart = get_cart();
    unset($cart[$product_id]);
    $_SESSION['cart'] = $cart;
}

function clear_cart(): void
{
    $_SESSION['cart'] = [];
}

function cart_item_count(): int
{
    $cart = get_cart();
    return array_sum($cart);
}

function get_products_by_ids(array $ids): array
{
    if (empty($ids)) {
        return [];
    }
    $db = get_db_connection();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
    $stmt->close();
    return $products;
}

function calculate_cart_totals(): array
{
    $cart = get_cart();
    $ids = array_keys($cart);
    $products = get_products_by_ids($ids);
    $total = 0;
    $items = [];
    foreach ($cart as $product_id => $qty) {
        if (!isset($products[$product_id])) {
            continue;
        }
        $p = $products[$product_id];
        $line_total = $p['price'] * $qty;
        $total += $line_total;
        $items[] = [
            'product' => $p,
            'quantity' => $qty,
            'line_total' => $line_total,
        ];
    }
    return [
        'items' => $items,
        'total' => $total,
    ];
}

function handle_image_upload(string $file_field, ?string $existing_file = null): ?string
{
    if (empty($_FILES[$file_field]) || $_FILES[$file_field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existing_file;
    }

    $file = $_FILES[$file_field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error.');
    }

    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new RuntimeException('File too large.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_types = unserialize(ALLOWED_IMAGE_TYPES);
    if (!in_array($mime, $allowed_types, true)) {
        throw new RuntimeException('Invalid file type.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = unserialize(ALLOWED_IMAGE_EXTENSIONS);
    if (!in_array($ext, $allowed_ext, true)) {
        throw new RuntimeException('Invalid file extension.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = uniqid('prod_', true) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    if ($existing_file && file_exists(UPLOAD_DIR . $existing_file)) {
        @unlink(UPLOAD_DIR . $existing_file);
    }

    return $filename;
}