<!-- Customer Quotations Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-file-earmark-check-fill text-warning me-2"></i>My Price Quotations & Estimates</h3>
        <p class="text-secondary small mb-0">Review company-issued price quotes and place official Sales Orders with 1 click</p>
    </div>
    <a href="<?= url('/customer-portal/dashboard') ?>" class="btn btn-outline-light btn-sm fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="card bg-dark border-secondary p-4 shadow-sm">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Quotation Reference</th>
                    <th>Date Issued</th>
                    <th>Validity Expiry</th>
                    <th>Total Contract Quote</th>
                    <th>Status</th>
                    <th class="text-end">1-Click Order Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($quotations)): ?>
                    <?php foreach ($quotations as $q): ?>
                        <tr>
                            <td class="fw-bold text-info font-monospace fs-6">
                                <i class="bi bi-file-earmark-text me-1.5"></i><?= e($q['quotation_no']) ?>
                            </td>
                            <td class="text-secondary small"><?= e($q['quotation_date']) ?></td>
                            <td class="text-warning small"><?= e($q['valid_until']) ?></td>
                            <td class="fw-bold text-success font-monospace fs-5"><?= format_currency($q['total_amount']) ?></td>
                            <td>
                                <?php 
                                $st = strtolower(trim($q['status'] ?? ''));
                                $isRfq = (strpos($q['quotation_no'], 'REQ-') === 0);
                                if ($st === 'converted'): ?>
                                    <span class="badge bg-success px-3 py-1.5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Accepted & Sales Order Auto-Placed</span>
                                <?php elseif ($st === 'dispatched'): ?>
                                    <span class="badge bg-primary px-3 py-1.5 fw-bold"><i class="bi bi-truck me-1"></i> Approved & Dispatched</span>
                                <?php elseif ($st === 'rejected'): ?>
                                    <span class="badge bg-danger px-3 py-1.5 fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Quotation Rejected</span>
                                <?php elseif ($st === 'pending_quote' || $st === 'pending' || $st === '' || ($isRfq && $st !== 'active' && $st !== 'sent')): ?>
                                    <span class="badge bg-warning text-dark px-3 py-1.5 fw-bold"><i class="bi bi-hourglass-split me-1"></i> Sent to Admin / Awaiting Quote</span>
                                <?php else: ?>
                                    <span class="badge bg-info px-3 py-1.5 fw-bold"><i class="bi bi-check2-square me-1"></i> Quote Ready for Order</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1.5 align-items-center">
                                    <a href="<?= url('/customer-portal/quotations/show/' . $q['id']) ?>" class="btn btn-info btn-sm fw-bold px-3 text-dark shadow-sm">
                                        <i class="bi bi-eye-fill me-1"></i> View Quotation
                                    </a>
                                    <?php if ($st === 'converted'): ?>
                                        <span class="badge bg-success px-3 py-1.5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Ordered</span>
                                    <?php elseif ($st === 'dispatched'): ?>
                                        <span class="badge bg-success px-3 py-1.5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                                    <?php elseif ($st === 'rejected'): ?>
                                        <span class="badge bg-danger px-3 py-1.5 fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>
                                    <?php elseif ($st === 'pending_quote' || $st === 'pending' || $st === '' || ($isRfq && $st !== 'active' && $st !== 'sent')): ?>
                                        <span class="badge bg-warning text-dark px-3 py-1.5 fw-bold"><i class="bi bi-hourglass-split me-1"></i> Awaiting Quote</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No quotations issued yet for your customer account.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
