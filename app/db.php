<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/config.php';
    $db = $config['database'];
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['name'], $db['charset']);

    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function db_select(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function db_first(string $sql, array $params = []): ?array
{
    $rows = db_select($sql, $params);
    return $rows[0] ?? null;
}

function db_execute(string $sql, array $params = []): bool
{
    $statement = db()->prepare($sql);
    return $statement->execute($params);
}
