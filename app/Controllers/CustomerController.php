<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Customer;
use App\Services\AuditService;

class CustomerController extends Controller {
    public function index(): void {
        if (!has_permission('customers.manage') && !has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (customers.manage) to view Customer Directory!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $customers = Customer::all();
        $this->render('customers/index', ['title' => 'Customer Master Directory', 'customers' => $customers]);
    }

    public function create(): void {
        if (!has_permission('customers.manage') && !has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (customers.manage) to register customers!', 'danger');
            (new Response())->redirect(url('/customers'));
            return;
        }
        $this->render('customers/create', ['title' => 'Register New Customer']);
    }

    public function store(): void {
        if (!has_permission('customers.manage') && !has_permission('sales.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (customers.manage) to register customers!', 'danger');
            (new Response())->redirect(url('/customers'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name'])) {
            Session::setFlash('error', 'Customer name is required.', 'danger');
            $redirect = !empty($data['redirect']) ? $data['redirect'] : url('/customers/create');
            $response->redirect($redirect);
        }

        $db = Database::getInstance();
        $code = !empty($data['code']) ? strtoupper(trim($data['code'])) : 'CUST-' . rand(10000, 99999);
        $rawPassword = !empty($data['password']) ? trim($data['password']) : 'customer123';
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
        $email = !empty($data['email']) ? trim($data['email']) : strtolower($code) . '@customer.com';

        $gstin = !empty($data['gstin']) ? strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $data['gstin']))) : null;
        $phone = !empty($data['phone']) ? trim($data['phone']) : null;
        $address = !empty($data['address']) ? trim($data['address']) : null;

        try {
            $stmt = $db->prepare("
                INSERT INTO customers (name, code, email, phone, gstin, address, credit_limit, password, status)
                VALUES (:name, :code, :email, :phone, :gstin, :address, :credit_limit, :password, 'active')
            ");

            $stmt->execute([
                'name' => trim($data['name']),
                'code' => $code,
                'email' => $email,
                'phone' => $phone,
                'gstin' => $gstin,
                'address' => $address,
                'credit_limit' => (float)($data['credit_limit'] ?? 100000.00),
                'password' => $hashedPassword
            ]);

            $custId = (int)$db->lastInsertId();

            // Fetch Customer Role ID
            $roleStmt = $db->query("SELECT id FROM roles WHERE name = 'customer' LIMIT 1");
            $roleId = (int)($roleStmt->fetchColumn() ?: 10);

            // Automatically create user login account in users table
            $uStmt = $db->prepare("
                INSERT INTO users (company_id, branch_id, role_id, customer_id, name, email, password, status)
                VALUES (1, 1, :rid, :cid, :name, :email, :pass, 'active')
                ON DUPLICATE KEY UPDATE role_id = :rid2, customer_id = :cid2, password = :pass2
            ");
            $uStmt->execute([
                'rid' => $roleId,
                'cid' => $custId,
                'name' => trim($data['name']),
                'email' => $email,
                'pass' => $hashedPassword,
                'rid2' => $roleId,
                'cid2' => $custId,
                'pass2' => $hashedPassword
            ]);

            AuditService::log('Customer', 'CREATE_CUSTOMER', $custId, null, $data);
            Session::setFlash('success', "🎉 Customer '{$data['name']}' registered! Login Account Created (Email: {$email} | Passcode: {$rawPassword}).", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to register customer: ' . $e->getMessage(), 'danger');
        }

        $redirect = !empty($data['redirect']) ? $data['redirect'] : url('/customers');
        $response->redirect($redirect);
    }

    /**
     * Customer Statement of Account / Financial Ledger
     */
    public function statement(int $id): void {
        if (!has_permission('customers.manage') && !has_permission('sales.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Customer Statement!', 'danger');
            (new Response())->redirect(url('/customers'));
            return;
        }

        $db = Database::getInstance();
        $customer = Customer::find($id);

        if (!$customer) {
            Session::setFlash('error', 'Customer record not found!', 'danger');
            (new Response())->redirect(url('/customers'));
            return;
        }

        // Fetch Invoices (Debits)
        $invoices = $db->query("
            SELECT si.id, si.invoice_no, si.invoice_date AS trans_date, si.total_amount AS amount,
                   'INVOICE' AS trans_type, si.status, so.order_no
            FROM sales_invoices si
            JOIN sales_orders so ON si.order_id = so.id
            WHERE si.customer_id = {$id}
            ORDER BY si.invoice_date ASC, si.id ASC
        ")->fetchAll();

        // Fetch Payments (Credits)
        $payments = $db->query("
            SELECT sp.id, sp.payment_no, sp.payment_date AS trans_date, sp.amount,
                   'PAYMENT' AS trans_type, sp.payment_mode, si.invoice_no
            FROM sales_payments sp
            LEFT JOIN sales_invoices si ON sp.invoice_id = si.id
            WHERE sp.customer_id = {$id}
            ORDER BY sp.payment_date ASC, sp.id ASC
        ")->fetchAll();

        // Fetch Sales Returns (Credits)
        $returns = $db->query("
            SELECT sr.id, sr.return_no, sr.return_date AS trans_date, sr.refund_amount AS amount,
                   'RETURN' AS trans_type, sr.reason, so.order_no
            FROM sales_returns sr
            JOIN sales_orders so ON sr.order_id = so.id
            WHERE sr.customer_id = {$id}
            ORDER BY sr.return_date ASC, sr.id ASC
        ")->fetchAll();

        // Merge into a chronological ledger trail
        $transactions = [];
        foreach ($invoices as $inv) {
            $transactions[] = [
                'date' => $inv['trans_date'],
                'ref_no' => $inv['invoice_no'],
                'type' => 'Tax Invoice',
                'description' => 'Sales Invoice for Order #' . $inv['order_no'],
                'debit' => (float)$inv['amount'],
                'credit' => 0,
                'status' => strtoupper($inv['status'])
            ];
        }

        foreach ($payments as $pay) {
            $transactions[] = [
                'date' => $pay['trans_date'] ?? date('Y-m-d'),
                'ref_no' => $pay['payment_no'] ?? ('PAY-' . $pay['id']),
                'type' => 'Payment Receipt',
                'description' => 'Customer Settlement via ' . strtoupper($pay['payment_mode'] ?? 'Bank'),
                'debit' => 0,
                'credit' => (float)$pay['amount'],
                'status' => 'SETTLED'
            ];
        }

        foreach ($returns as $ret) {
            $transactions[] = [
                'date' => $ret['trans_date'],
                'ref_no' => $ret['return_no'],
                'type' => 'Credit Note (Return)',
                'description' => 'Sales Return for Order #' . $ret['order_no'] . ' (' . ($ret['reason'] ?: 'Inspection Return') . ')',
                'debit' => 0,
                'credit' => (float)$ret['amount'],
                'status' => 'REFUNDED'
            ];
        }

        // Sort by date ascending
        usort($transactions, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance
        $runningBalance = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($transactions as &$t) {
            $totalDebit += $t['debit'];
            $totalCredit += $t['credit'];
            $runningBalance += ($t['debit'] - $t['credit']);
            $t['balance'] = $runningBalance;
        }

        $this->render('customers/statement', [
            'title' => 'Statement of Account - ' . $customer['name'],
            'customer' => $customer,
            'transactions' => $transactions,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'netOutstanding' => $runningBalance
        ]);
    }
}

