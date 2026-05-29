<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once __DIR__ . '/Product.php';

function cart_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('ecommerce_customer');
        session_start();
    }
    $_SESSION['cart'] ??= [];
}

function cart_items(): array
{
    cart_start();
    $items = [];
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $product = product_find((int) $productId);
        if (!$product || $product['status'] !== 'active') {
            continue;
        }
        $unit = (int) ($product['sale_price_cents'] ?? $product['price_cents']);
        $items[] = [
            'product' => $product,
            'quantity' => (int) $quantity,
            'unit_price_cents' => $unit,
            'total_cents' => $unit * (int) $quantity,
        ];
    }
    return $items;
}

function cart_add(int $productId, int $quantity = 1): void
{
    cart_start();
    $quantity = max(1, $quantity);
    $_SESSION['cart'][$productId] = (int) ($_SESSION['cart'][$productId] ?? 0) + $quantity;
}

function cart_update(int $productId, int $quantity): void
{
    cart_start();
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $quantity;
}

function cart_clear(): void
{
    cart_start();
    $_SESSION['cart'] = [];
    unset($_SESSION['coupon_code']);
}

function cart_subtotal_cents(): int
{
    return array_sum(array_column(cart_items(), 'total_cents'));
}

function cart_requires_shipping(): bool
{
    foreach (cart_items() as $item) {
        if ($item['product']['product_type'] === 'physical') {
            return true;
        }
    }
    return false;
}

function cart_set_coupon(?string $code): void
{
    cart_start();
    if ($code === null || trim($code) === '') {
        unset($_SESSION['coupon_code']);
        return;
    }
    $_SESSION['coupon_code'] = strtoupper(trim($code));
}

function cart_coupon_code(): ?string
{
    cart_start();
    return $_SESSION['coupon_code'] ?? null;
}
