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
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= route_url('assets/css/store-theme.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/seller-console.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/orders.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
<?php $sellerActive = 'orders'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h2>Order Details</h2><a href="<?= route_url('seller/orders') ?>" class="btn btn-secondary">Back to Orders</a></div>
    <div class="card shadow-sm mb-4"><div class="card-header"><strong>Order Information</strong></div><div class="card-body">
        <p><strong>Order Number:</strong> <?= e($order['order_number']) ?></p>
        <p><strong>Order Date:</strong> <?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></p>
        <p><strong>Order Status:</strong> <?= e(ucfirst($order['order_status'])) ?></p>
        <p><strong>Payment Method:</strong> <?= e(ucfirst($order['payment_method'])) ?></p>
        <p><strong>Payment Status:</strong> <?= e(ucfirst($order['payment_status'])) ?></p>
    </div></div>
    <div class="card shadow-sm mb-4"><div class="card-header"><strong>Customer and Shipping Information</strong></div><div class="card-body">
        <p><strong>Customer Account Name:</strong> <?= e($order['customer_name'] ?? 'N/A') ?></p>
        <p><strong>Recipient Name:</strong> <?= e($order['recipient_name']) ?></p>
        <p><strong>Email:</strong> <?= e($order['customer_email'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= e($order['phone']) ?></p>
        <p><strong>Shipping Address:</strong><br><?= nl2br(e($order['shipping_address'])) ?></p>
    </div></div>
    <div class="card shadow-sm"><div class="card-header"><strong>Ordered Products</strong></div><div class="card-body">
        <?php if (!$items): ?><p>No products were found for this order.</p><?php else: ?>
            <div class="table-responsive"><table class="table table-bordered align-middle">
                <thead class="table-dark"><tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                <tbody><?php foreach ($items as $item): ?><tr>
                    <td><?= e($item['product_name']) ?></td><td><?= (int) $item['quantity'] ?></td>
                    <td>₱<?= number_format((float) $item['unit_price'], 2) ?></td>
                    <td>₱<?= number_format((float) $item['subtotal'], 2) ?></td>
                </tr><?php endforeach; ?></tbody>
                <tfoot><tr><th colspan="3" class="text-end">Order Total</th><th>₱<?= number_format((float) $order['total_amount'], 2) ?></th></tr></tfoot>
            </table></div>
        <?php endif; ?>
    </div></div>
</div>
</body>
</html>
