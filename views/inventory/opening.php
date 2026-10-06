<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-arrow-in-down-right text-success me-2"></i>Opening Stock Entry</h3>
        <p class="text-muted small mb-0">Record initial inventory balances with batch numbers and manufacturing/expiry dates</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="card p-4 shadow-sm max-w-700 mx-auto">
    <form action="<?= url('/inventory/opening/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="mb-3">
            <label class="form-label small fw-bold">Select Product *</label>
            <select name="product_id" class="form-select" required>
                <option value="">-- Choose Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Warehouse Facility *</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Select Warehouse --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Storage Bin Location Code</label>
                <input type="text" name="bin_code" class="form-control" placeholder="e.g. BIN-A1-01" value="BIN-PRIMARY">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Batch / Lot Number *</label>
                <input type="text" name="batch_no" class="form-control font-monospace" placeholder="e.g. BATCH-2026-001" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Opening Quantity *</label>
                <input type="number" name="qty" class="form-control" placeholder="100" min="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Unit Valuation Cost (₹)</label>
                <input type="number" step="0.01" name="unit_cost" class="form-control" placeholder="0.00">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Manufacturing Date (MFD)</label>
                <input type="date" name="mfd_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Expiry Date (EXP)</label>
                <input type="date" name="exp_date" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success fw-bold text-white w-100 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-check-circle-fill me-1.5"></i> Post Opening Stock Entry
        </button>
    </form>
</div>
