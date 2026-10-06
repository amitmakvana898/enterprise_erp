<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use App\Helpers\Security;
use App\Services\JwtService;

class ApiBaseController extends Controller {
    protected function jsonResponse(array $data, int $status = 200): void {
        (new Response())->json($data, $status);
    }
}

class AuthApiController extends ApiBaseController {
    public function login(): void {
        $request = new Request();
        $data = $request->getBody();

        if (empty($data['email']) || empty($data['password'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Email and password required'], 400);
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $data['email']]);
        $user = $stmt->fetch();

        if (!$user || !Security::verifyPassword($data['password'], $user['password'])) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid credentials'], 401);
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

class ProductApiController extends ApiBaseController {
    public function index(): void {
        $products = Product::getDetailedCatalog();
        $this->jsonResponse(['status' => 'success', 'count' => count($products), 'data' => $products]);
    }

    public function show(int $id): void {
        $product = Product::find($id);
        if (!$product) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Product not found'], 404);
        }
        $this->jsonResponse(['status' => 'success', 'data' => $product]);
    }
}

class SupplierApiController extends ApiBaseController {
    public function index(): void {
        $suppliers = Supplier::all();
        $this->jsonResponse(['status' => 'success', 'count' => count($suppliers), 'data' => $suppliers]);
    }
}
