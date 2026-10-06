<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

class ApiBaseController extends Controller {
    protected function jsonResponse(array $data, int $status = 200): void {
        (new Response())->json($data, $status);
    }
}
