<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-file-earmark-diff-fill me-2.5" style="color:var(--warning)"></i>Request For Quotations (RFQ) & Supplier Bids</h3>
        <p class="page-header-sub">Multi-supplier quotation collection and competitive price evaluation matrix</p>
    </div>
    <?php if (has_permission('procurement.create_rfq')): ?>
    <a href="<?= url('/procurement/rfqs/create') ?>" class="btn btn-gradient-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Create New RFQ
    </a>
    <?php endif; ?>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr class="text-nowrap">
                    <th>RFQ Reference No</th>
                    <th>RFQ Description / Title</th>
                    <th>Purchase Request</th>
                    <th>Quotation Bids Received</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rfqs)): ?>
                    <?php foreach ($rfqs as $rfq): ?>
                        <?php 
                        $st = strtolower($rfq['status']);
                        $isCompleted = in_array($st, ['completed', 'closed', 'po_issued']);
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-primary"><?= e($rfq['rfq_no']) ?></div>
                                <small class="text-muted">Issued: <?= e(date('d-M-Y', strtotime($rfq['created_at']))) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:var(--text-primary);"><?= e($rfq['title']) ?></div>
                            </td>
                            <td><span class="badge bg-secondary"><?= e($rfq['request_no'] ?? 'Direct RFQ') ?></span></td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="bi bi-people-fill me-1"></i> <?= $rfq['quotation_count'] ?> Bids Received
                                </span>
                            </td>
                            <td class="text-secondary"><?= e($rfq['creator_name']) ?></td>
                            <td>
                                <?php if ($isCompleted): ?>
                                    <span class="badge bg-success text-capitalize"><i class="bi bi-check-circle-fill me-1"></i> Completed (PO Issued)</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark text-capitalize"><i class="bi bi-clock-history me-1"></i> <?= e($rfq['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($isCompleted): ?>
                                    <span class="badge bg-body-tertiary border text-muted py-2 px-3 fw-semibold">
                                        <i class="bi bi-check-all text-success me-1"></i> Process Completed
                                    </span>
                                <?php else: ?>
                                    <a href="<?= url('/procurement/quotations/compare/' . $rfq['id']) ?>" class="btn btn-outline-warning btn-sm fw-bold">
                                        <i class="bi bi-sliders me-1"></i> Compare Quotations
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2 d-block"></i>
                            No Request For Quotations found. Click <strong>Create New RFQ</strong> to issue quotation bids.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
