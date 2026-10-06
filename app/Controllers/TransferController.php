<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\WarehouseTransfer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\AuditService;
use Exception;

class TransferController extends Controller {
    public function index(): void {
        if (!has_permission('inventory.transfer')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.transfer) to view Inter-Warehouse Transfers!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $transfers = WarehouseTransfer::getDetailedTransfers();
        $this->render('transfers/index', ['title' => 'Warehouse-to-Warehouse Inter-Transfer', 'transfers' => $transfers]);
    }

    public function create(): void {
        if (!has_permission('inventory.transfer')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.transfer) to initiate warehouse transfers!', 'danger');
            (new Response())->redirect(url('/transfers'));
            return;
        }
        $warehouses = Warehouse::all();
        $products = Product::all();
        $this->render('transfers/create', ['title' => 'Initiate Inter-Warehouse Dispatch', 'warehouses' => $warehouses, 'products' => $products]);
    }

    public function store(): void {
        if (!has_permission('inventory.transfer')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.transfer) to initiate warehouse transfers!', 'danger');
            (new Response())->redirect(url('/transfers'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['source_warehouse_id']) || empty($data['dest_warehouse_id']) || empty($data['product_id']) || empty($data['qty'])) {
            Session::setFlash('error', 'Fill source, destination, product and quantity.', 'danger');
            $response->redirect(url('/transfers/create'));
        }

        if ($data['source_warehouse_id'] === $data['dest_warehouse_id']) {
            Session::setFlash('error', 'Source and destination warehouses cannot be identical.', 'danger');
            $response->redirect(url('/transfers/create'));
        }

        $db = Database::getInstance();
        $sourceWid = (int)$data['source_warehouse_id'];
        $destWid = (int)$data['dest_warehouse_id'];
        $productId = (int)$data['product_id'];
        $qtyToTransfer = (int)$data['qty'];

        if ($qtyToTransfer <= 0) {
            Session::setFlash('error', 'Quantity must be greater than zero.', 'danger');
            $response->redirect(url('/transfers/create'));
            return;
        }

        Database::beginTransaction();

        try {
            // Check available stock in source warehouse
            $stockRowsStmt = $db->prepare("
                SELECT * FROM inventory_stocks 
                WHERE warehouse_id = :swid AND product_id = :pid AND qty > 0 
                ORDER BY id ASC
            ");
            $stockRowsStmt->execute(['swid' => $sourceWid, 'pid' => $productId]);
            $stockRows = $stockRowsStmt->fetchAll();

            $totalAvailable = 0;
            foreach ($stockRows as $row) {
                $totalAvailable += (int)$row['qty'];
            }

            if ($totalAvailable < $qtyToTransfer) {
                throw new Exception("Insufficient stock in source warehouse! Available: {$totalAvailable} units, Requested: {$qtyToTransfer} units.");
            }

            $transferNo = 'TRF-' . date('Y') . '-' . rand(1000, 9999);
            $stmt = $db->prepare("
                INSERT INTO warehouse_transfers (transfer_no, source_warehouse_id, dest_warehouse_id, vehicle_no, driver_name, dispatch_date, status, created_by) 
                VALUES (:tno, :swid, :dwid, :vno, :driver, NOW(), 'in_transit', :uid)
            ");
            $stmt->execute([
                'tno' => $transferNo,
                'swid' => $sourceWid,
                'dwid' => $destWid,
                'vno' => $data['vehicle_no'] ?? 'TRK-' . rand(100, 999),
                'driver' => $data['driver_name'] ?? 'Ramesh Driver',
                'uid' => $user['id']
            ]);
            $transferId = (int)$db->lastInsertId();

            // Deduct stock from source warehouse across available bins (FIFO)
            $remaining = $qtyToTransfer;
            $primaryBinId = 1;

            foreach ($stockRows as $row) {
                if ($remaining <= 0) break;

                $primaryBinId = (int)$row['bin_id'];
                $deduct = min($remaining, (int)$row['qty']);
                $newQty = (int)$row['qty'] - $deduct;

                $db->prepare("UPDATE inventory_stocks SET qty = :nqty WHERE id = :id")->execute([
                    'nqty' => $newQty,
                    'id' => $row['id']
                ]);

                // Record stock movement transaction
                $db->prepare("
                    INSERT INTO stock_transactions 
                    (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by)
                    VALUES (:tno, :wid, :bid, :pid, :batch, 'TRANSFER_OUT', 'TRANSFER', :refid, 0, :out, :bal, :cost, :tot, :uid)
                ")->execute([
                    'tno' => 'TXN-TRF-OUT-' . time() . '-' . rand(10, 99),
                    'wid' => $sourceWid,
                    'bid' => $row['bin_id'],
                    'pid' => $productId,
                    'batch' => $row['batch_no'] ?? 'DEFAULT',
                    'refid' => $transferId,
                    'out' => $deduct,
                    'bal' => $newQty,
                    'cost' => $row['valuation_rate'] ?? 0,
                    'tot' => ($row['valuation_rate'] ?? 0) * $deduct,
                    'uid' => $user['id']
                ]);

                $remaining -= $deduct;
            }

            $itemStmt = $db->prepare("
                INSERT INTO warehouse_transfer_items (transfer_id, product_id, source_bin_id, qty_dispatched) 
                VALUES (:tid, :pid, :bid, :qty)
            ");
            $itemStmt->execute([
                'tid' => $transferId,
                'pid' => $productId,
                'bid' => $primaryBinId,
                'qty' => $qtyToTransfer
            ]);

            Database::commit();
            AuditService::log('WarehouseTransfer', 'DISPATCH', $transferId, null, $data);
            Session::setFlash('success', "Transfer {$transferNo} ({$qtyToTransfer} units) dispatched in transit!", 'success');
            $response->redirect(url('/transfers'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/transfers/create'));
        }
    }

    public function receive(int $id): void {
        if (!has_permission('inventory.transfer')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.transfer) to receive warehouse transfers!', 'danger');
            (new Response())->redirect(url('/transfers'));
            return;
        }
        $user = auth_user();
        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $transfer = WarehouseTransfer::find($id);
            $itemsStmt = $db->prepare("SELECT * FROM warehouse_transfer_items WHERE transfer_id = :tid");
            $itemsStmt->execute(['tid' => $id]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $qtyReceived = $item['qty_dispatched']; // Assuming full receipt
                $diff = $qtyReceived - $item['qty_dispatched'];

                $db->prepare("UPDATE warehouse_transfer_items SET qty_received = :rec, qty_difference = :diff WHERE id = :item_id")->execute([
                    'rec' => $qtyReceived,
                    'diff' => $diff,
                    'item_id' => $item['id']
                ]);

                // Add to destination warehouse stock using valid bin ID
                $validBinId = $db->query("SELECT id FROM bins LIMIT 1")->fetchColumn() ?: 1;

                $destCheck = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :dwid AND product_id = :pid LIMIT 1");
                $destCheck->execute(['dwid' => $transfer['dest_warehouse_id'], 'pid' => $item['product_id']]);
                $existing = $destCheck->fetch();

                if ($existing) {
                    $db->prepare("UPDATE inventory_stocks SET qty = qty + :qty WHERE id = :sid")->execute(['qty' => $qtyReceived, 'sid' => $existing['id']]);
                } else {
                    $prod = Product::find($item['product_id']);
                    $costRate = !empty($prod['purchase_rate']) ? (float)$prod['purchase_rate'] : 100.00;
                    $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, qty, valuation_rate) VALUES (:dwid, :bin_id, :pid, :qty, :val)")->execute([
                        'dwid' => $transfer['dest_warehouse_id'],
                        'bin_id' => $validBinId,
                        'pid' => $item['product_id'],
                        'qty' => $qtyReceived,
                        'val' => $costRate
                    ]);
                }
            }

            $db->prepare("UPDATE warehouse_transfers SET status = 'received', received_date = NOW(), received_by = :uid WHERE id = :tid")->execute(['uid' => $user['id'], 'tid' => $id]);

            Database::commit();
            AuditService::log('WarehouseTransfer', 'RECEIVE', $id);
            Session::setFlash('success', 'Transfer received and destination stock updated!', 'success');
            (new Response())->redirect(url('/transfers'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            (new Response())->redirect(url('/transfers'));
        }
    }
}
