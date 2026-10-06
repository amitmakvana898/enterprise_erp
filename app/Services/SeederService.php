<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class SeederService {
    public static function seed1000Products(): array {
        $db = Database::getInstance();
        $startTime = microtime(true);

        // Ensure categories exist
        $categoriesData = [
            ['name' => 'Electronics & Computers', 'slug' => 'electronics-computers', 'desc' => 'Laptops, Desktops, SSDs, Monitors, Keyboards & Accessories'],
            ['name' => 'Smartphones & Mobiles', 'slug' => 'smartphones-mobiles', 'desc' => 'Flagship Smartphones, Tablets, Wearables & Mobile Accessories'],
            ['name' => 'Clothing & Apparel', 'slug' => 'clothing-apparel', 'desc' => 'T-Shirts, Formal Shirts, Denim Jeans, Jackets & Garments'],
            ['name' => 'Footwear & Shoes', 'slug' => 'footwear-shoes', 'desc' => 'Sports Shoes, Leather Formal Shoes, Sneakers & Boots'],
            ['name' => 'Industrial Tools & Hardware', 'slug' => 'industrial-hardware', 'desc' => 'Power Tools, Hex Bolts, Copper Wires, Industrial Components'],
            ['name' => 'Home & Furniture', 'slug' => 'home-furniture', 'desc' => 'Office Ergonomic Chairs, Refrigerators, Washing Machines'],
            ['name' => 'FMCG & Healthcare', 'slug' => 'fmcg-healthcare', 'desc' => 'Personal Care, Health Monitors, Medical Supplies']
        ];

        foreach ($categoriesData as $c) {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (:n, :s, :d) ON DUPLICATE KEY UPDATE description = VALUES(description)");
            $stmt->execute(['n' => $c['name'], 's' => $c['slug'], 'd' => $c['desc']]);
        }

        // Fetch category map
        $categories = $db->query("SELECT id, name, slug FROM categories")->fetchAll();
        $catMap = [];
        foreach ($categories as $cat) {
            $catMap[strtolower($cat['name'])] = $cat['id'];
            $catMap[strtolower($cat['slug'])] = $cat['id'];
        }

        // Ensure brands exist
        $brandsData = [
            ['name' => 'Samsung', 'code' => 'SAMSUNG'],
            ['name' => 'Apple', 'code' => 'APPLE'],
            ['name' => 'Dell', 'code' => 'DELL'],
            ['name' => 'HP', 'code' => 'HP'],
            ['name' => 'Logitech', 'code' => 'LOGITECH'],
            ['name' => 'Nike', 'code' => 'NIKE'],
            ['name' => 'Adidas', 'code' => 'ADIDAS'],
            ['name' => 'Puma', 'code' => 'PUMA'],
            ['name' => 'Bosch', 'code' => 'BOSCH'],
            ['name' => 'Philips', 'code' => 'PHILIPS'],
            ['name' => 'LG Electronics', 'code' => 'LG'],
            ['name' => 'Sony', 'code' => 'SONY'],
            ['name' => 'Ray-Ban', 'code' => 'RAYBAN'],
            ['name' => 'Generic Enterprise', 'code' => 'GENERIC']
        ];

        foreach ($brandsData as $b) {
            $stmt = $db->prepare("INSERT INTO brands (name, code) VALUES (:n, :c) ON DUPLICATE KEY UPDATE code = VALUES(code)");
            $stmt->execute(['n' => $b['name'], 'c' => $b['code']]);
        }

        $brands = $db->query("SELECT id, name FROM brands")->fetchAll();
        $brandIds = array_column($brands, 'id');

        // Units
        $units = $db->query("SELECT id FROM units")->fetchAll(\PDO::FETCH_COLUMN);
        $unitId = $units[0] ?? 1;

        // Bins & Warehouses
        $bins = $db->query("
            SELECT bn.id AS bin_id, r.warehouse_id 
            FROM bins bn 
            JOIN racks r ON bn.rack_id = r.id
        ")->fetchAll();

        if (empty($bins)) {
            // Create fallback warehouse, rack, bin if missing
            $db->exec("INSERT IGNORE INTO warehouses (code, name, location) VALUES ('WH-MAIN', 'Main Distribution Hub', 'Mumbai')");
            $whId = (int)$db->lastInsertId() ?: 1;
            $db->exec("INSERT IGNORE INTO racks (warehouse_id, code, name) VALUES ({$whId}, 'RACK-A1', 'Storage Rack A1')");
            $rackId = (int)$db->lastInsertId() ?: 1;
            $db->exec("INSERT IGNORE INTO bins (rack_id, code, name) VALUES ({$rackId}, 'BIN-A1-01', 'Default Storage Bin')");
            $binId = (int)$db->lastInsertId() ?: 1;
            $bins = [['bin_id' => $binId, 'warehouse_id' => $whId]];
        }

        // Attributes map
        $attributes = $db->query("SELECT id, code FROM attributes")->fetchAll(\PDO::FETCH_KEY_PAIR); // code => id

        // Begin Seeding 1,000 Products
        Database::beginTransaction();

        $catalogTemplates = [
            // Category: Electronics & Computers
            [
                'cat_key' => 'electronics-computers',
                'hsn' => '84713010',
                'gst' => 18.00,
                'items' => [
                    'Dell XPS 15 Laptop i7 16GB', 'MacBook Pro 16 M3 Max', 'Lenovo ThinkPad X1 Carbon', 'HP Spectre x360 Convertible',
                    'Asus ROG Strix Gaming Laptop', 'Acer Predator Helios 300', 'Logitech MX Master 3S Mouse', 'Keychron K2 Mechanical Keyboard',
                    'Samsung 27 Inch 4K Monitor', 'LG UltraGear 34 Inch Curved Monitor', 'Crucial 1TB NVMe M.2 SSD', 'Samsung 980 PRO 2TB SSD',
                    'Corsair Vengeance 32GB DDR5 RAM', 'NVIDIA GeForce RTX 4090 GPU', 'Sony WH-1000XM5 Wireless Headphones', 'Bose QuietComfort Earbuds'
                ]
            ],
            // Category: Smartphones & Mobiles
            [
                'cat_key' => 'smartphones-mobiles',
                'hsn' => '85171200',
                'gst' => 18.00,
                'items' => [
                    'Apple iPhone 15 Pro Max 256GB', 'Samsung Galaxy S24 Ultra 512GB', 'Google Pixel 8 Pro 128GB', 'OnePlus 12 5G 256GB',
                    'Xiaomi 14 Ultra 512GB', 'Apple iPad Pro 12.9 M2', 'Samsung Galaxy Tab S9 Ultra', 'Apple Watch Ultra 2 GPS',
                    'Samsung Galaxy Watch 6 Classic', 'Garmin Fenix 7X Solar Watch', 'Anker 65W GaN Fast Charger', 'Belkin MagSafe Wireless Pad'
                ]
            ],
            // Category: Clothing & Apparel
            [
                'cat_key' => 'clothing-apparel',
                'hsn' => '61091000',
                'gst' => 12.00,
                'items' => [
                    'Men Combed Cotton Crew Neck T-Shirt', 'Men Slim Fit Oxford Cotton Shirt', 'Men Regular Fit Denim Jeans', 'Men Heavyweight Fleece Hoodie',
                    'Men Tailored Slim Fit Blazer', 'Women Floral Print Summer Cotton Dress', 'Women High-Waist Denim Jeans', 'Women Casual Cotton Blouse',
                    'Unisex Athletic Dry-Fit Running Tee', 'Unisex Fleece Sweatpants Tracksuit', 'Men Premium Thermal Innerwear', 'Women Designer Silk Saree'
                ]
            ],
            // Category: Footwear & Shoes
            [
                'cat_key' => 'footwear-shoes',
                'hsn' => '64039990',
                'gst' => 12.00,
                'items' => [
                    'Nike Air Zoom Pegasus Running Shoes', 'Adidas Ultraboost Light Running Shoes', 'Puma Velocity Nitro 2 Sneakers', 'Asics Gel-Kayano 30 Shoes',
                    'Clarks Premium Leather Oxford Shoes', 'Timberland Waterproof Chelsea Boots', 'Converse Chuck Taylor All Star High Top', 'Vans Old Skool Skate Shoes'
                ]
            ],
            // Category: Industrial Tools & Hardware
            [
                'cat_key' => 'industrial-hardware',
                'hsn' => '84672100',
                'gst' => 18.00,
                'items' => [
                    'Bosch Professional 850W Angle Grinder', 'DeWalt 20V MAX Cordless Drill Kit', 'Makita Rotary Hammer Drill 800W', 'Stanley 150-Piece Mechanic Tool Set',
                    'Stainless Steel Hex Bolts M8 x 50mm (Pack 100)', 'Industrial Deep Groove Ball Bearing 6204', 'Flexible Copper Wires 2.5 sq mm (100m Roll)', 'Heavy Duty PVC Conduit Pipe 25mm'
                ]
            ],
            // Category: Home & Furniture
            [
                'cat_key' => 'home-furniture',
                'hsn' => '94033010',
                'gst' => 18.00,
                'items' => [
                    'Ergonomic High-Back Mesh Office Chair', 'Teak Wood Executive Study & Computer Desk', 'Modular 5-Tier Wooden Bookcase Shelf',
                    'LG 450L Double Door Inverter Refrigerator', 'IFB 8kg Front Load Washing Machine', 'Philips XXL Digital Air Fryer 1.4kg'
                ]
            ],
            // Category: FMCG & Healthcare
            [
                'cat_key' => 'fmcg-healthcare',
                'hsn' => '90189099',
                'gst' => 12.00,
                'items' => [
                    'Omron Automatic Digital Blood Pressure Monitor', 'Braun Thermoscan 7 Infrared Ear Thermometer', '3M N95 Respirator Masks (Box of 20)',
                    'Vitamin C 1000mg Health Supplements', 'L\'Oreal Paris Total Repair 5 Shampoo 650ml', 'Nivea Nourishing Body Milk Lotion 400ml'
                ]
            ]
        ];

        $insertedCount = 0;
        $targetCount = 1000;
        $batchNumber = 1;

        $stmtProd = $db->prepare("
            INSERT INTO products (
                sku, barcode, qr_code, name, description, category_id, brand_id, unit_id, 
                purchase_rate, selling_rate, hsn_code, gst_rate, reorder_level, valuation_method, status
            ) VALUES (
                :sku, :barcode, :qr, :name, :desc, :cat_id, :brand_id, :unit_id, 
                :prate, :srate, :hsn, :gst, :reorder, :val, 'active'
            )
        ");

        $stmtStock = $db->prepare("
            INSERT INTO inventory_stocks (warehouse_id, bin_id, product_id, batch_no, qty, valuation_rate)
            VALUES (:wid, :bid, :pid, :batch, :qty, :cost)
            ON DUPLICATE KEY UPDATE qty = qty + :qty2
        ");

        $stmtAttr = $db->prepare("
            INSERT INTO product_attributes (product_id, attribute_id, attribute_value)
            VALUES (:pid, :aid, :val)
            ON DUPLICATE KEY UPDATE attribute_value = VALUES(attribute_value)
        ");

        // Loop until 1,000 products are generated
        while ($insertedCount < $targetCount) {
            foreach ($catalogTemplates as $tmpl) {
                if ($insertedCount >= $targetCount) break;

                $catId = $catMap[$tmpl['cat_key']] ?? reset($catMap);
                $hsn = $tmpl['hsn'];
                $gst = $tmpl['gst'];

                foreach ($tmpl['items'] as $baseItemName) {
                    if ($insertedCount >= $targetCount) break;

                    $insertedCount++;
                    $skuNumber = str_pad($insertedCount, 4, '0', STR_PAD_LEFT);
                    
                    // Generate unique item variation
                    $variantSuffixes = ['Standard Edition', 'Pro Version', 'Enterprise Pack', 'Gen 2', 'Plus Model', 'Ultra Series', 'V2 Edition'];
                    $variant = $variantSuffixes[$insertedCount % count($variantSuffixes)];
                    
                    $productName = $baseItemName . ' (' . $variant . ')';
                    $sku = 'PROD-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $baseItemName), 0, 4)) . '-' . $skuNumber;
                    $barcode = '890' . sprintf('%010d', 1000000000 + $insertedCount);
                    $qrCode = 'QR-' . $sku;
                    
                    $randomBrandId = $brandIds[array_rand($brandIds)];
                    
                    // Realistic rates
                    $basePrice = rand(250, 85000);
                    $margin = rand(15, 35) / 100.0;
                    $purchaseRate = round($basePrice, 2);
                    $sellingRate = round($basePrice * (1 + $margin), 2);
                    $reorderLevel = rand(10, 50);

                    $desc = "Enterprise Master Catalog Item: {$productName}. Premium grade specifications for commercial distribution and warehouse fulfillment.";

                    // Insert Product
                    $stmtProd->execute([
                        'sku' => $sku,
                        'barcode' => $barcode,
                        'qr' => $qrCode,
                        'name' => $productName,
                        'desc' => $desc,
                        'cat_id' => $catId,
                        'brand_id' => $randomBrandId,
                        'unit_id' => $unitId,
                        'prate' => $purchaseRate,
                        'srate' => $sellingRate,
                        'hsn' => $hsn,
                        'gst' => $gst,
                        'reorder' => $reorderLevel,
                        'val' => 'FIFO'
                    ]);

                    $productId = (int)$db->lastInsertId();

                    // Seed Stock across random bins
                    $chosenBin = $bins[array_rand($bins)];
                    $stockQty = rand(25, 450);

                    $stmtStock->execute([
                        'wid' => $chosenBin['warehouse_id'],
                        'bid' => $chosenBin['bin_id'],
                        'pid' => $productId,
                        'batch' => 'BATCH-ENTERPRISE-' . date('Ymd'),
                        'qty' => $stockQty,
                        'cost' => $purchaseRate,
                        'qty2' => $stockQty
                    ]);

                    // Seed Dynamic Attributes for ALL product categories
                    if ($tmpl['cat_key'] === 'clothing-apparel') {
                        if (isset($attributes['size'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['size'], 'val' => 'S, M, L, XL, XXL']);
                        }
                        if (isset($attributes['color'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['color'], 'val' => 'Black, White, Navy Blue, Crimson Red, Heather Grey']);
                        }
                    } elseif ($tmpl['cat_key'] === 'electronics-computers' || $tmpl['cat_key'] === 'smartphones-mobiles') {
                        if (isset($attributes['ram'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['ram'], 'val' => '8 GB, 16 GB, 32 GB, 64 GB']);
                        }
                        if (isset($attributes['storage'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['storage'], 'val' => '256 GB SSD, 512 GB SSD, 1 TB SSD']);
                        }
                        if (isset($attributes['processor'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['processor'], 'val' => 'Intel Core i5, Intel Core i7, Apple M3']);
                        }
                        if (isset($attributes['color'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['color'], 'val' => 'Black, Silver, Space Grey']);
                        }
                    } elseif ($tmpl['cat_key'] === 'footwear-shoes') {
                        if (isset($attributes['shoe_size'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['shoe_size'], 'val' => 'UK 7, UK 8, UK 9, UK 10, UK 11']);
                        }
                        if (isset($attributes['color'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['color'], 'val' => 'Black/White, Triple Black, Navy Blue']);
                        }
                    } else {
                        if (isset($attributes['color'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['color'], 'val' => 'Black, Silver, Navy Blue, White']);
                        }
                        if (isset($attributes['size'])) {
                            $stmtAttr->execute(['pid' => $productId, 'aid' => $attributes['size'], 'val' => 'Standard Pack, Bulk Pack']);
                        }
                    }
                }
            }
        }

        Database::commit();
        $executionTime = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'count' => $insertedCount,
            'execution_time' => $executionTime,
            'message' => "Successfully seeded {$insertedCount} Enterprise Master Products, Bin Stocks & Attributes in {$executionTime}s!"
        ];
    }
}
