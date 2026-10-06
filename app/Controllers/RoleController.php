<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuditService;

class RoleController extends Controller {
    public function index(): void {
        if (!has_permission('roles.manage') && !has_permission('roles.approve')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (roles.manage or roles.approve) to access Role Security Manager!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $showAll = isset($_GET['view']) && $_GET['view'] === 'all';

        $rolesQuery = "
            SELECT r.*, COUNT(u.id) AS user_count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id
            GROUP BY r.id
            ORDER BY r.id ASC
        ";
        $allRoles = $db->query($rolesQuery)->fetchAll();

        if ($showAll) {
            $roles = $allRoles;
        } else {
            $roles = array_values(array_filter($allRoles, function($r) {
                return (int)$r['user_count'] > 0 || $r['name'] === 'super_admin';
            }));
        }

        $permissions = Permission::all();
        $rolePermissions = $db->query("
            SELECT rp.*, p.code AS permission_code
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
        ")->fetchAll();

        $matrix = [];
        foreach ($rolePermissions as $rp) {
            $matrix[$rp['role_id']][$rp['permission_code']] = true;
        }

        $this->render('roles/index', [
            'title' => 'Role-Based Access Control (RBAC) Matrix',
            'roles' => $roles,
            'allRoles' => $allRoles,
            'showAll' => $showAll,
            'permissions' => $permissions,
            'matrix' => $matrix
        ]);
    }

    public function togglePermission(): void {
        $request = new Request();
        $response = new Response();

        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin' && !has_permission('roles.approve') && !has_permission('roles.manage')) {
            $response->json(['success' => false, 'message' => 'Access Denied: You do not have permission (roles.approve) to modify security permissions.'], 403);
            return;
        }

        $data = $request->getBody();
        $roleId = (int)($data['role_id'] ?? 0);
        $permissionId = (int)($data['permission_id'] ?? 0);

        if (!$roleId || !$permissionId) {
            $response->json(['success' => false, 'message' => 'Invalid role or permission parameters.'], 400);
            return;
        }

        $db = Database::getInstance();
        $stmtCheck = $db->prepare("SELECT 1 FROM role_permissions WHERE role_id = :r AND permission_id = :p");
        $stmtCheck->execute(['r' => $roleId, 'p' => $permissionId]);
        $exists = (bool)$stmtCheck->fetchColumn();

        if ($exists) {
            $stmtDel = $db->prepare("DELETE FROM role_permissions WHERE role_id = :r AND permission_id = :p");
            $stmtDel->execute(['r' => $roleId, 'p' => $permissionId]);
            $isGranted = false;
        } else {
            $stmtIns = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:r, :p)");
            $stmtIns->execute(['r' => $roleId, 'p' => $permissionId]);
            $isGranted = true;
        }

        AuditService::log('RBAC', 'PERMISSION_TOGGLE', $roleId, null, [
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'granted' => $isGranted
        ]);

        $response->json([
            'success' => true,
            'granted' => $isGranted,
            'message' => $isGranted ? 'Permission Granted successfully!' : 'Permission Revoked successfully!'
        ]);
    }

    public function grantAllPermissions(): void {
        $request = new Request();
        $response = new Response();

        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin' && !has_permission('roles.approve') && !has_permission('roles.manage')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (roles.approve) to modify security permissions.', 'danger');
            $response->redirect(url('/roles'));
            return;
        }

        $data = $request->getBody();
        $roleId = $data['role_id'] ?? 'all';
        $db = Database::getInstance();

        $permissions = $db->query("SELECT id FROM permissions")->fetchAll(\PDO::FETCH_COLUMN);

        if ($roleId === 'all') {
            $roles = $db->query("SELECT id FROM roles WHERE name != 'super_admin'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($roles as $rId) {
                foreach ($permissions as $pId) {
                    $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ({$rId}, {$pId})");
                }
            }
            AuditService::log('RBAC', 'GRANT_ALL_PERMISSIONS_SYSTEM_WIDE');
            Session::setFlash('success', 'ALL permissions granted & approved across ALL system roles successfully!', 'success');
        } else {
            $rId = (int)$roleId;
            foreach ($permissions as $pId) {
                $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES ({$rId}, {$pId})");
            }
            AuditService::log('RBAC', 'GRANT_ALL_PERMISSIONS_ROLE', $rId);
            Session::setFlash('success', "All permissions granted & approved for specified role successfully!", 'success');
        }

        $response->redirect(url('/roles'));
    }

    public function revokeAllPermissions(): void {
        $request = new Request();
        $response = new Response();

        $user = auth_user();
        if (($user['role_name'] ?? '') !== 'super_admin' && !has_permission('roles.approve') && !has_permission('roles.manage')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (roles.approve) to modify security permissions.', 'danger');
            $response->redirect(url('/roles'));
            return;
        }

        $data = $request->getBody();
        $roleId = (int)($data['role_id'] ?? 0);
        $db = Database::getInstance();

        if ($roleId > 0) {
            $stmtRole = $db->prepare("SELECT name FROM roles WHERE id = :id");
            $stmtRole->execute(['id' => $roleId]);
            $rName = $stmtRole->fetchColumn();

            if ($rName !== 'super_admin') {
                $db->prepare("DELETE FROM role_permissions WHERE role_id = :id")->execute(['id' => $roleId]);
                AuditService::log('RBAC', 'REVOKE_ALL_PERMISSIONS_ROLE', $roleId);
                Session::setFlash('success', "All permissions revoked for specified role.", 'warning');
            }
        }

        $response->redirect(url('/roles'));
    }
}
