<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/stripe.php';

function checkout_create_pending_order(array $post): int
{
    return create_order_from_cart([
        'email' => trim((string) $post['email']),
        'billing' => [
            'name' => trim((string) ($post['billing_name'] ?? '')),
            'address1' => trim((string) ($post['billing_address1'] ?? '')),
            'city' => trim((string) ($post['billing_city'] ?? '')),
            'region' => trim((string) ($post['billing_region'] ?? '')),
            'postal_code' => trim((string) ($post['billing_postal_code'] ?? '')),
            'country' => trim((string) ($post['billing_country'] ?? 'US')),
        ],
        'shipping' => [
            'name' => trim((string) ($post['shipping_name'] ?? '')),
            'address1' => trim((string) ($post['shipping_address1'] ?? '')),
            'city' => trim((string) ($post['shipping_city'] ?? '')),
            'region' => trim((string) ($post['shipping_region'] ?? '')),
            'postal_code' => trim((string) ($post['shipping_postal_code'] ?? '')),
            'country' => trim((string) ($post['shipping_country'] ?? 'US')),
        ],
        'notes' => trim((string) ($post['notes'] ?? '')),
    ]);
}
