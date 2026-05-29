<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/mail.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Coupon.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/DigitalFile.php';
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

function order_activity(int $orderId): array
{
    return db_select('SELECT * FROM activity_logs WHERE subject_type = "order" AND subject_id = ? ORDER BY created_at DESC', [$orderId]);
}

function order_log(int $orderId, string $action, array $properties = [], ?int $adminId = null): void
{
    db_execute(
        'INSERT INTO activity_logs (admin_id, action, subject_type, subject_id, properties, ip_address, created_at) VALUES (?, ?, "order", ?, ?, ?, NOW())',
        [$adminId, $action, $orderId, json_encode($properties), $_SERVER['REMOTE_ADDR'] ?? null]
    );
}

function order_number(): string
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function order_totals_for_cart(): array
{
    $subtotal = cart_subtotal_cents();
    $coupon = cart_coupon_code() ? coupon_by_code((string) cart_coupon_code()) : null;
    $discount = coupon_discount_cents($coupon, $subtotal);
    $shipping = cart_requires_shipping() ? (int) setting_get('shipping_flat_rate_cents', 0) : 0;
    $freeThreshold = (int) setting_get('shipping_free_threshold_cents', 0);
    if ($shipping > 0 && (($freeThreshold > 0 && $subtotal >= $freeThreshold) || coupon_gives_free_shipping($coupon, $subtotal))) {
        $shipping = 0;
    }
    $tax = 0;
    if ((string) setting_get('tax_enabled', '0') === '1') {
        $taxRate = (float) setting_get('tax_rate_percent', 0);
        $tax = (int) round(max(0, $subtotal - $discount) * ($taxRate / 100));
    }
    return [
        'subtotal_cents' => $subtotal,
        'discount_cents' => $discount,
        'shipping_cents' => $shipping,
        'tax_cents' => $tax,
        'total_cents' => max(0, $subtotal - $discount + $shipping + $tax),
        'coupon' => $coupon,
    ];
}

function create_order_from_cart(array $checkout): int
{
    $items = cart_items();
    if (!$items) {
        throw new RuntimeException('Cart is empty.');
    }

    $totals = order_totals_for_cart();
    $currency = setting_get('currency', 'USD');
    $email = strtolower(trim((string) $checkout['email']));
    $customerId = $checkout['customer_id'] ?? null;
    if (!$customerId) {
        $customer = customer_find_by_email($email);
        $customerId = $customer ? (int) $customer['id'] : null;
    }

    db_execute(
        'INSERT INTO orders (order_number, customer_id, customer_email, status, payment_status, fulfillment_status, subtotal_cents, discount_cents, shipping_cents, tax_cents, total_cents, currency, billing_address, shipping_address, order_notes, created_at, updated_at) VALUES (?, ?, ?, "pending", "unpaid", "unfulfilled", ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
        [order_number(), $customerId, $email, $totals['subtotal_cents'], $totals['discount_cents'], $totals['shipping_cents'], $totals['tax_cents'], $totals['total_cents'], $currency, json_encode($checkout['billing'] ?? []), json_encode($checkout['shipping'] ?? []), $checkout['notes'] ?? null]
    );
    $orderId = (int) db()->lastInsertId();

    foreach ($items as $item) {
        $product = $item['product'];
        db_execute(
            'INSERT INTO order_items (order_id, product_id, product_variant_id, title, sku, quantity, unit_price_cents, total_cents, product_snapshot) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?)',
            [$orderId, (int) $product['id'], $product['title'], $product['sku'], $item['quantity'], $item['unit_price_cents'], $item['total_cents'], json_encode($product)]
        );
    }

    if ($totals['coupon']) {
        db_execute('INSERT INTO coupon_redemptions (coupon_id, order_id, customer_id, redeemed_at) VALUES (?, ?, ?, NOW())', [(int) $totals['coupon']['id'], $orderId, $customerId]);
    }
    order_log($orderId, 'created', ['email' => $email, 'total_cents' => $totals['total_cents']]);

    return $orderId;
}

function order_mark_paid(int $orderId, string $source = 'manual', ?string $paymentIntentId = null): void
{
    $order = order_find($orderId);
    if (!$order || $order['payment_status'] === 'paid') {
        return;
    }
    db_execute('UPDATE orders SET status = "paid", payment_status = "paid", stripe_payment_intent_id = COALESCE(?, stripe_payment_intent_id), paid_at = NOW(), updated_at = NOW() WHERE id = ?', [$paymentIntentId, $orderId]);
    grant_downloads_for_order($orderId);
    send_order_download_email($orderId);
    order_log($orderId, 'paid', ['source' => $source, 'payment_intent_id' => $paymentIntentId]);
}

function order_mark_paid_by_stripe(string $checkoutSessionId, ?string $paymentIntentId = null): void
{
    $order = db_first('SELECT * FROM orders WHERE stripe_checkout_session_id = ?', [$checkoutSessionId]);
    if (!$order) {
        return;
    }
    order_mark_paid((int) $order['id'], 'stripe', $paymentIntentId);
}


function send_order_download_email(int $orderId): void
{
    $order = order_find($orderId);
    if (!$order || !$order['customer_id']) {
        return;
    }
    $downloads = downloads_for_customer((int) $order['customer_id']);
    $hasOrderDownload = false;
    foreach ($downloads as $download) {
        if ((int) $download['order_id'] === $orderId) {
            $hasOrderDownload = true;
            break;
        }
    }
    if (!$hasOrderDownload) {
        return;
    }
    send_store_mail($order['customer_email'], 'Your downloads are ready', '<p>Your digital downloads are ready in your account.</p><p><a href="' . e(url('/account/index.php')) . '">View downloads</a></p>');
}

function order_update_status(int $id, array $data, ?int $adminId = null): void
{
    $before = order_find($id);
    db_execute('UPDATE orders SET status = ?, payment_status = ?, fulfillment_status = ?, tracking_number = ?, internal_notes = ?, updated_at = NOW() WHERE id = ?', [$data['status'], $data['payment_status'], $data['fulfillment_status'], $data['tracking_number'] ?? null, $data['internal_notes'] ?? null, $id]);
    if (($before['payment_status'] ?? null) !== 'paid' && $data['payment_status'] === 'paid') {
        db_execute('UPDATE orders SET paid_at = COALESCE(paid_at, NOW()), updated_at = NOW() WHERE id = ?', [$id]);
        grant_downloads_for_order($id);
        send_order_download_email($id);
        order_log($id, 'paid', ['source' => 'admin'], $adminId);
    }
    order_log($id, 'updated', ['status' => $data['status'], 'payment_status' => $data['payment_status'], 'fulfillment_status' => $data['fulfillment_status']], $adminId);
}
