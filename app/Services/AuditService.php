<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

class AuditService {
    public static function log(string $module, string $action, ?int $recordId = null, $oldValues = null, $newValues = null): void {
        $user = auth_user();
        $userId = $user['id'] ?? null;
        $userName = $user['name'] ?? 'System';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown';

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, user_name, ip_address, user_agent, module, action, record_id, old_values, new_values) VALUES (:user_id, :user_name, :ip, :ua, :module, :action, :record_id, :old_val, :new_val)");

        $stmt->execute([
            'user_id' => $userId,
            'user_name' => $userName,
            'ip' => $ip,
            'ua' => $userAgent,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'old_val' => is_array($oldValues) || is_object($oldValues) ? json_encode($oldValues) : $oldValues,
            'new_val' => is_array($newValues) || is_object($newValues) ? json_encode($newValues) : $newValues
        ]);
    }
}
