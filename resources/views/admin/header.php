<?php require_once dirname(__DIR__, 3) . '/app/helpers.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Admin') ?></title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">Store Admin</div>
        <nav>
            <a<?= active_nav($nav ?? '', 'dashboard') ?> href="/admin/index.php">Dashboard</a>
            <a<?= active_nav($nav ?? '', 'products') ?> href="/admin/products.php">Products</a>
            <a<?= active_nav($nav ?? '', 'categories') ?> href="/admin/categories.php">Categories</a>
            <a<?= active_nav($nav ?? '', 'orders') ?> href="/admin/orders.php">Orders</a>
            <a<?= active_nav($nav ?? '', 'customers') ?> href="/admin/customers.php">Customers</a>
            <a<?= active_nav($nav ?? '', 'coupons') ?> href="/admin/coupons.php">Coupons</a>
            <a<?= active_nav($nav ?? '', 'pages') ?> href="/admin/pages.php">Pages</a>
            <a<?= active_nav($nav ?? '', 'media') ?> href="/admin/media.php">Media</a>
            <a<?= active_nav($nav ?? '', 'downloads') ?> href="/admin/digital-files.php">Digital Files</a>
            <a<?= active_nav($nav ?? '', 'shipping') ?> href="/admin/shipping.php">Shipping/Tax</a>
            <a<?= active_nav($nav ?? '', 'settings') ?> href="/admin/settings.php">Settings</a>
            <a<?= active_nav($nav ?? '', 'super') ?> href="/admin/super/index.php">Owner Tools</a>
            <a href="/admin/logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <header class="admin-topbar">
            <h1><?= e($title ?? 'Admin') ?></h1>
        </header>
