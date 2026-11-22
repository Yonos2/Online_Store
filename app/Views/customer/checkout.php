<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/checkout.css') ?>">
</head>
<body>
<div class="customer-toolbar"><form method="POST" action="<?= route_url('logout') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit">Log out</button>
</form></div>
<header class="checkout-heading">
    <a href="<?= route_url() ?>" class="checkout-brand">Market<span>.</span></a>
    <div><p class="eyebrow">ALMOST YOURS</p><h1>Secure checkout</h1></div>
</header>
<?php if ($successOrder !== null): ?>
    <div class="box checkout-success">
        <h2 class="success">Order placed successfully!</h2>
        <p>Your order number is: <strong><?= htmlspecialchars($successOrder, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p>Payment method: Cash on Delivery</p><p>Payment status: Pending</p><p>Order status: Pending</p>
        <a href="<?= route_url() ?>">Continue Shopping</a>
    </div>
<?php else: ?>
    <?php if ($error !== ''): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <div class="box order-summary">
        <p class="eyebrow">YOUR ORDER</p><h2>Order summary</h2>
        <?php if (!$items): ?>
            <p>Your cart is empty.</p><a href="<?= route_url() ?>">Continue Shopping</a>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <p><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?> × <?= (int) $item['quantity'] ?> — ₱<?= number_format((float) $item['subtotal'], 2) ?></p>
            <?php endforeach; ?>
            <h3>Total: ₱<?= number_format($total, 2) ?></h3>
        <?php endif; ?>
    </div>
    <?php if ($items): ?>
        <div class="box delivery-details">
            <p class="eyebrow">WHERE TO SEND IT</p><h2>Delivery details</h2>
            <form method="POST" action="<?= route_url('checkout') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="checkout_token" value="<?= htmlspecialchars($_SESSION['checkout_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="recipient_name">Recipient Name</label><input type="text" id="recipient_name" name="recipient_name" maxlength="255" required>
                <label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" maxlength="50" required>
                <label for="shipping_address">Shipping Address</label><textarea id="shipping_address" name="shipping_address" rows="4" maxlength="5000" required></textarea>
                <fieldset class="payment-method-options">
                    <legend>Payment Method</legend>
                    <label class="payment-method-option"><input type="radio" name="payment_method" value="cod" <?= $selectedPaymentMethod === 'cod' ? 'checked' : '' ?> required><span>Cash on Delivery (COD)</span></label>
                    <label class="payment-method-option"><input type="radio" name="payment_method" value="paymongo" <?= $selectedPaymentMethod === 'paymongo' ? 'checked' : '' ?> <?= PayMongo::isConfigured() ? '' : 'disabled' ?>><span>Pay online with PayMongo <small><?= PayMongo::isConfigured() ? 'Card, GCash, or Maya · test mode' : 'Test setup required' ?></small></span></label>
                </fieldset>
                <button type="submit">Place Order</button>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
