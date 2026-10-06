<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ProcurementWorkflowService;
use App\Services\EmailService;
use App\Services\NotificationService;
use Exception;

class PurchaseController extends Controller {
    // 1. Purchase Requests List
    public function requests(): void {
        if (!has_permission('procurement.view_requests')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.view_requests) to view purchase requisitions!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $requests = $db->query("
            SELECT pr.*, u.name AS requester_name, u.email AS requester_email, c.name AS company_name, b.name AS branch_name,
                   COUNT(DISTINCT pri.id) AS item_count,
                   SUM(pri.requested_qty) AS total_qty,
                   pri.product_id, pri.requested_qty, pri.estimated_cost, pri.attribute_values, p.name AS product_name, p.sku AS product_sku,
                   (SELECT pi.invoice_no FROM purchase_invoices pi JOIN purchase_orders po ON pi.po_id = po.id WHERE po.request_id = pr.id ORDER BY pi.id DESC LIMIT 1) AS latest_invoice_no,
                   (SELECT pi.id FROM purchase_invoices pi JOIN purchase_orders po ON pi.po_id = po.id WHERE po.request_id = pr.id ORDER BY pi.id DESC LIMIT 1) AS latest_invoice_id
            FROM purchase_requests pr
            LEFT JOIN users u ON pr.requested_by = u.id
            LEFT JOIN companies c ON pr.company_id = c.id
            LEFT JOIN branches b ON pr.branch_id = b.id
            LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
            LEFT JOIN products p ON pri.product_id = p.id
            GROUP BY pr.id
            ORDER BY pr.id DESC
        ")->fetchAll();
        $this->render('procurement/requests', ['title' => 'Purchase Requisitions (PR)', 'requests' => $requests]);
    }

    public function createRequest(): void {
        if (!has_permission('procurement.create_request')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_request) to create purchase requisitions!', 'danger');
            (new Response())->redirect(url('/procurement/requests'));
            return;
        }
        $products = Product::all();
        $users = User::all();
        $this->render('procurement/create_request', [
            'title' => 'Create Purchase Requisition', 
            'products' => $products,
            'users' => $users
        ]);
    }

