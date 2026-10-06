<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware {
    public function execute(Request $request, Response $response): void {
        if (!Session::get('user')) {
            Session::setFlash('error', 'Please log in to access this page.', 'danger');
            $response->redirect(url('/login'));
        }
    }
}
