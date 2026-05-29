<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/DigitalFile.php';
require_once dirname(__DIR__, 2) . '/app/models/Product.php';

require_admin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $filename = 'digital-' . bin2hex(random_bytes(12)) . '.bin';
        $destination = dirname(__DIR__, 2) . '/storage/digital-files/' . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            digital_file_create((int) $_POST['product_id'], $_FILES['file']['name'], 'storage/digital-files/' . $filename, (int) $_FILES['file']['size'], $_POST['download_limit'] !== '' ? (int) $_POST['download_limit'] : null, $_POST['expires_after_days'] !== '' ? (int) $_POST['expires_after_days'] : null);
            redirect('/admin/digital-files.php?uploaded=1');
        }
    } else {
        $errors[] = 'Choose a file to upload.';
    }
}
$files = digital_files_all();
$products = products_all_admin();
$title = 'Digital Files';
$nav = 'downloads';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['uploaded'])): ?><div class="alert success">Digital file attached.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="card form-grid" style="margin-bottom:18px"><?= csrf_field() ?><label>Product <select name="product_id" required><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>"><?= e($product['title']) ?></option><?php endforeach; ?></select></label><label>File <input type="file" name="file" required></label><label>Download Limit <input type="number" name="download_limit" min="0"></label><label>Expires After Days <input type="number" name="expires_after_days" min="0"></label><div class="full actions"><button type="submit">Attach File</button></div></form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>Product</th><th>File</th><th>Limit</th><th>Expires Days</th></tr></thead><tbody><?php foreach ($files as $file): ?><tr><td><?= e($file['product_title']) ?></td><td><?= e($file['original_name']) ?></td><td><?= e($file['download_limit'] ?? 'Unlimited') ?></td><td><?= e($file['expires_after_days'] ?? 'Never') ?></td></tr><?php endforeach; ?><?php if (!$files): ?><tr><td colspan="4">No digital files yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
