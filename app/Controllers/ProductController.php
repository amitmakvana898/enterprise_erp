<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Helpers\Validator;
use App\Services\AuditService;
use App\Services\SeederService;
use App\Services\ExcelExportService;

class ProductController extends Controller {
    public function index(): void {
        if (!has_permission('products.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.read) to view the Product Master Catalog!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $filter = $_GET['filter'] ?? null;

        if ($filter === 'low_stock') {
            $products = $db->query("
                SELECT p.*, c.name AS category_name, b.name AS brand_name, u.code AS unit_code,
                       COALESCE(SUM(s.qty), 0) AS total_stock
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN units u ON p.unit_id = u.id
                LEFT JOIN inventory_stocks s ON p.id = s.product_id
                GROUP BY p.id
                HAVING total_stock <= p.reorder_level
                ORDER BY p.name ASC
            ")->fetchAll();
        } else {
            $products = Product::getDetailedCatalog();
        }

        $categories = $db->query("
            SELECT c.*, COUNT(p.id) AS product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ")->fetchAll();
        $brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();

        // Fetch dynamic attributes for all products
        $productAttributes = [];
        $attrRows = $db->query("
            SELECT pa.product_id, a.name AS attr_name, a.code AS attr_code, pa.attribute_value
            FROM product_attributes pa
            JOIN attributes a ON pa.attribute_id = a.id
            ORDER BY a.id ASC
        ")->fetchAll();
        foreach ($attrRows as $ar) {
            $productAttributes[$ar['product_id']][] = [
                'name' => $ar['attr_name'],
                'code' => $ar['attr_code'],
                'value' => $ar['attribute_value']
            ];
        }

        $this->render('products/index', [
            'title' => 'Product Master & Categories',
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'productAttributes' => $productAttributes
        ]);
    }

    public function create(): void {
        if (!has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.create) to create products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $db = Database::getInstance();
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $warehouses = $db->query("SELECT * FROM warehouses ORDER BY name ASC")->fetchAll();
        $bins = $db->query("
            SELECT bn.*, r.code AS rack_code, w.name AS warehouse_name
            FROM bins bn
            JOIN racks r ON bn.rack_id = r.id
            JOIN warehouses w ON r.warehouse_id = w.id
            ORDER BY bn.code ASC
        ")->fetchAll();

        $allAttributes = $db->query("SELECT * FROM attributes ORDER BY id ASC")->fetchAll();

        $this->render('products/create', [
            'title' => 'Create Product',
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'warehouses' => $warehouses,
            'bins' => $bins,
            'allAttributes' => $allAttributes
        ]);
    }

    public function store(): void {
        if (!has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.create) to create products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['sku' => 'required', 'name' => 'required', 'category_id' => 'required', 'unit_id' => 'required', 'purchase_rate' => 'required|numeric', 'selling_rate' => 'required|numeric'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/products/create'));
            return;
        }

        $db = Database::getInstance();
        $sku = strtoupper(trim($data['sku']));

        // Check required dynamic attributes ONLY for attributes submitted or flagged as required
        $reqAttrRows = $db->query("SELECT id, name, LOWER(code) AS lower_code FROM attributes WHERE is_required = 1")->fetchAll();
        if (!empty($reqAttrRows)) {
            $userAttrs = $data['attributes'] ?? [];
            foreach ($reqAttrRows as $ra) {
                $code = $ra['lower_code'];
                if (array_key_exists($code, $userAttrs)) {
                    $val = $userAttrs[$code];
                    $hasValue = is_array($val) ? !empty(array_filter($val, function($v) { return trim($v) !== ''; })) : !empty(trim($val ?? ''));
                    if (!$hasValue) {
                        Session::setFlash('error', "Cannot add product: Required attribute field '{$ra['name']}' is empty. Please select or fill a value!", 'danger');
                        $response->redirect(url('/products/create'));
                        return;
                    }
                }
            }
        }

        // Check for duplicate SKU and disambiguate automatically if needed
        $stmtCheck = $db->prepare("SELECT id FROM products WHERE sku = :sku LIMIT 1");
        $stmtCheck->execute(['sku' => $sku]);
        if ($stmtCheck->fetchColumn()) {
            $sku = $sku . '-' . rand(100, 999);
        }

        $barcode = !empty($data['barcode']) ? trim($data['barcode']) : '890' . rand(100000000, 999999999);
        $qrCode = 'QR-' . $sku;

        try {
            $productId = Product::create([
                'sku' => $sku,
                'barcode' => $barcode,
                'qr_code' => $qrCode,
                'name' => trim($data['name']),
                'description' => trim($data['description'] ?? ''),
                'category_id' => (int) $data['category_id'],
                'brand_id' => !empty($data['brand_id']) ? (int) $data['brand_id'] : null,
                'unit_id' => (int) $data['unit_id'],
                'purchase_rate' => (float) $data['purchase_rate'],
                'selling_rate' => (float) $data['selling_rate'],
                'hsn_code' => trim($data['hsn_code'] ?? '8471'),
                'gst_rate' => (float) ($data['gst_rate'] ?? 18.00),
                'reorder_level' => (int) ($data['reorder_level'] ?? 10),
                'valuation_method' => $data['valuation_method'] ?? 'FIFO',
                'status' => 'active'
            ]);
        } catch (\Exception $e) {
            Session::setFlash('error', 'Error creating product: ' . $e->getMessage(), 'danger');
            $response->redirect(url('/products/create'));
            return;
        }

        // Optional Legacy Opening Stock (Only if explicitly enabled by admin)
        if (!empty($data['enable_opening_stock']) && !empty($data['initial_stock']) && (int)$data['initial_stock'] > 0 && !empty($data['bin_id'])) {
            $binStmt = $db->prepare("
                SELECT bn.*, r.warehouse_id 
                FROM bins bn 
                JOIN racks r ON bn.rack_id = r.id 
                WHERE bn.id = :bid LIMIT 1
            ");
            $binStmt->execute(['bid' => (int)$data['bin_id']]);
            $binInfo = $binStmt->fetch();

            if ($binInfo) {
                $whId = (int)$binInfo['warehouse_id'];
                $binId = (int)$data['bin_id'];
                $initQty = (int)$data['initial_stock'];
                $purchRate = (float)$data['purchase_rate'];

                $stockStmt = $db->prepare("
                    INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, qty, valuation_rate)
                    VALUES (:wid, :bid, :pid, :batch, :qty, :cost)
                    ON DUPLICATE KEY UPDATE qty = qty + :qty2
                ");
                $stockStmt->execute([
                    'wid' => $whId,
                    'bid' => $binId,
                    'pid' => $productId,
                    'batch' => 'BATCH-INIT-' . date('Ymd'),
                    'qty' => $initQty,
                    'cost' => $purchRate,
                    'qty2' => $initQty
                ]);

                $userId = auth_user()['id'] ?? null;
                if (!$userId) {
                    $userId = (int)$db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
                }
                $txNo = 'TXN-INIT-' . date('Ymd') . '-' . rand(1000, 9999);
                $totalCost = $initQty * $purchRate;

                $txStmt = $db->prepare("
                    INSERT INTO stock_transactions (
                        transaction_no, warehouse_id, bin_id, product_id, batch_no, 
                        transaction_type, reference_type, reference_id, in_qty, out_qty, 
                        balance_qty, unit_cost, total_cost, created_by
                    ) VALUES (
                        :txno, :wid, :bid, :pid, 'DEFAULT', 
                        'OPENING', 'INITIAL_STOCK', :refid, :in_qty, 0, 
                        :bal_qty, :cost, :total_cost, :uid
                    )
                ");
                $txStmt->execute([
                    'txno' => $txNo,
                    'wid' => $whId,
                    'bid' => $binId,
                    'pid' => $productId,
                    'refid' => $productId,
                    'in_qty' => $initQty,
                    'bal_qty' => $initQty,
                    'cost' => $purchRate,
                    'total_cost' => $totalCost,
                    'uid' => $userId
                ]);
            }
        }

        // Save Dynamic Product Attributes
        if (!empty($data['attributes']) && is_array($data['attributes'])) {
            $db = Database::getInstance();
            $attrRows = $db->query("SELECT id, LOWER(code) AS lower_code FROM attributes")->fetchAll();
            $attrMap = [];
            foreach ($attrRows as $ar) {
                $attrMap[$ar['lower_code']] = (int)$ar['id'];
            }

            $insAttr = $db->prepare("
                INSERT INTO product_attributes (product_id, attribute_id, attribute_value)
                VALUES (:pid, :aid, :val)
                ON DUPLICATE KEY UPDATE attribute_value = VALUES(attribute_value)
            ");

            foreach ($data['attributes'] as $code => $val) {
                $lowerCode = strtolower(trim($code));
                if (isset($attrMap[$lowerCode])) {
                    $finalVal = is_array($val) ? implode(', ', array_filter($val)) : trim($val);
                    if ($finalVal !== '') {
                        $insAttr->execute([
                            'pid' => $productId,
                            'aid' => $attrMap[$lowerCode],
                            'val' => $finalVal
                        ]);
                    }
                }
            }
        }

        AuditService::log('Product', 'CREATE', $productId, null, $data);
        Session::setFlash('success', 'Product created successfully with dynamic category & attributes!', 'success');
        $response->redirect(url('/products'));
    }

    public function show(int $id): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name, b.name AS brand_name, u.code AS unit_code
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
            WHERE p.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            Session::setFlash('error', 'Product not found.', 'danger');
            (new Response())->redirect(url('/products'));
        }

        // Fetch dynamic attributes
        $attrStmt = $db->prepare("
            SELECT pa.*, a.name AS attr_name, a.code AS attr_code
            FROM product_attributes pa
            JOIN attributes a ON pa.attribute_id = a.id
            WHERE pa.product_id = :pid
        ");
        $attrStmt->execute(['pid' => $id]);
        $attributes = $attrStmt->fetchAll();

        // Fetch bin stock locations
        $stockStmt = $db->prepare("
            SELECT s.*, w.name AS warehouse_name, bn.code AS bin_code
            FROM inventory_stocks s
            JOIN warehouses w ON s.warehouse_id = w.id
            JOIN bins bn ON s.bin_id = bn.id
            WHERE s.product_id = :pid
        ");
        $stockStmt->execute(['pid' => $id]);
        $stocks = $stockStmt->fetchAll();

        $this->render('products/show', [
            'title' => $product['name'],
            'product' => $product,
            'attributes' => $attributes,
            'stocks' => $stocks
        ]);
    }

    public function edit(int $id): void {
        if (!has_permission('products.update')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.update) to edit products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            Session::setFlash('error', 'Product not found.', 'danger');
            (new Response())->redirect(url('/products'));
        }

        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();

        $this->render('products/edit', [
            'title' => 'Edit Product: ' . $product['name'],
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units
        ]);
    }

    public function update(int $id): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['sku' => 'required', 'name' => 'required', 'category_id' => 'required', 'unit_id' => 'required', 'purchase_rate' => 'required|numeric', 'selling_rate' => 'required|numeric'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/products/edit/' . $id));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("
                UPDATE products SET 
                    sku = :sku,
                    barcode = :barcode,
                    name = :name,
                    description = :desc,
                    category_id = :cat,
                    brand_id = :brand,
                    unit_id = :unit,
                    purchase_rate = :prate,
                    selling_rate = :srate,
                    hsn_code = :hsn,
                    gst_rate = :gst,
                    reorder_level = :reorder,
                    valuation_method = :val
                WHERE id = :id
            ");

            $stmt->execute([
                'sku' => strtoupper(trim($data['sku'])),
                'barcode' => trim($data['barcode'] ?? ''),
                'name' => trim($data['name']),
                'desc' => trim($data['description'] ?? ''),
                'cat' => (int)$data['category_id'],
                'brand' => !empty($data['brand_id']) ? (int)$data['brand_id'] : null,
                'unit' => (int)$data['unit_id'],
                'prate' => (float)$data['purchase_rate'],
                'srate' => (float)$data['selling_rate'],
                'hsn' => trim($data['hsn_code'] ?? '8471'),
                'gst' => (float)($data['gst_rate'] ?? 18.00),
                'reorder' => (int)($data['reorder_level'] ?? 10),
                'val' => $data['valuation_method'] ?? 'FIFO',
                'id' => $id
            ]);

            AuditService::log('Product', 'UPDATE', $id, null, $data);
            Session::setFlash('success', "Product '{$data['name']}' updated successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to update product: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function storeCategory(): void {
        if (!has_permission('products.update') && !has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to manage categories!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name'])) {
            Session::setFlash('error', 'Category name is required.', 'danger');
            $response->redirect(url('/products'));
        }

        $name = trim($data['name']);
        $slug = !empty($data['slug']) ? strtolower(trim($data['slug'])) : strtolower(str_replace(' ', '-', $name));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)");
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'desc' => trim($data['description'] ?? '')
            ]);
            $catId = (int)$db->lastInsertId();

            AuditService::log('Product', 'CREATE_CATEGORY', $catId, null, $data);
            Session::setFlash('success', "Category '{$name}' added successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to add category: ' . $e->getMessage(), 'danger');
        }

        $redirect = !empty($data['redirect']) ? $data['redirect'] : url('/products');
        $response->redirect($redirect);
    }

    public function deleteCategory(int $id): void {
        if (!has_permission('products.delete')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to delete categories!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $response = new Response();
        $db = Database::getInstance();

        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM categories WHERE id = :id")->execute(['id' => $id]);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            Session::setFlash('success', 'Product Category deleted successfully.', 'success');
        } catch (\Exception $e) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            Session::setFlash('error', 'Failed to delete category: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function storeBrand(): void {
        if (!has_permission('products.update') && !has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to manage brands!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name']) || empty($data['code'])) {
            Session::setFlash('error', 'Brand name and code are required.', 'danger');
            $response->redirect(url('/products'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO brands (name, code) VALUES (:name, :code)");
            $stmt->execute([
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code']))
            ]);
            $brandId = (int)$db->lastInsertId();

            AuditService::log('Product', 'CREATE_BRAND', $brandId, null, $data);
            Session::setFlash('success', "Brand '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to add brand: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function storeUnit(): void {
        if (!has_permission('products.update') && !has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to manage units!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name']) || empty($data['code'])) {
            Session::setFlash('error', 'Unit name and code are required.', 'danger');
            $response->redirect(url('/products'));
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO units (name, code, allow_decimal) VALUES (:name, :code, :dec)");
            $stmt->execute([
                'name' => trim($data['name']),
                'code' => strtoupper(trim($data['code'])),
                'dec' => !empty($data['allow_decimal']) ? 1 : 0
            ]);
            $unitId = (int)$db->lastInsertId();

            AuditService::log('Product', 'CREATE_UNIT', $unitId, null, $data);
            Session::setFlash('success', "Unit of Measurement '{$data['name']}' added successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to add unit: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function seedCatalog(): void {
        if (!has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to seed catalog!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $response = new Response();
        try {
            $res = \App\Services\SeederService::seed1000Products();
            Session::setFlash('success', $res['message'], 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Seeding failed: ' . $e->getMessage(), 'danger');
        }
        $response->redirect(url('/products'));
    }

    public function delete(int $id): void {
        if (!has_permission('products.delete')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.delete) to delete products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $response = new Response();
        $db = Database::getInstance();

        try {
            $product = Product::find($id);
            if (!$product) {
                Session::setFlash('error', 'Product not found.', 'danger');
                $response->redirect(url('/products'));
            }

            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tablesWithProduct = [
                'product_attributes', 'inventory_stocks', 'stock_transactions', 
                'grn_items', 'purchase_order_items', 'purchase_request_items', 
                'purchase_return_items', 'sales_order_items', 'supplier_products', 
                'warehouse_transfer_items'
            ];

            foreach ($tablesWithProduct as $tbl) {
                $db->prepare("DELETE FROM {$tbl} WHERE product_id = :pid")->execute(['pid' => $id]);
            }
            
            $db->prepare("DELETE FROM products WHERE id = :pid")->execute(['pid' => $id]);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            AuditService::log('Product', 'DELETE', $id, $product, null);
            Session::setFlash('success', "Product '{$product['name']}' deleted successfully!", 'success');
        } catch (\Exception $e) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            Session::setFlash('error', 'Failed to delete product: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function deleteAll(): void {
        if (!has_permission('products.delete')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.delete) to clear products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }
        $response = new Response();
        $db = Database::getInstance();

        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tablesWithProduct = [
                'product_attributes', 'inventory_stocks', 'stock_transactions', 
                'grn_items', 'purchase_order_items', 'purchase_request_items', 
                'purchase_return_items', 'sales_order_items', 'supplier_products', 
                'warehouse_transfer_items', 'products'
            ];

            foreach ($tablesWithProduct as $tbl) {
                $db->exec("DELETE FROM {$tbl}");
            }

            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            AuditService::log('Product', 'DELETE_ALL_PRODUCTS', 0, null, null);
            Session::setFlash('success', "All product records & stocks deleted successfully!", 'success');
        } catch (\Exception $e) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            Session::setFlash('error', 'Failed to clear products: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/products'));
    }

    public function attributesJson(int $id): void {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT id, name, sku, purchase_rate FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found', 'attributes' => []]);
            exit;
        }

        $attrStmt = $db->prepare("
            SELECT pa.attribute_id, pa.attribute_value, 
                   a.name AS attribute_name, a.code AS attribute_code, a.type AS attribute_type, a.options_json
            FROM product_attributes pa
            JOIN attributes a ON pa.attribute_id = a.id
            WHERE pa.product_id = :pid
            ORDER BY a.id ASC
        ");
        $attrStmt->execute(['pid' => $id]);
        $rows = $attrStmt->fetchAll();

        // Smart Fallback: If no attributes are specifically mapped to this product, fetch standard master attributes
        if (empty($rows)) {
            $fallbackStmt = $db->query("
                SELECT id AS attribute_id, '' AS attribute_value, 
                       name AS attribute_name, code AS attribute_code, type AS attribute_type, options_json
                FROM attributes
                WHERE options_json IS NOT NULL AND options_json != '' AND options_json != '[]'
                ORDER BY id ASC
                LIMIT 6
            ");
            $rows = $fallbackStmt->fetchAll();
        }

        $attributes = [];
        foreach ($rows as $r) {
            $options = [];
            // Parse actual assigned value(s) for this specific product item
            if (!empty($r['attribute_value'])) {
                if (strpos($r['attribute_value'], ',') !== false) {
                    $options = array_map('trim', explode(',', $r['attribute_value']));
                } else {
                    $options = [trim($r['attribute_value'])];
                }
            }
            $options = array_values(array_filter(array_unique($options)));

            // Fallback to options_json only if no specific value was assigned
            if (empty($options) && !empty($r['options_json'])) {
                $decoded = json_decode($r['options_json'], true);
                if (is_array($decoded)) {
                    $options = array_values(array_filter(array_unique($decoded)));
                }
            }

            $attributes[] = [
                'id' => (int)$r['attribute_id'],
                'name' => $r['attribute_name'],
                'code' => $r['attribute_code'],
                'type' => $r['attribute_type'] ?? 'select',
                'value' => $r['attribute_value'],
                'options' => $options
            ];
        }

        echo json_encode([
            'success' => true,
            'product_id' => (int)$product['id'],
            'product_name' => $product['name'],
            'purchase_rate' => (float)$product['purchase_rate'],
            'attributes' => $attributes
        ]);
        exit;
    }

    /**
     * Export all Products to CSV
     */
    public function exportCsv(): void {
        if (!has_permission('products.read')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to export products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $db = Database::getInstance();
        $products = $db->query("
            SELECT p.id, p.name, p.sku, p.barcode, c.name AS category_name, b.name AS brand_name, u.code AS unit_code,
                   p.purchase_rate, p.sale_rate, p.tax_rate, p.hsn_code, p.reorder_level,
                   COALESCE(SUM(s.qty), 0) AS total_stock,
                   IF(p.is_active = 1, 'Active', 'Inactive') AS status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN units u ON p.unit_id = u.id
            LEFT JOIN inventory_stocks s ON p.id = s.product_id
            GROUP BY p.id
            ORDER BY p.id ASC
        ")->fetchAll();

        $headers = [
            'ID', 'Product Name', 'SKU', 'Barcode', 'Category', 'Brand', 'Unit',
            'Purchase Rate (INR)', 'Sale Rate (INR)', 'Tax Rate (%)', 'HSN Code',
            'Reorder Level', 'Current Stock', 'Status'
        ];

        $data = [];
        foreach ($products as $p) {
            $data[] = [
                $p['id'],
                $p['name'],
                $p['sku'],
                $p['barcode'] ?? '',
                $p['category_name'] ?? 'General',
                $p['brand_name'] ?? 'Generic',
                $p['unit_code'] ?? 'PCS',
                $p['purchase_rate'],
                $p['sale_rate'],
                $p['tax_rate'],
                $p['hsn_code'] ?? '',
                $p['reorder_level'] ?? 5,
                $p['total_stock'],
                $p['status']
            ];
        }

        ExcelExportService::downloadCsv('enterprise_erp_products_' . date('Ymd_His'), $headers, $data);
    }

    /**
     * Download Sample CSV Template for Bulk Product Import
     */
    public function downloadTemplate(): void {
        $headers = [
            'Product Name', 'SKU', 'Barcode', 'Category', 'Brand', 'Unit',
            'Purchase Rate', 'Sale Rate', 'Tax Rate', 'Opening Stock', 'HSN Code', 'Description'
        ];

        $sampleRows = [
            [
                'Cotton Polo T-Shirt Premium', 'SKU-POLO-001', '890123456701', 'Clothing', 'Zara', 'PCS',
                '350.00', '799.00', '5.00', '50', '61091000', '100% Cotton Polo Fit T-Shirt'
            ],
            [
                'Gaming Laptop Pro 16GB', 'SKU-LAP-992', '890123456702', 'Electronics', 'Dell', 'PCS',
                '45000.00', '58999.00', '18.00', '10', '84713010', 'High performance Intel i7 Laptop'
            ],
            [
                'Industrial Power Drill 750W', 'SKU-DRL-500', '890123456703', 'Hardware & Tools', 'Bosch', 'PCS',
                '2200.00', '3499.00', '18.00', '25', '84672100', 'Heavy duty electric impact drill'
            ]
        ];

        ExcelExportService::downloadTemplate('products_import_template', $headers, $sampleRows);
    }

    /**
     * Bulk Import Products from Uploaded CSV
     */
    public function importCsv(Request $request): void {
        if (!has_permission('products.create')) {
            Session::setFlash('error', 'Access Denied: You do not have permission to import products!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            Session::setFlash('error', 'Please choose a valid .csv file to import!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $tmpFile = $_FILES['csv_file']['tmp_name'];
        $rows = ExcelExportService::parseCsv($tmpFile);

        if (empty($rows)) {
            Session::setFlash('error', 'The uploaded CSV file is empty or formatted incorrectly!', 'danger');
            (new Response())->redirect(url('/products'));
            return;
        }

        $db = Database::getInstance();
        $importedCount = 0;
        $updatedCount = 0;

        // Fetch lookup dictionaries
        $categoriesMap = [];
        foreach ($db->query("SELECT id, LOWER(name) AS lname FROM categories")->fetchAll() as $c) {
            $categoriesMap[$c['lname']] = $c['id'];
        }

        $brandsMap = [];
        foreach ($db->query("SELECT id, LOWER(name) AS lname FROM brands")->fetchAll() as $b) {
            $brandsMap[$b['lname']] = $b['id'];
        }

        $unitsMap = [];
        foreach ($db->query("SELECT id, LOWER(code) AS lcode, LOWER(name) AS lname FROM units")->fetchAll() as $u) {
            $unitsMap[$u['lcode']] = $u['id'];
            $unitsMap[$u['lname']] = $u['id'];
        }

        // Get default warehouse for opening stock
        $defaultWh = $db->query("SELECT id FROM warehouses ORDER BY id ASC LIMIT 1")->fetch();
        $defaultWhId = $defaultWh ? $defaultWh['id'] : 1;

        $db->beginTransaction();
        try {
            foreach ($rows as $r) {
                $name = trim($r['product_name'] ?? $r['name'] ?? '');
                if (empty($name)) {
                    continue;
                }

                $sku = trim($r['sku'] ?? '');
                if (empty($sku)) {
                    $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 4)) . '-' . rand(1000, 9999);
                }

                $barcode = trim($r['barcode'] ?? '');
                if (empty($barcode)) {
                    $barcode = '890' . rand(100000000, 999999999);
                }

                // Category match or auto-create
                $catName = trim($r['category'] ?? $r['category_name'] ?? 'General');
                $catKey = strtolower($catName);
                if (!isset($categoriesMap[$catKey])) {
                    $cStmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)");
                    $cStmt->execute(['name' => $catName, 'slug' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $catName)), 'desc' => 'Auto-imported Category']);
                    $newCatId = $db->lastInsertId();
                    $categoriesMap[$catKey] = $newCatId;
                }
                $categoryId = $categoriesMap[$catKey];

                // Brand match or auto-create
                $brandName = trim($r['brand'] ?? $r['brand_name'] ?? 'Generic');
                $brandKey = strtolower($brandName);
                if (!isset($brandsMap[$brandKey])) {
                    $bStmt = $db->prepare("INSERT INTO brands (name) VALUES (:name)");
                    $bStmt->execute(['name' => $brandName]);
                    $newBrandId = $db->lastInsertId();
                    $brandsMap[$brandKey] = $newBrandId;
                }
                $brandId = $brandsMap[$brandKey];

                // Unit match
                $unitCode = strtolower(trim($r['unit'] ?? $r['unit_code'] ?? 'pcs'));
                $unitId = $unitsMap[$unitCode] ?? (isset($unitsMap['pcs']) ? $unitsMap['pcs'] : 1);

                $purchaseRate = (float)($r['purchase_rate'] ?? $r['cost_price'] ?? 0);
                $saleRate = (float)($r['sale_rate'] ?? $r['selling_price'] ?? ($purchaseRate * 1.3));
                $taxRate = (float)($r['tax_rate'] ?? $r['tax'] ?? 18);
                $openingStock = (int)($r['opening_stock'] ?? $r['stock'] ?? 0);
                $hsnCode = trim($r['hsn_code'] ?? $r['hsn'] ?? '');
                $description = trim($r['description'] ?? '');

                // Check if product exists by SKU
                $chk = $db->prepare("SELECT id FROM products WHERE sku = :sku LIMIT 1");
                $chk->execute(['sku' => $sku]);
                $existing = $chk->fetch();

                if ($existing) {
                    $uStmt = $db->prepare("
                        UPDATE products 
                        SET name = :name, barcode = :barcode, category_id = :cat, brand_id = :brand, unit_id = :unit,
                            purchase_rate = :pr, sale_rate = :sr, tax_rate = :tr, hsn_code = :hsn, description = :desc, updated_at = NOW()
                        WHERE id = :id
                    ");
                    $uStmt->execute([
                        'name' => $name, 'barcode' => $barcode, 'cat' => $categoryId, 'brand' => $brandId, 'unit' => $unitId,
                        'pr' => $purchaseRate, 'sr' => $saleRate, 'tr' => $taxRate, 'hsn' => $hsnCode, 'desc' => $description,
                        'id' => $existing['id']
                    ]);
                    $productId = $existing['id'];
                    $updatedCount++;
                } else {
                    $iStmt = $db->prepare("
                        INSERT INTO products (name, sku, barcode, category_id, brand_id, unit_id, purchase_rate, sale_rate, tax_rate, hsn_code, description, is_active, created_at, updated_at)
                        VALUES (:name, :sku, :barcode, :cat, :brand, :unit, :pr, :sr, :tr, :hsn, :desc, 1, NOW(), NOW())
                    ");
                    $iStmt->execute([
                        'name' => $name, 'sku' => $sku, 'barcode' => $barcode, 'cat' => $categoryId, 'brand' => $brandId, 'unit' => $unitId,
                        'pr' => $purchaseRate, 'sr' => $saleRate, 'tr' => $taxRate, 'hsn' => $hsnCode, 'desc' => $description
                    ]);
                    $productId = $db->lastInsertId();
                    $importedCount++;
                }

                // If opening stock provided, initialize inventory
                if ($openingStock > 0) {
                    $stkChk = $db->prepare("SELECT id FROM inventory_stocks WHERE product_id = :pid AND warehouse_id = :wid LIMIT 1");
                    $stkChk->execute(['pid' => $productId, 'wid' => $defaultWhId]);
                    $stkRow = $stkChk->fetch();

                    if ($stkRow) {
                        $db->prepare("UPDATE inventory_stocks SET qty = qty + :qty, updated_at = NOW() WHERE id = :id")
                           ->execute(['qty' => $openingStock, 'id' => $stkRow['id']]);
                    } else {
                        $db->prepare("INSERT INTO inventory_stocks (product_id, warehouse_id, qty, updated_at) VALUES (:pid, :wid, :qty, NOW())")
                           ->execute(['pid' => $productId, 'wid' => $defaultWhId, 'qty' => $openingStock]);
                    }

                    // Stock transaction ledger
                    $db->prepare("
                        INSERT INTO inventory_transactions (product_id, warehouse_id, transaction_type, qty, reference_no, notes, created_at)
                        VALUES (:pid, :wid, 'OPENING', :qty, :ref, :notes, NOW())
                    ")->execute([
                        'pid' => $productId,
                        'wid' => $defaultWhId,
                        'qty' => $openingStock,
                        'ref' => 'CSV-IMPORT',
                        'notes' => 'Bulk CSV Opening Stock Import'
                    ]);
                }
            }

            $db->commit();
            AuditService::log('PRODUCT_BULK_IMPORT', "Bulk imported {$importedCount} new products, updated {$updatedCount} products via CSV.");
            Session::setFlash('success', "🎉 Successfully imported {$importedCount} new products and updated {$updatedCount} items!", 'success');
        } catch (\Exception $e) {
            $db->rollBack();
            Session::setFlash('error', 'Bulk import failed: ' . $e->getMessage(), 'danger');
        }

        (new Response())->redirect(url('/products'));
    }
}

