<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
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
}
