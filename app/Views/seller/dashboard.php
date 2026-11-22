<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller-console.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/confirm-actions.css') ?>">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
</head>
<body>
<?php $sellerActive = 'dashboard'; require __DIR__ . '/partials/navigation.php'; ?>
<main class="dashboard-shell">
    <header class="dashboard-heading">
        <form method="POST" action="<?= route_url('seller/logout') ?>"
            data-confirm-message="Are you sure you want to log out of your seller account?"
            data-confirm-title="Log out?" data-confirm-action="Log out">
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <button class="seller-logout-button" type="submit">Log out</button>
        </form>
    </header>
    <p class="eyebrow">SELLER SPACE</p>
    <h1>Good to see you, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Seller') ?>.</h1>
    <p class="dashboard-intro">Here’s a quick look at how your shop is doing.</p>
    <section class="dashboard-stats" aria-label="Store overview">
        <article class="stat-card">
            <span>Products in store</span>
            <strong><?= $productCount ?></strong>
            <a href="<?= route_url('seller/products') ?>">Manage products →</a>
        </article>
        <article class="stat-card stat-card-accent">
            <span>Items in stock</span>
            <strong><?= $totalStock ?></strong>
            <a href="<?= route_url('seller/inventory') ?>">Review inventory →</a>
        </article>
    </section>
</main>
</body>
</html>
