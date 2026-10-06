<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-bell-fill text-warning me-2"></i>Real-Time Notification & Workflow Alert Hub</h3>
        <p class="text-secondary small mb-0">Automated system telemetry, inventory re-order alerts, and pending approval notifications</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="alert('All system notifications marked as read.');" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-check2-all me-1"></i> Mark All as Read
        </button>
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-primary btn-sm fw-bold"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
</div>

<!-- Notification Category Summary Cards (Structured Modern Boxes) -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Low Stock Re-order Warnings</span>
                <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-danger"><?= count($lowStock) ?> <span class="fs-6 fw-semibold text-secondary">Alerts</span></div>
            <div>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-box-seam me-1"></i> Requires Replenishment
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Pending PR Approvals</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-warning"><?= count($pendingPrs) ?> <span class="fs-6 fw-semibold text-secondary">Pending</span></div>
            <div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-hourglass-split me-1"></i> Procurement Review Needed
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Uncollected Customer Invoices</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-info"><?= count($pendingInvoices) ?> <span class="fs-6 fw-semibold text-secondary">Invoices</span></div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-receipt me-1"></i> Settlement Clearance Pending
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Live Alert Streams -->
<div class="row g-4">
    <!-- 1. Low Stock & Re-order Alerts -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <h5 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Critical Low Stock Warnings</h5>
            <div class="d-flex flex-column gap-2.5">
                <?php if (!empty($lowStock)): ?>
                    <?php foreach ($lowStock as $ls): ?>
                        <div class="p-3 rounded-3 border border-danger border-opacity-25 bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold fs-6"><?= e($ls['name']) ?></div>
                                <small class="text-danger font-monospace">SKU: <?= e($ls['sku']) ?> | Current: <strong><?= $ls['current_stock'] ?> Units</strong> (Reorder Level: <?= $ls['reorder_level'] ?>)</small>
                            </div>
                            <a href="<?= url('/procurement/requests/create') ?>" class="btn btn-danger btn-sm fw-bold px-3 text-nowrap shadow-sm">
                                <i class="bi bi-cart-plus me-1"></i> Reorder
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-success"><i class="bi bi-check-circle fs-4 me-1 d-block mb-1"></i>All bin stock levels are healthy!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2. Pending Requisition Approvals -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-warning me-2"></i>Pending Purchase Requisition Approvals</h5>
            <div class="d-flex flex-column gap-2.5">
                <?php if (!empty($pendingPrs)): ?>
                    <?php foreach ($pendingPrs as $pr): ?>
                        <div class="p-3 rounded-3 border border-warning border-opacity-25 bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold fs-6"><?= e($pr['request_no']) ?> — <?= e($pr['department']) ?></div>
                                <small class="text-warning">Requested by: <?= e($pr['requester_name'] ?: 'Requisitioner') ?> | Priority: <strong class="text-uppercase"><?= e($pr['priority']) ?></strong></small>
                            </div>
                            <a href="<?= url('/procurement/requests') ?>" class="btn btn-warning text-dark btn-sm fw-bold px-3 text-nowrap shadow-sm">
                                <i class="bi bi-eye me-1"></i> Review PR
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-success"><i class="bi bi-check-circle fs-4 me-1 d-block mb-1"></i>No pending purchase requisitions!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
