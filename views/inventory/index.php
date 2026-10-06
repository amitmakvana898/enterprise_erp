<!-- Header Banner -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-layers-fill text-danger me-2"></i>Multi-Warehouse Bin Stock Traceability</h3>
        <p class="text-secondary small mb-0">Exact item storage location mapping: Company ➔ Branch ➔ Warehouse ➔ Rack ➔ Bin Location</p>
    </div>
    <div>
        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 rounded-pill px-3 py-2 fw-bold">
            <i class="bi bi-shield-lock-fill me-1"></i>Zero Negative Stock Policy Enforced
        </span>
    </div>
</div>

<!-- Executive Action Command Grid (3 Professional Action Sections) -->
<div class="row g-3 mb-4">
    <!-- Section 1: Inward & Outward Movements -->
    <div class="col-lg-4">
        <div class="card p-3.5 h-100 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                <span class="fw-bold text-primary small text-uppercase" style="letter-spacing:0.04em;"><i class="bi bi-arrow-left-right me-1.5"></i>1. Stock Movements</span>
                <span class="badge bg-primary bg-opacity-15 text-primary" style="font-size:0.7rem;">Logistics</span>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= url('/inventory/opening') ?>" class="btn btn-outline-success btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-box-arrow-in-down-right me-2 text-success"></i>Opening Stock Entry</span>
                    <i class="bi bi-chevron-right text-success small"></i>
                </a>
                <a href="<?= url('/inventory/receive') ?>" class="btn btn-outline-info btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-box-arrow-in-down me-2 text-info"></i>Receive Inward Shipment</span>
                    <i class="bi bi-chevron-right text-info small"></i>
                </a>
                <a href="<?= url('/inventory/issue') ?>" class="btn btn-outline-warning btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-box-arrow-up-right me-2 text-warning"></i>Issue Stock Outward</span>
                    <i class="bi bi-chevron-right text-warning small"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Section 2: Reconciliations & Audits -->
    <div class="col-lg-4">
        <div class="card p-3.5 h-100 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                <span class="fw-bold text-warning small text-uppercase" style="letter-spacing:0.04em;"><i class="bi bi-sliders me-1.5"></i>2. Reconciliations</span>
                <span class="badge bg-warning bg-opacity-15 text-warning" style="font-size:0.7rem;">Control</span>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= url('/inventory/adjust') ?>" class="btn btn-outline-warning btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-sliders me-2 text-warning"></i>Adjust Stock Quantity</span>
                    <i class="bi bi-chevron-right text-warning small"></i>
                </a>
                <a href="<?= url('/inventory/damage') ?>" class="btn btn-outline-danger btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-trash-fill me-2 text-danger"></i>Damage & Scrap Log</span>
                    <i class="bi bi-chevron-right text-danger small"></i>
                </a>
                <a href="<?= url('/inventory/physical-verification') ?>" class="btn btn-outline-primary btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-clipboard-check-fill me-2 text-primary"></i>Audit Verification Take</span>
                    <i class="bi bi-chevron-right text-primary small"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Section 3: Intelligence & Financials -->
    <div class="col-lg-4">
        <div class="card p-3.5 h-100 shadow-sm border-0">
            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                <span class="fw-bold text-info small text-uppercase" style="letter-spacing:0.04em;"><i class="bi bi-graph-up me-1.5"></i>3. Financials & Audit</span>
                <span class="badge bg-info bg-opacity-15 text-info" style="font-size:0.7rem;">Analytics</span>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= url('/inventory/batches') ?>" class="btn btn-outline-secondary btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-tags-fill me-2 text-info"></i>Batches & Expiry Dates</span>
                    <i class="bi bi-chevron-right text-secondary small"></i>
                </a>
                <a href="<?= url('/inventory/ledger') ?>" class="btn btn-outline-primary btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-journal-text me-2 text-primary"></i>Stock Movement Ledger</span>
                    <i class="bi bi-chevron-right text-primary small"></i>
                </a>
                <?php if (has_permission('inventory.valuation')): ?>
                <a href="<?= url('/inventory/valuation') ?>" class="btn btn-outline-success btn-sm text-start fw-semibold py-2 px-3 d-flex align-items-center justify-content-between rounded-3">
                    <span><i class="bi bi-calculator me-2 text-success"></i>Inventory Valuation (₹)</span>
                    <i class="bi bi-chevron-right text-success small"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm p-4 border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Product & SKU</th>
                    <th>Warehouse</th>
                    <th>Bin Location</th>
                    <th>Batch Number</th>
                    <th>Manufacture & Expiry</th>
                    <th>Stock Quantity</th>
                    <th>Valuation Rate</th>
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
                            <td><span class="fw-semibold"><?= e($s['warehouse_name'] ?? 'N/A') ?></span></td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1"><i class="bi bi-geo-alt-fill me-1"></i><?= e($s['bin_code'] ?? 'MAIN') ?></span></td>
                            <td><span class="font-monospace fw-semibold text-warning"><?= e($s['batch_no'] ?? 'DEFAULT') ?></span></td>
                            <td class="text-secondary small">
                                MFD: <?= e($s['mfd_date'] ?? 'N/A') ?><br>
                                EXP: <?= e($s['exp_date'] ?? 'N/A') ?>
                            </td>
                            <td class="fw-bold text-success fs-6"><?= $s['qty'] ?? 0 ?> Units</td>
                            <td class="fw-bold font-monospace"><?= format_currency($s['valuation_rate'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No bin stock records found. Click "+ Opening Stock" above to record initial inventory balances.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
