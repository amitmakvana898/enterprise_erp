<?php

require_once __DIR__ . '/../app/Config/Constants.php';
require_once __DIR__ . '/../app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
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

use App\Core\Database;

try {
    $db = Database::getInstance();
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Truncate all transactional & entry tables
    $tablesToTruncate = [
        'audit_logs',
        'approval_logs',
        'notifications',
        'price_histories',
        'purchase_payments',
        'purchase_invoices',
        'quality_inspections',
        'grn_items',
        'goods_receipt_notes',
        'purchase_order_items',
        'purchase_orders',
        'purchase_request_items',
        'purchase_requests',
        'sales_payments',
        'sales_invoices',
        'sales_order_items',
        'sales_orders',
        'warehouse_transfer_items',
        'warehouse_transfers',
        'stock_transactions',
        'inventory_stocks',
        'product_attributes',
        'supplier_products',
        'suppliers',
        'customers',
        'product_units',
        'products'
    ];

    foreach ($tablesToTruncate as $table) {
        $db->exec("TRUNCATE TABLE `{$table}`");
        echo "Truncated table: {$table}\n";
    }

    // 2. Remove non-system dummy users (keep default Super Admin user id=1 / email='admin@erp.com')
    $db->exec("DELETE FROM `users` WHERE `email` != 'admin@erp.com' AND `id` > 1;");
    echo "Cleaned non-admin user records.\n";

    // 3. Log fresh system initialization audit event
    $adminUser = $db->query("SELECT id FROM users WHERE email = 'admin@erp.com' LIMIT 1")->fetch();
    if ($adminUser) {
        $db->prepare("INSERT INTO audit_logs (user_id, user_name, ip_address, user_agent, module, action, old_values, new_values) VALUES (:uid, 'Super Admin', '127.0.0.1', 'System Reset', 'System', 'SYSTEM_RESET_FRESH_START', NULL, 'Database entries reset for fresh operations')")->execute(['uid' => $adminUser['id']]);
    }

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\n----------------------------------------------------\n";
    echo "SUCCESS: All entry data and dummy users cleared!\n";
    echo "The site database is now 100% clean and ready for fresh user registrations and real operational entries.\n";
    echo "----------------------------------------------------\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
