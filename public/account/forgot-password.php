<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
customer_auth_start();

$tokenUrl = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $token = customer_create_password_reset($_POST['email'] ?? '');
    if ($token) {
        $tokenUrl = '/account/reset-password.php?email=' . urlencode((string) $_POST['email']) . '&token=' . urlencode($token);
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forgot Password</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><form method="post" class="card form-grid"><?= csrf_field() ?><h1 class="full">Reset Password</h1><p class="full">Enter your email address. In this test build, the reset link is shown on-screen instead of sent by email.</p><label class="full">Email <input type="email" name="email" required></label><div class="full actions"><button type="submit">Create Reset Link</button></div><?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?><div class="full alert success"><?= $tokenUrl ? 'Reset link: <a href="' . e($tokenUrl) . '">' . e($tokenUrl) . '</a>' : 'If that email exists, a reset link has been created.' ?></div><?php endif; ?></form></div></main></body></html>
