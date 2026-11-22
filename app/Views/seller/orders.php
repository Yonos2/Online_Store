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
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= route_url('assets/css/store-theme.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/seller-console.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/seller/orders.css') ?>" rel="stylesheet">
    <link href="<?= route_url('assets/css/confirm-actions.css') ?>" rel="stylesheet">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
    <script src="<?= route_url('assets/js/seller/orders.js') ?>" defer></script>
</head>
<body class="bg-light">
<?php $sellerActive = 'orders'; require __DIR__ . '/partials/navigation.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Orders</h2><a href="<?= route_url('seller/dashboard') ?>" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($newOrders): ?>
        <section class="alert new-checkout-alert"><div><strong><?= count($newOrders) ?> new <?= count($newOrders) === 1 ? 'checkout' : 'checkouts' ?> need review</strong><span>Pending orders stay here until you update their status.</span></div><a href="#orders-table">Review orders</a></section>
    <?php endif; ?>
    <div class="card shadow-sm"><div class="card-body">
        <?php if (!$orders): ?><p class="text-center mb-0">No orders found.</p><?php else: ?>
            <div class="row g-3 mb-3" data-order-filters hidden>
                <div class="col-md-8"><label for="order-search" class="form-label">Search orders</label><input type="search" id="order-search" class="form-control" placeholder="Order number, customer, email..." autocomplete="off" aria-controls="orders-table" data-order-search></div>
                <div class="col-md-4"><label for="order-status-filter" class="form-label">Filter by status</label><select id="order-status-filter" class="form-select" aria-controls="orders-table" data-order-status-filter><option value="">All statuses</option><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>"><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><p class="form-text mb-0" data-order-count aria-live="polite"></p></div>
            </div>
            <div class="table-responsive"><table id="orders-table" class="table table-bordered table-hover align-middle">
                <thead class="table-dark"><tr><th>Order Number</th><th>Customer</th><th>Total</th><th>Payment</th><th>Order Status</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr data-order-row data-order-status="<?= e($order['order_status']) ?>">
                        <td><?= e($order['order_number']) ?><?php if ($order['order_status'] === 'pending'): ?> <span class="new-order-row-badge">New</span><?php endif; ?></td>
                        <td><?= e($order['customer_name'] ?: $order['recipient_name']) ?><br><small class="text-muted"><?= e($order['customer_email'] ?? '') ?></small></td>
                        <td>₱<?= number_format((float) $order['total_amount'], 2) ?></td>
                        <td><?= e(ucfirst($order['payment_method'])) ?><br><small>Payment: <?= e(ucfirst($order['payment_status'])) ?></small></td>
                        <td><form method="POST" action="<?= route_url('seller/orders') ?>" class="d-flex gap-2" data-confirm-message="Update this customer's order status?" data-confirm-title="Confirm order update" data-confirm-action="Update status">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <select name="order_status" class="form-select form-select-sm"><?php foreach ($allowedStatuses as $status): ?><option value="<?= e($status) ?>" <?= $order['order_status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select>
                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        </form></td>
                        <td><?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></td>
                        <td><a href="<?= route_url('seller/orders/details?id=' . (int) $order['id']) ?>" class="btn btn-sm btn-info">View Details</a></td>
                    </tr>
                <?php endforeach; ?>
                <tr data-no-order-matches hidden><td colspan="7" class="text-center">No orders match your search.</td></tr>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div></div>
</div>
</body>
</html>
