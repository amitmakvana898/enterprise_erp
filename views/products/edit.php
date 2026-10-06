<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-pencil-square text-warning me-2.5"></i>Edit Product Master</h3>
        <p class="page-header-sub">Modify SKU, Barcode, Pricing, Category, Brand, and Stock Reorder Limits</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/products') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Catalog
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4">
    <form action="<?= url('/products/update/' . $product['id']) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Product SKU *</label>
                <input type="text" name="sku" class="form-control font-monospace" value="<?= e($product['sku']) ?>" required style="text-transform: uppercase;">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold">Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Barcode / EAN</label>
                <input type="text" name="barcode" class="form-control font-monospace" value="<?= e($product['barcode']) ?>">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Category *</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Brand</label>
                <select name="brand_id" class="form-select">
                    <option value="">-- Generic --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : '' ?>>
                            <?= e($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Primary Unit *</label>
                <select name="unit_id" class="form-select" required>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $product['unit_id'] == $u['id'] ? 'selected' : '' ?>>
                            <?= e($u['name']) ?> (<?= e($u['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Purchase Rate (₹) *</label>
                <input type="number" step="0.01" name="purchase_rate" class="form-control font-monospace" value="<?= e($product['purchase_rate']) ?>" required>
                <small class="text-warning" style="font-size: 0.75rem;"><i class="bi bi-bag-check me-1"></i>Used for Vendor Purchase Orders (PO)</small>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Selling Rate (₹) *</label>
                <input type="number" step="0.01" name="selling_rate" class="form-control font-monospace" value="<?= e($product['selling_rate']) ?>" required>
                <small class="text-success" style="font-size: 0.75rem;"><i class="bi bi-cart-check me-1"></i>Used for Customer Sales Orders (SO)</small>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">HSN Code</label>
                <input type="text" name="hsn_code" class="form-control font-monospace" value="<?= e($product['hsn_code'] ?? '8471') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">GST Rate (%)</label>
                <input type="number" step="0.01" name="gst_rate" class="form-control font-monospace" value="<?= e($product['gst_rate'] ?? '18.00') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Reorder Level</label>
                <input type="number" name="reorder_level" class="form-control font-monospace" value="<?= e($product['reorder_level'] ?? '10') ?>">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label class="form-label small fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Product detailed specifications..."><?= e($product['description'] ?? '') ?></textarea>
            </div>
            <input type="hidden" name="valuation_method" value="<?= e($product['valuation_method'] ?? 'AVCO') ?>">
        </div>

        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
            <a href="<?= url('/products') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-gradient-primary btn-sm px-4 fw-bold shadow-sm">
                <i class="bi bi-check-circle-fill me-1"></i> Update Product Master
            </button>
        </div>
    </form>
</div>
