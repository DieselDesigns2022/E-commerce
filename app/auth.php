<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('ecommerce_admin');
        session_start();
    }
}

function current_admin(): ?array
{
    auth_start();
    $adminId = $_SESSION['admin_id'] ?? null;
    if (!$adminId) {
        return null;
    }

    return db_first('SELECT id, name, email, role, is_active FROM admins WHERE id = ? AND is_active = 1', [(int) $adminId]);
}

function attempt_admin_login(string $email, string $password): bool
{
    auth_start();
    $admin = db_first('SELECT * FROM admins WHERE email = ? AND is_active = 1', [strtolower(trim($email))]);

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    db_execute('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [(int) $admin['id']]);

    return true;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('/admin/login.php');
    }

    return $admin;
}

function admin_logout(): void
{
    auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
