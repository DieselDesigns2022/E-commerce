<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Category.php';

require_admin();
$errors = [];
$editing = isset($_GET['id']) ? category_find((int) $_GET['id']) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    if ($name === '') {
        $errors[] = 'Category name is required.';
    } else {
        category_save($_POST, isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null);
        redirect('/admin/categories.php?saved=1');
    }
}

$categories = categories_all();
$title = 'Categories';
$nav = 'categories';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['saved'])): ?><div class="alert success">Category saved.</div><?php endif; ?>
<?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid" style="margin-bottom:20px">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
    <label>Name <input name="name" required value="<?= e($editing['name'] ?? '') ?>"></label>
    <label>Slug <input name="slug" value="<?= e($editing['slug'] ?? '') ?>"></label>
    <label>Image URL <input name="image_path" value="<?= e($editing['image_path'] ?? '') ?>"></label>
    <label>Status <select name="status"><option value="active" <?= ($editing['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option><option value="hidden" <?= ($editing['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option></select></label>
    <label>Sort Order <input type="number" name="sort_order" value="<?= e($editing['sort_order'] ?? '0') ?>"></label>
    <label class="full">Description <textarea name="description" rows="3"><?= e($editing['description'] ?? '') ?></textarea></label>
    <div class="full actions"><button type="submit">Save Category</button></div>
</form>
<div class="card table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Sort</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr><td><?= e($category['name']) ?></td><td><?= e($category['slug']) ?></td><td><?= e($category['status']) ?></td><td><?= (int) $category['sort_order'] ?></td><td><a class="button secondary" href="/admin/categories.php?id=<?= (int) $category['id'] ?>">Edit</a></td></tr>
        <?php endforeach; ?>
        <?php if (!$categories): ?><tr><td colspan="5">No categories yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
