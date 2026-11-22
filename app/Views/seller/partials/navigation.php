<?php
$sellerActive = $sellerActive ?? '';
$sellerLinks = [
    'dashboard' => ['Dashboard', 'seller/dashboard'],
    'products' => ['Products', 'seller/products'],
    'inventory' => ['Inventory', 'seller/inventory'],
    'checkouts' => ['New checkouts', 'seller/checkouts'],
    'orders' => ['Orders', 'seller/orders']
];
?>
<header class="seller-app-header">
    <a class="seller-app-brand" href="<?= route_url('seller/dashboard') ?>">
        <span class="seller-brand-mark" aria-hidden="true">M</span>
        <span>Market<small>SELLER CONSOLE</small></span>
    </a>
    <nav class="seller-app-nav" aria-label="Seller navigation">
        <?php foreach ($sellerLinks as $key => [$label, $route]): ?>
            <a href="<?= route_url($route) ?>"
                <?= $sellerActive === $key ? 'aria-current="page"' : '' ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>
