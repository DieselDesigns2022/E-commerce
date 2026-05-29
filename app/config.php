<?php

declare(strict_types=1);

if (!function_exists('env_value')) {
function env_value(string $key, mixed $default = null): mixed
{
    static $loaded = false;
    static $values = [];

    if (!$loaded) {
        $envPath = dirname(__DIR__) . '/.env';
        if (is_readable($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = explode('=', $line, 2);
                $values[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }
        $loaded = true;
    }

    return $_ENV[$key] ?? getenv($key) ?: ($values[$key] ?? $default);
}
}

return [
    'app' => [
        'env' => env_value('APP_ENV', 'production'),
        'url' => rtrim((string) env_value('APP_URL', ''), '/'),
        'key' => (string) env_value('APP_KEY', 'change-this-secret'),
    ],
    'database' => [
        'host' => env_value('DB_HOST', '127.0.0.1'),
        'port' => env_value('DB_PORT', '3306'),
        'name' => env_value('DB_DATABASE', 'ecommerce_store'),
        'user' => env_value('DB_USERNAME', 'root'),
        'password' => env_value('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_address' => env_value('MAIL_FROM_ADDRESS', 'orders@example.com'),
        'from_name' => env_value('MAIL_FROM_NAME', 'Store Orders'),
    ],
    'stripe' => [
        'secret_key' => env_value('STRIPE_SECRET_KEY', ''),
        'webhook_secret' => env_value('STRIPE_WEBHOOK_SECRET', ''),
    ],
];
