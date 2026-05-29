<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';
require_once dirname(__DIR__, 2) . '/app/models/DigitalFile.php';

$customer = require_customer();
$orders = customer_orders((int) $customer['id']);
$downloads = downloads_for_customer((int) $customer['id']);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    customer_update_profile((int) $customer['id'], $_POST);
    if (!empty($_POST['password'])) {
        if (strlen((string) $_POST['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($_POST['password'] !== ($_POST['password_confirmation'] ?? '')) {
            $errors[] = 'Password confirmation does not match.';
        } else {
            customer_update_password((int) $customer['id'], (string) $_POST['password']);
        }
    }
    if (!$errors) {
        redirect('/account/index.php?saved=1');
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>My Account</title><link rel="stylesheet" href="/assets/storefront.css"><link rel="stylesheet" href="/assets/admin.css"></head><body><header class="site-header"><div class="container header-inner"><a class="brand" href="/">My Account</a><nav class="nav"><a href="/shop.php">Shop</a><a href="/cart.php">Cart</a><a href="/account/logout.php">Logout</a></nav></div></header><main class="section"><div class="container"><h1>My Account</h1><?php if (isset($_GET['saved'])): ?><div class="alert success">Profile updated.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?><section class="card" style="margin-bottom:20px"><h2>Orders</h2><table class="admin-table"><thead><tr><th>Order</th><th>Status</th><th>Total</th><th>Date</th><th></th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= e($order['order_number']) ?></td><td><?= e($order['status']) ?></td><td><?= e(money((int) $order['total_cents'], $order['currency'])) ?></td><td><?= e($order['created_at']) ?></td><td><a class="button secondary" href="/account/order.php?id=<?= (int) $order['id'] ?>">View</a></td></tr><?php endforeach; ?><?php if (!$orders): ?><tr><td colspan="5">No orders yet.</td></tr><?php endif; ?></tbody></table></section><section class="card" style="margin-bottom:20px"><h2>Downloads</h2><table class="admin-table"><thead><tr><th>Product</th><th>File</th><th>Order</th><th>Downloads</th><th></th></tr></thead><tbody><?php foreach ($downloads as $download): ?><tr><td><?= e($download['product_title']) ?></td><td><?= e($download['original_name']) ?></td><td><?= e($download['order_number']) ?></td><td><?= (int) $download['download_count'] ?><?= $download['download_limit'] !== null ? ' / ' . (int) $download['download_limit'] : '' ?></td><td><?php if (download_can_use($download)): ?><a class="button" href="/download/file.php?id=<?= (int) $download['id'] ?>&signature=<?= e(download_public_token((int) $download['id'])) ?>">Download</a><?php else: ?>Unavailable<?php endif; ?></td></tr><?php endforeach; ?><?php if (!$downloads): ?><tr><td colspan="5">No downloads available.</td></tr><?php endif; ?></tbody></table></section><form method="post" class="card form-grid"><?= csrf_field() ?><h2 class="full">Profile</h2><label>First Name <input name="first_name" value="<?= e($customer['first_name'] ?? '') ?>"></label><label>Last Name <input name="last_name" value="<?= e($customer['last_name'] ?? '') ?>"></label><label>Phone <input name="phone" value="<?= e($customer['phone'] ?? '') ?>"></label><label class="checkbox-row"><input type="checkbox" name="accepts_marketing" value="1" <?= !empty($customer['accepts_marketing']) ? 'checked' : '' ?>> Email me updates and offers</label><label>Password <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password"></label><label>Confirm Password <input type="password" name="password_confirmation" minlength="8"></label><div class="full actions"><button type="submit">Update Profile</button></div></form></div></main></body></html>
