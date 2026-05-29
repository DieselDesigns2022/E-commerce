<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/auth.php';
require_once dirname(__DIR__, 3) . '/app/models/Setting.php';

$admin = require_admin();
if (($admin['role'] ?? '') !== 'owner') { http_response_code(403); exit('Owner access required.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['license_status','hosting_status','maintenance_mode','software_version'] as $key) {
        setting_set($key, $_POST[$key] ?? '');
    }
    redirect('/admin/super/index.php?saved=1');
}
$settings = settings_all();
$config = require dirname(__DIR__, 3) . '/app/config.php';
$title = 'Owner Tools';
$nav = 'super';
require dirname(__DIR__, 3) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Owner settings saved.</div><?php endif; ?>
<section class="card"><h2>Install Configuration</h2><p><strong>PHP:</strong> <?= e(PHP_VERSION) ?></p><p><strong>Software Version:</strong> <?= e($settings['software_version'] ?? '0.1.0') ?></p><p><strong>Environment:</strong> <?= e($config['app']['env']) ?></p></section>
<form method="post" class="card form-grid" style="margin-top:18px"><?= csrf_field() ?><label>License Status <input name="license_status" value="<?= e($settings['license_status'] ?? 'active') ?>"></label><label>Hosting Status <input name="hosting_status" value="<?= e($settings['hosting_status'] ?? 'active') ?>"></label><label>Maintenance Mode <select name="maintenance_mode"><option value="0" <?= ($settings['maintenance_mode'] ?? '0') === '0' ? 'selected' : '' ?>>Off</option><option value="1" <?= ($settings['maintenance_mode'] ?? '') === '1' ? 'selected' : '' ?>>On</option></select></label><label>Software Version <input name="software_version" value="<?= e($settings['software_version'] ?? '0.1.0') ?>"></label><div class="full actions"><button type="submit">Save Owner Settings</button><a class="button secondary" href="/admin/super/migrations.php">Migration Status</a></div></form>
<?php require dirname(__DIR__, 3) . '/resources/views/admin/footer.php'; ?>
