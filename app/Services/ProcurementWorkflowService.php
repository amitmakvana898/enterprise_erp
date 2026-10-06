<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class ProcurementWorkflowService {
    public static function processGrnStockReceipt(int $grnId, int $receivedByUserId): bool {
        $db = Database::getInstance();
        $inTxn = $db->inTransaction();
        if (!$inTxn) {
            Database::beginTransaction();
        }

        try {
            $grnStmt = $db->prepare("SELECT * FROM goods_receipt_notes WHERE id = :grn_id LIMIT 1");
            $grnStmt->execute(['grn_id' => $grnId]);
            $grn = $grnStmt->fetch();

            if (!$grn) throw new Exception("GRN not found.");

            $itemsStmt = $db->prepare("SELECT * FROM grn_items WHERE grn_id = :grn_id");
            $itemsStmt->execute(['grn_id' => $grnId]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $acceptedQty = $item['accepted_qty'];
                if ($acceptedQty <= 0) continue;

                $batchNo = $item['batch_no'] ?: 'BATCH-GRN-' . $grnId;

                // Check existing bin stock
                $stockCheck = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wh_id AND bin_id = :bin_id AND product_id = :prod_id AND batch_no = :batch_no LIMIT 1");
                $stockCheck->execute([
                    'wh_id' => $grn['warehouse_id'],
                    'bin_id' => $item['bin_id'],
                    'prod_id' => $item['product_id'],
                    'batch_no' => $batchNo
                ]);
                $existingStock = $stockCheck->fetch();

                // Get product purchase rate
                $prodStmt = $db->prepare("SELECT purchase_rate FROM products WHERE id = :prod_id");
                $prodStmt->execute(['prod_id' => $item['product_id']]);
                $prodRate = (float) $prodStmt->fetchColumn();

                if ($existingStock) {
                    $newQty = $existingStock['qty'] + $acceptedQty;
                    $updStock = $db->prepare("UPDATE inventory_stocks SET qty = :qty, valuation_rate = :val_rate WHERE id = :stock_id");
                    $updStock->execute(['qty' => $newQty, 'val_rate' => $prodRate, 'stock_id' => $existingStock['id']]);
                    $balanceQty = $newQty;
                } else {
                    $insStock = $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, mfd_date, exp_date, qty, valuation_rate) VALUES (:wh_id, :bin_id, :prod_id, :batch_no, :mfd, :exp, :qty, :val_rate)");
                    $insStock->execute([
                        'wh_id' => $grn['warehouse_id'],
                        'bin_id' => $item['bin_id'],
                        'prod_id' => $item['product_id'],
                        'batch_no' => $batchNo,
                        'mfd' => $item['mfd_date'],
                        'exp' => $item['exp_date'],
                        'qty' => $acceptedQty,
                        'val_rate' => $prodRate
                    ]);
                    $balanceQty = $acceptedQty;
                }

                // Record stock ledger entry
                $txnNo = 'TXN-GRN-' . time() . '-' . rand(10, 99);
                $insTxn = $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:txn_no, :wh_id, :bin_id, :prod_id, :batch_no, 'PURCHASE_GRN', 'GRN', :ref_id, :in_qty, 0, :bal_qty, :unit_cost, :tot_cost, :created_by)");
                $insTxn->execute([
                    'txn_no' => $txnNo,
                    'wh_id' => $grn['warehouse_id'],
                    'bin_id' => $item['bin_id'],
                    'prod_id' => $item['product_id'],
                    'batch_no' => $batchNo,
                    'ref_id' => $grnId,
                    'in_qty' => $acceptedQty,
                    'bal_qty' => $balanceQty,
                    'unit_cost' => $prodRate,
                    'tot_cost' => $prodRate * $acceptedQty,
                    'created_by' => $receivedByUserId
                ]);
            }

            // Mark GRN completed
            $updGrn = $db->prepare("UPDATE goods_receipt_notes SET status = 'completed' WHERE id = :grn_id");
            $updGrn->execute(['grn_id' => $grnId]);

            if (!$inTxn) {
                Database::commit();
            }
            AuditService::log('Procurement', 'GRN_STOCK_RECEIVED', $grnId, null, ['status' => 'completed']);
            return true;
        } catch (Exception $e) {
            if (!$inTxn) {
                Database::rollBack();
            }
            return false;
        }
    }
}
