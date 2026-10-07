<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryValuationService;
use App\Services\AuditService;
use App\Services\ExcelExportService;
use Exception;

class InventoryController extends Controller {

    public function index(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.read) to view Bin Stock Levels!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $stocks = InventoryStock::getStockWithBinDetails();
        $this->render('inventory/index', ['title' => 'Bin Stock Levels & Operations - Inventory Management', 'stocks' => $stocks]);
    }

    public function ledger(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.read) to view Stock Ledger!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $transactions = $db->query("
            SELECT t.*, p.name AS product_name, p.sku, w.name AS warehouse_name, bn.code AS bin_code, u.name AS user_name
            FROM stock_transactions t
            JOIN products p ON t.product_id = p.id
            JOIN warehouses w ON t.warehouse_id = w.id
            JOIN bins bn ON t.bin_id = bn.id
            JOIN users u ON t.created_by = u.id
            ORDER BY t.id DESC
        ")->fetchAll();
        $this->render('inventory/ledger', ['title' => 'Stock Audit Ledger (Movement Trail)', 'transactions' => $transactions]);
    }

    /* ─── 1. OPENING STOCK ─── */
    public function openingStock(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to record Opening Stock.', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        $this->render('inventory/opening', [
            'title' => 'Opening Stock Setup - Inventory Management',
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    public function storeOpeningStock(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to record Opening Stock.', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['warehouse_id']) || empty($data['qty']) || (int)$data['qty'] <= 0) {
            Session::setFlash('error', 'Valid product, warehouse, and positive opening quantity are required.', 'danger');
            $response->redirect(url('/inventory/opening'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $productId = (int)$data['product_id'];
            $warehouseId = (int)$data['warehouse_id'];
            $binId = !empty($data['bin_id']) ? (int)$data['bin_id'] : 1;
            $qty = (int)$data['qty'];
            $batchNo = !empty($data['batch_no']) ? trim($data['batch_no']) : 'BATCH-' . strtoupper(substr(md5(time() . rand()), 0, 6));
            $mfdDate = !empty($data['mfd_date']) ? $data['mfd_date'] : null;
            $expDate = !empty($data['exp_date']) ? $data['exp_date'] : null;

            $product = Product::find($productId);
            $unitCost = !empty($data['unit_cost']) ? (float)$data['unit_cost'] : (float)($product['purchase_rate'] ?? 0);

            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND bin_id = :bid AND product_id = :pid AND batch_no = :batch LIMIT 1");
            $stockStmt->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId, 'batch' => $batchNo]);
            $existing = $stockStmt->fetch();

            if ($existing) {
                $newQty = $existing['qty'] + $qty;
                $db->prepare("UPDATE inventory_stocks SET qty = :qty, valuation_rate = :val WHERE id = :sid")
                   ->execute(['qty' => $newQty, 'val' => $unitCost, 'sid' => $existing['id']]);
            } else {
                $newQty = $qty;
                $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, mfd_date, exp_date, qty, valuation_rate) VALUES (:wid, :bid, :pid, :batch, :mfd, :exp, :qty, :val)")
                   ->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId, 'batch' => $batchNo, 'mfd' => $mfdDate, 'exp' => $expDate, 'qty' => $qty, 'val' => $unitCost]);
            }

            // Ledger entry
            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, :batch, 'OPENING', 'INITIAL_ENTRY', :in, 0, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-OPEN-' . time(),
                'wid' => $warehouseId,
                'bid' => $binId,
                'pid' => $productId,
                'batch' => $batchNo,
                'in' => $qty,
                'bal' => $newQty,
                'cost' => $unitCost,
                'tot' => $unitCost * $qty,
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'OPENING_STOCK', $productId, null, $data);
            Session::setFlash('success', "Opening stock of {$qty} units recorded for {$product['name']} (Batch: {$batchNo})!", 'success');
            $response->redirect(url('/inventory'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/opening'));
        }
    }

    /* ─── 2. STOCK ADJUSTMENT ─── */
    public function adjust(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.adjust) to perform stock adjustments!', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }

        $products = Product::all();
        $warehouses = Warehouse::all();
        $selectedProductId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        $this->render('inventory/adjust', [
            'title' => 'Stock Adjustment (Audit Reconciliation)',
            'products' => $products,
            'warehouses' => $warehouses,
            'selectedProductId' => $selectedProductId
        ]);
    }

    public function storeAdjustment(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.adjust) to post stock adjustments!', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['warehouse_id']) || empty($data['qty']) || empty($data['type'])) {
            Session::setFlash('error', 'Product, warehouse, type, and quantity required.', 'danger');
            $response->redirect(url('/inventory/adjust'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $productId = (int)$data['product_id'];
            $warehouseId = (int)$data['warehouse_id'];
            $binId = !empty($data['bin_id']) ? (int)$data['bin_id'] : 1;
            $qty = (int)$data['qty'];
            $type = $data['type']; // ADD or SUB

            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND bin_id = :bid AND product_id = :pid LIMIT 1");
            $stockStmt->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId]);
            $existing = $stockStmt->fetch();

            $product = Product::find($productId);

            if ($type === 'SUB') {
                // STRICT NEGATIVE STOCK POLICY
                if (!$existing || $existing['qty'] < $qty) {
                    throw new Exception("Negative stock is strictly disallowed! Current available stock: " . ($existing['qty'] ?? 0) . " units.");
                }
                $newQty = $existing['qty'] - $qty;
                $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :sid")->execute(['qty' => $newQty, 'sid' => $existing['id']]);
                $inQty = 0; $outQty = $qty;
            } else {
                $newQty = ($existing['qty'] ?? 0) + $qty;
                if ($existing) {
                    $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :sid")->execute(['qty' => $newQty, 'sid' => $existing['id']]);
                } else {
                    $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, qty, valuation_rate) VALUES (:wid, :bid, :pid, :qty, :val)")->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId, 'qty' => $qty, 'val' => $product['purchase_rate']]);
                }
                $inQty = $qty; $outQty = 0;
            }

            // Ledger entry
            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, :ttype, 'AUDIT_ADJUSTMENT', :in, :out, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-ADJ-' . time(),
                'wid' => $warehouseId,
                'bid' => $binId,
                'pid' => $productId,
                'ttype' => ($type === 'SUB') ? 'ADJUSTMENT_SUB' : 'ADJUSTMENT_ADD',
                'in' => $inQty,
                'out' => $outQty,
                'bal' => $newQty,
                'cost' => $product['purchase_rate'],
                'tot' => $product['purchase_rate'] * $qty,
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'STOCK_ADJUSTMENT', $productId, null, $data);
            Session::setFlash('success', 'Stock adjustment posted cleanly!', 'success');
            $response->redirect(url('/inventory'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/adjust'));
        }
    }

    /* ─── 3. STOCK ISSUE & RECEIVE ─── */
    public function stockIssue(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        $this->render('inventory/issue', [
            'title' => 'Stock Issue (Internal/Consumption) - Inventory Management',
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    public function storeStockIssue(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['warehouse_id']) || empty($data['qty']) || (int)$data['qty'] <= 0) {
            Session::setFlash('error', 'Valid product, warehouse, and positive issue quantity are required.', 'danger');
            $response->redirect(url('/inventory/issue'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $productId = (int)$data['product_id'];
            $warehouseId = (int)$data['warehouse_id'];
            $binId = !empty($data['bin_id']) ? (int)$data['bin_id'] : 1;
            $qty = (int)$data['qty'];

            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND bin_id = :bid AND product_id = :pid LIMIT 1");
            $stockStmt->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId]);
            $existing = $stockStmt->fetch();

            // STRICT NEGATIVE STOCK POLICY
            if (!$existing || $existing['qty'] < $qty) {
                throw new Exception("Negative stock is strictly disallowed! Requested: {$qty}, Available: " . ($existing['qty'] ?? 0) . " units.");
            }

            $newQty = $existing['qty'] - $qty;
            $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :sid")->execute(['qty' => $newQty, 'sid' => $existing['id']]);

            $product = Product::find($productId);

            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, 'SALES_ISSUE', 'INTERNAL_ISSUE', 0, :out, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-ISSUE-' . time(),
                'wid' => $warehouseId,
                'bid' => $binId,
                'pid' => $productId,
                'out' => $qty,
                'bal' => $newQty,
                'cost' => $product['purchase_rate'],
                'tot' => $product['purchase_rate'] * $qty,
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'STOCK_ISSUE', $productId, null, $data);
            Session::setFlash('success', "Issued {$qty} units of {$product['name']} successfully!", 'success');
            $response->redirect(url('/inventory'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/issue'));
        }
    }

    public function stockReceive(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        $this->render('inventory/receive', [
            'title' => 'Stock Receive (Inbound Material) - Inventory Management',
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    public function storeStockReceive(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['warehouse_id']) || empty($data['qty']) || (int)$data['qty'] <= 0) {
            Session::setFlash('error', 'Valid product, warehouse, and positive receive quantity are required.', 'danger');
            $response->redirect(url('/inventory/receive'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $productId = (int)$data['product_id'];
            $warehouseId = (int)$data['warehouse_id'];
            $binId = !empty($data['bin_id']) ? (int)$data['bin_id'] : 1;
            $qty = (int)$data['qty'];
            $batchNo = !empty($data['batch_no']) ? trim($data['batch_no']) : 'BATCH-' . rand(1000, 9999);

            $product = Product::find($productId);
            $unitCost = !empty($data['unit_cost']) ? (float)$data['unit_cost'] : (float)($product['purchase_rate'] ?? 0);

            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND bin_id = :bid AND product_id = :pid AND batch_no = :batch LIMIT 1");
            $stockStmt->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId, 'batch' => $batchNo]);
            $existing = $stockStmt->fetch();

            if ($existing) {
                $newQty = $existing['qty'] + $qty;
                $db->prepare("UPDATE inventory_stocks SET qty = :qty, valuation_rate = :val WHERE id = :sid")->execute(['qty' => $newQty, 'val' => $unitCost, 'sid' => $existing['id']]);
            } else {
                $newQty = $qty;
                $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, qty, valuation_rate) VALUES (:wid, :bid, :pid, :batch, :qty, :val)")->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId, 'batch' => $batchNo, 'qty' => $qty, 'val' => $unitCost]);
            }

            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, :batch, 'PURCHASE_GRN', 'MATERIAL_RECEIPT', :in, 0, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-REC-' . time(),
                'wid' => $warehouseId,
                'bid' => $binId,
                'pid' => $productId,
                'batch' => $batchNo,
                'in' => $qty,
                'bal' => $newQty,
                'cost' => $unitCost,
                'tot' => $unitCost * $qty,
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'STOCK_RECEIVE', $productId, null, $data);
            Session::setFlash('success', "Received {$qty} units of {$product['name']} successfully!", 'success');
            $response->redirect(url('/inventory'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/receive'));
        }
    }

    /* ─── 4. STOCK DAMAGE / SCRAP ─── */
    public function stockDamage(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $products = Product::all();
        $warehouses = Warehouse::all();
        $this->render('inventory/damage', [
            'title' => 'Stock Damage & Scrap Write-Off - Inventory Management',
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    public function storeStockDamage(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['warehouse_id']) || empty($data['qty']) || (int)$data['qty'] <= 0) {
            Session::setFlash('error', 'Valid product, warehouse, and positive damage quantity required.', 'danger');
            $response->redirect(url('/inventory/damage'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $productId = (int)$data['product_id'];
            $warehouseId = (int)$data['warehouse_id'];
            $binId = !empty($data['bin_id']) ? (int)$data['bin_id'] : 1;
            $qty = (int)$data['qty'];

            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND bin_id = :bid AND product_id = :pid LIMIT 1");
            $stockStmt->execute(['wid' => $warehouseId, 'bid' => $binId, 'pid' => $productId]);
            $existing = $stockStmt->fetch();

            // STRICT NEGATIVE STOCK POLICY
            if (!$existing || $existing['qty'] < $qty) {
                throw new Exception("Negative stock is strictly disallowed! Requested damage scrap: {$qty}, Available: " . ($existing['qty'] ?? 0) . " units.");
            }

            $newQty = $existing['qty'] - $qty;
            $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :sid")->execute(['qty' => $newQty, 'sid' => $existing['id']]);

            $product = Product::find($productId);

            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, 'DAMAGE', 'SCRAP_WRITE_OFF', 0, :out, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-DMG-' . time(),
                'wid' => $warehouseId,
                'bid' => $binId,
                'pid' => $productId,
                'out' => $qty,
                'bal' => $newQty,
                'cost' => $product['purchase_rate'],
                'tot' => $product['purchase_rate'] * $qty,
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'STOCK_DAMAGE', $productId, null, $data);
            Session::setFlash('success', "Wrote off {$qty} damaged units of {$product['name']} successfully!", 'success');
            $response->redirect(url('/inventory'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/damage'));
        }
    }

    /* ─── 5. PHYSICAL VERIFICATION ─── */
    public function physicalVerification(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $stocks = InventoryStock::getStockWithBinDetails();
        $this->render('inventory/physical', [
            'title' => 'Physical Stock Audit Verification & Reconcile',
            'stocks' => $stocks
        ]);
    }

    public function storePhysicalVerification(): void {
        if (!has_permission('inventory.adjust')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['stock_id']) || !isset($data['physical_qty'])) {
            Session::setFlash('error', 'Stock item and physical counted quantity required.', 'danger');
            $response->redirect(url('/inventory/physical-verification'));
            return;
        }

        $physicalQty = (int)$data['physical_qty'];
        if ($physicalQty < 0) {
            Session::setFlash('error', 'Invalid physical stock count: Physical quantity cannot be negative!', 'danger');
            $response->redirect(url('/inventory/physical-verification'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $stockId = (int)$data['stock_id'];

            $stmt = $db->prepare("SELECT s.*, p.name AS product_name, p.purchase_rate FROM inventory_stocks s JOIN products p ON s.product_id = p.id WHERE s.id = :sid LIMIT 1");
            $stmt->execute(['sid' => $stockId]);
            $stock = $stmt->fetch();

            if (!$stock) {
                throw new Exception("Stock record not found.");
            }

            $systemQty = (int)$stock['qty'];
            $variance = $physicalQty - $systemQty;

            if ($variance === 0) {
                Session::setFlash('info', 'No variance detected. System count matches physical verification!', 'info');
                $response->redirect(url('/inventory/physical-verification'));
                return;
            }

            // Update physical count directly
            $db->prepare("UPDATE inventory_stocks SET qty = :pqty WHERE id = :sid")->execute(['pqty' => $physicalQty, 'sid' => $stockId]);

            $type = ($variance > 0) ? 'ADJUSTMENT_ADD' : 'ADJUSTMENT_SUB';
            $inQty = ($variance > 0) ? $variance : 0;
            $outQty = ($variance < 0) ? abs($variance) : 0;

            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, :bid, :pid, :batch, :ttype, 'PHYSICAL_VERIFICATION', :in, :out, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-PHYS-' . time(),
                'wid' => $stock['warehouse_id'],
                'bid' => $stock['bin_id'],
                'pid' => $stock['product_id'],
                'batch' => $stock['batch_no'],
                'ttype' => $type,
                'in' => $inQty,
                'out' => $outQty,
                'bal' => $physicalQty,
                'cost' => $stock['purchase_rate'],
                'tot' => $stock['purchase_rate'] * abs($variance),
                'uid' => $user['id']
            ]);

            Database::commit();
            AuditService::log('Inventory', 'PHYSICAL_VERIFICATION', $stock['product_id'], null, ['variance' => $variance, 'physical_qty' => $physicalQty]);
            Session::setFlash('success', "Physical verification reconciled! Stock updated to {$physicalQty} (Variance: {$variance}).", 'success');
            $response->redirect(url('/inventory/physical-verification'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/inventory/physical-verification'));
        }
    }

    /* ─── 6. BATCH & EXPIRY MANAGEMENT ─── */
    public function batches(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $batches = $db->query("
            SELECT s.*, p.name AS product_name, p.sku, w.name AS warehouse_name, bn.code AS bin_code
            FROM inventory_stocks s
            JOIN products p ON s.product_id = p.id
            JOIN warehouses w ON s.warehouse_id = w.id
            JOIN bins bn ON s.bin_id = bn.id
            ORDER BY s.exp_date ASC
        ")->fetchAll();

        $this->render('inventory/batches', [
            'title' => 'Batch & Lot Tracking - Inventory Management',
            'batches' => $batches
        ]);
    }

    public function expiryDashboard(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $expired = $db->query("
            SELECT s.*, p.name AS product_name, p.sku, w.name AS warehouse_name, bn.code AS bin_code
            FROM inventory_stocks s
            JOIN products p ON s.product_id = p.id
            JOIN warehouses w ON s.warehouse_id = w.id
            JOIN bins bn ON s.bin_id = bn.id
            WHERE s.exp_date IS NOT NULL AND s.exp_date < CURDATE()
            ORDER BY s.exp_date ASC
        ")->fetchAll();

        $expiringSoon = $db->query("
            SELECT s.*, p.name AS product_name, p.sku, w.name AS warehouse_name, bn.code AS bin_code, DATEDIFF(s.exp_date, CURDATE()) AS days_left
            FROM inventory_stocks s
            JOIN products p ON s.product_id = p.id
            JOIN warehouses w ON s.warehouse_id = w.id
            JOIN bins bn ON s.bin_id = bn.id
            WHERE s.exp_date IS NOT NULL AND s.exp_date >= CURDATE() AND s.exp_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY s.exp_date ASC
        ")->fetchAll();

        $this->render('inventory/expiry', [
            'title' => 'Expiry Tracking & Alerts - Inventory Management',
            'expired' => $expired,
            'expiringSoon' => $expiringSoon
        ]);
    }

    /* ─── 7. VALUATION ─── */
    public function valuation(): void {
        if (!has_permission('inventory.valuation')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.valuation) to view Inventory Valuation Engine!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $products = Product::all();
        $valuations = [];
        foreach ($products as $p) {
            $valuations[] = array_merge($p, InventoryValuationService::calculateProductValuation($p['id'], $p['valuation_method']));
        }
        $this->render('inventory/valuation', ['title' => 'Inventory Valuation Engine (FIFO / LIFO / Weighted Avg)', 'valuations' => $valuations]);
    }

    /**
     * Export Inventory Stock Levels & Bin Locations to CSV
     */
    public function exportCsv(): void {
        if (!has_permission('inventory.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to export inventory!', 'danger');
            (new Response())->redirect(url('/inventory'));
            return;
        }

        $db = Database::getInstance();
        $stocks = $db->query("
            SELECT s.id, p.name AS product_name, p.sku, p.barcode, c.name AS category_name,
                   w.name AS warehouse_name, bn.code AS bin_code,
                   s.batch_no, s.mfg_date, s.exp_date, s.qty, p.purchase_rate,
                   (s.qty * p.purchase_rate) AS total_valuation
            FROM inventory_stocks s
            JOIN products p ON s.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            JOIN warehouses w ON s.warehouse_id = w.id
            LEFT JOIN bins bn ON s.bin_id = bn.id
            ORDER BY p.name ASC, w.name ASC
        ")->fetchAll();

        $headers = [
            'Record ID', 'Product Name', 'SKU', 'Barcode', 'Category',
            'Warehouse', 'Bin Location', 'Batch No', 'Mfg Date', 'Expiry Date',
            'Quantity in Stock', 'Unit Cost (INR)', 'Total Valuation (INR)'
        ];

        $data = [];
        foreach ($stocks as $s) {
            $data[] = [
                $s['id'],
                $s['product_name'],
                $s['sku'],
                $s['barcode'] ?? '',
                $s['category_name'] ?? 'General',
                $s['warehouse_name'],
                $s['bin_code'] ?? 'MAIN-STORAGE',
                $s['batch_no'] ?? 'N/A',
                $s['mfg_date'] ?? 'N/A',
                $s['exp_date'] ?? 'N/A',
                $s['qty'],
                $s['purchase_rate'],
                $s['total_valuation']
            ];
        }

        ExcelExportService::downloadCsv('enterprise_erp_inventory_stock_' . date('Ymd_His'), $headers, $data);
    }
}

