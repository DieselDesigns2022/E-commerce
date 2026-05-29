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

function coupon_normalize_date(?string $date, bool $endOfDay = false): ?string
{
    $date = trim((string) $date);
    if ($date === '') {
        return null;
    }
    return $date . ($endOfDay ? ' 23:59:59' : ' 00:00:00');
}

function coupon_save(array $data, ?int $id = null): int
{
    $params = [
        strtoupper(trim((string) $data['code'])),
        $data['discount_type'],
        (int) round(((float) ($data['discount_value'] ?? 0)) * ($data['discount_type'] === 'percent' ? 1 : 100)),
        coupon_normalize_date($data['starts_at'] ?? null),
        coupon_normalize_date($data['ends_at'] ?? null, true),
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

function coupon_is_valid(?array $coupon, int $subtotalCents): bool
{
    if (!$coupon || !(int) $coupon['is_active']) {
        return false;
    }
    $now = time();
    if ($coupon['starts_at'] !== null && strtotime((string) $coupon['starts_at']) > $now) {
        return false;
    }
    if ($coupon['ends_at'] !== null && strtotime((string) $coupon['ends_at']) < $now) {
        return false;
    }
    if ($coupon['minimum_order_cents'] !== null && $subtotalCents < (int) $coupon['minimum_order_cents']) {
        return false;
    }
    if ($coupon['usage_limit'] !== null) {
        $redemptions = db_first('SELECT COUNT(*) AS count FROM coupon_redemptions WHERE coupon_id = ?', [(int) $coupon['id']]);
        if ((int) ($redemptions['count'] ?? 0) >= (int) $coupon['usage_limit']) {
            return false;
        }
    }
    return true;
}

function coupon_discount_cents(?array $coupon, int $subtotalCents): int
{
    if (!coupon_is_valid($coupon, $subtotalCents)) {
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

function coupon_gives_free_shipping(?array $coupon, int $subtotalCents): bool
{
    return coupon_is_valid($coupon, $subtotalCents) && $coupon['discount_type'] === 'free_shipping';
}
