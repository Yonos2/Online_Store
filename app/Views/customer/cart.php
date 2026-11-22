<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Shopping Cart</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/cart.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/confirm-actions.css') ?>">
    <script src="<?= route_url('assets/js/confirm-actions.js') ?>" defer></script>
</head>

<body>
<div class="container page-shell">
    <div class="customer-toolbar">
        <form method="POST" action="<?= route_url('logout') ?>">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >
            <button type="submit">Log out</button>
        </form>
    </div>

    <div class="page-heading">
        <div>
            <p class="eyebrow">YOUR PICKS</p>
            <h1>Shopping cart</h1>
        </div>
        <a class="text-link" href="<?= route_url() ?>">← Continue shopping</a>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <p class="message-success">Cart quantity updated successfully.</p>
    <?php endif; ?>

    <?php if (isset($_GET['removed'])): ?>
    <p class="message-success">Item removed from cart.</p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <p class="message-error">
        <?php
        $messages = [
            'invalid_request' => 'Invalid request. Please try again.',
            'item_not_found' => 'Cart item not found.',
            'invalid_quantity' => 'Please enter a valid quantity.',
            'insufficient_stock' => 'Requested quantity exceeds available stock.',
            'server' => 'Unable to update your cart. Please try again.'
        ];
        $errorCode = is_string($_GET['error']) ? $_GET['error'] : '';
        echo htmlspecialchars(
            $messages[$errorCode] ?? 'Something went wrong.',
            ENT_QUOTES,
            'UTF-8'
        );
        ?>
    </p>
    <?php endif; ?>

    <?php if (isset($_GET['added'])): ?>
    <p class="message-success">Product added to your cart successfully!</p>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <div class="empty">
            <h2>Your cart is empty.</h2>
            <p>Browse our products and add something to your cart.</p>
            <a class="link-button" href="<?= route_url() ?>">Browse Products</a>
        </div>
    <?php else: ?>
        <?php foreach ($cartItems as $item): ?>
            <article class="cart-item">
                <?php if (!empty($item['image_path'])): ?>
                    <img
                        class="cart-image"
                        src="<?= htmlspecialchars(route_url(ltrim($item['image_path'], '/')), ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($item['name']) ?>"
                    >
                <?php else: ?>
                    <div class="cart-image">No image</div>
                <?php endif; ?>

                <div class="item-info">
                    <h2><?= htmlspecialchars($item['name']) ?></h2>
                    <p>Price: ₱<?= number_format((float) $item['price'], 2) ?></p>
                    <p>Quantity: <?= (int) $item['quantity'] ?></p>
                    <p>Available stock: <?= (int) $item['stock'] ?></p>
                    <p class="price">
                        Subtotal: ₱<?= number_format((float) $item['subtotal'], 2) ?>
                    </p>

                    <form action="<?= route_url('cart/action') ?>" method="POST">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <input
                            type="hidden"
                            name="cart_item_id"
                            value="<?= (int) $item['cart_item_id'] ?>"
                        >
                        <label for="quantity-<?= (int) $item['cart_item_id'] ?>">
                            Quantity
                        </label>
                        <input
                            type="number"
                            id="quantity-<?= (int) $item['cart_item_id'] ?>"
                            name="quantity"
                            value="<?= (int) $item['quantity'] ?>"
                            min="1"
                            max="<?= (int) $item['stock'] ?>"
                            required
                        >
                        <button type="submit" name="action" value="update">
                            Update Quantity
                        </button>
                        <button
                            type="submit"
                            name="action"
                            value="remove"
                            formnovalidate
                            data-confirm-message="Remove this item from your cart?"
                        >
                            Remove
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>

        <div class="summary">
            <h2>Total: ₱<?= number_format($total, 2) ?></h2>
            <a href="<?= route_url('checkout') ?>">
                <a class="link-button" href="<?= route_url('checkout') ?>">Proceed to checkout</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
