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
    <title>New Checkouts</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller-console.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/new_checkouts.css') ?>">
</head>
<body>
<?php $sellerActive = 'checkouts'; require __DIR__ . '/partials/navigation.php'; ?>
<main class="checkouts-page">
    <header class="checkouts-header">
        <div><p class="eyebrow">SELLER SPACE · NEW ORDERS</p><h1>New checkouts</h1><p>Pending orders from customers who have completed checkout.</p></div>
        <nav class="checkouts-actions" aria-label="Checkout actions">
            <a class="checkouts-button checkouts-button-secondary" href="<?= route_url('seller/dashboard') ?>">Dashboard</a>
            <a class="checkouts-button" href="<?= route_url('seller/orders') ?>">Manage orders</a>
        </nav>
    </header>
    <section class="checkouts-summary" aria-label="New checkout count">
        <span class="checkout-indicator" aria-hidden="true"></span>
        <p><strong><?= count($newCheckouts) ?></strong> pending <?= count($newCheckouts) === 1 ? 'checkout' : 'checkouts' ?></p>
    </section>
    <?php if (!$newCheckouts): ?>
        <section class="checkouts-empty">
            <span class="checkouts-empty-mark" aria-hidden="true">✓</span>
            <h2>You’re all caught up</h2>
            <p>New customer checkouts will appear here until their order status is updated.</p>
            <a class="checkouts-button" href="<?= route_url('seller/orders') ?>">View all orders</a>
        </section>
    <?php else: ?>
        <section class="checkout-list" aria-label="Pending customer checkouts">
            <?php foreach ($newCheckouts as $checkout): ?>
                <article class="checkout-card">
                    <div class="checkout-card-main">
                        <div class="checkout-card-heading">
                            <span class="checkout-status">New checkout</span>
                            <time datetime="<?= e(date(DATE_ATOM, strtotime($checkout['created_at']))) ?>"><?= e(date('M d, Y · h:i A', strtotime($checkout['created_at']))) ?></time>
                        </div>
                        <h2><?= e($checkout['customer_name']) ?></h2>
                        <p class="checkout-number"><?= e($checkout['order_number']) ?></p>
                        <?php if (!empty($checkout['customer_email'])): ?><p class="checkout-email"><?= e($checkout['customer_email']) ?></p><?php endif; ?>
                        <p class="checkout-recipient">Recipient: <?= e($checkout['recipient_name']) ?></p>
                    </div>
                    <div class="checkout-card-side">
                        <span class="checkout-total">₱<?= number_format((float) $checkout['total_amount'], 2) ?></span>
                        <span class="checkout-payment"><?= e($checkout['payment_method'] === 'cod' ? 'Cash on Delivery' : ucfirst($checkout['payment_method'])) ?></span>
                        <a class="checkouts-button" href="<?= route_url('seller/orders/details?id=' . (int) $checkout['id']) ?>">Review checkout</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
