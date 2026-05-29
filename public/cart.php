<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/models/Cart.php';
require_once dirname(__DIR__) . '/app/models/Coupon.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/helpers.php';

if (isset($_GET['add'])) {
    cart_add((int) $_GET['add'], max(1, (int) ($_GET['qty'] ?? 1)));
    redirect('/cart.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'update') {
        foreach ($_POST['qty'] ?? [] as $productId => $quantity) {
            cart_update((int) $productId, (int) $quantity);
        }
    }
    if (($_POST['action'] ?? '') === 'coupon') {
        cart_set_coupon($_POST['coupon_code'] ?? null);
    }
    redirect('/cart.php');
}
$settings = settings_all();
$items = cart_items();
$subtotal = cart_subtotal_cents();
$coupon = cart_coupon_code() ? coupon_by_code((string) cart_coupon_code()) : null;
$discount = coupon_discount_cents($coupon, $subtotal);
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cart | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/shop.php">Shop</a><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container"><h1>Your Cart</h1><?php if ($items): ?><form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="update"><table class="admin-table"><thead><tr><th>Product</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['product']['title']) ?></td><td><input style="max-width:90px" type="number" min="0" name="qty[<?= (int) $item['product']['id'] ?>]" value="<?= (int) $item['quantity'] ?>"></td><td><?= e(money((int) $item['total_cents'])) ?></td></tr><?php endforeach; ?></tbody></table><div class="actions" style="margin-top:16px"><button class="button" type="submit">Update Cart</button></div></form><form method="post" class="card form-grid" style="margin-top:18px"><?= csrf_field() ?><input type="hidden" name="action" value="coupon"><label>Coupon Code <input name="coupon_code" value="<?= e(cart_coupon_code() ?? '') ?>"></label><div class="actions"><button class="button" type="submit">Apply</button></div></form><section class="card" style="margin-top:18px"><p>Subtotal: <strong><?= e(money($subtotal)) ?></strong></p><p>Discount: <strong>-<?= e(money($discount)) ?></strong></p><p>Estimated total before shipping/tax: <strong><?= e(money(max(0, $subtotal - $discount))) ?></strong></p><a class="button" href="/checkout.php">Proceed to checkout</a></section><?php else: ?><p>Your cart is empty.</p><a class="button" href="/shop.php">Shop products</a><?php endif; ?></div></main>
<footer class="site-footer"><div class="container"><?= e($settings['footer_text'] ?? ('© ' . date('Y') . ' ' . $storeName)) ?></div></footer></body></html>
