<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-tags-fill text-info me-2"></i>Batch Management & Lot Tracking</h3>
        <p class="text-secondary small mb-0">Trace item stock levels, manufacturing dates, and expiry windows per batch</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/inventory/expiry') ?>" class="btn btn-outline-warning btn-sm fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Expiry Alerts</a>
        <a href="<?= url('/inventory') ?>" class="btn btn-outline-light btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Inventory</a>
    </div>
</div>

<div class="card bg-dark border-secondary p-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Batch / Lot Code</th>
                    <th>Product & SKU</th>
                    <th>Warehouse & Bin</th>
                    <th>Manufactured Date</th>
                    <th>Expiry Date</th>
                    <th>Available Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($batches)): ?>
                    <?php foreach ($batches as $b): ?>
                        <?php
                            $exp = $b['exp_date'] ? strtotime($b['exp_date']) : null;
                            $now = time();
                            $daysLeft = $exp ? ceil(($exp - $now) / 86400) : null;
                        ?>
                        <tr>
                            <td><span class="badge bg-secondary font-monospace fs-6 px-2.5 py-1.5"><?= e($b['batch_no'] ?? 'DEFAULT') ?></span></td>
                            <td>
                                <div class="fw-bold text-white"><?= e($b['product_name']) ?></div>
                                <small class="text-info"><?= e($b['sku']) ?></small>
                            </td>
                            <td><?= e($b['warehouse_name']) ?> (<span class="text-warning"><?= e($b['bin_code']) ?></span>)</td>
                            <td class="text-secondary"><?= e($b['mfd_date'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($exp): ?>
                                    <span class="fw-bold <?= $daysLeft < 0 ? 'text-danger' : ($daysLeft <= 30 ? 'text-warning' : 'text-success') ?>">
                                        <?= e($b['exp_date']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-secondary">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-extrabold text-success fs-5"><?= $b['qty'] ?> Units</td>
                            <td>
                                <?php if ($exp && $daysLeft < 0): ?>
                                    <span class="badge bg-danger px-2.5 py-1"><i class="bi bi-x-circle me-1"></i>Expired</span>
                                <?php elseif ($exp && $daysLeft <= 30): ?>
                                    <span class="badge bg-warning text-dark px-2.5 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Expiring Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-2.5 py-1"><i class="bi bi-check-circle me-1"></i>Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No batch inventory records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
