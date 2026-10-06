<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model {
    protected static string $table = 'users';

    public static function findWithRoleAndCompany(int $id): ?array {
        $stmt = self::db()->prepare("
            SELECT u.*, r.name AS role_name, r.display_name AS role_display, c.name AS company_name, b.name AS branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN companies c ON u.company_id = c.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getUserPermissions(int $userId): array {
        $stmt = self::db()->prepare("
            SELECT p.code
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN users u ON rp.role_id = u.role_id
            WHERE u.id = :uid
            UNION
            SELECT p.code
            FROM permissions p
            JOIN user_permissions up ON p.id = up.permission_id
            WHERE up.user_id = :uid2 AND up.is_granted = 1
        ");
        $stmt->execute(['uid' => $userId, 'uid2' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function getDetailedUsers(): array {
        $stmt = self::db()->query("
            SELECT u.*, r.name AS role_name, r.display_name AS role_display, c.name AS company_name, b.name AS branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN companies c ON u.company_id = c.id
            LEFT JOIN branches b ON u.branch_id = b.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }
}
