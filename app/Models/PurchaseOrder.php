<?php

namespace App\Models;

use App\Core\Model;

class PurchaseOrder extends Model {
    protected static string $table = 'purchase_orders';

    public static function getDetailedOrders(): array {
        $stmt = self::db()->query("
            SELECT po.*, s.name AS supplier_name, s.email AS supplier_email, c.name AS company_name, b.name AS branch_name, w.name AS warehouse_name, u.name AS creator_name,
                   COALESCE(SUM(poi.qty), 0) AS total_ordered_qty,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id
                   ), 0) AS total_received_qty,
                   COALESCE((
                       SELECT SUM(gi.accepted_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id
                   ), 0) AS total_accepted_qty,
                   COALESCE((
                       SELECT SUM(total_amount)
                       FROM purchase_invoices pi
                       WHERE pi.po_id = po.id
                   ), 0.00) AS total_invoiced_amount,
                   COALESCE((
                       SELECT SUM(paid_amount)
                       FROM purchase_invoices pi
                       WHERE pi.po_id = po.id
                   ), 0.00) AS total_paid_amount,
                   (SELECT COUNT(*) FROM purchase_invoices pi WHERE pi.po_id = po.id) AS invoice_count,
                   (SELECT COUNT(*) FROM goods_receipt_notes g WHERE g.po_id = po.id) AS grn_count
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN companies c ON po.company_id = c.id
            LEFT JOIN branches b ON po.branch_id = b.id
            LEFT JOIN warehouses w ON po.warehouse_id = w.id
            LEFT JOIN users u ON po.created_by = u.id
            LEFT JOIN purchase_order_items poi ON poi.po_id = po.id
            GROUP BY po.id
            ORDER BY po.id DESC
        ");
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $ordered = (int)($o['total_ordered_qty'] ?? 0);
            $received = (int)($o['total_received_qty'] ?? 0);
            $contractTot = (float)($o['total_amount'] ?? 0.00);
            $invTot = (float)($o['total_invoiced_amount'] ?? 0.00);
            $paidTot = (float)($o['total_paid_amount'] ?? 0.00);
            $invCount = (int)($o['invoice_count'] ?? 0);
            $grnCount = (int)($o['grn_count'] ?? 0);

            $o['pending_qty'] = max(0, $ordered - $received);
            $o['is_fully_received'] = ($ordered > 0 && $received >= $ordered);
            
            // Fully invoiced if an invoice exists and covers contract OR if 100% received and invoice exists
            $o['is_fully_invoiced'] = ($invCount > 0 && ($invTot >= ($contractTot - 5.00) || ($o['is_fully_received'] && $invCount >= $grnCount)));
            $o['is_fully_paid'] = ($invCount > 0 && ($paidTot >= ($contractTot - 5.00) || ($invTot > 0 && $paidTot >= ($invTot - 5.00))));
            $o['is_order_settled'] = ($o['is_fully_received'] && $o['is_fully_paid']);
        }

        return $orders;
    }
}
