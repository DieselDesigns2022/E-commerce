<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Coupon.php';

require_admin();
$editing = isset($_GET['id']) ? coupon_find((int) $_GET['id']) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    coupon_save($_POST, isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null);
    redirect('/admin/coupons.php?saved=1');
}
$coupons = coupons_all();
$title = 'Coupons';
$nav = 'coupons';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Coupon saved.</div><?php endif; ?>
<form method="post" class="card form-grid" style="margin-bottom:18px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
<label>Code <input name="code" required value="<?= e($editing['code'] ?? '') ?>"></label>
<label>Type <select name="discount_type"><?php foreach (['percent'=>'Percent','fixed'=>'Fixed amount','free_shipping'=>'Free shipping'] as $value=>$label): ?><option value="<?= e($value) ?>" <?= ($editing['discount_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>Discount Value <input name="discount_value" type="number" step="0.01" min="0" value="<?= e(isset($editing['discount_value']) ? ($editing['discount_type'] === 'percent' ? $editing['discount_value'] : number_format((int) $editing['discount_value']/100, 2, '.', '')) : '') ?>"></label>
<label>Minimum Order <input name="minimum_order" type="number" step="0.01" min="0" value="<?= e(isset($editing['minimum_order_cents']) && $editing['minimum_order_cents'] !== null ? number_format((int) $editing['minimum_order_cents']/100, 2, '.', '') : '') ?>"></label>
<label>Starts At <input name="starts_at" type="date" value="<?= e(isset($editing['starts_at']) && $editing['starts_at'] ? substr((string) $editing['starts_at'], 0, 10) : '') ?>"></label>
<label>Ends At <input name="ends_at" type="date" value="<?= e(isset($editing['ends_at']) && $editing['ends_at'] ? substr((string) $editing['ends_at'], 0, 10) : '') ?>"></label>
<label>Usage Limit <input name="usage_limit" type="number" min="0" value="<?= e($editing['usage_limit'] ?? '') ?>"></label>
<label class="checkbox-row"><input type="checkbox" name="is_active" value="1" <?= !isset($editing) || !empty($editing['is_active']) ? 'checked' : '' ?>> Active</label>
<div class="full actions"><button type="submit">Save Coupon</button></div></form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Active</th><th></th></tr></thead><tbody><?php foreach ($coupons as $coupon): ?><tr><td><?= e($coupon['code']) ?></td><td><?= e($coupon['discount_type']) ?></td><td><?= e((string) $coupon['discount_value']) ?></td><td><?= $coupon['is_active'] ? 'Yes' : 'No' ?></td><td><a class="button secondary" href="/admin/coupons.php?id=<?= (int) $coupon['id'] ?>">Edit</a></td></tr><?php endforeach; ?><?php if (!$coupons): ?><tr><td colspan="5">No coupons yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
