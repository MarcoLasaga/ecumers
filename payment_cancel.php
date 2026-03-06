<?php
require_once __DIR__ . '/config.php';

require_login();

$orderId = $_SESSION['pending_order_id'] ?? null;
$paymentId = $_SESSION['pending_payment_id'] ?? null;

if ($orderId && $paymentId) {
    $db = get_db_connection();
    $status = 'Canceled';
    $stmt = $db->prepare("UPDATE payments SET status = ? WHERE id = ? AND order_id = ?");
    $stmt->bind_param('sii', $status, $paymentId, $orderId);
    $stmt->execute();
    $stmt->close();

    $orderStatus = 'Pending';
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $orderStatus, $orderId);
    $stmt->execute();
    $stmt->close();
}

unset($_SESSION['pending_order_id'], $_SESSION['pending_payment_id']);

set_flash('error', 'Payment was cancelled.');
redirect('cart.php');