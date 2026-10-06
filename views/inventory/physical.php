<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-clipboard-check-fill me-2" style="color:#A855F7;"></i>Physical Verification & Audit Count</h3>
        <p class="text-muted small mb-0">Reconcile physical stock count against system records and post variance ledger adjustments</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="card p-4 shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-uppercase">
                    <th>Product & SKU</th>
                    <th>Warehouse / Bin</th>
                    <th>Batch No</th>
                    <th>System Count</th>
                    <th>Physical Audit Count</th>
                    <th>Variance Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stocks)): ?>
                    <?php foreach ($stocks as $s): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= e($s['product_name'] ?? 'N/A') ?></div>
                                <small class="text-primary font-monospace"><?= e($s['sku'] ?? 'N/A') ?></small>
                            </td>
                            <td><?= e($s['warehouse_name'] ?? 'N/A') ?> <span class="badge bg-primary ms-1"><?= e($s['bin_code'] ?? 'MAIN') ?></span></td>
                            <td class="text-warning fw-semibold font-monospace"><?= e($s['batch_no'] ?? 'DEFAULT') ?></td>
                            <td class="fw-bold text-info fs-6"><?= $s['qty'] ?? 0 ?> Units</td>
                            <form action="<?= url('/inventory/physical-verification/reconcile') ?>" method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <input type="hidden" name="stock_id" value="<?= $s['id'] ?>">
                                <td>
                                    <input type="number" name="physical_qty" class="form-control form-control-sm text-center fw-bold" style="width:110px;" value="<?= $s['qty'] ?? 0 ?>" min="0" required>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">System Audit</span>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                                        <i class="bi bi-check-lg me-1"></i> Reconcile
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No stock records found for physical verification.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
