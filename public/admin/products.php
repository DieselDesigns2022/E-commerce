<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Product.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive') {
    verify_csrf();
    product_archive((int) $_POST['id']);
    redirect('/admin/products.php?archived=1');
}

$products = products_all_admin();
$title = 'Products';
$nav = 'products';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Product saved.</div><?php endif; ?>
<?php if (isset($_GET['archived'])): ?><div class="alert success">Product archived.</div><?php endif; ?>
<div class="actions" style="margin-bottom:16px">
    <a class="button" href="/admin/products-edit.php">Add Product</a>
</div>
<div class="card table-wrap">
    <table class="admin-table">
        <thead><tr><th>Title</th><th>SKU</th><th>Type</th><th>Status</th><th>Price</th><th>Inventory</th><th>Categories</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= e($product['title']) ?></td>
                <td><?= e($product['sku']) ?></td>
                <td><?= e($product['product_type']) ?></td>
                <td><?= e($product['status']) ?></td>
                <td><?= e(money((int) $product['price_cents'])) ?></td>
                <td><?= $product['track_inventory'] ? e($product['inventory_quantity'] ?? '0') : 'Not tracked' ?></td>
                <td><?= e($product['category_names'] ?? '') ?></td>
                <td class="actions">
                    <a class="button secondary" href="/admin/products-edit.php?id=<?= (int) $product['id'] ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Archive this product?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="archive">
                        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                        <button class="button danger" type="submit">Archive</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?><tr><td colspan="8">No products yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
