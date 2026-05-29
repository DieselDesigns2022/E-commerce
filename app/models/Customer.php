<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';

function customers_all(?string $search = null): array
{
    if ($search) {
        return db_select('SELECT * FROM customers WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC', ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    }
    return db_select('SELECT * FROM customers ORDER BY created_at DESC');
}

function customer_find(int $id): ?array
{
    return db_first('SELECT * FROM customers WHERE id = ?', [$id]);
}

function customer_orders(int $customerId): array
{
    return db_select('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC', [$customerId]);
}
