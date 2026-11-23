<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | JOSAM Online Store</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/index.css') ?>">
    <script src="<?= route_url('assets/js/customer/index.js') ?>" defer></script>
</head>

<body>
<header>
    <a class="store-brand" href="<?= route_url() ?>">
        <span class="brand-mark" aria-hidden="true">JS</span>
        <span>JOSAM <small>LOCAL FINDS, GOOD FINDS</small></span>
    </a>
    <nav>
        <a href="<?= route_url() ?>">Home</a>
        <?php if ($isCustomerLoggedIn): ?>
            <a href="<?= route_url('orders') ?>">My Orders</a>
            <span>
                Hello,
                <?= htmlspecialchars($_SESSION['full_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form class="logout-form" method="POST" action="<?= route_url('logout') ?>">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button class="logout-button" type="submit">Log out</button>
            </form>
        <?php endif; ?>
    </nav>
</header>

<main>
    <section class="shop-hero">
        <div class="welcome-copy">
            <p class="eyebrow">WELCOME TO JOSAM</p>
            <h1>Welcome to your<br><span>next good find.</span></h1>
            <p>
                Discover thoughtful picks from your neighborhood store.
                We’re glad you’re here.
            </p>
            <?php if ($isCustomerLoggedIn): ?>
                <p class="welcome-order-prompt">
                    Visit your customer dashboard to keep track of your orders.
                </p>
            <?php else: ?>
                <p class="welcome-order-prompt">Sign up or log in to order.</p>
                <div class="welcome-actions">
                    <a class="welcome-button welcome-button-primary" href="<?= route_url('register') ?>">Sign Up</a>
                    <a class="welcome-button welcome-button-secondary" href="<?= route_url('login') ?>">Log In</a>
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-stamp" aria-hidden="true">
            <span>LOCAL FINDS</span>
            <strong>GOOD<br>FINDS</strong>
        </div>
    </section>

    <?php if (isset($_GET['logged_out'])): ?>
        <dialog class="logout-popup" aria-labelledby="logout-popup-title"
            aria-describedby="logout-popup-message" data-logout-popup>
            <section class="logout-popup-content">
                <span class="logout-popup-icon" aria-hidden="true">✓</span>
                <h2 id="logout-popup-title">Logged out</h2>
                <p id="logout-popup-message">You have been securely logged out.</p>
                <button type="button" class="logout-popup-close" data-close-popup>Close</button>
            </section>
        </dialog>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p class="message-error" role="alert">
            We could not complete that request. Please try again.
        </p>
    <?php endif; ?>
</main>
</body>
</html>
