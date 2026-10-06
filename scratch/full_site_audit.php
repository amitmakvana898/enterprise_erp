<?php
/**
 * Enterprise ERP - Comprehensive A-to-Z Site Audit Script
 * Tests every page, every process, every form for proper functionality
 */

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=enterprise_erp;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  ENTERPRISE ERP - COMPREHENSIVE A-TO-Z SITE AUDIT          ║\n";
echo "║  Date: " . date('Y-m-d H:i:s') . "                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$results = [];
$totalPass = 0;
$totalFail = 0;
$totalWarn = 0;

function test($category, $testName, $status, $detail = '') {
    global $results, $totalPass, $totalFail, $totalWarn;
    $icon = $status === 'PASS' ? '✅' : ($status === 'FAIL' ? '❌' : '⚠️');
    $results[] = ['category' => $category, 'test' => $testName, 'status' => $status, 'detail' => $detail];
    if ($status === 'PASS') $totalPass++;
    elseif ($status === 'FAIL') $totalFail++;
    else $totalWarn++;
    echo "  $icon [$status] $testName" . ($detail ? " — $detail" : "") . "\n";
}

// ═══════════════════════════════════════════════════════════════
// 1. DATABASE INTEGRITY AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 1. DATABASE INTEGRITY AUDIT ═══\n";

// Check all expected tables exist
$expectedTables = [
    'users', 'roles', 'permissions', 'role_permissions',
    'companies', 'branches', 'warehouses', 'racks', 'bins',
    'categories', 'brands', 'units', 'products', 'product_images',
    'attribute_groups', 'attributes', 'product_attributes',
    'suppliers', 'customers',
    'purchase_requests', 'purchase_request_items',
    'rfqs', 'rfq_items', 'rfq_responses',
    'purchase_orders', 'purchase_order_items',
    'goods_received_notes', 'grn_items',
    'purchase_invoices', 'purchase_invoice_items',
    'purchase_payments',
    'purchase_returns', 'purchase_return_items',
    'inventory_stocks', 'stock_transactions',
    'stock_transfers', 'stock_transfer_items',
    'sales_orders', 'sales_order_items',
    'sales_quotations', 'sales_quotation_items',
    'sales_delivery_challans',
    'sales_invoices', 'sales_invoice_items',
    'sales_payments',
    'sales_returns',
    'audit_logs'
];

$stmt = $pdo->query("SHOW TABLES");
$existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($expectedTables as $table) {
    if (in_array($table, $existingTables)) {
        test('DATABASE', "Table '$table' exists", 'PASS');
    } else {
        test('DATABASE', "Table '$table' exists", 'FAIL', 'Table missing from database');
    }
}

// Check foreign key integrity on critical tables
echo "\n--- Foreign Key & Data Integrity ---\n";

// Users with valid roles
$stmt = $pdo->query("SELECT COUNT(*) as c FROM users WHERE role_id NOT IN (SELECT id FROM roles)");
$orphanUsers = $stmt->fetch()['c'];
test('DATABASE', 'Users all have valid role_id FK', $orphanUsers == 0 ? 'PASS' : 'FAIL', "$orphanUsers orphan users");

// Sales orders with valid customers
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM sales_orders WHERE customer_id NOT IN (SELECT id FROM customers)");
    $orphanSO = $stmt->fetch()['c'];
    test('DATABASE', 'Sales orders have valid customer_id FK', $orphanSO == 0 ? 'PASS' : 'WARN', "$orphanSO orphan sales orders");
} catch (Exception $e) {
    test('DATABASE', 'Sales orders FK check', 'WARN', $e->getMessage());
}

// Purchase orders with valid suppliers
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM purchase_orders WHERE supplier_id NOT IN (SELECT id FROM suppliers)");
    $orphanPO = $stmt->fetch()['c'];
    test('DATABASE', 'Purchase orders have valid supplier_id FK', $orphanPO == 0 ? 'PASS' : 'WARN', "$orphanPO orphan POs");
} catch (Exception $e) {
    test('DATABASE', 'Purchase orders FK check', 'WARN', $e->getMessage());
}