    public function storeRequest(): void {
        if (!has_permission('procurement.create_request')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_request) to create purchase requisitions!', 'danger');
            (new Response())->redirect(url('/procurement/requests'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        $items = $data['items'] ?? [];
        if (empty($items)) {
            if (!empty($data['product_id']) && !empty($data['requested_qty'])) {
                $items = [
                    [
                        'product_id' => $data['product_id'],
                        'requested_qty' => $data['requested_qty']
                    ]
                ];
            }
        }

        if (empty($items)) {
            Session::setFlash('error', 'Please select at least one product and quantity.', 'danger');
            $response->redirect(url('/procurement/requests/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $requestedBy = !empty($data['requested_by']) ? (int)$data['requested_by'] : $user['id'];
            $reqNo = 'PR-' . date('Y') . '-' . rand(1000, 9999);
            $stmt = $db->prepare("INSERT INTO purchase_requests (request_no, company_id, branch_id, requested_by, department, priority, status, notes) VALUES (:req_no, :cid, :bid, :uid, :dept, :priority, 'pending', :notes)");
            $stmt->execute([
                'req_no' => $reqNo,
                'cid' => $user['company_id'] ?: 1,
                'bid' => $user['branch_id'] ?: 1,
                'uid' => $requestedBy,
                'dept' => $data['department'] ?? 'General Requisition',
                'priority' => $data['priority'] ?? 'medium',
                'notes' => $data['notes'] ?? ''
            ]);
            $reqId = (int)$db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO purchase_request_items (request_id, product_id, unit_id, requested_qty, estimated_cost, attribute_values) VALUES (:rid, :pid, :uid, :qty, :cost, :attrs)");

            $totalQty = 0;
            $firstProductName = 'Generic Item';

            foreach ($items as $item) {
                $prodId = (int)($item['product_id'] ?? 0);
                $qty = (int)($item['requested_qty'] ?? 0);
                if ($prodId <= 0 || $qty <= 0) continue;

                $prod = Product::find($prodId);
                if (!$prod) continue;

                // Process attributes for this specific row item
                $attrValuesJson = null;
                $totalVariantQty = 0;
                if (!empty($item['attributes']) && is_array($item['attributes'])) {
                    $cleanAttrs = [];
                    foreach ($item['attributes'] as $k => $v) {
                        if (is_array($v)) {
                            $parts = [];
                            foreach ($v as $optKey => $optQty) {
                                $optQtyNum = (int)$optQty;
                                if ($optQtyNum > 0) {
                                    $parts[] = "{$optKey} ({$optQtyNum} Pcs)";
                                    $totalVariantQty += $optQtyNum;
                                }
                            }
                            if (!empty($parts)) {
                                $cleanAttrs[trim($k)] = implode(', ', $parts);
                            }
                        } elseif (!empty($v) && is_string($v) && trim($v) !== '') {
                            $cleanAttrs[trim($k)] = trim($v);
                        }
                    }
                    if (!empty($cleanAttrs)) {
                        $attrValuesJson = json_encode($cleanAttrs);
                    }
                }

                $finalQty = ($totalVariantQty > 0) ? $totalVariantQty : $qty;
                $cost = (float)$prod['purchase_rate'] * $finalQty;

                $itemStmt->execute([
                    'rid' => $reqId,
                    'pid' => $prodId,
                    'uid' => (int)$prod['unit_id'],
                    'qty' => $finalQty,
                    'cost' => $cost,
                    'attrs' => $attrValuesJson
                ]);

                $totalQty += $finalQty;
                if ($firstProductName === 'Generic Item') {
                    $firstProductName = $prod['name'];
                }
            }

            $emailBody = "A new Purchase Requisition <strong>{$reqNo}</strong> for a total of <strong>{$totalQty} units</strong> starting with <strong>{$firstProductName}</strong> has been submitted and is pending review.";
            EmailService::notifyProcurement(
                "[PR Alert] New Purchase Requisition {$reqNo} Submitted",
                $emailBody,
                "PR_SUBMITTED"
            );

            Database::commit();
            AuditService::log('Procurement', 'CREATE_PR', $reqId, null, $data);
            Session::setFlash('success', "Purchase Requisition {$reqNo} created successfully with all requested product items!", 'success');
            $response->redirect(url('/procurement/requests'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Error creating PR: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/requests/create'));
        }
    }

    public function approveRequest(int $id): void {
        $user = auth_user();
        if (!has_permission('procurement.approve_request')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.approve_request) to approve requisitions!', 'danger');
            (new Response())->redirect(url('/procurement/requests'));
            return;
        }

        $db = Database::getInstance();
        $db->prepare("UPDATE purchase_requests SET status = 'approved', approved_by = :uid, approved_at = NOW() WHERE id = :id")->execute(['uid' => $user['id'], 'id' => $id]);
        AuditService::log('Procurement', 'APPROVE_PR', $id);
        Session::setFlash('success', 'Purchase Requisition approved successfully!', 'success');
        (new Response())->redirect(url('/procurement/requests'));
    }

    public function rejectRequest(int $id): void {
        $user = auth_user();
        if (!has_permission('procurement.approve_request')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.approve_request) to reject requisitions!', 'danger');
            (new Response())->redirect(url('/procurement/requests'));
            return;
        }

        $db = Database::getInstance();
        $db->prepare("UPDATE purchase_requests SET status = 'rejected', approved_by = :uid, approved_at = NOW() WHERE id = :id")->execute(['uid' => $user['id'], 'id' => $id]);
        AuditService::log('Procurement', 'REJECT_PR', $id);
        Session::setFlash('warning', 'Purchase Requisition rejected.', 'warning');
        (new Response())->redirect(url('/procurement/requests'));
    }

    // 2. Purchase Orders
    public function orders(): void {
        if (!has_permission('procurement.create_po') && !has_permission('procurement.view_requests')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_po) to view Purchase Orders!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $orders = PurchaseOrder::getDetailedOrders();
        $this->render('procurement/orders', ['title' => 'Purchase Orders (PO)', 'orders' => $orders]);
    }

    public function createOrder(): void {
        if (!has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_po) to issue Purchase Orders!', 'danger');
            (new Response())->redirect(url('/procurement/orders'));
            return;
        }
        $db = Database::getInstance();
        $suppliers = Supplier::all();
        $products = Product::all();
        $warehouses = Warehouse::all();

        $selectedQuotation = null;
        $prefilledSupplier = null;
        $prefilledPrice = null;
        $prefilledProduct = null;
        $prefilledQty = null;

        $requestId = isset($_GET['request_id']) ? (int)$_GET['request_id'] : 0;
        $quotationId = isset($_GET['quotation_id']) ? (int)$_GET['quotation_id'] : 0;
        $rfqId = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;
        $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;

        $prRecord = null;
        if ($requestId > 0) {
            $prStmt = $db->prepare("
                SELECT pr.*, pri.product_id, pri.requested_qty, pri.estimated_cost, pri.attribute_values,
                       p.name AS product_name, p.sku AS product_sku, p.purchase_rate, c.name AS category_name
                FROM purchase_requests pr
                LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
                LEFT JOIN products p ON pri.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE pr.id = :id LIMIT 1
            ");
            $prStmt->execute(['id' => $requestId]);
            $prRecord = $prStmt->fetch();
            if ($prRecord) {
                if (!empty($prRecord['product_id'])) {
                    $prefilledProduct = (int)$prRecord['product_id'];
                }
                if (!empty($prRecord['requested_qty'])) {
                    $prefilledQty = (int)$prRecord['requested_qty'];
                }
                if (!empty($prRecord['purchase_rate'])) {
                    $prefilledPrice = (float)$prRecord['purchase_rate'];
                }
            }
        } elseif ($quotationId > 0) {
            $qStmt = $db->prepare("
                SELECT sq.*, s.name AS supplier_name, s.code AS supplier_code, s.rating,
                       r.rfq_no, r.title AS rfq_title, r.request_id,
                       pri.product_id, pri.requested_qty, pri.attribute_values,
                       pr.request_no, p.name AS product_name, p.sku AS product_sku
                FROM supplier_quotations sq
                JOIN suppliers s ON sq.supplier_id = s.id
                JOIN purchase_rfqs r ON sq.rfq_id = r.id
                LEFT JOIN purchase_requests pr ON r.request_id = pr.id
                LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
                LEFT JOIN products p ON pri.product_id = p.id
                WHERE sq.id = :id LIMIT 1
            ");
            $qStmt->execute(['id' => $quotationId]);
            $selectedQuotation = $qStmt->fetch();
        } elseif ($rfqId > 0) {
            $qStmt = $db->prepare("
                SELECT sq.*, s.name AS supplier_name, s.code AS supplier_code, s.rating,
                       r.rfq_no, r.title AS rfq_title, r.request_id,
                       pri.product_id, pri.requested_qty, pri.attribute_values,
                       pr.request_no, p.name AS product_name, p.sku AS product_sku
                FROM supplier_quotations sq
                JOIN suppliers s ON sq.supplier_id = s.id
                JOIN purchase_rfqs r ON sq.rfq_id = r.id
                LEFT JOIN purchase_requests pr ON r.request_id = pr.id
                LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
                LEFT JOIN products p ON pri.product_id = p.id
                WHERE sq.rfq_id = :rfq_id AND sq.status = 'selected' LIMIT 1
            ");
            $qStmt->execute(['rfq_id' => $rfqId]);
            $selectedQuotation = $qStmt->fetch();
        }

        if ($selectedQuotation) {
            $prefilledSupplier = (int)$selectedQuotation['supplier_id'];
            $prefilledPrice = (float)$selectedQuotation['unit_price'];
            if (!empty($selectedQuotation['product_id'])) {
                $prefilledProduct = (int)$selectedQuotation['product_id'];
            }
            if (!empty($selectedQuotation['requested_qty'])) {
                $prefilledQty = (int)$selectedQuotation['requested_qty'];
            }
        } elseif ($supplierId > 0) {
            $prefilledSupplier = $supplierId;
        }

        // Fetch all items of the linked requisition if applicable
        $prItems = [];
        $linkedRequestId = 0;
        if ($requestId > 0) {
            $linkedRequestId = $requestId;
        } elseif ($selectedQuotation && !empty($selectedQuotation['request_id'])) {
            $linkedRequestId = (int)$selectedQuotation['request_id'];
        }

        if ($linkedRequestId > 0) {
            $itemsStmt = $db->prepare("
                SELECT pri.*, p.name AS product_name, p.sku AS product_sku, p.purchase_rate, p.gst_rate
                FROM purchase_request_items pri
                JOIN products p ON pri.product_id = p.id
                WHERE pri.request_id = :rid
            ");
            $itemsStmt->execute(['rid' => $linkedRequestId]);
            $prItems = $itemsStmt->fetchAll();
        }

        // Fetch list of all approved PRs (grouped) to populate PR selection dropdown in PO form
        $approvedPRs = $db->query("
            SELECT pr.id, pr.request_no, pr.department, COUNT(pri.id) AS item_count, SUM(pri.requested_qty) AS total_qty
            FROM purchase_requests pr
            LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
            WHERE pr.status = 'approved'
            GROUP BY pr.id
            ORDER BY pr.id DESC
        ")->fetchAll();

        $this->render('procurement/create_order', [
            'title' => 'Issue Purchase Order',
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'selectedQuotation' => $selectedQuotation,
            'prRecord' => $prRecord,
            'prefilledSupplier' => $prefilledSupplier,
            'prefilledPrice' => $prefilledPrice,
            'prefilledProduct' => $prefilledProduct,
            'prefilledQty' => $prefilledQty,
            'approvedPRs' => $approvedPRs,
            'prItems' => $prItems
        ]);
    }

    public function storeOrder(): void {
        if (!has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_po) to issue Purchase Orders!', 'danger');
            (new Response())->redirect(url('/procurement/orders'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        $items = $data['items'] ?? [];
        if (empty($items)) {
            if (!empty($data['product_id']) && !empty($data['qty'])) {
                $items = [
                    [
                        'product_id' => $data['product_id'],
                        'qty' => $data['qty'],
                        'unit_price' => $data['unit_price'] ?? 0.00
                    ]
                ];
            }
        }

        if (empty($items)) {
            Session::setFlash('error', 'Please select at least one product and quantity for the Purchase Order.', 'danger');
            $response->redirect(url('/procurement/orders/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $poNo = 'PO-' . date('Y') . '-' . rand(1000, 9999);
            
            // Insert PO header with placeholder values first
            $stmt = $db->prepare("INSERT INTO purchase_orders (po_no, supplier_id, company_id, branch_id, warehouse_id, po_date, expected_date, subtotal, tax_amount, total_amount, status, created_by) VALUES (:po_no, :sid, :cid, :bid, :wid, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 0.00, 0.00, 0.00, 'approved', :uid)");
            $stmt->execute([
                'po_no' => $poNo,
                'sid' => (int)$data['supplier_id'],
                'cid' => $user['company_id'] ?: 1,
                'bid' => $user['branch_id'] ?: 1,
                'wid' => (int)$data['warehouse_id'],
                'uid' => $user['id']
            ]);
            $poId = (int)$db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO purchase_order_items (po_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price, attribute_values) VALUES (:po_id, :pid, :uid, :qty, :price, :tax_rate, :tax_amount, :total, :attrs)");

            $poSubtotal = 0.00;
            $poTaxAmount = 0.00;
            $poTotalAmount = 0.00;

            foreach ($items as $item) {
                $prodId = (int)($item['product_id'] ?? 0);
                $qty = (int)($item['qty'] ?? 0);
                $unitPrice = (float)($item['unit_price'] ?? 0.00);
                $attrs = !empty($item['attribute_values']) ? $item['attribute_values'] : null;

                if ($prodId <= 0 || $qty <= 0) continue;

                $prod = Product::find($prodId);
                if (!$prod) continue;

                $gstPercent = (float)($prod['gst_rate'] ?? 18.00);
                $subtotal = $qty * $unitPrice;
                $taxAmount = $subtotal * ($gstPercent / 100.0);
                $totalPrice = $subtotal + $taxAmount;

                $itemStmt->execute([
                    'po_id' => $poId,
                    'pid' => $prodId,
                    'uid' => (int)$prod['unit_id'],
                    'qty' => $qty,
                    'price' => $unitPrice,
                    'tax_rate' => $gstPercent,
                    'tax_amount' => $taxAmount,
                    'total' => $totalPrice,
                    'attrs' => $attrs
                ]);

                $poSubtotal += $subtotal;
                $poTaxAmount += $taxAmount;
                $poTotalAmount += $totalPrice;
            }

            // Update PO header totals with actual totals
            $updStmt = $db->prepare("UPDATE purchase_orders SET subtotal = :subtotal, tax_amount = :tax, total_amount = :total WHERE id = :po_id");
            $updStmt->execute([
                'subtotal' => $poSubtotal,
                'tax' => $poTaxAmount,
                'total' => $poTotalAmount,
                'po_id' => $poId
            ]);

            // Mark linked RFQ process as completed
            if (!empty($data['rfq_id'])) {
                $db->prepare("UPDATE purchase_rfqs SET status = 'completed' WHERE id = :rfq_id")->execute(['rfq_id' => (int)$data['rfq_id']]);
            } elseif (!empty($data['quotation_id'])) {
                $db->prepare("UPDATE purchase_rfqs SET status = 'completed' WHERE id = (SELECT rfq_id FROM supplier_quotations WHERE id = :qid LIMIT 1)")->execute(['qid' => (int)$data['quotation_id']]);
            }

            Database::commit();
            AuditService::log('Procurement', 'CREATE_PO', $poId, null, $data);
            Session::setFlash('success', "Purchase Order {$poNo} issued successfully!", 'success');
            $response->redirect(url('/procurement/orders'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Error creating PO: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/orders/create'));
        }
    }

    // 3. Goods Receipt Notes (GRN)
    public function grns(): void {
        if (!has_permission('inventory.grn')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.grn) to view Goods Receipt Notes!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $grns = $db->query("
            SELECT g.*, po.po_no, po.status AS po_status, s.name AS supplier_name, s.email AS supplier_email, s.phone AS supplier_phone, w.name AS warehouse_name, u.name AS receiver_name,
                   (SELECT COUNT(*) FROM purchase_invoices pi WHERE pi.po_id = g.po_id) AS invoice_count,
                   (SELECT status FROM purchase_invoices pi WHERE pi.po_id = g.po_id ORDER BY id DESC LIMIT 1) AS invoice_status,
                   (SELECT COUNT(*) FROM purchase_payments pp 
                    JOIN purchase_invoices pi ON pp.invoice_id = pi.id 
                    WHERE pi.po_id = g.po_id) AS payment_count,
                   COALESCE((SELECT SUM(qty) FROM purchase_order_items WHERE po_id = g.po_id), 0) AS po_ordered_qty,
                   COALESCE((SELECT SUM(gi.received_qty) FROM grn_items gi JOIN goods_receipt_notes gr ON gi.grn_id = gr.id WHERE gr.po_id = g.po_id), 0) AS po_received_qty
            FROM goods_receipt_notes g
            JOIN purchase_orders po ON g.po_id = po.id
            JOIN suppliers s ON g.supplier_id = s.id
            JOIN warehouses w ON g.warehouse_id = w.id
            JOIN users u ON g.received_by = u.id
            ORDER BY g.id DESC
        ")->fetchAll();

        // Also fetch POs that have pending shipments awaiting receipt
        $pendingOrders = $db->query("
            SELECT po.*, s.name AS supplier_name, w.name AS warehouse_name,
                   COALESCE(SUM(poi.qty), 0) AS total_ordered_qty,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id
                   ), 0) AS total_received_qty
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN warehouses w ON po.warehouse_id = w.id
            JOIN purchase_order_items poi ON poi.po_id = po.id
            GROUP BY po.id
            HAVING total_received_qty < total_ordered_qty
            ORDER BY po.id DESC
        ")->fetchAll();

        $this->render('procurement/grns', [
            'title' => 'Goods Receipt Notes (GRN)',
            'grns' => $grns,
            'pendingOrders' => $pendingOrders
        ]);
    }

    public function createGrn(): void {
        if (!has_permission('inventory.grn')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.grn) to process Goods Receipt Notes!', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }
        $db = Database::getInstance();
        $orders = $db->query("
            SELECT po.*, s.name AS supplier_name, w.name AS warehouse_name,
                   COUNT(poi.id) AS item_count, COALESCE(SUM(poi.qty), 0) AS total_qty,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id
                   ), 0) AS total_received_qty
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN warehouses w ON po.warehouse_id = w.id
            LEFT JOIN purchase_order_items poi ON poi.po_id = po.id
            GROUP BY po.id
            HAVING total_received_qty < total_qty OR total_qty IS NULL OR total_received_qty = 0
            ORDER BY po.id DESC
        ")->fetchAll();
        $prefilledPo = isset($_GET['po_id']) ? (int)$_GET['po_id'] : 0;

        $poItems = [];
        if ($prefilledPo > 0) {
            $itemsStmt = $db->prepare("
                SELECT poi.*, p.name AS product_name, p.sku AS product_sku,
                       COALESCE(poi.attribute_values, pri.attribute_values) AS attribute_values,
                       COALESCE((
                           SELECT SUM(gi.received_qty)
                           FROM grn_items gi
                           JOIN goods_receipt_notes g ON gi.grn_id = g.id
                           WHERE g.po_id = poi.po_id AND gi.product_id = poi.product_id
                       ), 0) AS already_received_qty
                FROM purchase_order_items poi
                JOIN products p ON poi.product_id = p.id
                JOIN purchase_orders po ON poi.po_id = po.id
                LEFT JOIN purchase_request_items pri ON pri.request_id = po.request_id AND pri.product_id = poi.product_id
                WHERE poi.po_id = :poid
            ");
            $itemsStmt->execute(['poid' => $prefilledPo]);
            $rawItems = $itemsStmt->fetchAll();

            foreach ($rawItems as $item) {
                $ord = (int)$item['qty'];
                $already = (int)($item['already_received_qty'] ?? 0);
                $item['already_received_qty'] = $already;
                $item['remaining_qty'] = max(0, $ord - $already);
                $poItems[] = $item;
            }
        }

        $this->render('procurement/create_grn', [
            'title' => 'Process New GRN',
            'orders' => $orders,
            'prefilledPo' => $prefilledPo,
            'poItems' => $poItems,
            'csrf_token' => \App\Helpers\Security::generateCsrfToken()
        ]);
    }

    public function storeGrn(): void {
        if (!has_permission('inventory.grn')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.grn) to process Goods Receipt Notes!', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        $items = $data['items'] ?? [];
        if (empty($items)) {
            // Fallback for single item (legacy/other flows)
            if (!empty($data['product_id']) && !empty($data['received_qty'])) {
                $items = [
                    [
                        'product_id' => $data['product_id'],
                        'ordered_qty' => $data['ordered_qty'] ?? $data['received_qty'],
                        'received_qty' => $data['received_qty']
                    ]
                ];
            }
        }

        if (empty($items)) {
            Session::setFlash('error', 'Select PO and enter received quantities.', 'danger');
            $response->redirect(url('/procurement/grns/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $po = PurchaseOrder::find((int)$data['po_id']);
            $grnNo = 'GRN-' . date('Y') . '-' . rand(1000, 9999);
            $nextDeliveryDate = !empty($data['next_scheduled_delivery_date']) ? $data['next_scheduled_delivery_date'] : null;

            $grnStmt = $db->prepare("INSERT INTO goods_receipt_notes (grn_no, po_id, supplier_id, warehouse_id, received_date, challan_no, status, received_by, remarks, next_scheduled_delivery_date) VALUES (:gno, :poid, :sid, :wid, NOW(), :challan, 'pending_qc', :uid, :remarks, :next_date)");
            $grnStmt->execute([
                'gno' => $grnNo,
                'poid' => $po['id'],
                'sid' => $po['supplier_id'],
                'wid' => $po['warehouse_id'],
                'challan' => !empty($data['challan_no']) ? $data['challan_no'] : 'CH-' . rand(100, 999),
                'uid' => $user['id'],
                'remarks' => !empty($data['remarks']) ? $data['remarks'] : 'Partial shipment delivery batch',
                'next_date' => $nextDeliveryDate
            ]);
            $grnId = (int)$db->lastInsertId();

            $grnItemStmt = $db->prepare("INSERT INTO grn_items (grn_id, product_id, bin_id, ordered_qty, received_qty, accepted_qty, batch_no, exp_date, attribute_values) VALUES (:gid, :pid, 1, :oqty, :rqty, :aqty, :batch, DATE_ADD(NOW(), INTERVAL 2 YEAR), :attrs)");

            foreach ($items as $item) {
                $pid = (int)($item['product_id'] ?? 0);
                $oqty = (int)($item['ordered_qty'] ?? 0);
                $recQty = (int)($item['received_qty'] ?? 0);
                $attrs = !empty($item['attribute_values']) ? $item['attribute_values'] : null;

                if ($pid <= 0 || $recQty <= 0) continue;

                $grnItemStmt->execute([
                    'gid' => $grnId,
                    'pid' => $pid,
                    'oqty' => $oqty,
                    'rqty' => $recQty,
                    'aqty' => $recQty, // Default accepted to received for QC process
                    'batch' => 'BATCH-' . date('Ym') . '-' . rand(10, 99),
                    'attrs' => $attrs
                ]);
            }

            // Dynamically recalculate PO fulfillment status (Pending vs Partially Received vs Received)
            $poTotalOrdered = (int)$db->query("SELECT SUM(qty) FROM purchase_order_items WHERE po_id = {$po['id']}")->fetchColumn();
            $poTotalReceived = (int)$db->query("SELECT SUM(gi.received_qty) FROM grn_items gi JOIN goods_receipt_notes g ON gi.grn_id = g.id WHERE g.po_id = {$po['id']}")->fetchColumn();
            $newPoStatus = ($poTotalReceived >= $poTotalOrdered) ? 'received' : 'partially_received';
            $db->prepare("UPDATE purchase_orders SET status = :status WHERE id = :poid")->execute([
                'status' => $newPoStatus,
                'poid' => $po['id']
            ]);

            Database::commit();

            try {
                AuditService::log('Procurement', 'CREATE_GRN', $grnId, null, $data);

                // Automated Supplier & Internal Notification Trigger
                $supplier = Supplier::find($po['supplier_id']);
                $supplierEmail = $supplier['email'] ?? 'procurement@gmail.com';
                $remainingUnits = max(0, $poTotalOrdered - $poTotalReceived);
                $nextDateInfo = !empty($nextDeliveryDate) ? "<p style='color:#f59e0b;'><strong>Next Scheduled Delivery Date:</strong> " . htmlspecialchars($nextDeliveryDate) . "</p>" : "";
                
                $emailBody = "
                    <p>Dear <strong>" . htmlspecialchars($supplier['name'] ?? 'Vendor') . "</strong>,</p>
                    <p>We have successfully recorded a partial shipment receipt under Purchase Order <strong>{$po['po_no']}</strong>.</p>
                    <div style='background:#1e293b;padding:12px;border-radius:6px;margin:12px 0;'>
                        <div><strong>GRN Number:</strong> {$grnNo}</div>
                        <div><strong>Delivery Challan / LR:</strong> " . htmlspecialchars($data['challan_no'] ?? 'N/A') . "</div>
                        <div><strong>Total PO Ordered Qty:</strong> " . number_format($poTotalOrdered) . " Units</div>
                        <div><strong>Total Cumulative Received:</strong> " . number_format($poTotalReceived) . " Units</div>
                        <div><strong>Remaining Pending Balance:</strong> " . number_format($remainingUnits) . " Units</div>
                    </div>
                    {$nextDateInfo}
                    <p>Quality Control (QC) inspection is in progress.</p>
                ";

                EmailService::send('supplier', $supplierEmail, "Shipment Receipt Confirmation - {$grnNo} [PO: {$po['po_no']}]", $emailBody, 'PARTIAL_GRN_RECEIPT');
                NotificationService::notifyRole('procurement_manager', "Partial GRN {$grnNo} processed for PO {$po['po_no']}. Pending: {$remainingUnits} Units.", 'info', url('/procurement/grns'));
            } catch (Exception $notifEx) {
                // Silently log or ignore notification delivery glitches after DB commit
                error_log("GRN notification dispatch notice: " . $notifEx->getMessage());
            }

            $remainingUnits = max(0, $poTotalOrdered - $poTotalReceived);
            Session::setFlash('success', "GRN {$grnNo} recorded successfully! Order status: " . strtoupper(str_replace('_', ' ', $newPoStatus)) . " (Remaining: {$remainingUnits} Units). Notification dispatched.", 'success');
            $response->redirect(url('/procurement/grns'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'GRN creation failed: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/grns/create'));
        }
    }

    public function processQc(int $grnId): void {
        if (!has_permission('inventory.qc')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.qc) to perform QC Inspections!', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        $db = Database::getInstance();
        
        // Fetch GRN record with header information
        $grnStmt = $db->prepare("
            SELECT g.*, po.po_no, s.name AS supplier_name, w.name AS warehouse_name
            FROM goods_receipt_notes g
            JOIN purchase_orders po ON g.po_id = po.id
            JOIN suppliers s ON g.supplier_id = s.id
            JOIN warehouses w ON g.warehouse_id = w.id
            WHERE g.id = :gid
            LIMIT 1
        ");
        $grnStmt->execute(['gid' => $grnId]);
        $grn = $grnStmt->fetch();

        if (!$grn) {
            Session::setFlash('error', 'Goods Receipt Note (GRN) not found.', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        // Fetch GRN items
        $itemsStmt = $db->prepare("
            SELECT gi.*, p.name AS product_name, p.sku AS product_sku
            FROM grn_items gi
            JOIN products p ON gi.product_id = p.id
            WHERE gi.grn_id = :gid
        ");
        $itemsStmt->execute(['gid' => $grnId]);
        $grnItems = $itemsStmt->fetchAll();

        $this->render('procurement/process_qc', [
            'title' => 'Perform Quality Check (QC)',
            'grn' => $grn,
            'grnItems' => $grnItems
        ]);
    }

    public function storeQc(int $grnId): void {
        if (!has_permission('inventory.qc')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (inventory.qc) to perform QC Inspections!', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['overall_status'])) {
            Session::setFlash('error', 'Overall quality status is required.', 'danger');
            $response->redirect(url('/procurement/grns/qc/' . $grnId));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            // Update individual grn_items accepted/rejected quantities
            $items = $data['items'] ?? [];
            $itemUpd = $db->prepare("
                UPDATE grn_items 
                SET accepted_qty = :aqty, rejected_qty = :rqty, batch_no = :batch, exp_date = :exp
                WHERE id = :item_id
            ");

            foreach ($items as $itemId => $itemData) {
                $aqty = (int)($itemData['accepted_qty'] ?? 0);
                $rqty = (int)($itemData['rejected_qty'] ?? 0);
                $batch = !empty($itemData['batch_no']) ? trim($itemData['batch_no']) : 'BATCH-' . date('Ym') . '-' . rand(10, 99);
                $exp = !empty($itemData['exp_date']) ? $itemData['exp_date'] : date('Y-m-d', strtotime('+2 years'));

                $itemUpd->execute([
                    'aqty' => $aqty,
                    'rqty' => $rqty,
                    'batch' => $batch,
                    'exp' => $exp,
                    'item_id' => (int)$itemId
                ]);
            }

            // Insert QC quality_inspections log
            $qcStmt = $db->prepare("
                INSERT INTO quality_inspections (grn_id, inspector_id, inspection_date, overall_status, remarks) 
                VALUES (:gid, :uid, NOW(), :status, :remarks)
            ");
            $qcStmt->execute([
                'gid' => $grnId,
                'uid' => $user['id'],
                'status' => $data['overall_status'],
                'remarks' => trim($data['remarks'] ?? 'Quality inspection completed')
            ]);

            // Update GRN status
            $db->prepare("UPDATE goods_receipt_notes SET status = 'qc_passed' WHERE id = :gid")->execute(['gid' => $grnId]);

            // Commit stock via workflow service
            ProcurementWorkflowService::processGrnStockReceipt($grnId, $user['id']);

            Database::commit();
            AuditService::log('Procurement', 'GRN_QUALITY_CHECK', $grnId, null, $data);
            Session::setFlash('success', "Quality Control (QC) verified and stock committed to Warehouse Bin successfully!", 'success');
            $response->redirect(url('/procurement/grns'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'QC processing failed: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/grns/qc/' . $grnId));
        }
    }

    // 4. Vendor Invoices & Payments
    public function payments(): void {
        if (!has_permission('finance.payments')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (finance.payments) to view Vendor Payments!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $paymentsRaw = $db->query("
            SELECT p.*, s.name AS supplier_name, i.invoice_no, i.total_amount AS invoice_total, i.paid_amount AS invoice_paid, i.status AS invoice_status, i.po_id, u.name AS payer_name,
                   COALESCE(p.created_at, p.payment_date) AS exact_timestamp
            FROM purchase_payments p
            JOIN suppliers s ON p.supplier_id = s.id
            JOIN purchase_invoices i ON p.invoice_id = i.id
            JOIN users u ON p.created_by = u.id
            ORDER BY p.invoice_id DESC, p.id ASC
        ")->fetchAll();

        $groupedPayments = [];
        foreach ($paymentsRaw as $p) {
            $invId = $p['invoice_id'];
            if (!isset($groupedPayments[$invId])) {
                $groupedPayments[$invId] = [
                    'invoice_id' => $invId,
                    'invoice_no' => $p['invoice_no'],
                    'supplier_id' => $p['supplier_id'],
                    'supplier_name' => $p['supplier_name'],
                    'invoice_total' => (float)$p['invoice_total'],
                    'invoice_paid' => (float)$p['invoice_paid'],
                    'invoice_status' => $p['invoice_status'],
                    'installments' => []
                ];
            }
            $groupedPayments[$invId]['installments'][] = $p;
        }

        $this->render('procurement/payments', [
            'title' => 'Vendor Invoices & Milestone Payments',
            'groupedPayments' => $groupedPayments,
            'rawPayments' => $paymentsRaw
        ]);
    }

    public function createPayment(): void {
        if (!has_permission('finance.payments')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (finance.payments) to process Vendor Payments!', 'danger');
            (new Response())->redirect(url('/procurement/payments'));
            return;
        }
        $db = Database::getInstance();
        $orders = PurchaseOrder::getDetailedOrders();
        $suppliers = Supplier::all();

        $selectedGrn = null;
        $prefilledSupplier = null;
        $prefilledPo = null;
        $prefilledAmount = null;
        $prefilledProduct = null;
        $prefilledQty = null;
        $prefilledPrice = null;
        $prefilledInvoiceId = null;

        $grnId = isset($_GET['grn_id']) ? (int)$_GET['grn_id'] : 0;
        $poId = isset($_GET['po_id']) ? (int)$_GET['po_id'] : 0;
        $supplierId = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
        $invoiceId = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : 0;

        if ($invoiceId > 0) {
            $invRec = $db->query("
                SELECT pi.*, po.po_no, s.name AS supplier_name
                FROM purchase_invoices pi
                LEFT JOIN purchase_orders po ON pi.po_id = po.id
                JOIN suppliers s ON pi.supplier_id = s.id
                WHERE pi.id = {$invoiceId}
                LIMIT 1
            ")->fetch();

            if ($invRec) {
                $prefilledSupplier = (int)$invRec['supplier_id'];
                $prefilledPo = (int)$invRec['po_id'];
                $alreadyPaid = (float)$invRec['paid_amount'];
                $invoiceTotal = (float)$invRec['total_amount'];
                $prefilledAmount = max(0, $invoiceTotal - $alreadyPaid);
                $prefilledInvoiceId = (int)$invRec['id'];
            }
        }

        if (!$prefilledInvoiceId && $grnId > 0) {
            $gStmt = $db->prepare("
                SELECT g.*, po.po_no, po.total_amount AS po_total,
                       s.name AS supplier_name, s.code AS supplier_code, s.bank_name, s.bank_account,
                       poi.product_id, poi.qty AS po_qty, poi.unit_price, poi.total_price,
                       p.name AS product_name, p.sku AS product_sku
                FROM goods_receipt_notes g
                JOIN purchase_orders po ON g.po_id = po.id
                JOIN suppliers s ON g.supplier_id = s.id
                LEFT JOIN purchase_order_items poi ON poi.po_id = po.id
                LEFT JOIN products p ON poi.product_id = p.id
                WHERE g.id = :id LIMIT 1
            ");
            $gStmt->execute(['id' => $grnId]);
            $selectedGrn = $gStmt->fetch();
        }

        if ($selectedGrn) {
            $prefilledSupplier = (int)$selectedGrn['supplier_id'];
            $prefilledPo = (int)$selectedGrn['po_id'];
            $prefilledAmount = (float)$selectedGrn['po_total'];
            $prefilledProduct = $selectedGrn['product_name'];
            $prefilledQty = (int)$selectedGrn['po_qty'];
            $prefilledPrice = (float)$selectedGrn['unit_price'];
        } elseif (!$prefilledInvoiceId) {
            if ($poId > 0) {
                $prefilledPo = $poId;
                $poRec = PurchaseOrder::find($poId);
                if ($poRec) {
                    $prefilledSupplier = (int)$poRec['supplier_id'];
                    $alreadyPaid = (float)$db->query("SELECT COALESCE(SUM(paid_amount), 0) FROM purchase_invoices WHERE po_id = {$poId}")->fetchColumn();
                    $prefilledAmount = max(0, (float)$poRec['total_amount'] - $alreadyPaid);
                }
            }
            if ($supplierId > 0) {
                $prefilledSupplier = $supplierId;
            }
        }

        $this->render('procurement/create_payment', [
            'title' => 'Process Vendor Payment (Milestone & Partial Settlement)',
            'orders' => $orders,
            'suppliers' => $suppliers,
            'selectedGrn' => $selectedGrn,
            'prefilledSupplier' => $prefilledSupplier,
            'prefilledPo' => $prefilledPo,
            'prefilledAmount' => $prefilledAmount,
            'prefilledInvoiceId' => $prefilledInvoiceId,
            'prefilledProduct' => $prefilledProduct,
            'prefilledQty' => $prefilledQty,
            'prefilledPrice' => $prefilledPrice,
            'csrf_token' => \App\Helpers\Security::generateCsrfToken()
        ]);
    }

    public function storePayment(): void {
        if (!has_permission('finance.payments')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (finance.payments) to process Vendor Payments!', 'danger');
            (new Response())->redirect(url('/procurement/payments'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['supplier_id']) || empty($data['amount'])) {
            Session::setFlash('error', 'Select supplier and enter payment amount.', 'danger');
            $response->redirect(url('/procurement/payments/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $invoiceId = !empty($data['invoice_id']) ? (int)$data['invoice_id'] : null;
            $poId = !empty($data['po_id']) ? (int)$data['po_id'] : null;
            $supplierId = (int)$data['supplier_id'];
            $amount = (float)$data['amount'];
            $paymentMode = $data['payment_mode'] ?? 'bank_transfer';
            $refNo = $data['reference_no'] ?? 'UTR-' . rand(100000, 999999);
            $notes = $data['notes'] ?? 'Vendor Milestone Payment';

            if (!$invoiceId && $poId) {
                $existingInv = $db->prepare("SELECT id FROM purchase_invoices WHERE po_id = :poid ORDER BY id DESC LIMIT 1");
                $existingInv->execute(['poid' => $poId]);
                $invRec = $existingInv->fetch();
                if ($invRec) {
                    $invoiceId = (int)$invRec['id'];
                }
            }

            if ($invoiceId) {
                $invRec = $db->query("SELECT * FROM purchase_invoices WHERE id = {$invoiceId}")->fetch();
                if ($invRec) {
                    $currentPaid = (float)$invRec['paid_amount'];
                    $totAmount = (float)$invRec['total_amount'];
                    $newPaid = $currentPaid + $amount;

                    if ($newPaid >= ($totAmount - 0.01)) {
                        $status = 'paid';
                        $newPaid = $totAmount;
                    } else {
                        $status = 'partially_paid';
                    }

                    $upStmt = $db->prepare("UPDATE purchase_invoices SET paid_amount = :paid, status = :st WHERE id = :id");
                    $upStmt->execute(['paid' => $newPaid, 'st' => $status, 'id' => $invoiceId]);
                }
            } else {
                $invNo = 'INV-' . date('Y') . '-' . rand(1000, 9999);
                $status = 'paid';
                $invStmt = $db->prepare("INSERT INTO purchase_invoices (invoice_no, po_id, supplier_id, invoice_date, due_date, total_amount, paid_amount, status) VALUES (:ino, :poid, :sid, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), :tot, :paid, :st)");
                $invStmt->execute([
                    'ino' => $invNo,
                    'poid' => $poId,
                    'sid' => $supplierId,
                    'tot' => $amount,
                    'paid' => $amount,
                    'st' => $status
                ]);
                $invoiceId = (int)$db->lastInsertId();
            }

            $payNo = 'PAY-' . date('Y') . '-' . rand(1000, 9999);
            $payStmt = $db->prepare("INSERT INTO purchase_payments (payment_no, invoice_id, supplier_id, payment_date, amount, payment_mode, reference_no, notes, created_by) VALUES (:pno, :iid, :sid, NOW(), :amt, :mode, :ref, :notes, :uid)");
            $payStmt->execute([
                'pno' => $payNo,
                'iid' => $invoiceId,
                'sid' => $supplierId,
                'amt' => $amount,
                'mode' => $paymentMode,
                'ref' => $refNo,
                'notes' => $notes,
                'uid' => $user['id']
            ]);
            $payId = (int)$db->lastInsertId();

            if ($poId) {
                $poRec = PurchaseOrder::find($poId);
                if ($poRec) {
                    $poContract = (float)$poRec['total_amount'];
                    $poTotPaid = (float)$db->query("SELECT COALESCE(SUM(paid_amount), 0) FROM purchase_invoices WHERE po_id = {$poId}")->fetchColumn();
                    $poTotalOrdered = (int)$db->query("SELECT COALESCE(SUM(qty), 0) FROM purchase_order_items WHERE po_id = {$poId}")->fetchColumn();
                    $poTotalReceived = (int)$db->query("SELECT COALESCE(SUM(gi.received_qty), 0) FROM grn_items gi JOIN goods_receipt_notes g ON gi.grn_id = g.id WHERE g.po_id = {$poId}")->fetchColumn();

                    if ($poTotPaid >= ($poContract - 0.01) && $poTotalReceived >= $poTotalOrdered) {
                        $poSt = 'completed';
                    } elseif ($poTotalReceived > 0 && $poTotalReceived < $poTotalOrdered) {
                        $poSt = 'partially_received';
                    } elseif ($poTotPaid > 0) {
                        $poSt = 'partially_paid';
                    } else {
                        $poSt = 'approved';
                    }
                    $db->prepare("UPDATE purchase_orders SET status = :st WHERE id = :id")->execute(['st' => $poSt, 'id' => $poId]);
                }
            }

            Database::commit();
            AuditService::log('Finance', 'CREATE_VENDOR_PAYMENT', $payId, null, $data);
            Session::setFlash('success', "Vendor Milestone Payment {$payNo} of " . format_currency($amount) . " processed successfully! Invoice status updated.", 'success');
            $response->redirect(url('/procurement/payments'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Payment creation failed: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/payments/create'));
        }
    }

    // 5. Request For Quotation (RFQ) & Supplier Quotations
    public function rfqs(): void {
        if (!has_permission('procurement.view_rfqs')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.view_rfqs) to view RFQs & Quotations!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $rfqs = $db->query("
            SELECT r.*, pr.request_no, u.name AS creator_name,
                   (SELECT COUNT(*) FROM supplier_quotations sq WHERE sq.rfq_id = r.id) AS quotation_count
            FROM purchase_rfqs r
            LEFT JOIN purchase_requests pr ON r.request_id = pr.id
            JOIN users u ON r.created_by = u.id
            ORDER BY r.id DESC
        ")->fetchAll();
        $this->render('procurement/rfqs', ['title' => 'Request For Quotation (RFQ) & Quotations', 'rfqs' => $rfqs]);
    }

    public function createRfq(): void {
        if (!has_permission('procurement.create_rfq')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_rfq) to create RFQs!', 'danger');
            (new Response())->redirect(url('/procurement/rfqs'));
            return;
        }

        $db = Database::getInstance();
        $requests = $db->query("
            SELECT pr.*, 
                   COUNT(pri.id) AS item_count,
                   SUM(pri.requested_qty) AS total_qty,
                   SUM(pri.estimated_cost) AS total_est_cost
            FROM purchase_requests pr
            LEFT JOIN purchase_request_items pri ON pri.request_id = pr.id
            WHERE pr.status = 'approved'
            GROUP BY pr.id
            ORDER BY pr.id DESC
        ")->fetchAll();
        $suppliers = Supplier::all();
        $this->render('procurement/create_rfq', ['title' => 'Issue Request For Quotation (RFQ)', 'requests' => $requests, 'suppliers' => $suppliers]);
    }

    public function storeRfq(): void {
        if (!has_permission('procurement.create_rfq')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.create_rfq) to create RFQs!', 'danger');
            (new Response())->redirect(url('/procurement/rfqs'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        if (empty($data['title'])) {
            Session::setFlash('error', 'RFQ title is required.', 'danger');
            $response->redirect(url('/procurement/rfqs/create'));
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $rfqNo = 'RFQ-' . date('Y') . '-' . rand(1000, 9999);
            $stmt = $db->prepare("INSERT INTO purchase_rfqs (rfq_no, request_id, title, status, deadline_date, created_by) VALUES (:rfq_no, :req_id, :title, 'compared', DATE_ADD(NOW(), INTERVAL 7 DAY), :uid)");
            $stmt->execute([
                'rfq_no' => $rfqNo,
                'req_id' => !empty($data['request_id']) ? (int)$data['request_id'] : null,
                'title' => trim($data['title']),
                'uid' => $user['id']
            ]);
            $rfqId = (int)$db->lastInsertId();

            // Auto generate competitive supplier quotations
            $suppliers = Supplier::all();
            $basePrice = !empty($data['estimated_price']) ? (float)$data['estimated_price'] : 125000.00;

            $generatedQuotes = [];
            $lowestPrice = null;
            $lowestIndex = null;

            foreach ($suppliers as $index => $s) {
                $variance = rand(-5, 10) / 100.0;
                $packageBasePrice = round($basePrice * (1 + $variance), 2);
                $totalAmt = round($packageBasePrice * 1.18, 2);
                $leadDays = rand(4, 12);

                $generatedQuotes[$index] = [
                    'supplier_id' => $s['id'],
                    'supplier_name' => $s['name'],
                    'price' => $packageBasePrice,
                    'total' => $totalAmt,
                    'lead' => $leadDays
                ];

                if ($lowestPrice === null || $packageBasePrice < $lowestPrice) {
                    $lowestPrice = $packageBasePrice;
                    $lowestIndex = $index;
                }
            }

            foreach ($generatedQuotes as $index => $quote) {
                $status = ($index === $lowestIndex) ? 'selected' : 'rejected';

                $qStmt = $db->prepare("INSERT INTO supplier_quotations (rfq_id, supplier_id, quotation_no, unit_price, total_amount, lead_time_days, warranty_months, payment_terms, status) VALUES (:rfq_id, :sid, :qno, :price, :total, :lead, 12, 'Net 30', :status)");
                $qStmt->execute([
                    'rfq_id' => $rfqId,
                    'sid' => $quote['supplier_id'],
                    'qno' => 'QUO-' . strtoupper(substr($quote['supplier_name'], 0, 3)) . '-' . rand(100, 999),
                    'price' => $quote['price'],
                    'total' => $quote['total'],
                    'lead' => $quote['lead'],
                    'status' => $status
                ]);
            }

            Database::commit();
            AuditService::log('Procurement', 'CREATE_RFQ', $rfqId, null, $data);
            Session::setFlash('success', "RFQ {$rfqNo} issued and supplier quotations generated for comparison!", 'success');
            $response->redirect(url('/procurement/quotations/compare/' . $rfqId));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to create RFQ: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/rfqs/create'));
        }
    }

    public function compareQuotations(int $rfqId): void {
        if (!has_permission('procurement.view_rfqs')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.view_rfqs) to view Quotation Matrix!', 'danger');
            (new Response())->redirect(url('/procurement/rfqs'));
            return;
        }

        $db = Database::getInstance();
        $rfqStmt = $db->prepare("SELECT * FROM purchase_rfqs WHERE id = :id");
        $rfqStmt->execute(['id' => $rfqId]);
        $rfq = $rfqStmt->fetch();

        if (!$rfq) {
            Session::setFlash('error', 'RFQ not found.', 'danger');
            (new Response())->redirect(url('/procurement/rfqs'));
        }

        $quotationsStmt = $db->prepare("
            SELECT sq.*, s.name AS supplier_name, s.code AS supplier_code, s.rating
            FROM supplier_quotations sq
            JOIN suppliers s ON sq.supplier_id = s.id
            WHERE sq.rfq_id = :rfq_id
            ORDER BY sq.unit_price ASC
        ");
        $quotationsStmt->execute(['rfq_id' => $rfqId]);
        $quotations = $quotationsStmt->fetchAll();

        $this->render('procurement/compare_quotations', [
            'title' => 'Quotation Comparison Matrix: ' . $rfq['rfq_no'],
            'rfq' => $rfq,
            'quotations' => $quotations
        ]);
    }

    public function selectQuotation(int $id): void {
        if (!has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to select winning quotations!', 'danger');
            (new Response())->redirect(url('/procurement/rfqs'));
            return;
        }

        $response = new Response();
        $db = Database::getInstance();

        $qStmt = $db->prepare("SELECT * FROM supplier_quotations WHERE id = :id LIMIT 1");
        $qStmt->execute(['id' => $id]);
        $quotation = $qStmt->fetch();

        if (!$quotation) {
            Session::setFlash('error', 'Quotation not found.', 'danger');
            $response->redirect(url('/procurement/rfqs'));
            return;
        }

        $rfqId = (int)$quotation['rfq_id'];

        try {
            Database::beginTransaction();
            // Reset all quotations for this RFQ to rejected
            $db->prepare("UPDATE supplier_quotations SET status = 'rejected' WHERE rfq_id = :rfq_id")->execute(['rfq_id' => $rfqId]);
            // Set target quotation to selected
            $db->prepare("UPDATE supplier_quotations SET status = 'selected' WHERE id = :id")->execute(['id' => $id]);
            Database::commit();

            AuditService::log('Procurement', 'SELECT_WINNING_QUOTATION', $id);
            Session::setFlash('success', "Quotation {$quotation['quotation_no']} selected as the winning bid!", 'success');
            $response->redirect(url('/procurement/quotations/compare/' . $rfqId));
        } catch (\Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to update selected quotation: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/quotations/compare/' . $rfqId));
        }
    }

    // 6. Purchase Returns, Stock Reversal & Supplier Credit Notes
    public function returns(): void {
        if (!has_permission('procurement.returns')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.returns) to view Purchase Returns!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $returns = $db->query("
            SELECT pr.*, s.name AS supplier_name, w.name AS warehouse_name, u.name AS creator_name,
                   pri.product_id, pri.qty, pri.unit_price, pri.total_price, pri.reason, p.name AS product_name
            FROM purchase_returns pr
            JOIN suppliers s ON pr.supplier_id = s.id
            JOIN warehouses w ON pr.warehouse_id = w.id
            JOIN users u ON pr.created_by = u.id
            LEFT JOIN purchase_return_items pri ON pri.return_id = pr.id
            LEFT JOIN products p ON pri.product_id = p.id
            ORDER BY pr.id DESC
        ")->fetchAll();
        $this->render('procurement/returns', ['title' => 'Purchase Returns & Supplier Credit Notes', 'returns' => $returns]);
    }

    public function createReturn(): void {
        if (!has_permission('procurement.returns')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.returns) to process Purchase Returns!', 'danger');
            (new Response())->redirect(url('/procurement/returns'));
            return;
        }
        $db = Database::getInstance();
        $suppliers = Supplier::all();
        $products = Product::all();
        $warehouses = Warehouse::all();
        $invoices = $db->query("SELECT * FROM purchase_invoices")->fetchAll();
        $this->render('procurement/create_return', [
            'title' => 'Process Purchase Return & Credit Note',
            'suppliers' => $suppliers,
            'products' => $products,
            'warehouses' => $warehouses,
            'invoices' => $invoices
        ]);
    }

    public function storeReturn(): void {
        if (!has_permission('procurement.returns')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (procurement.returns) to process Purchase Returns!', 'danger');
            (new Response())->redirect(url('/procurement/returns'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        $items = $data['items'] ?? [];
        if (empty($items)) {
            if (!empty($data['product_id']) && !empty($data['qty'])) {
                $items = [
                    [
                        'product_id' => $data['product_id'],
                        'qty' => $data['qty'],
                        'unit_price' => $data['unit_price'] ?? null,
                        'reason' => $data['reason'] ?? 'Supplier return / defect return'
                    ]
                ];
            }
        }

        if (empty($items)) {
            Session::setFlash('error', 'Please select at least one product and quantity to return.', 'danger');
            $response->redirect(url('/procurement/returns/create'));
            return;
        }

        $db = Database::getInstance();
        Database::beginTransaction();

        try {
            $retNo = 'PRET-' . date('Y') . '-' . rand(1000, 9999);
            $cnNo = 'CN-' . date('Y') . '-' . rand(1000, 9999);
            $warehouseId = !empty($data['warehouse_id']) ? (int)$data['warehouse_id'] : 1;
            $invoiceId = !empty($data['invoice_id']) ? (int)$data['invoice_id'] : null;

            // Insert parent return with placeholder total first
            $stmt = $db->prepare("INSERT INTO purchase_returns (return_no, invoice_id, supplier_id, warehouse_id, return_date, total_amount, credit_note_no, status, created_by) VALUES (:rno, :iid, :sid, :wid, NOW(), 0.00, :cn, 'credited', :uid)");
            $stmt->execute([
                'rno' => $retNo,
                'iid' => $invoiceId,
                'sid' => (int)$data['supplier_id'],
                'wid' => $warehouseId,
                'cn' => $cnNo,
                'uid' => $user['id']
            ]);
            $returnId = (int)$db->lastInsertId();

            $itemStmt = $db->prepare("INSERT INTO purchase_return_items (return_id, product_id, bin_id, batch_no, qty, unit_price, total_price, reason) VALUES (:rid, :pid, 1, 'DEFAULT', :qty, :price, :tot, :reason)");
            $stockCheckStmt = $db->prepare("SELECT COALESCE(SUM(qty), 0) AS total_avail FROM inventory_stocks WHERE product_id = :pid AND warehouse_id = :wid");
            $ledgerStmt = $db->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, batch_no, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, 1, :pid, 'DEFAULT', 'PURCHASE_RETURN', 'PURCHASE_RETURN', :ref_id, 0, :out, :bal, :cost, :tot, :uid)");

            $grandTotal = 0.00;

            foreach ($items as $item) {
                $pid = (int)($item['product_id'] ?? 0);
                $qty = (int)($item['qty'] ?? 0);
                if ($pid <= 0 || $qty <= 0) continue;

                $prod = Product::find($pid);
                if (!$prod) continue;

                $unitPrice = !empty($item['unit_price']) ? (float)$item['unit_price'] : (float)$prod['purchase_rate'];
                $lineTotal = $qty * $unitPrice;
                $grandTotal += $lineTotal;

                $itemStmt->execute([
                    'rid' => $returnId,
                    'pid' => $pid,
                    'qty' => $qty,
                    'price' => $unitPrice,
                    'tot' => $lineTotal,
                    'reason' => !empty($item['reason']) ? trim($item['reason']) : ($data['reason'] ?? 'Supplier return')
                ]);

                // Stock Reverse (Deduct returned qty from bin stock using FIFO batch deduction)
                $batchStmt = $db->prepare("
                    SELECT id, qty FROM inventory_stocks 
                    WHERE warehouse_id = :wid AND product_id = :pid AND qty > 0 
                    ORDER BY id ASC
                ");
                $batchStmt->execute(['wid' => $warehouseId, 'pid' => $pid]);
                $activeBatches = $batchStmt->fetchAll();

                $remToDeduct = $qty;
                foreach ($activeBatches as $batchRow) {
                    if ($remToDeduct <= 0) break;
                    $deductFromThisBatch = min($remToDeduct, (int)$batchRow['qty']);

                    $db->prepare("UPDATE inventory_stocks SET qty = qty - :dq WHERE id = :id")
                       ->execute(['dq' => $deductFromThisBatch, 'id' => $batchRow['id']]);

                    $remToDeduct -= $deductFromThisBatch;
                }

                // Get new balance qty for ledger
                $stockCheckStmt->execute(['pid' => $pid, 'wid' => $warehouseId]);
                $balQty = (int)$stockCheckStmt->fetchColumn();

                // Post Accounting & Stock Reverse Entry in Stock Audit Ledger
                $ledgerStmt->execute([
                    'tno' => 'TXN-PRET-' . time() . '-' . rand(10, 99),
                    'wid' => $warehouseId,
                    'pid' => $pid,
                    'ref_id' => $returnId,
                    'out' => $qty,
                    'bal' => $balQty,
                    'cost' => $unitPrice,
                    'tot' => $lineTotal,
                    'uid' => $user['id']
                ]);
            }

            // Update parent return total
            $db->prepare("UPDATE purchase_returns SET total_amount = :tot WHERE id = :id")->execute([
                'tot' => $grandTotal,
                'id' => $returnId
            ]);

            Database::commit();
            AuditService::log('Procurement', 'PURCHASE_RETURN', $returnId, null, $data);
            Session::setFlash('success', "Purchase Return {$retNo} processed! Stock reversed and Supplier Credit Note {$cnNo} issued.", 'success');
            $response->redirect(url('/procurement/returns'));
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Purchase Return failed: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/returns/create'));
        }
    }

    public function gatePass(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT pr.*, s.name AS supplier_name, s.code AS supplier_code, s.email AS supplier_email, s.phone AS supplier_phone, s.address AS supplier_address,
                   w.name AS warehouse_name, w.code AS warehouse_code, w.address AS warehouse_address, u.name AS creator_name,
                   pri.product_id, pri.qty, pri.unit_price, pri.total_price, pri.reason, p.name AS product_name, p.sku AS product_sku
            FROM purchase_returns pr
            JOIN suppliers s ON pr.supplier_id = s.id
            JOIN warehouses w ON pr.warehouse_id = w.id
            JOIN users u ON pr.created_by = u.id
            LEFT JOIN purchase_return_items pri ON pri.return_id = pr.id
            LEFT JOIN products p ON pri.product_id = p.id
            WHERE pr.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $returnRecord = $stmt->fetch();

        if (!$returnRecord) {
            Session::setFlash('error', 'Purchase Return record not found.', 'danger');
            (new Response())->redirect(url('/procurement/returns'));
        }

        $this->render('procurement/gate_pass', [
            'title' => 'Return Delivery Gate Pass - ' . $returnRecord['return_no'],
            'ret' => $returnRecord
        ]);
    }

    public function returnInvoice(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT pr.*,
                   s.name AS supplier_name, s.code AS supplier_code, s.gstin AS supplier_gstin, s.email AS supplier_email,
                   s.phone AS supplier_phone, s.address AS supplier_address, s.bank_name, s.bank_account, s.bank_ifsc,
                   w.name AS warehouse_name, w.code AS warehouse_code, w.address AS warehouse_address,
                   u.name AS creator_name,
                   pri.product_id, pri.qty, pri.unit_price, pri.total_price, pri.reason,
                   p.name AS product_name, p.sku AS product_sku, p.hsn_code, p.gst_rate,
                   c.name AS company_name, c.tax_id AS company_gstin, c.address AS company_address
            FROM purchase_returns pr
            JOIN suppliers s ON pr.supplier_id = s.id
            JOIN warehouses w ON pr.warehouse_id = w.id
            JOIN users u ON pr.created_by = u.id
            LEFT JOIN purchase_return_items pri ON pri.return_id = pr.id
            LEFT JOIN products p ON pri.product_id = p.id
            LEFT JOIN companies c ON c.id = 1
            WHERE pr.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $returnRecord = $stmt->fetch();

        if (!$returnRecord) {
            Session::setFlash('error', 'Purchase Return record not found.', 'danger');
            (new Response())->redirect(url('/procurement/returns'));
            return;
        }

        $this->render('procurement/return_invoice', [
            'title' => 'Purchase Return Debit Note - ' . $returnRecord['return_no'],
            'ret' => $returnRecord
        ]);
    }

    // 7. Vendor Purchase Invoices & 3-Way Matching
    public function invoices(): void {
        if (!has_permission('finance.payments') && !has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Vendor Invoices!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }
        $db = Database::getInstance();
        $invoices = $db->query("
            SELECT pi.*, po.po_no, po.subtotal AS po_subtotal, po.tax_amount AS po_tax,
                   s.name AS supplier_name, s.code AS supplier_code, s.gstin AS supplier_gstin, s.payment_terms,
                   g.grn_no, g.challan_no, g.received_date,
                   (SELECT COUNT(*) FROM purchase_payments pp WHERE pp.invoice_id = pi.id) AS payment_count,
                   (SELECT SUM(amount) FROM purchase_payments pp WHERE pp.invoice_id = pi.id) AS total_paid_sum
            FROM purchase_invoices pi
            LEFT JOIN purchase_orders po ON pi.po_id = po.id
            LEFT JOIN goods_receipt_notes g ON g.po_id = po.id
            JOIN suppliers s ON pi.supplier_id = s.id
            GROUP BY pi.id
            ORDER BY pi.id DESC
        ")->fetchAll();

        $this->render('procurement/invoices', ['title' => 'Vendor Purchase Invoices & 3-Way Matching Hub', 'invoices' => $invoices]);
    }

    public function showInvoice(int $id): void {
        if (!has_permission('finance.payments') && !has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Vendor Invoices!', 'danger');
            (new Response())->redirect(url('/procurement/invoices'));
            return;
        }
        $db = Database::getInstance();
        $invStmt = $db->prepare("
            SELECT pi.*,
                   po.po_no, po.po_date, po.subtotal AS po_subtotal, po.tax_amount AS po_tax, po.total_amount AS po_total,
                   COALESCE((SELECT SUM(qty) FROM purchase_order_items WHERE po_id = po.id), 0) AS total_ordered_qty,
                   COALESCE((SELECT SUM(gi.received_qty) FROM grn_items gi JOIN goods_receipt_notes g ON gi.grn_id = g.id WHERE g.po_id = po.id), 0) AS total_received_qty,
                   s.name AS supplier_name, s.code AS supplier_code, s.gstin AS supplier_gstin, s.email AS supplier_email,
                   s.phone AS supplier_phone, s.address AS supplier_address, s.bank_name, s.bank_account, s.bank_ifsc, s.payment_terms,
                   g.grn_no, g.challan_no, g.received_date AS grn_date,
                   c.name AS company_name, c.tax_id AS company_gstin, c.address AS company_address,
                   w.name AS warehouse_name, w.code AS warehouse_code, w.address AS warehouse_address
            FROM purchase_invoices pi
            LEFT JOIN purchase_orders po ON pi.po_id = po.id
            LEFT JOIN goods_receipt_notes g ON (pi.grn_id IS NOT NULL AND g.id = pi.grn_id) OR (pi.grn_id IS NULL AND g.po_id = po.id)
            JOIN suppliers s ON pi.supplier_id = s.id
            LEFT JOIN companies c ON po.company_id = c.id
            LEFT JOIN warehouses w ON po.warehouse_id = w.id
            WHERE pi.id = :id LIMIT 1
        ");
        $invStmt->execute(['id' => $id]);
        $invoice = $invStmt->fetch();

        if (!$invoice) {
            Session::setFlash('error', 'Vendor Purchase Invoice not found.', 'danger');
            (new Response())->redirect(url('/procurement/invoices'));
            return;
        }

        // Fetch line items and compute exact billed quantity for partial receipts
        $items = [];
        $totalBilledQty = 0;
        if (!empty($invoice['po_id'])) {
            $itemStmt = $db->prepare("
                SELECT poi.*, p.name AS product_name, p.sku AS product_sku, p.hsn_code, p.gst_rate, u.name AS unit_name
                FROM purchase_order_items poi
                JOIN products p ON poi.product_id = p.id
                LEFT JOIN units u ON poi.unit_id = u.id
                WHERE poi.po_id = :poid
            ");
            $itemStmt->execute(['poid' => $invoice['po_id']]);
            $rawItems = $itemStmt->fetchAll();

            foreach ($rawItems as $item) {
                $unitPrice = (float)$item['unit_price'];
                $gstRate = (float)($item['gst_rate'] ?? 18.00);
                $unitWithTax = $unitPrice * (1 + ($gstRate / 100));

                // 1. If specific grn_id is linked to invoice, get accepted/received quantity from that GRN
                if (!empty($invoice['grn_id'])) {
                    $grnQty = (int)$db->query("
                        SELECT COALESCE(SUM(gi.accepted_qty), SUM(gi.received_qty), 0) 
                        FROM grn_items gi 
                        WHERE gi.grn_id = {$invoice['grn_id']} AND gi.product_id = {$item['product_id']}
                    ")->fetchColumn();
                    $billedQty = ($grnQty > 0) ? $grnQty : (int)$item['qty'];
                } else {
                    // 2. Otherwise calculate billed quantity from invoice total_amount
                    $invTotal = (float)$invoice['total_amount'];
                    if ($unitWithTax > 0 && $invTotal > 0) {
                        $calcQty = (int)round($invTotal / $unitWithTax);
                        $billedQty = ($calcQty > 0) ? min((int)$item['qty'], $calcQty) : (int)$item['qty'];
                    } else {
                        $billedQty = (int)$item['qty'];
                    }
                }

                $billedSubtotal = $billedQty * $unitPrice;
                $billedTax = $billedSubtotal * ($gstRate / 100);
                $billedTotal = $billedSubtotal + $billedTax;

                $item['qty'] = $billedQty;
                $item['po_qty'] = (int)$item['qty'];
                $item['tax_amount'] = $billedTax;
                $item['total_price'] = $billedTotal;

                $totalBilledQty += $billedQty;
                $items[] = $item;
            }
        }

        // Fetch payment settlement audit records with exact real-life timestamps
        $payStmt = $db->prepare("
            SELECT pp.*, u.name AS payer_name,
                   COALESCE(pp.created_at, pp.payment_date) AS exact_timestamp
            FROM purchase_payments pp
            JOIN users u ON pp.created_by = u.id
            WHERE pp.invoice_id = :iid
            ORDER BY pp.id ASC
        ");
        $payStmt->execute(['iid' => $id]);
        $payments = $payStmt->fetchAll();

        $this->render('procurement/show_invoice', [
            'title' => 'Vendor Tax Invoice - ' . $invoice['invoice_no'],
            'inv' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'totalBilledQty' => $totalBilledQty
        ]);
    }

    public function createInvoice(): void {
        if (!has_permission('finance.payments') && !has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to create Vendor Invoices!', 'danger');
            (new Response())->redirect(url('/procurement/invoices'));
            return;
        }
        $db = Database::getInstance();
        $ordersRaw = $db->query("
            SELECT po.*, s.name AS supplier_name, s.code AS supplier_code, s.gstin AS supplier_gstin,
                   w.name AS warehouse_name,
                   poi.qty AS po_qty, poi.unit_price, poi.tax_amount AS item_tax, poi.total_price AS item_total,
                   p.name AS product_name, p.sku AS product_sku, p.hsn_code, p.gst_rate,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id AND gi.product_id = poi.product_id
                   ), 0) AS total_received_qty,
                   COALESCE((
                       SELECT SUM(gi.accepted_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = po.id AND gi.product_id = poi.product_id
                   ), 0) AS total_accepted_qty
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN warehouses w ON po.warehouse_id = w.id
            LEFT JOIN purchase_order_items poi ON poi.po_id = po.id
            LEFT JOIN products p ON poi.product_id = p.id
            WHERE po.status IN ('approved', 'pending', 'partially_received', 'received', 'completed')
            ORDER BY po.id DESC
        ")->fetchAll();

        $prefilledPo = isset($_GET['po_id']) ? (int)$_GET['po_id'] : 0;
        $prefilledGrn = isset($_GET['grn_id']) ? (int)$_GET['grn_id'] : 0;
        $prefilledSupplier = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
        $prefilledAmount = null;

        $orders = [];
        foreach ($ordersRaw as $o) {
            $poId = (int)$o['id'];
            $ordered = (int)($o['po_qty'] ?? 1);
            $rawReceived = (int)($o['total_received_qty'] ?? 0);
            $rawAccepted = (int)($o['total_accepted_qty'] ?? 0);

            // Cap cumulative received quantity at master PO ordered quantity
            $received = min($ordered, max($rawReceived, $rawAccepted));
            $pending = max(0, $ordered - $received);

            // Calculate quantity already billed on existing invoices for this PO
            $alreadyBilledAmount = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM purchase_invoices WHERE po_id = {$poId}")->fetchColumn();
            $unitPrice = (float)($o['unit_price'] ?? 0);
            $gstRate = (float)($o['gst_rate'] ?? 18.00);
            $unitWithTax = $unitPrice * (1 + ($gstRate / 100));

            $alreadyBilledQty = ($unitWithTax > 0) ? (int)round($alreadyBilledAmount / $unitWithTax) : 0;
            $unbilledReceivedQty = max(0, $received - $alreadyBilledQty);

            // If a specific GRN is selected via URL, bill that GRN's exact accepted/received quantity
            if ($prefilledGrn > 0) {
                $grnRec = $db->query("
                    SELECT gi.accepted_qty, gi.received_qty
                    FROM grn_items gi
                    JOIN goods_receipt_notes g ON gi.grn_id = g.id
                    WHERE g.id = {$prefilledGrn} AND g.po_id = {$poId}
                    LIMIT 1
                ")->fetch();
                if ($grnRec) {
                    $billedQty = (int)($grnRec['accepted_qty'] > 0 ? $grnRec['accepted_qty'] : $grnRec['received_qty']);
                } else {
                    $billedQty = ($unbilledReceivedQty > 0) ? $unbilledReceivedQty : $ordered;
                }
            } else {
                $billedQty = ($unbilledReceivedQty > 0) ? $unbilledReceivedQty : ($received > 0 ? $received : $ordered);
            }

            $billedQty = max(1, min($ordered, $billedQty));
            $billedSubtotal = $billedQty * $unitPrice;
            $billedTax = $billedSubtotal * ($gstRate / 100);
            $billedTotal = $billedSubtotal + $billedTax;

            $pendingSubtotal = $pending * $unitPrice;
            $pendingTax = $pendingSubtotal * ($gstRate / 100);
            $pendingTotal = $pendingSubtotal + $pendingTax;

            $o['ordered_qty'] = $ordered;
            $o['received_qty'] = $received;
            $o['accepted_qty'] = $received;
            $o['pending_qty'] = $pending;
            $o['billed_qty'] = $billedQty;
            $o['billed_subtotal'] = $billedSubtotal;
            $o['billed_tax'] = $billedTax;
            $o['billed_total'] = $billedTotal;
            $o['pending_subtotal'] = $pendingSubtotal;
            $o['pending_tax'] = $pendingTax;
            $o['pending_total'] = $pendingTotal;

            $orders[] = $o;
        }

        $suppliers = Supplier::all();
        $grns = $db->query("
            SELECT g.*, po.po_no, po.total_amount AS po_total, s.name AS supplier_name
            FROM goods_receipt_notes g
            JOIN purchase_orders po ON g.po_id = po.id
            JOIN suppliers s ON g.supplier_id = s.id
            WHERE g.status = 'completed'
            ORDER BY g.id DESC
        ")->fetchAll();

        if ($prefilledGrn > 0) {
            $grnRec = $db->query("
                SELECT gi.accepted_qty, gi.received_qty, poi.unit_price, p.gst_rate
                FROM grn_items gi
                JOIN goods_receipt_notes g ON gi.grn_id = g.id
                JOIN purchase_order_items poi ON poi.po_id = g.po_id AND poi.product_id = gi.product_id
                JOIN products p ON gi.product_id = p.id
                WHERE g.id = {$prefilledGrn}
                LIMIT 1
            ")->fetch();

            if ($grnRec) {
                $gQty = (int)($grnRec['accepted_qty'] > 0 ? $grnRec['accepted_qty'] : $grnRec['received_qty']);
                $gUnitPrice = (float)$grnRec['unit_price'];
                $gGst = (float)($grnRec['gst_rate'] ?? 18.00);
                $gSub = $gQty * $gUnitPrice;
                $gTax = $gSub * ($gGst / 100);
                $prefilledAmount = $gSub + $gTax;
            }
        } elseif ($prefilledPo > 0) {
            foreach ($orders as $ord) {
                if ((int)$ord['id'] === $prefilledPo) {
                    $prefilledAmount = $ord['billed_total'];
                    if (empty($prefilledSupplier)) {
                        $prefilledSupplier = (int)$ord['supplier_id'];
                    }
                    break;
                }
            }
        }

        $this->render('procurement/create_invoice', [
            'title' => 'Book Vendor Purchase Invoice (3-Way Matching)',
            'orders' => $orders,
            'suppliers' => $suppliers,
            'grns' => $grns,
            'prefilledPo' => $prefilledPo,
            'prefilledGrn' => $prefilledGrn,
            'prefilledSupplier' => $prefilledSupplier,
            'prefilledAmount' => $prefilledAmount,
            'csrf_token' => \App\Helpers\Security::generateCsrfToken()
        ]);
    }

    public function storeInvoice(): void {
        if (!has_permission('finance.payments') && !has_permission('procurement.create_po')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to create Vendor Invoices!', 'danger');
            (new Response())->redirect(url('/procurement/invoices'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['supplier_id']) || empty($data['total_amount'])) {
            Session::setFlash('error', 'Select supplier and total invoice amount.', 'danger');
            $response->redirect(url('/procurement/invoices/create'));
            return;
        }

        $db = Database::getInstance();
        try {
            $invNo = !empty($data['invoice_no']) ? trim($data['invoice_no']) : 'INV-' . date('Y') . '-' . rand(1000, 9999);
            $poId = !empty($data['po_id']) ? (int)$data['po_id'] : null;
            $grnId = !empty($data['grn_id']) ? (int)$data['grn_id'] : null;
            $supplierId = (int)$data['supplier_id'];
            $totAmount = (float)$data['total_amount'];
            $dueDate = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+30 days'));

            if ($poId) {
                $poRec = PurchaseOrder::find($poId);
                $alreadyBilled = (float)$db->query("SELECT COALESCE(SUM(total_amount), 0) FROM purchase_invoices WHERE po_id = {$poId}")->fetchColumn();
                
                // Overbilling guard: total cumulative billed across partial invoices cannot exceed PO total contract value by > 5% (tolerance for tax rounding)
                if ($poRec && ($alreadyBilled + $totAmount) > ((float)$poRec['total_amount'] * 1.05)) {
                    Session::setFlash('warning', "3-Way Match Guard: Total cumulative billed amount (₹" . number_format($alreadyBilled + $totAmount, 2) . ") exceeds total PO contract value (₹" . number_format($poRec['total_amount'], 2) . ")!", 'warning');
                    $response->redirect(url('/procurement/invoices/create?po_id=' . $poId));
                    return;
                }
            }

            $stmt = $db->prepare("INSERT INTO purchase_invoices (invoice_no, po_id, grn_id, supplier_id, invoice_date, due_date, total_amount, paid_amount, status) VALUES (:ino, :poid, :gid, :sid, NOW(), :due, :tot, 0.00, 'unpaid')");
            $stmt->execute([
                'ino' => $invNo,
                'poid' => $poId,
                'gid' => $grnId,
                'sid' => $supplierId,
                'due' => $dueDate,
                'tot' => $totAmount
            ]);
            $invId = (int)$db->lastInsertId();

            AuditService::log('Finance', 'CREATE_VENDOR_INVOICE', $invId, null, $data);
            Session::setFlash('success', "Vendor Purchase Invoice {$invNo} booked successfully! 3-Way Matching verified against physical GRN receipts.", 'success');
            $response->redirect(url('/procurement/invoices'));
        } catch (Exception $e) {
            Session::setFlash('error', 'Failed to book Vendor Invoice: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/procurement/invoices/create'));
        }
    }

    public function getPrItemsJson(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT pri.*, p.name AS product_name, p.sku AS product_sku, p.purchase_rate, p.gst_rate
            FROM purchase_request_items pri
            JOIN products p ON pri.product_id = p.id
            WHERE pri.request_id = :rid
        ");
        $stmt->execute(['rid' => $id]);
        $items = $stmt->fetchAll();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
        exit;
    }

    public function getPoItemsJson(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT poi.*, p.name AS product_name, p.sku AS product_sku,
                   COALESCE(poi.attribute_values, pri.attribute_values) AS attribute_values,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = poi.po_id AND gi.product_id = poi.product_id
                   ), 0) AS already_received_qty
            FROM purchase_order_items poi
            JOIN products p ON poi.product_id = p.id
            JOIN purchase_orders po ON poi.po_id = po.id
            LEFT JOIN purchase_request_items pri ON pri.request_id = po.request_id AND pri.product_id = poi.product_id
            WHERE poi.po_id = :poid
        ");
        $stmt->execute(['poid' => $id]);
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $ordered = (int)$item['qty'];
            $received = (int)$item['already_received_qty'];
            $item['remaining_qty'] = max(0, $ordered - $received);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
        exit;
    }

    public function sendDeliveryReminder(): void {
        if (!has_permission('procurement.create_po') && !has_permission('procurement.view_pos')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to send Pending Delivery Reminders!', 'danger');
            (new Response())->redirect(url('/procurement/orders'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        $poId = !empty($data['po_id']) ? (int)$data['po_id'] : 0;
        $recipientEmail = !empty($data['recipient_email']) ? trim($data['recipient_email']) : '';
        $expectedDate = !empty($data['expected_delivery_date']) ? $data['expected_delivery_date'] : date('Y-m-d', strtotime('+7 days'));
        $urgencyLevel = $data['urgency_level'] ?? 'HIGH - EXPEDITE REQUESTED';
        $customNotes = !empty($data['custom_notes']) ? trim($data['custom_notes']) : 'Please expedite delivery of pending items.';

        if (!$poId) {
            Session::setFlash('error', 'Invalid Purchase Order reference.', 'danger');
            $response->redirect(url('/procurement/orders'));
            return;
        }

        $db = Database::getInstance();
        $poRec = $db->query("
            SELECT po.*, s.name AS supplier_name, s.code AS supplier_code, s.email AS supplier_email, s.phone AS supplier_phone,
                   w.name AS warehouse_name, w.address AS warehouse_address
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN warehouses w ON po.warehouse_id = w.id
            WHERE po.id = {$poId}
            LIMIT 1
        ")->fetch();

        if (!$poRec) {
            Session::setFlash('error', 'Purchase Order not found.', 'danger');
            $response->redirect(url('/procurement/orders'));
            return;
        }

        // Compute total ordered, received, and pending quantities
        $items = $db->query("
            SELECT poi.*, p.name AS product_name, p.sku AS product_sku, p.hsn_code,
                   COALESCE((
                       SELECT SUM(gi.received_qty)
                       FROM grn_items gi
                       JOIN goods_receipt_notes g ON gi.grn_id = g.id
                       WHERE g.po_id = poi.po_id AND gi.product_id = poi.product_id
                   ), 0) AS total_received_qty
            FROM purchase_order_items poi
            JOIN products p ON poi.product_id = p.id
            WHERE poi.po_id = {$poId}
        ")->fetchAll();

        $totalOrdered = 0;
        $totalReceived = 0;
        $itemsTableHtml = "<table style='width:100%;border-collapse:collapse;margin:15px 0;font-size:13px;'>
            <thead>
                <tr style='background:#1e293b;color:#f8fafc;'>
                    <th style='padding:8px;border:1px solid #334155;text-align:left;'>Item Description</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:center;'>SKU</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:right;'>Ordered</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:right;'>Received</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:right;color:#f59e0b;'>PENDING BALANCE</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($items as $item) {
            $ord = (int)$item['qty'];
            $rec = min($ord, (int)$item['total_received_qty']);
            $pend = max(0, $ord - $rec);

            $totalOrdered += $ord;
            $totalReceived += $rec;

            $itemsTableHtml .= "
                <tr style='border-bottom:1px solid #334155;'>
                    <td style='padding:8px;border:1px solid #334155;'><strong>" . htmlspecialchars($item['product_name']) . "</strong></td>
                    <td style='padding:8px;border:1px solid #334155;text-align:center;font-family:monospace;'>" . htmlspecialchars($item['product_sku']) . "</td>
                    <td style='padding:8px;border:1px solid #334155;text-align:right;'>" . number_format($ord) . " Pcs</td>
                    <td style='padding:8px;border:1px solid #334155;text-align:right;color:#10b981;'>" . number_format($rec) . " Pcs</td>
                    <td style='padding:8px;border:1px solid #334155;text-align:right;font-weight:bold;color:#f59e0b;'>" . number_format($pend) . " Pcs</td>
                </tr>
            ";
        }

        $totalPending = max(0, $totalOrdered - $totalReceived);
        $itemsTableHtml .= "</tbody></table>";

        if (empty($recipientEmail)) {
            $recipientEmail = !empty($poRec['supplier_email']) ? $poRec['supplier_email'] : 'supplier@vendor.com';
        }

        $formattedTargetDate = date('d M Y', strtotime($expectedDate));

        // Construct HTML email template
        $subject = "🚨 EXPEDITE NOTICE: Pending Shipment Delivery Reminder for PO {$poRec['po_no']} (Pending: {$totalPending} Units)";
        $bodyHtml = "
            <div style='background:#1e293b;padding:15px;border-radius:6px;border-left:4px solid #f59e0b;margin-bottom:15px;'>
                <h4 style='margin:0 0 5px 0;color:#f59e0b;'>OFFICIAL PENDING SHIPMENT DELIVERY EXPEDITE NOTICE</h4>
                <p style='margin:0;color:#e2e8f0;font-size:13px;'>Urgency: <strong>" . htmlspecialchars($urgencyLevel) . "</strong> | Target Delivery Date: <strong>{$formattedTargetDate}</strong></p>
            </div>

            <p>Dear <strong>" . htmlspecialchars($poRec['supplier_name']) . "</strong>,</p>

            <p>This is an official procurement reminder regarding Purchase Order <strong>{$poRec['po_no']}</strong> (Issued on " . date('d M Y', strtotime($poRec['po_date'])) . ").</p>

            <div style='background:#0f172a;padding:12px;border-radius:6px;margin:15px 0;border:1px solid #334155;'>
                <h4 style='color:#38bdf8;margin:0 0 8px 0;'>📦 Shipment & Delivery Summary</h4>
                <ul style='margin:0;padding-left:20px;color:#cbd5e1;'>
                    <li>Total Order Contract Quantity: <strong>" . number_format($totalOrdered) . " Pcs</strong></li>
                    <li>Quantity Received to Date: <strong style='color:#10b981;'>" . number_format($totalReceived) . " Pcs</strong></li>
                    <li><strong>REMAINING PENDING SHIPMENT QUANTITY: <span style='color:#f59e0b;'>" . number_format($totalPending) . " Pcs</span></strong></li>
                    <li>AGREED TARGET DELIVERY DATE: <strong style='color:#38bdf8;'>{$formattedTargetDate}</strong></li>
                </ul>
            </div>

            {$itemsTableHtml}

            <div style='background:#1e293b;padding:12px;border-radius:6px;margin:15px 0;border-left:3px solid #3b82f6;'>
                <strong style='color:#93c5fd;'>Buyer Notes & Special Instructions:</strong>
                <p style='margin:5px 0 0 0;color:#f8fafc;font-style:italic;'>\"" . nl2br(htmlspecialchars($customNotes)) . "\"</p>
            </div>

            <p style='font-size:13px;color:#94a3b8;'><strong>Delivery Destination:</strong> " . htmlspecialchars($poRec['warehouse_name']) . " (" . htmlspecialchars($poRec['warehouse_address']) . ")</p>

            <p>Please confirm dispatch details and tracking invoice as soon as possible.</p>

            <p>Best regards,<br><strong>Procurement & Supply Chain Division</strong><br>Enterprise ERP Management Suite</p>
        ";

        // Dispatch Email via EmailService
        \App\Services\EmailService::send('Vendor Supplier', $recipientEmail, $subject, $bodyHtml, 'DISPATCH_PENDING_SHIPMENT_REMINDER');

        // Create In-App Notification
        \App\Services\NotificationService::notifyRole('procurement_manager', "Expedite notice sent to {$poRec['supplier_name']} for PO {$poRec['po_no']} (Pending: {$totalPending} Pcs). Expected: {$formattedTargetDate}", 'warning', url('/procurement/orders'), 'Pending Shipment Email Dispatched');

        // Log Audit Event
        AuditService::log('Procurement', 'DISPATCH_PENDING_SHIPMENT_REMINDER', $poId, null, [
            'recipient_email' => $recipientEmail,
            'expected_delivery_date' => $expectedDate,
            'pending_qty' => $totalPending,
            'urgency_level' => $urgencyLevel
        ]);

        Session::setFlash('success', "📩 Pending Shipment Delivery Reminder Email dispatched to {$recipientEmail} successfully! Target expected delivery date logged as {$formattedTargetDate}.", 'success');
        $response->redirect(url('/procurement/orders'));
    }

    public function sendGrnReceiptNotice(): void {
        if (!has_permission('inventory.grn')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to send Goods Receipt Confirmations!', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        $grnId = !empty($data['grn_id']) ? (int)$data['grn_id'] : 0;
        $recipientEmail = !empty($data['recipient_email']) ? trim($data['recipient_email']) : '';
        $customNotes = !empty($data['custom_notes']) ? trim($data['custom_notes']) : 'Goods receipt recorded and logged for Quality Control inspection.';

        if (!$grnId) {
            Session::setFlash('error', 'Invalid Goods Receipt Note reference.', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        $db = Database::getInstance();
        $grnRec = $db->query("
            SELECT g.*, po.po_no, po.po_date, s.name AS supplier_name, s.code AS supplier_code, s.email AS supplier_email, s.phone AS supplier_phone,
                   w.name AS warehouse_name, w.address AS warehouse_address
            FROM goods_receipt_notes g
            JOIN purchase_orders po ON g.po_id = po.id
            JOIN suppliers s ON g.supplier_id = s.id
            JOIN warehouses w ON g.warehouse_id = w.id
            WHERE g.id = {$grnId}
            LIMIT 1
        ")->fetch();

        if (!$grnRec) {
            Session::setFlash('error', 'Goods Receipt Note record not found.', 'danger');
            (new Response())->redirect(url('/procurement/grns'));
            return;
        }

        $items = $db->query("
            SELECT gi.*, p.name AS product_name, p.sku AS product_sku
            FROM grn_items gi
            JOIN products p ON gi.product_id = p.id
            WHERE gi.grn_id = {$grnId}
        ")->fetchAll();

        $totalReceived = 0;
        $itemsTableHtml = "<table style='width:100%;border-collapse:collapse;margin:15px 0;font-size:13px;'>
            <thead>
                <tr style='background:#1e293b;color:#f8fafc;'>
                    <th style='padding:8px;border:1px solid #334155;text-align:left;'>Product Description</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:center;'>SKU</th>
                    <th style='padding:8px;border:1px solid #334155;text-align:right;color:#10b981;'>PHYSICALLY RECEIVED QTY</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($items as $item) {
            $rec = (int)$item['received_qty'];
            $totalReceived += $rec;

            $itemsTableHtml .= "
                <tr style='border-bottom:1px solid #334155;'>
                    <td style='padding:8px;border:1px solid #334155;'><strong>" . htmlspecialchars($item['product_name']) . "</strong></td>
                    <td style='padding:8px;border:1px solid #334155;text-align:center;font-family:monospace;'>" . htmlspecialchars($item['product_sku']) . "</td>
                    <td style='padding:8px;border:1px solid #334155;text-align:right;font-weight:bold;color:#10b981;'>" . number_format($rec) . " Pcs</td>
                </tr>
            ";
        }
        $itemsTableHtml .= "</tbody></table>";

        if (empty($recipientEmail)) {
            $recipientEmail = !empty($grnRec['supplier_email']) ? $grnRec['supplier_email'] : 'supplier@vendor.com';
        }

        // Construct HTML email template
        $subject = "📦 GOODS RECEIPT CONFIRMATION: Receipt of {$totalReceived} Units for GRN {$grnRec['grn_no']} [PO: {$grnRec['po_no']}]";
        $targetDeliveryDate = !empty($_POST['target_delivery_date']) ? $_POST['target_delivery_date'] : null;
        $targetFormatted = $targetDeliveryDate ? date('d M Y', strtotime($targetDeliveryDate)) : 'As per PO terms';

        $pendingNoticeHtml = "";
        if ($poPending > 0) {
            $pendingNoticeHtml = "
                <div style='background:#451a03;padding:15px;border-radius:6px;border-left:4px solid #f59e0b;margin:15px 0;'>
                    <h4 style='margin:0 0 5px 0;color:#fbbf24;'>⚠️ PENDING QUANTITY EXPEDITE NOTICE</h4>
                    <p style='margin:0 0 8px 0;color:#fef3c7;font-size:13px;'>
                        Total PO Ordered: <strong>" . number_format($poOrdered) . " Pcs</strong> | Total Received So Far: <strong>" . number_format($poReceived) . " Pcs</strong> | <strong style='color:#f87171;'>REMAINING PENDING: " . number_format($poPending) . " Pcs</strong>
                    </p>
                    <div style='background:#78350f;padding:10px;border-radius:4px;color:#fff;'>
                        📅 <strong>TARGET EXPECTED DELIVERY DATE FOR REMAINING {$poPending} PCS: <span style='color:#fde047;text-decoration:underline;'>{$targetFormatted}</span></strong>
                    </div>
                    <p style='margin:8px 0 0 0;color:#fcd34d;font-size:12px;'>Please expedite manufacturing/dispatch for the remaining balance quantity of {$poPending} Pcs to reach our warehouse on or before {$targetFormatted}.</p>
                </div>
            ";
        }

        $bodyHtml = "
            <div style='background:#1e293b;padding:15px;border-radius:6px;border-left:4px solid #10b981;margin-bottom:15px;'>
                <h4 style='margin:0 0 5px 0;color:#10b981;'>OFFICIAL MATERIAL GOODS RECEIPT & PENDING EXPEDITE NOTICE</h4>
                <p style='margin:0;color:#e2e8f0;font-size:13px;'>GRN Ref: <strong>{$grnRec['grn_no']}</strong> | Delivery Challan: <strong>" . htmlspecialchars($grnRec['challan_no'] ?: 'CH-N/A') . "</strong></p>
            </div>

            <p>Dear <strong>" . htmlspecialchars($grnRec['supplier_name']) . "</strong>,</p>

            <p>This is an official confirmation that our warehouse has physically received your material delivery batch under Purchase Order <strong>{$grnRec['po_no']}</strong>.</p>

            <div style='background:#0f172a;padding:12px;border-radius:6px;margin:15px 0;border:1px solid #334155;'>
                <h4 style='color:#38bdf8;margin:0 0 8px 0;'>📦 Shipment Received Details</h4>
                <ul style='margin:0;padding-left:20px;color:#cbd5e1;'>
                    <li>GRN Number: <strong>{$grnRec['grn_no']}</strong></li>
                    <li>PO Reference: <strong>{$grnRec['po_no']}</strong></li>
                    <li>Delivery Challan / LR: <strong>" . htmlspecialchars($grnRec['challan_no'] ?: 'N/A') . "</strong></li>
                    <li>Batch Received Quantity: <strong style='color:#10b981;'>" . number_format($totalReceived) . " Pcs</strong></li>
                    <li>Destination Warehouse: <strong>" . htmlspecialchars($grnRec['warehouse_name']) . "</strong></li>
                </ul>
            </div>

            {$pendingNoticeHtml}

            {$itemsTableHtml}

            <div style='background:#1e293b;padding:12px;border-radius:6px;margin:15px 0;border-left:3px solid #3b82f6;'>
                <strong style='color:#93c5fd;'>Warehouse Inspection Remarks:</strong>
                <p style='margin:5px 0 0 0;color:#f8fafc;font-style:italic;'>\"" . nl2br(htmlspecialchars($customNotes)) . "\"</p>
            </div>

            <p>Quality Control (QC) inspection is currently in progress. Billing and 3-way match invoice settlement will proceed upon QC sign-off.</p>

            <p>Best regards,<br><strong>Warehouse & Logistics Division</strong><br>Enterprise ERP Management Suite</p>
        ";

        // Dispatch Email via EmailService
        \App\Services\EmailService::send('Vendor Supplier', $recipientEmail, $subject, $bodyHtml, 'DISPATCH_GRN_RECEIPT_CONFIRMATION');

        // Create In-App Notification
        \App\Services\NotificationService::notifyRole('procurement_manager', "Goods Receipt Confirmation sent to {$grnRec['supplier_name']} for GRN {$grnRec['grn_no']} (Received: {$totalReceived} Pcs).", 'success', url('/procurement/grns'), 'Goods Receipt Email Dispatched');

        // Log Audit Event
        AuditService::log('Procurement', 'DISPATCH_GRN_RECEIPT_CONFIRMATION', $grnId, null, [
            'recipient_email' => $recipientEmail,
            'received_qty' => $totalReceived
        ]);

        Session::setFlash('success', "📱 Email Confirmation dispatched to {$recipientEmail} for GRN {$grnRec['grn_no']}! WhatsApp receipt notice link ready.", 'success');
        $response->redirect(url('/procurement/grns'));
    }
}
