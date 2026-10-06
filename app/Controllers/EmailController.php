<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class EmailController extends Controller {

    public function index(): void {
        if (!has_permission('audit.view') && !has_permission('organization.manage') && (auth_user()['role_name'] ?? '') !== 'super_admin') {
            Session::setFlash('error', 'Access Denied: You do not have permission to view System Email Outbox Logs!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $emails = $db->query("SELECT * FROM email_logs ORDER BY id DESC")->fetchAll();

        $this->render('emails/index', [
            'title' => 'System Email Outbox & Notification Activity Logs',
            'emails' => $emails
        ]);
    }
}
