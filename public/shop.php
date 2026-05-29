<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/models/Product.php';
require_once dirname(__DIR__) . '/app/models/Category.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$settings = settings_all();
$categories = categories_all();
$products = products_search(['q' => $_GET['q'] ?? null, 'category_id' => $_GET['category_id'] ?? null, 'sort' => $_GET['sort'] ?? 'newest']);
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Shop | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/shop.php">Shop</a><a href="/account/login.php">Account</a><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container"><h1>Shop</h1><form class="card form-grid" method="get" style="margin-bottom:24px"><label>Search <input name="q" value="<?= e($_GET['q'] ?? '') ?>"></label><label>Category <select name="category_id"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) ($_GET['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label><label>Sort <select name="sort"><?php foreach (['newest'=>'Newest','price_asc'=>'Price low-high','price_desc'=>'Price high-low','name_asc'=>'Name A-Z'] as $value=>$label): ?><option value="<?= e($value) ?>" <?= ($_GET['sort'] ?? 'newest') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label><div class="actions"><button type="submit">Filter</button></div></form><div class="product-grid">
<?php foreach ($products as $product): ?><article class="product-card"><?php if (!empty($product['image_path'])): ?><img src="<?= e($product['image_path']) ?>" alt="<?= e($product['title']) ?>"><?php else: ?><div class="product-placeholder">No image</div><?php endif; ?><div class="product-info"><h2><?= e($product['title']) ?></h2><p class="price"><?= e(money((int) ($product['sale_price_cents'] ?? $product['price_cents']))) ?></p><a class="button" href="/product.php?id=<?= (int) $product['id'] ?>">View</a></div></article><?php endforeach; ?>
<?php if (!$products): ?><p>No products match your filters.</p><?php endif; ?>
</div></div></main>
<footer class="site-footer"><div class="container"><?= e($settings['footer_text'] ?? ('© ' . date('Y') . ' ' . $storeName)) ?></div></footer>
</body></html>
