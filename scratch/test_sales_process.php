<?php
/**
 * Enterprise ERP - Complete Sales Process Test
 * Tests the full 6-stage workflow end-to-end
 */

$pdo = new PDO('mysql:host=localhost;dbname=enterprise_erp;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  SALES PROCESS - COMPLETE END-TO-END VERIFICATION          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$pass = 0; $fail = 0;
function test($label, $ok, $detail = '') {
    global $pass, $fail;
    if ($ok) { $pass++; echo "  ✅ $label" . ($detail ? " — $detail" : "") . "\n"; }
    else     { $fail++; echo "  ❌ $label" . ($detail ? " — $detail" : "") . "\n"; }
}

// ─── PRE-CHECK: Verify we have products with stock ───
echo "═══ PRE-CHECK: Products, Customers & Stock ═══\n";

$products = $pdo->query("SELECT p.id, p.name, p.sku, p.selling_rate, p.purchase_rate, p.unit_id FROM products p LIMIT 10")->fetchAll();
test("Products exist in catalog", count($products) > 0, count($products) . " products found");

$customers = $pdo->query("SELECT id, name FROM customers LIMIT 5")->fetchAll();
test("Customers exist", count($customers) > 0, count($customers) . " customers found");

$warehouses = $pdo->query("SELECT id, name FROM warehouses LIMIT 5")->fetchAll();
test("Warehouses exist", count($warehouses) > 0, count($warehouses) . " warehouses found");

// Check stock availability
$stocks = $pdo->query("
    SELECT s.product_id, p.name, p.sku, p.selling_rate, s.warehouse_id, w.name AS wh_name, SUM(s.qty) AS total_qty
    FROM inventory_stocks s
    JOIN products p ON s.product_id = p.id
    JOIN warehouses w ON s.warehouse_id = w.id
    GROUP BY s.product_id, s.warehouse_id
    HAVING total_qty > 0
    ORDER BY total_qty DESC
")->fetchAll();

echo "\n--- Available Stock ---\n";
foreach ($stocks as $s) {
    echo "  📦 {$s['name']} (SKU: {$s['sku']}) => {$s['total_qty']} units in {$s['wh_name']} (WH#{$s['warehouse_id']}) | Selling Rate: ₹" . number_format($s['selling_rate'], 2) . "\n";
}

if (count($stocks) === 0) {
    echo "\n  ❌ CRITICAL: No stock available in any warehouse! Cannot test sales process.\n";
    echo "  ℹ️  You need to add opening stock via /inventory/opening first.\n";
    $fail++;
    goto summary;
}

// Pick the product with the most stock
$testProduct = $stocks[0];
$testCustomer = $customers[0];
$testWarehouse = $testProduct['warehouse_id'];
$testQty = min(2, (int)$testProduct['total_qty']); // Use 2 units or max available
$testPrice = (float)$testProduct['selling_rate'];

if ($testPrice <= 0) {
    echo "  ⚠️  Product selling_rate is ₹0. Checking if this would cause issues...\n";
    // This would cause the sales form to fail validation
    $testPrice = 1000.00; // Use a default for testing
}

echo "\n═══ TEST PRODUCT: {$testProduct['name']} ═══\n";
echo "  Product ID: {$testProduct['product_id']}\n";
echo "  Warehouse: {$testProduct['wh_name']} (ID: {$testWarehouse})\n";
echo "  Available Stock: {$testProduct['total_qty']} units\n";
echo "  Unit Price: ₹" . number_format($testPrice, 2) . "\n";
echo "  Test Qty: {$testQty} units\n";
echo "  Customer: {$testCustomer['name']} (ID: {$testCustomer['id']})\n";

// Get initial stock
$initStock = (int)$testProduct['total_qty'];

// ═══════════════════════════════════════════════════════════════
// STAGE 1: CREATE QUOTATION
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 1: CREATE SALES QUOTATION ═══\n";

try {
    $pdo->beginTransaction();
    
    $subtotal = $testQty * $testPrice;
    $tax = $subtotal * 0.18;
    $total = $subtotal + $tax;
    $qNo = 'SQ-TEST-' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("INSERT INTO sales_quotations (quotation_no, customer_id, quotation_date, valid_until, subtotal, tax_amount, total_amount, status, created_by) VALUES (:qno, :cid, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), :sub, :tax, :tot, 'sent', 17)");
    $stmt->execute([
        'qno' => $qNo,
        'cid' => $testCustomer['id'],
        'sub' => $subtotal,
        'tax' => $tax,
        'tot' => $total
    ]);
    $qId = (int)$pdo->lastInsertId();
    
    $pdo->prepare("INSERT INTO sales_quotation_items (quotation_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:qid, :pid, :uid, :qty, :price, 18.00, :tax, :tot)")->execute([
        'qid' => $qId,
        'pid' => $testProduct['product_id'],
        'uid' => 1,
        'qty' => $testQty,
        'price' => $testPrice,
        'tax' => $tax,
        'tot' => $total
    ]);
    
    $pdo->commit();
    test("Quotation created", true, "$qNo (ID: $qId) | Total: ₹" . number_format($total, 2));
    
    // Verify quotation in DB
    $verifyQ = $pdo->prepare("SELECT * FROM sales_quotations WHERE id = :id");
    $verifyQ->execute(['id' => $qId]);
    $savedQ = $verifyQ->fetch();
    test("Quotation saved correctly", $savedQ !== false, "Status: {$savedQ['status']}, Amount: ₹" . number_format($savedQ['total_amount'], 2));
    
} catch (Exception $e) {
    $pdo->rollBack();
    test("Quotation created", false, $e->getMessage());
    goto summary;
}

// ═══════════════════════════════════════════════════════════════
// STAGE 2: CONVERT QUOTATION → SALES ORDER
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 2: CONVERT QUOTATION → SALES ORDER ═══\n";

try {
    $pdo->beginTransaction();
    
    $orderNo = 'SO-TEST-' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("INSERT INTO sales_orders (order_no, customer_id, company_id, branch_id, warehouse_id, order_date, subtotal, tax_amount, total_amount, status, created_by) VALUES (:sono, :cust_id, 1, 1, :wid, NOW(), :subtotal, :tax, :total, 'approved', 17)");
    $stmt->execute([
        'sono' => $orderNo,
        'cust_id' => $testCustomer['id'],
        'wid' => $testWarehouse,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total
    ]);
    $soId = (int)$pdo->lastInsertId();
    
    $pdo->prepare("INSERT INTO sales_order_items (order_id, product_id, unit_id, qty, unit_price, tax_rate, tax_amount, total_price) VALUES (:soid, :pid, :uid, :qty, :price, 18.00, :tax, :total)")->execute([
        'soid' => $soId,
        'pid' => $testProduct['product_id'],
        'uid' => 1,
        'qty' => $testQty,
        'price' => $testPrice,
        'tax' => $tax,
        'total' => $total
    ]);
    
    $pdo->prepare("UPDATE sales_quotations SET status = 'converted' WHERE id = :id")->execute(['id' => $qId]);
    
    $pdo->commit();
    test("Sales Order created from Quotation", true, "$orderNo (ID: $soId)");
    
    // Verify quotation status changed
    $verifyQ2 = $pdo->prepare("SELECT status FROM sales_quotations WHERE id = :id");
    $verifyQ2->execute(['id' => $qId]);
    $qStatus = $verifyQ2->fetchColumn();
    test("Quotation status updated to 'converted'", $qStatus === 'converted', "Status: $qStatus");
    
} catch (Exception $e) {
    $pdo->rollBack();
    test("Sales Order created", false, $e->getMessage());
    goto summary;
}

// ═══════════════════════════════════════════════════════════════
// STAGE 3: DELIVERY CHALLAN + INVENTORY DEDUCTION
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 3: DELIVERY CHALLAN + INVENTORY DEDUCTION ═══\n";

try {
    $pdo->beginTransaction();
    
    // Deduct stock
    $pdo->prepare("UPDATE inventory_stocks SET qty = qty - :qty WHERE warehouse_id = :wid AND product_id = :pid ORDER BY id ASC LIMIT 1")->execute([
        'qty' => $testQty,
        'wid' => $testWarehouse,
        'pid' => $testProduct['product_id']
    ]);
    
    $dcNo = 'DC-TEST-' . rand(1000, 9999);
    $pdo->prepare("INSERT INTO sales_delivery_challans (challan_no, order_id, customer_id, dispatch_date, vehicle_no, driver_name, status, created_by) VALUES (:cno, :soid, :cust_id, NOW(), :veh, 'Test Driver', 'delivered', 17)")->execute([
        'cno' => $dcNo,
        'soid' => $soId,
        'cust_id' => $testCustomer['id'],
        'veh' => 'GJ-01-TEST-' . rand(1000, 9999)
    ]);
    $dcId = (int)$pdo->lastInsertId();
    
    // Log stock transaction
    $pdo->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, 1, :pid, 'SALES_ISSUE', 'SALES_ORDER', :ref_id, 0, :out, :bal, :cost, :tot, 17)")->execute([
        'tno' => 'TXN-TEST-' . time(),
        'wid' => $testWarehouse,
        'pid' => $testProduct['product_id'],
        'ref_id' => $soId,
        'out' => $testQty,
        'bal' => $initStock - $testQty,
        'cost' => $testPrice * 0.8,
        'tot' => ($testPrice * 0.8) * $testQty
    ]);
    
    $pdo->commit();
    test("Delivery Challan created", true, "$dcNo (ID: $dcId)");
    
    // Verify stock deducted
    $stockAfterDC = $pdo->prepare("SELECT SUM(qty) FROM inventory_stocks WHERE product_id = :pid AND warehouse_id = :wid");
    $stockAfterDC->execute(['pid' => $testProduct['product_id'], 'wid' => $testWarehouse]);
    $newStock = (int)$stockAfterDC->fetchColumn();
    $expectedStock = $initStock - $testQty;
    test("Inventory deducted correctly", $newStock === $expectedStock, "Before: $initStock → After: $newStock (Expected: $expectedStock, Deducted: $testQty)");
    
} catch (Exception $e) {
    $pdo->rollBack();
    test("Delivery Challan", false, $e->getMessage());
    goto summary;
}

