<?php
require_once __DIR__ . '/config.php';

require_login();

$orderId = $_SESSION['pending_order_id'] ?? null;
$paymentId = $_SESSION['pending_payment_id'] ?? null;

if (!$orderId || !$paymentId) {
    set_flash('error', 'No pending payment found.');
    redirect('cart.php');
}

$db = get_db_connection();

$stmt = $db->prepare("SELECT o.*, p.status AS payment_status, p.amount
                      FROM orders o
                      JOIN payments p ON p.order_id = o.id
                      WHERE o.id = ? AND p.id = ?");
$stmt->bind_param('ii', $orderId, $paymentId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    set_flash('error', 'Order not found.');
    redirect('cart.php');
}

$pageTitle = 'Payment – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="section-header">
        <h1>Complete your order</h1>
        <p>Secure checkout powered by a simulated gateway. Ideal for local testing.</p>
    </div>

    <div class="payment-options">
        <div class="payment-card">
            <h2>Simulated Payment</h2>
            <p>Order #<?php echo (int)$data['id']; ?> — Total: $<?php echo number_format($data['amount'], 2); ?></p>
            <p>Click “Pay Now” to simulate a successful payment and mark this order as paid.</p>
            <form method="post" action="payment_process.php">
                <?php echo csrf_input_field(); ?>
                <button class="btn btn-primary" type="submit">Pay Now</button>
                <a href="payment_cancel.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>

        <div class="payment-card payment-card-alt">
            <h3>Stripe Test (Optional)</h3>
            <p>
                To wire this into Stripe Checkout, install the Stripe PHP SDK,
                drop your test keys into <code>config.php</code>, and replace this simulated flow
                with Checkout Session creation + webhooks.
            </p>
            <p>
                Current placeholder keys:
                <br>Publishable: <code>pk_test_XXXXXXXXXXXXXXXXXXXXXXXX</code>
                <br>Secret: <code>sk_test_XXXXXXXXXXXXXXXXXXXXXXXX</code>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>