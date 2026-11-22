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
    <title>Inventory Overview</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller-console.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/inventory.css') ?>">
</head>
<body>
<?php $sellerActive = 'inventory'; require __DIR__ . '/partials/navigation.php'; ?>
<main class="inventory-page">
    <header class="inventory-header">
        <div>
            <p class="eyebrow">SELLER SPACE</p>
            <h1>Inventory overview</h1>
            <p>Review stock levels across your product catalog.</p>
        </div>
        <nav class="inventory-actions" aria-label="Inventory actions">
            <a class="inventory-button inventory-button-secondary" href="<?= route_url('seller/dashboard') ?>">Dashboard</a>
            <a class="inventory-button" href="<?= route_url('seller/products') ?>">Manage products</a>
        </nav>
    </header>
    <section class="inventory-stats" aria-label="Inventory summary">
        <article class="inventory-stat"><span>Products tracked</span><strong><?= count($products) ?></strong></article>
        <article class="inventory-stat inventory-stat-highlight"><span>Total units in stock</span><strong><?= $totalUnits ?></strong></article>
        <article class="inventory-stat"><span>Out of stock</span><strong><?= $outOfStockCount ?></strong></article>
    </section>
    <section class="inventory-notification" aria-labelledby="new-checkouts-heading">
        <div class="inventory-notification-heading">
            <div><p class="eyebrow">ORDER UPDATES</p><h2 id="new-checkouts-heading">New customer checkouts</h2></div>
            <span class="inventory-notification-count"><?= $newOrderCount ?> pending</span>
        </div>
        <?php if (!$newOrders): ?>
            <p class="inventory-notification-empty">No new checkouts right now. New customer orders will appear here.</p>
        <?php else: ?>
            <ul class="inventory-notification-list">
                <?php foreach ($newOrders as $order): ?>
                    <li>
                        <a href="<?= route_url('seller/orders/details?id=' . (int) $order['id']) ?>">
                            <span><strong><?= e($order['customer_name']) ?></strong>
                                <small><?= e($order['order_number']) ?> · <?= e(date('M d, Y h:i A', strtotime($order['created_at']))) ?></small></span>
                            <span class="inventory-notification-view">View order →</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($newOrderCount > count($newOrders)): ?>
                <p class="inventory-notification-more">And <?= $newOrderCount - count($newOrders) ?> more pending <?= $newOrderCount - count($newOrders) === 1 ? 'order' : 'orders' ?>.</p>
            <?php endif; ?>
        <?php endif; ?>
        <a class="inventory-notification-button" href="<?= route_url('seller/checkouts') ?>">View all new checkouts <span aria-hidden="true">→</span></a>
    </section>
    <section class="inventory-card" aria-labelledby="inventory-list-heading">
        <div class="inventory-card-heading">
            <div><p class="eyebrow">STOCK LEVELS</p><h2 id="inventory-list-heading">All products</h2></div>
            <span><?= count($products) ?> products</span>
        </div>
        <?php if (!$products): ?>
            <p class="inventory-empty">No products to review yet. <a href="<?= route_url('seller/products/new') ?>">Add your first product</a></p>
        <?php else: ?>
            <div class="inventory-table-wrap">
                <table>
                    <thead><tr><th scope="col">Product</th><th scope="col">Category</th><th scope="col">Availability</th><th scope="col">Units</th><th scope="col">Listing status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $product): $stock = (int) $product['stock']; ?>
                        <tr>
                            <th scope="row"><?= e($product['name']) ?></th>
                            <td><?= e($product['category_name'] ?? 'Uncategorized') ?></td>
                            <td><span class="stock-badge <?= $stock > 0 ? 'stock-available' : 'stock-empty' ?>"><?= $stock > 0 ? 'In stock' : 'Out of stock' ?></span></td>
                            <td class="stock-quantity"><?= $stock ?></td>
                            <td><?= e(ucfirst($product['status'])) ?></td>
                            <td><a class="inventory-edit-link" href="<?= route_url('seller/products/edit?id=' . (int) $product['id']) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
