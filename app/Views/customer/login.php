<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/login.css') ?>">
</head>

<body>
<div class="login-box">
    <a class="auth-brand" href="<?= route_url() ?>">Market<span>.</span></a>
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Customer Login</h1>
    <p>Log in to shop and manage your cart.</p>

    <?php if (isset($_GET['registered'])): ?>
        <p class="registration-success">
            Registration successful! You can now log in.
        </p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="<?= route_url('login') ?>">
        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            required
            autocomplete="email"
        >
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >
        <button type="submit">Log In</button>
    </form>

    <div class="links">
        Don't have an account? <a href="<?= route_url('register') ?>">Register here</a>
    </div>
    <div class="links"><a href="<?= route_url() ?>">Back to Store</a></div>
</div>
</body>
</html>
