<?php

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/order_success.css') ?>">
    <script src="<?= route_url('assets/js/customer/order_success.js') ?>" defer></script>
</head>
<body>
<div class="container">
    <div class="customer-toolbar"><form method="POST" action="<?= route_url('logout') ?>">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
        <button type="submit">Log out</button>
    </form></div>
    <section class="card success">
        <div class="checkmark">&#10003;</div>
        <h1><?= $order['payment_method'] === 'paymongo' && $order['payment_status'] !== 'paid' ? 'Order received — payment pending' : 'Order Placed Successfully!' ?></h1>
        <p class="muted">Thank you for your purchase. Your order has been recorded.</p>
        <div class="order-number">Order Number: <?= e($order['order_number']) ?></div>
        <p class="muted">Order Date: <?= e($createdAt) ?></p>
        <p>Order Status: <span class="status"><?= e(ucfirst($order['order_status'])) ?></span></p>
    </section>
    <section class="card">
        <h2>Order Items</h2>
        <?php if (!$items): ?><p>No order items were found.</p><?php else: ?>
            <div class="table-wrapper"><table><thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>
                <?php foreach ($items as $item): ?><tr>
                    <td><?= e($item['product_name']) ?></td><td>₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                    <td><?= (int) $item['quantity'] ?></td><td>₱<?= number_format((float) $item['subtotal'], 2) ?></td>
                </tr><?php endforeach; ?>
            </tbody></table></div>
            <div class="total">Total: ₱<?= number_format((float) $order['total_amount'], 2) ?></div>
        <?php endif; ?>
    </section>
    <section class="card"><h2>Delivery Information</h2><div class="details">
        <div class="detail"><p><strong>Recipient:</strong></p><p><?= e($order['recipient_name']) ?></p>
            <p><strong>Phone Number:</strong></p><p><?= e($order['phone']) ?></p>
            <p><strong>Shipping Address:</strong></p><p><?= nl2br(e($order['shipping_address'])) ?></p></div>
        <div class="detail"><p><strong>Payment Method:</strong></p><p><?= e($paymentMethod) ?></p>
            <p><strong>Payment Status:</strong></p><p><?= e(ucfirst($order['payment_status'])) ?></p>
            <?php if ($paymentMessage !== ''): ?><p class="payment-result-message"><?= e($paymentMessage) ?></p><?php endif; ?>
            <?php if ($order['payment_method'] === 'paymongo' && $order['payment_status'] !== 'paid' && is_string($retryPaymentUrl)): ?>
                <a class="payment-retry-link" href="<?= e($retryPaymentUrl) ?>">Continue online payment</a>
            <?php endif; ?>
            <p><strong>Order Status:</strong></p><p><?= e(ucfirst($order['order_status'])) ?></p></div>
    </div></section>
    <div class="actions"><a class="btn" href="<?= route_url() ?>">Continue Shopping</a>
        <button class="btn secondary" type="button" data-print-page hidden>Print Confirmation</button></div>
</div>
</body>
</html>
