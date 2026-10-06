<?php

namespace App\Core;

use PDO;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO {
        return Database::getInstance();
    }

    public static function all(string $orderBy = 'id DESC'): array {
        $table = static::$table;
        $stmt = self::db()->query("SELECT * FROM {$table} ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = self::db()->prepare("SELECT * FROM {$table} WHERE {$pk} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function where(string $column, $value, string $operator = '='): array {
        $table = static::$table;
        $stmt = self::db()->prepare("SELECT * FROM {$table} WHERE {$column} {$operator} :val");
        $stmt->execute(['val' => $value]);
        return $stmt->fetchAll();
    }

    public static function findOneWhere(string $column, $value): ?array {
        $results = static::where($column, $value);
        return $results[0] ?? null;
    }

    public static function create(array $data): int {
        $table = static::$table;
        $keys = array_keys($data);
        $fields = implode(', ', array_map(fn($k) => "`$k`", $keys));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $table = static::$table;
        $pk = static::$primaryKey;
        $fields = implode(', ', array_map(fn($k) => "`$k` = :$k", array_keys($data)));

        $data['id'] = $id;
        $sql = "UPDATE {$table} SET {$fields} WHERE {$pk} = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool {
        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = self::db()->prepare("DELETE FROM {$table} WHERE {$pk} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function count(): int {
        $table = static::$table;
        $stmt = self::db()->query("SELECT COUNT(*) FROM {$table}");
        return (int) $stmt->fetchColumn();
    }

    public static function paginate(int $page = 1, int $perPage = 10, string $where = '1=1', array $params = [], string $orderBy = 'id DESC'): array {
        $table = static::$table;
        $offset = ($page - 1) * $perPage;

        $countStmt = self::db()->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
        $countStmt->execute($params);
        $totalRecords = (int) $countStmt->fetchColumn();

        $sql = "SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $totalRecords,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, ceil($totalRecords / $perPage))
        ];
    }
}
