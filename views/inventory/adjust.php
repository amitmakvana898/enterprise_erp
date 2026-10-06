<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-sliders text-warning me-2"></i>Stock Adjustment (Audit Reconciliation)</h3>
        <p class="text-secondary small mb-0">Post physical count adjustments and audit corrections to bin stock</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Bin Stock</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card bg-dark border-secondary shadow-sm p-4">
            <form method="POST" action="<?= url('/inventory/adjust/store') ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label text-white fw-semibold">Select Product</label>
                    <select name="product_id" class="form-select bg-dark text-white border-secondary" required>
                        <option value="">— Select Product —</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= isset($selectedProductId) && $selectedProductId == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['name']) ?> (<?= e($p['sku']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white fw-semibold">Select Warehouse</label>
                    <select name="warehouse_id" class="form-select bg-dark text-white border-secondary" required>
                        <option value="">— Select Warehouse —</option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white fw-semibold">Adjustment Type</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeAdd" value="ADD" checked>
                            <label class="form-check-label text-success fw-bold" for="typeAdd">
                                <i class="bi bi-plus-circle me-1"></i> Increase Stock (ADD)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeSub" value="SUB">
                            <label class="form-check-label text-danger fw-bold" for="typeSub">
                                <i class="bi bi-dash-circle me-1"></i> Decrease Stock (SUB)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-white fw-semibold">Quantity to Adjust</label>
                    <input type="number" name="qty" class="form-control bg-dark text-white border-secondary" min="1" placeholder="e.g. 10" required>
                </div>

                <button type="submit" class="btn btn-warning w-100 text-dark fw-bold py-2.5">
                    <i class="bi bi-check-circle-fill me-1"></i> Post Audit Adjustment
                </button>
            </form>
        </div>
    </div>
</div>
