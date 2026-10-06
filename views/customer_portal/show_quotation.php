<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-file-earmark-text-fill text-warning me-2"></i>Formal Commercial Price Quotation</h3>
        <p class="text-muted small mb-0">Official B2B proforma price estimate issued to <?= e($customer['name']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print();" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print / Download PDF Quote
        </button>
        <a href="<?= url('/customer-portal/quotations') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Quotations List
        </a>
    </div>
</div>

<!-- Formal Quotation Document Card -->
<div class="card p-4 shadow-sm mb-4">
    
    <!-- Top Document Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-primary mb-1">ENTERPRISE ERP SOLUTIONS INC.</h4>
            <div class="text-muted small">Commercial B2B Operations & Supply Chain Hub</div>
            <div class="text-muted small">GSTIN: <strong class="font-monospace">27AAACE1234F1Z9</strong> • Corporate Tax Identification</div>
        </div>
        <div class="text-md-end">
            <span class="badge bg-warning text-dark font-monospace fw-bold px-3 py-1.5 fs-6 mb-2">FORMAL PRICE QUOTATION</span>
            <h4 class="fw-bold text-warning font-monospace mb-1"><?= e($quotation['quotation_no']) ?></h4>
            <div class="text-muted small">Date Issued: <strong><?= date('d M Y', strtotime($quotation['quotation_date'])) ?></strong></div>
            <div class="text-warning small">Valid Until: <strong><?= date('d M Y', strtotime($quotation['valid_until'])) ?></strong></div>
        </div>
    </div>

    <!-- B2B Billing & Shipping Info -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3 rounded bg-body-tertiary border">
                <span class="text-muted small fw-bold d-block text-uppercase mb-1">CLIENT BILLING ADDRESS</span>
                <h5 class="fw-bold mb-1"><?= e($customer['name']) ?></h5>
                <div class="text-muted small">Customer Code: <strong class="text-warning font-monospace"><?= e($customer['code']) ?></strong></div>
                <div class="text-muted small"><i class="bi bi-envelope me-1 text-info"></i><?= e($customer['email']) ?></div>
                <div class="text-muted small"><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($customer['address'] ?: 'Commercial Address On File') ?></div>
                <div class="mt-1"><span class="badge bg-info bg-opacity-20 text-info font-monospace">GSTIN: <?= e($customer['gstin'] ?: '27AAAAA0000A1Z5') ?></span></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded bg-body-tertiary border h-100">
                <span class="text-muted small fw-bold d-block text-uppercase mb-1">QUOTATION TERMS & COMPLIANCE</span>
                <div class="small mb-1"><i class="bi bi-truck me-1 text-success"></i> <strong>Delivery Terms</strong>: Dispatch within 3 business days of order confirmation</div>
                <div class="small mb-1"><i class="bi bi-shield-check me-1 text-primary"></i> <strong>Warranty</strong>: Standard 1-Year Manufacturer Direct Warranty</div>
                <div class="small"><i class="bi bi-percent me-1 text-warning"></i> <strong>Tax Structure</strong>: Inclusive of 18% CGST + SGST</div>
            </div>
        </div>
    </div>

    <!-- Quotation Item Matrix Table -->
    <h5 class="fw-bold mb-3"><i class="bi bi-list-stars text-info me-2"></i>Quotation Item Breakdown & Pricing Matrix</h5>
    <div class="table-responsive mb-4">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-uppercase">
                    <th>#</th>
                    <th>Product / Material Description</th>
                    <th>SKU Code</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Rate (₹)</th>
                    <th class="text-end">GST Rate</th>
                    <th class="text-end">Total Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="fw-bold font-monospace"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="fw-semibold"><?= e($item['product_name']) ?></strong>
                            </td>
                            <td class="font-monospace text-info small"><?= e($item['sku']) ?></td>
                            <td class="text-center font-monospace fw-bold fs-6"><?= (int)$item['qty'] ?></td>
                            <td class="text-end font-monospace"><?= format_currency($item['unit_price']) ?></td>
                            <td class="text-end font-monospace text-muted">18.00%</td>
                            <td class="text-end font-monospace text-success fw-bold fs-6"><?= format_currency($item['total_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">Item breakdown details available in contract summary below.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Contract Total Valuation -->
    <div class="row justify-content-end mb-4">
        <div class="col-md-5">
            <div class="p-3 bg-body-tertiary border border-warning rounded-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small fw-bold">Subtotal (Excl. Tax):</span>
                    <strong class="font-monospace"><?= format_currency($quotation['total_amount'] / 1.18) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small fw-bold">Estimated GST (18%):</span>
                    <strong class="text-info font-monospace"><?= format_currency($quotation['total_amount'] - ($quotation['total_amount'] / 1.18)) ?></strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6">Total Contract Price:</span>
                    <h3 class="fw-bold text-success font-monospace mb-0"><?= format_currency($quotation['total_amount']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Action Buttons -->
    <?php if (!in_array($quotation['status'], ['converted', 'dispatched', 'completed'])): ?>
    <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            <?php if ($quotation['status'] === 'rejected'): ?>
                <i class="bi bi-x-circle text-danger me-1"></i> Quotation Rejected
            <?php elseif ($quotation['status'] === 'pending_quote'): ?>
                <i class="bi bi-clock-history text-warning me-1"></i> Awaiting Admin Quotation
            <?php else: ?>
                <i class="bi bi-info-circle text-info me-1"></i> Ready for 1-Click Order Placement
            <?php endif; ?>
        </div>
        <div>
            <?php if ($quotation['status'] === 'rejected'): ?>
                <button class="btn btn-outline-danger btn-lg fw-bold px-4" disabled>
                    <i class="bi bi-x-circle-fill me-1.5"></i> Rejected
                </button>
            <?php elseif ($quotation['status'] === 'pending_quote'): ?>
                <button class="btn btn-outline-warning btn-lg fw-bold px-4" disabled>
                    <i class="bi bi-clock-history me-1.5"></i> Awaiting Quotation
                </button>
            <?php else: ?>
                <a href="<?= url('/customer-portal/quotations/reject/' . $quotation['id']) ?>" class="btn btn-outline-danger btn-lg fw-bold px-4 py-2.5 me-2" onclick="return confirm('Are you sure you want to REJECT this price quotation?');">
                    <i class="bi bi-x-circle me-1"></i> Reject Quotation
                </a>
                <a href="<?= url('/customer-portal/quotations/accept/' . $quotation['id']) ?>" class="btn btn-success btn-lg fw-bold px-5 py-2.5 shadow-sm" onclick="return confirm('Accept quotation <?= e($quotation['quotation_no']) ?> and place official Sales Order?');">
                    <i class="bi bi-check-circle-fill me-2"></i> Accept & Place Sales Order
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-success small fw-bold">
            <i class="bi bi-check-circle-fill me-1.5"></i> This quotation has been converted to an active Sales Order.
        </div>
    </div>
    <?php endif; ?>
</div>
