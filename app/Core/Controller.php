<?php

namespace App\Core;

use App\Helpers\Security;

abstract class Controller {
    public string $action = '';

    public function render(string $view, array $params = [], string $layout = 'layouts/main'): void {
        View::render($view, array_merge(['csrf_token' => Security::generateCsrfToken()], $params), $layout);
    }

    public function json(array $data, int $statusCode = 200): void {
        (new Response())->json($data, $statusCode);
    }

    public function redirect(string $url): void {
        (new Response())->redirect($url);
    }
}
