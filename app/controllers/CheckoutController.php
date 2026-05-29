<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/Order.php';
require_once dirname(__DIR__) . '/stripe.php';

function checkout_create_pending_order(array $post, ?array $customer = null): int
{
    $billing = [
        'name' => trim((string) ($post['billing_name'] ?? '')),
        'address1' => trim((string) ($post['billing_address1'] ?? '')),
        'address2' => trim((string) ($post['billing_address2'] ?? '')),
        'city' => trim((string) ($post['billing_city'] ?? '')),
        'region' => trim((string) ($post['billing_region'] ?? '')),
        'postal_code' => trim((string) ($post['billing_postal_code'] ?? '')),
        'country' => trim((string) ($post['billing_country'] ?? 'US')),
    ];
    $shipping = !empty($post['shipping_same_as_billing']) ? $billing : [
        'name' => trim((string) ($post['shipping_name'] ?? '')),
        'address1' => trim((string) ($post['shipping_address1'] ?? '')),
        'address2' => trim((string) ($post['shipping_address2'] ?? '')),
        'city' => trim((string) ($post['shipping_city'] ?? '')),
        'region' => trim((string) ($post['shipping_region'] ?? '')),
        'postal_code' => trim((string) ($post['shipping_postal_code'] ?? '')),
        'country' => trim((string) ($post['shipping_country'] ?? 'US')),
    ];

    return create_order_from_cart([
        'customer_id' => $customer['id'] ?? null,
        'email' => trim((string) ($customer['email'] ?? $post['email'])),
        'billing' => $billing,
        'shipping' => $shipping,
        'notes' => trim((string) ($post['notes'] ?? '')),
    ]);
}

function checkout_validate(array $post, bool $requiresShipping): array
{
    $errors = [];
    if (!filter_var($post['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    foreach (['billing_name' => 'name', 'billing_address1' => 'street address', 'billing_city' => 'city', 'billing_region' => 'state/region', 'billing_postal_code' => 'postal code'] as $field => $label) {
        if (trim((string) ($post[$field] ?? '')) === '') {
            $errors[] = 'Please enter your ' . $label . '.';
        }
    }
    if ($requiresShipping && empty($post['shipping_same_as_billing'])) {
        foreach (['shipping_name' => 'shipping name', 'shipping_address1' => 'shipping street address', 'shipping_city' => 'shipping city', 'shipping_region' => 'shipping state/region', 'shipping_postal_code' => 'shipping postal code'] as $field => $label) {
            if (trim((string) ($post[$field] ?? '')) === '') {
                $errors[] = 'Please enter your ' . $label . '.';
            }
        }
    }
    return $errors;
}
