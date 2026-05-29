<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';
require_once dirname(__DIR__, 2) . '/app/models/Product.php';
require_once dirname(__DIR__, 2) . '/app/models/Category.php';

require_admin();
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = $id ? product_find($id) : null;
if ($id && !$product) {
    http_response_code(404);
    exit('Product not found.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $titleValue = trim((string) ($_POST['title'] ?? ''));
    if ($titleValue === '') {
        $errors[] = 'Title is required.';
    }
    if (!is_numeric($_POST['price'] ?? null) || (float) $_POST['price'] < 0) {
        $errors[] = 'Price must be a valid non-negative number.';
    }

    if (!$errors) {
        $productId = product_save($_POST, $id);
        product_sync_categories($productId, $_POST['category_ids'] ?? []);

        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!empty($_FILES['images']['name'][0])) {
            $existingImages = product_images($productId);
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                if (!is_uploaded_file($tmpName)) {
                    continue;
                }
                $mime = mime_content_type($tmpName) ?: '';
                if (!isset($allowedTypes[$mime])) {
                    $errors[] = 'One image was skipped because its file type is not allowed.';
                    continue;
                }
                $filename = 'product-' . $productId . '-' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mime];
                $destination = dirname(__DIR__) . '/uploads/products/' . $filename;
                if (move_uploaded_file($tmpName, $destination)) {
                    product_add_image($productId, '/uploads/products/' . $filename, count($existingImages) === 0);
                    $existingImages[] = ['path' => $destination];
                }
            }
        }

        redirect('/admin/products.php?saved=1');
    }
}

$categories = categories_all();
$selectedCategoryIds = $id ? array_map(static fn (array $category): int => (int) $category['id'], product_categories($id)) : [];
$title = $id ? 'Edit Product' : 'Add Product';
$nav = 'products';
require dirname(__DIR__, 2) . '/resources/views/admin/header.php';
?>
<?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/product-form.php'; ?>
<?php if ($id): ?>
    <section class="card" style="margin-top:20px">
        <h2>Uploaded Images</h2>
        <div class="product-grid">
            <?php foreach (product_images($id) as $image): ?>
                <div><img style="max-width:160px;border-radius:12px" src="<?= e($image['path']) ?>" alt=""></div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/resources/views/admin/footer.php'; ?>
