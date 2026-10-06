<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-trash-fill text-danger me-2"></i>Stock Damage & Scrap Write-Off</h3>
        <p class="text-muted small mb-0">Record damaged, defective, or expired stock write-offs (Strictly prevents negative stock)</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="card p-4 shadow-sm max-w-700 mx-auto">
    <form action="<?= url('/inventory/damage/store') ?>" method="POST">
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
                <label class="form-label small fw-bold">Damage Quantity to Write Off *</label>
                <input type="number" name="qty" class="form-control" placeholder="5" min="1" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Damage Reason / Scrap Notes *</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Transit liquid spill damage / expired shelf life" required></textarea>
        </div>

        <button type="submit" class="btn btn-danger fw-bold text-white w-100 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-trash-fill me-1.5"></i> Confirm Damage Write-Off
        </button>
    </form>
</div>
