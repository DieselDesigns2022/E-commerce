<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/models/Customer.php';

function customer_auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('ecommerce_customer');
        session_start();
    }
}

function current_customer(): ?array
{
    customer_auth_start();
    $customerId = $_SESSION['customer_id'] ?? null;
    if (!$customerId) {
        return null;
    }

    return customer_find((int) $customerId);
}

function require_customer(): array
{
    $customer = current_customer();
    if (!$customer) {
        redirect('/account/login.php');
    }
    return $customer;
}

function attempt_customer_login(string $email, string $password): bool
{
    customer_auth_start();
    $customer = customer_find_by_email($email);
    if (!$customer || !$customer['password_hash'] || !password_verify($password, $customer['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int) $customer['id'];
    return true;
}

function login_customer(int $customerId): void
{
    customer_auth_start();
    session_regenerate_id(true);
    $_SESSION['customer_id'] = $customerId;
}

function customer_logout(): void
{
    customer_auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function customer_create_password_reset(string $email): ?string
{
    $customer = customer_find_by_email($email);
    if (!$customer) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    db_execute(
        'INSERT INTO password_resets (email, token_hash, user_type, expires_at, created_at) VALUES (?, ?, "customer", DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())',
        [strtolower(trim($email)), hash('sha256', $token)]
    );

    return $token;
}

function customer_reset_from_token(string $email, string $token, string $password): bool
{
    $reset = db_first(
        'SELECT * FROM password_resets WHERE email = ? AND user_type = "customer" AND token_hash = ? AND used_at IS NULL AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1',
        [strtolower(trim($email)), hash('sha256', $token)]
    );
    if (!$reset) {
        return false;
    }

    $customer = customer_find_by_email($email);
    if (!$customer) {
        return false;
    }

    customer_update_password((int) $customer['id'], $password);
    db_execute('UPDATE password_resets SET used_at = NOW() WHERE id = ?', [(int) $reset['id']]);
    return true;
}
