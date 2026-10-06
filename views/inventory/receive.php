<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-arrow-in-down text-info me-2"></i>Stock Receive (Inbound Material)</h3>
        <p class="text-muted small mb-0">Record inbound goods receipt or ad-hoc stock arrival into warehouse bins</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="card p-4 shadow-sm max-w-700 mx-auto">
    <form action="<?= url('/inventory/receive/store') ?>" method="POST">
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
                <label class="form-label small fw-bold">Destination Warehouse *</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Select Warehouse --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Batch / Lot Number</label>
                <input type="text" name="batch_no" class="form-control font-monospace" placeholder="e.g. BATCH-REC-001">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Quantity Received *</label>
                <input type="number" name="qty" class="form-control" placeholder="50" min="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Unit Purchase Rate (₹)</label>
                <input type="number" step="0.01" name="unit_cost" class="form-control" placeholder="0.00">
            </div>
        </div>

        <button type="submit" class="btn btn-info fw-bold text-dark w-100 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-box-arrow-in-down me-1.5"></i> Post Stock Receipt
        </button>
    </form>
</div>
