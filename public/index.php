<?php

// Enterprise ERP Front Controller

require_once dirname(__DIR__) . '/app/Config/Constants.php';
require_once dirname(__DIR__) . '/app/Helpers/Functions.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Application;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\RoleController;
use App\Controllers\OrganizationController;
use App\Controllers\ProductController;
use App\Controllers\AttributeController;
use App\Controllers\SupplierController;
use App\Controllers\PurchaseController;
use App\Controllers\InventoryController;
use App\Controllers\TransferController;
use App\Controllers\SalesController;
use App\Controllers\CustomerController;
use App\Controllers\BarcodeController;
use App\Controllers\ReportController;
use App\Controllers\AnalyticsController;
use App\Controllers\NotificationController;
use App\Controllers\EmailController;
use App\Controllers\AuditController;
use App\Controllers\SearchController;
use App\Controllers\SuperAdminController;
use App\Controllers\SettingsController;
use App\Controllers\ProfileController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\ProductApiController;
use App\Controllers\Api\SupplierApiController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\ApiAuthMiddleware;

$app = new Application();

// -------------------------------------------------------------
// WEB ROUTES DEFINITION
// -------------------------------------------------------------

// Home & Auth
$app->router->get('/', [HomeController::class, 'index']);
$app->router->get('/about', [HomeController::class, 'about']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'handleLogin'], [CsrfMiddleware::class]);
$app->router->get('/register', function() {
    (new \App\Core\Response())->redirect(url('/login'));
});
$app->router->post('/register', function() {
    (new \App\Core\Response())->redirect(url('/login'));
});
$app->router->get('/quick-login', [AuthController::class, 'quickLogin']);
$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$app->router->post('/forgot-password', [AuthController::class, 'handleForgotPassword']);
$app->router->get('/change-password', [AuthController::class, 'changePassword'], [AuthMiddleware::class]);
$app->router->post('/change-password/store', [AuthController::class, 'handleChangePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Dashboard & Profile
$app->router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/profile', [ProfileController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/profile/update', [ProfileController::class, 'updateProfile'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/profile/change-password', [ProfileController::class, 'updatePassword'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Product Master & Dynamic Attributes
$app->router->get('/products', [ProductController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/products/export', [ProductController::class, 'exportCsv'], [AuthMiddleware::class]);
$app->router->get('/products/import/template', [ProductController::class, 'downloadTemplate'], [AuthMiddleware::class]);
$app->router->post('/products/import', [ProductController::class, 'importCsv'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/products/create', [ProductController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/products/store', [ProductController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/products/edit/{id}', [ProductController::class, 'edit'], [AuthMiddleware::class]);
$app->router->post('/products/update/{id}', [ProductController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/categories/store', [ProductController::class, 'storeCategory'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/categories/delete/{id}', [ProductController::class, 'deleteCategory'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/brands/store', [ProductController::class, 'storeBrand'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/units/store', [ProductController::class, 'storeUnit'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/seed-1000', [ProductController::class, 'seedCatalog'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/delete-all', [ProductController::class, 'deleteAll'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/products/delete/{id}', [ProductController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/products/attributes/{id}', [ProductController::class, 'attributesJson'], [AuthMiddleware::class]);
$app->router->get('/products/{id}', [ProductController::class, 'show'], [AuthMiddleware::class]);

$app->router->get('/attributes', [AttributeController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/attributes/store', [AttributeController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/attributes/update/{id}', [AttributeController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/attributes/groups/store', [AttributeController::class, 'storeGroup'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/attributes/groups/delete/{id}', [AttributeController::class, 'deleteGroup'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/attributes/delete/{id}', [AttributeController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/attributes/store-value', [AttributeController::class, 'storeValue'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Suppliers
$app->router->get('/suppliers', [SupplierController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/suppliers/statement/{id}', [SupplierController::class, 'statement'], [AuthMiddleware::class]);
$app->router->get('/suppliers/create', [SupplierController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/suppliers/store', [SupplierController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Procurement Workflow
$app->router->get('/procurement/requests', [PurchaseController::class, 'requests'], [AuthMiddleware::class]);
$app->router->get('/procurement/requests/create', [PurchaseController::class, 'createRequest'], [AuthMiddleware::class]);
$app->router->post('/procurement/requests/store', [PurchaseController::class, 'storeRequest'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/procurement/requests/approve/{id}', [PurchaseController::class, 'approveRequest'], [AuthMiddleware::class]);
$app->router->get('/procurement/requests/reject/{id}', [PurchaseController::class, 'rejectRequest'], [AuthMiddleware::class]);
$app->router->get('/procurement/requests/items/{id}', [PurchaseController::class, 'getPrItemsJson'], [AuthMiddleware::class]);

$app->router->get('/procurement/rfqs', [PurchaseController::class, 'rfqs'], [AuthMiddleware::class]);
$app->router->get('/procurement/rfqs/create', [PurchaseController::class, 'createRfq'], [AuthMiddleware::class]);
$app->router->post('/procurement/rfqs/store', [PurchaseController::class, 'storeRfq'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/procurement/quotations/compare/{id}', [PurchaseController::class, 'compareQuotations'], [AuthMiddleware::class]);
$app->router->get('/procurement/quotations/select/{id}', [PurchaseController::class, 'selectQuotation'], [AuthMiddleware::class]);

$app->router->get('/procurement/orders', [PurchaseController::class, 'orders'], [AuthMiddleware::class]);
$app->router->get('/procurement/orders/create', [PurchaseController::class, 'createOrder'], [AuthMiddleware::class]);
$app->router->post('/procurement/orders/store', [PurchaseController::class, 'storeOrder'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/procurement/orders/items/{id}', [PurchaseController::class, 'getPoItemsJson'], [AuthMiddleware::class]);
$app->router->post('/procurement/orders/send-reminder', [PurchaseController::class, 'sendDeliveryReminder'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/procurement/grns', [PurchaseController::class, 'grns'], [AuthMiddleware::class]);
$app->router->get('/procurement/grns/create', [PurchaseController::class, 'createGrn'], [AuthMiddleware::class]);
$app->router->post('/procurement/grns/store', [PurchaseController::class, 'storeGrn'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/procurement/grns/send-receipt-notice', [PurchaseController::class, 'sendGrnReceiptNotice'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/procurement/grns/qc/{id}', [PurchaseController::class, 'processQc'], [AuthMiddleware::class]);
$app->router->post('/procurement/grns/qc/store/{id}', [PurchaseController::class, 'storeQc'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/procurement/invoices', [PurchaseController::class, 'invoices'], [AuthMiddleware::class]);
$app->router->get('/procurement/invoices/create', [PurchaseController::class, 'createInvoice'], [AuthMiddleware::class]);
$app->router->get('/procurement/invoices/show/{id}', [PurchaseController::class, 'showInvoice'], [AuthMiddleware::class]);
$app->router->post('/procurement/invoices/store', [PurchaseController::class, 'storeInvoice'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/procurement/payments', [PurchaseController::class, 'payments'], [AuthMiddleware::class]);
$app->router->get('/procurement/payments/create', [PurchaseController::class, 'createPayment'], [AuthMiddleware::class]);
$app->router->post('/procurement/payments/store', [PurchaseController::class, 'storePayment'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/procurement/returns', [PurchaseController::class, 'returns'], [AuthMiddleware::class]);
$app->router->get('/procurement/returns/create', [PurchaseController::class, 'createReturn'], [AuthMiddleware::class]);
$app->router->post('/procurement/returns/store', [PurchaseController::class, 'storeReturn'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/procurement/returns/gate-pass/{id}', [PurchaseController::class, 'gatePass'], [AuthMiddleware::class]);
$app->router->get('/procurement/returns/invoice/{id}', [PurchaseController::class, 'returnInvoice'], [AuthMiddleware::class]);

// Inventory & Warehouse Transfers
$app->router->get('/inventory', [InventoryController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/inventory/export', [InventoryController::class, 'exportCsv'], [AuthMiddleware::class]);
$app->router->get('/inventory/opening', [InventoryController::class, 'openingStock'], [AuthMiddleware::class]);
$app->router->post('/inventory/opening/store', [InventoryController::class, 'storeOpeningStock'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/issue', [InventoryController::class, 'stockIssue'], [AuthMiddleware::class]);
$app->router->post('/inventory/issue/store', [InventoryController::class, 'storeStockIssue'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/receive', [InventoryController::class, 'stockReceive'], [AuthMiddleware::class]);
$app->router->post('/inventory/receive/store', [InventoryController::class, 'storeStockReceive'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/damage', [InventoryController::class, 'stockDamage'], [AuthMiddleware::class]);
$app->router->post('/inventory/damage/store', [InventoryController::class, 'storeStockDamage'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/physical-verification', [InventoryController::class, 'physicalVerification'], [AuthMiddleware::class]);
$app->router->post('/inventory/physical-verification/reconcile', [InventoryController::class, 'storePhysicalVerification'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/batches', [InventoryController::class, 'batches'], [AuthMiddleware::class]);
$app->router->get('/inventory/expiry', [InventoryController::class, 'expiryDashboard'], [AuthMiddleware::class]);
$app->router->get('/inventory/ledger', [InventoryController::class, 'ledger'], [AuthMiddleware::class]);
$app->router->get('/inventory/adjust', [InventoryController::class, 'adjust'], [AuthMiddleware::class]);
$app->router->post('/inventory/adjust/store', [InventoryController::class, 'storeAdjustment'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/inventory/valuation', [InventoryController::class, 'valuation'], [AuthMiddleware::class]);

$app->router->get('/transfers', [TransferController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/transfers/create', [TransferController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/transfers/store', [TransferController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/transfers/receive/{id}', [TransferController::class, 'receive'], [AuthMiddleware::class]);

// Sales & Customers
$app->router->get('/customers', [CustomerController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/customers/statement/{id}', [CustomerController::class, 'statement'], [AuthMiddleware::class]);
$app->router->get('/customers/create', [CustomerController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/customers/store', [CustomerController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/sales', [SalesController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/sales/export', [SalesController::class, 'exportCsv'], [AuthMiddleware::class]);
$app->router->get('/sales/create', [SalesController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/sales/store', [SalesController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/sales/quotations', [SalesController::class, 'quotations'], [AuthMiddleware::class]);
$app->router->get('/sales/quotations/create', [SalesController::class, 'createQuotation'], [AuthMiddleware::class]);
$app->router->get('/sales/create-quotation', [SalesController::class, 'createQuotation'], [AuthMiddleware::class]);
$app->router->post('/sales/quotations/store', [SalesController::class, 'storeQuotation'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/sales/quotations/convert/{id}', [SalesController::class, 'convertQuotationToOrder'], [AuthMiddleware::class]);
$app->router->get('/sales/quotations/send/{id}', [SalesController::class, 'sendQuotationToCustomer'], [AuthMiddleware::class]);
$app->router->get('/sales/orders/dispatch/{id}', [SalesController::class, 'dispatchOrder'], [AuthMiddleware::class]);
$app->router->get('/sales/requests/reject/{id}', [SalesController::class, 'rejectRequest'], [AuthMiddleware::class]);
$app->router->get('/sales/invoices', [SalesController::class, 'invoices'], [AuthMiddleware::class]);
$app->router->get('/sales/invoices/show/{id}', [SalesController::class, 'showInvoice'], [AuthMiddleware::class]);
$app->router->get('/sales/payments', [SalesController::class, 'payments'], [AuthMiddleware::class]);
$app->router->get('/sales/returns', [SalesController::class, 'returns'], [AuthMiddleware::class]);
$app->router->get('/sales/returns/create', [SalesController::class, 'createReturn'], [AuthMiddleware::class]);
$app->router->get('/sales/create-return', [SalesController::class, 'createReturn'], [AuthMiddleware::class]);
$app->router->post('/sales/returns/store', [SalesController::class, 'storeReturn'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Customer Self-Service Portal
$app->router->get('/customer-portal/login', [\App\Controllers\CustomerPortalController::class, 'login']);
$app->router->post('/customer-portal/login', [\App\Controllers\CustomerPortalController::class, 'authenticate']);
$app->router->get('/customer-portal/dashboard', [\App\Controllers\CustomerPortalController::class, 'dashboard']);
$app->router->get('/customer-portal/requests/create', [\App\Controllers\CustomerPortalController::class, 'createRequest']);
$app->router->post('/customer-portal/requests/store', [\App\Controllers\CustomerPortalController::class, 'storeRequest']);
$app->router->get('/customer-portal/quotations', [\App\Controllers\CustomerPortalController::class, 'quotations']);
$app->router->get('/customer-portal/quotations/show/{id}', [\App\Controllers\CustomerPortalController::class, 'showQuotation']);
$app->router->get('/customer-portal/quotations/accept/{id}', [\App\Controllers\CustomerPortalController::class, 'acceptQuotation']);
$app->router->get('/customer-portal/quotations/reject/{id}', [\App\Controllers\CustomerPortalController::class, 'rejectQuotation']);
$app->router->get('/customer-portal/invoices', [\App\Controllers\CustomerPortalController::class, 'invoices']);
$app->router->get('/customer-portal/invoices/show/{id}', [\App\Controllers\CustomerPortalController::class, 'showInvoice']);
$app->router->get('/customer-portal/payments', [\App\Controllers\CustomerPortalController::class, 'invoices']);
$app->router->get('/customer-portal/invoices/pay/{id}', [\App\Controllers\CustomerPortalController::class, 'payInvoice']);
$app->router->get('/customer-portal/logout', [\App\Controllers\CustomerPortalController::class, 'logout']);

$app->router->get('/notifications/read/{id}', [\App\Controllers\NotificationController::class, 'readAndRedirect'], [AuthMiddleware::class]);
$app->router->post('/notifications/mark-all-read', [\App\Controllers\NotificationController::class, 'markAllRead'], [AuthMiddleware::class]);

// Settings (Master Configuration)
$app->router->get('/settings', [SettingsController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/settings/update', function() { (new \App\Core\Response())->redirect(url('/settings')); }, [AuthMiddleware::class]);
$app->router->post('/settings/update', [SettingsController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/settings/backup', [SettingsController::class, 'backup'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/settings/clear-cache', [SettingsController::class, 'clearCache'], [AuthMiddleware::class, CsrfMiddleware::class]);

// Administration & Tools
$app->router->get('/super-admin', [SuperAdminController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/super-admin/backup', [SuperAdminController::class, 'backup'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/super-admin/clear-cache', [SuperAdminController::class, 'clearCache'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/organization', [OrganizationController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/organization/companies/store', [OrganizationController::class, 'storeCompany'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/organization/branches/store', [OrganizationController::class, 'storeBranch'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/organization/warehouses/store', [OrganizationController::class, 'storeWarehouse'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/organization/racks/store', [OrganizationController::class, 'storeRack'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/organization/bins/store', [OrganizationController::class, 'storeBin'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/users', [UserController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/users/create', [UserController::class, 'create'], [AuthMiddleware::class]);
$app->router->post('/users/store', [UserController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/users/toggle-status/{id}', [UserController::class, 'toggleStatus'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/users/delete/{id}', [UserController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->get('/roles', [RoleController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/roles/toggle-permission', [RoleController::class, 'togglePermission'], [AuthMiddleware::class]);
$app->router->post('/roles/grant-all', [RoleController::class, 'grantAllPermissions'], [AuthMiddleware::class, CsrfMiddleware::class]);
$app->router->post('/roles/revoke-all', [RoleController::class, 'revokeAllPermissions'], [AuthMiddleware::class, CsrfMiddleware::class]);

$app->router->get('/barcode', [BarcodeController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/barcode/generate', [BarcodeController::class, 'generate'], [AuthMiddleware::class]);
$app->router->get('/reports', [ReportController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/reports/gst', [ReportController::class, 'gst'], [AuthMiddleware::class]);
$app->router->get('/analytics', [AnalyticsController::class, 'index'], [AuthMiddleware::class]);
$app->router->post('/reports', [ReportController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/reports/export', [ReportController::class, 'exportCsv'], [AuthMiddleware::class]);
$app->router->get('/reports/export-csv', [ReportController::class, 'exportCsv'], [AuthMiddleware::class]);
$app->router->get('/reports/export-excel', [ReportController::class, 'exportExcel'], [AuthMiddleware::class]);
$app->router->get('/reports/export-pdf', [ReportController::class, 'exportPdf'], [AuthMiddleware::class]);
$app->router->get('/emails', [EmailController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/notifications', [NotificationController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/audit', [AuditController::class, 'index'], [AuthMiddleware::class]);
$app->router->get('/search', [SearchController::class, 'index'], [AuthMiddleware::class]);

// REST API ROUTES
$app->router->post('/api/v1/auth/login', [AuthApiController::class, 'login']);
$app->router->get('/api/v1/products', [ProductApiController::class, 'index'], [ApiAuthMiddleware::class]);
$app->router->get('/api/v1/products/{id}', [ProductApiController::class, 'show'], [ApiAuthMiddleware::class]);
$app->router->get('/api/v1/suppliers', [SupplierApiController::class, 'index'], [ApiAuthMiddleware::class]);

$app->run();
