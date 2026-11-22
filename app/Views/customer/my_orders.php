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
    <title>My Orders</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/my_orders.css') ?>">
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
        <h1>My Orders</h1>
        <p class="muted">View your order history and current order status.</p>
        <a class="btn" href="<?= route_url() ?>">Continue Shopping</a>
    </div>
    <div class="card">
        <?php if (!$orders): ?>
            <p>You have not placed any orders yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Order Number</th><th>Date</th><th>Total</th>
                        <th>Payment</th><th>Status</th><th>Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= e($order['order_number']) ?></td>
                            <td><?= e(date('M d, Y', strtotime($order['created_at']))) ?></td>
                            <td>₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                            <td><?= e($order['payment_method'] === 'cod'
                                ? 'Cash on Delivery'
                                : ucfirst($order['payment_method'])) ?></td>
                            <td><?= e(ucfirst($order['order_status'])) ?></td>
                            <td><a href="<?= route_url('orders/details?id=' . (int) $order['id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
