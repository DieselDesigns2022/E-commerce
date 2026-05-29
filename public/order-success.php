<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/models/Order.php';
require_once dirname(__DIR__) . '/app/models/Cart.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$order = isset($_GET['order_id']) ? order_find((int) $_GET['order_id']) : null;
if ($order && isset($_GET['manual'])) {
    order_mark_paid((int) $order['id'], 'manual-success');
    $order = order_find((int) $order['id']);
}
if ($order) { cart_clear(); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Order Received</title><link rel="stylesheet" href="/assets/storefront.css"></head><body><main class="section"><div class="container"><h1>Thank you</h1><?php if ($order): ?><p>Your order <strong><?= e($order['order_number']) ?></strong> was received.</p><p>Status: <strong><?= e($order['status']) ?></strong></p><p>Payment is confirmed by Stripe webhook in production. Local manual-success mode marks this test order paid.</p><?php else: ?><p>Order details are unavailable.</p><?php endif; ?><a class="button" href="/shop.php">Continue shopping</a></div></main></body></html>
