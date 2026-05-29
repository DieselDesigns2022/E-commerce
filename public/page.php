<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/db.php';
require_once dirname(__DIR__) . '/app/models/Setting.php';
require_once dirname(__DIR__) . '/app/helpers.php';

$page = db_first('SELECT * FROM pages WHERE slug = ? AND status = "published"', [$_GET['slug'] ?? '']);
if (!$page) {
    http_response_code(404);
    exit('Page not found.');
}
$settings = settings_all();
$storeName = $settings['store_name'] ?? 'Your Store';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($page['title']) ?> | <?= e($storeName) ?></title><link rel="stylesheet" href="/assets/storefront.css"></head><body>
<header class="site-header"><div class="container header-inner"><a class="brand" href="/"><?= e($storeName) ?></a><nav class="nav"><a href="/shop.php">Shop</a><a href="/cart.php">Cart</a></nav></div></header>
<main class="section"><div class="container"><h1><?= e($page['title']) ?></h1><div><?= nl2br(e($page['body'])) ?></div></div></main>
<footer class="site-footer"><div class="container"><?= e($settings['footer_text'] ?? ('© ' . date('Y') . ' ' . $storeName)) ?></div></footer>
</body></html>
