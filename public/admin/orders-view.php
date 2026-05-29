<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';

$admin = require_admin();
$order = order_find((int) ($_GET['id'] ?? 0));
if (!$order) { http_response_code(404); exit('Order not found.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    order_update_status((int) $order['id'], $_POST, (int) $admin['id']);
    redirect('/admin/orders-view.php?id=' . (int) $order['id'] . '&saved=1');
}
$items = order_items((int) $order['id']);
$activity = order_activity((int) $order['id']);
$title = 'Order ' . $order['order_number'];
$nav = 'orders';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Order updated.</div><?php endif; ?>
<section class="card"><h2>Order Details</h2><p><strong>Email:</strong> <?= e($order['customer_email']) ?></p><p><strong>Total:</strong> <?= e(money((int) $order['total_cents'], $order['currency'])) ?></p></section>
<section class="card" style="margin-top:18px"><h2>Items</h2><table class="admin-table"><thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['title']) ?></td><td><?= (int) $item['quantity'] ?></td><td><?= e(money((int) $item['unit_price_cents'], $order['currency'])) ?></td><td><?= e(money((int) $item['total_cents'], $order['currency'])) ?></td></tr><?php endforeach; ?></tbody></table></section>
<section class="card" style="margin-top:18px"><h2>Timeline</h2><?php foreach ($activity as $event): ?><p><strong><?= e($event['action']) ?></strong> — <?= e($event['created_at']) ?><br><small><?= e($event['properties'] ?? '') ?></small></p><?php endforeach; ?><?php if (!$activity): ?><p>No activity yet.</p><?php endif; ?></section>
<form method="post" class="card form-grid" style="margin-top:18px"><?= csrf_field() ?>
<label>Order Status <select name="status"><?php foreach (['pending','paid','fulfilled','refunded','cancelled'] as $status): ?><option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
<label>Payment Status <select name="payment_status"><?php foreach (['unpaid','paid','refunded','failed'] as $status): ?><option value="<?= e($status) ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
<label>Fulfillment <select name="fulfillment_status"><?php foreach (['unfulfilled','fulfilled','partially_fulfilled'] as $status): ?><option value="<?= e($status) ?>" <?= $order['fulfillment_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></label>
<label>Tracking Number <input name="tracking_number" value="<?= e($order['tracking_number'] ?? '') ?>"></label>
<label class="full">Internal Notes <textarea name="internal_notes" rows="4"><?= e($order['internal_notes'] ?? '') ?></textarea></label>
<div class="full actions"><button type="submit">Save Order</button></div></form>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
