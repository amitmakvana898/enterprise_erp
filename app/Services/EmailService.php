<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class EmailService {

    /**
     * Role Email Routing Table
     */
    public static array $roleEmails = [
        'super_admin' => 'superadmin@gmail.com',
        'sales_manager' => 'sales@gmail.com',
        'procurement_manager' => 'procurement@gmail.com',
        'warehouse_manager' => 'warehouse@gmail.com',
        'finance_manager' => 'finance@gmail.com',
        'qc_inspector' => 'quality@gmail.com',
        'dept_manager' => 'requisitioner@gmail.com',
        'company_admin' => 'admin@gmail.com'
    ];

    /**
     * Dispatch an automated email notification and log to email_logs outbox
     */
    public static function send(string $recipientRole, string $recipientEmail, string $subject, string $bodyHtml, string $triggerEvent = 'WORKFLOW_ALERT'): bool {
        try {
            $db = Database::getInstance();
            
            // Standard email header wrap
            $fullHtml = "
                <div style='font-family:Arial,sans-serif;background:#0f172a;color:#ffffff;padding:20px;border-radius:8px;'>
                    <div style='border-bottom:1px solid #334155;padding-bottom:10px;margin-bottom:15px;'>
                        <h3 style='color:#f59e0b;margin:0;'>Enterprise ERP Notification</h3>
                        <small style='color:#94a3b8;'>Automated System Dispatch to " . htmlspecialchars($recipientEmail) . "</small>
                    </div>
                    <div style='line-height:1.6;font-size:14px;'>
                        {$bodyHtml}
                    </div>
                    <div style='border-top:1px solid #334155;margin-top:20px;padding-top:10px;font-size:12px;color:#64748b;'>
                        This is an automated notification sent to role <strong>" . htmlspecialchars($recipientRole) . "</strong>.
                    </div>
                </div>
            ";

            // Attempt PHP mail dispatch silently
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Enterprise ERP System <no-reply@enterprise-erp.com>' . "\r\n";
            if (ini_get('SMTP')) {
                @mail($recipientEmail, $subject, $fullHtml, $headers);
            }

            // Log entry into email_logs table for audit & live preview
            $stmt = $db->prepare("
                INSERT INTO email_logs (recipient_role, recipient_email, subject, body_html, trigger_event, status, sent_at)
                VALUES (:rrole, :remail, :subj, :body, :event, 'sent', NOW())
            ");
            $stmt->execute([
                'rrole' => $recipientRole,
                'remail' => $recipientEmail,
                'subj' => $subject,
                'body' => $fullHtml,
                'event' => $triggerEvent
            ]);

            return true;
        } catch (Exception $e) {
            error_log("EmailService Error: " . $e->getMessage());
            return false;
        }
    }

    // Role-specific quick dispatch helpers
    public static function notifyProcurement(string $subject, string $bodyHtml, string $event = 'PR_SUBMITTED'): bool {
        return self::send('Procurement Manager', self::$roleEmails['procurement_manager'], $subject, $bodyHtml, $event);
    }

    public static function notifyWarehouse(string $subject, string $bodyHtml, string $event = 'STOCK_DISPATCH'): bool {
        return self::send('Warehouse Manager', self::$roleEmails['warehouse_manager'], $subject, $bodyHtml, $event);
    }

    public static function notifyFinance(string $subject, string $bodyHtml, string $event = 'INVOICE_GENERATED'): bool {
        return self::send('Finance Manager', self::$roleEmails['finance_manager'], $subject, $bodyHtml, $event);
    }

    public static function notifySales(string $subject, string $bodyHtml, string $event = 'SALES_ORDER'): bool {
        return self::send('Sales Manager', self::$roleEmails['sales_manager'], $subject, $bodyHtml, $event);
    }

    public static function notifyQuality(string $subject, string $bodyHtml, string $event = 'QC_REJECTION'): bool {
        return self::send('QC Inspector', self::$roleEmails['qc_inspector'], $subject, $bodyHtml, $event);
    }

    public static function notifySuperAdmin(string $subject, string $bodyHtml, string $event = 'ADMIN_ALERT'): bool {
        return self::send('Super Administrator', self::$roleEmails['super_admin'], $subject, $bodyHtml, $event);
    }
}
