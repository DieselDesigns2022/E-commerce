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

function customer_find_by_email(string $email): ?array
{
    return db_first('SELECT * FROM customers WHERE email = ?', [strtolower(trim($email))]);
}

function customer_orders(int $customerId): array
{
    return db_select('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC', [$customerId]);
}

function customer_create(array $data): int
{
    db_execute(
        'INSERT INTO customers (first_name, last_name, email, password_hash, phone, accepts_marketing, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
        [trim((string) $data['first_name']), trim((string) $data['last_name']), strtolower(trim((string) $data['email'])), password_hash((string) $data['password'], PASSWORD_DEFAULT), trim((string) ($data['phone'] ?? '')), !empty($data['accepts_marketing']) ? 1 : 0]
    );
    $customerId = (int) db()->lastInsertId();
    customer_link_orders_by_email($customerId, (string) $data['email']);
    return $customerId;
}

function customer_update_profile(int $id, array $data): void
{
    db_execute(
        'UPDATE customers SET first_name = ?, last_name = ?, phone = ?, accepts_marketing = ?, updated_at = NOW() WHERE id = ?',
        [trim((string) $data['first_name']), trim((string) $data['last_name']), trim((string) ($data['phone'] ?? '')), !empty($data['accepts_marketing']) ? 1 : 0, $id]
    );
}

function customer_update_password(int $id, string $password): void
{
    db_execute('UPDATE customers SET password_hash = ?, updated_at = NOW() WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
}

function customer_link_orders_by_email(int $customerId, string $email): void
{
    db_execute('UPDATE orders SET customer_id = ?, updated_at = NOW() WHERE customer_id IS NULL AND customer_email = ?', [$customerId, strtolower(trim($email))]);
    db_execute('UPDATE downloads d INNER JOIN orders o ON o.id = d.order_id SET d.customer_id = ? WHERE d.customer_id IS NULL AND o.customer_email = ?', [$customerId, strtolower(trim($email))]);
}
