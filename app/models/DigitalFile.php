<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function digital_files_all(): array
{
    return db_select('SELECT df.*, p.title AS product_title FROM digital_files df INNER JOIN products p ON p.id = df.product_id ORDER BY df.created_at DESC');
}

function digital_file_create(int $productId, string $originalName, string $storagePath, int $size, ?int $downloadLimit, ?int $expiresAfterDays): int
{
    db_execute('INSERT INTO digital_files (product_id, original_name, storage_path, file_size, download_limit, expires_after_days, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())', [$productId, $originalName, $storagePath, $size, $downloadLimit, $expiresAfterDays]);
    return (int) db()->lastInsertId();
}
