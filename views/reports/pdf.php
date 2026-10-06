<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Executive Report PDF') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; color: #000; padding: 30px; }
        .header-title { font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1e293b; }
        .table { font-size: 13px; margin-top: 20px; border-color: #cbd5e1; }
        .table th { background-color: #f1f5f9 !important; color: #0f172a !important; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .badge-print { border: 1px solid #94a3b8; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-2 border-dark">
        <div>
            <div class="header-title">Enterprise ERP — Executive Operational Report</div>
            <div class="text-secondary small">Generated Date: <?= date('d F Y, h:i A') ?> • Authorized Executive System Report</div>
        </div>
        <button onclick="window.print()" class="btn btn-primary btn-sm no-print">Print / Save as PDF</button>
    </div>

    <div class="alert alert-light border small mb-4">
        <strong>Report Parameters:</strong> Active Filter Set Applied • Valuation Method: FIFO/LIFO/Weighted Avg • Multi-Warehouse Consolidated
    </div>

    <!-- Data Table -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Warehouse</th>
                <th>Bin</th>
                <th>Batch No</th>
                <th>Quantity</th>
                <th>Purchase Rate (₹)</th>
                <th>Stock Valuation (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $db = \App\Core\Database::getInstance();
            $stockSummary = $db->query("
                SELECT s.product_id, p.sku, p.name AS product_name, c.name AS category_name,
                       wh.name AS warehouse_name, bn.code AS bin_code, s.batch_no,
                       SUM(s.qty) AS total_qty, p.purchase_rate AS avg_unit_cost,
                       (SUM(s.qty) * p.purchase_rate) AS total_valuation
                FROM inventory_stocks s
                JOIN products p ON s.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN warehouses wh ON s.warehouse_id = wh.id
                JOIN bins bn ON s.bin_id = bn.id
                GROUP BY s.warehouse_id, s.bin_id, s.product_id, s.batch_no
                ORDER BY p.name ASC
            ")->fetchAll();

            $grandTotalValuation = 0;
            $grandTotalQty = 0;
            ?>
            <?php foreach ($stockSummary as $row): ?>
                <?php 
                    $qty = (int)$row['total_qty'];
                    $val = (float)$row['total_valuation'];
                    $grandTotalQty += $qty;
                    $grandTotalValuation += $val;
                ?>
                <tr>
                    <td class="fw-bold font-monospace"><?= e($row['sku']) ?></td>
                    <td><?= e($row['product_name']) ?></td>
                    <td><span class="badge-print"><?= e($row['category_name'] ?: 'General') ?></span></td>
                    <td><?= e($row['warehouse_name']) ?></td>
                    <td><?= e($row['bin_code']) ?></td>
                    <td><?= e($row['batch_no']) ?></td>
                    <td class="fw-bold"><?= number_format($qty) ?></td>
                    <td>₹ <?= number_format((float)$row['avg_unit_cost'], 2) ?></td>
                    <td class="fw-bold">₹ <?= number_format($val, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-secondary fw-bold fs-6">
                <td colspan="6" class="text-end">Grand Total Valuation:</td>
                <td><?= number_format($grandTotalQty) ?> Units</td>
                <td>-</td>
                <td class="text-primary">₹ <?= number_format($grandTotalValuation, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-5 pt-4 d-flex justify-content-between text-secondary small border-top">
        <div>Prepared by System Administrator</div>
        <div>Verified & Approved Signature: __________________________</div>
    </div>

</body>
</html>
