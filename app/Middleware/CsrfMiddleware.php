<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Security;

class CsrfMiddleware {
    public function execute(Request $request, Response $response): void {
        if ($request->isPost()) {
            $token = $request->get('csrf_token');
            if (!Security::validateCsrfToken($token)) {
                $response->setStatusCode(403);
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Security token expired or invalid. Please refresh the page.']);
                    exit;
                }
                \App\Core\Session::setFlash('error', 'Security session expired or invalid CSRF token. Please try submitting the form again.', 'danger');
                $referer = $_SERVER['HTTP_REFERER'] ?? url('/dashboard');
                $response->redirect($referer);
                exit;
            }
        }
    }
}
