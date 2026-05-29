<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function media_all(): array
{
    return db_select('SELECT * FROM media ORDER BY created_at DESC');
}

function media_record(string $path, string $originalName, string $mime, int $size): int
{
    db_execute('INSERT INTO media (disk, path, original_name, mime_type, file_size, created_at) VALUES ("public", ?, ?, ?, ?, NOW())', [$path, $originalName, $mime, $size]);
    return (int) db()->lastInsertId();
}
