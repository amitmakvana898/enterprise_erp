<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SystemSetting {
    protected static ?array $cache = null;

    public static function createTableIfNotExists(): void {
        $db = Database::getInstance();
        $db->exec("
            CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT NULL,
                setting_group VARCHAR(50) DEFAULT 'general',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Seed default settings if empty
        $count = (int)$db->query("SELECT COUNT(*) FROM system_settings")->fetchColumn();
        if ($count === 0) {
            $defaults = [
                // General & Company
                ['company_name', 'Enterprise Global Inc.', 'company'],
                ['company_legal_name', 'Enterprise ERP Systems Private Limited', 'company'],
                ['company_email', 'contact@enterprise-erp.com', 'company'],
                ['company_phone', '+91 98765 43210', 'company'],
                ['company_address', 'Tower 4, Infocity Tech Park, Gandhinagar, Gujarat 382007', 'company'],
                ['company_gstin', '24AAACE1234F1Z5', 'company'],
                ['currency_code', 'INR', 'company'],
                ['currency_symbol', '₹', 'company'],
                ['financial_year_start', '04-01', 'company'],
                
                // Invoicing & Tax
                ['default_tax_rate', '18', 'billing'],
                ['invoice_prefix', 'INV-2026-', 'billing'],
                ['po_prefix', 'PO-2026-', 'billing'],
                ['pr_prefix', 'PR-2026-', 'billing'],
                ['grn_prefix', 'GRN-2026-', 'billing'],
                ['payment_terms_days', '30', 'billing'],
                ['bank_name', 'HDFC Bank Ltd.', 'billing'],
                ['bank_account_no', '50200012345678', 'billing'],
                ['bank_ifsc', 'HDFC0001234', 'billing'],
                ['bank_upi_id', 'enterprise.erp@hdfcbank', 'billing'],

                // Notifications & SMTP
                ['low_stock_threshold', '10', 'notifications'],
                ['smtp_host', 'smtp.gmail.com', 'notifications'],
                ['smtp_port', '587', 'notifications'],
                ['smtp_username', 'alerts@enterprise-erp.com', 'notifications'],
                ['smtp_sender_name', 'Enterprise ERP Notification Hub', 'notifications'],

                // Appearance & System
                ['default_theme', 'light', 'appearance'],
                ['date_format', 'd-M-Y', 'appearance'],
                ['auto_backup_enabled', '1', 'system'],
                ['session_timeout_minutes', '60', 'system']
            ];

            $stmt = $db->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
            foreach ($defaults as $d) {
                $stmt->execute($d);
            }
        }
    }

    public static function getAll(): array {
        self::createTableIfNotExists();
        $db = Database::getInstance();
        $rows = $db->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        return $rows ?: [];
    }

    public static function get(string $key, $default = null): ?string {
        if (self::$cache === null) {
            self::$cache = self::getAll();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): bool {
        self::createTableIfNotExists();
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_group)
            VALUES (:key, :val, :grp)
            ON DUPLICATE KEY UPDATE setting_value = :val2, setting_group = :grp2
        ");
        $success = $stmt->execute([
            'key' => $key,
            'val' => $value,
            'grp' => $group,
            'val2' => $value,
            'grp2' => $group
        ]);
        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
        return $success;
    }
}
