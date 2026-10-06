<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Database;
use App\Helpers\Security;
use App\Services\JwtService;

class AuthApiController extends ApiBaseController {
    public function login(): void {
        $request = new Request();
        $data = $request->getBody();

        if (empty($data['email']) || empty($data['password'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Email and password required'], 400);
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($data['password'], $user['password'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            return;
        }

        $token = JwtService::generateToken([
            'id' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id']
        ]);

        $this->jsonResponse([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }
}
