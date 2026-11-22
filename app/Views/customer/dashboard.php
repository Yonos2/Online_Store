<?php

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
$unreadNotifications = $unreadProductNotifications;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/my_orders.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/customer_dashboard.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/confirm-actions.css') ?>">
    <script src="<?= route_url('assets/js/customer/product-image-viewer.js') ?>" defer></script>
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
    <script src="<?= route_url('assets/js/customer/customer-dashboard.js') ?>" defer></script>
</head>
<body data-order-details-url="<?= route_url('orders/details') ?>"
    data-order-updates-url="<?= route_url('orders/updates') ?>">
<header class="dashboard-header"><div class="container"><div class="customer-toolbar">
    <a class="dashboard-brand" href="<?= route_url('dashboard') ?>"><span class="dashboard-brand-mark">JS</span><span>JOSAM <small>LOCAL FINDS, GOOD FINDS</small></span></a>
    <nav class="dashboard-navigation" aria-label="Customer navigation">
        <a class="dashboard-link" href="#shop-products">Browse Products</a>
        <button class="dashboard-link dashboard-link-secondary dashboard-section-toggle" type="button" data-dialog-target="order-updates-dialog" aria-haspopup="dialog" data-order-updates-button>
            Order Updates <span class="dashboard-nav-count" data-order-updates-badge data-product-unread-count="<?= count($unreadNotifications) ?>" <?= !$unreadOrderUpdates && !$unreadNotifications ? 'hidden' : '' ?>><?= count($unreadOrderUpdates) + count($unreadNotifications) ?></span>
        </button>
        <button class="dashboard-link dashboard-link-secondary dashboard-section-toggle" type="button" data-dialog-target="purchase-history-dialog" aria-haspopup="dialog">Purchase History</button>
        <a class="dashboard-link dashboard-link-secondary" href="<?= route_url('cart') ?>">Shopping Cart</a><a class="dashboard-link dashboard-link-secondary" href="<?= route_url() ?>">Home</a>
    </nav>
    <form method="POST" action="<?= route_url('logout') ?>"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><button type="submit">Log out</button></form>
