<?php
$product = $product ?? [];
$selectedCategoryIds = $selectedCategoryIds ?? [];
?>
<form method="post" enctype="multipart/form-data" class="card form-grid">
    <?= csrf_field() ?>
    <label>Title
        <input required name="title" value="<?= e($product['title'] ?? '') ?>">
    </label>
    <label>Slug
        <input name="slug" value="<?= e($product['slug'] ?? '') ?>" placeholder="auto-generated if blank">
    </label>
    <label>Price
        <input required name="price" type="number" step="0.01" min="0" value="<?= e(isset($product['price_cents']) ? number_format((int) $product['price_cents'] / 100, 2, '.', '') : '') ?>">
    </label>
    <label>Sale Price
        <input name="sale_price" type="number" step="0.01" min="0" value="<?= e(isset($product['sale_price_cents']) && $product['sale_price_cents'] !== null ? number_format((int) $product['sale_price_cents'] / 100, 2, '.', '') : '') ?>">
    </label>
    <label>SKU
        <input name="sku" value="<?= e($product['sku'] ?? '') ?>">
    </label>
    <label>Product Type
        <select name="product_type">
            <option value="physical" <?= ($product['product_type'] ?? 'physical') === 'physical' ? 'selected' : '' ?>>Physical</option>
            <option value="digital" <?= ($product['product_type'] ?? '') === 'digital' ? 'selected' : '' ?>>Digital</option>
        </select>
    </label>
    <label>Status
        <select name="status">
            <?php foreach (['active' => 'Active', 'draft' => 'Draft', 'hidden' => 'Hidden'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= ($product['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Inventory Quantity
        <input name="inventory_quantity" type="number" min="0" value="<?= e($product['inventory_quantity'] ?? '') ?>">
    </label>
    <label class="checkbox-row">
        <input name="track_inventory" type="checkbox" value="1" <?= !empty($product['track_inventory']) ? 'checked' : '' ?>> Track inventory
    </label>
    <label>Categories
        <select name="category_ids[]" multiple>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= in_array((int) $category['id'], $selectedCategoryIds, true) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="full">Description
        <textarea name="description" rows="8"><?= e($product['description'] ?? '') ?></textarea>
    </label>
    <label class="full">Product Images
        <input name="images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
        <small>Allowed: JPG, PNG, WebP, GIF.</small>
    </label>
    <div class="full actions">
        <button class="button" type="submit">Save Product</button>
        <a class="button secondary" href="/admin/products.php">Cancel</a>
    </div>
</form>
