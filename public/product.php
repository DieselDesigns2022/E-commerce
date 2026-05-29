<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/models/Product.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$product = product_find((int) ($_GET['id'] ?? 0));
if (!$product || $product['status'] !== 'active') {
    http_response_code(404);
    exit('Product not found.');
}
$images = product_images((int) $product['id']);
$settings = settings_all();
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($product['title']) ?> | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/shop.php">Shop</a><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:32px">
<div><?php if ($images): ?><img style="width:100%;border-radius:18px" src="<?= e($images[0]['path']) ?>" alt="<?= e($product['title']) ?>"><?php else: ?><div class="product-placeholder">No image</div><?php endif; ?></div>
<div><h1><?= e($product['title']) ?></h1><p class="price"><?= e(money((int) ($product['sale_price_cents'] ?? $product['price_cents']))) ?></p><?php if ($product['product_type'] === 'digital'): ?><p><strong>Digital product:</strong> download access is delivered after payment.</p><?php endif; ?><div><?= nl2br(e($product['description'])) ?></div><p style="margin-top:24px"><a class="button" href="/cart.php?add=<?= (int) $product['id'] ?>">Add to cart</a></p></div>
</div></main>
<footer class="site-footer"><div class="container"><?= e($settings['footer_text'] ?? ('© ' . date('Y') . ' ' . $storeName)) ?></div></footer>
</body></html>
