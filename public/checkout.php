<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/controllers/CheckoutController.php';
require_once dirname(__DIR__) . '/app/models/Cart.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/stripe.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$settings = settings_all();
$items = cart_items();
if (!$items) { redirect('/cart.php'); }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $orderId = checkout_create_pending_order($_POST);
        $currency = strtolower((string) setting_get('currency', 'USD'));
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = ['name' => $item['product']['title'], 'unit_amount' => $item['unit_price_cents'], 'quantity' => $item['quantity'], 'currency' => $currency];
        }
        $session = stripe_create_checkout_session($orderId, $lineItems, url('/order-success.php'), url('/checkout.php'));
        db_execute('UPDATE orders SET stripe_checkout_session_id = ?, updated_at = NOW() WHERE id = ?', [$session['id'] ?? null, $orderId]);
        if (!empty($session['url'])) { redirect($session['url']); }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Checkout | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container"><h1>Checkout</h1><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post" class="card form-grid"><?= csrf_field() ?><label>Email <input type="email" required name="email"></label><label>Billing Name <input required name="billing_name"></label><label>Billing Address <input name="billing_address1"></label><label>Billing City <input name="billing_city"></label><label>Billing State/Region <input name="billing_region"></label><label>Billing Postal Code <input name="billing_postal_code"></label><?php if (cart_requires_shipping()): ?><label>Shipping Name <input name="shipping_name"></label><label>Shipping Address <input name="shipping_address1"></label><label>Shipping City <input name="shipping_city"></label><label>Shipping State/Region <input name="shipping_region"></label><label>Shipping Postal Code <input name="shipping_postal_code"></label><?php endif; ?><label class="full">Order Notes <textarea name="notes" rows="4"></textarea></label><div class="full actions"><button class="button" type="submit">Continue to Payment</button></div></form></div></main></body></html>
