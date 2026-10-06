<?php

namespace App\Models;

use App\Core\Model;

class SalesOrder extends Model {
    protected static string $table = 'sales_orders';

    public static function getDetailedOrders(): array {
        $stmt = self::db()->query("
            SELECT so.*, cust.name AS customer_name, c.name AS company_name, b.name AS branch_name, w.name AS warehouse_name, u.name AS creator_name,
                   si.id AS invoice_id, si.invoice_no
            FROM sales_orders so
            LEFT JOIN customers cust ON so.customer_id = cust.id
            LEFT JOIN companies c ON so.company_id = c.id
            LEFT JOIN branches b ON so.branch_id = b.id
            LEFT JOIN warehouses w ON so.warehouse_id = w.id
            LEFT JOIN users u ON so.created_by = u.id
            LEFT JOIN sales_invoices si ON si.order_id = so.id
            ORDER BY so.id DESC
        ");
        return $stmt->fetchAll();
    }
}
