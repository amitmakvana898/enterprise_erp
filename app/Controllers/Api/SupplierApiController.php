<?php

namespace App\Controllers\Api;

use App\Models\Supplier;

class SupplierApiController extends ApiBaseController {
    public function index(): void {
        $suppliers = Supplier::all();
        $this->jsonResponse(['status' => 'success', 'count' => count($suppliers), 'data' => $suppliers]);
    }
}
