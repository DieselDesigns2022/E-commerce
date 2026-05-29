<?php

declare(strict_types=1);

require_once __DIR__ . '/Setting.php';

function shipping_settings_save(array $data): void
{
    setting_set('shipping_flat_rate_cents', (string) (int) round(((float) ($data['shipping_flat_rate'] ?? 0)) * 100));
    setting_set('shipping_free_threshold_cents', (string) (int) round(((float) ($data['shipping_free_threshold'] ?? 0)) * 100));
    setting_set('local_pickup_enabled', !empty($data['local_pickup_enabled']) ? '1' : '0');
    setting_set('tax_enabled', !empty($data['tax_enabled']) ? '1' : '0');
    setting_set('tax_rate_percent', (string) (float) ($data['tax_rate_percent'] ?? 0));
}
