<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Product.php';
require_once dirname(__DIR__, 2) . '/app/models/Setting.php';

$admin = require_admin();
$settings = settings_all();
$productCount = db_first('SELECT COUNT(*) AS count FROM products')['count'] ?? 0;
$orderCount = db_first('SELECT COUNT(*) AS count FROM orders')['count'] ?? 0;
$pendingOrders = db_first('SELECT COUNT(*) AS count FROM orders WHERE status = "pending"')['count'] ?? 0;
$lowStock = db_first('SELECT COUNT(*) AS count FROM products WHERE track_inventory = 1 AND inventory_quantity <= 5')['count'] ?? 0;
$recentProducts = array_slice(products_all_admin(), 0, 5);
$title = 'Dashboard';
$nav = 'dashboard';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<section class="stats">
    <div class="card stat"><span>Products</span><strong><?= (int) $productCount ?></strong></div>
    <div class="card stat"><span>Total Orders</span><strong><?= (int) $orderCount ?></strong></div>
    <div class="card stat"><span>Pending Orders</span><strong><?= (int) $pendingOrders ?></strong></div>
    <div class="card stat"><span>Low Stock</span><strong><?= (int) $lowStock ?></strong></div>
</section>
<section class="card" style="margin-top:20px">
    <h2><?= e($settings['store_name'] ?? 'Store') ?> setup</h2>
    <p>Welcome, <?= e($admin['name']) ?>. Manage core settings and products now. Cart, checkout, emails, and pages are structured for the next phase.</p>
    <div class="actions">
        <a class="button" href="/admin/products-edit.php">Add product</a>
        <a class="button secondary" href="/admin/settings.php">Store settings</a>
    </div>
</section>
<section class="card" style="margin-top:20px">
    <h2>Recent Products</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead><tr><th>Product</th><th>Status</th><th>Price</th><th>Categories</th></tr></thead>
            <tbody>
            <?php foreach ($recentProducts as $product): ?>
                <tr>
                    <td><?= e($product['title']) ?></td>
                    <td><?= e($product['status']) ?></td>
                    <td><?= e(money((int) $product['price_cents'])) ?></td>
                    <td><?= e($product['category_names'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recentProducts): ?><tr><td colspan="4">No products yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
