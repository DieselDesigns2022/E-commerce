<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';

require_admin();
$orders = orders_all($_GET['status'] ?? null, $_GET['q'] ?? null);
$title = 'Orders';
$nav = 'orders';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<form class="card form-grid" method="get" style="margin-bottom:18px">
    <label>Status <select name="status"><option value="">All</option><?php foreach (['pending','paid','fulfilled','refunded','cancelled'] as $status): ?><option value="<?= e($status) ?>" <?= ($_GET['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
    <label>Search <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Order number or email"></label>
    <div class="actions"><button type="submit">Filter</button></div>
</form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Date</th><th></th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td><?= e($order['order_number']) ?></td><td><?= e($order['customer_email']) ?></td><td><?= e($order['status']) ?></td><td><?= e($order['payment_status']) ?></td><td><?= e(money((int) $order['total_cents'], $order['currency'])) ?></td><td><?= e($order['created_at']) ?></td><td><a class="button secondary" href="/admin/orders-view.php?id=<?= (int) $order['id'] ?>">View</a></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="7">No orders yet.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
