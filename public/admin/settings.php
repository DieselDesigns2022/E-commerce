<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Setting.php';

require_admin();
$message = null;
$fields = [
    'store_name' => 'Store Name',
    'store_email' => 'Store Email',
    'currency' => 'Currency',
    'timezone' => 'Timezone',
    'announcement_text' => 'Announcement Bar Text',
    'hero_headline' => 'Homepage Headline',
    'hero_subtitle' => 'Homepage Subtitle',
    'hero_cta_text' => 'Hero Button Text',
    'hero_cta_url' => 'Hero Button URL',
    'brand_primary_color' => 'Primary Brand Color',
    'footer_text' => 'Footer Text',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($fields as $key => $label) {
        setting_set($key, $_POST[$key] ?? '');
    }
    $message = 'Settings saved.';
}

$settings = settings_all();
$title = 'Store Settings';
$nav = 'settings';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if ($message): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <?php foreach ($fields as $key => $label): ?>
        <label class="<?= in_array($key, ['hero_subtitle', 'announcement_text', 'footer_text'], true) ? 'full' : '' ?>"><?= e($label) ?>
            <?php if (in_array($key, ['hero_subtitle', 'announcement_text', 'footer_text'], true)): ?>
                <textarea name="<?= e($key) ?>" rows="3"><?= e($settings[$key] ?? '') ?></textarea>
            <?php else: ?>
                <input name="<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>">
            <?php endif; ?>
        </label>
    <?php endforeach; ?>
    <div class="full actions"><button type="submit">Save Settings</button></div>
</form>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
