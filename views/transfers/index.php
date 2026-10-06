<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-truck text-primary me-2"></i>Inter-Warehouse Dispatches & In-Transit Control Hub</h3>
        <p class="text-secondary small mb-0">Warehouse-to-Warehouse stock transfers, vehicle tracking, in-transit telemetry, and bin restocking</p>
    </div>
    <a href="<?= url('/transfers/create') ?>" class="btn btn-primary fw-bold px-3 py-2 rounded-3 shadow-sm">
        <i class="bi bi-plus-lg me-1.5"></i> New Warehouse Stock Transfer
    </a>
</div>

<!-- Logistics Summary KPI Cards (Structured Boxes with Containerized Icons) -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Total Dispatches Created</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-box-arrow-left fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace" style="color: var(--text-primary);"><?= count($transfers) ?> <span class="fs-6 fw-semibold text-secondary">Transfers</span></div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-arrow-left-right me-1"></i> Multi-Warehouse Movement
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Active In-Transit Shipments</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-truck fs-5"></i>
                </div>
            </div>
            <?php
            $inTransitCount = 0;
            foreach ($transfers as $tr) {
                if ($tr['status'] === 'in_transit' || $tr['status'] === 'dispatched') $inTransitCount++;
            }
            ?>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-warning"><?= $inTransitCount ?> <span class="fs-6 fw-semibold text-secondary">In-Transit</span></div>
            <div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-clock-history me-1"></i> Awaiting Gate Receipt
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Restocked & Completed</span>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-success"><?= count($transfers) - $inTransitCount ?> <span class="fs-6 fw-semibold text-secondary">Completed</span></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-shield-check me-1"></i> Bin Quantities Updated
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Transfers Table -->
<div class="card shadow-sm p-4 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-journal-text text-warning me-2"></i>Warehouse Transfer Manifest & Audit Ledger</h5>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 fw-bold"><?= count($transfers) ?> Records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Transfer #</th>
                    <th>Source Warehouse</th>
                    <th>Destination Warehouse</th>
                    <th>Vehicle & Driver</th>
                    <th>Dispatch Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transfers)): ?>
                    <?php foreach ($transfers as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary font-monospace"><?= e($t['transfer_no']) ?></td>
                            <td><span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 px-2.5 py-1.5 fw-bold"><?= e($t['source_warehouse']) ?></span></td>
                            <td><span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-2.5 py-1.5 fw-bold"><?= e($t['dest_warehouse']) ?></span></td>
                            <td>
                                <div class="fw-bold"><i class="bi bi-truck me-1 text-warning"></i><?= e($t['vehicle_no']) ?></div>
                                <small class="text-secondary"><?= e($t['driver_name']) ?></small>
                            </td>
                            <td class="text-secondary small"><?= date('d M Y', strtotime($t['dispatch_date'])) ?></td>
                            <td>
                                <?php if ($t['status'] === 'received'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Stock Restocked
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5 fw-bold">
                                        <i class="bi bi-truck me-1"></i> In-Transit
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($t['status'] === 'in_transit' || $t['status'] === 'dispatched'): ?>
                                    <a href="<?= url('/transfers/receive/' . $t['id']) ?>" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Receive & Restock
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5"><i class="bi bi-check2-all me-1"></i> Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No stock transfers recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
