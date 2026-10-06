<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i><?= e($product['name']) ?></h3>
        <p class="text-muted small mb-0">SKU: <span class="text-primary fw-bold"><?= e($product['sku']) ?></span> • Barcode: <?= e($product['barcode']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('/inventory/adjust?product_id=' . $product['id']) ?>" class="btn btn-success btn-sm fw-semibold"><i class="bi bi-sliders me-1"></i> Adjust Quantity</a>
        <a href="<?= url('/products/edit/' . $product['id']) ?>" class="btn btn-warning btn-sm fw-semibold"><i class="bi bi-pencil-square me-1"></i> Edit Master</a>
        <a href="<?= url('/barcode?product_id=' . $product['id']) ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-qr-code me-1"></i> Printable Label</a>
        <a href="<?= url('/products') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Catalog</a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Specs & Dynamic Attributes -->
    <div class="col-md-5">
        <div class="card shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-info me-2"></i>Product Master Specs</h5>
            <table class="table table-borderless small mb-0">
                <tr><td class="text-muted">Category:</td><td class="fw-bold" style="color:var(--text-primary);"><?= e($product['category_name']) ?></td></tr>
                <tr><td class="text-muted">Brand:</td><td class="fw-bold" style="color:var(--text-primary);"><?= e($product['brand_name'] ?? 'Generic') ?></td></tr>
                <tr><td class="text-muted">Purchase Rate:</td><td class="fw-bold text-warning"><?= format_currency($product['purchase_rate']) ?></td></tr>
                <tr><td class="text-muted">Selling Rate:</td><td class="fw-bold text-success"><?= format_currency($product['selling_rate']) ?></td></tr>
                <tr><td class="text-muted">HSN Code:</td><td class="fw-bold" style="color:var(--text-primary);"><?= e($product['hsn_code']) ?></td></tr>
            </table>
        </div>

        <!-- Dynamic Attribute Engine (EAV) -->
        <div class="card shadow-sm border p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-sliders text-warning me-2"></i>Dynamic EAV Attributes</h5>
            </div>
            
            <?php if (!empty($attributes)): ?>
                <table class="table table-hover small mb-3">
                    <thead><tr class="text-muted"><th>Attribute</th><th>Assigned Value</th></tr></thead>
                    <tbody>
                        <?php foreach ($attributes as $attr): ?>
                            <tr>
                                <td class="fw-semibold text-warning"><?= e($attr['attr_name']) ?></td>
                                <td class="fw-bold" style="color:var(--text-primary);"><?= e($attr['attribute_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted small mb-0">No dynamic attributes assigned to this item yet. Click <a href="<?= url('/products/edit/' . $product['id']) ?>" class="text-warning fw-semibold">Edit Master</a> to configure attributes.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column: Bin Storage Locations & Stock Traceability -->
    <div class="col-md-7">
        <div class="card shadow-sm border p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-diagram-3-fill text-success me-2"></i>Traceability down to Bin Storage Locations</h5>
            <?php if (!empty($stocks)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead>
                            <tr class="text-muted">
                                <th>Warehouse</th>
                                <th>Bin Location</th>
                                <th>Batch No</th>
                                <th>Quantity</th>
                                <th>Valuation Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $s): ?>
                                <tr>
                                    <td class="fw-semibold" style="color:var(--text-primary);"><?= e($s['warehouse_name']) ?></td>
                                    <td><span class="badge bg-primary fs-6"><?= e($s['bin_code']) ?></span></td>
                                    <td class="text-warning"><?= e($s['batch_no']) ?></td>
                                    <td class="fw-bold text-success fs-6"><?= $s['qty'] ?></td>
                                    <td style="color:var(--text-secondary);"><?= format_currency($s['valuation_rate']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> No stock recorded in bins for this product. Receive stock via GRN or Opening Adjustment.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
