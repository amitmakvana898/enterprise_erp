<?php $logs = $logs ?? []; $selectedModule = $selectedModule ?? ''; ?>

<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-shield-check text-info me-2"></i>Real-Time Enterprise Audit Trail</h3>
        <p class="text-secondary small mb-0">Immutable system-wide activity log, security traceability & user action history</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold"><i class="bi bi-printer me-1"></i> Print Audit Trail</button>
        <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 p-2 px-3 fs-6 d-flex align-items-center gap-2">
            <span class="spinner-grow spinner-grow-sm text-info" role="status"></span> Live Security Audit Active
        </span>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between border-0">
            <div>
                <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-2">Total Audit Events</div>
                <div class="fs-3 fw-extrabold text-body-emphasis font-monospace"><?= number_format(count($logs)) ?></div>
            </div>
            <small class="text-info mt-2 d-block"><i class="bi bi-shield-check me-1"></i>Immutable Ledger</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between border-0">
            <div>
                <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-2">Latest Audited Event</div>
                <div class="fs-6 fw-bold text-primary text-truncate" title="<?= !empty($logs) ? e($logs[0]['module'] . ' / ' . $logs[0]['action']) : 'N/A' ?>">
                    <?= !empty($logs) ? e($logs[0]['module'] . ' / ' . $logs[0]['action']) : 'N/A' ?>
                </div>
            </div>
            <small class="text-secondary mt-2 d-block"><i class="bi bi-clock-history me-1"></i><?= !empty($logs) ? date('d M Y, h:i A', strtotime($logs[0]['created_at'])) : 'Live' ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between border-0">
            <div>
                <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-2">Active Audit Session</div>
                <div class="fs-6 fw-bold text-warning text-truncate" title="<?= e(auth_user()['name'] ?? 'Super Admin') ?>">
                    <?= e(auth_user()['name'] ?? 'Super Admin') ?>
                </div>
            </div>
            <small class="text-warning mt-2 d-block"><i class="bi bi-person-badge me-1"></i>Role: <?= e(auth_user()['role_name'] ?? 'super_admin') ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between border-0">
            <div>
                <div class="text-secondary small fw-bold text-uppercase tracking-wider mb-2">Security Enforcement</div>
                <div class="fs-6 fw-bold text-success text-uppercase"><i class="bi bi-shield-lock-fill me-1"></i>High Immutability</div>
            </div>
            <small class="text-success mt-2 d-block"><i class="bi bi-key-fill me-1"></i>41 RBAC Scopes Enforced</small>
        </div>
    </div>
</div>

<!-- Module Filter Tabs -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= url('/audit') ?>" class="btn btn-sm <?= empty($selectedModule) ? 'btn-primary' : 'btn-outline-secondary' ?> fw-bold px-3 py-1.5 rounded-pill">
        All Modules
    </a>
    <a href="<?= url('/audit?module=Authentication') ?>" class="btn btn-sm <?= $selectedModule === 'Authentication' ? 'btn-primary' : 'btn-outline-secondary' ?> fw-bold px-3 py-1.5 rounded-pill">
        <i class="bi bi-shield-lock me-1"></i> Authentication
    </a>
    <a href="<?= url('/audit?module=Procurement') ?>" class="btn btn-sm <?= $selectedModule === 'Procurement' ? 'btn-primary' : 'btn-outline-secondary' ?> fw-bold px-3 py-1.5 rounded-pill">
        <i class="bi bi-cart-check me-1"></i> Procurement
    </a>
    <a href="<?= url('/audit?module=Sales') ?>" class="btn btn-sm <?= $selectedModule === 'Sales' ? 'btn-primary' : 'btn-outline-secondary' ?> fw-bold px-3 py-1.5 rounded-pill">
        <i class="bi bi-receipt me-1"></i> Sales & Invoicing
    </a>
    <a href="<?= url('/audit?module=Inventory') ?>" class="btn btn-sm <?= $selectedModule === 'Inventory' ? 'btn-primary' : 'btn-outline-secondary' ?> fw-bold px-3 py-1.5 rounded-pill">
        <i class="bi bi-boxes me-1"></i> Inventory & Bins
    </a>
</div>

<!-- Audit Logs Table -->
<div class="card shadow-sm p-4 border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>ID & Timestamp</th>
                    <th>User & Role</th>
                    <th>Module</th>
                    <th>Action Executed</th>
                    <th>Record ID</th>
                    <th>IP Address & Device</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-info font-monospace">#<?= $log['id'] ?></div>
                                <small class="text-secondary" style="font-size:0.78rem;"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= e($log['user_name'] ?: 'System / Automated') ?></div>
                                <small class="text-secondary"><?= e($log['user_email'] ?: 'system@enterprise-erp.com') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-2.5 py-1 fw-bold">
                                    <?= e($log['module']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-40 px-2.5 py-1 font-monospace fw-bold">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td class="font-monospace fw-bold"><?= $log['record_id'] ? '#' . $log['record_id'] : '—' ?></td>
                            <td>
                                <div class="font-monospace small text-secondary"><i class="bi bi-hdd-network me-1"></i><?= e($log['ip_address'] ?: '127.0.0.1') ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No audit logs recorded for this filter.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
