<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
customer_auth_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (customer_find_by_email($email)) {
        $errors[] = 'An account already exists for that email.';
    }
    if (strlen((string) ($_POST['password'] ?? '')) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (($_POST['password'] ?? '') !== ($_POST['password_confirmation'] ?? '')) {
        $errors[] = 'Password confirmation does not match.';
    }
    if (!$errors) {
        $customerId = customer_create($_POST);
        login_customer($customerId);
        redirect('/account/index.php');
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create Account</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><form method="post" class="card form-grid"><?= csrf_field() ?><h1 class="full">Create Account</h1><?php foreach ($errors as $error): ?><div class="full alert error"><?= e($error) ?></div><?php endforeach; ?><label>First Name <input name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>"></label><label>Last Name <input name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>"></label><label>Email <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label><label>Phone <input name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label><label>Password <input type="password" name="password" required minlength="8"></label><label>Confirm Password <input type="password" name="password_confirmation" required minlength="8"></label><label class="full checkbox-row"><input type="checkbox" name="accepts_marketing" value="1" <?= !empty($_POST['accepts_marketing']) ? 'checked' : '' ?>> Email me updates and offers</label><div class="full actions"><button type="submit">Create Account</button><a href="/account/login.php">Already have an account?</a></div></form></div></main></body></html>
