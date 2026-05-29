<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/customer_auth.php';
require_once dirname(__DIR__, 2) . '/app/models/DigitalFile.php';

$customer = require_customer();
$download = download_find_by_signed_id((int) ($_GET['id'] ?? 0), (string) ($_GET['signature'] ?? ''));
if (!$download || (int) $download['customer_id'] !== (int) $customer['id'] || !download_can_use($download)) {
    http_response_code(403);
    exit('Download unavailable.');
}

$path = dirname(__DIR__, 2) . '/' . ltrim((string) $download['storage_path'], '/');
if (!is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

download_increment((int) $download['id']);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename((string) $download['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
