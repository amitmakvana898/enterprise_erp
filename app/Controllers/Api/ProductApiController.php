<?php

namespace App\Controllers\Api;

use App\Models\Product;

class ProductApiController extends ApiBaseController {
    public function index(): void {
        $products = Product::getDetailedCatalog();
        $this->jsonResponse(['status' => 'success', 'count' => count($products), 'data' => $products]);
    }

    public function show(int $id): void {
        $product = Product::find($id);
        if (!$product) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Product not found'], 404);
            return;
        }
        $this->jsonResponse(['status' => 'success', 'data' => $product]);
    }
}
