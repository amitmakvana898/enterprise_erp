<!-- Vendor Statement of Account / Payables Ledger -->
<div class="d-print-none mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <a href="<?= url('/suppliers') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold shadow-sm" style="background-image:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="bi bi-printer-fill me-1"></i> Print / Download Vendor Statement
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 p-md-5 rounded-3 print-card" style="background: var(--bg-surface);">
    <!-- Company Header & Title -->
    <div class="row align-items-center pb-4 mb-4 border-bottom">
        <div class="col-sm-7">
            <h3 class="fw-extrabold text-primary mb-1">
                <i class="bi bi-building-fill me-2"></i><?= e(get_setting('company_name', 'ENTERPRISE ERP CORP')) ?>
            </h3>
            <div class="small text-secondary">
                <div><?= e(get_setting('company_address', 'Silicon Valley Tech Park, Sector 4, Gandhinagar')) ?></div>
                <div>GSTIN: <strong><?= e(get_setting('company_gstin', '24AAACE1234F1Z5')) ?></strong> | Email: <?= e(get_setting('company_email', 'accounts@enterprise-erp.com')) ?></div>
            </div>
        </div>
        <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
            <span class="badge bg-dark px-3 py-1.5 fs-6 fw-bold text-uppercase" style="letter-spacing:0.05em;">Vendor Statement of Account</span>
            <div class="small text-secondary mt-1">Generated: <strong><?= date('d M Y, h:i A') ?></strong></div>
            <div class="small text-secondary">Ledger Nature: <strong>Accounts Payable (AP)</strong></div>
        </div>
    </div>

    <!-- Vendor Details & Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3.5 rounded-3 bg-light border h-100" style="background: rgba(248,250,252,0.8);">
                <span class="badge bg-primary text-uppercase mb-2 font-monospace"><?= e($supplier['code']) ?></span>
                <h5 class="fw-bold mb-1" style="color:var(--text-primary);"><?= e($supplier['name']) ?></h5>
                <div class="small text-secondary">
                    <?php if (!empty($supplier['gstin'])): ?>
                        <div>Vendor GSTIN: <strong class="text-dark"><?= e($supplier['gstin']) ?></strong></div>
                    <?php endif; ?>
                    <div>Email: <strong class="text-dark"><?= e($supplier['email']) ?></strong></div>
                    <div>Phone: <strong class="text-dark"><?= e($supplier['phone'] ?: 'N/A') ?></strong></div>
                    <div>Bank Details: <span class="text-dark"><?= e($supplier['bank_name'] ? ($supplier['bank_name'] . ' - ' . $supplier['bank_account']) : 'Standard Direct Transfer') ?></span></div>
                    <div>Payment Terms: <span class="badge bg-secondary-subtle text-secondary"><?= e($supplier['payment_terms'] ?? 'Net 30') ?></span></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row g-2 h-100">
                <div class="col-6">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-xs">
                        <small class="text-secondary fw-semibold">Total Invoiced (Payable)</small>
                        <div class="fs-5 fw-extrabold text-danger mt-1"><?= format_currency($totalPayable) ?></div>
                        <small class="text-muted small">Purchases & Goods Invoiced</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-xs">
                        <small class="text-secondary fw-semibold">Total Disbursed (Paid)</small>
                        <div class="fs-5 fw-extrabold text-success mt-1"><?= format_currency($totalPaid) ?></div>
                        <small class="text-muted small">Payments & Debit Notes</small>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="p-3 rounded-3 border <?= ($netPayable > 0) ? 'bg-warning bg-opacity-10 border-warning border-opacity-30' : 'bg-success bg-opacity-10 border-success border-opacity-30' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="fw-bold <?= ($netPayable > 0) ? 'text-warning' : 'text-success' ?>">NET PAYABLE OUTSTANDING TO VENDOR</small>
                                <div class="fs-4 fw-extrabold <?= ($netPayable > 0) ? 'text-warning' : 'text-success' ?>"><?= format_currency($netPayable) ?></div>
                            </div>
                            <div>
                                <?php if ($netPayable <= 0): ?>
                                    <span class="badge bg-success px-3 py-2 fw-bold"><i class="bi bi-check-all me-1"></i> FULLY PAID</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-3 py-2 fw-bold"><i class="bi bi-clock-history me-1"></i> OUTSTANDING PAYABLE</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Transaction Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-uppercase" style="letter-spacing:0.03em;">
                    <th style="width: 110px;">Date</th>
                    <th style="width: 150px;">Ref #</th>
                    <th style="width: 170px;">Transaction Type</th>
                    <th>Particulars / Description</th>
                    <th class="text-end" style="width: 130px;">Payable (+₹)</th>
                    <th class="text-end" style="width: 130px;">Disbursed (-₹)</th>
                    <th class="text-end" style="width: 150px;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td class="small font-monospace"><?= date('d M Y', strtotime($t['date'])) ?></td>
                            <td class="fw-bold font-monospace text-primary small"><?= e($t['ref_no']) ?></td>
                            <td>
                                <span class="badge <?= ($t['credit'] > 0) ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' ?> fw-semibold">
                                    <?= e($t['type']) ?>
                                </span>
                            </td>
                            <td class="small text-secondary"><?= e($t['description']) ?></td>
                            <td class="text-end fw-bold font-monospace <?= ($t['credit'] > 0) ? 'text-danger' : 'text-muted' ?>">
                                <?= ($t['credit'] > 0) ? format_currency($t['credit']) : '-' ?>
                            </td>
                            <td class="text-end fw-bold font-monospace <?= ($t['debit'] > 0) ? 'text-success' : 'text-muted' ?>">
                                <?= ($t['debit'] > 0) ? format_currency($t['debit']) : '-' ?>
                            </td>
                            <td class="text-end fw-extrabold font-monospace <?= ($t['balance'] > 0) ? 'text-warning' : 'text-success' ?>">
                                <?= format_currency($t['balance']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No transactions recorded for this supplier yet.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end text-uppercase small">Total Payables & Disbursements:</td>
                    <td class="text-end text-danger font-monospace"><?= format_currency($totalPayable) ?></td>
                    <td class="text-end text-success font-monospace"><?= format_currency($totalPaid) ?></td>
                    <td class="text-end font-monospace fs-6 <?= ($netPayable > 0) ? 'text-warning' : 'text-success' ?>"><?= format_currency($netPayable) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatory Section -->
    <div class="row pt-4 mt-2 border-top">
        <div class="col-sm-7">
            <h6 class="fw-bold small text-uppercase mb-2"><i class="bi bi-shield-check me-1 text-primary"></i>Audit Verification Note</h6>
            <div class="small text-secondary">
                This document is a computer-generated statement of accounts payable verified against approved Purchase Orders and Goods Receipt Notes (GRN).
            </div>
        </div>
        <div class="col-sm-5 text-sm-end mt-4 mt-sm-0">
            <div class="small text-muted mb-5">For <?= e(get_setting('company_name', 'ENTERPRISE ERP CORP')) ?></div>
            <div class="fw-bold border-top d-inline-block pt-1 px-4 small">Accounts Payable Desk</div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .sidebar, .navbar, .header, .d-print-none, .btn { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card.print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>
