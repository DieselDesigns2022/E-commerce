<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function settings_all(): array
{
    $rows = db_select('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function setting_get(string $key, mixed $default = null): mixed
{
    $row = db_first('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
    return $row['setting_value'] ?? $default;
}

function setting_set(string $key, mixed $value): void
{
    db_execute(
        'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
        [$key, (string) $value]
    );
}
