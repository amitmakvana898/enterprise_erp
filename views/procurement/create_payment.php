<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-extrabold text-white mb-1"><i class="bi bi-bank2 text-success me-2.5"></i>Process Vendor Payment Settlement</h2>
        <p class="text-secondary small mb-0">Record 3-way match financial payment settlement against vendor tax invoice</p>
    </div>
    <a href="<?= url('/procurement/payments') ?>" class="btn btn-outline-light btn-sm rounded-3 px-3 py-2 fw-semibold">
        <i class="bi bi-arrow-left me-1.5"></i> Back to Payments
    </a>
</div>

<?php if (!empty($selectedGrn)): ?>
    <!-- 3-Way Match Verified Procurement Card -->
    <div class="card p-4 mb-4 bg-success bg-opacity-10 border border-success rounded-3 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-success border-opacity-25 pb-2 flex-wrap gap-2">
            <div>
                <span class="badge bg-success text-white px-3 py-1.5 fw-bold">
                    <i class="bi bi-shield-check me-1"></i> 3-WAY MATCH VERIFIED PROCUREMENT TRACE SUMMARY
                </span>
            </div>
            <span class="badge bg-dark text-warning border border-warning px-3 py-1.5 fw-bold">
                <i class="bi bi-lock-fill me-1"></i> Contract Valuation Locked
            </span>
        </div>
        <div class="row g-3 small text-light">
            <div class="col-md-3">
                <span class="text-secondary d-block">GRN Reference:</span>
                <strong class="text-warning fs-6"><i class="bi bi-truck me-1"></i><?= e($selectedGrn['grn_no']) ?></strong>
                <div class="text-secondary" style="font-size: 0.75rem;">Challan: <?= e($selectedGrn['challan_no'] ?? 'CH-N/A') ?></div>
            </div>
            <div class="col-md-3">
                <span class="text-secondary d-block">Linked Purchase Order:</span>
                <strong class="text-primary fs-6"><i class="bi bi-bag-check me-1"></i><?= e($selectedGrn['po_no']) ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-secondary d-block">Material Item & Received Qty:</span>
                <strong class="text-white d-block"><?= e($selectedGrn['product_name'] ?? 'Requisitioned Item') ?></strong>
                <span class="badge bg-success bg-opacity-20 text-success border border-success px-2 py-0.5 mt-1"><?= $selectedGrn['po_qty'] ?? 10 ?> Units QC Passed</span>
            </div>
            <div class="col-md-3 text-end">
                <span class="text-secondary d-block">Total Settlement Amount (incl 18% GST):</span>
                <strong class="text-success fs-3 fw-extrabold font-monospace"><?= format_currency($selectedGrn['po_total']) ?></strong>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card p-4 shadow-sm mb-4 bg-dark border-secondary">
    <form action="<?= url('/procurement/payments/store') ?>" method="POST" class="needs-validation">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <?php if (!empty($prefilledInvoiceId)): ?>
            <input type="hidden" name="invoice_id" value="<?= (int)$prefilledInvoiceId ?>">
        <?php endif; ?>

        <!-- Section 1: Vendor & Reference -->
        <h5 class="fw-bold text-white mb-3"><i class="bi bi-building-check text-info me-2"></i>Vendor & Invoice Settlement Details</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Select Vendor / Beneficiary Supplier *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-warning"><i class="bi bi-building"></i></span>
                    <?php if (!empty($prefilledSupplier)): ?>
                        <input type="hidden" name="supplier_id" value="<?= $prefilledSupplier ?>">
                        <select class="form-select bg-dark text-light border-success fw-bold" disabled>
                            <?php foreach ($suppliers as $s): ?>
                                <?php if ((int)$s['id'] === (int)$prefilledSupplier): ?>
                                    <option value="<?= $s['id'] ?>" selected>🔒 <?= e($s['name']) ?> (Code: <?= e($s['code']) ?> • Bank: <?= e($s['bank_name'] ?? 'HDFC Corporate Bank') ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="supplier_id" class="form-select bg-dark text-light border-secondary" required>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Code: <?= e($s['code']) ?> • Bank: <?= e($s['bank_name'] ?? 'HDFC Corporate Bank') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <?php if (!empty($prefilledSupplier)): ?>
                    <small class="text-success d-block mt-1" style="font-size: 0.78rem;"><i class="bi bi-shield-check me-1"></i> Vendor beneficiary account locked from record.</small>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Linked Purchase Order / Tax Invoice Reference</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-primary"><i class="bi bi-file-earmark-text"></i></span>
                    <?php if (!empty($prefilledPo)): ?>
                        <input type="hidden" name="po_id" value="<?= $prefilledPo ?>">
                        <select class="form-select bg-dark text-light border-primary fw-bold" disabled>
                            <?php foreach ($orders as $po): ?>
                                <?php if ((int)$po['id'] === (int)$prefilledPo): ?>
                                    <option value="<?= $po['id'] ?>" selected>🔒 <?= e($po['po_no']) ?> - <?= e($po['supplier_name']) ?> (Total: <?= format_currency($po['total_amount']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="po_id" class="form-select bg-dark text-light border-secondary">
                            <option value="">-- Standalone Vendor Invoice Payment --</option>
                            <?php foreach ($orders as $po): ?>
                                <option value="<?= $po['id'] ?>"><?= e($po['po_no']) ?> - <?= e($po['supplier_name']) ?> (Total: <?= format_currency($po['total_amount']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Section 2: Financial Settlement & Real-World Banking Channels -->
        <h5 class="fw-bold text-white mb-3"><i class="bi bi-credit-card-2-front text-success me-2"></i>Banking Channel & Payment Settlement</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label text-light small fw-bold">Settlement Amount (₹) * <span class="badge bg-primary bg-opacity-20 text-info border border-info ms-1">Part-Wise / Milestone Enabled</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-success"><i class="bi bi-currency-rupee"></i></span>
                    <input type="number" step="0.01" id="paymentAmountInput" name="amount" class="form-control bg-dark text-success border-success fw-extrabold fs-5 font-monospace" placeholder="0.00" required value="<?= !empty($prefilledAmount) ? number_format($prefilledAmount, 2, '.', '') : '10000.00' ?>" min="0.01">
                </div>
                <?php if (!empty($prefilledAmount)): ?>
                    <div class="d-flex gap-1 mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm font-monospace" style="font-size:0.72rem;" onclick="setPartialPct(0.25)">25%</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm font-monospace" style="font-size:0.72rem;" onclick="setPartialPct(0.50)">50% (Half)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm font-monospace" style="font-size:0.72rem;" onclick="setPartialPct(0.75)">75%</button>
                        <button type="button" class="btn btn-outline-success btn-sm font-monospace fw-bold" style="font-size:0.72rem;" onclick="setPartialPct(1.00)">100% Full</button>
                    </div>
                    <script>
                        const maxInvoiceBal = <?= (float)$prefilledAmount ?>;
                        function setPartialPct(pct) {
                            const amt = (maxInvoiceBal * pct).toFixed(2);
                            document.getElementById('paymentAmountInput').value = amt;
                        }
                    </script>
                <?php endif; ?>
                <small class="text-secondary d-block mt-1" style="font-size: 0.78rem;"><i class="bi bi-info-circle me-1"></i> Enter partial payment for milestone settlement. Balance will be tracked automatically.</small>
            </div>

            <div class="col-md-4">
                <label class="form-label text-light small fw-bold">Banking Payment Channel *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-wallet2"></i></span>
                    <select name="payment_mode" id="payModeSelect" class="form-select bg-dark text-light border-secondary fw-semibold" onchange="togglePaymentFields()" required>
                        <option value="bank_transfer" selected>🏦 NEFT / RTGS (Electronic Interbank)</option>
                        <option value="netbanking">🏛️ Online Corporate NetBanking (IMPS)</option>
                        <option value="cheque">📄 Cheque / Demand Draft (DD)</option>
                        <option value="upi">📲 Corporate UPI / VPA Transfer</option>
                        <option value="credit_card">💳 Corporate Line of Credit / Card</option>
                        <option value="cash">💵 Petty Cash Receipt Voucher</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label text-light small fw-bold" id="refLabel">Bank Transaction Reference UTR # *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-light"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" name="reference_no" id="payRefInput" class="form-control bg-dark text-light border-secondary font-monospace fw-bold" placeholder="UTR-HDFC-991208312" value="UTR-HDFC-<?= rand(100000, 999999) ?>" required>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold" onclick="generateAutoUtr()" title="Auto-Generate UTR Number">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Auto
                    </button>
                </div>
            </div>
        </div>

        <!-- Dynamic Real-World Payment Details Card -->
        <div id="payment_details_card" class="p-3.5 rounded-3 mb-4 shadow-sm" style="background: rgba(16, 185, 129, 0.08); border: 1.5px solid rgba(16, 185, 129, 0.3);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="small fw-bold text-success" id="payDetailsHeader">
                    <i class="bi bi-bank me-1.5"></i> NEFT / RTGS Interbank Transfer Details
                </div>
                <span class="badge bg-success text-white px-2.5 py-1 fw-bold" id="payChannelBadge">NEFT / RTGS</span>
            </div>
            <div class="row g-3 small" id="payDetailsRow">
                <div class="col-md-4">
                    <span class="text-secondary d-block">Company Paying Bank Account:</span>
                    <strong class="text-white">HDFC Corporate Current A/C (A/C: ****8912)</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-secondary d-block">Beneficiary Vendor IFSC:</span>
                    <strong class="text-warning font-monospace">HDFC0000241</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-secondary d-block">Settlement Status:</span>
                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> 100% Full Invoice Clearance</strong>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-light small fw-bold">Payment Notes & Finance Controller Remarks</label>
            <textarea name="notes" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Full vendor invoice settlement approved by Finance Controller">Full vendor invoice settlement approved by Finance Controller.</textarea>
        </div>

        <div class="pt-3 border-top border-secondary d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/payments') ?>" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Cancel</a>
            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-check-circle-fill me-1.5"></i> Authorize & Process Vendor Payment
            </button>
        </div>
    </form>
</div>

<script>
function generateAutoUtr() {
    const mode = document.getElementById('payModeSelect').value;
    const rand = Math.floor(100000 + Math.random() * 900000);
    const refInput = document.getElementById('payRefInput');
    
    if (mode === 'cheque') {
        refInput.value = 'CHQ-' + rand;
    } else if (mode === 'upi') {
        refInput.value = 'UPI-TXN-' + rand;
    } else if (mode === 'credit_card') {
        refInput.value = 'AUTH-CC-' + rand;
    } else if (mode === 'cash') {
        refInput.value = 'CASH-VOUCHER-' + rand;
    } else {
        refInput.value = 'UTR-HDFC-' + rand;
    }
}

function togglePaymentFields() {
    const mode = document.getElementById('payModeSelect').value;
    const refLabel = document.getElementById('refLabel');
    const header = document.getElementById('payDetailsHeader');
    const badge = document.getElementById('payChannelBadge');
    const row = document.getElementById('payDetailsRow');
    const refInput = document.getElementById('payRefInput');

    if (mode === 'cheque') {
        refLabel.innerText = 'Cheque / DD Number *';
        header.innerHTML = '<i class="bi bi-card-heading me-1.5"></i> Cheque / Demand Draft Issuance Details';
        badge.innerText = 'CHEQUE / DD';
        badge.className = 'badge bg-warning text-dark px-2.5 py-1 fw-bold';
        row.innerHTML = `
            <div class="col-md-4">
                <span class="text-secondary d-block">Drawn Bank Name:</span>
                <strong class="text-white">State Bank of India (Corporate Branch)</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Cheque Issue Date:</span>
                <strong class="text-warning font-monospace">${new Date().toISOString().split('T')[0]}</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Clearance Status:</span>
                <strong class="text-info"><i class="bi bi-clock-history me-1"></i> Subject to Cheque Realization</strong>
            </div>
        `;
        if (refInput.value.startsWith('UTR')) refInput.value = 'CHQ-' + Math.floor(100000 + Math.random() * 900000);
    } else if (mode === 'upi') {
        refLabel.innerText = 'UPI Transaction Ref / VPA ID *';
        header.innerHTML = '<i class="bi bi-qr-code-scan me-1.5"></i> Corporate UPI / Instant VPA Settlement Details';
        badge.innerText = 'UPI / VPA';
        badge.className = 'badge bg-info text-dark px-2.5 py-1 fw-bold';
        row.innerHTML = `
            <div class="col-md-4">
                <span class="text-secondary d-block">Corporate VPA:</span>
                <strong class="text-white font-monospace">vendor@hdfcbank</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Gateway Processor:</span>
                <strong class="text-info">HDFC Bank UPI Hub</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Settlement Status:</span>
                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Instant 24x7 Settlement</strong>
            </div>
        `;
        if (refInput.value.startsWith('UTR')) refInput.value = 'UPI-TXN-' + Math.floor(100000 + Math.random() * 900000);
    } else if (mode === 'credit_card') {
        refLabel.innerText = 'Card Auth Approval Code *';
        header.innerHTML = '<i class="bi bi-credit-card me-1.5"></i> Corporate Credit Card / Line of Credit Settlement';
        badge.innerText = 'CREDIT CARD';
        badge.className = 'badge bg-primary px-2.5 py-1 fw-bold';
        row.innerHTML = `
            <div class="col-md-4">
                <span class="text-secondary d-block">Card Account:</span>
                <strong class="text-white">Corporate Amex ****9012</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Billing Cycle:</span>
                <strong class="text-warning">30-Day Revolving Credit</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Settlement Status:</span>
                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Authorized & Captured</strong>
            </div>
        `;
        if (refInput.value.startsWith('UTR')) refInput.value = 'AUTH-CC-' + Math.floor(100000 + Math.random() * 900000);
    } else if (mode === 'cash') {
        refLabel.innerText = 'Cash Voucher Receipt No *';
        header.innerHTML = '<i class="bi bi-cash-stack me-1.5"></i> Petty Cash Disbursement Voucher';
        badge.innerText = 'PETTY CASH';
        badge.className = 'badge bg-secondary px-2.5 py-1 fw-bold';
        row.innerHTML = `
            <div class="col-md-4">
                <span class="text-secondary d-block">Disbursement Source:</span>
                <strong class="text-white">Main Office Petty Cash Drawer</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Disbursing Officer:</span>
                <strong class="text-warning">Finance Cashier</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Receipt Voucher:</span>
                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Cash Signed Voucher Received</strong>
            </div>
        `;
        if (refInput.value.startsWith('UTR')) refInput.value = 'CASH-VOUCHER-' + Math.floor(100000 + Math.random() * 900000);
    } else {
        refLabel.innerText = 'Bank Transaction Reference UTR # *';
        header.innerHTML = '<i class="bi bi-bank me-1.5"></i> NEFT / RTGS Interbank Transfer Details';
        badge.innerText = 'NEFT / RTGS';
        badge.className = 'badge bg-success text-white px-2.5 py-1 fw-bold';
        row.innerHTML = `
            <div class="col-md-4">
                <span class="text-secondary d-block">Company Paying Bank Account:</span>
                <strong class="text-white">HDFC Corporate Current A/C (A/C: ****8912)</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Beneficiary Vendor IFSC:</span>
                <strong class="text-warning font-monospace">HDFC0000241</strong>
            </div>
            <div class="col-md-4">
                <span class="text-secondary d-block">Settlement Status:</span>
                <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> 100% Full Invoice Clearance</strong>
            </div>
        `;
        if (!refInput.value.startsWith('UTR')) refInput.value = 'UTR-HDFC-' + Math.floor(100000 + Math.random() * 900000);
    }
}
</script>
