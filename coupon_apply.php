<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$code = strtoupper(trim($_POST['code'] ?? ''));
if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Enter a coupon code.']);
    exit;
}

$cartTotals = calculate_cart_totals();
$subtotal = $cartTotals['total'];

$db = get_db_connection();
$stmt = $db->prepare("SELECT id, code, type, value, expiry_date, active
                      FROM coupons
                      WHERE UPPER(code) = ? AND active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                      LIMIT 1");
$stmt->bind_param('s', $code);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$coupon) {
    unset($_SESSION['coupon']);
    echo json_encode([
        'success' => false,
        'message' => 'Coupon not found or expired.',
        'grand_total' => $subtotal
    ]);
    exit;
}

$discount = 0.0;
if ($coupon['type'] === 'percentage') {
    $discount = $subtotal * ((float)$coupon['value'] / 100.0);
} else {
    $discount = (float)$coupon['value'];
}
if ($discount > $subtotal) {
    $discount = $subtotal;
}
$grandTotal = max(0, $subtotal - $discount);

$_SESSION['coupon'] = [
    'id' => (int)$coupon['id'],
    'code' => $coupon['code'],
    'type' => $coupon['type'],
    'value' => (float)$coupon['value'],
    'discount_amount' => $discount,
];

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied: ' . $coupon['code'],
    'discount_amount' => $discount,
    'grand_total' => $grandTotal
]);