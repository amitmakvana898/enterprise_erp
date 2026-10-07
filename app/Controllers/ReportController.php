<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;

class ReportController extends Controller {

    public function index(): void {
        if (!has_permission('reports.view')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (reports.view) to view Reports & Analytics!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $request = new Request();
        $filters = $request->getBody();

        $reportType = $filters['report_type'] ?? 'inventory'; // 'inventory', 'procurement', 'sales', 'ledger'
        $companyId = !empty($filters['company_id']) ? (int)$filters['company_id'] : null;
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;
        $warehouseId = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;
        $supplierId = !empty($filters['supplier_id']) ? (int)$filters['supplier_id'] : null;
        $productId = !empty($filters['product_id']) ? (int)$filters['product_id'] : null;
        $categoryId = !empty($filters['category_id']) ? (int)$filters['category_id'] : null;
        $status = !empty($filters['status']) ? trim($filters['status']) : null;
        $startDate = !empty($filters['start_date']) ? $filters['start_date'] : null;
        $endDate = !empty($filters['end_date']) ? $filters['end_date'] : null;

        $db = Database::getInstance();

        // 1. Filter Option Datasets for Dropdowns
        $companies = Company::all();
        $branches = Branch::all();
        $warehouses = Warehouse::all();
        $suppliers = Supplier::all();
        $products = Product::all();
        $categories = Category::all();

        // 2. Build Filtered Report Data
        $reportData = [];

        if ($reportType === 'procurement') {
            $where = ["1=1"];
            $params = [];

            if ($supplierId) { $where[] = "po.supplier_id = :sid"; $params['sid'] = $supplierId; }
            if ($status) { $where[] = "po.status = :st"; $params['st'] = $status; }
            if ($startDate) { $where[] = "DATE(po.created_at) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(po.created_at) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT po.*, s.name AS supplier_name, u.name AS creator_name
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id = s.id
                JOIN users u ON po.created_by = u.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY po.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll();

        } elseif ($reportType === 'sales') {
            $where = ["1=1"];
            $params = [];

            if ($companyId) { $where[] = "so.company_id = :cid"; $params['cid'] = $companyId; }
            if ($branchId) { $where[] = "so.branch_id = :bid"; $params['bid'] = $branchId; }
            if ($warehouseId) { $where[] = "so.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($status) { $where[] = "so.status = :st"; $params['st'] = $status; }
            if ($startDate) { $where[] = "DATE(so.order_date) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(so.order_date) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT so.*, c.name AS customer_name, w.name AS warehouse_name
                FROM sales_orders so
                JOIN customers c ON so.customer_id = c.id
                JOIN warehouses w ON so.warehouse_id = w.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY so.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll();

        } elseif ($reportType === 'ledger') {
            $where = ["1=1"];
            $params = [];

            if ($warehouseId) { $where[] = "t.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($productId) { $where[] = "t.product_id = :pid"; $params['pid'] = $productId; }
            if ($startDate) { $where[] = "DATE(t.created_at) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(t.created_at) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT t.*, p.name AS product_name, p.sku, w.name AS warehouse_name, bn.code AS bin_code, u.name AS user_name
                FROM stock_transactions t
                JOIN products p ON t.product_id = p.id
                JOIN warehouses w ON t.warehouse_id = w.id
                JOIN bins bn ON t.bin_id = bn.id
                JOIN users u ON t.created_by = u.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY t.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll();

        } else { // 'inventory' (default)
            $where = ["1=1"];
            $params = [];

            if ($warehouseId) { $where[] = "s.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($productId) { $where[] = "s.product_id = :pid"; $params['pid'] = $productId; }
            if ($categoryId) { $where[] = "p.category_id = :cat_id"; $params['cat_id'] = $categoryId; }

            $sql = "
                SELECT s.product_id, p.sku, p.name AS product_name, c.name AS category_name,
                       wh.name AS warehouse_name, bn.code AS bin_code, s.batch_no,
                       SUM(s.qty) AS total_qty, p.purchase_rate AS avg_unit_cost,
                       (SUM(s.qty) * p.purchase_rate) AS total_valuation
                FROM inventory_stocks s
                JOIN products p ON s.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN warehouses wh ON s.warehouse_id = wh.id
                JOIN bins bn ON s.bin_id = bn.id
                WHERE " . implode(" AND ", $where) . "
                GROUP BY s.warehouse_id, s.bin_id, s.product_id, s.batch_no
                ORDER BY p.name ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reportData = $stmt->fetchAll();
        }

        $this->render('reports/index', [
            'title' => 'Executive Filtering Reports & Export Analytics',
            'reportType' => $reportType,
            'reportData' => $reportData,
            'filters' => $filters,
            'companies' => $companies,
            'branches' => $branches,
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function exportCsv(): void {
        $this->generateExport('csv');
    }

    public function exportExcel(): void {
        $this->generateExport('excel');
    }

    public function exportPdf(): void {
        $request = new Request();
        $filters = $request->getBody();
        $this->render('reports/pdf', ['title' => 'Print / PDF Report', 'filters' => $filters], 'layouts/empty');
    }

    private function generateExport(string $format): void {
        if (!has_permission('reports.export') && !has_permission('reports.view')) {
            Session::setFlash('error', 'Access Denied', 'danger');
            (new Response())->redirect(url('/reports'));
            return;
        }

        $request = new Request();
        $filters = $request->getBody();
        $reportType = $filters['report_type'] ?? 'inventory';
        $companyId = !empty($filters['company_id']) ? (int)$filters['company_id'] : null;
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;
        $warehouseId = !empty($filters['warehouse_id']) ? (int)$filters['warehouse_id'] : null;
        $supplierId = !empty($filters['supplier_id']) ? (int)$filters['supplier_id'] : null;
        $productId = !empty($filters['product_id']) ? (int)$filters['product_id'] : null;
        $categoryId = !empty($filters['category_id']) ? (int)$filters['category_id'] : null;
        $status = !empty($filters['status']) ? trim($filters['status']) : null;
        $startDate = !empty($filters['start_date']) ? $filters['start_date'] : null;
        $endDate = !empty($filters['end_date']) ? $filters['end_date'] : null;

        $db = Database::getInstance();
        $headers = [];
        $rows = [];

        if ($reportType === 'procurement') {
            $headers = ['PO Number', 'Supplier', 'Creator', 'Total Amount', 'Status', 'Date'];
            $where = ["1=1"];
            $params = [];
            if ($supplierId) { $where[] = "po.supplier_id = :sid"; $params['sid'] = $supplierId; }
            if ($status) { $where[] = "po.status = :st"; $params['st'] = $status; }
            if ($startDate) { $where[] = "DATE(po.created_at) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(po.created_at) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT po.po_no, s.name AS supplier_name, u.name AS creator_name, po.total_amount, po.status, po.created_at
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id = s.id
                JOIN users u ON po.created_by = u.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY po.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $r) {
                $rows[] = [
                    $r['po_no'],
                    $r['supplier_name'],
                    $r['creator_name'],
                    number_format((float)$r['total_amount'], 2, '.', ''),
                    strtoupper($r['status']),
                    $r['created_at']
                ];
            }
        } elseif ($reportType === 'sales') {
            $headers = ['Order Number', 'Customer', 'Warehouse', 'Total Amount', 'Status', 'Order Date'];
            $where = ["1=1"];
            $params = [];
            if ($companyId) { $where[] = "so.company_id = :cid"; $params['cid'] = $companyId; }
            if ($branchId) { $where[] = "so.branch_id = :bid"; $params['bid'] = $branchId; }
            if ($warehouseId) { $where[] = "so.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($status) { $where[] = "so.status = :st"; $params['st'] = $status; }
            if ($startDate) { $where[] = "DATE(so.order_date) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(so.order_date) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT so.order_no, c.name AS customer_name, w.name AS warehouse_name, so.total_amount, so.status, so.order_date
                FROM sales_orders so
                JOIN customers c ON so.customer_id = c.id
                JOIN warehouses w ON so.warehouse_id = w.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY so.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $r) {
                $rows[] = [
                    $r['order_no'],
                    $r['customer_name'],
                    $r['warehouse_name'],
                    number_format((float)$r['total_amount'], 2, '.', ''),
                    strtoupper($r['status']),
                    $r['order_date']
                ];
            }
        } elseif ($reportType === 'ledger') {
            $headers = ['Txn No', 'Product SKU', 'Product Name', 'Warehouse', 'Bin Location', 'Batch No', 'Type', 'In Qty', 'Out Qty', 'Balance Qty', 'Unit Cost', 'Total Cost', 'Created By', 'Date'];
            $where = ["1=1"];
            $params = [];
            if ($warehouseId) { $where[] = "t.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($productId) { $where[] = "t.product_id = :pid"; $params['pid'] = $productId; }
            if ($startDate) { $where[] = "DATE(t.created_at) >= :sdate"; $params['sdate'] = $startDate; }
            if ($endDate) { $where[] = "DATE(t.created_at) <= :edate"; $params['edate'] = $endDate; }

            $sql = "
                SELECT t.transaction_no, p.sku, p.name AS product_name, w.name AS warehouse_name, bn.code AS bin_code,
                       t.batch_no, t.transaction_type, t.in_qty, t.out_qty, t.balance_qty, t.unit_cost, t.total_cost, u.name AS user_name, t.created_at
                FROM stock_transactions t
                JOIN products p ON t.product_id = p.id
                JOIN warehouses w ON t.warehouse_id = w.id
                JOIN bins bn ON t.bin_id = bn.id
                JOIN users u ON t.created_by = u.id
                WHERE " . implode(" AND ", $where) . "
                ORDER BY t.id DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $r) {
                $rows[] = [
                    $r['transaction_no'],
                    $r['sku'],
                    $r['product_name'],
                    $r['warehouse_name'],
                    $r['bin_code'],
                    $r['batch_no'],
                    $r['transaction_type'],
                    (int)$r['in_qty'],
                    (int)$r['out_qty'],
                    (int)$r['balance_qty'],
                    number_format((float)$r['unit_cost'], 2, '.', ''),
                    number_format((float)$r['total_cost'], 2, '.', ''),
                    $r['user_name'],
                    $r['created_at']
                ];
            }
        } else { // 'inventory'
            $headers = ['SKU', 'Product Name', 'Category', 'Warehouse', 'Bin Location', 'Batch No', 'Total Qty', 'Avg Unit Cost', 'Total Valuation'];
            $where = ["1=1"];
            $params = [];
            if ($warehouseId) { $where[] = "s.warehouse_id = :wid"; $params['wid'] = $warehouseId; }
            if ($productId) { $where[] = "s.product_id = :pid"; $params['pid'] = $productId; }
            if ($categoryId) { $where[] = "p.category_id = :cat_id"; $params['cat_id'] = $categoryId; }

            $sql = "
                SELECT p.sku, p.name AS product_name, COALESCE(c.name, 'General') AS category_name,
                       wh.name AS warehouse_name, bn.code AS bin_code, s.batch_no,
                       SUM(s.qty) AS total_qty, p.purchase_rate AS avg_unit_cost,
                       (SUM(s.qty) * p.purchase_rate) AS total_valuation
                FROM inventory_stocks s
                JOIN products p ON s.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN warehouses wh ON s.warehouse_id = wh.id
                JOIN bins bn ON s.bin_id = bn.id
                WHERE " . implode(" AND ", $where) . "
                GROUP BY s.warehouse_id, s.bin_id, s.product_id, s.batch_no
                ORDER BY p.name ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $r) {
                $rows[] = [
                    $r['sku'],
                    $r['product_name'],
                    $r['category_name'],
                    $r['warehouse_name'],
                    $r['bin_code'],
                    $r['batch_no'],
                    (int)$r['total_qty'],
                    number_format((float)$r['avg_unit_cost'], 2, '.', ''),
                    number_format((float)$r['total_valuation'], 2, '.', '')
                ];
            }
        }

        while (ob_get_level() > 0) ob_end_clean();

        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $reportType . '_report_' . date('Ymd_His') . '.xls');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo "<table border='1'><thead><tr>";
            foreach ($headers as $h) {
                echo "<th>" . e($h) . "</th>";
            }
            echo "</tr></thead><tbody>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . e((string)$cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            exit;
        }

        // CSV Export
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $reportType . '_report_' . date('Ymd_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * GST / Tax Filing Summary & Reconciliation Engine (GSTR-1 & GSTR-3B)
     */
    public function gst(): void {
        if (!has_permission('reports.view')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Tax Reports!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();

        // 1. Outward Supplies (GSTR-1 Sales Invoices)
        $outwardInvoices = $db->query("
            SELECT si.id, si.invoice_no, si.invoice_date, c.name AS customer_name, c.gstin AS customer_gstin,
                   si.subtotal AS taxable_value, si.tax_amount, si.total_amount, si.status
            FROM sales_invoices si
            JOIN customers c ON si.customer_id = c.id
            ORDER BY si.invoice_date DESC, si.id DESC
        ")->fetchAll();

        // 2. Inward Supplies (GSTR-2B / ITC Purchase Invoices)
        $inwardInvoices = $db->query("
            SELECT pi.id, pi.invoice_no, pi.invoice_date, s.name AS supplier_name, s.gstin AS supplier_gstin,
                   pi.subtotal AS taxable_value, pi.tax_amount, pi.total_amount, pi.status
            FROM purchase_invoices pi
            JOIN suppliers s ON pi.supplier_id = s.id
            ORDER BY pi.invoice_date DESC, pi.id DESC
        ")->fetchAll();

        // 3. HSN-wise Outward Tax Breakdown
        $hsnSummary = $db->query("
            SELECT COALESCE(p.hsn_code, '61091000') AS hsn_code,
                   c.name AS category_name,
                   SUM(soi.qty) AS total_qty,
                   SUM(soi.total_price) AS taxable_value,
                   p.tax_rate,
                   SUM(soi.total_price * (p.tax_rate / 100)) AS total_tax,
                   SUM(soi.total_price * (p.tax_rate / 200)) AS cgst,
                   SUM(soi.total_price * (p.tax_rate / 200)) AS sgst
            FROM sales_order_items soi
            JOIN products p ON soi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            GROUP BY p.hsn_code, p.tax_rate, c.name
            ORDER BY taxable_value DESC
        ")->fetchAll();

        // Compute Totals
        $totalOutputTaxable = 0;
        $totalOutputTax = 0;
        foreach ($outwardInvoices as $out) {
            $totalOutputTaxable += (float)$out['taxable_value'];
            $totalOutputTax += (float)$out['tax_amount'];
        }

        $totalInputTaxable = 0;
        $totalInputTax = 0;
        foreach ($inwardInvoices as $in) {
            $totalInputTaxable += (float)$in['taxable_value'];
            $totalInputTax += (float)$in['tax_amount'];
        }

        $netTaxPayable = max(0, $totalOutputTax - $totalInputTax);

        $this->render('reports/gst', [
            'title' => 'GST & Tax Compliance Dashboard (GSTR-1 & GSTR-3B)',
            'outwardInvoices' => $outwardInvoices,
            'inwardInvoices' => $inwardInvoices,
            'hsnSummary' => $hsnSummary,
            'totalOutputTaxable' => $totalOutputTaxable,
            'totalOutputTax' => $totalOutputTax,
            'totalInputTaxable' => $totalInputTaxable,
            'totalInputTax' => $totalInputTax,
            'netTaxPayable' => $netTaxPayable
        ]);
    }
}

