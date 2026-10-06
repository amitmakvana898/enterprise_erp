<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuditController extends Controller {

    public function index(): void {
        if (!has_permission('audit.view') && (auth_user()['role_name'] ?? '') !== 'super_admin') {
            Session::setFlash('error', 'Access Denied: You do not have permission to view Audit Trail!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $request = new Request();
        $module = trim($request->get('module') ?? '');

        $db = Database::getInstance();
        if (!empty($module)) {
            $stmt = $db->prepare("
                SELECT a.*, u.name AS user_name, u.email AS user_email
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.module = :mod
                ORDER BY a.id DESC
                LIMIT 100
            ");
            $stmt->execute(['mod' => $module]);
            $logs = $stmt->fetchAll();
        } else {
            $logs = $db->query("
                SELECT a.*, u.name AS user_name, u.email AS user_email
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.id DESC
                LIMIT 100
            ")->fetchAll();
        }

        $this->render('audit/index', [
            'title' => 'Real-Time Enterprise Audit Trail',
            'logs' => $logs,
            'selectedModule' => $module
        ]);
    }
}
