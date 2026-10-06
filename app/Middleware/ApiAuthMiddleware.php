<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\JwtService;

class ApiAuthMiddleware {
    public function execute(Request $request, Response $response): void {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            $response->json(['status' => 'error', 'message' => 'Authorization header missing or invalid format'], 401);
        }

        $token = substr($authHeader, 7);
        $payload = JwtService::verifyToken($token);

        if (!$payload) {
            $response->json(['status' => 'error', 'message' => 'Invalid or expired API JWT token'], 401);
        }

        $_REQUEST['api_user'] = $payload;
    }
}
