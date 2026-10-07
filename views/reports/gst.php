<!-- GST & Tax Filing Summary Dashboard (GSTR-1 & GSTR-3B) -->
<div class="d-print-none mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt text-warning me-2"></i>GST & Tax Compliance Dashboard</h3>
        <p class="text-secondary small mb-0">Automated GSTR-1 Outward Supplies, Input Tax Credit (ITC) reconciliation, and HSN summary</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/reports') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold shadow-sm" style="background-image:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="bi bi-printer-fill me-1"></i> Print Tax Return
        </button>
    </div>
</div>

<!-- Tax KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3.5 h-100 shadow-sm border-0 rounded-3" style="background: var(--bg-surface);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold text-uppercase">1. Outward Taxable Value</span>
                <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary"><i class="bi bi-cart-check-fill fs-5"></i></div>
            </div>
            <div class="fs-4 fw-extrabold text-primary font-monospace"><?= format_currency($totalOutputTaxable) ?></div>
            <small class="text-secondary">Total B2B & B2C Sales</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3.5 h-100 shadow-sm border-0 rounded-3" style="background: var(--bg-surface);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold text-uppercase">2. Output GST Collected</span>
                <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-danger"><i class="bi bi-file-earmark-diff-fill fs-5"></i></div>
            </div>
            <div class="fs-4 fw-extrabold text-danger font-monospace"><?= format_currency($totalOutputTax) ?></div>
            <small class="text-danger"><i class="bi bi-arrow-up-right me-1"></i>Gross Output Liability</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3.5 h-100 shadow-sm border-0 rounded-3" style="background: var(--bg-surface);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small fw-bold text-uppercase">3. Input Tax Credit (ITC)</span>
                <div class="p-2 rounded-3 bg-success bg-opacity-10 text-success"><i class="bi bi-shield-check fs-5"></i></div>
            </div>
            <div class="fs-4 fw-extrabold text-success font-monospace"><?= format_currency($totalInputTax) ?></div>
            <small class="text-success"><i class="bi bi-arrow-down-left me-1"></i>Eligible Inward ITC</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3.5 h-100 shadow-sm border-0 rounded-3 <?= ($netTaxPayable > 0) ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10' ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase <?= ($netTaxPayable > 0) ? 'text-warning-emphasis' : 'text-success' ?>">4. Net GST Payable</span>
                <div class="p-2 rounded-3 bg-white shadow-xs <?= ($netTaxPayable > 0) ? 'text-warning' : 'text-success' ?>"><i class="bi bi-bank2 fs-5"></i></div>
            </div>
            <div class="fs-4 fw-extrabold font-monospace <?= ($netTaxPayable > 0) ? 'text-dark' : 'text-success' ?>"><?= format_currency($netTaxPayable) ?></div>
            <small class="fw-semibold <?= ($netTaxPayable > 0) ? 'text-warning-emphasis' : 'text-success' ?>">
                <?= ($netTaxPayable > 0) ? 'Output Tax - ITC Offset' : 'Tax Fully Offset by ITC' ?>
            </small>
        </div>
    </div>
</div>

