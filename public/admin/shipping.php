<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Shipping.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    shipping_settings_save($_POST);
    redirect('/admin/shipping.php?saved=1');
}
$settings = settings_all();
$title = 'Shipping and Tax';
$nav = 'shipping';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Shipping and tax settings saved.</div><?php endif; ?>
<form method="post" class="card form-grid"><?= csrf_field() ?>
<label>Flat Rate Shipping <input name="shipping_flat_rate" type="number" step="0.01" min="0" value="<?= e(number_format(((int) ($settings['shipping_flat_rate_cents'] ?? 0)) / 100, 2, '.', '')) ?>"></label>
<label>Free Shipping Threshold <input name="shipping_free_threshold" type="number" step="0.01" min="0" value="<?= e(number_format(((int) ($settings['shipping_free_threshold_cents'] ?? 0)) / 100, 2, '.', '')) ?>"></label>
<label class="checkbox-row"><input type="checkbox" name="local_pickup_enabled" value="1" <?= !empty($settings['local_pickup_enabled']) ? 'checked' : '' ?>> Local pickup enabled</label>
<label class="checkbox-row"><input type="checkbox" name="tax_enabled" value="1" <?= !empty($settings['tax_enabled']) ? 'checked' : '' ?>> Tax enabled</label>
<label>Manual Tax Percentage <input name="tax_rate_percent" type="number" step="0.001" min="0" value="<?= e($settings['tax_rate_percent'] ?? '0') ?>"></label>
<div class="full actions"><button type="submit">Save</button></div></form>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
