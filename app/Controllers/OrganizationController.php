<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Company;
use App\Models\Branch;
use App\Services\AuditService;

class OrganizationController extends Controller {
    public function index(): void {
        if (!has_permission('organization.manage') && !has_permission('inventory.read')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.manage) to view Multi-Organization Structure!', 'danger');
            (new \App\Core\Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $companies = Company::all();
        $branches = Branch::all();
        $warehouses = $db->query("
            SELECT w.*, b.name AS branch_name, c.name AS company_name
            FROM warehouses w
            JOIN branches b ON w.branch_id = b.id
            JOIN companies c ON b.company_id = c.id
        ")->fetchAll();
        $racks = $db->query("
            SELECT r.*, w.name AS warehouse_name, w.code AS warehouse_code
            FROM racks r
            JOIN warehouses w ON r.warehouse_id = w.id
        ")->fetchAll();
        $bins = $db->query("
            SELECT bn.*, r.code AS rack_code, w.name AS warehouse_name
            FROM bins bn
            JOIN racks r ON bn.rack_id = r.id
            JOIN warehouses w ON r.warehouse_id = w.id
        ")->fetchAll();

        $this->render('organization/index', [
            'title' => 'Multi-Organization Structure (Company -> Branch -> Warehouse -> Rack -> Bin)',
            'companies' => $companies,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'racks' => $racks,
            'bins' => $bins
        ]);
    }

    public function storeCompany(): void {
        if (!has_permission('organization.manage') && !has_permission('organization.create_company')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.create_company) to add enterprise companies.', 'danger');
            (new \App\Core\Response())->redirect(url('/organization'));
            return;
        }

        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $data = $request->getBody();

        if (empty($data['name']) || empty($data['code'])) {
            \App\Core\Session::setFlash('error', 'Company name and code are required.', 'danger');
            $response->redirect(url('/organization'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO companies (name, code, tax_id, address) VALUES (:name, :code, :tax_id, :address)");
            $stmt->execute([
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'tax_id' => trim($data['tax_id'] ?? 'TAX-IN-' . rand(100000, 999999)),
                'address' => trim($data['address'] ?? '')
            ]);
            $companyId = (int)$db->lastInsertId();

            \App\Services\AuditService::log('Organization', 'CREATE_COMPANY', $companyId, null, $data);
            \App\Core\Session::setFlash('success', "Company '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            \App\Core\Session::setFlash('error', 'Failed to add company: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/organization'));
    }

    public function storeBranch(): void {
        if (!has_permission('organization.manage') && !has_permission('organization.create_branch')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.create_branch) to add operating branches.', 'danger');
            (new \App\Core\Response())->redirect(url('/organization'));
            return;
        }

        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $data = $request->getBody();

        if (empty($data['company_id']) || empty($data['name']) || empty($data['code'])) {
            \App\Core\Session::setFlash('error', 'Company, branch name and branch code are required.', 'danger');
            $response->redirect(url('/organization'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO branches (company_id, name, code, address) VALUES (:cid, :name, :code, :address)");
            $stmt->execute([
                'cid' => (int)$data['company_id'],
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'address' => trim($data['address'] ?? '')
            ]);
            $branchId = (int)$db->lastInsertId();

            \App\Services\AuditService::log('Organization', 'CREATE_BRANCH', $branchId, null, $data);
            \App\Core\Session::setFlash('success', "Branch '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            \App\Core\Session::setFlash('error', 'Failed to add branch: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/organization'));
    }

    public function storeWarehouse(): void {
        if (!has_permission('organization.manage') && !has_permission('organization.create_warehouse')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.create_warehouse) to add warehouses.', 'danger');
            (new \App\Core\Response())->redirect(url('/organization'));
            return;
        }

        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $data = $request->getBody();

        if (empty($data['branch_id']) || empty($data['name']) || empty($data['code'])) {
            \App\Core\Session::setFlash('error', 'Branch, Warehouse Name and Code are required.', 'danger');
            $response->redirect(url('/organization'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO warehouses (branch_id, name, code, capacity_sqft, address) VALUES (:bid, :name, :code, :cap, :addr)");
            $stmt->execute([
                'bid' => (int)$data['branch_id'],
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'cap' => (float)($data['capacity_sqft'] ?? 5000),
                'addr' => trim($data['address'] ?? '')
            ]);
            $whId = (int)$db->lastInsertId();

            \App\Services\AuditService::log('Organization', 'CREATE_WAREHOUSE', $whId, null, $data);
            \App\Core\Session::setFlash('success', "Warehouse '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            \App\Core\Session::setFlash('error', 'Failed to add warehouse: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/organization'));
    }

    public function storeRack(): void {
        if (!has_permission('organization.manage') && !has_permission('organization.create_rack')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.create_rack) to add storage racks.', 'danger');
            (new \App\Core\Response())->redirect(url('/organization'));
            return;
        }

        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $data = $request->getBody();

        if (empty($data['warehouse_id']) || empty($data['name']) || empty($data['code'])) {
            \App\Core\Session::setFlash('error', 'Warehouse, Rack Name and Code are required.', 'danger');
            $response->redirect(url('/organization'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO racks (warehouse_id, name, code) VALUES (:wid, :name, :code)");
            $stmt->execute([
                'wid' => (int)$data['warehouse_id'],
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code']))
            ]);
            $rackId = (int)$db->lastInsertId();

            \App\Services\AuditService::log('Organization', 'CREATE_RACK', $rackId, null, $data);
            \App\Core\Session::setFlash('success', "Storage Rack '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            \App\Core\Session::setFlash('error', 'Failed to add rack: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/organization'));
    }

    public function storeBin(): void {
        if (!has_permission('organization.manage') && !has_permission('organization.create_bin')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (organization.create_bin) to add storage bins.', 'danger');
            (new \App\Core\Response())->redirect(url('/organization'));
            return;
        }

        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $data = $request->getBody();

        if (empty($data['rack_id']) || empty($data['name']) || empty($data['code'])) {
            \App\Core\Session::setFlash('error', 'Rack, Bin Name and Code are required.', 'danger');
            $response->redirect(url('/organization'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO bins (rack_id, name, code, max_capacity) VALUES (:rid, :name, :code, :cap)");
            $stmt->execute([
                'rid' => (int)$data['rack_id'],
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'cap' => (int)($data['max_capacity'] ?? 1000)
            ]);
            $binId = (int)$db->lastInsertId();

            \App\Services\AuditService::log('Organization', 'CREATE_BIN', $binId, null, $data);
            \App\Core\Session::setFlash('success', "Storage Bin '{$data['code']}' added successfully!", 'success');
        } catch (\Exception $e) {
            \App\Core\Session::setFlash('error', 'Failed to add bin location: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/organization'));
    }
}