// Inventory stocks have valid product and warehouse FKs
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM inventory_stocks WHERE product_id NOT IN (SELECT id FROM products)");
    $orphanStock = $stmt->fetch()['c'];
    test('DATABASE', 'Inventory stocks have valid product_id FK', $orphanStock == 0 ? 'PASS' : 'FAIL', "$orphanStock orphan stock rows");
} catch (Exception $e) {
    test('DATABASE', 'Inventory stocks product FK check', 'WARN', $e->getMessage());
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM inventory_stocks WHERE warehouse_id NOT IN (SELECT id FROM warehouses)");
    $orphanWh = $stmt->fetch()['c'];
    test('DATABASE', 'Inventory stocks have valid warehouse_id FK', $orphanWh == 0 ? 'PASS' : 'FAIL', "$orphanWh orphan warehouse FK");
} catch (Exception $e) {
    test('DATABASE', 'Inventory stocks warehouse FK check', 'WARN', $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════
// 2. DATA POPULATION AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 2. DATA POPULATION AUDIT ═══\n";

$dataChecks = [
    ['users', 'Users registered'],
    ['roles', 'Roles configured'],
    ['permissions', 'Permissions defined'],
    ['companies', 'Companies set up'],
    ['branches', 'Branches configured'],
    ['warehouses', 'Warehouses configured'],
    ['categories', 'Product categories'],
    ['brands', 'Brands registered'],
    ['units', 'Units of measure'],
    ['products', 'Products in catalog'],
    ['suppliers', 'Suppliers registered'],
    ['customers', 'Customers registered'],
];

foreach ($dataChecks as [$table, $label]) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as c FROM $table");
        $count = $stmt->fetch()['c'];
        $status = $count > 0 ? 'PASS' : 'WARN';
        test('DATA', "$label ($table)", $status, "$count records");
    } catch (Exception $e) {
        test('DATA', "$label ($table)", 'FAIL', $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════
// 3. CONTROLLER METHOD EXISTENCE AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 3. CONTROLLER METHOD AUDIT ═══\n";

$controllerChecks = [
    ['SalesController', ['index', 'create', 'store', 'createQuotation', 'storeQuotation', 'convertQuotationToOrder', 'createReturn', 'storeReturn']],
    ['PurchaseController', ['requests', 'createRequest', 'storeRequest', 'approveRequest', 'rejectRequest', 'rfqs', 'createRfq', 'storeRfq', 'compareQuotations', 'orders', 'createOrder', 'storeOrder', 'grns', 'createGrn', 'storeGrn', 'processQc', 'invoices', 'createInvoice', 'storeInvoice', 'showInvoice', 'payments', 'createPayment', 'storePayment', 'returns', 'createReturn', 'storeReturn', 'gatePass', 'returnInvoice']],
    ['InventoryController', ['index', 'openingStock', 'storeOpeningStock', 'stockIssue', 'storeStockIssue', 'stockReceive', 'storeStockReceive', 'stockDamage', 'storeStockDamage', 'physicalVerification', 'storePhysicalVerification', 'batches', 'expiryDashboard', 'ledger', 'adjust', 'storeAdjustment', 'valuation']],
    ['ProductController', ['index', 'create', 'store', 'edit', 'update', 'show', 'storeCategory', 'deleteCategory', 'storeBrand', 'storeUnit', 'seedCatalog', 'deleteAll', 'delete', 'attributesJson']],
    ['CustomerController', ['index', 'create', 'store']],
    ['SupplierController', ['index', 'create', 'store']],
    ['TransferController', ['index', 'create', 'store', 'receive']],
    ['OrganizationController', ['index', 'storeCompany', 'storeBranch', 'storeWarehouse', 'storeRack', 'storeBin']],
    ['UserController', ['index', 'create', 'store', 'toggleStatus', 'delete']],
    ['RoleController', ['index', 'togglePermission', 'grantAllPermissions', 'revokeAllPermissions']],
    ['DashboardController', ['index']],
    ['ReportController', ['index', 'exportCsv', 'exportExcel', 'exportPdf']],
    ['AuditController', ['index']],
    ['BarcodeController', ['index', 'generate']],
    ['AuthController', ['login', 'handleLogin', 'register', 'handleRegister', 'quickLogin', 'logout', 'forgotPassword', 'handleForgotPassword', 'changePassword', 'handleChangePassword']],
    ['ProfileController', ['index', 'updateProfile', 'updatePassword']],
    ['SuperAdminController', ['index', 'backup', 'clearCache']],
    ['SearchController', ['index']],
];

foreach ($controllerChecks as [$controllerName, $methods]) {
    $className = "App\\Controllers\\$controllerName";
    $filePath = dirname(__DIR__) . "/app/Controllers/$controllerName.php";
    
    if (!file_exists($filePath)) {
        test('CONTROLLER', "$controllerName file exists", 'FAIL', "File not found: $filePath");
        continue;
    }
    
    $contents = file_get_contents($filePath);
    
    foreach ($methods as $method) {
        $pattern = '/public\s+function\s+' . preg_quote($method, '/') . '\s*\(/';
        if (preg_match($pattern, $contents)) {
            test('CONTROLLER', "$controllerName::$method() exists", 'PASS');
        } else {
            test('CONTROLLER', "$controllerName::$method() exists", 'FAIL', "Method not found in controller");
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// 4. VIEW FILE EXISTENCE AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 4. VIEW FILE AUDIT ═══\n";

$viewChecks = [
    // Layouts
    'layouts/main.php', 'layouts/auth.php', 'layouts/landing.php', 'layouts/print.php',
    // Auth
    'auth/login.php', 'auth/register.php', 'auth/forgot.php', 'auth/change_password.php',
    // Dashboard
    'dashboard/index.php',
    // Products
    'products/index.php', 'products/create.php', 'products/edit.php', 'products/show.php',
    // Attributes
    'attributes/index.php',
    // Suppliers
    'suppliers/index.php',
    // Customers
    'customers/index.php',
    // Sales
    'sales/index.php', 'sales/create.php', 'sales/create_quotation.php', 'sales/create_return.php',
    // Procurement
    'procurement/requests.php', 'procurement/create_request.php',
    'procurement/rfqs.php', 'procurement/create_rfq.php', 'procurement/compare_quotations.php',
    'procurement/orders.php', 'procurement/create_order.php',
    'procurement/grns.php', 'procurement/create_grn.php',
    'procurement/invoices.php', 'procurement/create_invoice.php', 'procurement/show_invoice.php',
    'procurement/payments.php', 'procurement/create_payment.php',
    'procurement/returns.php', 'procurement/create_return.php', 'procurement/gate_pass.php', 'procurement/return_invoice.php',
    // Inventory
    'inventory/index.php', 'inventory/opening.php', 'inventory/issue.php', 'inventory/receive.php',
    'inventory/damage.php', 'inventory/physical.php', 'inventory/batches.php', 'inventory/expiry.php',
    'inventory/ledger.php', 'inventory/adjust.php', 'inventory/valuation.php',
    // Transfers
    'transfers/index.php',
    // Organization
    'organization/index.php',
    // Users & Roles
    'users/index.php',
    'roles/index.php',
    // Reports & Audit
    'reports/index.php',
    'audit/index.php',
    // Barcode
    'barcode/index.php',
    // Profile
    'profile/index.php',
    // Admin
    'admin/index.php',
    // Home
    'home/index.php', 'home/about.php',
    // Errors
    'errors/404.php',
];

$viewBasePath = dirname(__DIR__) . '/views/';
foreach ($viewChecks as $viewFile) {
    $fullPath = $viewBasePath . $viewFile;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        test('VIEW', "View '$viewFile' exists", 'PASS', number_format($size) . " bytes");
    } else {
        test('VIEW', "View '$viewFile' exists", 'FAIL', "File missing");
    }
}

// ═══════════════════════════════════════════════════════════════
// 5. PHP SYNTAX VALIDATION OF ALL VIEW FILES
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 5. PHP SYNTAX VALIDATION ═══\n";

$allPhpFiles = array_merge(
    glob($viewBasePath . '*/*.php'),
    glob($viewBasePath . '*/*/*.php'),
    glob(dirname(__DIR__) . '/app/Controllers/*.php'),
    glob(dirname(__DIR__) . '/app/Models/*.php'),
    glob(dirname(__DIR__) . '/app/Core/*.php')
);

foreach ($allPhpFiles as $phpFile) {
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($phpFile) . " 2>&1", $output, $returnVar);
    $shortName = str_replace(dirname(__DIR__) . '\\', '', $phpFile);
    $shortName = str_replace(dirname(__DIR__) . '/', '', $shortName);
    if ($returnVar === 0) {
        test('SYNTAX', "Syntax check: $shortName", 'PASS');
    } else {
        test('SYNTAX', "Syntax check: $shortName", 'FAIL', implode(' ', $output));
    }
}

// ═══════════════════════════════════════════════════════════════
// 6. SALES WORKFLOW INTEGRITY AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 6. SALES WORKFLOW AUDIT ═══\n";

// Check Sales Order table structure
try {
    $stmt = $pdo->query("DESCRIBE sales_orders");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredCols = ['id', 'order_no', 'customer_id', 'order_date', 'status', 'total_amount'];
    foreach ($requiredCols as $col) {
        test('SALES', "sales_orders has column '$col'", in_array($col, $cols) ? 'PASS' : 'FAIL');
    }
} catch (Exception $e) {
    test('SALES', 'sales_orders table structure', 'FAIL', $e->getMessage());
}

// Check Sales Quotation table structure
try {
    $stmt = $pdo->query("DESCRIBE sales_quotations");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredCols = ['id', 'quotation_no', 'customer_id', 'status', 'total_amount'];
    foreach ($requiredCols as $col) {
        test('SALES', "sales_quotations has column '$col'", in_array($col, $cols) ? 'PASS' : 'FAIL');
    }
} catch (Exception $e) {
    test('SALES', 'sales_quotations table structure', 'FAIL', $e->getMessage());
}

// Check Delivery Challans
try {
    $stmt = $pdo->query("DESCRIBE sales_delivery_challans");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    test('SALES', "sales_delivery_challans table exists", 'PASS', count($cols) . " columns");
} catch (Exception $e) {
    test('SALES', 'sales_delivery_challans table', 'FAIL', $e->getMessage());
}

// Check Sales Invoices
try {
    $stmt = $pdo->query("DESCRIBE sales_invoices");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    test('SALES', "sales_invoices table exists", 'PASS', count($cols) . " columns");
} catch (Exception $e) {
    test('SALES', 'sales_invoices table', 'FAIL', $e->getMessage());
}

// Check Sales Payments
try {
    $stmt = $pdo->query("DESCRIBE sales_payments");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    test('SALES', "sales_payments table exists", 'PASS', count($cols) . " columns");
} catch (Exception $e) {
    test('SALES', 'sales_payments table', 'FAIL', $e->getMessage());
}

// Check Sales Returns
try {
    $stmt = $pdo->query("DESCRIBE sales_returns");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    test('SALES', "sales_returns table exists", 'PASS', count($cols) . " columns");
} catch (Exception $e) {
    test('SALES', 'sales_returns table', 'FAIL', $e->getMessage());
}

// Verify existing sales data
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM sales_quotations");
    $count = $stmt->fetch()['c'];
    test('SALES', 'Sales quotations data', $count > 0 ? 'PASS' : 'WARN', "$count records");
} catch (Exception $e) { test('SALES', 'Sales quotations data', 'WARN', $e->getMessage()); }

try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM sales_orders");
    $count = $stmt->fetch()['c'];
    test('SALES', 'Sales orders data', $count > 0 ? 'PASS' : 'WARN', "$count records");
} catch (Exception $e) { test('SALES', 'Sales orders data', 'WARN', $e->getMessage()); }

// ═══════════════════════════════════════════════════════════════
// 7. PROCUREMENT WORKFLOW INTEGRITY AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 7. PROCUREMENT WORKFLOW AUDIT ═══\n";

$procTables = [
    'purchase_requests' => ['id', 'request_no', 'status'],
    'purchase_request_items' => ['id', 'request_id', 'product_id'],
    'rfqs' => ['id', 'rfq_no'],
    'rfq_items' => ['id', 'rfq_id', 'product_id'],
    'purchase_orders' => ['id', 'po_no', 'supplier_id', 'status', 'total_amount'],
    'purchase_order_items' => ['id', 'po_id', 'product_id'],
    'goods_received_notes' => ['id', 'grn_no', 'po_id'],
    'grn_items' => ['id', 'grn_id', 'product_id'],
    'purchase_invoices' => ['id', 'invoice_no'],
    'purchase_payments' => ['id', 'payment_no'],
    'purchase_returns' => ['id', 'return_no'],
];

foreach ($procTables as $table => $requiredCols) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($requiredCols as $col) {
            test('PROCUREMENT', "$table has column '$col'", in_array($col, $cols) ? 'PASS' : 'FAIL');
        }
        
        // Count records
        $stmt2 = $pdo->query("SELECT COUNT(*) as c FROM $table");
        $count = $stmt2->fetch()['c'];
        test('PROCUREMENT', "$table has data", $count > 0 ? 'PASS' : 'WARN', "$count records");
    } catch (Exception $e) {
        test('PROCUREMENT', "$table exists", 'FAIL', $e->getMessage());
    }
}

