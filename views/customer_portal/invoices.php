<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-credit-card-2-front-fill text-success me-2"></i>My Invoices & Payment Gateway</h3>
        <p class="text-secondary small mb-0">View all tax invoices, payment receipts, and settle outstanding balances</p>
    </div>
    <a href="<?= url('/customer-portal/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<!-- Financial Summary Cards -->
<?php
    $totalBilled = 0;
    $totalPaid = 0;
    $totalOutstanding = 0;
    foreach ($invoices as $inv) {
        $totalBilled += (float)$inv['total_amount'];
        if ($inv['status'] === 'paid') {
            $totalPaid += (float)$inv['total_amount'];
        } else {
            $totalOutstanding += (float)$inv['total_amount'];
        }
    }
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-dark border-secondary p-3.5 shadow-sm rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold">Total Invoiced Amount</span>
                <i class="bi bi-file-earmark-dollar text-info fs-4"></i>
            </div>
            <h3 class="fw-extrabold text-white mb-0 font-monospace"><?= format_currency($totalBilled) ?></h3>
            <small class="text-info mt-1 d-block"><i class="bi bi-receipt me-1"></i><?= count($invoices) ?> Tax Invoices Issued</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary p-3.5 shadow-sm rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold">Total Paid & Settled</span>
                <i class="bi bi-check-circle-fill text-success fs-4"></i>
            </div>
            <h3 class="fw-extrabold text-success mb-0 font-monospace"><?= format_currency($totalPaid) ?></h3>
            <small class="text-success mt-1 d-block"><i class="bi bi-shield-check me-1"></i><?= count($payments) ?> Payments Recorded</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-dark border-secondary p-3.5 shadow-sm rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold">Outstanding Balance</span>
                <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
            </div>
            <h3 class="fw-extrabold text-warning mb-0 font-monospace"><?= format_currency($totalOutstanding) ?></h3>
            <small class="text-warning mt-1 d-block"><i class="bi bi-clock-history me-1"></i>Ready for 1-Click Settlement</small>
        </div>
    </div>
</div>

<!-- Invoices Table Card -->
<div class="card bg-dark border-secondary p-4 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-receipt text-warning me-2"></i>Tax Invoices</h5>
        <span class="badge bg-success px-2.5 py-1.5 fw-bold"><i class="bi bi-shield-check me-1"></i> Official Tax Invoices</span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Invoice #</th>
                    <th>Sales Order #</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total Amount (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td class="fw-bold text-warning font-monospace fs-6"><?= e($inv['invoice_no']) ?></td>
                            <td class="text-info font-monospace small"><?= e($inv['order_no']) ?></td>
                            <td class="text-secondary small"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="text-<?= (strtotime($inv['due_date']) < time() && $inv['status'] !== 'paid') ? 'danger' : 'warning' ?> small"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                            <td class="fw-bold text-success font-monospace fs-6"><?= format_currency($inv['total_amount']) ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success fw-bold px-3 py-1.5"><i class="bi bi-check-circle-fill me-1"></i> Paid & Settled</span>
                                <?php elseif ($inv['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger fw-bold px-3 py-1.5"><i class="bi bi-exclamation-circle-fill me-1"></i> Overdue</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5"><i class="bi bi-clock-fill me-1"></i> Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/customer-portal/invoices/show/' . $inv['id']) ?>" class="btn btn-info btn-sm fw-bold px-3 text-dark shadow-sm">
                                    <i class="bi bi-eye-fill me-1"></i> View Invoice
                                </a>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success ms-1.5 px-2.5 py-1.5 fw-bold small"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">
                            <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                            No tax invoices issued yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Payment History Receipts Card -->
<div class="card bg-dark border-secondary p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-journal-check text-info me-2"></i>Payment Receipts & Transaction History</h5>
        <span class="badge bg-info text-dark font-monospace fw-bold"><i class="bi bi-lock-fill me-1"></i> Encrypted Audit Trail</span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Payment Ref #</th>
                    <th>Invoice #</th>
                    <th>Payment Date</th>
                    <th>Payment Mode</th>
                    <th>Transaction Ref</th>
                    <th class="text-end">Amount Paid (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $pay): ?>
                        <tr>
                            <td class="fw-bold text-info font-monospace"><?= e($pay['payment_no']) ?></td>
                            <td class="text-warning font-monospace small"><?= e($pay['invoice_no']) ?></td>
                            <td class="text-secondary small"><?= date('d M Y, h:i A', strtotime($pay['payment_date'])) ?></td>
                            <td><span class="badge bg-dark border border-secondary text-light font-monospace text-uppercase"><?= e($pay['payment_mode']) ?></span></td>
                            <td class="font-monospace text-secondary small"><?= e($pay['reference_no']) ?></td>
                            <td class="text-end fw-extrabold text-success font-monospace fs-6"><?= format_currency($pay['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No payment transactions recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
