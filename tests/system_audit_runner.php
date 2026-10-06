<?php
/**
 * Enterprise ERP - 4-Mind Full System Audit & Test Runner
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/app/Config/Constants.php';
require_once dirname(__DIR__) . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Core\Database;
use App\Core\Session;

// Mock Admin Session
Session::set('user_id', 1);
Session::set('user', [
    'id' => 1,
    'name' => 'Super Administrator',
    'email' => 'admin@erp.com',
    'role_name' => 'super_admin',
    'role_display' => 'Super Administrator'
]);

$results = [
    'passed' => 0,
    'failed' => 0,
    'details' => []
];

function runTest($name, $callback) {
    global $results;
    try {
        ob_start();
        $res = $callback();
        $output = ob_get_clean();
        $results['passed']++;
        $results['details'][] = ['name' => $name, 'status' => 'PASS', 'note' => $res ?? 'OK'];
        echo "[PASS] $name\n";
    } catch (\Throwable $e) {
        if (ob_get_level() > 0) ob_end_clean();
        $results['failed']++;
        $results['details'][] = ['name' => $name, 'status' => 'FAIL', 'error' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()];
        echo "[FAIL] $name: " . $e->getMessage() . " on line " . $e->getLine() . "\n";
    }
}

echo "====================================================\n";
echo " ENTERPRISE ERP - 4-MIND AUDIT & TEST SUITE\n";
echo "====================================================\n\n";

// 1. Database Connection & Critical Schema Check
runTest("Database: Connection & Critical Tables", function() {
    $db = Database::getInstance();
    $tables = ['users', 'roles', 'products', 'categories', 'suppliers', 'purchase_requests', 'purchase_orders', 'inventory_stocks', 'sales_orders', 'customers', 'audit_logs'];
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    }
    return "All " . count($tables) . " core tables accessible";
});

// 2. Dashboard Controller Render
runTest("Controller: DashboardController::index", function() {
    $c = new \App\Controllers\DashboardController();
    $c->index();
    return "Dashboard rendered successfully";
});

// 3. Product Controller Views
runTest("Controller: ProductController::index", function() {
    $c = new \App\Controllers\ProductController();
    $c->index();
    return "Product master rendered";
});

runTest("Controller: ProductController::create", function() {
    $c = new \App\Controllers\ProductController();
    $c->create();
    return "Product create form rendered";
});

// 4. Attribute Controller
runTest("Controller: AttributeController::index", function() {
    $c = new \App\Controllers\AttributeController();
    $c->index();
    return "Attributes rendered";
});

// 5. Supplier Controller
runTest("Controller: SupplierController::index", function() {
    $c = new \App\Controllers\SupplierController();
    $c->index();
    return "Suppliers rendered";
});

// 6. Procurement Workflow Views
runTest("Controller: PurchaseController::requests", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->requests();
    return "PR list rendered";
});

runTest("Controller: PurchaseController::rfqs", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->rfqs();
    return "RFQs rendered";
});

runTest("Controller: PurchaseController::orders", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->orders();
    return "Purchase orders rendered";
});

runTest("Controller: PurchaseController::grns", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->grns();
    return "GRNs rendered";
});

runTest("Controller: PurchaseController::invoices", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->invoices();
    return "Invoices rendered";
});

runTest("Controller: PurchaseController::payments", function() {
    $c = new \App\Controllers\PurchaseController();
    $c->payments();
    return "Payments rendered";
});

// 7. Inventory & Warehouse Views
runTest("Controller: InventoryController::index", function() {
    $c = new \App\Controllers\InventoryController();
    $c->index();
    return "Inventory bin stocks rendered";
});

runTest("Controller: OrganizationController::index", function() {
    $c = new \App\Controllers\OrganizationController();
    $c->index();
    return "Warehouses & companies rendered";
});

// 8. Sales & Customer Views
runTest("Controller: SalesController::index", function() {
    $c = new \App\Controllers\SalesController();
    $c->index();
    return "Sales orders rendered";
});

runTest("Controller: CustomerController::index", function() {
    $c = new \App\Controllers\CustomerController();
    $c->index();
    return "Customer directory rendered";
});

// 9. Administration & Security Views
runTest("Controller: UserController::index", function() {
    $c = new \App\Controllers\UserController();
    $c->index();
    return "Users management rendered";
});

runTest("Controller: RoleController::index", function() {
    $c = new \App\Controllers\RoleController();
    $c->index();
    return "RBAC roles rendered";
});

runTest("Controller: AuditController::index", function() {
    $c = new \App\Controllers\AuditController();
    $c->index();
    return "Audit trail rendered";
});

runTest("Controller: SuperAdminController::index", function() {
    $c = new \App\Controllers\SuperAdminController();
    $c->index();
    return "Super Admin console rendered";
});

// 10. Reports & Analytics
runTest("Controller: ReportController::index", function() {
    $c = new \App\Controllers\ReportController();
    $c->index();
    return "Reports rendered";
});

runTest("Controller: AnalyticsController::index", function() {
    $c = new \App\Controllers\AnalyticsController();
    $c->index();
    return "Analytics telemetry rendered";
});

// 11. Master System Settings
runTest("Controller: SettingsController::index", function() {
    $c = new \App\Controllers\SettingsController();
    $c->index();
    return "Master Settings rendered";
});

// 12. Customer Portal Views
runTest("Controller: CustomerPortalController::dashboard", function() {
    $_SESSION['customer_user'] = [
        'id' => 1,
        'name' => 'Demo Customer',
        'code' => 'CUST-DEMO',
        'email' => 'customer@erp.com',
        'credit_limit' => 500000
    ];
    $c = new \App\Controllers\CustomerPortalController();
    $c->dashboard();
    return "Customer self-service portal rendered";
});

echo "\n====================================================\n";
echo " RESULTS: Total Passed: {$results['passed']} | Failed: {$results['failed']}\n";
echo "====================================================\n";

if ($results['failed'] > 0) {
    echo "\nFailed Tests Detail:\n";
    foreach ($results['details'] as $d) {
        if ($d['status'] === 'FAIL') {
            echo " - {$d['name']}: {$d['error']}\n";
        }
    }
    exit(1);
} else {
    echo "\n🎉 ALL CONTROLLERS & VIEW TEMPLATES PASSED 100% WITH ZERO ERRORS!\n";
    exit(0);
}
