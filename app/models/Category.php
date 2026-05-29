<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

function categories_all(): array
{
    return db_select('SELECT * FROM categories ORDER BY sort_order, name');
}

function category_find(int $id): ?array
{
    return db_first('SELECT * FROM categories WHERE id = ?', [$id]);
}

function category_save(array $data, ?int $id = null): int
{
    $slug = $data['slug'] ?: slugify($data['name']);
    $params = [$data['name'], $slug, $data['description'] ?? '', $data['image_path'] ?? null, $data['status'] ?? 'active', (int) ($data['sort_order'] ?? 0)];
    if ($id) {
        db_execute('UPDATE categories SET name = ?, slug = ?, description = ?, image_path = ?, status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?', [...$params, $id]);
        return $id;
    }
    db_execute('INSERT INTO categories (name, slug, description, image_path, status, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())', $params);
    return (int) db()->lastInsertId();
}

function category_save_quick(string $name): int
{
    return category_save(['name' => $name, 'slug' => '', 'description' => '', 'image_path' => null, 'status' => 'active', 'sort_order' => 0]);
}
