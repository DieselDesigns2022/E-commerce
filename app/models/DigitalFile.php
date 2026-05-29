<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function digital_files_all(): array
{
    return db_select('SELECT df.*, p.title AS product_title FROM digital_files df INNER JOIN products p ON p.id = df.product_id ORDER BY df.created_at DESC');
}

function digital_files_for_product(int $productId): array
{
    return db_select('SELECT * FROM digital_files WHERE product_id = ? ORDER BY created_at DESC', [$productId]);
}

function digital_file_find(int $id): ?array
{
    return db_first('SELECT * FROM digital_files WHERE id = ?', [$id]);
}

function digital_file_create(int $productId, string $originalName, string $storagePath, int $size, ?int $downloadLimit, ?int $expiresAfterDays): int
{
    db_execute('INSERT INTO digital_files (product_id, original_name, storage_path, file_size, download_limit, expires_after_days, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())', [$productId, $originalName, $storagePath, $size, $downloadLimit, $expiresAfterDays]);
    return (int) db()->lastInsertId();
}

function grant_downloads_for_order(int $orderId): void
{
    $items = db_select('SELECT product_id FROM order_items WHERE order_id = ? AND product_id IS NOT NULL', [$orderId]);
    $order = db_first('SELECT * FROM orders WHERE id = ?', [$orderId]);
    if (!$order) {
        return;
    }

    foreach ($items as $item) {
        foreach (digital_files_for_product((int) $item['product_id']) as $file) {
            $exists = db_first('SELECT id FROM downloads WHERE digital_file_id = ? AND order_id = ?', [(int) $file['id'], $orderId]);
            if ($exists) {
                continue;
            }
            $token = bin2hex(random_bytes(32));
            $expiresAtSql = $file['expires_after_days'] !== null ? 'DATE_ADD(NOW(), INTERVAL ' . (int) $file['expires_after_days'] . ' DAY)' : 'NULL';
            db_execute(
                'INSERT INTO downloads (digital_file_id, order_id, customer_id, token_hash, download_count, expires_at, created_at) VALUES (?, ?, ?, ?, 0, ' . $expiresAtSql . ', NOW())',
                [(int) $file['id'], $orderId, $order['customer_id'] !== null ? (int) $order['customer_id'] : null, hash('sha256', $token)]
            );
        }
    }
}

function downloads_for_customer(int $customerId): array
{
    return db_select('SELECT d.*, df.original_name, df.storage_path, df.download_limit, p.title AS product_title, o.order_number FROM downloads d INNER JOIN digital_files df ON df.id = d.digital_file_id INNER JOIN products p ON p.id = df.product_id INNER JOIN orders o ON o.id = d.order_id WHERE d.customer_id = ? ORDER BY d.created_at DESC', [$customerId]);
}

function download_find_by_token(string $token): ?array
{
    return db_first('SELECT d.*, df.original_name, df.storage_path, df.download_limit FROM downloads d INNER JOIN digital_files df ON df.id = d.digital_file_id WHERE d.token_hash = ?', [hash('sha256', $token)]);
}

function download_public_token(int $downloadId): string
{
    $config = require dirname(__DIR__) . '/config.php';
    return hash_hmac('sha256', (string) $downloadId, $config['app']['key']);
}

function download_find_by_signed_id(int $downloadId, string $signature): ?array
{
    if (!hash_equals(download_public_token($downloadId), $signature)) {
        return null;
    }
    return db_first('SELECT d.*, df.original_name, df.storage_path, df.download_limit FROM downloads d INNER JOIN digital_files df ON df.id = d.digital_file_id WHERE d.id = ?', [$downloadId]);
}

function download_can_use(array $download): bool
{
    if ($download['expires_at'] !== null && strtotime((string) $download['expires_at']) < time()) {
        return false;
    }
    if ($download['download_limit'] !== null && (int) $download['download_count'] >= (int) $download['download_limit']) {
        return false;
    }
    return true;
}

function download_increment(int $downloadId): void
{
    db_execute('UPDATE downloads SET download_count = download_count + 1 WHERE id = ?', [$downloadId]);
}
