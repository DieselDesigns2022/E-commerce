<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Coupon.php';
require_once __DIR__ . '/Setting.php';

function orders_all(?string $status = null, ?string $search = null): array
{
    $where = [];
    $params = [];
    if ($status) {
        $where[] = 'status = ?';
        $params[] = $status;
    }
    if ($search) {
        $where[] = '(order_number LIKE ? OR customer_email LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    $sql = 'SELECT * FROM orders' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC';
    return db_select($sql, $params);
}

function order_find(int $id): ?array
{
    return db_first('SELECT * FROM orders WHERE id = ?', [$id]);
}

function order_by_number(string $number): ?array
{
    return db_first('SELECT * FROM orders WHERE order_number = ?', [$number]);
}

function order_items(int $orderId): array
{
    return db_select('SELECT * FROM order_items WHERE order_id = ?', [$orderId]);
}

function order_number(): string
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function create_order_from_cart(array $checkout): int
{
    $items = cart_items();
    if (!$items) {
        throw new RuntimeException('Cart is empty.');
    }

    $subtotal = cart_subtotal_cents();
    $coupon = cart_coupon_code() ? coupon_by_code((string) cart_coupon_code()) : null;
    $discount = coupon_discount_cents($coupon, $subtotal);
    $shipping = cart_requires_shipping() ? (int) setting_get('shipping_flat_rate_cents', 0) : 0;
    $taxRate = (float) setting_get('tax_rate_percent', 0);
    $tax = (int) round(max(0, $subtotal - $discount) * ($taxRate / 100));
    $total = max(0, $subtotal - $discount + $shipping + $tax);
    $currency = setting_get('currency', 'USD');

    db_execute(
        'INSERT INTO orders (order_number, customer_id, customer_email, status, payment_status, fulfillment_status, subtotal_cents, discount_cents, shipping_cents, tax_cents, total_cents, currency, billing_address, shipping_address, order_notes, created_at, updated_at) VALUES (?, NULL, ?, "pending", "unpaid", "unfulfilled", ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
        [order_number(), $checkout['email'], $subtotal, $discount, $shipping, $tax, $total, $currency, json_encode($checkout['billing'] ?? []), json_encode($checkout['shipping'] ?? []), $checkout['notes'] ?? null]
    );
    $orderId = (int) db()->lastInsertId();

    foreach ($items as $item) {
        $product = $item['product'];
        db_execute(
            'INSERT INTO order_items (order_id, product_id, product_variant_id, title, sku, quantity, unit_price_cents, total_cents, product_snapshot) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?)',
            [$orderId, (int) $product['id'], $product['title'], $product['sku'], $item['quantity'], $item['unit_price_cents'], $item['total_cents'], json_encode($product)]
        );
    }

    if ($coupon) {
        db_execute('INSERT INTO coupon_redemptions (coupon_id, order_id, customer_id, redeemed_at) VALUES (?, ?, NULL, NOW())', [(int) $coupon['id'], $orderId]);
    }

    return $orderId;
}

function order_mark_paid_by_stripe(string $checkoutSessionId, ?string $paymentIntentId = null): void
{
    db_execute('UPDATE orders SET status = "paid", payment_status = "paid", stripe_payment_intent_id = COALESCE(?, stripe_payment_intent_id), paid_at = NOW(), updated_at = NOW() WHERE stripe_checkout_session_id = ?', [$paymentIntentId, $checkoutSessionId]);
}

function order_update_status(int $id, array $data): void
{
    db_execute('UPDATE orders SET status = ?, payment_status = ?, fulfillment_status = ?, tracking_number = ?, internal_notes = ?, updated_at = NOW() WHERE id = ?', [$data['status'], $data['payment_status'], $data['fulfillment_status'], $data['tracking_number'] ?? null, $data['internal_notes'] ?? null, $id]);
}
