<?php

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Order Management</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller-console.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/confirm-actions.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller_orders.css') ?>">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
</head>
<body>
<?php $sellerActive = 'orders'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container">
    <div class="card"><h1>Order Management</h1><p>Review customer orders and update their order status.</p></div>
    <div class="card">
        <?php if ($message !== ''): ?><p class="success"><?= e($message) ?></p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
        <?php if (!$orders): ?><p>No customer orders found.</p><?php else: ?>
            <div class="table-wrap"><table>
                <thead><tr><th>Order</th><th>Customer</th><th>Delivery</th><th>Total</th><th>Payment</th><th>Current Status</th><th>Update Status</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= e($order['order_number']) ?></strong><br><small><?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></small></td>
                        <td><?= e($order['recipient_name']) ?><br><?= e($order['phone']) ?></td>
                        <td class="address"><?= nl2br(e($order['shipping_address'])) ?></td>
                        <td>₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                        <td><?= e($order['payment_method'] === 'cod' ? 'Cash on Delivery' : ucfirst($order['payment_method'])) ?><br><small>Payment: <?= e($order['payment_status']) ?></small></td>
                        <td><?= e(ucfirst($order['order_status'])) ?></td>
                        <td><form method="POST" data-confirm-message="Update this customer's order status?" data-confirm-title="Confirm order update" data-confirm-action="Update status">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <select name="order_status" required><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $status === $order['order_status'] ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select>
                            <button type="submit">Update</button>
                        </form></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
