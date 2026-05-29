<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $config = require __DIR__ . '/config.php';
    $base = $config['app']['url'];
    return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $submitted = $_POST['_csrf_token'] ?? '';
    $expected = $_SESSION['_csrf_token'] ?? '';

    if (!is_string($submitted) || !is_string($expected) || !hash_equals($expected, $submitted)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    return trim($value, '-') ?: bin2hex(random_bytes(4));
}

function money(int $cents, string $currency = 'USD'): string
{
    return number_format($cents / 100, 2) . ' ' . strtoupper($currency);
}

function active_nav(string $current, string $target): string
{
    return $current === $target ? ' class="active"' : '';
}