// ═══════════════════════════════════════════════════════════════
// 8. INVENTORY INTEGRITY AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 8. INVENTORY INTEGRITY AUDIT ═══\n";

// Check for negative stock
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM inventory_stocks WHERE qty < 0");
    $negStock = $stmt->fetch()['c'];
    test('INVENTORY', 'No negative stock balances', $negStock == 0 ? 'PASS' : 'FAIL', "$negStock rows with negative qty");
} catch (Exception $e) {
    test('INVENTORY', 'Negative stock check', 'WARN', $e->getMessage());
}

// Check stock transaction log
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM stock_transactions");
    $txCount = $stmt->fetch()['c'];
    test('INVENTORY', 'Stock transactions logged', $txCount > 0 ? 'PASS' : 'WARN', "$txCount transaction records");
} catch (Exception $e) {
    test('INVENTORY', 'Stock transactions', 'WARN', $e->getMessage());
}

// Total stock value
try {
    $stmt = $pdo->query("SELECT SUM(s.qty) as total_qty FROM inventory_stocks s");
    $totalQty = $stmt->fetch()['total_qty'] ?? 0;
    test('INVENTORY', 'Total inventory quantity', $totalQty > 0 ? 'PASS' : 'WARN', number_format($totalQty) . " total units");
} catch (Exception $e) {
    test('INVENTORY', 'Total inventory', 'WARN', $e->getMessage());
}

