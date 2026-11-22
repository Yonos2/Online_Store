<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Login</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/seller/seller-login.css') ?>">
</head>
<body>
<main class="seller-login-card">
    <a class="auth-brand" href="<?= route_url('seller/login') ?>">Market<span>.</span></a>
    <p class="eyebrow">SELLER CONSOLE</p>
    <h1>Welcome back</h1>
    <p class="seller-login-intro">Sign in to manage your products, inventory, and customer orders.</p>

    <?php if ($error !== ''): ?>
        <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['logged_out'])): ?>
        <p class="alert alert-success">You have been securely logged out.</p>
    <?php endif; ?>

    <form method="POST">
        <label for="seller-email">Email address</label>
        <input type="email" id="seller-email" name="email" autocomplete="username" required>
        <label for="seller-password">Password</label>
        <input type="password" id="seller-password" name="password" autocomplete="current-password" required>
        <button type="submit">Sign in to seller console</button>
    </form>
    <p class="seller-login-footnote">Authorized seller access only.</p>
</main>
</body>
</html>
