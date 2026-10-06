<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-qr-code-scan text-warning me-2"></i>Barcode & QR Code Warehouse Gateway</h3>
        <p class="page-header-sub">Generate Code128 Barcodes, QR Tags, and printable warehouse shelf stickers</p>
    </div>
    <div>
        <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-30 rounded-pill px-3 py-2 fw-bold">
            <i class="bi bi-upc-scan me-1"></i>Live WMS Scanner Active
        </span>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Card 1: Print Barcode Sticker Form -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-printer-fill me-2 text-warning"></i>Generate & Print Barcode Label Tag</h5>
            <form action="<?= url('/barcode/generate') ?>" method="GET" target="_blank">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Target Product *</label>
                    <select name="product_id" id="barcodeSelect" class="form-select" required onchange="updateBarcodePreview(this)">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" data-sku="<?= e($p['sku']) ?>" data-rate="<?= e($p['selling_rate']) ?>">
                                <?= e($p['name']) ?> — SKU: <?= e($p['sku']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Sticker Format</label>
                        <select class="form-select">
                            <option value="single">Single Thermal Sticker (4" x 2")</option>
                            <option value="sheet12">A4 Sheet (12 Stickers per page)</option>
                            <option value="sheet24">A4 Sheet (24 Stickers per page)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Include Elements</label>
                        <div class="form-check mt-1">
                            <input class="form-check-input" type="checkbox" checked id="chkQr">
                            <label class="form-check-label text-secondary small" for="chkQr">Include 2D QR Code</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning px-4 py-2.5 w-100 fw-bold rounded-3 shadow-sm">
                    <i class="bi bi-printer-fill me-1.5"></i> Print Thermal Barcode Sticker Tag
                </button>
            </form>
        </div>
    </div>

    <!-- Card 2: Live Barcode & QR Code Preview Gateway -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 text-center d-flex flex-column align-items-center justify-content-center">
            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 rounded-pill px-3 py-1 fw-bold mb-2">
                Live Barcode Preview
            </span>
            <h5 class="fw-bold mb-1" id="previewTitle"><?= e($products[0]['name'] ?? 'Product Label') ?></h5>
            <small class="text-primary font-monospace d-block mb-3" id="previewSku">SKU: <?= e($products[0]['sku'] ?? 'SKU-001') ?></small>

            <!-- Dynamic Barcode SVG Simulation -->
            <div class="p-3 rounded-3 shadow-sm mb-3 border text-center" style="max-width: 320px; width: 100%; background: #ffffff; color: #0f172a;">
                <div class="font-monospace fw-bold small text-muted mb-1" style="font-size:0.75rem;">ENTERPRISE WMS TAG</div>
                <div class="d-flex align-items-center justify-content-center mb-1" id="previewBarcodeContainer" style="height: 75px;">
                    <?php if (!empty($products)): ?>
                        <?= \App\Services\BarcodeGeneratorService::generateCode128($products[0]['sku'], 50, '#000000', 'transparent') ?>
                    <?php endif; ?>
                </div>
                <div class="font-monospace fw-bold fs-6" id="previewCode" style="color:#0f172a;"><?= e($products[0]['sku'] ?? 'SKU-001') ?></div>
                <small class="d-block mt-1" style="font-size:0.75rem; color:#475569;">Mapped Bin: <strong id="previewBinSpan" style="color:#0f172a;"><?= e($products[0]['bin_code'] ?? 'BIN-A12') ?></strong></small>
            </div>

            <small class="text-secondary"><i class="bi bi-info-circle me-1"></i>Scannable by all standard 1D/2D laser scanners & Android WMS devices.</small>
        </div>
    </div>
</div>

<!-- Product Barcode Catalog Matrix Table -->
<div class="card p-4 shadow-sm border-0">
    <h5 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap-fill me-2 text-warning"></i>Product Master Barcode & QR Code Inventory Catalog</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Product Name</th>
                    <th>SKU Code</th>
                    <th>Category</th>
                    <th>Default Bin</th>
                    <th>Code128 Barcode</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="fw-bold"><?= e($p['name']) ?></td>
                        <td class="font-monospace text-primary fw-bold"><?= e($p['sku']) ?></td>
                        <td><span class="badge bg-secondary-subtle text-secondary border"><?= e($p['category_name'] ?? 'General') ?></span></td>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-geo-alt-fill me-1"></i><?= e($p['bin_code'] ?? 'BIN-A12') ?></span></td>
                        <td style="width: 200px;">
                            <div class="p-1.5 bg-white rounded border d-inline-block" style="height: 38px; max-width: 160px; overflow: hidden;">
                                <?= \App\Services\BarcodeGeneratorService::generateCode128($p['sku'], 26, '#000000', 'transparent') ?>
                            </div>
                        </td>
                        <td>
                            <a href="<?= url('/barcode/generate?product_id=' . $p['id']) ?>" target="_blank" class="btn btn-outline-warning btn-sm fw-bold">
                                <i class="bi bi-printer me-1"></i> Print Tag
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$barcodeMap = [];
foreach ($products as $p) {
    $barcodeMap[$p['id']] = [
        'name' => $p['name'],
        'sku' => $p['sku'],
        'bin' => $p['bin_code'] ?? 'BIN-A12',
        'barcode_svg' => \App\Services\BarcodeGeneratorService::generateCode128($p['sku'], 50, '#000000', 'transparent')
    ];
}
?>
<script>
const barcodeMap = <?= json_encode($barcodeMap) ?>;
function updateBarcodePreview(sel) {
    const pid = sel.value;
    const data = barcodeMap[pid];
    if (!data) return;

    document.getElementById('previewTitle').innerText = data.name;
    document.getElementById('previewSku').innerText = 'SKU: ' + data.sku;
    document.getElementById('previewCode').innerText = data.sku;
    document.getElementById('previewBinSpan').innerText = data.bin;
    document.getElementById('previewBarcodeContainer').innerHTML = data.barcode_svg;
}
</script>
