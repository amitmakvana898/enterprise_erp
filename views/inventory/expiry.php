<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-clock-history text-warning me-2"></i>Expiry Management & Real-Time Alerts</h3>
        <p class="text-secondary small mb-0">Monitor expired stock write-offs and batches expiring within 30 days</p>
    </div>
    <a href="<?= url('/inventory/batches') ?>" class="btn btn-outline-light btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Batches</a>
</div>

<div class="row g-4 mb-4">
    <!-- Expired Stock (Critical Alert) -->
    <div class="col-12">
        <div class="card bg-dark border-danger p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>Expired Stock Batches (Requires Damage Write-Off)</h5>
                <span class="badge bg-danger px-3 py-1.5 fs-6"><?= count($expired) ?> Expired</span>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Batch No</th>
                            <th>Product Name & SKU</th>
                            <th>Warehouse</th>
                            <th>Expiry Date</th>
                            <th>Stock Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expired)): ?>
                            <?php foreach ($expired as $ex): ?>
                                <tr>
                                    <td><span class="badge bg-danger font-monospace"><?= e($ex['batch_no']) ?></span></td>
                                    <td class="fw-bold text-white"><?= e($ex['product_name']) ?> <small class="text-info d-block"><?= e($ex['sku']) ?></small></td>
                                    <td><?= e($ex['warehouse_name']) ?> (<?= e($ex['bin_code']) ?>)</td>
                                    <td class="text-danger fw-bold"><?= e($ex['exp_date']) ?></td>
                                    <td class="fw-extrabold text-danger fs-5"><?= $ex['qty'] ?> Units</td>
                                    <td>
                                        <a href="<?= url('/inventory/damage') ?>" class="btn btn-outline-danger btn-sm fw-bold">
                                            <i class="bi bi-trash-fill me-1"></i> Write Off Scrap
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-3 text-success"><i class="bi bi-check-circle me-1"></i>No expired stock batches found!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Expiring Soon (< 30 Days) -->
    <div class="col-12">
        <div class="card bg-dark border-warning p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-warning mb-0"><i class="bi bi-hourglass-split me-2"></i>Batches Expiring Within 30 Days</h5>
                <span class="badge bg-warning text-dark px-3 py-1.5 fs-6"><?= count($expiringSoon) ?> Near Expiry</span>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>Batch No</th>
                            <th>Product Name & SKU</th>
                            <th>Warehouse</th>
                            <th>Expiry Date</th>
                            <th>Days Remaining</th>
                            <th>Stock Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expiringSoon)): ?>
                            <?php foreach ($expiringSoon as $es): ?>
                                <tr>
                                    <td><span class="badge bg-warning text-dark font-monospace"><?= e($es['batch_no']) ?></span></td>
                                    <td class="fw-bold text-white"><?= e($es['product_name']) ?> <small class="text-info d-block"><?= e($es['sku']) ?></small></td>
                                    <td><?= e($es['warehouse_name']) ?> (<?= e($es['bin_code']) ?>)</td>
                                    <td class="text-warning fw-bold"><?= e($es['exp_date']) ?></td>
                                    <td><span class="badge bg-warning text-dark fw-bold"><?= $es['days_left'] ?> Days Left</span></td>
                                    <td class="fw-extrabold text-white fs-5"><?= $es['qty'] ?> Units</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-3 text-secondary">No stock expiring within 30 days.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