</div></div></header>
<main class="dashboard-main"><div class="container">
    <section class="card dashboard-intro"><p class="eyebrow">YOUR ACCOUNT</p><h1>Customer Dashboard</h1><p class="muted">Welcome, <?= e($_SESSION['full_name'] ?? 'Customer') ?>. Review your order updates and purchase history here.</p></section>
    <section class="dashboard-catalog" id="shop-products" aria-labelledby="products-title">
        <div class="dashboard-section-heading dashboard-catalog-heading"><div><p class="eyebrow">THE COLLECTION</p><h2 id="products-title">Shop the latest</h2></div><span class="muted"><?= count($products) ?> available</span></div>
        <?php if (!$products): ?><div class="card"><p class="muted">There are no products available right now. Please check back soon.</p></div><?php else: ?>
            <div class="dashboard-products"><?php foreach ($products as $product): $imageUrl = !empty($product['image_path']) ? route_url(ltrim($product['image_path'], '/')) : null; $stock = (int) $product['stock']; ?>
                <article class="dashboard-product"><div class="dashboard-product-visual">
                    <?php if ($imageUrl): ?><a class="dashboard-product-image-link" href="<?= e($imageUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="View a larger image of <?= e($product['name']) ?>" data-product-image-view data-product-name="<?= e($product['name']) ?>"><img src="<?= e($imageUrl) ?>" alt="<?= e($product['name']) ?>" loading="lazy"></a><?php else: ?><span>No image available</span><?php endif; ?>
                </div><div class="dashboard-product-info"><h3><?= e($product['name']) ?></h3><p class="dashboard-product-price">₱<?= number_format((float) $product['price'], 2) ?></p>
                    <?php if (!empty($product['description'])): ?><p class="dashboard-product-description"><?= nl2br(e($product['description'])) ?></p><?php endif; ?>
                    <p class="dashboard-product-stock"><?= $stock > 0 ? 'In stock: ' . $stock : 'Out of stock' ?></p>
                    <form action="<?= route_url('cart/add') ?>" method="POST"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><label for="quantity-<?= (int) $product['id'] ?>">Quantity</label><input type="number" id="quantity-<?= (int) $product['id'] ?>" name="quantity" value="1" min="1" max="<?= $stock ?>" required <?= $stock < 1 ? 'disabled' : '' ?>><button type="submit" <?= $stock < 1 ? 'disabled' : '' ?>><?= $stock < 1 ? 'Out of Stock' : 'Add to Cart' ?></button></form>
                </div></article>
            <?php endforeach; ?></div>
        <?php endif; ?>
    </section>
    <dialog class="dashboard-section-dialog" id="order-updates-dialog" aria-labelledby="order-updates-title"><form method="dialog" class="dashboard-dialog-close-form"><button type="submit" class="dashboard-dialog-close" aria-label="Close order updates">&times;</button></form>
        <section class="card dashboard-updates" id="order-updates"><div class="dashboard-section-heading"><div><p class="eyebrow">ORDER UPDATES</p><h2 id="order-updates-title">Status notifications</h2></div><span class="dashboard-update-count" data-order-updates-count <?= !$unreadOrderUpdates ? 'hidden' : '' ?>><?= count($unreadOrderUpdates) ?> unread</span></div>
            <p class="muted" data-no-live-order-updates <?= $unreadOrderUpdates ? 'hidden' : '' ?>>You’re all caught up. New seller status updates will appear here.</p>
            <ul class="dashboard-update-list" data-live-order-updates><?php foreach (array_slice($unreadOrderUpdates, 0, 5) as $order): ?><li data-order-update-id="<?= (int) ($order['id'] ?? 0) ?>" data-order-status="<?= e($order['order_status'] ?? '') ?>" data-order-updated-at="<?= e($order['updated_at'] ?? '') ?>"><div><strong>Order <?= e($order['order_number'] ?? '') ?></strong><span><?= e(ucfirst($order['order_status'] ?? '')) ?> · <?= e(date('M d, Y', strtotime($order['updated_at'] ?? ''))) ?></span></div><a href="<?= route_url('orders/details?id=' . (int) ($order['id'] ?? 0)) ?>">View update</a></li><?php endforeach; ?></ul><p class="muted dashboard-more-updates" data-live-more-order-updates hidden></p>
            <div class="dashboard-product-updates"><div class="dashboard-section-heading"><div><p class="eyebrow">STORE ACTIVITY</p><h3>Product updates</h3></div><?php if ($unreadNotifications): ?><span class="dashboard-update-count"><?= count($unreadNotifications) ?> new</span><?php endif; ?></div>
                <?php if (!$productNotifications): ?><p class="muted">There are no product updates yet.</p><?php else: ?><ul class="dashboard-update-list"><?php foreach ($productNotifications as $notification): $title = match ($notification['action']) {'added' => 'New product added', 'updated' => 'Product updated', 'deleted' => 'Product removed', default => 'Store update'}; ?><li><div><strong><?= e($title) ?></strong><span><?= e($notification['product_name']) ?> · <?= e(date('M d, Y', strtotime($notification['created_at']))) ?><?= !$notification['is_read'] ? ' · New' : '' ?></span></div><form method="POST" action="<?= route_url('notifications/dismiss') ?>" data-confirm-message="Remove this product update from your Store Activity? It will only be removed from your account." data-confirm-title="Delete this activity?" data-confirm-action="Delete activity" data-confirm-variant="danger"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>"><button class="dashboard-activity-delete" type="submit">Delete</button></form></li><?php endforeach; ?></ul>
                    <?php if ($unreadNotifications): ?><form method="POST" action="<?= route_url('notifications/read') ?>"><input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>"><?php foreach ($unreadNotifications as $notification): ?><input type="hidden" name="notification_ids[]" value="<?= (int) $notification['id'] ?>"><?php endforeach; ?><button class="dashboard-link dashboard-link-secondary" type="submit">Mark product updates as read</button></form><?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </dialog>
    <dialog class="dashboard-section-dialog" id="purchase-history-dialog" aria-labelledby="order-history-title"><form method="dialog" class="dashboard-dialog-close-form"><button type="submit" class="dashboard-dialog-close" aria-label="Close purchase history">&times;</button></form>
        <section class="card dashboard-orders" id="purchase-history"><div class="dashboard-section-heading"><div><p class="eyebrow">PURCHASE HISTORY</p><h2 id="order-history-title">My Orders</h2></div><span class="muted"><?= count($customerOrders) ?> total</span></div>
            <?php if (!$customerOrders): ?><p>You have not placed any orders yet.</p><a class="dashboard-link" href="<?= route_url() ?>">Explore products</a><?php else: ?><div class="table-wrap"><table><thead><tr><th>Order Number</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Details</th></tr></thead><tbody><?php foreach ($customerOrders as $order): ?><tr><td><?= e($order['order_number']) ?></td><td><?= e(date('M d, Y', strtotime($order['created_at']))) ?></td><td>₱<?= number_format((float) $order['total_amount'], 2) ?></td><td><?= e($order['payment_method'] === 'cod' ? 'Cash on Delivery' : ucfirst($order['payment_method'])) ?></td><td><?= e(ucfirst($order['order_status'])) ?></td><td><a href="<?= route_url('orders/details?id=' . (int) $order['id']) ?>">View</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </section>
    </dialog>
</div></main>
<dialog class="product-image-dialog dashboard-product-image-dialog" aria-labelledby="product-image-caption"><form method="dialog"><button class="dashboard-product-image-close" type="submit" aria-label="Close image viewer">&times;</button></form><figure><img data-product-image-dialog-img alt=""><figcaption id="product-image-caption" data-product-image-caption></figcaption></figure></dialog>
</body>
</html>