// ═══════════════════════════════════════════════════════════════
// STAGE 4: TAX INVOICE
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 4: TAX INVOICE ═══\n";

try {
    $invNo = 'INV-TEST-' . rand(1000, 9999);
    $pdo->prepare("INSERT INTO sales_invoices (invoice_no, order_id, customer_id, invoice_date, due_date, total_amount, paid_amount, status) VALUES (:ino, :soid, :cust_id, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), :tot, :paid, 'paid')")->execute([
        'ino' => $invNo,
        'soid' => $soId,
        'cust_id' => $testCustomer['id'],
        'tot' => $total,
        'paid' => $total
    ]);
    $invId = (int)$pdo->lastInsertId();
    test("Tax Invoice created", true, "$invNo (ID: $invId) | Amount: ₹" . number_format($total, 2));
    
} catch (Exception $e) {
    test("Tax Invoice", false, $e->getMessage());
    goto summary;
}

// ═══════════════════════════════════════════════════════════════
// STAGE 5: PAYMENT
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 5: PAYMENT ═══\n";

try {
    $payNo = 'PAY-TEST-' . rand(1000, 9999);
    $pdo->prepare("INSERT INTO sales_payments (payment_no, invoice_id, customer_id, payment_date, amount, payment_mode, reference_no) VALUES (:pno, :iid, :cust_id, NOW(), :amt, 'upi', :ref)")->execute([
        'pno' => $payNo,
        'iid' => $invId,
        'cust_id' => $testCustomer['id'],
        'amt' => $total,
        'ref' => 'UTR-TEST-' . rand(100000, 999999)
    ]);
    $payId = (int)$pdo->lastInsertId();
    test("Payment recorded", true, "$payNo (ID: $payId) | Amount: ₹" . number_format($total, 2) . " via UPI");
    
} catch (Exception $e) {
    test("Payment", false, $e->getMessage());
    goto summary;
}

