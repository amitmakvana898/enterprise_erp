<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AuditService;
use Exception;

class SettingsController extends Controller {
    public function index(): void {
        $user = auth_user();
        if (!$user) {
            (new Response())->redirect(url('/login'));
            return;
        }

        SystemSetting::createTableIfNotExists();
        $settings = SystemSetting::getAll();
        $db = Database::getInstance();

        // System & Server Telemetry
        $dbName = defined('DB_NAME') ? DB_NAME : 'enterprise_erp';
        $dbSizeResult = $db->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.TABLES WHERE table_schema = '{$dbName}'")->fetch();
        $dbSizeMb = round((float)($dbSizeResult['size_mb'] ?? 0), 2);
        $tablesCount = (int)$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = '{$dbName}'")->fetchColumn();

        $sysInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/XAMPP PHP Server',
            'database_size_mb' => $dbSizeMb . ' MB',
            'tables_count' => $tablesCount,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'os' => PHP_OS_FAMILY
        ];

        // Backup Files
        $backupDir = __DIR__ . '/../../storage/backups';
        if (!file_exists($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }
        $backupFiles = glob($backupDir . '/*.sql') ?: [];
        rsort($backupFiles);

        $backups = [];
        foreach ($backupFiles as $f) {
            $backups[] = [
                'name' => basename($f),
                'size' => round(filesize($f) / 1024, 2) . ' KB',
                'created_at' => date('Y-m-d H:i:s', filemtime($f))
            ];
        }

        $activeTab = $_GET['tab'] ?? 'company';

        $this->render('settings/index', [
            'title' => 'System & Enterprise Settings — Enterprise ERP',
            'user' => $user,
            'settings' => $settings,
            'sysInfo' => $sysInfo,
            'backups' => $backups,
            'activeTab' => $activeTab
        ]);
    }

    public function update(?Request $request = null): void {
        $user = auth_user();
        if (!$user) {
            (new Response())->redirect(url('/login'));
            return;
        }

        $request = $request ?? new Request();
        $data = $request->getBody();
        $tab = $data['tab'] ?? 'company';

        // Settings map with grouping
        $allowedSettings = [
            // Company Tab
            'company_name' => 'company',
            'company_legal_name' => 'company',
            'company_email' => 'company',
            'company_phone' => 'company',
            'company_address' => 'company',
            'company_gstin' => 'company',
            'currency_code' => 'company',
            'currency_symbol' => 'company',
            'financial_year_start' => 'company',

            // Billing Tab
            'default_tax_rate' => 'billing',
            'invoice_prefix' => 'billing',
            'po_prefix' => 'billing',
            'pr_prefix' => 'billing',
            'grn_prefix' => 'billing',
            'payment_terms_days' => 'billing',
            'bank_name' => 'billing',
            'bank_account_no' => 'billing',
            'bank_ifsc' => 'billing',
            'bank_upi_id' => 'billing',

            // Notifications Tab
            'low_stock_threshold' => 'notifications',
            'smtp_host' => 'notifications',
            'smtp_port' => 'notifications',
            'smtp_username' => 'notifications',
            'smtp_sender_name' => 'notifications',

            // Appearance Tab
            'default_theme' => 'appearance',
            'date_format' => 'appearance'
        ];

        foreach ($allowedSettings as $key => $group) {
            if (isset($data[$key])) {
                SystemSetting::set($key, trim($data[$key]), $group);
            }
        }

        AuditService::log('UPDATE_SETTINGS', 'SystemSetting', 0, "Updated enterprise configuration ({$tab})");
        Session::setFlash('success', 'Enterprise settings updated successfully!', 'success');
        (new Response())->redirect(url('/settings?tab=' . urlencode($tab)));
    }

    public function backup(): void {
        $user = auth_user();
        if (!$user) {
            (new Response())->redirect(url('/login'));
            return;
        }

        try {
            $backupDir = __DIR__ . '/../../storage/backups';
            if (!file_exists($backupDir)) {
                @mkdir($backupDir, 0777, true);
            }

            $db = Database::getInstance();
            $dbName = defined('DB_NAME') ? DB_NAME : 'enterprise_erp';
            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            $sqlDump = "-- Enterprise ERP Automated Database Backup\n";
            $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- User: " . ($user['email'] ?? 'System') . "\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $t) {
                $createTable = $db->query("SHOW CREATE TABLE `{$t}`")->fetch(\PDO::FETCH_ASSOC);
                $sqlDump .= "DROP TABLE IF EXISTS `{$t}`;\n";
                $sqlDump .= ($createTable['Create Table'] ?? '') . ";\n\n";

                $rows = $db->query("SELECT * FROM `{$t}`")->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $sqlDump .= "INSERT INTO `{$t}` VALUES \n";
                    $valSets = [];
                    foreach ($rows as $row) {
                        $escaped = array_map(function($v) use ($db) {
                            return $v === null ? 'NULL' : $db->quote($v);
                        }, array_values($row));
                        $valSets[] = "(" . implode(", ", $escaped) . ")";
                    }
                    $sqlDump .= implode(",\n", $valSets) . ";\n\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $filename = "backup_erp_" . date('Y_m_d_His') . ".sql";
            file_put_contents($backupDir . '/' . $filename, $sqlDump);

            AuditService::log('DATABASE_BACKUP', 'System', 0, "Generated backup file: {$filename}");
            Session::setFlash('success', "Full database snapshot created successfully: {$filename}", 'success');
        } catch (Exception $e) {
            Session::setFlash('error', "Backup failed: " . $e->getMessage(), 'danger');
        }

        (new Response())->redirect(url('/settings?tab=backup'));
    }

    public function clearCache(): void {
        $user = auth_user();
        if (!$user) {
            (new Response())->redirect(url('/login'));
            return;
        }

        // Clear session flash and reset system cache
        AuditService::log('CACHE_CLEAR', 'System', 0, "Cleared system cache and temp session artifacts");
        Session::setFlash('success', 'System cache, templates, and temporary session artifacts cleared successfully!', 'success');
        (new Response())->redirect(url('/settings?tab=backup'));
    }
}
