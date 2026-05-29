<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
customer_auth_start();

$email = (string) ($_GET['email'] ?? $_POST['email'] ?? '');
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (strlen((string) ($_POST['password'] ?? '')) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
        $errors[] = 'Password confirmation does not match.';
    }
    if (!$errors && customer_reset_from_token($email, $token, (string) $_POST['password'])) {
        $success = true;
    } elseif (!$errors) {
        $errors[] = 'The reset link is invalid or expired.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset Password</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><form method="post" class="card form-grid"><?= csrf_field() ?><h1 class="full">Choose New Password</h1><?php if ($success): ?><div class="full alert success">Password updated. <a href="/account/login.php">Log in</a>.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="full alert error"><?= e($error) ?></div><?php endforeach; ?><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="token" value="<?= e($token) ?>"><label>Password <input type="password" name="password" required minlength="8"></label><label>Confirm Password <input type="password" name="password_confirmation" required minlength="8"></label><div class="full actions"><button type="submit">Reset Password</button></div></form></div></main></body></html>
