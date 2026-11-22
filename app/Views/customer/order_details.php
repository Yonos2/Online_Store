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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Details</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/order_details.css') ?>">
</head>
<body>
<div class="container">
    <div class="customer-toolbar">
        <form method="POST" action="<?= route_url('logout') ?>">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <button type="submit">Log out</button>
        </form>
    </div>
    <div class="card">
        <h1>Order Details</h1>
        <p><strong>Order Number:</strong> <?= e($order['order_number']) ?></p>
        <p><strong>Date:</strong> <?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></p>
        <p><strong>Order Status:</strong> <?= e(ucfirst($order['order_status'])) ?></p>
        <p><strong>Payment Status:</strong> <?= e(ucfirst($order['payment_status'])) ?></p>
    </div>
    <div class="card">
        <h2>Items</h2>
        <div class="wrap">
            <table>
                <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?></td>
                        <td>₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td>₱<?= number_format((float) $item['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <h3>Total: ₱<?= number_format((float) $order['total_amount'], 2) ?></h3>
    </div>
    <div class="card">
        <h2>Delivery Information</h2>
        <p><strong>Recipient:</strong> <?= e($order['recipient_name']) ?></p>
        <p><strong>Phone:</strong> <?= e($order['phone']) ?></p>
        <p><strong>Address:</strong><br><?= nl2br(e($order['shipping_address'])) ?></p>
        <p><strong>Payment Method:</strong>
            <?= e($order['payment_method'] === 'cod' ? 'Cash on Delivery' : ucfirst($order['payment_method'])) ?>
        </p>
        <div class="order-navigation">
            <a class="btn btn-secondary" href="<?= route_url() ?>">Back to Home</a>
            <a class="btn" href="<?= route_url('orders') ?>">Back to My Orders</a>
        </div>
    </div>
</div>
</body>
</html>
