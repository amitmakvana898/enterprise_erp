<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class DashboardController extends Controller {
    public function index(): void {
        $user = auth_user();
        $db = Database::getInstance();
        $roleName = $user['role_name'] ?? 'super_admin';

        if ($roleName === 'customer') {
            if (empty($_SESSION['customer_user'])) {
                $custStmt = $db->prepare("SELECT * FROM customers WHERE id = :cid OR email = :email LIMIT 1");
                $custStmt->execute(['cid' => $user['customer_id'] ?? 0, 'email' => $user['email']]);
                $cust = $custStmt->fetch();
                if ($cust) {
                    $_SESSION['customer_user'] = $cust;
                } else {
                    $_SESSION['customer_user'] = [
                        'id' => 1,
                        'name' => $user['name'],
                        'code' => 'CUST-DEMO',
                        'email' => $user['email'],
                        'credit_limit' => 100000.00
                    ];
                }
            }
            (new \App\Core\Response())->redirect(url('/customer-portal/dashboard'));
            return;
        }

        $range = $_GET['range'] ?? 'this_month';
        $startDate = null;
        $endDate = date('Y-m-d');

        switch ($range) {
            case 'today':
                $startDate = date('Y-m-d');
                break;
            case 'this_week':
                $startDate = date('Y-m-d', strtotime('monday this week'));
                break;
            case 'this_year':
                $startDate = date('Y-01-01');
                break;
            case 'all':
                $startDate = '2000-01-01';
                break;
            case 'this_month':
            default:
                $range = 'this_month';
                $startDate = date('Y-m-01');
                break;
        }

        // Filtered Dynamic Purchases & Sales based on selected Date Range
        $rangePurchase = (float) $db->query("SELECT SUM(total_amount) FROM purchase_orders WHERE DATE(created_at) BETWEEN '{$startDate}' AND '{$endDate}' AND status != 'cancelled'")->fetchColumn() ?: 0.00;
        $rangeSales = (float) $db->query("SELECT SUM(total_amount) FROM sales_orders WHERE DATE(order_date) BETWEEN '{$startDate}' AND '{$endDate}' AND status != 'cancelled'")->fetchColumn() ?: 0.00;

        // 1. Today's Purchase & Today's Sales
        $todaysPurchase = (float) $db->query("SELECT SUM(total_amount) FROM purchase_orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")->fetchColumn() ?: 0.00;
        $todaysSales = (float) $db->query("SELECT SUM(total_amount) FROM sales_orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'")->fetchColumn() ?: 0.00;

        // 2. Monthly Purchase & Monthly Sales
        $monthlyPurchase = (float) $db->query("SELECT SUM(total_amount) FROM purchase_orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) AND status != 'cancelled'")->fetchColumn() ?: 0.00;
        $monthlySales = (float) $db->query("SELECT SUM(total_amount) FROM sales_orders WHERE YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE()) AND status != 'cancelled'")->fetchColumn() ?: 0.00;

        // 3. Inventory Value, Low Stock & Out of Stock
        $inventoryValue = (float) $db->query("SELECT SUM(s.qty * s.valuation_rate) FROM inventory_stocks s")->fetchColumn() ?: 0.00;
        
        $lowStockCount = (int) $db->query("
            SELECT COUNT(*) FROM (
                SELECT s.product_id, SUM(s.qty) AS t_qty, p.reorder_level 
                FROM inventory_stocks s 
                JOIN products p ON s.product_id = p.id 
                GROUP BY s.product_id 
                HAVING t_qty <= p.reorder_level AND t_qty > 0
            ) AS t
        ")->fetchColumn();

        $outOfStockCount = (int) $db->query("
            SELECT COUNT(*) FROM products p 
            LEFT JOIN inventory_stocks s ON p.id = s.product_id 
            GROUP BY p.id 
            HAVING COALESCE(SUM(s.qty), 0) = 0
        ")->fetchColumn();

        // 4. Pending Approvals
        $pendingPRs = (int) $db->query("SELECT COUNT(*) FROM purchase_requests WHERE status = 'pending'")->fetchColumn();
        $pendingPOs = (int) $db->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'pending'")->fetchColumn();
        $pendingQC = (int) $db->query("SELECT COUNT(*) FROM goods_receipt_notes WHERE status = 'pending_qc'")->fetchColumn();
        $totalPendingApprovals = $pendingPRs + $pendingPOs + $pendingQC;

        // 5. Top Selling Products
        $topSellingProducts = $db->query("
            SELECT p.id, p.name, p.sku, c.name AS category_name, SUM(soi.qty) AS total_qty_sold, SUM(soi.total_price) AS total_revenue
            FROM sales_order_items soi
            JOIN products p ON soi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            GROUP BY p.id
            ORDER BY total_revenue DESC
            LIMIT 5
        ")->fetchAll();

        // 6. Bar Chart Data (Jan - Dec)
        $monthlyBarChartData = [
            'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'purchases' => array_fill(0, 12, 0.00),
            'sales' => array_fill(0, 12, 0.00)
        ];
        
        $mPurchases = $db->query("SELECT MONTH(created_at) AS m, SUM(total_amount) AS tot FROM purchase_orders WHERE YEAR(created_at) = YEAR(CURDATE()) AND status != 'cancelled' GROUP BY MONTH(created_at)")->fetchAll();
        foreach ($mPurchases as $mp) {
            $monthlyBarChartData['purchases'][(int)$mp['m'] - 1] = (float)$mp['tot'];
        }

        $mSales = $db->query("SELECT MONTH(order_date) AS m, SUM(total_amount) AS tot FROM sales_orders WHERE YEAR(order_date) = YEAR(CURDATE()) AND status != 'cancelled' GROUP BY MONTH(order_date)")->fetchAll();
        foreach ($mSales as $ms) {
            $monthlyBarChartData['sales'][(int)$ms['m'] - 1] = (float)$ms['tot'];
        }

        // 7. 30-Day Line Chart Trend Data
        $dailyTrendData = [
            'dates' => [],
            'sales' => [],
            'purchases' => []
        ];
        for ($i = 14; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dailyTrendData['dates'][] = date('d M', strtotime($d));

            $dS = (float)$db->query("SELECT SUM(total_amount) FROM sales_orders WHERE DATE(order_date) = '$d' AND status != 'cancelled'")->fetchColumn() ?: 0.00;
            $dP = (float)$db->query("SELECT SUM(total_amount) FROM purchase_orders WHERE DATE(created_at) = '$d' AND status != 'cancelled'")->fetchColumn() ?: 0.00;

            $dailyTrendData['sales'][] = $dS;
            $dailyTrendData['purchases'][] = $dP;
        }

        // 8. Pie Chart Data (Category Stock Valuation)
        $categoryPieChart = $db->query("
            SELECT c.name AS category_name, COALESCE(SUM(s.qty * s.valuation_rate), 0) AS total_val
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN inventory_stocks s ON p.id = s.product_id
            GROUP BY c.id
        ")->fetchAll();

        // Base Org Totals
        $totalCompanies = (int) $db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        $totalBranches = (int) $db->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        $totalWarehouses = (int) $db->query("SELECT COUNT(*) FROM warehouses")->fetchColumn();
        $totalProducts = (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $totalSuppliers = (int) $db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();

        // Audit Logs & Recent Records
        $recentAuditLogs = $db->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 5")->fetchAll();
        $recentPOs = $db->query("SELECT po.*, s.name AS supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON po.supplier_id = s.id ORDER BY po.id DESC LIMIT 5")->fetchAll();
        $recentSales = $db->query("SELECT so.*, c.name AS customer_name FROM sales_orders so JOIN customers c ON so.customer_id = c.id ORDER BY so.id DESC LIMIT 5")->fetchAll();

        $this->render('dashboard/index', [
            'title' => 'Executive Dashboard - Enterprise ERP',
            'user' => $user,
            'role_name' => $roleName,
            'selected_range' => $range,
            'range_sales' => $rangeSales,
            'range_purchase' => $rangePurchase,
            'kpis' => [
                'todays_purchase' => $todaysPurchase,
                'todays_sales' => $todaysSales,
                'monthly_purchase' => $monthlyPurchase,
                'monthly_sales' => $monthlySales,
                'range_sales' => $rangeSales,
                'range_purchase' => $rangePurchase,
                'inventory_value' => $inventoryValue,
                'total_stock_value' => $inventoryValue,
                'pending_approvals' => $totalPendingApprovals,
                'low_stock' => $lowStockCount,
                'low_stock_count' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
                'total_products' => $totalProducts,
                'total_suppliers' => $totalSuppliers,
                'total_companies' => $totalCompanies,
                'total_branches' => $totalBranches,
                'total_warehouses' => $totalWarehouses,
            ],
            'top_selling_products' => $topSellingProducts,
            'bar_chart_data' => $monthlyBarChartData,
            'line_chart_data' => $dailyTrendData,
            'pie_chart_data' => $categoryPieChart,
            'recent_audits' => $recentAuditLogs,
            'recent_pos' => $recentPOs,
            'recent_sales' => $recentSales
        ]);
    }
}
