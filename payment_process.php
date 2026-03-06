<?php
require_once __DIR__ . '/config.php';

require_login();

if (!is_post() || !verify_csrf_token()) {
    set_flash('error', 'Invalid request.');
    redirect('cart.php');
}

$orderId = $_SESSION['pending_order_id'] ?? null;
$paymentId = $_SESSION['pending_payment_id'] ?? null;

if (!$orderId || !$paymentId) {
    set_flash('error', 'No pending payment found.');
    redirect('cart.php');
}

$db = get_db_connection();
$db->begin_transaction();

try {
    $statusPaid = 'Paid';
    $transactionId = 'SIM-' . uniqid();
    $stmt = $db->prepare("UPDATE payments SET status = ?, transaction_id = ? WHERE id = ? AND order_id = ?");
    $stmt->bind_param('ssii', $statusPaid, $transactionId, $paymentId, $orderId);
    $stmt->execute();
    $stmt->close();

    $orderStatus = 'Paid';
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $orderStatus, $orderId);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    clear_cart();
    unset($_SESSION['pending_order_id'], $_SESSION['pending_payment_id']);

    $_SESSION['last_order_id'] = $orderId;
    redirect('payment_success.php');
} catch (Throwable $e) {
    $db->rollback();
    set_flash('error', 'Payment failed. Please try again.');
    redirect('payment.php');
}