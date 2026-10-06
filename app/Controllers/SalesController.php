<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AuditService;
use Exception;

class SalesController extends Controller {

    /* ─── MAIN DASHBOARD & ORDERS LIST ─── */
    public function index(string $activeTab = 'orders'): void {
        if (!has_permission('sales.read') && !has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (sales.read) to view Sales Management!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $orders = SalesOrder::getDetailedOrders();
        
        $orderItems = $db->query("
            SELECT soi.*, p.name AS product_name, p.sku, c.name AS category_name, b.name AS brand_name, u.code AS unit_code
            FROM sales_order_items soi
            JOIN products p ON soi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
        ")->fetchAll();

        $orderItemsMap = [];
        foreach ($orderItems as $item) {
            $orderItemsMap[$item['order_id']][] = $item;
        }

        $quotations = $db->query("
            SELECT sq.*, c.name AS customer_name,
                   (SELECT p.name FROM sales_quotation_items sqi JOIN products p ON sqi.product_id = p.id WHERE sqi.quotation_id = sq.id LIMIT 1) AS product_name,
                   (SELECT sqi.qty FROM sales_quotation_items sqi WHERE sqi.quotation_id = sq.id LIMIT 1) AS product_qty
            FROM sales_quotations sq
            JOIN customers c ON sq.customer_id = c.id
            ORDER BY sq.id DESC
        ")->fetchAll();

        $challans = $db->query("
            SELECT dc.*, so.order_no, c.name AS customer_name
            FROM sales_delivery_challans dc
            JOIN sales_orders so ON dc.order_id = so.id
            JOIN customers c ON dc.customer_id = c.id
            ORDER BY dc.id DESC
        ")->fetchAll();

        $invoices = $db->query("
            SELECT si.*, so.order_no, c.name AS customer_name, sp.payment_no, sp.payment_mode
            FROM sales_invoices si
            JOIN sales_orders so ON si.order_id = so.id
            JOIN customers c ON si.customer_id = c.id
            LEFT JOIN sales_payments sp ON sp.invoice_id = si.id
            ORDER BY si.id DESC
        ")->fetchAll();

        $payments = $db->query("
            SELECT sp.*, si.invoice_no, c.name AS customer_name
            FROM sales_payments sp
            JOIN sales_invoices si ON sp.invoice_id = si.id
            JOIN customers c ON sp.customer_id = c.id
            ORDER BY sp.id DESC
        ")->fetchAll();

        $returns = $db->query("
            SELECT sr.*, so.order_no, c.name AS customer_name
            FROM sales_returns sr
            JOIN sales_orders so ON sr.order_id = so.id
            JOIN customers c ON sr.customer_id = c.id
            ORDER BY sr.id DESC
        ")->fetchAll();

        $this->render('sales/index', [
            'title' => 'Sales Management & O2C Pipeline',
            'orders' => $orders,
            'quotations' => $quotations,
            'challans' => $challans,
            'invoices' => $invoices,
            'payments' => $payments,
            'returns' => $returns,
            'orderItemsMap' => $orderItemsMap,
            'activeTab' => $activeTab
        ]);
    }

    /* ─── STAGE 1: QUOTATIONS ─── */
    public function quotations(): void {
        $this->index('quotations');
    }

    public function invoices(): void {
        $this->index('invoices');
    }

    public function payments(): void {
        $this->index('payments');
    }

    public function returns(): void {
        $this->index('returns');
    }

    public function createQuotation(): void {
        if (!has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/sales'));
            return;
        }
        $customers = Customer::all();
        $products = Product::all();
        $this->render('sales/create_quotation', [
            'title' => 'Create Sales Quotation',
            'customers' => $customers,
            'products' => $products
        ]);
    }

    public function storeQuotation(): void {
        if (!has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/sales'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['customer_id']) || empty($data['product_id']) || empty($data['qty'])) {
            Session::setFlash('error', 'Customer, product, and quantity required.', 'danger');
            $response->redirect(url('/sales/quotations/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $product = Product::find((int)$data['product_id']);
            if (!$product) throw new Exception("Selected product does not exist.");

            $qty = (int)$data['qty'];
            if ($qty <= 0) throw new Exception("Quotation quantity must be at least 1.");

            $price = !empty($data['unit_price']) && (float)$data['unit_price'] > 0 
                     ? (float)$data['unit_price'] 
                     : (float)($product['selling_rate'] ?: 0);

            if ($price <= 0) {
                throw new Exception("Invalid Unit Selling Price! Quotation price must be greater than ₹0.00.");
            }

            $taxRate = isset($product['tax_rate']) && $product['tax_rate'] !== null ? (float)$product['tax_rate'] : 18.00;
            $subtotal = $qty * $price;
            $tax = round($subtotal * ($taxRate / 100), 2);
            $total = $subtotal + $tax;

            $user = auth_user();
            $userId = !empty($user['id']) ? (int)$user['id'] : 17;
            $requestId = !empty($data['request_id']) ? (int)$data['request_id'] : 0;

            if ($requestId > 0) {
                // Update existing quotation (the RFQ)
                $qRow = $db->prepare("SELECT quotation_no FROM sales_quotations WHERE id = :id LIMIT 1");
                $qRow->execute(['id' => $requestId]);
                $currNo = $qRow->fetchColumn() ?: '';
                $qNo = str_replace('REQ-', 'SQ-', $currNo);

                $db->prepare("
                    UPDATE sales_quotations 
                    SET quotation_no = :qno, customer_id = :cid, subtotal = :sub, tax_amount = :tax, total_amount = :tot, status = 'pending_quote', created_by = :uid 
                    WHERE id = :qid
                ")->execute([
                    'qno' => $qNo,
                    'cid' => (int)$data['customer_id'],
                    'sub' => $subtotal,
                    'tax' => $tax,
                    'tot' => $total,
                    'uid' => $userId,
                    'qid' => $requestId
                ]);
                $qId = $requestId;

                // Clear existing items
                $db->prepare("DELETE FROM sales_quotation_items WHERE quotation_id = :qid")->execute(['qid' => $qId]);
            } else {
                // Insert a new manual quotation
                $qNo = 'SQ-' . date('Y') . '-' . rand(1000, 9999);
                $stmt = $db->prepare("INSERT INTO sales_quotations (quotation_no, customer_id, quotation_date, valid_until, subtotal, tax_amount, total_amount, status, created_by) VALUES (:qno, :cid, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), :sub, :tax, :tot, 'sent', :uid)");
                $stmt->execute([
                    'qno' => $qNo,
                    'cid' => (int)$data['customer_id'],
                    'sub' => $subtotal,
                    'tax' => $tax,
                    'tot' => $total,
                    'uid' => $userId
                ]);
                $qId = (int)$db->lastInsertId();
            }

            // Insert quotation items
            $db->prepare("INSERT INTO sales_quotation_items (quotation_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:qid, :pid, :uid, :qty, :price, :trate, :tax, :tot)")->execute([
                'qid' => $qId,
                'pid' => $product['id'],
                'uid' => $product['unit_id'] ?: 1,
                'qty' => $qty,
                'price' => $price,
                'trate' => $taxRate,
                'tax' => $tax,
                'tot' => $total
            ]);

            Database::commit();
            AuditService::log('Sales', 'CREATE_QUOTATION', $qId, null, $data);
            Session::setFlash('success', "Sales Quotation {$qNo} generated successfully with Total Value " . format_currency($total) . "!", 'success');
            $response->redirect(url('/sales'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/sales/quotations/create'));
        }
    }

    public function convertQuotationToOrder(int $id): void {
        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $stmt = $db->prepare("SELECT * FROM sales_quotations WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $q = $stmt->fetch();

            if (!$q) throw new Exception("Quotation not found.");

            $qItems = $db->query("SELECT * FROM sales_quotation_items WHERE quotation_id = {$id}")->fetchAll();
            if (empty($qItems)) throw new Exception("No quotation items found to convert.");

            $user = auth_user();
            $userId = !empty($user['id']) ? (int)$user['id'] : 17;
            $orderNo = 'SO-' . date('Y') . '-' . rand(1000, 9999);

            $stmt = $db->prepare("INSERT INTO sales_orders (order_no, customer_id, company_id, branch_id, warehouse_id, order_date, subtotal, tax_amount, total_amount, status, created_by) VALUES (:sono, :cust_id, 1, 1, 1, NOW(), :subtotal, :tax, :total, 'approved', :uid)");
            $stmt->execute([
                'sono' => $orderNo,
                'cust_id' => $q['customer_id'],
                'subtotal' => $q['subtotal'],
                'tax' => $q['tax_amount'],
                'total' => $q['total_amount'],
                'uid' => $userId
            ]);
            $soId = (int)$db->lastInsertId();

            foreach ($qItems as $item) {
                $trate = isset($item['tax_rate']) && $item['tax_rate'] !== null ? (float)$item['tax_rate'] : 18.00;
                $db->prepare("INSERT INTO sales_order_items (order_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:soid, :pid, :uid, :qty, :price, :trate, :tax, :total)")->execute([
                    'soid' => $soId,
                    'pid' => $item['product_id'],
                    'uid' => $item['unit_id'] ?: 1,
                    'qty' => $item['qty'],
                    'price' => $item['unit_price'],
                    'trate' => $trate,
                    'tax' => $item['tax_amount'],
                    'total' => $item['total_price']
                ]);
            }

            $db->prepare("UPDATE sales_quotations SET status = 'converted' WHERE id = :id")->execute(['id' => $id]);

            Database::commit();
            AuditService::log('Sales', 'CONVERT_QUOTATION_TO_SO', $soId, null, ['quotation_id' => $id]);
            Session::setFlash('success', "Quotation converted to Sales Order {$orderNo} successfully!", 'success');
            $response = new Response();
            $response->redirect(url('/sales'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            (new Response())->redirect(url('/sales'));
        }
    }

    public function sendQuotationToCustomer(int $id): void {
        $db = Database::getInstance();
        $response = new Response();
        try {
            $stmt = $db->prepare("SELECT sq.*, c.name AS customer_name FROM sales_quotations sq JOIN customers c ON sq.customer_id = c.id WHERE sq.id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $q = $stmt->fetch();

            if (!$q) throw new Exception("Quotation not found.");

            // Update status to active (formal quotation sent to customer)
            $db->prepare("UPDATE sales_quotations SET status = 'active' WHERE id = :id")->execute(['id' => $id]);

            // Notify Customer via Notification
            $db->prepare("
                INSERT INTO notifications (user_id, role_target, title, message, link, is_read)
                VALUES (NULL, 'customer', '📄 Formal Quotation Received!', :msg, '/customer-portal/quotations', 0)
            ")->execute(['msg' => "Formal Quotation {$q['quotation_no']} for " . format_currency($q['total_amount']) . " has been issued for {$q['customer_name']}. Please log in to accept & place your order."]);

            AuditService::log('Sales', 'SEND_QUOTATION_TO_CUSTOMER', $id, null, $q);
            Session::setFlash('success', "Formal Quotation {$q['quotation_no']} sent to Customer {$q['customer_name']}!", 'success');
        } catch (Exception $e) {
            Session::setFlash('error', $e->getMessage(), 'danger');
        }
        $response->redirect(url('/sales'));
    }

    public function dispatchOrder(int $id): void {
        $db = Database::getInstance();
        $response = new Response();
        $user = auth_user();
        $userId = !empty($user['id']) ? (int)$user['id'] : 17;

        try {
            Database::beginTransaction();

            // Find quotation / order
            $qStmt = $db->prepare("SELECT sq.*, c.name AS customer_name FROM sales_quotations sq JOIN customers c ON sq.customer_id = c.id WHERE sq.id = :id LIMIT 1");
            $qStmt->execute(['id' => $id]);
            $q = $qStmt->fetch();

            if (!$q) throw new Exception("Quotation or Order request not found.");

            // Create dedicated Sales Order for this quotation/request
            $orderNo = 'SO-' . date('Y') . '-' . rand(1000, 9999);
            $targetWid = (int)($q['warehouse_id'] ?: 1);
            $db->prepare("INSERT INTO sales_orders (order_no, customer_id, company_id, branch_id, warehouse_id, order_date, subtotal, tax_amount, total_amount, status, created_by) VALUES (:ono, :cid, 1, 1, :wid, NOW(), :sub, :tax, :tot, 'dispatched', :uid)")->execute([
                'ono' => $orderNo,
                'cid' => $q['customer_id'],
                'wid' => $targetWid,
                'sub' => $q['subtotal'],
                'tax' => $q['tax_amount'],
                'tot' => $q['total_amount'],
                'uid' => $userId
            ]);
            $soId = (int)$db->lastInsertId();
            $soNo = $orderNo;

            // Copy items from quotation to sales_order_items
            $qItems = $db->query("SELECT * FROM sales_quotation_items WHERE quotation_id = {$q['id']}")->fetchAll();
            foreach ($qItems as $qi) {
                $db->prepare("INSERT INTO sales_order_items (order_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:oid, :pid, :uid, :qty, :price, :trate, :tamt, :tot)")->execute([
                    'oid' => $soId,
                    'pid' => $qi['product_id'],
                    'uid' => $qi['unit_id'] ?: 1,
                    'qty' => $qi['qty'],
                    'price' => $qi['unit_price'],
                    'trate' => $qi['tax_rate'] ?: 18.00,
                    'tamt' => $qi['tax_amount'],
                    'tot' => $qi['total_price']
                ]);
            }

            // 1. Create Delivery Challan
            $dcNo = 'DC-' . date('Y') . '-' . rand(1000, 9999);
            $dcStmt = $db->prepare("
                INSERT INTO sales_delivery_challans (challan_no, order_id, customer_id, dispatch_date, vehicle_no, driver_name, status, created_by)
                VALUES (:cno, :oid, :cid, NOW(), 'MH-12-EX-4491', 'Express Logistics', 'dispatched', :uid)
            ");
            $dcStmt->execute([
                'cno' => $dcNo,
                'oid' => $soId,
                'cid' => $q['customer_id'],
                'uid' => $userId
            ]);

            // Deduct stock levels in warehouse and post movement ledger (FIFO)
            if ($soId > 0) {
                $itemsStmt = $db->prepare("SELECT soi.*, p.name AS product_name FROM sales_order_items soi JOIN products p ON soi.product_id = p.id WHERE soi.order_id = :oid");
                $itemsStmt->execute(['oid' => $soId]);
                $orderItems = $itemsStmt->fetchAll();

                foreach ($orderItems as $item) {
                    $pid = (int)$item['product_id'];
                    $qtyToDeduct = (int)$item['qty'];
                    $pName = $item['product_name'] ?? "Product #{$pid}";

                    // Fetch active stock batches with positive quantity for this product, prioritizing target warehouse
                    $targetWid = (int)($so['warehouse_id'] ?? $q['warehouse_id'] ?? 1);
                    $stockStmt = $db->prepare("
                        SELECT * FROM inventory_stocks 
                        WHERE product_id = :pid AND qty > 0
                        ORDER BY (warehouse_id = :wid) DESC, id ASC
                    ");
                    $stockStmt->execute(['pid' => $pid, 'wid' => $targetWid]);
                    $batches = $stockStmt->fetchAll();

                    if (empty($batches)) {
                        throw new Exception("No available stock in any warehouse for {$pName} (SKU/ID: {$pid})!");
                    }

                    $remainingToDeduct = $qtyToDeduct;
                    foreach ($batches as $batch) {
                        if ($remainingToDeduct <= 0) break;

                        $deductFromThisBatch = min($remainingToDeduct, $batch['qty']);
                        $newBatchQty = $batch['qty'] - $deductFromThisBatch;

                        // Update inventory_stocks
                        $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :id")
                           ->execute(['qty' => $newBatchQty, 'id' => $batch['id']]);

                        // Ledger Entry
                        $db->prepare("
                            INSERT INTO stock_transactions 
                            (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by)
                            VALUES (:tno, :wid, :bid, :pid, :batch, 'SALES_ISSUE', 'SALES_ORDER', :soid, 0, :out, :bal, :cost, :tot, :uid)
                        ")->execute([
                            'tno' => 'TXN-SO-' . time() . '-' . rand(10, 99),
                            'wid' => $batch['warehouse_id'],
                            'bid' => $batch['bin_id'],
                            'pid' => $pid,
                            'batch' => $batch['batch_no'],
                            'soid' => $soId,
                            'out' => $deductFromThisBatch,
                            'bal' => $newBatchQty,
                            'cost' => $batch['valuation_rate'],
                            'tot' => $batch['valuation_rate'] * $deductFromThisBatch,
                            'uid' => $userId
                        ]);

                        $remainingToDeduct -= $deductFromThisBatch;
                    }

                    if ($remainingToDeduct > 0) {
                        throw new Exception("Insufficient stock for {$pName}! Needed {$qtyToDeduct} units, but missing {$remainingToDeduct} units across warehouses.");
                    }
                }
            }

            // 2. Generate Tax Invoice
            $invNo = 'INV-' . date('Y') . '-' . rand(1000, 9999);
            $invStmt = $db->prepare("
                INSERT INTO sales_invoices (invoice_no, order_id, customer_id, invoice_date, due_date, total_amount, paid_amount, status)
                VALUES (:ino, :oid, :cid, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), :tot, 0.00, 'unpaid')
            ");
            $invStmt->execute([
                'ino' => $invNo,
                'oid' => $soId,
                'cid' => $q['customer_id'],
                'tot' => $q['total_amount']
            ]);

            // 3. Update Quotation and SO status
            $db->prepare("UPDATE sales_quotations SET status = 'dispatched' WHERE id = :id")->execute(['id' => $id]);
            if ($soId > 0) {
                $db->prepare("UPDATE sales_orders SET status = 'dispatched' WHERE id = :id")->execute(['id' => $soId]);
            }

            // 4. Send Bell Icon Notification to Customer
            $db->prepare("
                INSERT INTO notifications (user_id, role_target, title, message, link, is_read)
                VALUES (NULL, 'customer', '🚚 Order Dispatched & Tax Invoice Issued!', :msg, '/customer-portal/dashboard', 0)
            ")->execute(['msg' => "Your Order {$soNo} has been approved and dispatched! Delivery Challan {$dcNo} and Tax Invoice {$invNo} are now active in your portal."]);

            Database::commit();
            AuditService::log('Sales', 'DISPATCH_ORDER_AND_INVOICE', $id, null, ['challan_no' => $dcNo, 'invoice_no' => $invNo]);
            Session::setFlash('success', "🎉 Order {$soNo} Approved & Dispatched! Delivery Challan {$dcNo} & Tax Invoice {$invNo} generated and sent to customer.", 'success');

        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to dispatch order: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/sales'));
    }

    public function rejectRequest(int $id): void {
        $db = Database::getInstance();
        $response = new Response();
        $user = auth_user();
        $userId = !empty($user['id']) ? (int)$user['id'] : 17;

        try {
            Database::beginTransaction();

            // Find quotation / request
            $qStmt = $db->prepare("SELECT sq.*, c.name AS customer_name FROM sales_quotations sq JOIN customers c ON sq.customer_id = c.id WHERE sq.id = :id LIMIT 1");
            $qStmt->execute(['id' => $id]);
            $q = $qStmt->fetch();

            if (!$q) throw new Exception("Quotation or Order request not found.");

            // Find linked Sales Order
            $soStmt = $db->prepare("SELECT * FROM sales_orders WHERE customer_id = :cid ORDER BY id DESC LIMIT 1");
            $soStmt->execute(['cid' => $q['customer_id']]);
            $so = $soStmt->fetch();
            $soId = $so ? (int)$so['id'] : 0;

            // 1. Update quotation status to rejected
            $db->prepare("UPDATE sales_quotations SET status = 'rejected' WHERE id = :id")->execute(['id' => $id]);

            // 2. If a Sales Order was created, update its status to rejected
            if ($soId > 0 && ($q['status'] === 'converted' || $q['status'] === 'approved')) {
                $db->prepare("UPDATE sales_orders SET status = 'rejected' WHERE id = :id")->execute(['id' => $soId]);
            }

            // 3. Send Notification to Customer
            $db->prepare("
                INSERT INTO notifications (user_id, role_target, title, message, link, is_read)
                VALUES (NULL, 'customer', '❌ Order Request Rejected', :msg, '/customer-portal/dashboard', 0)
            ")->execute(['msg' => "Your Order Request {$q['quotation_no']} was reviewed and rejected by the admin team. Please contact your account manager for details."]);

            Database::commit();
            AuditService::log('Sales', 'REJECT_CUSTOMER_REQUEST', $id, null, ['quotation_no' => $q['quotation_no']]);
            Session::setFlash('warning', "❌ Customer Request {$q['quotation_no']} has been rejected. Notification sent to customer.", 'warning');

        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to reject request: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/sales'));
    }

    /* ─── STAGE 2: SALES ORDERS ─── */
    public function create(): void {
        if (!has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (sales.create) to create Sales Orders!', 'danger');
            (new Response())->redirect(url('/sales'));
            return;
        }

        $db = Database::getInstance();
        $customers = Customer::all();
        $warehouses = Warehouse::all();

        $products = $db->query("
            SELECT p.*, c.name AS category_name, b.name AS brand_name, u.code AS unit_code
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
            ORDER BY p.name ASC
        ")->fetchAll();

        // Calculate real-time available stock map per product and per warehouse
        $stockRows = $db->query("
            SELECT product_id, warehouse_id, SUM(qty) AS total_qty
            FROM inventory_stocks
            GROUP BY product_id, warehouse_id
        ")->fetchAll();

        $stockMap = [];
        foreach ($stockRows as $r) {
            $stockMap[$r['product_id']][$r['warehouse_id']] = (int)$r['total_qty'];
        }

        // Fetch product attributes for variant display
        $paRows = $db->query("
            SELECT pa.product_id, a.name AS attribute_name, pa.attribute_value AS value
            FROM product_attributes pa
            JOIN attributes a ON pa.attribute_id = a.id
        ")->fetchAll();

        $productAttributes = [];
        foreach ($paRows as $pa) {
            $productAttributes[$pa['product_id']][] = [
                'name' => $pa['attribute_name'],
                'value' => $pa['value']
            ];
        }

        $this->render('sales/create', [
            'title' => 'Create Sales Order',
            'customers' => $customers,
            'products' => $products,
            'warehouses' => $warehouses,
            'stockMap' => $stockMap,
            'productAttributes' => $productAttributes
        ]);
    }

    public function store(): void {
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $userId = !empty($user['id']) ? (int)$user['id'] : 17;
        $data = $request->getBody();

        if (empty($data['customer_id']) || empty($data['product_id']) || empty($data['qty'])) {
            Session::setFlash('error', 'Customer, product, and quantity required.', 'danger');
            $response->redirect(url('/sales/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $product = Product::find((int)$data['product_id']);
            if (!$product) throw new Exception("Selected product does not exist.");

            $qty = (int)$data['qty'];
            if ($qty <= 0) throw new Exception("Order quantity must be at least 1.");

            $warehouseId = !empty($data['warehouse_id']) ? (int)$data['warehouse_id'] : 1;

            $unitPrice = !empty($data['unit_price']) && (float)$data['unit_price'] > 0 
                         ? (float)$data['unit_price'] 
                         : (float)($product['selling_rate'] ?: 0);

            if ($unitPrice <= 0) {
                throw new Exception("Invalid Unit Price! Product selling price must be greater than ₹0.00.");
            }

            // Stock availability check (Negative stock disallowed)
            $stockCheck = $db->prepare("SELECT SUM(qty) AS total_avail FROM inventory_stocks WHERE product_id = :pid AND warehouse_id = :wid");
            $stockCheck->execute(['pid' => $product['id'], 'wid' => $warehouseId]);
            $avail = (int) $stockCheck->fetchColumn();

            if ($avail < $qty) {
                throw new Exception("Negative stock disallowed! Available in warehouse: {$avail}, Requested: {$qty}");
            }

            $orderNo = 'SO-' . date('Y') . '-' . rand(1000, 9999);
            $taxRate = isset($product['tax_rate']) && $product['tax_rate'] !== null ? (float)$product['tax_rate'] : 18.00;
            $unitPrice = !empty($data['unit_price']) ? (float)$data['unit_price'] : (float)$product['selling_rate'];
            $subtotal = $qty * $unitPrice;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = $subtotal + $taxAmount;

            $stmt = $db->prepare("INSERT INTO sales_orders (order_no, customer_id, company_id, branch_id, warehouse_id, order_date, subtotal, tax_amount, total_amount, status, created_by) VALUES (:sono, :cust_id, :cid, :bid, :wid, NOW(), :subtotal, :tax, :total, 'approved', :uid)");
            $stmt->execute([
                'sono' => $orderNo,
                'cust_id' => (int)$data['customer_id'],
                'cid' => !empty($user['company_id']) ? (int)$user['company_id'] : 1,
                'bid' => !empty($user['branch_id']) ? (int)$user['branch_id'] : 1,
                'wid' => $warehouseId,
                'subtotal' => $subtotal,
                'tax' => $taxAmount,
                'total' => $totalAmount,
                'uid' => $userId
            ]);
            $soId = (int)$db->lastInsertId();

            $db->prepare("INSERT INTO sales_order_items (order_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:soid, :pid, :uid, :qty, :price, :trate, :tax, :total)")->execute([
                'soid' => $soId,
                'pid' => $product['id'],
                'uid' => $product['unit_id'] ?: 1,
                'qty' => $qty,
                'price' => $unitPrice,
                'trate' => $taxRate,
                'tax' => $taxAmount,
                'total' => $totalAmount
            ]);

            // AUTO INVENTORY DEDUCTION (Delivery Challan Dispatched)
            $db->prepare("UPDATE inventory_stocks SET qty = qty - :qty WHERE warehouse_id = :wid AND product_id = :pid ORDER BY id ASC LIMIT 1")->execute([
                'qty' => $qty,
                'wid' => $warehouseId,
                'pid' => $product['id']
            ]);

            // Post Stock Ledger
            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, 1, :pid, 'SALES_ISSUE', 'SALES_ORDER', :ref_id, 0, :out, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-SO-' . time(),
                'wid' => $warehouseId,
                'pid' => $product['id'],
                'ref_id' => $soId,
                'out' => $qty,
                'bal' => $avail - $qty,
                'cost' => $product['purchase_rate'] ?: ($unitPrice * 0.8),
                'tot' => ($product['purchase_rate'] ?: ($unitPrice * 0.8)) * $qty,
                'uid' => $userId
            ]);

            // Auto generate Delivery Challan
            $dcNo = 'DC-' . date('Y') . '-' . rand(1000, 9999);
            $db->prepare("INSERT INTO sales_delivery_challans (challan_no, order_id, customer_id, dispatch_date, vehicle_no, driver_name, status, created_by) VALUES (:cno, :soid, :cust_id, NOW(), :veh, 'Logistics Express', 'delivered', :uid)")->execute([
                'cno' => $dcNo,
                'soid' => $soId,
                'cust_id' => (int)$data['customer_id'],
                'veh' => 'GJ-01-AB-' . rand(1000, 9999),
                'uid' => $userId
            ]);

            // Auto generate Tax Invoice & Payment
            $invNo = 'INV-' . date('Y') . '-' . rand(1000, 9999);
            $db->prepare("INSERT INTO sales_invoices (invoice_no, order_id, customer_id, invoice_date, due_date, total_amount, paid_amount, status) VALUES (:ino, :soid, :cust_id, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), :tot, :paid, 'paid')")->execute([
                'ino' => $invNo,
                'soid' => $soId,
                'cust_id' => (int)$data['customer_id'],
                'tot' => $totalAmount,
                'paid' => $totalAmount
            ]);
            $invId = (int)$db->lastInsertId();

            $payNo = 'PAY-' . date('Y') . '-' . rand(1000, 9999);
            $db->prepare("INSERT INTO sales_payments (payment_no, invoice_id, customer_id, payment_date, amount, payment_mode, reference_no) VALUES (:pno, :iid, :cust_id, NOW(), :amt, 'upi', :ref)")->execute([
                'pno' => $payNo,
                'iid' => $invId,
                'cust_id' => (int)$data['customer_id'],
                'amt' => $totalAmount,
                'ref' => 'UTR-HDFC-' . rand(100000, 999999)
            ]);

            Database::commit();
            AuditService::log('Sales', 'CREATE_SO', $soId, null, $data);
            Session::setFlash('success', "Sales Order {$orderNo}, Delivery Challan {$dcNo} & Tax Invoice {$invNo} created cleanly! Inventory stock updated automatically.", 'success');
            $response->redirect(url('/sales'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage(), 'danger');
            $response->redirect(url('/sales/create'));
        }
    }

    /* ─── STAGE 6: SALES RETURNS (RESTOCKS INVENTORY AUTOMATICALY) ─── */
    public function createReturn(): void {
        $db = Database::getInstance();
        $orders = $db->query("
            SELECT so.*, c.name AS customer_name, si.invoice_no
            FROM sales_orders so
            JOIN customers c ON so.customer_id = c.id
            LEFT JOIN sales_invoices si ON si.order_id = so.id
            ORDER BY so.id DESC
        ")->fetchAll();

        $this->render('sales/create_return', [
            'title' => 'Create Sales Return (Restock Inventory)',
            'orders' => $orders
        ]);
    }

    public function storeReturn(): void {
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $userId = !empty($user['id']) ? (int)$user['id'] : 17;
        $data = $request->getBody();

        if (empty($data['order_id']) || empty($data['qty'])) {
            Session::setFlash('error', 'Order ID and return quantity required.', 'danger');
            $response->redirect(url('/sales/returns/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $orderId = (int)$data['order_id'];
            $qty = (int)$data['qty'];

            $soStmt = $db->prepare("SELECT * FROM sales_orders WHERE id = :id LIMIT 1");
            $soStmt->execute(['id' => $orderId]);
            $order = $soStmt->fetch();

            if (!$order) throw new Exception("Sales Order not found.");

            $productId = !empty($data['product_id']) ? (int)$data['product_id'] : 0;
            if ($productId > 0) {
                $itemStmt = $db->prepare("SELECT * FROM sales_order_items WHERE order_id = :soid AND product_id = :pid LIMIT 1");
                $itemStmt->execute(['soid' => $orderId, 'pid' => $productId]);
            } else {
                $itemStmt = $db->prepare("SELECT * FROM sales_order_items WHERE order_id = :soid ORDER BY id ASC LIMIT 1");
                $itemStmt->execute(['soid' => $orderId]);
            }
            $item = $itemStmt->fetch();

            if (!$item) throw new Exception("Order item record not found for this sales order.");

            if ($qty <= 0) {
                throw new Exception("Return quantity must be at least 1.");
            }

            if ($qty > (int)$item['qty']) {
                throw new Exception("Return quantity ({$qty}) cannot exceed the ordered quantity ({$item['qty']}).");
            }

            $retNo = 'SR-' . date('Y') . '-' . rand(1000, 9999);
            $trate = isset($item['tax_rate']) && $item['tax_rate'] !== null ? (float)$item['tax_rate'] : 18.00;
            $refundAmt = round($qty * (float)$item['unit_price'] * (1 + ($trate / 100)), 2);

            $db->prepare("INSERT INTO sales_returns (return_no, order_id, customer_id, return_date, returned_qty, refund_amount, reason, status, created_by) VALUES (:rno, :soid, :cid, NOW(), :qty, :ref, :reason, 'restocked', :uid)")->execute([
                'rno' => $retNo,
                'soid' => $orderId,
                'cid' => $order['customer_id'],
                'qty' => $qty,
                'ref' => $refundAmt,
                'reason' => trim($data['reason'] ?? 'Customer Return / Damaged Transit'),
                'uid' => $userId
            ]);
            $srId = (int)$db->lastInsertId();

            // AUTOMATIC INVENTORY RESTOCK
            $stockStmt = $db->prepare("SELECT * FROM inventory_stocks WHERE warehouse_id = :wid AND product_id = :pid ORDER BY id ASC LIMIT 1");
            $stockStmt->execute(['wid' => $order['warehouse_id'], 'pid' => $item['product_id']]);
            $existing = $stockStmt->fetch();

            $product = Product::find($item['product_id']);
            $costPrice = (float)($product['purchase_rate'] ?: ((float)$item['unit_price'] * 0.8));

            if ($existing) {
                $newQty = $existing['qty'] + $qty;
                $db->prepare("UPDATE inventory_stocks SET qty = :qty WHERE id = :sid")->execute(['qty' => $newQty, 'sid' => $existing['id']]);
            } else {
                $newQty = $qty;
                $db->prepare("INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, qty, valuation_rate) VALUES (:wid, 1, :pid, 'BATCH-RESTOCK', :qty, :val)")->execute([
                    'wid' => $order['warehouse_id'],
                    'pid' => $item['product_id'],
                    'qty' => $qty,
                    'val' => $costPrice
                ]);
            }

            // Post Stock Ledger Transaction
            $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, 1, :pid, 'SALES_RETURN', 'SALES_RETURN', :ref_id, :in, 0, :bal, :cost, :tot, :uid)")->execute([
                'tno' => 'TXN-SR-' . time(),
                'wid' => $order['warehouse_id'],
                'pid' => $item['product_id'],
                'ref_id' => $srId,
                'in' => $qty,
                'bal' => $newQty,
                'cost' => $costPrice,
                'tot' => $costPrice * $qty,
                'uid' => $userId
            ]);

            Database::commit();
            AuditService::log('Sales', 'CREATE_SALES_RETURN', $srId, null, $data);
            Session::setFlash('success', "Sales Return {$retNo} processed successfully! Restocked {$qty} units back to Warehouse.", 'success');
            $response->redirect(url('/sales'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Sales Return failed: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/sales/returns/create'));
        }
    }

    public function showInvoice(int $id): void {
        if (!has_permission('sales.read') && !has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (sales.read) to view Sales Invoices!', 'danger');
            (new Response())->redirect(url('/sales'));
            return;
        }

        $db = Database::getInstance();
        $invStmt = $db->prepare("
            SELECT si.*,
                   so.order_no, so.order_date, so.subtotal AS so_subtotal, so.tax_amount AS so_tax, so.total_amount AS so_total,
                   c.name AS customer_name, c.code AS customer_code, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.gstin AS customer_gstin,
                   dc.challan_no, dc.dispatch_date, dc.vehicle_no, dc.driver_name,
                   w.name AS warehouse_name, comp.name AS company_name, comp.tax_id AS company_gstin
            FROM sales_invoices si
            LEFT JOIN sales_orders so ON si.order_id = so.id
            LEFT JOIN customers c ON si.customer_id = c.id
            LEFT JOIN sales_delivery_challans dc ON dc.order_id = so.id
            LEFT JOIN warehouses w ON so.warehouse_id = w.id
            LEFT JOIN companies comp ON so.company_id = comp.id
            WHERE si.id = :id
            LIMIT 1
        ");
        $invStmt->execute(['id' => $id]);
        $inv = $invStmt->fetch();

        if (!$inv) {
            Session::setFlash('error', 'Sales Invoice not found.', 'danger');
            (new Response())->redirect(url('/sales/invoices'));
            return;
        }

        // Fetch invoice line items
        $itemsStmt = $db->prepare("
            SELECT soi.*, p.name AS product_name, p.sku AS product_sku, p.hsn_code, u.code AS unit_name
            FROM sales_order_items soi
            JOIN products p ON soi.product_id = p.id
            LEFT JOIN units u ON soi.unit_id = u.id
            WHERE soi.order_id = :soid
        ");
        $itemsStmt->execute(['soid' => $inv['order_id']]);
        $items = $itemsStmt->fetchAll();

        // Fetch payment history
        $payStmt = $db->prepare("
            SELECT sp.*
            FROM sales_payments sp
            WHERE sp.invoice_id = :iid
            ORDER BY sp.id DESC
        ");
        $payStmt->execute(['iid' => $id]);
        $payments = $payStmt->fetchAll();

        $this->render('sales/show_invoice', [
            'title' => 'Customer Tax Invoice Details - ' . $inv['invoice_no'],
            'inv' => $inv,
            'items' => $items,
            'payments' => $payments
        ]);
    }
}
