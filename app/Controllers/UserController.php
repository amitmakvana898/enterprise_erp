<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\Role;
use App\Helpers\Security;
use App\Services\AuditService;

class UserController extends Controller {
    public function index(): void {
        $users = User::getDetailedUsers();
        $this->render('users/index', [
            'title' => 'User Management & Role Assignment',
            'users' => $users
        ]);
    }

    public function create(): void {
        $roles = Role::all();
        $this->render('users/create', [
            'title' => 'Register New System User',
            'roles' => $roles
        ]);
    }

    public function store(): void {
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        $email = trim($data['email'] ?? '');
        $name = trim($data['name'] ?? '');

        if (empty($email) || empty($name)) {
            Session::setFlash('error', 'User full name and email address are required.', 'danger');
            $response->redirect(url('/users/create'));
            return;
        }

        $db = \App\Core\Database::getInstance();
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetchColumn()) {
            Session::setFlash('error', "Cannot register user: Email address '{$email}' is already in use by another system account!", 'danger');
            $response->redirect(url('/users/create'));
            return;
        }

        $userId = User::create([
            'company_id' => 1,
            'branch_id' => 1,
            'role_id' => (int)($data['role_id'] ?? 6),
            'name' => $name,
            'email' => $email,
            'password' => Security::hashPassword($data['password'] ?? 'password123'),
            'status' => $data['status'] ?? 'active'
        ]);

        AuditService::log('UserManagement', 'CREATE_USER', $userId);
        Session::setFlash('success', 'User registered successfully!', 'success');
        $response->redirect(url('/users'));
    }

    public function toggleStatus(int $id): void {
        $user = auth_user();
        $response = new Response();

        if ((int)$user['id'] === $id) {
            Session::setFlash('error', 'You cannot block your own active user account!', 'danger');
            $response->redirect(url('/users'));
            return;
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT status, name FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $targetUser = $stmt->fetch();

        if (!$targetUser) {
            Session::setFlash('error', 'User not found.', 'danger');
            $response->redirect(url('/users'));
            return;
        }

        $newStatus = ($targetUser['status'] === 'active') ? 'suspended' : 'active';
        $db->prepare("UPDATE users SET status = :status WHERE id = :id")->execute(['status' => $newStatus, 'id' => $id]);

        AuditService::log('UserManagement', 'TOGGLE_USER_STATUS', $id, null, ['old_status' => $targetUser['status'], 'new_status' => $newStatus]);
        
        $msg = ($newStatus === 'suspended') 
            ? "User '{$targetUser['name']}' has been blocked/suspended!" 
            : "User '{$targetUser['name']}' has been unblocked & activated!";
            
        Session::setFlash('success', $msg, ($newStatus === 'suspended' ? 'warning' : 'success'));
        $response->redirect(url('/users'));
    }

    public function delete(int $id): void {
        $user = auth_user();
        $response = new Response();

        if ((int)$user['id'] === $id) {
            Session::setFlash('error', 'You cannot delete your own active user account!', 'danger');
            $response->redirect(url('/users'));
            return;
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT name FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $targetUser = $stmt->fetch();

        if ($targetUser) {
            try {
                $db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $id]);
                AuditService::log('UserManagement', 'DELETE_USER', $id, null, ['deleted_name' => $targetUser['name']]);
                Session::setFlash('success', "User '{$targetUser['name']}' deleted from system permanently.", 'success');
            } catch (\PDOException $e) {
                // Foreign key constraint protection: User has linked PR/PO/GRN transactions
                $db->prepare("UPDATE users SET status = 'inactive' WHERE id = :id")->execute(['id' => $id]);
                AuditService::log('UserManagement', 'DEACTIVATE_USER_FK', $id, null, ['name' => $targetUser['name']]);
                Session::setFlash('info', "User '{$targetUser['name']}' has linked transaction history (PRs/POs/GRNs). Account deactivated & blocked to preserve audit history integrity.", 'warning');
            }
        }

        $response->redirect(url('/users'));
    }
}