// Stock transfers integrity
try {
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM stock_transfers");
    $transfers = $stmt->fetch()['c'];
    test('INVENTORY', 'Stock transfers logged', $transfers > 0 ? 'PASS' : 'WARN', "$transfers records");
} catch (Exception $e) {
    test('INVENTORY', 'Stock transfers', 'WARN', $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════
// 9. HTTP PAGE ACCESSIBILITY AUDIT (via cURL)
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 9. HTTP PAGE ACCESSIBILITY AUDIT ═══\n";

$baseUrl = 'http://localhost/enterprise_erp';

// Public pages (no auth required)
$publicPages = [
    '/' => 'Home Page',
    '/about' => 'About Page',
    '/login' => 'Login Page',
    '/register' => 'Register Page',
    '/forgot-password' => 'Forgot Password Page',
    '/quick-login' => 'Quick Login Page',
];

foreach ($publicPages as $path => $label) {
    $ch = curl_init($baseUrl . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        test('HTTP', "$label ($path)", 'PASS', "HTTP $httpCode, " . number_format(strlen($body)) . " bytes");
    } elseif ($httpCode == 302) {
        test('HTTP', "$label ($path)", 'PASS', "HTTP $httpCode (redirect)");
    } else {
        test('HTTP', "$label ($path)", 'FAIL', "HTTP $httpCode");
    }
}

// Auth-required pages (simulate login via cookie)
// First, login and get session cookie
$loginUrl = $baseUrl . '/login';
$cookieFile = tempnam(sys_get_temp_dir(), 'erp_audit_cookie_');

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$loginPage = curl_exec($ch);
curl_close($ch);

// Extract CSRF token from login page
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage, $csrfMatch);
$csrfToken = $csrfMatch[1] ?? '';

if ($csrfToken) {
    $ch = curl_init($loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => 'admin@erp.com',
        'password' => 'admin123',
        'csrf_token' => $csrfToken,
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $loginResponse = curl_exec($ch);
    $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    $loggedIn = ($loginCode == 200 && strpos($finalUrl, 'dashboard') !== false);
    test('AUTH', 'Admin login via HTTP', $loggedIn ? 'PASS' : 'WARN', "HTTP $loginCode → $finalUrl");
    
    if ($loggedIn) {
        // Test ALL authenticated pages
        $authPages = [
            '/dashboard' => 'Dashboard',
            '/products' => 'Products List',
            '/products/create' => 'Create Product',
            '/attributes' => 'Attributes',
            '/suppliers' => 'Suppliers List',
            '/suppliers/create' => 'Create Supplier',
            '/customers' => 'Customers List',
            '/customers/create' => 'Create Customer',
            '/sales' => 'Sales Hub',
            '/sales/create' => 'Create Sales Order',
            '/sales/quotations/create' => 'Create Sales Quotation',
            '/sales/returns/create' => 'Create Sales Return',
            '/procurement/requests' => 'Purchase Requests',
            '/procurement/requests/create' => 'Create Purchase Request',
            '/procurement/rfqs' => 'RFQs',
            '/procurement/rfqs/create' => 'Create RFQ',
            '/procurement/orders' => 'Purchase Orders',
            '/procurement/orders/create' => 'Create PO',
            '/procurement/grns' => 'GRNs',
            '/procurement/grns/create' => 'Create GRN',
            '/procurement/invoices' => 'Purchase Invoices',
            '/procurement/invoices/create' => 'Create Purchase Invoice',
            '/procurement/payments' => 'Purchase Payments',
            '/procurement/payments/create' => 'Create Purchase Payment',
            '/procurement/returns' => 'Purchase Returns',
            '/procurement/returns/create' => 'Create Purchase Return',
            '/inventory' => 'Inventory Dashboard',
            '/inventory/opening' => 'Opening Stock',
            '/inventory/issue' => 'Stock Issue',
            '/inventory/receive' => 'Stock Receive',
            '/inventory/damage' => 'Stock Damage',
            '/inventory/physical-verification' => 'Physical Verification',
            '/inventory/batches' => 'Batch Tracking',
            '/inventory/expiry' => 'Expiry Dashboard',
            '/inventory/ledger' => 'Inventory Ledger',
            '/inventory/adjust' => 'Stock Adjustment',
            '/inventory/valuation' => 'Inventory Valuation',
            '/transfers' => 'Stock Transfers',
            '/transfers/create' => 'Create Transfer',
            '/organization' => 'Organization',
            '/users' => 'Users Management',
            '/users/create' => 'Create User',
            '/roles' => 'Roles & Permissions',
            '/barcode' => 'Barcode Generator',
            '/reports' => 'Reports',
            '/audit' => 'Audit Trail',
            '/super-admin' => 'Super Admin Panel',
            '/profile' => 'Profile',
            '/search' => 'Search',
            '/change-password' => 'Change Password',
        ];
        
        foreach ($authPages as $path => $label) {
            $ch = curl_init($baseUrl . $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            
            // Check for PHP errors in response
            $hasPhpError = (
                stripos($body, 'Fatal error') !== false ||
                stripos($body, 'Parse error') !== false ||
                stripos($body, 'Uncaught Exception') !== false ||
                stripos($body, 'Undefined variable') !== false ||
                stripos($body, 'Call to undefined') !== false
            );
            
            $hasWarning = (
                stripos($body, 'Warning:') !== false ||
                stripos($body, 'Notice:') !== false ||
                stripos($body, 'Deprecated:') !== false
            );
            
            if ($httpCode == 200 && !$hasPhpError) {
                if ($hasWarning) {
                    // Extract warning
                    preg_match('/(Warning|Notice|Deprecated):.*?(?=<|$)/s', $body, $warnMatch);
                    $warnText = isset($warnMatch[0]) ? substr(trim($warnMatch[0]), 0, 120) : 'PHP warnings detected';
                    test('HTTP', "$label ($path)", 'WARN', "HTTP 200 but has PHP warnings: $warnText");
                } else {
                    test('HTTP', "$label ($path)", 'PASS', "HTTP $httpCode, " . number_format(strlen($body)) . " bytes");
                }
            } elseif ($httpCode == 200 && $hasPhpError) {
                preg_match('/(Fatal error|Parse error|Uncaught Exception|Call to undefined|Undefined variable):.*?(?=<|$)/s', $body, $errorMatch);
                $errorText = isset($errorMatch[0]) ? substr(trim($errorMatch[0]), 0, 200) : 'PHP error in page';
                test('HTTP', "$label ($path)", 'FAIL', "HTTP 200 but PHP ERROR: $errorText");
            } elseif ($httpCode == 500) {
                test('HTTP', "$label ($path)", 'FAIL', "HTTP 500 Internal Server Error");
            } else {
                // If redirected to login, auth may have failed
                if (strpos($finalUrl, 'login') !== false) {
                    test('HTTP', "$label ($path)", 'WARN', "Redirected to login (session expired?)");
                } else {
                    test('HTTP', "$label ($path)", 'WARN', "HTTP $httpCode");
                }
            }
        }
    }
} else {
    test('AUTH', 'CSRF token extraction from login', 'FAIL', 'Could not extract csrf_token');
}

// Cleanup cookie file
if (file_exists($cookieFile)) unlink($cookieFile);

// ═══════════════════════════════════════════════════════════════
// 10. FORM VALIDATION AUDIT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ 10. FORM & VALIDATION AUDIT ═══\n";

// Check sales create form has required validation attributes
$salesCreateView = file_get_contents(dirname(__DIR__) . '/views/sales/create.php');
test('VALIDATION', 'Sales create form has customer_id field', strpos($salesCreateView, 'customer_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'Sales create form has product selection', strpos($salesCreateView, 'product_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'Sales create form has CSRF token', strpos($salesCreateView, 'csrf_token') !== false ? 'PASS' : 'FAIL');

// Check quotation form validation
$quotCreateView = file_get_contents(dirname(__DIR__) . '/views/sales/create_quotation.php');
test('VALIDATION', 'Quotation form has customer_id field', strpos($quotCreateView, 'customer_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'Quotation form has price input', (strpos($quotCreateView, 'unit_price') !== false || strpos($quotCreateView, 'price') !== false) ? 'PASS' : 'FAIL');
test('VALIDATION', 'Quotation form has CSRF token', strpos($quotCreateView, 'csrf_token') !== false ? 'PASS' : 'FAIL');

// Check sales return form
$returnView = file_get_contents(dirname(__DIR__) . '/views/sales/create_return.php');
test('VALIDATION', 'Return form has order selection', strpos($returnView, 'order_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'Return form has CSRF token', strpos($returnView, 'csrf_token') !== false ? 'PASS' : 'FAIL');

// Check procurement forms
$prCreateView = file_get_contents(dirname(__DIR__) . '/views/procurement/create_request.php');
test('VALIDATION', 'Purchase request form has product selection', strpos($prCreateView, 'product_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'Purchase request form has CSRF token', strpos($prCreateView, 'csrf_token') !== false ? 'PASS' : 'FAIL');

$poCreateView = file_get_contents(dirname(__DIR__) . '/views/procurement/create_order.php');
test('VALIDATION', 'PO create form has supplier selection', strpos($poCreateView, 'supplier_id') !== false ? 'PASS' : 'FAIL');
test('VALIDATION', 'PO create form has CSRF token', strpos($poCreateView, 'csrf_token') !== false ? 'PASS' : 'FAIL');

// ═══════════════════════════════════════════════════════════════
// FINAL SUMMARY
// ═══════════════════════════════════════════════════════════════
echo "\n\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    AUDIT SUMMARY                           ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
$total = $totalPass + $totalFail + $totalWarn;
echo "║  Total Tests:    " . str_pad($total, 6) . "                                   ║\n";
echo "║  ✅ PASSED:      " . str_pad($totalPass, 6) . " (" . round(($totalPass / $total) * 100) . "%)                              ║\n";
echo "║  ❌ FAILED:      " . str_pad($totalFail, 6) . " (" . round(($totalFail / $total) * 100) . "%)                              ║\n";
echo "║  ⚠️  WARNINGS:    " . str_pad($totalWarn, 6) . " (" . round(($totalWarn / $total) * 100) . "%)                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Print failed tests summary
if ($totalFail > 0) {
    echo "═══ FAILED TESTS (NEED FIXING) ═══\n";
    foreach ($results as $r) {
        if ($r['status'] === 'FAIL') {
            echo "  ❌ [{$r['category']}] {$r['test']}" . ($r['detail'] ? " — {$r['detail']}" : "") . "\n";
        }
    }
    echo "\n";
}

if ($totalWarn > 0) {
    echo "═══ WARNINGS (SHOULD CHECK) ═══\n";
    foreach ($results as $r) {
        if ($r['status'] === 'WARN') {
            echo "  ⚠️ [{$r['category']}] {$r['test']}" . ($r['detail'] ? " — {$r['detail']}" : "") . "\n";
        }
    }
    echo "\n";
}

echo "Audit completed at " . date('Y-m-d H:i:s') . "\n";
