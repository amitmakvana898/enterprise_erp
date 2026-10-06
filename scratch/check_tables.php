<?php
$pdo = new PDO('mysql:host=localhost;dbname=enterprise_erp', 'root', '');
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "=== ALL TABLES IN enterprise_erp (" . count($tables) . " total) ===\n";
foreach ($tables as $t) {
    $stmt2 = $pdo->query("SELECT COUNT(*) as c FROM `$t`");
    $count = $stmt2->fetch(PDO::FETCH_ASSOC)['c'];
    echo "  $t => $count rows\n";
}

// Check what the PurchaseController references for RFQ/GRN table names
echo "\n=== CHECKING ACTUAL TABLE NAMES IN CONTROLLERS ===\n";
$purchaseCtrl = file_get_contents(__DIR__ . '/app/Controllers/PurchaseController.php');

// Find RFQ table name
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*rfq\w*)/i', $purchaseCtrl, $rfqMatches);
echo "RFQ table references: " . implode(', ', array_unique($rfqMatches[1])) . "\n";

// Find GRN table name
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*grn\w*)/i', $purchaseCtrl, $grnMatches);
echo "GRN table references: " . implode(', ', array_unique($grnMatches[1])) . "\n";

// Find transfer table name
$transferCtrl = file_get_contents(__DIR__ . '/app/Controllers/TransferController.php');
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*transfer\w*)/i', $transferCtrl, $trMatches);
echo "Transfer table references: " . implode(', ', array_unique($trMatches[1])) . "\n";

// Find invoice items table
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*invoice_item\w*)/i', $purchaseCtrl, $iiMatches);
echo "Invoice items references: " . implode(', ', array_unique($iiMatches[1])) . "\n";

// Find sales invoice items
$salesCtrl = file_get_contents(__DIR__ . '/app/Controllers/SalesController.php');
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*invoice\w*)/i', $salesCtrl, $siMatches);
echo "Sales invoice references: " . implode(', ', array_unique($siMatches[1])) . "\n";

// Check for product_images
$productCtrl = file_get_contents(__DIR__ . '/app/Controllers/ProductController.php');
preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+(\w*image\w*)/i', $productCtrl, $imgMatches);
echo "Product image references: " . implode(', ', array_unique($imgMatches[1])) . "\n";
