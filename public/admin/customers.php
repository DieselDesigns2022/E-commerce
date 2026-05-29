<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Customer.php';

require_admin();
$customers = customers_all($_GET['q'] ?? null);
$title = 'Customers';
$nav = 'customers';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<form class="card form-grid" method="get" style="margin-bottom:18px"><label>Search <input name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Email or name"></label><div class="actions"><button type="submit">Search</button></div></form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Created</th></tr></thead><tbody><?php foreach ($customers as $customer): ?><tr><td><?= e(trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))) ?></td><td><?= e($customer['email']) ?></td><td><?= e($customer['phone'] ?? '') ?></td><td><?= e($customer['created_at']) ?></td></tr><?php endforeach; ?><?php if (!$customers): ?><tr><td colspan="4">No customers yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
