<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';

$customer = require_customer();
$order = order_find((int) ($_GET['id'] ?? 0));
if (!$order || (int) $order['customer_id'] !== (int) $customer['id']) {
    http_response_code(404);
    exit('Order not found.');
}
$items = order_items((int) $order['id']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($order['order_number']) ?></title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><main class="section"><div class="container"><p><a href="/account/index.php">← Back to account</a></p><section class="card"><h1><?= e($order['order_number']) ?></h1><p>Status: <strong><?= e($order['status']) ?></strong></p><p>Payment: <strong><?= e($order['payment_status']) ?></strong></p><p>Total: <strong><?= e(money((int) $order['total_cents'], $order['currency'])) ?></strong></p></section><section class="card" style="margin-top:18px"><h2>Items</h2><table class="admin-table"><thead><tr><th>Product</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['title']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= e(money((int) $item['total_cents'], $order['currency'])) ?></td></tr><?php endforeach; ?></tbody></table></section></div></main></body></html>
