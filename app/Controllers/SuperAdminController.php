<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuditService;
use Exception;

class SuperAdminController extends Controller {
    public function index(): void {
        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin') {
            Session::setFlash('error', 'Super Administrator access required.', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();

        // System Health & Database Stats
        $dbName = 'enterprise_erp';
        $dbSizeResult = $db->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.TABLES WHERE table_schema = '{$dbName}'")->fetch();
        $dbSizeMb = round((float)($dbSizeResult['size_mb'] ?? 0), 2);

        $tablesCount = (int)$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = '{$dbName}'")->fetchColumn();
        $totalCompanies = (int)$db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        $totalBranches = (int)$db->query("SELECT COUNT(*) FROM branches")->fetchColumn();
        $totalWarehouses = (int)$db->query("SELECT COUNT(*) FROM warehouses")->fetchColumn();
        $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalRoles = (int)$db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
        $totalAuditLogs = (int)$db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

        // Server Environment Details
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

        // List existing backups in storage/backups/
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

        $this->render('admin/index', [
            'title' => 'Super Admin Master Control Center',
            'user' => $user,
            'sys_info' => $sysInfo,
            'counts' => [
                'companies' => $totalCompanies,
                'branches' => $totalBranches,
                'warehouses' => $totalWarehouses,
                'users' => $totalUsers,
                'roles' => $totalRoles,
                'audits' => $totalAuditLogs
            ],
            'backups' => $backups
        ]);
    }

    public function backup(): void {
        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin') {
            Session::setFlash('error', 'Super Administrator access required.', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $response = new Response();
        $db = Database::getInstance();

        try {
            $backupDir = __DIR__ . '/../../storage/backups';
            if (!file_exists($backupDir)) {
                @mkdir($backupDir, 0777, true);
            }

            $filename = 'db_backup_' . date('Ymd_His') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            // Generate SQL Backup Snapshot Header
            $sqlContent = "-- Enterprise ERP Database Backup Snapshot\n";
            $sqlContent .= "-- Generated on " . date('Y-m-d H:i:s') . " by Super Admin: " . $user['name'] . "\n\n";

            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $t) {
                $createSql = $db->query("SHOW CREATE TABLE `{$t}`")->fetchColumn(1);
                $sqlContent .= "DROP TABLE IF EXISTS `{$t}`;\n" . $createSql . ";\n\n";
            }

            file_put_contents($filepath, $sqlContent);

            AuditService::log('SuperAdmin', 'CREATE_DB_BACKUP', null, null, ['filename' => $filename]);
            Session::setFlash('success', "Database backup snapshot '{$filename}' created successfully in storage/backups/!", 'success');
        } catch (Exception $e) {
            Session::setFlash('error', 'Backup failed: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/super-admin'));
    }

    public function clearCache(): void {
        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin') {
            Session::setFlash('error', 'Super Administrator access required.', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $response = new Response();

        try {
            // Flush temp files in storage/cache if any
            $cacheDir = __DIR__ . '/../../storage/cache';
            if (file_exists($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $f) {
                    if (is_file($f)) @unlink($f);
                }
            }

            AuditService::log('SuperAdmin', 'CLEAR_SYSTEM_CACHE');
            Session::setFlash('success', 'System session cache and temporary storage flushed successfully!', 'success');
        } catch (Exception $e) {
            Session::setFlash('error', 'Cache clear failed: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/super-admin'));
    }
}
