<?php

namespace App\Services;

use App\Core\Database;

class NotificationService {
    public static function send(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read, link, created_at) VALUES (:user_id, :title, :message, :type, 0, :link, NOW())");
        return $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link
        ]);
    }

    public static function notifyRole(string $roleName, string $message, string $type = 'info', ?string $link = null, ?string $title = null): bool {
        $db = Database::getInstance();
        $title = $title ?: 'ERP Workflow Notification';
        
        // Find users matching this role
        $users = $db->query("
            SELECT u.id 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = " . $db->quote($roleName) . " OR r.display_name = " . $db->quote($roleName) . "
        ")->fetchAll();

        if (!empty($users)) {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, role_target, title, message, type, is_read, link, created_at) VALUES (:user_id, :role, :title, :message, :type, 0, :link, NOW())");
            foreach ($users as $u) {
                $stmt->execute([
                    'user_id' => $u['id'],
                    'role'    => $roleName,
                    'title'   => $title,
                    'message' => $message,
                    'type'    => $type,
                    'link'    => $link
                ]);
            }
            return true;
        } else {
            // General broadcast placeholder
            $stmt = $db->prepare("INSERT INTO notifications (user_id, role_target, title, message, type, is_read, link, created_at) VALUES (1, :role, :title, :message, :type, 0, :link, NOW())");
            return $stmt->execute([
                'role'    => $roleName,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'link'    => $link
            ]);
        }
    }
}
