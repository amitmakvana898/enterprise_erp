<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Supplier;
use App\Helpers\Validator;
use App\Services\AuditService;

class SupplierController extends Controller {
    public function index(): void {
        if (!has_permission('suppliers.manage')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (suppliers.manage) to view Supplier Master Management!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $suppliers = Supplier::all();
        $this->render('suppliers/index', ['title' => 'Supplier Master Management', 'suppliers' => $suppliers]);
    }

    public function create(): void {
        if (!has_permission('suppliers.manage')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (suppliers.manage) to register suppliers!', 'danger');
            (new Response())->redirect(url('/suppliers'));
            return;
        }
        $this->render('suppliers/create', ['title' => 'Register New Supplier']);
    }

    public function store(): void {
        if (!has_permission('suppliers.manage')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (suppliers.manage) to register suppliers!', 'danger');
            (new Response())->redirect(url('/suppliers'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['name' => 'required', 'code' => 'required', 'email' => 'required|email', 'phone' => 'required'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/suppliers/create'));
        }

        $id = Supplier::create([
            'company_id' => auth_user()['company_id'] ?? 1,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'tax_id' => $data['tax_id'] ?? '',
            'gstin' => $data['gstin'] ?? '',
            'bank_name' => $data['bank_name'] ?? '',
            'bank_account' => $data['bank_account'] ?? '',
            'bank_ifsc' => $data['bank_ifsc'] ?? '',
            'credit_limit' => (float) ($data['credit_limit'] ?? 500000.00),
            'payment_terms' => $data['payment_terms'] ?? 'Net 30',
            'rating' => (float) ($data['rating'] ?? 5.00),
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'country' => $data['country'] ?? 'India',
            'status' => 'active'
        ]);

        AuditService::log('Supplier', 'CREATE', $id, null, $data);
        Session::setFlash('success', 'Supplier registered successfully!', 'success');
        $response->redirect(url('/suppliers'));
    }

    /**
     * Supplier Statement of Account / Vendor Ledger
     */
    public function statement(int $id): void {
        if (!has_permission('suppliers.manage') && !has_permission('purchase.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Vendor Statement!', 'danger');
            (new Response())->redirect(url('/suppliers'));
            return;
        }

        $db = Database::getInstance();
        $supplier = Supplier::find($id);

        if (!$supplier) {
            Session::setFlash('error', 'Supplier record not found!', 'danger');
            (new Response())->redirect(url('/suppliers'));
            return;
        }

        // Fetch Vendor Invoices (Payables - Credit)
        $invoices = $db->query("
            SELECT pi.id, pi.invoice_no, pi.invoice_date AS trans_date, pi.total_amount AS amount,
                   'INVOICE' AS trans_type, pi.status, po.po_no
            FROM purchase_invoices pi
            LEFT JOIN purchase_orders po ON pi.po_id = po.id
            WHERE pi.supplier_id = {$id}
            ORDER BY pi.invoice_date ASC, pi.id ASC
        ")->fetchAll();

        // Fetch Payments Made (Debits)
        $payments = $db->query("
            SELECT pp.id, pp.payment_no, pp.payment_date AS trans_date, pp.amount,
                   'PAYMENT' AS trans_type, pp.payment_mode, pi.invoice_no
            FROM purchase_payments pp
            LEFT JOIN purchase_invoices pi ON pp.invoice_id = pi.id
            WHERE pp.supplier_id = {$id}
            ORDER BY pp.payment_date ASC, pp.id ASC
        ")->fetchAll();

        // Fetch Purchase Returns / Debit Notes (Debits)
        $returns = $db->query("
            SELECT pr.id, pr.return_no, pr.return_date AS trans_date, pr.total_refund_amount AS amount,
                   'DEBIT_NOTE' AS trans_type, pr.reason, po.po_no
            FROM purchase_returns pr
            LEFT JOIN purchase_orders po ON pr.po_id = po.id
            WHERE pr.supplier_id = {$id}
            ORDER BY pr.return_date ASC, pr.id ASC
        ")->fetchAll();

        // Merge into a chronological ledger trail
        $transactions = [];
        foreach ($invoices as $inv) {
            $transactions[] = [
                'date' => $inv['trans_date'],
                'ref_no' => $inv['invoice_no'],
                'type' => 'Vendor Tax Invoice',
                'description' => 'Procurement Billing for PO #' . ($inv['po_no'] ?? 'Direct'),
                'credit' => (float)$inv['amount'], // We owe the vendor (Payable)
                'debit' => 0,
                'status' => strtoupper($inv['status'])
            ];
        }

        foreach ($payments as $pay) {
            $transactions[] = [
                'date' => $pay['trans_date'] ?? date('Y-m-d'),
                'ref_no' => $pay['payment_no'] ?? ('PAY-' . $pay['id']),
                'type' => 'Payment Disbursed',
                'description' => 'Vendor Payment Disbursed via ' . strtoupper($pay['payment_mode'] ?? 'Bank'),
                'credit' => 0,
                'debit' => (float)$pay['amount'], // Reduced our liability
                'status' => 'DISBURSED'
            ];
        }

        foreach ($returns as $ret) {
            $transactions[] = [
                'date' => $ret['trans_date'],
                'ref_no' => $ret['return_no'],
                'type' => 'Debit Note (Return)',
                'description' => 'Purchase Return & Material Rejection for PO #' . ($ret['po_no'] ?? 'N/A') . ' (' . ($ret['reason'] ?: 'Quality Rejection') . ')',
                'credit' => 0,
                'debit' => (float)$ret['amount'],
                'status' => 'ADJUSTED'
            ];
        }

        // Sort by date ascending
        usort($transactions, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        // Compute running balance
        $runningBalance = 0;
        $totalPayable = 0;
        $totalPaid = 0;

        foreach ($transactions as &$t) {
            $totalPayable += $t['credit'];
            $totalPaid += $t['debit'];
            $runningBalance += ($t['credit'] - $t['debit']);
            $t['balance'] = $runningBalance;
        }

        $this->render('suppliers/statement', [
            'title' => 'Vendor Statement of Account - ' . $supplier['name'],
            'supplier' => $supplier,
            'transactions' => $transactions,
            'totalPayable' => $totalPayable,
            'totalPaid' => $totalPaid,
            'netPayable' => $runningBalance
        ]);
    }
}

