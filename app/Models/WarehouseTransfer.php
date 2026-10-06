<?php

namespace App\Models;

use App\Core\Model;

class WarehouseTransfer extends Model {
    protected static string $table = 'warehouse_transfers';

    public static function getDetailedTransfers(): array {
        $stmt = self::db()->query("
            SELECT wt.*, sw.name AS source_warehouse, dw.name AS dest_warehouse, u.name AS creator_name
            FROM warehouse_transfers wt
            JOIN warehouses sw ON wt.source_warehouse_id = sw.id
            JOIN warehouses dw ON wt.dest_warehouse_id = dw.id
            JOIN users u ON wt.created_by = u.id
            ORDER BY wt.id DESC
        ");
        return $stmt->fetchAll();
    }
}
