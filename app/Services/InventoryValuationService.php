<?php

namespace App\Services;

use App\Core\Database;

class InventoryValuationService {
    public static function calculateProductValuation(int $productId, string $method = 'FIFO'): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT * FROM stock_transactions WHERE product_id = :prod_id AND in_qty > 0 ORDER BY created_at ASC");
        $stmt->execute(['prod_id' => $productId]);
        $inboundTxns = $stmt->fetchAll();

        $stockStmt = $db->prepare("SELECT SUM(qty) AS current_qty FROM inventory_stocks WHERE product_id = :prod_id");
        $stockStmt->execute(['prod_id' => $productId]);
        $totalQty = (int) ($stockStmt->fetchColumn() ?: 0);

        if ($totalQty <= 0) {
            return ['method' => $method, 'total_qty' => 0, 'unit_value' => 0.00, 'total_value' => 0.00];
        }

        if ($method === 'WEIGHTED_AVG') {
            $totalCost = 0.00;
            $totalInQty = 0;
            foreach ($inboundTxns as $txn) {
                $totalCost += ($txn['in_qty'] * $txn['unit_cost']);
                $totalInQty += $txn['in_qty'];
            }
            $avgUnitCost = $totalInQty > 0 ? ($totalCost / $totalInQty) : 0.00;
            return [
                'method' => 'WEIGHTED_AVG',
                'total_qty' => $totalQty,
                'unit_value' => round($avgUnitCost, 2),
                'total_value' => round($avgUnitCost * $totalQty, 2)
            ];
        }

        if ($method === 'LIFO') {
            usort($inboundTxns, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        }

        $remainingQty = $totalQty;
        $totalValuation = 0.00;

        foreach ($inboundTxns as $txn) {
            if ($remainingQty <= 0) break;
            $takeQty = min($remainingQty, $txn['in_qty']);
            $totalValuation += ($takeQty * $txn['unit_cost']);
            $remainingQty -= $takeQty;
        }

        $unitValue = $totalQty > 0 ? ($totalValuation / $totalQty) : 0.00;

        return [
            'method' => $method,
            'total_qty' => $totalQty,
            'unit_value' => round($unitValue, 2),
            'total_value' => round($totalValuation, 2)
        ];
    }
}
