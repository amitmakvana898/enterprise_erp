<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\User;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Services\AuditService;

class AuthController extends Controller {
    public function login(): void {
        if (Session::get('user')) {
            $this->redirect(url('/dashboard'));
        }
        $this->render('auth/login', ['title' => 'Sign In - Enterprise ERP'], 'layouts/auth');
    }

    public function handleLogin(): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();

        $data = $request->getBody();
        if (!$validator->validate($data, ['email' => 'required|email', 'password' => 'required'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/login'));
        }

        $user = User::findWithRoleAndCompany(0); // Query manually by email
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.name AS role_name, r.display_name AS role_display, c.name AS company_name, b.name AS branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN companies c ON u.company_id = c.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.email = :email LIMIT 1
        ");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($data['password'], $user['password'])) {
            Session::setFlash('error', 'Invalid email or password.', 'danger');
            $response->redirect(url('/login'));
        }

        if ($user['status'] !== 'active') {
            Session::setFlash('error', 'Your account is inactive or suspended.', 'danger');
            $response->redirect(url('/login'));
        }

        // Fetch permissions
        $permissions = User::getUserPermissions((int)$user['id']);

        // Set session
        Session::set('user', [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => (int)$user['role_id'],
            'role_name' => $user['role_name'],
            'role_display' => $user['role_display'],
            'company_id' => (int)$user['company_id'],
            'company_name' => $user['company_name'],
            'branch_id' => (int)$user['branch_id'],
            'branch_name' => $user['branch_name']
        ]);
        Session::set('user_permissions', $permissions);

        // Update last login
        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")->execute(['id' => $user['id']]);

        AuditService::log('Authentication', 'LOGIN_SUCCESS', (int)$user['id']);

        if (($user['role_name'] ?? '') === 'customer') {
            $custStmt = $db->prepare("SELECT * FROM customers WHERE id = :cid OR email = :email LIMIT 1");
            $custStmt->execute(['cid' => $user['customer_id'] ?? 0, 'email' => $user['email']]);
            $cust = $custStmt->fetch();
            if ($cust) {
                $_SESSION['customer_user'] = $cust;
            } else {
                $_SESSION['customer_user'] = [
                    'id' => 1,
                    'name' => $user['name'],
                    'code' => 'CUST-DEMO',
                    'email' => $user['email'],
                    'credit_limit' => 100000.00
                ];
            }
            Session::setFlash('success', "Welcome to your Customer Self-Service Portal, {$user['name']}!", 'success');
            $response->redirect(url('/customer-portal/dashboard'));
            return;
        }

        Session::setFlash('success', "Welcome back, {$user['name']}!", 'success');
        $response->redirect(url('/dashboard'));
    }

    public function quickLogin(): void {
        $request = new Request();
        $response = new Response();
        $role = $request->get('role', 'super_admin');

        $roleMap = [
            'super_admin' => 'admin@erp.com',
            'company_admin' => 'companyadmin@erp.com',
            'procurement_manager' => 'procurement@erp.com',
            'warehouse_manager' => 'warehouse@erp.com',
            'qc_inspector' => 'qc@erp.com',
            'sales_manager' => 'sales@erp.com',
            'finance_manager' => 'finance@erp.com',
            'dept_manager' => 'manager@erp.com',
            'customer' => 'customer@enterprise.com',
        ];

        $email = $roleMap[$role] ?? 'admin@erp.com';

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.*, r.name AS role_name, r.display_name AS role_display, c.name AS company_name, b.name AS branch_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN companies c ON u.company_id = c.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.email = :email LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $permissions = User::getUserPermissions((int)$user['id']);
            Session::set('user', [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => (int)$user['role_id'],
                'role_name' => $user['role_name'],
                'role_display' => $user['role_display'],
                'company_id' => (int)$user['company_id'],
                'company_name' => $user['company_name'],
                'branch_id' => (int)$user['branch_id'],
                'branch_name' => $user['branch_name']
            ]);
            Session::set('user_permissions', $permissions);
            Session::setFlash('success', "Switched role to {$user['role_display']} ({$user['name']})", 'info');
        }

        $response->redirect(url('/dashboard'));
    }

    public function register(): void {
        Session::setFlash('info', 'Public registration is disabled. User accounts are created internally by the System Administrator.', 'info');
        (new Response())->redirect(url('/login'));
    }

    public function handleRegister(): void {
        Session::setFlash('info', 'Public registration is disabled. User accounts are created internally by the System Administrator.', 'info');
        (new Response())->redirect(url('/login'));
    }

    public function logout(): void {
        AuditService::log('Authentication', 'LOGOUT');
        Session::destroy();
        (new Response())->redirect(url('/'));
    }

    public function forgotPassword(): void {
        $this->render('auth/forgot', ['title' => 'Reset Password - Enterprise ERP'], 'layouts/auth');
    }

    public function handleForgotPassword(): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['email' => 'required|email', 'new_password' => 'required|min:6'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/forgot-password'));
            return;
        }

        if (($data['new_password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            Session::setFlash('error', 'Passwords do not match. Please ensure New Password and Confirm Password match exactly.', 'danger');
            $response->redirect(url('/forgot-password'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            Session::setFlash('error', 'No registered user account found with email: ' . $data['email'], 'danger');
            $response->redirect(url('/forgot-password'));
            return;
        }

        $newHash = Security::hashPassword($data['new_password']);
        $db->prepare("UPDATE users SET password = :pwd WHERE id = :id")->execute([
            'pwd' => $newHash,
            'id' => $user['id']
        ]);

        AuditService::log('Authentication', 'PASSWORD_RESET', (int)$user['id'], null, ['email' => $data['email']]);
        Session::setFlash('success', 'Password reset successfully for ' . $user['name'] . '! You can now log in with your new password.', 'success');
        $response->redirect(url('/login'));
    }

    public function changePassword(): void {
        $this->render('auth/change_password', ['title' => 'Change Password']);
    }

    public function handleChangePassword(): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['current_password' => 'required', 'new_password' => 'required|min:6'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/change-password'));
        }

        $user = auth_user();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        $currentHash = $stmt->fetchColumn();

        if (!Security::verifyPassword($data['current_password'], $currentHash)) {
            Session::setFlash('error', 'Current password is incorrect.', 'danger');
            $response->redirect(url('/change-password'));
        }

        $newHash = Security::hashPassword($data['new_password']);
        $db->prepare("UPDATE users SET password = :pwd WHERE id = :id")->execute(['pwd' => $newHash, 'id' => $user['id']]);

        AuditService::log('Authentication', 'CHANGE_PASSWORD', $user['id']);
        Session::setFlash('success', 'Password updated successfully.', 'success');
        $response->redirect(url('/dashboard'));
    }
}
