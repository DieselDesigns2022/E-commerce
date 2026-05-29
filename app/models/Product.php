<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

function products_all_admin(): array
{
    return db_select('SELECT p.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ", ") AS category_names FROM products p LEFT JOIN product_categories pc ON pc.product_id = p.id LEFT JOIN categories c ON c.id = pc.category_id GROUP BY p.id ORDER BY p.created_at DESC');
}

function products_public(int $limit = 12): array
{
    return db_select('SELECT p.*, pi.path AS image_path FROM products p LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1 WHERE p.status = "active" ORDER BY p.created_at DESC LIMIT ' . (int) $limit);
}

function product_find(int $id): ?array
{
    return db_first('SELECT * FROM products WHERE id = ?', [$id]);
}

function product_categories(int $productId): array
{
    return db_select('SELECT c.* FROM categories c INNER JOIN product_categories pc ON pc.category_id = c.id WHERE pc.product_id = ? ORDER BY c.name', [$productId]);
}

function product_images(int $productId): array
{
    return db_select('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$productId]);
}

function product_save(array $data, ?int $id = null): int
{
    $slug = $data['slug'] ?: slugify($data['title']);
    $priceCents = (int) round(((float) $data['price']) * 100);
    $salePriceCents = $data['sale_price'] !== '' ? (int) round(((float) $data['sale_price']) * 100) : null;
    $inventoryQuantity = $data['inventory_quantity'] !== '' ? (int) $data['inventory_quantity'] : null;

    if ($id) {
        db_execute(
            'UPDATE products SET title = ?, slug = ?, description = ?, price_cents = ?, sale_price_cents = ?, sku = ?, inventory_quantity = ?, track_inventory = ?, product_type = ?, status = ?, updated_at = NOW() WHERE id = ?',
            [$data['title'], $slug, $data['description'], $priceCents, $salePriceCents, $data['sku'], $inventoryQuantity, !empty($data['track_inventory']) ? 1 : 0, $data['product_type'], $data['status'], $id]
        );
        return $id;
    }

    db_execute(
        'INSERT INTO products (title, slug, description, price_cents, sale_price_cents, sku, inventory_quantity, track_inventory, product_type, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
        [$data['title'], $slug, $data['description'], $priceCents, $salePriceCents, $data['sku'], $inventoryQuantity, !empty($data['track_inventory']) ? 1 : 0, $data['product_type'], $data['status']]
    );

    return (int) db()->lastInsertId();
}

function product_sync_categories(int $productId, array $categoryIds): void
{
    db_execute('DELETE FROM product_categories WHERE product_id = ?', [$productId]);
    foreach ($categoryIds as $categoryId) {
        db_execute('INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (?, ?)', [$productId, (int) $categoryId]);
    }
}

function product_archive(int $id): void
{
    db_execute('UPDATE products SET status = "hidden", updated_at = NOW() WHERE id = ?', [$id]);
}

function product_add_image(int $productId, string $path, bool $primary = false): void
{
    if ($primary) {
        db_execute('UPDATE product_images SET is_primary = 0 WHERE product_id = ?', [$productId]);
    }

    db_execute('INSERT INTO product_images (product_id, path, alt_text, is_primary, sort_order, created_at) VALUES (?, ?, ?, ?, 0, NOW())', [$productId, $path, '', $primary ? 1 : 0]);
}

function products_search(array $filters = []): array
{
    $where = ['p.status = "active"'];
    $params = [];
    $join = 'LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1';
    if (!empty($filters['category_id'])) {
        $join .= ' INNER JOIN product_categories pc_filter ON pc_filter.product_id = p.id';
        $where[] = 'pc_filter.category_id = ?';
        $params[] = (int) $filters['category_id'];
    }
    if (!empty($filters['q'])) {
        $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
        $params[] = '%' . $filters['q'] . '%';
        $params[] = '%' . $filters['q'] . '%';
    }
    $sort = match ($filters['sort'] ?? 'newest') {
        'price_asc' => 'COALESCE(p.sale_price_cents, p.price_cents) ASC',
        'price_desc' => 'COALESCE(p.sale_price_cents, p.price_cents) DESC',
        'name_asc' => 'p.title ASC',
        default => 'p.created_at DESC',
    };
    return db_select('SELECT p.*, pi.path AS image_path FROM products p ' . $join . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $sort . ' LIMIT 96', $params);
}
