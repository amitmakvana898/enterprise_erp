<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuditService;
use Exception;

class CustomerPortalController extends Controller {

    /* ─── CUSTOMER LOGIN VIEW ─── */
    public function login(): void {
        if (!empty($_SESSION['customer_user'])) {
            (new Response())->redirect(url('/customer-portal/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $customers = $db->query("SELECT id, name, email FROM customers WHERE status = 'active' LIMIT 10")->fetchAll();

        $this->render('customer_portal/login', [
            'title' => 'Customer Self-Service Portal Login',
            'customers' => $customers
        ], 'layouts/auth');
    }

    /* ─── CUSTOMER AUTHENTICATION ─── */
    public function authenticate(): void {
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Please enter your customer email and password.', 'danger');
            $response->redirect(url('/customer-portal/login'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM customers WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $customer = $stmt->fetch();

        if (!$customer || !password_verify($password, $customer['password'] ?? '')) {
            // Fallback for demo testing: check if email matches customer
            if ($customer && $password === 'customer123') {
                $_SESSION['customer_user'] = $customer;
                Session::setFlash('success', "Welcome to your Customer Self-Service Portal, {$customer['name']}!", 'success');
                $response->redirect(url('/customer-portal/dashboard'));
                return;
            }

            Session::setFlash('error', 'Invalid customer credentials. Use email and password: customer123', 'danger');
            $response->redirect(url('/customer-portal/login'));
            return;
        }

        $_SESSION['customer_user'] = $customer;
        Session::setFlash('success', "Welcome to your Customer Self-Service Portal, {$customer['name']}!", 'success');
        $response->redirect(url('/customer-portal/dashboard'));
    }

    /* ─── CUSTOMER PORTAL DASHBOARD ─── */
    public function dashboard(): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();

        $quotations = $db->prepare("
            SELECT sq.*, (SELECT COUNT(*) FROM sales_quotation_items sqi WHERE sqi.quotation_id = sq.id) AS item_count
            FROM sales_quotations sq
            WHERE sq.customer_id = :cid
            ORDER BY sq.id DESC
        ");
        $quotations->execute(['cid' => $customer['id']]);
        $quotationsList = $quotations->fetchAll();

        $orders = $db->prepare("
            SELECT so.*, w.name AS warehouse_name
            FROM sales_orders so
            LEFT JOIN warehouses w ON so.warehouse_id = w.id
            WHERE so.customer_id = :cid
            ORDER BY so.id DESC
        ");
        $orders->execute(['cid' => $customer['id']]);
        $ordersList = $orders->fetchAll();

        $invoices = $db->prepare("
            SELECT si.*, COALESCE(so.order_no, 'N/A') AS order_no
            FROM sales_invoices si
            LEFT JOIN sales_orders so ON si.order_id = so.id
            WHERE si.customer_id = :cid
            ORDER BY si.id DESC
        ");
        $invoices->execute(['cid' => $customer['id']]);
        $invoicesList = $invoices->fetchAll();

        $this->render('customer_portal/dashboard', [
            'title' => 'Customer Portal Dashboard',
            'customer' => $customer,
            'quotations' => $quotationsList,
            'orders' => $ordersList,
            'invoices' => $invoicesList
        ]);
    }

    /* ─── CUSTOMER QUOTATIONS LIST ─── */
    public function quotations(): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();

        $quotations = $db->prepare("
            SELECT sq.*, (SELECT COUNT(*) FROM sales_quotation_items sqi WHERE sqi.quotation_id = sq.id) AS item_count
            FROM sales_quotations sq
            WHERE sq.customer_id = :cid
            ORDER BY sq.id DESC
        ");
        $quotations->execute(['cid' => $customer['id']]);
        $quotationsList = $quotations->fetchAll();

        $this->render('customer_portal/quotations', [
            'title' => 'My Price Quotations & Estimates',
            'customer' => $customer,
            'quotations' => $quotationsList
        ]);
    }

    /* ─── CUSTOMER INVOICES & PAYMENTS VIEW ─── */
    public function invoices(): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();

        $invoices = $db->prepare("
            SELECT si.*, COALESCE(so.order_no, 'N/A') AS order_no
            FROM sales_invoices si
            LEFT JOIN sales_orders so ON si.order_id = so.id
            WHERE si.customer_id = :cid
            ORDER BY si.id DESC
        ");
        $invoices->execute(['cid' => $customer['id']]);
        $invoicesList = $invoices->fetchAll();

        $payments = $db->prepare("
            SELECT sp.*, si.invoice_no
            FROM sales_payments sp
            JOIN sales_invoices si ON sp.invoice_id = si.id
            WHERE sp.customer_id = :cid
            ORDER BY sp.id DESC
        ");
        $payments->execute(['cid' => $customer['id']]);
        $paymentsList = $payments->fetchAll();

        $this->render('customer_portal/invoices', [
            'title' => 'My Invoices & Payment Gateway',
            'customer' => $customer,
            'invoices' => $invoicesList,
            'payments' => $paymentsList
        ]);
    }

    /* ─── SHOW DETAILED FORMAL QUOTATION DOCUMENT ─── */
    public function showQuotation(int $id): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();

        $qStmt = $db->prepare("
            SELECT sq.*, c.name AS customer_name, c.code AS customer_code, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.gstin AS customer_gstin
            FROM sales_quotations sq
            JOIN customers c ON sq.customer_id = c.id
            WHERE sq.id = :id AND sq.customer_id = :cid
            LIMIT 1
        ");
        $qStmt->execute(['id' => $id, 'cid' => $customer['id']]);
        $quotation = $qStmt->fetch();

        if (!$quotation) {
            Session::setFlash('error', 'Quotation document not found.', 'danger');
            (new Response())->redirect(url('/customer-portal/quotations'));
            return;
        }

        $itemsStmt = $db->prepare("
            SELECT sqi.*, p.name AS product_name, p.sku, p.hsn_code
            FROM sales_quotation_items sqi
            JOIN products p ON sqi.product_id = p.id
            WHERE sqi.quotation_id = :qid
        ");
        $itemsStmt->execute(['qid' => $id]);
        $items = $itemsStmt->fetchAll();

        $this->render('customer_portal/show_quotation', [
            'title' => "Formal Quotation {$quotation['quotation_no']}",
            'customer' => $customer,
            'quotation' => $quotation,
            'items' => $items
        ]);
    }

    /* ─── CREATE CUSTOMER ORDER REQUEST VIEW ─── */
    public function createRequest(): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();

        $products = $db->query("SELECT id, name, sku, selling_rate FROM products WHERE status = 'active' ORDER BY name ASC")->fetchAll();

        $this->render('customer_portal/create_request', [
            'title' => 'Submit New Customer Order Request',
            'customer' => $customer,
            'products' => $products
        ]);
    }

    /* ─── STORE CUSTOMER ORDER REQUEST ─── */
    public function storeRequest(): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['qty'])) {
            Session::setFlash('error', 'Select a product and enter requested quantity.', 'danger');
            $response->redirect(url('/customer-portal/requests/create'));
            return;
        }

        $db = Database::getInstance();
        $productId = (int)$data['product_id'];
        $qty = (int)$data['qty'];
        $reqType = trim($data['requirement_type'] ?? 'Standard Order');
        $userNotes = trim($data['inquiry_notes'] ?? $data['notes'] ?? '');
        $notes = "[Type: {$reqType}]" . ($userNotes !== '' ? " Notes: {$userNotes}" : '');
        $systemUserId = (int)($db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 17);

        $pStmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $pStmt->execute(['id' => $productId]);
        $prod = $pStmt->fetch();

        $rate = (float)($prod['selling_rate'] ?? 1000.00);
        $totalAmt = ($qty * $rate) * 1.18; // incl 18% GST

        try {
            Database::beginTransaction();

            $qNo = 'REQ-' . date('Y') . '-' . rand(1000, 9999);
            $stmt = $db->prepare("
                INSERT INTO sales_quotations (quotation_no, company_id, branch_id, customer_id, quotation_date, valid_until, total_amount, status, created_by)
                VALUES (:qno, 1, 1, :cid, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), :tot, 'pending_quote', :uid)
            ");
            $stmt->execute([
                'qno' => $qNo,
                'cid' => $customer['id'],
                'tot' => $totalAmt,
                'uid' => $systemUserId
            ]);
            $qId = (int)$db->lastInsertId();

            $sqiStmt = $db->prepare("
                INSERT INTO sales_quotation_items (quotation_id, product_id, qty, unit_price, total_price)
                VALUES (:qid, :pid, :qty, :price, :tot)
            ");
            $sqiStmt->execute([
                'qid' => $qId,
                'pid' => $productId,
                'qty' => $qty,
                'price' => $rate,
                'tot' => $totalAmt
            ]);

            // Notify Sales Manager via Bell Icon
            $notifStmt = $db->prepare("
                INSERT INTO notifications (role_target, title, message, link, is_read)
                VALUES ('sales', :title, :msg, :link, 0)
            ");
            $notifStmt->execute([
                'title' => '📥 New Customer Order Inquiry Received!',
                'msg' => "Customer {$customer['name']} submitted Order Request {$qNo} for {$qty}x {$prod['name']}. Notes: {$notes}",
                'link' => '/sales'
            ]);

            Database::commit();
            AuditService::log('CustomerPortal', 'CREATE_ORDER_REQUEST', $qId, null, $data);

            Session::setFlash('success', "🎉 Your Order Request {$qNo} has been submitted! Sales Manager notified via Bell Icon.", 'success');
            $response->redirect(url('/customer-portal/dashboard'));

        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to submit order request: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/customer-portal/requests/create'));
        }
    }

    /* ─── 1-CLICK ACCEPT & PLACE ORDER ACTION ─── */
    public function acceptQuotation(int $id): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $response = new Response();
        $db = Database::getInstance();
        $systemUserId = (int)($db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 17);

        $qStmt = $db->prepare("SELECT * FROM sales_quotations WHERE id = :id AND customer_id = :cid LIMIT 1");
        $qStmt->execute(['id' => $id, 'cid' => $customer['id']]);
        $quotation = $qStmt->fetch();

        if (!$quotation) {
            Session::setFlash('error', 'Quotation not found.', 'danger');
            $response->redirect(url('/customer-portal/quotations'));
            return;
        }

        if ($quotation['status'] === 'converted') {
            Session::setFlash('warning', 'This quotation has already been converted to an active Sales Order!', 'warning');
            $response->redirect(url('/customer-portal/quotations'));
            return;
        }

        Database::beginTransaction();

        try {
            // 1. Fetch all quotation items
            $qiStmt = $db->prepare("SELECT * FROM sales_quotation_items WHERE quotation_id = :qid");
            $qiStmt->execute(['qid' => $id]);
            $items = $qiStmt->fetchAll();

            $totalAmt = (float)$quotation['total_amount'];

            // 2. Generate Sales Order No
            $orderNo = 'SO-' . date('Y') . '-' . rand(1000, 9999);
            $targetWarehouseId = !empty($quotation['warehouse_id']) ? (int)$quotation['warehouse_id'] : 1;
            $soStmt = $db->prepare("
                INSERT INTO sales_orders (order_no, company_id, branch_id, customer_id, warehouse_id, order_date, total_amount, status, created_by)
                VALUES (:ono, 1, 1, :cid, :wid, NOW(), :tot, 'approved', :uid)
            ");
            $soStmt->execute([
                'ono' => $orderNo,
                'cid' => $customer['id'],
                'wid' => $targetWarehouseId,
                'tot' => $totalAmt,
                'uid' => $systemUserId
            ]);
            $orderId = (int)$db->lastInsertId();

            // 3. Insert All Sales Order Items
            $soiStmt = $db->prepare("
                INSERT INTO sales_order_items (order_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price)
                VALUES (:oid, :pid, :uid, :qty, :price, :trate, :tamt, :tot)
            ");
            
            if (!empty($items)) {
                foreach ($items as $qi) {
                    $soiStmt->execute([
                        'oid' => $orderId,
                        'pid' => (int)$qi['product_id'],
                        'uid' => !empty($qi['unit_id']) ? (int)$qi['unit_id'] : 1,
                        'qty' => (int)$qi['qty'],
                        'price' => (float)$qi['unit_price'],
                        'trate' => !empty($qi['tax_rate']) ? (float)$qi['tax_rate'] : 18.00,
                        'tamt' => !empty($qi['tax_amount']) ? (float)$qi['tax_amount'] : 0.00,
                        'tot' => (float)$qi['total_price']
                    ]);
                }
            } else {
                // Fallback for edge cases with empty item rows
                $soiStmt->execute([
                    'oid' => $orderId,
                    'pid' => 1,
                    'uid' => 1,
                    'qty' => 1,
                    'price' => $totalAmt,
                    'trate' => 18.00,
                    'tamt' => 0.00,
                    'tot' => $totalAmt
                ]);
            }

            // 4. Update Quotation status to converted
            $db->prepare("UPDATE sales_quotations SET status = 'converted' WHERE id = :id")->execute(['id' => $id]);

            // Tax Invoice is NOT generated here; it is generated by Admin during Dispatch (Step 5) O2C Workflow

            // 6. Insert Bell Icon Notification for Sales Manager
            $notifStmt = $db->prepare("
                INSERT INTO notifications (role_target, title, message, link, is_read)
                VALUES ('sales', :title, :msg, :link, 0)
            ");
            $notifStmt->execute([
                'title' => '🔔 New Customer Order Placed!',
                'msg' => "Customer {$customer['name']} accepted quotation {$quotation['quotation_no']} and placed Sales Order {$orderNo} (Valuation: " . format_currency($totalAmt) . ").",
                'link' => '/sales'
            ]);

            Database::commit();
            AuditService::log('CustomerPortal', 'ACCEPT_QUOTATION_PLACE_SO', $orderId, null, ['quotation_id' => $id, 'customer_id' => $customer['id']]);

            Session::setFlash('success', "🎉 Quotation Accepted! Sales Order {$orderNo} has been auto-placed and Sales Manager notified via Bell Icon.", 'success');
            $response->redirect(url('/customer-portal/dashboard'));

        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to place Sales Order: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/customer-portal/quotations'));
        }
    }

    /* ─── CUSTOMER PORTAL REJECT QUOTATION ACTION ─── */
    public function rejectQuotation(int $id): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $response = new Response();
        $db = Database::getInstance();

        $qStmt = $db->prepare("SELECT * FROM sales_quotations WHERE id = :id AND customer_id = :cid LIMIT 1");
        $qStmt->execute(['id' => $id, 'cid' => $customer['id']]);
        $quotation = $qStmt->fetch();

        if (!$quotation) {
            Session::setFlash('error', 'Quotation not found.', 'danger');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        if ($quotation['status'] === 'converted' || $quotation['status'] === 'dispatched') {
            Session::setFlash('warning', 'This quotation has already been converted to an active Sales Order!', 'warning');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        try {
            Database::beginTransaction();

            // 1. Update status to rejected
            $db->prepare("UPDATE sales_quotations SET status = 'rejected' WHERE id = :id")->execute(['id' => $id]);

            // 2. Notify Sales Team
            $db->prepare("
                INSERT INTO notifications (role_target, title, message, link, is_read)
                VALUES ('sales', '❌ Customer Rejected Quotation', :msg, '/sales', 0)
            ")->execute(['msg' => "Customer {$customer['name']} has rejected quotation {$quotation['quotation_no']}."]);

            Database::commit();
            AuditService::log('CustomerPortal', 'REJECT_QUOTATION', $id, null, ['quotation_no' => $quotation['quotation_no']]);

            Session::setFlash('warning', "❌ Quotation {$quotation['quotation_no']} has been rejected.", 'warning');
        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Failed to reject quotation: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/customer-portal/dashboard'));
    }

    /* ─── CUSTOMER PORTAL TAX INVOICE DETAILS ─── */
    public function showInvoice(int $id): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $db = Database::getInstance();
        $response = new Response();

        // Fetch detailed invoice
        $invStmt = $db->prepare("
            SELECT si.*, so.order_no, so.order_date, c.name AS customer_name, c.code AS customer_code, c.phone AS customer_phone, c.email AS customer_email, c.address AS customer_address, c.gstin AS customer_gstin,
                   dc.challan_no, dc.vehicle_no, w.name AS warehouse_name, comp.name AS company_name
            FROM sales_invoices si
            JOIN sales_orders so ON si.order_id = so.id
            JOIN customers c ON si.customer_id = c.id
            LEFT JOIN sales_delivery_challans dc ON dc.order_id = so.id
            LEFT JOIN warehouses w ON so.warehouse_id = w.id
            LEFT JOIN companies comp ON so.company_id = comp.id
            WHERE si.id = :id AND si.customer_id = :cid
            LIMIT 1
        ");
        $invStmt->execute(['id' => $id, 'cid' => $customer['id']]);
        $invoice = $invStmt->fetch();

        if (!$invoice) {
            Session::setFlash('error', 'Tax Invoice not found or unauthorized.', 'danger');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        // Fetch invoice items (linked to order items)
        $items = $db->prepare("
            SELECT soi.*, p.name AS product_name, p.sku, u.code AS unit_code
            FROM sales_order_items soi
            JOIN products p ON soi.product_id = p.id
            LEFT JOIN units u ON p.unit_id = u.id
            WHERE soi.order_id = :oid
        ");
        $items->execute(['oid' => $invoice['order_id']]);
        $invoiceItems = $items->fetchAll();

        $this->render('customer_portal/show_invoice', [
            'title' => "Tax Invoice {$invoice['invoice_no']}",
            'inv' => $invoice,
            'items' => $invoiceItems,
            'customer' => $customer
        ]);
    }

    /* ─── CUSTOMER PAY INVOICE ─── */
    public function payInvoice(int $id): void {
        $this->checkAuth();
        $customer = $_SESSION['customer_user'];
        $response = new Response();
        $db = Database::getInstance();
        $request = new Request();
        $data = $request->getBody();

        $invStmt = $db->prepare("SELECT si.*, COALESCE(so.order_no, 'N/A') AS order_no FROM sales_invoices si LEFT JOIN sales_orders so ON si.order_id = so.id WHERE si.id = :id AND si.customer_id = :cid LIMIT 1");
        $invStmt->execute(['id' => $id, 'cid' => $customer['id']]);
        $invoice = $invStmt->fetch();

        if (!$invoice) {
            Session::setFlash('error', 'Invoice not found.', 'danger');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        if ($invoice['status'] === 'paid') {
            Session::setFlash('warning', 'This invoice has already been fully paid.', 'warning');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        Database::beginTransaction();
        try {
            $payMode = $data['payment_mode'] ?? 'bank_transfer';
            $payRef = $data['reference_no'] ?? ('PAY-REF-' . rand(100000, 999999));
            $payAmt = (float)$invoice['total_amount'];
            $payNo = 'PAY-' . date('Y') . '-' . rand(1000, 9999);

            // Insert payment record
            $db->prepare("
                INSERT INTO sales_payments (payment_no, invoice_id, customer_id, payment_date, amount, payment_mode, reference_no)
                VALUES (:pno, :iid, :cid, NOW(), :amt, :mode, :ref)
            ")->execute([
                'pno' => $payNo,
                'iid' => $id,
                'cid' => $customer['id'],
                'amt' => $payAmt,
                'mode' => $payMode,
                'ref' => $payRef
            ]);

            // Mark invoice as paid
            $db->prepare("UPDATE sales_invoices SET paid_amount = total_amount, status = 'paid' WHERE id = :id")->execute(['id' => $id]);

            // Notify admin/sales team
            $db->prepare("
                INSERT INTO notifications (role_target, title, message, link, is_read)
                VALUES ('sales', '💰 Customer Payment Received!', :msg, '/sales', 0)
            ")->execute(['msg' => "Customer {$customer['name']} paid Invoice {$invoice['invoice_no']} (Order {$invoice['order_no']}) — Amount: " . format_currency($payAmt) . " via {$payMode}. Payment Ref: {$payRef}."]);

            Database::commit();
            AuditService::log('CustomerPortal', 'PAY_INVOICE', $id, null, ['payment_no' => $payNo]);
            Session::setFlash('success', "🎉 Payment of " . format_currency($payAmt) . " for Invoice {$invoice['invoice_no']} received successfully! Payment Ref: {$payNo}", 'success');

        } catch (Exception $e) {
            Database::rollBack();
            Session::setFlash('error', 'Payment failed: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/customer-portal/dashboard'));
    }

    /* ─── LOGOUT ─── */
    public function logout(): void {
        unset($_SESSION['customer_user']);
        unset($_SESSION['user']);
        unset($_SESSION['user_permissions']);
        Session::destroy();
        Session::setFlash('success', 'You have been successfully logged out.', 'info');
        (new Response())->redirect(url('/login'));
    }

    private function checkAuth(): void {
        if (empty($_SESSION['customer_user'])) {
            if (!empty($_SESSION['user'])) {
                $db = Database::getInstance();
                $uEmail = $_SESSION['user']['email'] ?? '';
                $custStmt = $db->prepare("SELECT * FROM customers WHERE email = :email AND status = 'active' LIMIT 1");
                $custStmt->execute(['email' => $uEmail]);
                $c = $custStmt->fetch();
                if ($c) {
                    $_SESSION['customer_user'] = $c;
                    return;
                }
            }

            Session::setFlash('warning', 'Please log in to access your account.', 'warning');
            (new Response())->redirect(url('/login'));
            exit;
        }
    }
}
