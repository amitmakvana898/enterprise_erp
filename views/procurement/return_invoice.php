<!-- Page Header Actions -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-file-earmark-minus text-danger me-2"></i>Purchase Return Debit Note / Tax Invoice</h3>
        <p class="text-muted small mb-0">Return Reference: <strong><?= e($ret['return_no']) ?></strong> | Supplier Credit Note: <strong><?= e($ret['credit_note_no']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print();" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print Debit Note
        </button>
        <a href="<?= url('/procurement/returns/gate-pass/' . $ret['id']) ?>" class="btn btn-outline-warning btn-sm fw-bold">
            <i class="bi bi-qr-code me-1"></i> View Gate Pass
        </a>
        <a href="<?= url('/procurement/returns') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Returns
        </a>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .sidebar-wrapper, .erp-topbar, .d-print-none { display: none !important; }
    .main-content-area { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { border: 1px solid #000 !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
    .card * { color: #000 !important; }
    .badge { border: 1px solid #000 !important; color: #000 !important; }
    .table { color: #000 !important; border-color: #000 !important; }
}
</style>

<!-- Tax Debit Note Document Card -->
<div class="card shadow-sm border p-4 mb-4">
    <!-- Header: Supplier & Debit Note Details -->
    <div class="row pb-4 mb-4 border-bottom g-4">
        <div class="col-md-6">
            <span class="badge bg-danger text-white font-monospace mb-2">OFFICIAL DEBIT NOTE (TAX ADJUSTMENT)</span>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><?= e($ret['supplier_name']) ?></h4>
            <div class="text-muted small">
                <div><strong>Vendor Code:</strong> <?= e($ret['supplier_code']) ?> | <strong>GSTIN:</strong> <?= e($ret['supplier_gstin'] ?: '27AAAAA0000A1Z5') ?></div>
                <div><strong>Address:</strong> <?= e($ret['supplier_address'] ?: 'Commercial Tax Division, Industrial Estate') ?></div>
                <div><strong>Contact:</strong> <?= e($ret['supplier_phone'] ?: 'N/A') ?> | <?= e($ret['supplier_email'] ?: 'billing@vendor.com') ?></div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <h3 class="fw-extrabold text-danger mb-1 font-monospace"><?= e($ret['return_no']) ?></h3>
            <div class="text-muted small mb-2">
                <div><strong>Debit Note Date:</strong> <?= date('d M Y', strtotime($ret['return_date'])) ?></div>
                <div><strong>Supplier Credit Note Ref:</strong> <span class="text-warning font-monospace"><?= e($ret['credit_note_no']) ?></span></div>
                <div><strong>Processed By:</strong> <?= e($ret['creator_name']) ?></div>
            </div>
            <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 fw-bold">
                <i class="bi bi-check-circle-fill me-1"></i>Stock Reversed & Ledger Debit Posted
            </span>
        </div>
    </div>

    <!-- Origin & Destination Info Bar -->
    <div class="row g-3 mb-4 p-3 rounded-3 border" style="background:var(--bg-page);">
        <div class="col-md-6">
            <small class="text-muted d-block">Dispatch Origin Warehouse</small>
            <strong style="color:var(--text-primary);"><?= e($ret['warehouse_name']) ?></strong>
            <span class="text-muted small d-block"><?= e($ret['warehouse_address'] ?: 'Main Central Logistics Hub') ?></span>
        </div>
        <div class="col-md-6 text-md-end">
            <small class="text-muted d-block">Issued By / Billed Company</small>
            <strong style="color:var(--text-primary);"><?= e($ret['company_name'] ?: 'Enterprise ERP Suite') ?></strong>
            <span class="text-info small font-monospace d-block">GSTIN: <?= e($ret['company_gstin'] ?: '27BBBBB9999B1Z2') ?></span>
        </div>
    </div>

    <!-- Returned Line Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr class="text-muted small" style="background:var(--bg-page);">
                    <th style="width:40px;">#</th>
                    <th>Returned Product Description</th>
                    <th>SKU / Code</th>
                    <th>HSN / SAC</th>
                    <th>Reason for Return</th>
                    <th class="text-end">Returned Qty</th>
                    <th class="text-end">Unit Price (₹)</th>
                    <th class="text-end">Total Debit (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong style="color:var(--text-primary);"><?= e($ret['product_name'] ?: 'Catalog Item') ?></strong>
                    </td>
                    <td class="font-monospace text-info small"><?= e($ret['product_sku'] ?: 'N/A') ?></td>
                    <td class="font-monospace text-muted small"><?= e($ret['hsn_code'] ?: '8471') ?></td>
                    <td class="text-warning small"><?= e($ret['reason'] ?: 'Material Rejected during Quality Inspection') ?></td>
                    <td class="text-end fw-bold text-danger"><?= (int)($ret['qty'] ?: 1) ?> Units</td>
                    <td class="text-end font-monospace"><?= number_format($ret['unit_price'] ?: $ret['total_amount'], 2) ?></td>
                    <td class="text-end fw-bold text-danger font-monospace">₹<?= number_format($ret['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Financial Valuation & Reversal Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3 rounded-3 border h-100" style="background:var(--bg-page);">
                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-info-circle me-1.5"></i>Vendor Debit Adjustment Note</h6>
                <p class="small text-muted mb-0">
                    This Debit Note serves as formal notification that your vendor account balance has been debited by 
                    <strong style="color:var(--text-primary);">₹<?= number_format($ret['total_amount'], 2) ?></strong> due to returned/rejected inventory. 
                    Corresponding stock ledger entries have been adjusted.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3 border" style="background:var(--bg-page);">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Base Taxable Return Amount:</span>
                    <span class="font-monospace" style="color:var(--text-primary);">₹<?= number_format($ret['total_amount'] / 1.18, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>CGST Reversal (9%):</span>
                    <span class="text-warning font-monospace">₹<?= number_format(($ret['total_amount'] - ($ret['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>SGST Reversal (9%):</span>
                    <span class="text-warning font-monospace">₹<?= number_format(($ret['total_amount'] - ($ret['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top fs-5 fw-extrabold">
                    <span style="color:var(--text-primary);">Total Debit Note Valuation:</span>
                    <span class="text-danger font-monospace">₹<?= number_format($ret['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
