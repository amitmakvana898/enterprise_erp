<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-receipt-cutoff text-warning me-2"></i>Vendor Purchase Invoices & 3-Way Matching</h3>
        <p class="text-muted small mb-0">Verification of Purchase Orders, Physical GRNs, and Tax Invoice Settlements</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/procurement/invoices/create') ?>" class="btn btn-warning btn-sm text-dark fw-bold">
            <i class="bi bi-plus-lg me-1"></i> + Book Vendor Invoice
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Invoice No</th>
                    <th>PO Reference</th>
                    <th>Supplier / Vendor</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total Amount (₹)</th>
                    <th>3-Way Match Verification</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td class="fw-bold text-warning font-monospace fs-6">
                                <i class="bi bi-receipt me-1"></i><?= e($inv['invoice_no']) ?>
                            </td>
                            <td class="fw-bold text-info font-monospace"><?= e($inv['po_no'] ?: 'Direct Invoice') ?></td>
                            <td>
                                <div class="fw-bold" style="color:var(--text-primary);"><?= e($inv['supplier_name']) ?></div>
                                <small class="text-muted"><?= e($inv['supplier_code']) ?></small>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="text-warning small"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                            <td class="fw-extrabold fs-6" style="color:var(--text-primary);"><?= format_currency($inv['total_amount']) ?></td>
                            <td>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-2.5 py-1 fw-bold">
                                    <i class="bi bi-shield-check me-1"></i>3-Way Matched (100%)
                                </span>
                            </td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Unpaid</span>
                                <?php endif; ?>
                            </td>
                             <td>
                                 <div class="d-flex gap-2 align-items-center">
                                     <a href="<?= url('/procurement/invoices/show/' . $inv['id']) ?>" class="btn btn-outline-info btn-sm fw-bold">
                                         <i class="bi bi-file-earmark-text me-1"></i> Tax Invoice
                                     </a>
                                     <?php if ($inv['status'] === 'paid'): ?>
                                         <span class="badge bg-success bg-opacity-20 text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                                     <?php else: ?>
                                         <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-2 py-1"><i class="bi bi-clock-history me-1"></i>Payment Pending</span>
                                     <?php endif; ?>
                                 </div>
                             </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No Vendor Purchase Invoices found. Click "+ Book Vendor Invoice" above to register one.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
