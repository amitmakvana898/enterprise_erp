<?php

namespace App\Models;

use App\Core\Model;

class InventoryStock extends Model {
    protected static string $table = 'inventory_stocks';

    public static function getStockWithBinDetails(): array {
        $stmt = self::db()->query("
            SELECT s.*, p.name AS product_name, p.sku, p.barcode, w.name AS warehouse_name, bn.code AS bin_code, r.code AS rack_code
            FROM inventory_stocks s
            JOIN products p ON s.product_id = p.id
            JOIN warehouses w ON s.warehouse_id = w.id
            JOIN bins bn ON s.bin_id = bn.id
            JOIN racks r ON bn.rack_id = r.id
            ORDER BY s.id DESC
        ");
        return $stmt->fetchAll();
    }
}
