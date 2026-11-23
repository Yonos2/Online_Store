
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="<?= route_url('assets/css/store-theme.css') ?>">
    <link rel="stylesheet" href="<?= route_url('assets/css/customer/register.css') ?>">
</head>

<body>
<div class="register-box">
    <a class="auth-brand" href="<?= route_url() ?>">Market<span>.</span></a>
    <p class="eyebrow">YOUR NEXT GOOD FIND STARTS HERE</p>
    <h1>Create Account</h1>
    <p>Register as a customer to shop online.</p>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="<?= route_url('register') ?>">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
        >

        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name"
            value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
            maxlength="255"
            autocomplete="name"
            required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            maxlength="255"
            autocomplete="email"
            required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone"
            value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
            maxlength="30"
            autocomplete="tel">

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
            minlength="8"
            autocomplete="new-password"
            required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password"
            minlength="8"
            autocomplete="new-password"
            required>

        <button type="submit">Create Account</button>
    </form>

    <div class="login-link">
        Already have an account?
        <a href="<?= route_url('login') ?>">Log in</a>
    </div>

    <div class="login-link">
        <a href="<?= route_url() ?>">Back to Store</a>
    </div>
</div>
</body>
</html>
