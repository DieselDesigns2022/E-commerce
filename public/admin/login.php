<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';

if (current_admin()) {
    redirect('/admin/index.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (attempt_admin_login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        redirect('/admin/index.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="login-page">
    <form method="post" class="card login-card">
        <?= csrf_field() ?>
        <h1>Admin Login</h1>
        <p>Sign in to manage this store.</p>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <label>Email
            <input name="email" type="email" required autocomplete="email">
        </label>
        <label>Password
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <div class="actions">
            <button type="submit">Login</button>
        </div>
    </form>
</body>
</html>