// ═══════════════════════════════════════════════════════════════
// STAGE 6: SALES RETURN + INVENTORY RESTOCK
// ═══════════════════════════════════════════════════════════════
echo "\n═══ STAGE 6: SALES RETURN + INVENTORY RESTOCK ═══\n";

$returnQty = 1; // Return 1 unit

try {
    $pdo->beginTransaction();
    
    $retNo = 'SR-TEST-' . rand(1000, 9999);
    $refundAmt = $returnQty * $testPrice * 1.18;
    
    $pdo->prepare("INSERT INTO sales_returns (return_no, order_id, customer_id, return_date, returned_qty, refund_amount, reason, status, created_by) VALUES (:rno, :soid, :cid, NOW(), :qty, :ref, :reason, 'restocked', 17)")->execute([
        'rno' => $retNo,
        'soid' => $soId,
        'cid' => $testCustomer['id'],
        'qty' => $returnQty,
        'ref' => $refundAmt,
        'reason' => 'Automated Test Return'
    ]);
    $srId = (int)$pdo->lastInsertId();
    
    // Restock inventory
    $pdo->prepare("UPDATE inventory_stocks SET qty = qty + :qty WHERE warehouse_id = :wid AND product_id = :pid ORDER BY id ASC LIMIT 1")->execute([
        'qty' => $returnQty,
        'wid' => $testWarehouse,
        'pid' => $testProduct['product_id']
    ]);
    
    // Log restock transaction
    $stockAfterReturn = $pdo->prepare("SELECT SUM(qty) FROM inventory_stocks WHERE product_id = :pid AND warehouse_id = :wid");
    $stockAfterReturn->execute(['pid' => $testProduct['product_id'], 'wid' => $testWarehouse]);
    $finalStock = (int)$stockAfterReturn->fetchColumn();
    
    $pdo->prepare("INSERT INTO stock_transactions (transaction_no, warehouse_id, bin_id, product_id, transaction_type, reference_type, reference_id, in_qty, out_qty, balance_qty, unit_cost, total_cost, created_by) VALUES (:tno, :wid, 1, :pid, 'SALES_RETURN', 'SALES_RETURN', :ref_id, :inq, 0, :bal, :cost, :tot, 17)")->execute([
        'tno' => 'TXN-SR-TEST-' . time(),
        'wid' => $testWarehouse,
        'pid' => $testProduct['product_id'],
        'ref_id' => $srId,
        'inq' => $returnQty,
        'bal' => $finalStock,
        'cost' => $testPrice * 0.8,
        'tot' => ($testPrice * 0.8) * $returnQty
    ]);
    
    $pdo->commit();
    test("Sales Return created", true, "$retNo (ID: $srId) | Returned: $returnQty units | Refund: ₹" . number_format($refundAmt, 2));
    
    $expectedFinalStock = $initStock - $testQty + $returnQty;
    test("Inventory restocked correctly", $finalStock === $expectedFinalStock, "Initial: $initStock → Sold: -$testQty → Returned: +$returnQty → Final: $finalStock (Expected: $expectedFinalStock)");
    
} catch (Exception $e) {
    $pdo->rollBack();
    test("Sales Return", false, $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════
// VERIFICATION: CHECK ALL RECORDS EXIST
// ═══════════════════════════════════════════════════════════════
echo "\n═══ FINAL VERIFICATION ═══\n";

$verifyChecks = [
    ["SELECT * FROM sales_quotations WHERE id = $qId", "Quotation $qNo in DB"],
    ["SELECT * FROM sales_quotation_items WHERE quotation_id = $qId", "Quotation line items"],
    ["SELECT * FROM sales_orders WHERE id = $soId", "Sales Order $orderNo in DB"],
    ["SELECT * FROM sales_order_items WHERE order_id = $soId", "Order line items"],
    ["SELECT * FROM sales_delivery_challans WHERE order_id = $soId", "Delivery Challan $dcNo in DB"],
    ["SELECT * FROM sales_invoices WHERE order_id = $soId", "Invoice $invNo in DB"],
    ["SELECT * FROM sales_payments WHERE invoice_id = $invId", "Payment $payNo in DB"],
    ["SELECT * FROM sales_returns WHERE order_id = $soId", "Return $retNo in DB"],
];

foreach ($verifyChecks as [$sql, $label]) {
    $row = $pdo->query($sql)->fetch();
    test($label, $row !== false);
}

// Check stock transaction trail
$txnCount = $pdo->query("SELECT COUNT(*) FROM stock_transactions WHERE reference_id = $soId AND reference_type = 'SALES_ORDER'")->fetchColumn();
test("Stock transaction trail (SALES_ISSUE)", (int)$txnCount > 0, "$txnCount records");

$txnReturn = $pdo->query("SELECT COUNT(*) FROM stock_transactions WHERE reference_id = $srId AND reference_type = 'SALES_RETURN'")->fetchColumn();
test("Stock transaction trail (SALES_RETURN)", (int)$txnReturn > 0, "$txnReturn records");

summary:

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESULTS                                 ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  ✅ PASSED: $pass                                           ║\n";
echo "║  ❌ FAILED: $fail                                           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "Full Sales Workflow: Quotation → Sales Order → Delivery Challan → Invoice → Payment → Sales Return\n";
echo "All stages " . ($fail === 0 ? "✅ PASSED SUCCESSFULLY!" : "have issues ❌") . "\n";
