<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Services\AuditService;

class ProfileController extends Controller {
    public function index(): void {
        $user = auth_user();
        $db = Database::getInstance();

        // Fetch full detailed user record from database
        $stmt = $db->prepare("
            SELECT u.*, r.display_name AS role_display, c.name AS company_name, b.name AS branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN companies c ON u.company_id = c.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $user['id']]);
        $profile = $stmt->fetch();

        // Recent audit log entries for this specific user
        $stmtAudit = $db->prepare("
            SELECT * FROM audit_logs 
            WHERE user_id = :uid OR user_name = :uname
            ORDER BY id DESC LIMIT 5
        ");
        $stmtAudit->execute(['uid' => $user['id'], 'uname' => $user['name']]);
        $userLogs = $stmtAudit->fetchAll();

        $this->render('profile/index', [
            'title' => 'My Account Profile - Enterprise ERP',
            'user' => $user,
            'profile' => $profile,
            'user_logs' => $userLogs
        ]);
    }

    public function updateProfile(): void {
        $request = new Request();
        $response = new Response();
        $user = auth_user();
        $data = $request->getBody();

        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($name)) {
            Session::setFlash('error', 'Full Name is required.', 'danger');
            $response->redirect(url('/profile'));
        }

        $db = Database::getInstance();
        $db->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id")->execute([
            'name' => $name,
            'phone' => $phone,
            'id' => $user['id']
        ]);

        // Update session
        $sessionUser = auth_user() ?? [];
        $sessionUser['name'] = $name;
        Session::set('user', $sessionUser);

        AuditService::log('Profile', 'UPDATE_PROFILE_INFO', $user['id'], null, ['name' => $name, 'phone' => $phone]);
        Session::setFlash('success', 'Profile information updated successfully!', 'success');
        $response->redirect(url('/profile'));
    }

    public function updatePassword(): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $user = auth_user();
        $data = $request->getBody();

        if (!$validator->validate($data, ['current_password' => 'required', 'new_password' => 'required|min:6'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/profile'));
        }

        if (($data['new_password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            Session::setFlash('error', 'New passwords do not match.', 'danger');
            $response->redirect(url('/profile'));
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        $currentHash = $stmt->fetchColumn();

        if (!Security::verifyPassword($data['current_password'], $currentHash)) {
            Session::setFlash('error', 'Current password is incorrect.', 'danger');
            $response->redirect(url('/profile'));
        }

        $newHash = Security::hashPassword($data['new_password']);
        $db->prepare("UPDATE users SET password = :pwd WHERE id = :id")->execute(['pwd' => $newHash, 'id' => $user['id']]);

        AuditService::log('Profile', 'CHANGE_PASSWORD', $user['id']);
        Session::setFlash('success', 'Account security password updated successfully!', 'success');
        $response->redirect(url('/profile'));
    }
}
