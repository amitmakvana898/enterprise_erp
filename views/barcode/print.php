<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Barcode Tag — <?= e($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .barcode-tag-card { background: #ffffff; border: 2px solid #0f172a; border-radius: 12px; padding: 24px; width: 380px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; position: relative; }
        .company-header { border-bottom: 1.5px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; color: #0f172a; text-transform: uppercase; }
        .barcode-svg svg { max-width: 100%; height: 60px; }
        .qr-svg svg { width: 90px; height: 90px; }
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .barcode-tag-card { border: 1.5px solid #000; box-shadow: none; margin: 0 auto; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="no-print mb-4 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary fw-bold px-4 py-2"><i class="bi bi-printer me-1"></i> Print Barcode Label</button>
        <button onclick="window.close()" class="btn btn-outline-secondary fw-bold px-3 py-2">Close Window</button>
    </div>

    <!-- Thermal Barcode Label Tag -->
    <div class="barcode-tag-card">
        <div class="company-header d-flex justify-content-between align-items-center">
            <span>Enterprise ERP WMS Tag</span>
            <span class="badge bg-dark text-white font-monospace">BIN-A12</span>
        </div>

        <h5 class="fw-extrabold text-dark mb-0" style="font-size: 1.15rem; line-height: 1.3;"><?= e($product['name']) ?></h5>
        <div class="font-monospace text-primary fw-bold my-1" style="font-size: 0.95rem;">SKU: <?= e($product['sku']) ?></div>

        <!-- Code128 Barcode SVG -->
        <div class="barcode-svg my-2">
            <?= $barcodeSvg ?>
        </div>

        <div class="d-flex align-items-center justify-content-center gap-3 my-2 pt-2 border-top border-secondary border-opacity-25">
            <div class="qr-svg">
                <?= $qrSvg ?>
            </div>
            <div class="text-start font-monospace" style="font-size: 0.78rem;">
                <div><strong>MRP:</strong> ₹<?= number_format($product['selling_rate'], 2) ?></div>
                <div><strong>WH:</strong> Central Warehouse</div>
                <div><strong>Bin:</strong> BIN-A12</div>
                <div class="text-success fw-bold">QC PASSED</div>
            </div>
        </div>

        <div class="small fw-bold text-muted border-top border-secondary border-opacity-25 pt-2" style="font-size: 0.72rem;">
            SCANNABLE WMS ASSET TAG • ENTERPRISE LOGISTIC SUITE
        </div>
    </div>

</body>
</html>
