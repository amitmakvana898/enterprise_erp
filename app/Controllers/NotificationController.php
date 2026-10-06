<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;

class NotificationController extends Controller {

    public function index(): void {
        $db = Database::getInstance();

        // 1. Low Stock Alerts
        $lowStock = $db->query("
            SELECT p.name, p.sku, COALESCE(SUM(s.qty), 0) AS current_stock, p.reorder_level
            FROM products p
            LEFT JOIN inventory_stocks s ON p.id = s.product_id
            GROUP BY p.id, p.name, p.sku, p.reorder_level
            HAVING current_stock <= p.reorder_level OR current_stock = 0
            LIMIT 5
        ")->fetchAll();

        // 2. Pending Requisitions
        $pendingPrs = $db->query("
            SELECT pr.request_no, pr.department, pr.priority, pr.created_at, u.name AS requester_name
            FROM purchase_requests pr
            LEFT JOIN users u ON pr.requested_by = u.id
            WHERE pr.status = 'pending'
            ORDER BY pr.id DESC
            LIMIT 5
        ")->fetchAll();

        // 3. Outstanding Customer Invoices
        $pendingInvoicesQuery = "
            SELECT si.invoice_no, c.name AS customer_name, (si.total_amount - si.paid_amount) AS due_amount, si.due_date
            FROM sales_invoices si
            JOIN customers c ON si.customer_id = c.id
            WHERE si.status != 'paid'
            ORDER BY si.id DESC
            LIMIT 5
        ";
        $pendingInvoices = $db->query($pendingInvoicesQuery)->fetchAll();

        $this->render('notifications/index', [
            'title' => 'Real-Time Notification & Workflow Alert Hub',
            'lowStock' => $lowStock,
            'pendingPrs' => $pendingPrs,
            'pendingInvoices' => $pendingInvoices
        ]);
    }

    public function readAndRedirect(int $id): void {
        $db = Database::getInstance();
        $response = new Response();

        $stmt = $db->prepare("SELECT * FROM notifications WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $n = $stmt->fetch();

        if ($n) {
            // Mark as read
            $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id")->execute(['id' => $id]);
            
            // Redirect to destination link
            $response->redirect(url($n['link'] ?: '/dashboard'));
            return;
        }

        $response->redirect(url('/dashboard'));
    }

    public function markAllRead(): void {
        $db = Database::getInstance();
        $response = new Response();
        $currUser = auth_user();

        if ($currUser) {
            $roleTarget = (($currUser['role_name'] ?? '') === 'customer') ? 'customer' : 'sales';
            $db->prepare("
                UPDATE notifications 
                SET is_read = 1 
                WHERE role_target = :role OR user_id = :uid
            ")->execute([
                'role' => $roleTarget,
                'uid' => $currUser['id'] ?? 0
            ]);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $response->redirect(url('/dashboard'));
    }
}
