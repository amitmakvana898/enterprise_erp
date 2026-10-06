<?php

// Enterprise ERP Scheduler & Background Cron Job

require_once __DIR__ . '/app/Config/Constants.php';
require_once __DIR__ . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;
use App\Services\AuditService;

echo "========================================================\n";
echo "    Enterprise ERP Background Cron Runner              \n";
echo "========================================================\n";
echo "[" . date('Y-m-d H:i:s') . "] Starting automated jobs...\n";

$db = Database::getInstance();

// Job 1: Low Stock Alert Check
$lowStockStmt = $db->query("
    SELECT p.id, p.name, p.sku, SUM(s.qty) AS current_qty, p.reorder_level
    FROM inventory_stocks s
    JOIN products p ON s.product_id = p.id
    GROUP BY p.id
    HAVING current_qty <= p.reorder_level
");
$lowStockItems = $lowStockStmt->fetchAll();

echo "[" . date('Y-m-d H:i:s') . "] Found " . count($lowStockItems) . " low stock alerts.\n";
foreach ($lowStockItems as $item) {
    echo " -> ALERT: Product '{$item['name']}' (SKU: {$item['sku']}) stock is {$item['current_qty']} (Reorder Level: {$item['reorder_level']})\n";
}

// Job 2: Pending Approval Reminder
$pendingPOs = $db->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'pending'")->fetchColumn();
echo "[" . date('Y-m-d H:i:s') . "] Found {$pendingPOs} pending Purchase Orders requiring approval.\n";

// Job 3: Database Backup Simulation
$backupFile = STORAGE_PATH . '/backups/db_backup_' . date('Ymd_His') . '.sql';
file_put_contents($backupFile, "-- Automated Enterprise ERP Database Backup --\n-- Backup Date: " . date('Y-m-d H:i:s') . "\n");
echo "[" . date('Y-m-d H:i:s') . "] Database backup file created: {$backupFile}\n";

AuditService::log('Scheduler', 'CRON_RUN_SUCCESS');
echo "[" . date('Y-m-d H:i:s') . "] All automated cron jobs finished successfully!\n";