<!-- Section 1: HSN Summary Table -->
<div class="card shadow-sm p-4 mb-4 border-0 rounded-3" style="background: var(--bg-surface);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-upc-scan text-primary me-2"></i>Table 12: HSN-Wise Summary of Outward Supplies</h5>
        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-1.5 fw-bold"><?= count($hsnSummary) ?> HSN Codes</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>HSN / SAC Code</th>
                    <th>Category Description</th>
                    <th class="text-end">Total Units</th>
                    <th class="text-end">Taxable Value (₹)</th>
                    <th class="text-center">Rate (%)</th>
                    <th class="text-end">CGST (₹)</th>
                    <th class="text-end">SGST (₹)</th>
                    <th class="text-end">Total GST (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($hsnSummary)): ?>
                    <?php foreach ($hsnSummary as $h): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-primary fs-6"><?= e($h['hsn_code']) ?></td>
                            <td class="fw-semibold"><?= e($h['category_name'] ?: 'General Product Category') ?></td>
                            <td class="text-end font-monospace"><?= number_format($h['total_qty']) ?></td>
                            <td class="text-end fw-bold font-monospace"><?= format_currency($h['taxable_value']) ?></td>
                            <td class="text-center"><span class="badge bg-info bg-opacity-15 text-info font-monospace"><?= $h['tax_rate'] ?>%</span></td>
                            <td class="text-end font-monospace text-secondary"><?= format_currency($h['cgst']) ?></td>
                            <td class="text-end font-monospace text-secondary"><?= format_currency($h['sgst']) ?></td>
                            <td class="text-end fw-extrabold font-monospace text-danger"><?= format_currency($h['total_tax']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No HSN records generated.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: GSTR-1 Outward B2B Invoices Table -->
<div class="card shadow-sm p-4 mb-4 border-0 rounded-3" style="background: var(--bg-surface);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text text-danger me-2"></i>GSTR-1: Outward B2B Sales Tax Invoices</h5>
        <span class="badge bg-danger bg-opacity-15 text-danger px-3 py-1.5 fw-bold"><?= count($outwardInvoices) ?> Tax Invoices</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Invoice No</th>
                    <th>Invoice Date</th>
                    <th>Customer Name</th>
                    <th>Customer GSTIN</th>
                    <th class="text-end">Taxable Value (₹)</th>
                    <th class="text-end">Tax Amount (₹)</th>
                    <th class="text-end">Total Invoice (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($outwardInvoices)): ?>
                    <?php foreach ($outwardInvoices as $inv): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-primary"><?= e($inv['invoice_no']) ?></td>
                            <td class="small font-monospace"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="fw-semibold"><?= e($inv['customer_name']) ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary font-monospace"><?= e($inv['customer_gstin'] ?: '24AAACE1234F1Z5') ?></span></td>
                            <td class="text-end font-monospace fw-bold"><?= format_currency($inv['taxable_value']) ?></td>
                            <td class="text-end font-monospace fw-bold text-danger"><?= format_currency($inv['tax_amount']) ?></td>
                            <td class="text-end font-monospace fw-extrabold text-success"><?= format_currency($inv['total_amount']) ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Settled</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No outward invoices recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 3: GSTR-2B Input Tax Credit (ITC) Inward Invoices -->
<div class="card shadow-sm p-4 border-0 rounded-3" style="background: var(--bg-surface);">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-cart-check text-success me-2"></i>GSTR-2B: Inward Procurement Invoices & Input Tax Credit (ITC)</h5>
        <span class="badge bg-success bg-opacity-15 text-success px-3 py-1.5 fw-bold"><?= count($inwardInvoices) ?> Vendor Invoices</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Vendor Invoice No</th>
                    <th>Date</th>
                    <th>Supplier / Vendor</th>
                    <th>Supplier GSTIN</th>
                    <th class="text-end">Taxable Value (₹)</th>
                    <th class="text-end">ITC Claimable (₹)</th>
                    <th class="text-end">Total Bill (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inwardInvoices)): ?>
                    <?php foreach ($inwardInvoices as $pinv): ?>
                        <tr>
                            <td class="fw-bold font-monospace text-info"><?= e($pinv['invoice_no']) ?></td>
                            <td class="small font-monospace"><?= date('d M Y', strtotime($pinv['invoice_date'])) ?></td>
                            <td class="fw-semibold"><?= e($pinv['supplier_name']) ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary font-monospace"><?= e($pinv['supplier_gstin'] ?: '27BBBBC5678G2Z1') ?></span></td>
                            <td class="text-end font-monospace fw-bold"><?= format_currency($pinv['taxable_value']) ?></td>
                            <td class="text-end font-monospace fw-bold text-success"><?= format_currency($pinv['tax_amount']) ?></td>
                            <td class="text-end font-monospace fw-extrabold text-primary"><?= format_currency($pinv['total_amount']) ?></td>
                            <td><span class="badge bg-success bg-opacity-15 text-success">Verified ITC</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No inward procurement invoices recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
