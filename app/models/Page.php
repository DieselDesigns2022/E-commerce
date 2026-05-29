<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

function pages_all(): array
{
    return db_select('SELECT * FROM pages ORDER BY title');
}

function page_find(int $id): ?array
{
    return db_first('SELECT * FROM pages WHERE id = ?', [$id]);
}

function page_save(array $data, ?int $id = null): int
{
    $slug = $data['slug'] ?: slugify($data['title']);
    if ($id) {
        db_execute('UPDATE pages SET title = ?, slug = ?, body = ?, status = ?, updated_at = NOW() WHERE id = ?', [$data['title'], $slug, $data['body'], $data['status'], $id]);
        return $id;
    }
    db_execute('INSERT INTO pages (title, slug, body, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [$data['title'], $slug, $data['body'], $data['status']]);
    return (int) db()->lastInsertId();
}
