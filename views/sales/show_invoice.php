<!-- Page Header Actions -->
<div class="page-header d-print-none">
    <div>
        <h3 class="page-header-title"><i class="bi bi-receipt-cutoff text-warning me-2.5"></i>Customer Tax Invoice Details</h3>
        <p class="page-header-sub">Invoice Reference: <strong class="font-monospace"><?= e($inv['invoice_no']) ?></strong> | Status: <strong class="text-uppercase text-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?>"><?= e($inv['status']) ?></strong></p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <button onclick="window.print();" class="btn btn-warning text-dark btn-sm fw-bold shadow-sm">
            <i class="bi bi-printer me-1"></i> Print / Save PDF Invoice
        </button>
        <a href="<?= url('/emails') ?>" class="btn btn-outline-info btn-sm fw-bold" title="View Dispatched Email Notification Logs">
            <i class="bi bi-envelope-at me-1"></i> Email Outbox
        </a>
        <a href="<?= url('/sales/invoices') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales Invoices
        </a>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .sidebar, .topbar, .d-print-none { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { border: 1px solid #000 !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
    .text-muted, .text-secondary { color: #555 !important; }
    .badge { border: 1px solid #000 !important; color: #000 !important; }
    .table { color: #000 !important; border-color: #000 !important; }
}
</style>

<?php if ($inv['status'] !== 'paid'): ?>
<!-- Outstanding Payment Action Banner -->
<div class="card p-3 mb-4 border-warning bg-warning bg-opacity-10 d-print-none">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-circle bg-warning text-dark">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0" style="color:var(--text-primary);">Payment Collection Pending for <?= e($inv['invoice_no']) ?></h5>
                <small class="text-warning fw-semibold">Outstanding Customer Balance: <strong><?= format_currency($inv['total_amount'] - $inv['paid_amount']) ?></strong> • Due Date: <?= date('d M Y', strtotime($inv['due_date'])) ?></small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Customer Tax Invoice Card Document -->
<div class="card shadow-sm border p-4 mb-4">
    <!-- Header: Customer & Company GSTIN Details -->
    <div class="row pb-4 mb-4 border-bottom g-4">
        <div class="col-md-6">
            <span class="badge bg-warning text-dark font-monospace mb-2">ORIGINAL SALES TAX INVOICE</span>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><?= e($inv['customer_name']) ?></h4>
            <div class="text-muted small">
                <div><strong>Customer Code:</strong> <?= e($inv['customer_code'] ?: 'CUST-001') ?> | <strong>GSTIN:</strong> <?= e($inv['customer_gstin'] ?: '27AAAAA0000A1Z5') ?></div>
                <div><strong>Address:</strong> <?= e($inv['customer_address'] ?: 'Commercial Business District, Tax Division') ?></div>
                <div><strong>Contact:</strong> <?= e($inv['customer_phone'] ?: 'N/A') ?> | <?= e($inv['customer_email'] ?: 'billing@customer.com') ?></div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <h3 class="fw-extrabold text-warning mb-1 font-monospace"><?= e($inv['invoice_no']) ?></h3>
            <div class="text-muted small mb-2">
                <div><strong>Invoice Date:</strong> <?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                <div><strong>Payment Due Date:</strong> <span class="text-warning fw-semibold"><?= date('d M Y', strtotime($inv['due_date'])) ?></span></div>
                <div><strong>Payment Terms:</strong> Net 30 Days</div>
            </div>
            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-3 py-1.5 fw-bold">
                <i class="bi bi-shield-check me-1"></i>O2C Verified (Sales Order + Delivery Challan + Tax Invoice)
            </span>
        </div>
    </div>

    <!-- O2C Linked References Bar -->
    <div class="row g-3 mb-4 p-3 rounded-3 bg-body-tertiary border">
        <div class="col-md-4">
            <small class="text-muted d-block">Linked Sales Order (SO)</small>
            <strong class="text-info font-monospace fs-6"><?= e($inv['order_no'] ?: 'Direct Sales Order') ?></strong>
            <?php if (!empty($inv['order_date'])): ?>
                <span class="text-muted small d-block">(Date: <?= date('d M Y', strtotime($inv['order_date'])) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Linked Delivery Challan (DC)</small>
            <strong class="text-primary font-monospace fs-6"><?= e($inv['challan_no'] ?: 'DC-DISPATCHED') ?></strong>
            <?php if (!empty($inv['vehicle_no'])): ?>
                <span class="text-warning small d-block">(Vehicle: <?= e($inv['vehicle_no']) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Dispatching Warehouse / Billed From</small>
            <strong style="color:var(--text-primary);"><?= e($inv['warehouse_name'] ?: 'Central Operations Warehouse') ?></strong>
            <span class="text-muted small d-block"><?= e($inv['company_name'] ?: 'Enterprise ERP Suite') ?></span>
        </div>
    </div>

    <!-- Billed Line Items Table -->
    <div class="table-responsive mb-4 border rounded-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary small text-nowrap">
                    <th style="width:40px;">#</th>
                    <th>Product / Item Description</th>
                    <th>SKU / Code</th>
                    <th>HSN / SAC</th>
                    <th class="text-end">Billed Qty</th>
                    <th class="text-end">Unit Price (₹)</th>
                    <th class="text-end">GST Tax</th>
                    <th class="text-end">Total Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $idx = 1; foreach ($items as $item): ?>
                        <tr>
                            <td><?= $idx++ ?></td>
                            <td>
                                <strong style="color:var(--text-primary);"><?= e($item['product_name']) ?></strong>
                            </td>
                            <td class="font-monospace text-info small"><?= e($item['product_sku']) ?></td>
                            <td class="font-monospace text-muted small"><?= e($item['hsn_code'] ?: '8471') ?></td>
                            <td class="text-end fw-bold text-success"><?= (int)$item['qty'] ?> <?= e($item['unit_name'] ?: 'Units') ?></td>
                            <td class="text-end font-monospace"><?= number_format($item['unit_price'], 2) ?></td>
                            <td class="text-end text-warning font-monospace"><?= number_format($item['tax_rate'] ?? 18.00, 2) ?>% (₹<?= number_format($item['tax_amount'] ?? 0, 2) ?>)</td>
                            <td class="text-end fw-bold font-monospace" style="color:var(--text-primary);">₹<?= number_format($item['total_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Line items referenced directly from Sales Order <?= e($inv['order_no']) ?>. Total Taxable Invoice Value: ₹<?= number_format($inv['total_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Valuation & Tax Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3 rounded-3 bg-body-tertiary border h-100">
                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-bank me-1.5"></i>Company Bank Accounts for Remittance</h6>
                <div class="small text-muted">
                    <div><strong>Bank Name:</strong> HDFC Bank Corporate Branch</div>
                    <div><strong>Account No:</strong> <span class="font-monospace fw-semibold" style="color:var(--text-primary);">99881122334455</span></div>
                    <div><strong>IFSC Code:</strong> <span class="font-monospace text-info fw-semibold">HDFC0001234</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3 bg-body-tertiary border">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Taxable Subtotal (Base Amount):</span>
                    <span class="font-monospace fw-semibold" style="color:var(--text-primary);">₹<?= number_format(!empty($inv['so_subtotal']) ? $inv['so_subtotal'] : $inv['total_amount'] / 1.18, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>CGST (9%):</span>
                    <span class="text-warning font-monospace fw-semibold">₹<?= number_format((!empty($inv['so_tax']) ? $inv['so_tax'] : $inv['total_amount'] - ($inv['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>SGST (9%):</span>
                    <span class="text-warning font-monospace fw-semibold">₹<?= number_format((!empty($inv['so_tax']) ? $inv['so_tax'] : $inv['total_amount'] - ($inv['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top fs-5 fw-extrabold" style="color:var(--text-primary);">
                    <span>Grand Total Invoice Amount:</span>
                    <span class="text-success font-monospace">₹<?= number_format($inv['total_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2 pt-2 border-top small">
                    <span class="text-muted">Amount Collected / Paid:</span>
                    <span class="fw-bold text-success">₹<?= number_format($inv['paid_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1 small">
                    <span class="text-muted">Outstanding Due Balance:</span>
                    <span class="fw-bold text-danger">₹<?= number_format($inv['total_amount'] - $inv['paid_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Payment Audit History -->
    <?php if (!empty($payments)): ?>
        <div class="border-top pt-4">
            <h6 class="fw-bold text-success mb-3"><i class="bi bi-clock-history me-1.5"></i>Customer Payment Receipt History</h6>
            <div class="table-responsive border rounded-3">
                <table class="table table-hover align-middle table-sm small mb-0">
                    <thead class="table-light">
                        <tr class="text-secondary text-nowrap">
                            <th>Payment No</th>
                            <th>Date</th>
                            <th>Payment Mode</th>
                            <th>Reference / UTR No</th>
                            <th class="text-end">Amount Paid (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td class="font-monospace text-primary fw-semibold"><?= e($p['payment_no']) ?></td>
                                <td><?= date('d M Y, h:i A', strtotime($p['payment_date'])) ?></td>
                                <td class="text-capitalize text-warning fw-semibold"><?= str_replace('_', ' ', e($p['payment_mode'])) ?></td>
                                <td class="font-monospace text-info"><?= e($p['reference_no']) ?></td>
                                <td class="text-end fw-bold text-success">₹<?= number_format($p['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
