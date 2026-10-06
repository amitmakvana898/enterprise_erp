<!-- Page Header Actions -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-warning me-2"></i>My Tax Invoice Details</h3>
        <p class="text-muted small mb-0">Invoice Reference: <strong><?= e($inv['invoice_no']) ?></strong> | Status: <strong class="text-uppercase text-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?>"><?= e($inv['status']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print();" class="btn btn-warning text-dark btn-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print / Save PDF Invoice
        </button>
        <a href="<?= url('/customer-portal/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
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
    .text-white, .text-light, .text-secondary, .text-muted { color: #000 !important; }
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
                <h5 class="fw-bold mb-0">Payment Action Required for <?= e($inv['invoice_no']) ?></h5>
                <small class="text-warning">Outstanding Balance: <strong><?= format_currency($inv['total_amount'] - $inv['paid_amount']) ?></strong> • Due Date: <?= date('d M Y', strtotime($inv['due_date'])) ?></small>
            </div>
        </div>
        <div>
            <a href="<?= url('/customer-portal/invoices/pay/' . $inv['id']) ?>" 
               class="btn btn-success btn-lg fw-bold px-4 shadow-sm"
               onclick="if(confirm('Authorize payment of <?= format_currency($inv['total_amount']) ?> for this Tax Invoice?')){ this.style.pointerEvents='none'; this.style.opacity='0.6'; return true; } else return false;">
                <i class="bi bi-credit-card-2-front-fill me-2"></i> Pay Now (₹<?= number_format($inv['total_amount'], 2) ?>)
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Invoice Paid Success Banner -->
<div class="card p-3 mb-4 border-success bg-success bg-opacity-10 d-print-none">
    <div class="d-flex align-items-center gap-3">
        <div class="p-2.5 rounded-circle bg-success text-white">
            <i class="bi bi-check-circle-fill fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-0">Invoice Fully Settled (100% Paid)</h5>
            <small class="text-success">Thank you! Your payment of <strong><?= format_currency($inv['total_amount']) ?></strong> has been received and processed.</small>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Customer Tax Invoice Card Document -->
<div class="card p-4 shadow-sm mb-4">
    <!-- Header: Customer & Company GSTIN Details -->
    <div class="row pb-4 mb-4 border-bottom g-4">
        <div class="col-md-6">
            <span class="badge bg-warning text-dark font-monospace mb-2">ORIGINAL SALES TAX INVOICE</span>
            <h4 class="fw-bold mb-1"><?= e($inv['customer_name']) ?></h4>
            <div class="text-muted small">
                <div><strong>Customer Code:</strong> <?= e($inv['customer_code'] ?: 'CUST-001') ?> | <strong>GSTIN:</strong> <?= e($inv['customer_gstin'] ?: '27AAAAA0000A1Z5') ?></div>
                <div><strong>Address:</strong> <?= e($inv['customer_address'] ?: 'Commercial Business District, Tax Division') ?></div>
                <div><strong>Contact:</strong> <?= e($inv['customer_phone'] ?: 'N/A') ?> | <?= e($inv['customer_email'] ?: 'billing@customer.com') ?></div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <h3 class="fw-bold text-warning mb-1 font-monospace"><?= e($inv['invoice_no']) ?></h3>
            <div class="text-muted small mb-2">
                <div><strong>Invoice Date:</strong> <?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                <div><strong>Payment Due Date:</strong> <span class="text-warning"><?= date('d M Y', strtotime($inv['due_date'])) ?></span></div>
                <div><strong>Payment Terms:</strong> Net 30 Days</div>
            </div>
            <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 fw-bold">
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
            <strong class="fw-bold"><?= e($inv['warehouse_name'] ?: 'Central Operations Warehouse') ?></strong>
            <span class="text-muted small d-block"><?= e($inv['company_name'] ?: 'Enterprise ERP Suite') ?></span>
        </div>
    </div>

    <!-- Billed Line Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-uppercase">
                    <th>#</th>
                    <th>Product / Material</th>
                    <th>SKU</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price (₹)</th>
                    <th class="text-end">GST Rate</th>
                    <th class="text-end">Taxable Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($items as $item): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td class="fw-bold"><?= e($item['product_name']) ?></td>
                        <td class="text-info font-monospace small"><?= e($item['sku']) ?></td>
                        <td class="text-center fw-bold text-warning"><?= $item['qty'] ?> <?= e($item['unit_code'] ?: 'Units') ?></td>
                        <td class="text-end font-monospace"><?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-end text-muted">18.00% GST</td>
                        <td class="text-end font-monospace fw-bold text-success"><?= number_format($item['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Summary Details Matrix -->
    <div class="row justify-content-end">
        <div class="col-md-5">
            <div class="card p-3 bg-body-tertiary border">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Taxable Subtotal:</span>
                    <span class="fw-bold">₹<?= number_format($inv['total_amount'] / 1.18, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">GST (18% integrated):</span>
                    <span class="fw-bold text-warning">₹<?= number_format($inv['total_amount'] - ($inv['total_amount'] / 1.18), 2) ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fw-bold fs-6">Total Billed Amount:</span>
                    <span class="fw-bold text-success fs-5">₹<?= number_format($inv['total_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 mt-1 border-top small">
                    <span class="text-muted">Paid Amount:</span>
                    <span class="fw-bold text-info">₹<?= number_format($inv['paid_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
