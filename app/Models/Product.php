<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model {
    protected static string $table = 'products';

    public static function getDetailedCatalog(): array {
        $stmt = self::db()->query("
            SELECT p.*, c.name AS category_name, b.name AS brand_name, u.code AS unit_code,
                   COALESCE(SUM(s.qty), 0) AS total_stock
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
            LEFT JOIN inventory_stocks s ON p.id = s.product_id
            GROUP BY p.id
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }
}
