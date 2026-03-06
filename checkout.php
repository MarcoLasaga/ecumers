<?php
require_once __DIR__ . '/config.php';

require_login();

$cartTotals = calculate_cart_totals();
if (empty($cartTotals['items'])) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$errors = [];
$name = '';
$address = '';
$city = '';
$postal_code = '';
$country = '';
$formatted_address = '';
$lat = '';
$lng = '';

$coupon = $_SESSION['coupon'] ?? null;
$subtotal = $cartTotals['total'];
$discountAmount = $coupon['discount_amount'] ?? 0.0;
$grandTotal = max(0, $subtotal - $discountAmount);

if (is_post()) {
    if (!verify_csrf_token()) {
        $errors['csrf'] = 'Invalid request.';
    } else {
        $required = [
            'name' => 'Name is required.',
            'address' => 'Address is required.',
            'city' => 'City is required.',
            'postal_code' => 'Postal code is required.',
            'country' => 'Country is required.',
        ];
        $errors = validate_required($required, $_POST);

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $formatted_address = trim($_POST['formatted_address'] ?? '');
        $lat = trim($_POST['address_lat'] ?? '');
        $lng = trim($_POST['address_lng'] ?? '');

        if (empty($errors)) {
            $db = get_db_connection();
            $db->begin_transaction();

            try {
                $userId = current_user()['id'];
                $status = 'Pending';
                $couponCode = $coupon['code'] ?? null;
                $discountAmount = $coupon['discount_amount'] ?? 0.0;
                $grandTotal = max(0, $subtotal - $discountAmount);

                $stmt = $db->prepare("INSERT INTO orders
                        (user_id, total_amount, status,
                         shipping_name, shipping_address, shipping_city, shipping_postal_code, shipping_country,
                         shipping_formatted, shipping_lat, shipping_lng,
                         coupon_code, discount_amount,
                         created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param(
                    'idsssssssssd',
                    $userId,
                    $grandTotal,
                    $status,
                    $name,
                    $address,
                    $city,
                    $postal_code,
                    $country,
                    $formatted_address,
                    $lat,
                    $lng,
                    $couponCode,
                    $discountAmount
                );
                $stmt->execute();
                $orderId = $stmt->insert_id;
                $stmt->close();

                $stmtItem = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, created_at)
                                          VALUES (?, ?, ?, ?, NOW())");
                $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

                foreach ($cartTotals['items'] as $item) {
                    $p = $item['product'];
                    $qty = $item['quantity'];
                    $price = $p['price'];

                    $stmtStock->bind_param('iii', $qty, $p['id'], $qty);
                    $stmtStock->execute();
                    if ($stmtStock->affected_rows === 0) {
                        throw new RuntimeException('Insufficient stock for product: ' . $p['name']);
                    }

                    $stmtItem->bind_param('iiid', $orderId, $p['id'], $qty, $price);
                    $stmtItem->execute();
                }

                $stmtItem->close();
                $stmtStock->close();

                $paymentStatus = 'Pending';
                $paymentMethod = 'SimulatedGateway';
                $stmtPay = $db->prepare("INSERT INTO payments (order_id, amount, status, method, transaction_id, created_at)
                                         VALUES (?, ?, ?, ?, NULL, NOW())");
                $stmtPay->bind_param('idss', $orderId, $grandTotal, $paymentStatus, $paymentMethod);
                $stmtPay->execute();
                $paymentId = $stmtPay->insert_id;
                $stmtPay->close();

                $db->commit();

                $_SESSION['pending_order_id'] = $orderId;
                $_SESSION['pending_payment_id'] = $paymentId;
                unset($_SESSION['coupon']);

                redirect('payment.php');
            } catch (Throwable $e) {
                $db->rollback();
                $errors['general'] = 'An error occurred while placing your order. Please try again.';
            }
        }
    }
}

$pageTitle = 'Checkout – Salsa Store';
include __DIR__ . '/includes/header.php';
?>

<section class="section" data-reveal>
    <div class="section-header">
        <h1>Checkout</h1>
        <p>Enter your shipping details to complete your order.</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="post" class="checkout-form" id="checkout-form" novalidate>
            <?php echo csrf_input_field(); ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo e($name ?: (current_user()['name'] ?? '')); ?>" required>
                <?php if (!empty($errors['name'])): ?><div class="error"><?php echo e($errors['name']); ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="address_autocomplete">Address</label>
                <input type="text" id="address_autocomplete" name="address_autocomplete"
                       placeholder="Start typing your address"
                       value="<?php echo e($formatted_address ?: $address); ?>">
            </div>

            <div class="form-group">
                <label for="address">Street</label>
                <input type="text" id="address" name="address" value="<?php echo e($address); ?>" required>
                <?php if (!empty($errors['address'])): ?><div class="error"><?php echo e($errors['address']); ?></div><?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo e($city); ?>" required>
                    <?php if (!empty($errors['city'])): ?><div class="error"><?php echo e($errors['city']); ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo e($postal_code); ?>" required>
                    <?php if (!empty($errors['postal_code'])): ?><div class="error"><?php echo e($errors['postal_code']); ?></div><?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" value="<?php echo e($country); ?>" required>
                <?php if (!empty($errors['country'])): ?><div class="error"><?php echo e($errors['country']); ?></div><?php endif; ?>
            </div>

            <input type="hidden" id="formatted_address" name="formatted_address" value="<?php echo e($formatted_address); ?>">
            <input type="hidden" id="address_lat" name="address_lat" value="<?php echo e($lat); ?>">
            <input type="hidden" id="address_lng" name="address_lng" value="<?php echo e($lng); ?>">

            <button class="btn btn-primary" type="submit">Proceed to Payment</button>
        </form>

        <div class="checkout-summary">
            <h2>Order Summary</h2>
            <ul>
                <?php foreach ($cartTotals['items'] as $item): ?>
                    <li>
                        <span><?php echo e($item['product']['name']); ?> x <?php echo (int)$item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['line_total'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="checkout-totals">
                <p>
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </p>

                <div class="form-group checkout-coupon">
                    <label for="coupon_code">Coupon</label>
                    <div class="checkout-coupon-row">
                        <input type="text" id="coupon_code" placeholder="Enter code"
                               value="<?php echo isset($coupon['code']) ? e($coupon['code']) : ''; ?>">
                        <button type="button" class="btn btn-outline" id="apply-coupon-btn">Apply</button>
                    </div>
                    <div class="checkout-coupon-message" id="coupon-message"></div>
                </div>

                <p>
                    <span>Discount</span>
                    <span id="discount-amount">
                        -$<?php echo number_format($discountAmount, 2); ?>
                    </span>
                </p>
                <p class="checkout-total">
                    <span>Total</span>
                    <span id="grand-total">$<?php echo number_format($grandTotal, 2); ?></span>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>