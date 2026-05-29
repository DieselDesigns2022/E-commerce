<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Page.php';

require_admin();
$editing = isset($_GET['id']) ? page_find((int) $_GET['id']) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    page_save($_POST, isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null);
    redirect('/admin/pages.php?saved=1');
}
$pages = pages_all();
$title = 'Pages';
$nav = 'pages';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Page saved.</div><?php endif; ?>
<form method="post" class="card form-grid" style="margin-bottom:18px"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
<label>Title <input name="title" required value="<?= e($editing['title'] ?? '') ?>"></label>
<label>Slug <input name="slug" value="<?= e($editing['slug'] ?? '') ?>"></label>
<label>Status <select name="status"><option value="draft" <?= ($editing['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= ($editing['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option></select></label>
<label class="full">Body / HTML <textarea name="body" rows="10"><?= e($editing['body'] ?? '') ?></textarea></label>
<div class="full actions"><button type="submit">Save Page</button></div></form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>Title</th><th>Slug</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($pages as $page): ?><tr><td><?= e($page['title']) ?></td><td><?= e($page['slug']) ?></td><td><?= e($page['status']) ?></td><td><a class="button secondary" href="/admin/pages.php?id=<?= (int) $page['id'] ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
