<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/controllers/CheckoutController.php';
require_once dirname(__DIR__) . '/app/customer_auth.php';
require_once dirname(__DIR__) . '/app/models/Cart.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/stripe.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$settings = settings_all();
$items = cart_items();
if (!$items) { redirect('/cart.php'); }
$customer = current_customer();
$totals = order_totals_for_cart();
$requiresShipping = cart_requires_shipping();
$errors = [];

customer_auth_start();
if (empty($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!hash_equals($_SESSION['checkout_token'] ?? '', (string) ($_POST['checkout_token'] ?? ''))) {
        $errors[] = 'This checkout form was already submitted. Please review your cart and try again.';
    }
    $errors = array_merge($errors, checkout_validate($_POST, $requiresShipping));
    if (!$errors) {
        try {
            unset($_SESSION['checkout_token']);
            $orderId = checkout_create_pending_order($_POST, $customer);
            $currency = strtolower((string) setting_get('currency', 'USD'));
            $lineItems = [];
            foreach ($items as $item) {
                $lineItems[] = ['name' => $item['product']['title'], 'unit_amount' => $item['unit_price_cents'], 'quantity' => $item['quantity'], 'currency' => $currency];
            }
            $session = stripe_create_checkout_session($orderId, $lineItems, url('/order-success.php'), url('/checkout.php'));
            db_execute('UPDATE orders SET stripe_checkout_session_id = ?, updated_at = NOW() WHERE id = ?', [$session['id'] ?? null, $orderId]);
            if (!empty($session['url'])) { redirect($session['url']); }
        } catch (Throwable $exception) {
            $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
            $errors[] = $exception->getMessage();
        }
    }
}
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Checkout | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"><script>function toggleShipping(){const box=document.querySelector('[name="shipping_same_as_billing"]');const panel=document.getElementById('shipping-fields');if(panel){panel.style.display=box&&box.checked?'none':'grid';}}document.addEventListener('DOMContentLoaded',toggleShipping);</script></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/shop.php">Shop</a><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container"><h1>Checkout</h1><?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?><div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:24px;align-items:start"><form method="post" class="card form-grid"><?= csrf_field() ?><input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>"><label>Email <input type="email" required name="email" value="<?= e($customer['email'] ?? ($_POST['email'] ?? '')) ?>"></label><label>Name <input required name="billing_name" value="<?= e($_POST['billing_name'] ?? trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))) ?>"></label><label>Street Address <input required name="billing_address1" value="<?= e($_POST['billing_address1'] ?? '') ?>"></label><label>Apartment, suite, etc. <input name="billing_address2" value="<?= e($_POST['billing_address2'] ?? '') ?>"></label><label>City <input required name="billing_city" value="<?= e($_POST['billing_city'] ?? '') ?>"></label><label>State / Region <input required name="billing_region" value="<?= e($_POST['billing_region'] ?? '') ?>"></label><label>Postal Code <input required name="billing_postal_code" value="<?= e($_POST['billing_postal_code'] ?? '') ?>"></label><label>Country <input name="billing_country" value="<?= e($_POST['billing_country'] ?? 'US') ?>"></label><?php if ($requiresShipping): ?><input type="hidden" name="shipping_same_as_billing" value="0"><label class="full checkbox-row"><input type="checkbox" name="shipping_same_as_billing" value="1" onchange="toggleShipping()" <?= (($_POST['shipping_same_as_billing'] ?? '1') !== '0') ? 'checked' : '' ?>> Shipping address is the same as billing</label><div id="shipping-fields" class="full form-grid"><label>Shipping Name <input name="shipping_name" value="<?= e($_POST['shipping_name'] ?? '') ?>"></label><label>Shipping Street Address <input name="shipping_address1" value="<?= e($_POST['shipping_address1'] ?? '') ?>"></label><label>Shipping Apartment, suite, etc. <input name="shipping_address2" value="<?= e($_POST['shipping_address2'] ?? '') ?>"></label><label>Shipping City <input name="shipping_city" value="<?= e($_POST['shipping_city'] ?? '') ?>"></label><label>Shipping State / Region <input name="shipping_region" value="<?= e($_POST['shipping_region'] ?? '') ?>"></label><label>Shipping Postal Code <input name="shipping_postal_code" value="<?= e($_POST['shipping_postal_code'] ?? '') ?>"></label><label>Shipping Country <input name="shipping_country" value="<?= e($_POST['shipping_country'] ?? 'US') ?>"></label></div><?php endif; ?><label class="full">Order Notes <textarea name="notes" rows="4"><?= e($_POST['notes'] ?? '') ?></textarea></label><div class="full actions"><button class="button" type="submit">Continue to Payment</button></div></form><aside class="card"><h2>Order Summary</h2><?php foreach ($items as $item): ?><p><?= e($item['product']['title']) ?> × <?= (int) $item['quantity'] ?><br><strong><?= e(money((int) $item['total_cents'])) ?></strong></p><?php endforeach; ?><hr><p>Subtotal: <strong><?= e(money($totals['subtotal_cents'])) ?></strong></p><p>Discount: <strong>-<?= e(money($totals['discount_cents'])) ?></strong></p><p>Shipping: <strong><?= e(money($totals['shipping_cents'])) ?></strong></p><p>Tax: <strong><?= e(money($totals['tax_cents'])) ?></strong></p><p style="font-size:1.2rem">Total: <strong><?= e(money($totals['total_cents'])) ?></strong></p></aside></div></div></main></body></html>
