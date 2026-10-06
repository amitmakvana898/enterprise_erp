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
}
