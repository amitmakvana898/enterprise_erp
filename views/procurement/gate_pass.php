<!-- Page Header Actions -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-shield-check text-warning me-2"></i>Return Outward Delivery Gate Pass</h3>
        <p class="text-muted small mb-0">Official warehouse security gate pass for returning damaged/defective stock to supplier</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-warning text-dark btn-sm fw-bold"><i class="bi bi-printer me-1"></i> Print Return Gate Pass</button>
        <a href="<?= url('/procurement/returns') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Returns</a>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .sidebar-wrapper, .erp-topbar, .d-print-none { display: none !important; }
    .main-content-area { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .gate-pass-card { border: 2px solid #000 !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
    .gate-pass-card * { color: #000 !important; }
    .badge { border: 1px solid #000 !important; color: #000 !important; background: #fff !important; }
    .table { color: #000 !important; border-color: #000 !important; }
}
</style>

<!-- Gate Pass Card -->
<div class="card gate-pass-card shadow-sm border p-4 p-md-5 rounded-3 mb-4 mx-auto" style="max-width: 900px;">
    <!-- Header: Company & Pass Reference -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <span class="badge bg-warning text-dark font-monospace mb-2">OFFICIAL OUTWARD SECURITY PASS</span>
            <h3 class="fw-extrabold text-uppercase tracking-tight mb-1" style="color:var(--text-primary);">Enterprise ERP Logistics</h3>
            <div class="text-muted small">
                <i class="bi bi-geo-alt-fill text-warning me-1"></i>Warehouse: <strong style="color:var(--text-primary);"><?= e($ret['warehouse_name'] ?? 'Main Warehouse') ?></strong> (Code: <?= e($ret['warehouse_code'] ?? 'WH-01') ?>)
            </div>
        </div>
        <div class="text-end">
            <h4 class="fw-extrabold text-info font-monospace mb-1">GP-<?= e($ret['return_no']) ?></h4>
            <div class="text-muted small">Issued: <strong style="color:var(--text-primary);"><?= date('d M Y, h:i A', strtotime($ret['return_date'])) ?></strong></div>
            <span class="badge bg-success bg-opacity-20 text-success border border-success mt-2 px-2.5 py-1">
                <i class="bi bi-shield-lock-fill me-1"></i>Security Clearance Granted
            </span>
        </div>
    </div>

    <!-- Supplier & Accounting Vouchers Grid -->
    <div class="row g-4 mb-4 p-3 rounded-3 border" style="background:var(--bg-page);">
        <div class="col-md-6 border-end">
            <small class="text-muted text-uppercase fw-bold d-block mb-1"><i class="bi bi-truck me-1"></i> Consignee / Return Supplier</small>
            <strong class="fs-6 d-block" style="color:var(--text-primary);"><?= e($ret['supplier_name']) ?> (Code: <?= e($ret['supplier_code']) ?>)</strong>
            <div class="text-muted small mt-1">
                <div><?= e($ret['supplier_address'] ?: 'Industrial Commercial Estate, Zone II') ?></div>
                <div>Ph: <?= e($ret['supplier_phone'] ?: '+91 98765 43210') ?> | Email: <?= e($ret['supplier_email'] ?: 'vendor@supplier.com') ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <small class="text-muted text-uppercase fw-bold d-block mb-1"><i class="bi bi-receipt me-1"></i> Linked Accounting Vouchers</small>
            <div class="small text-muted">
                <div>Return Voucher: <strong class="text-danger font-monospace"><?= e($ret['return_no']) ?></strong></div>
                <div>Credit Note No: <strong class="text-warning font-monospace"><?= e($ret['credit_note_no'] ?: 'CN-2026-AUTO') ?></strong></div>
                <div>Status: <span class="badge bg-danger"><i class="bi bi-dash-circle me-1"></i>Outward Stock Deduction Posted</span></div>
            </div>
        </div>
    </div>

    <!-- Dispatched Items Table -->
    <h6 class="fw-bold text-uppercase text-warning mb-2.5"><i class="bi bi-box-seam me-1"></i> Dispatched Return Inventory Specification</h6>
    <div class="table-responsive mb-4">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr class="text-muted small" style="background:var(--bg-page);">
                    <th style="width:40px;">#</th>
                    <th>Product Item Description</th>
                    <th>SKU Code</th>
                    <th class="text-center">Return Qty</th>
                    <th class="text-end">Unit Rate (₹)</th>
                    <th class="text-end">Total Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <strong class="fs-6" style="color:var(--text-primary);"><?= e($ret['product_name'] ?: 'Dell XPS 15 Workstation Laptop') ?></strong>
                        <div class="text-warning small mt-0.5">
                            <strong>Defect Report:</strong> <?= e($ret['reason'] ?: 'Damaged packaging in transit - display screen cracked') ?>
                        </div>
                    </td>
                    <td class="font-monospace text-info small"><?= e($ret['product_sku'] ?: 'PROD-DELL-001') ?></td>
                    <td class="text-center fw-extrabold text-danger fs-6"><?= (int)($ret['qty'] ?: 1) ?> Pcs</td>
                    <td class="text-end font-monospace">₹<?= number_format($ret['unit_price'] ?: $ret['total_amount'], 2) ?></td>
                    <td class="text-end fw-extrabold text-success font-monospace fs-6">₹<?= number_format($ret['total_amount'], 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background:var(--bg-page); border-top: 2px solid var(--border);">
                    <td colspan="5" class="text-end fw-bold text-uppercase py-3" style="color:var(--text-secondary);">Total Dispatched Credit Note Amount:</td>
                    <td class="text-end fw-extrabold font-monospace fs-5 py-3 text-success">₹<?= number_format($ret['total_amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Gate Security Notice Card -->
    <div class="p-3 mb-4 rounded-3 border border-warning bg-warning bg-opacity-10">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-warning fs-4"></i>
            <div>
                <div class="fw-bold small" style="color:var(--text-primary);">Warehouse Gate Security Clearance</div>
                <div class="text-muted small" style="font-size:0.78rem;">
                    Authorizes outward vehicle transit of <strong><?= (int)($ret['qty'] ?: 1) ?> defective units</strong> from <?= e($ret['warehouse_name'] ?: 'Main Warehouse') ?> back to <?= e($ret['supplier_name']) ?>. Stock ledger updated under return ref <?= e($ret['return_no']) ?>.
                </div>
            </div>
        </div>
    </div>

    <!-- Signatures Block -->
    <div class="row pt-4 mt-2 text-center g-4">
        <div class="col-4">
            <div class="border-top pt-2 fw-bold small text-uppercase" style="color:var(--text-primary);">Warehouse Manager</div>
            <small class="text-muted"><?= e($ret['creator_name'] ?: 'Store Supervisor') ?></small>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 fw-bold small text-uppercase" style="color:var(--text-primary);">Gate Security Officer</div>
            <small class="text-muted">Security Outward Stamp</small>
        </div>
        <div class="col-4">
            <div class="border-top pt-2 fw-bold small text-uppercase" style="color:var(--text-primary);">Driver / Transporter</div>
            <small class="text-muted">Vehicle Driver Sign</small>
        </div>
    </div>
</div>
