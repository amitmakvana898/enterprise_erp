<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-envelope-at-fill text-warning me-2"></i>System Email Outbox & Role Alerts</h3>
        <p class="text-secondary small mb-0">Live audit log of all automated workflow email notifications dispatched to role accounts</p>
    </div>
    <a href="<?= url('/dashboard') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
</div>

<!-- Email Activity Summary Cards (Structured Modern Boxes) -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Total System Emails Sent</span>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-send-check-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-success"><?= count($emails) ?> <span class="fs-6 fw-semibold text-secondary">Emails</span></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> Automated Workflow Alerts
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Registered Role Handles</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-info">8 <span class="fs-6 fw-semibold text-secondary">Roles</span></div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                    Super Admin, Sales, Procurement, WMS, Finance, QC
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Delivery Success Rate</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-warning">100%</div>
            <div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-shield-lock me-1"></i> Logged in System Outbox
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Dispatched Emails Table -->
<div class="card shadow-sm p-4 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-inbox-fill me-2 text-warning"></i>Dispatched Email Notifications</h5>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 fw-bold"><?= count($emails) ?> Total Dispatched</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>#</th>
                    <th>Recipient Role</th>
                    <th>Recipient Email</th>
                    <th>Event Trigger</th>
                    <th>Subject</th>
                    <th>Sent At</th>
                    <th>Status</th>
                    <th>Preview</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($emails)): ?>
                    <?php $idx = 1; foreach ($emails as $em): ?>
                        <tr>
                            <td><?= $idx++ ?></td>
                            <td><span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-2.5 py-1 fw-bold"><?= e($em['recipient_role']) ?></span></td>
                            <td class="fw-bold font-monospace"><?= e($em['recipient_email']) ?></td>
                            <td><span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-40 font-monospace fw-bold"><?= e($em['trigger_event']) ?></span></td>
                            <td class="fw-semibold"><?= e($em['subject']) ?></td>
                            <td class="text-secondary small"><?= date('d M Y, h:i A', strtotime($em['sent_at'])) ?></td>
                            <td><span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-2 py-1"><i class="bi bi-check-circle me-1"></i><?= e($em['status']) ?></span></td>
                            <td>
                                <button type="button" class="btn btn-outline-info btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#emailModal<?= $em['id'] ?>">
                                    <i class="bi bi-eye me-1"></i> View Email
                                </button>

                                <!-- Modal HTML Preview -->
                                <div class="modal fade" id="emailModal<?= $em['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-envelope-open me-2"></i><?= e($em['subject']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 small text-secondary">
                                                    <div><strong>To Role:</strong> <?= e($em['recipient_role']) ?> (<?= e($em['recipient_email']) ?>)</div>
                                                    <div><strong>Event Trigger:</strong> <?= e($em['trigger_event']) ?></div>
                                                    <div><strong>Sent Date:</strong> <?= date('d M Y, h:i A', strtotime($em['sent_at'])) ?></div>
                                                </div>
                                                <div class="p-3 rounded-3 border bg-body-tertiary">
                                                    <?= $em['body_html'] ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">No email notifications dispatched yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
