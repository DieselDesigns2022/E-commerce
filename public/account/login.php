<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
customer_auth_start();

if (current_customer()) {
    redirect('/account/index.php');
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (attempt_customer_login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        redirect('/account/index.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer Login</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><form method="post" class="card form-grid"><?= csrf_field() ?><h1 class="full">Customer Login</h1><?php if ($error): ?><div class="full alert error"><?= e($error) ?></div><?php endif; ?><label class="full">Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label><label class="full">Password <input type="password" name="password" required></label><div class="full actions"><button type="submit">Login</button><a href="/account/register.php">Register</a><a href="/account/forgot-password.php">Forgot password?</a></div></form></div></main></body></html>
