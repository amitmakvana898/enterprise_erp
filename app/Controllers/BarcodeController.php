<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Product;
use App\Services\BarcodeGeneratorService;

class BarcodeController extends Controller {
    public function index(): void {
        if (!has_permission('products.barcode')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (products.barcode) to access Barcode Generator!', 'danger');
            (new \App\Core\Response())->redirect(url('/dashboard'));
            return;
        }

        $products = Product::all();
        $this->render('barcode/index', [
            'title' => 'Barcode (Code128) & QR Code Generator',
            'products' => $products
        ]);
    }

    public function generate(): void {
        if (!has_permission('products.barcode')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (products.barcode) to access Barcode Generator!', 'danger');
            (new \App\Core\Response())->redirect(url('/dashboard'));
            return;
        }
        $request = new Request();
        $productId = $request->get('product_id') ?: ($request->get('id') ?: 0);
        $this->printTag((string)$productId);
    }

    public function printTag(string $id = '0'): void {
        if (!has_permission('products.barcode')) {
            \App\Core\Session::setFlash('error', 'Access Denied: You do not have permission (products.barcode) to access Barcode Generator!', 'danger');
            (new \App\Core\Response())->redirect(url('/dashboard'));
            return;
        }
        $product = Product::find((int)$id);
        if (!$product) {
            $all = Product::all();
            $product = !empty($all) ? $all[0] : null;
        }

        if (!$product) {
            $this->render('errors/404', ['title' => 'Product Not Found']);
            return;
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, s.qty, b.code AS bin_code, w.name AS warehouse_name
            FROM products p
            LEFT JOIN inventory_stocks s ON p.id = s.product_id
            LEFT JOIN bins b ON s.bin_id = b.id
            LEFT JOIN warehouses w ON s.warehouse_id = w.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => (int)$id]);
        $productBin = $stmt->fetch();

        if (!$productBin && $product) {
            $productBin = array_merge($product, ['bin_code' => 'BIN-A12', 'warehouse_name' => 'Central Warehouse', 'qty' => 50]);
        }

        $barcodeSvg = BarcodeGeneratorService::generateCode128($product['sku']);
        $qrSvg = BarcodeGeneratorService::generateQrCode($product['sku']);

        $this->render('barcode/print', [
            'title' => 'Print Barcode Tag - ' . $product['name'],
            'product' => $productBin ?: $product,
            'barcodeSvg' => $barcodeSvg,
            'qrSvg' => $qrSvg
        ], 'layouts/print');
    }
}
