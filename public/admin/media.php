<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Media.php';

require_admin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','application/pdf'=>'pdf'];
    if (!empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $mime = mime_content_type($_FILES['file']['tmp_name']) ?: '';
        if (!isset($allowed[$mime])) {
            $errors[] = 'File type is not allowed.';
        } else {
            $filename = 'media-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
            $destination = dirname(__DIR__) . '/uploads/media/' . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                media_record('/uploads/media/' . $filename, $_FILES['file']['name'], $mime, (int) $_FILES['file']['size']);
                redirect('/admin/media.php?uploaded=1');
            }
        }
    }
}
$media = media_all();
$title = 'Media Library';
$nav = 'media';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php if (isset($_GET['uploaded'])): ?><div class="alert success">File uploaded.</div><?php endif; ?><?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="card form-grid" style="margin-bottom:18px"><?= csrf_field() ?><label>Upload File <input type="file" name="file" required></label><div class="actions"><button type="submit">Upload</button></div></form>
<div class="card table-wrap"><table class="admin-table"><thead><tr><th>File</th><th>Type</th><th>Size</th><th>URL</th></tr></thead><tbody><?php foreach ($media as $file): ?><tr><td><?= e($file['original_name']) ?></td><td><?= e($file['mime_type']) ?></td><td><?= number_format((int) $file['file_size'] / 1024, 1) ?> KB</td><td><a href="<?= e($file['path']) ?>" target="_blank"><?= e($file['path']) ?></a></td></tr><?php endforeach; ?><?php if (!$media): ?><tr><td colspan="4">No media files yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
