<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/models/Product.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$settings = settings_all();
$products = products_public(8);
$storeName = $settings['store_name'] ?? 'Your Store';
$primaryColor = $settings['brand_primary_color'] ?? '#2563eb';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($storeName) ?></title>
    <link rel="stylesheet" href="/assets/storefront.css">
    <style>:root{--brand:<?= e($primaryColor) ?>}</style>
</head>
<body>
<?php if (!empty($settings['announcement_text'])): ?>
    <div style="background:var(--brand);color:#fff;text-align:center;padding:10px;font-weight:700"><?= e($settings['announcement_text']) ?></div>
<?php endif; ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/"><?= e($storeName) ?></a>
        <nav class="nav">
            <a href="/shop.php">Shop</a>
            <a href="/page.php?slug=about">About</a>
            <a href="/page.php?slug=contact">Contact</a>
            <a href="/cart.php">Cart</a>
        </nav>
    </div>
</header>
<main>
    <section class="hero">
        <div class="container">
            <h1><?= e($settings['hero_headline'] ?? 'Simple ecommerce for small businesses') ?></h1>
            <p><?= e($settings['hero_subtitle'] ?? 'A clean, affordable storefront with essential selling tools built in.') ?></p>
            <a class="button" href="<?= e($settings['hero_cta_url'] ?? '/shop.php') ?>"><?= e($settings['hero_cta_text'] ?? 'Shop now') ?></a>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <?php if (!empty($product['image_path'])): ?>
                            <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['title']) ?>">
                        <?php else: ?>
                            <div class="product-placeholder">No image</div>
                        <?php endif; ?>
                        <div class="product-info">
                            <h3><?= e($product['title']) ?></h3>
                            <p class="price"><?= e(money((int) ($product['sale_price_cents'] ?? $product['price_cents']))) ?></p>
                            <a class="button" href="/product.php?id=<?= (int) $product['id'] ?>">View product</a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$products): ?><p>No products are available yet.</p><?php endif; ?>
            </div>
        </div>
    </section>
</main>
<footer class="site-footer"><div class="container"><?= e($settings['footer_text'] ?? ('© ' . date('Y') . ' ' . $storeName)) ?></div></footer>
</body>
</html>
