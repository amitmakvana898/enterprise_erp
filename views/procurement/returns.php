<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="page-title mb-1">
            <i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Purchase Returns & Supplier Credit Notes
        </h3>
        <p class="text-muted small mb-0">Supplier returns, inventory reversal, credit note issuance, and debit note audit ledger</p>
    </div>
    <?php if (has_permission('procurement.returns')): ?>
    <a href="<?= url('/procurement/returns/create') ?>" class="btn btn-danger btn-sm fw-bold px-3 py-2 rounded-3 shadow-sm">
        <i class="bi bi-plus-lg me-1.5"></i> Issue Purchase Return
    </a>
    <?php endif; ?>
</div>

<!-- Returns Table Container -->
<div class="card shadow-sm border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th class="py-3 ps-4" style="min-width: 140px;">Return # & Date</th>
                    <th class="py-3" style="min-width: 180px;">Supplier / Vendor</th>
                    <th class="py-3" style="min-width: 150px;">Credit Note #</th>
                    <th class="py-3" style="min-width: 220px;">Returned Item & Reason</th>
                    <th class="py-3 text-end" style="min-width: 140px;">Credit Amount</th>
                    <th class="py-3 text-center" style="min-width: 130px;">Stock Action</th>
                    <th class="py-3 text-center" style="min-width: 160px;">Accounting Entry</th>
                    <th class="py-3 text-end pe-4" style="min-width: 210px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($returns)): ?>
                    <?php foreach ($returns as $ret): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-danger font-monospace"><?= e($ret['return_no']) ?></div>
                                <small class="text-muted" style="font-size:0.78rem;"><i class="bi bi-calendar3 me-1"></i><?= e($ret['return_date']) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold" style="color:var(--text-primary);"><?= e($ret['supplier_name']) ?></div>
                                <small class="text-muted d-block" style="font-size:0.78rem;"><i class="bi bi-geo-alt me-1"></i>Wh: <?= e($ret['warehouse_name']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-30 px-2.5 py-1.5 font-monospace fw-bold">
                                    <i class="bi bi-file-earmark-diff me-1"></i><?= e($ret['credit_note_no'] ?? 'CN-2026-AUTO') ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:var(--text-primary);"><?= e($ret['product_name'] ?? 'Item Master') ?></div>
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 px-2 py-0.5 mt-0.5 fw-bold" style="font-size:0.75rem;">
                                    Qty: <?= $ret['qty'] ?? 1 ?> Units
                                </span>
                                <?php if (!empty($ret['reason'])): ?>
                                <div class="small text-muted mt-1 text-truncate" style="max-width: 220px;" title="<?= e($ret['reason']) ?>">
                                    <i class="bi bi-chat-left-text me-1 text-warning"></i><?= e($ret['reason']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-extrabold text-success font-monospace text-nowrap fs-6">
                                <?= format_currency($ret['total_amount']) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-40 px-2.5 py-1.5 fw-semibold">
                                    <i class="bi bi-dash-circle me-1"></i> Stock Reversed
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-2.5 py-1.5 fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Ledger Posted
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1.5">
                                    <a href="<?= url('/procurement/returns/invoice/' . $ret['id']) ?>" class="btn btn-outline-danger btn-sm px-2.5 py-1 fw-bold rounded-2 text-nowrap" title="View Purchase Return Debit Note">
                                        <i class="bi bi-file-earmark-minus me-1"></i> Debit Note
                                    </a>
                                    <a href="<?= url('/procurement/returns/gate-pass/' . $ret['id']) ?>" class="btn btn-outline-info btn-sm px-2.5 py-1 fw-bold rounded-2 text-nowrap" title="Print Return Gate Pass">
                                        <i class="bi bi-printer me-1"></i> Gate Pass
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2 d-block text-danger"></i>
                            No Purchase Return records found. Click <strong>Issue Purchase Return</strong> to record a vendor return.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
