<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function coupons_all(): array
{
    return db_select('SELECT * FROM coupons ORDER BY created_at DESC');
}

function coupon_find(int $id): ?array
{
    return db_first('SELECT * FROM coupons WHERE id = ?', [$id]);
}

function coupon_by_code(string $code): ?array
{
    return db_first('SELECT * FROM coupons WHERE code = ?', [strtoupper(trim($code))]);
}

function coupon_save(array $data, ?int $id = null): int
{
    $params = [
        strtoupper(trim((string) $data['code'])),
        $data['discount_type'],
        (int) round(((float) ($data['discount_value'] ?? 0)) * ($data['discount_type'] === 'percent' ? 1 : 100)),
        $data['starts_at'] ?: null,
        $data['ends_at'] ?: null,
        $data['usage_limit'] !== '' ? (int) $data['usage_limit'] : null,
        $data['minimum_order'] !== '' ? (int) round(((float) $data['minimum_order']) * 100) : null,
        !empty($data['is_active']) ? 1 : 0,
    ];

    if ($id) {
        db_execute('UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, starts_at = ?, ends_at = ?, usage_limit = ?, minimum_order_cents = ?, is_active = ?, updated_at = NOW() WHERE id = ?', [...$params, $id]);
        return $id;
    }

    db_execute('INSERT INTO coupons (code, discount_type, discount_value, starts_at, ends_at, usage_limit, minimum_order_cents, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())', $params);
    return (int) db()->lastInsertId();
}

function coupon_discount_cents(?array $coupon, int $subtotalCents): int
{
    if (!$coupon || !(int) $coupon['is_active']) {
        return 0;
    }
    if ($coupon['minimum_order_cents'] !== null && $subtotalCents < (int) $coupon['minimum_order_cents']) {
        return 0;
    }
    if ($coupon['discount_type'] === 'percent') {
        return min($subtotalCents, (int) floor($subtotalCents * ((int) $coupon['discount_value'] / 100)));
    }
    if ($coupon['discount_type'] === 'fixed') {
        return min($subtotalCents, (int) $coupon['discount_value']);
    }
    return 0;
}
