<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-receipt-cutoff text-warning me-2"></i>Book Vendor Purchase Invoice</h3>
        <p class="text-secondary small mb-0">Procure-to-Pay (P2P): Perform 3-Way Matching between PO Subtotal, GRN Stock Accepted, and Vendor Invoice</p>
    </div>
    <a href="<?= url('/procurement/invoices') ?>" class="btn btn-outline-light btn-sm rounded-3"><i class="bi bi-arrow-left me-1"></i>Back to Vendor Invoices</a>
</div>

<div class="card bg-dark border-secondary p-4 shadow-sm max-w-700 mx-auto">
    <form action="<?= url('/procurement/invoices/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? \App\Helpers\Security::generateCsrfToken()) ?>">
        <input type="hidden" name="grn_id" value="<?= (int)($prefilledGrn ?? 0) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Vendor Invoice Number *</label>
                <input type="text" name="invoice_no" class="form-control bg-dark bg-opacity-50 text-light border-secondary" placeholder="e.g. VEND-INV-9941" value="INV-<?= date('Y') ?>-<?= rand(1000, 9999) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Select Supplier / Vendor *</label>
                <select name="supplier_id" id="invSupplierSelect" class="form-select bg-dark bg-opacity-50 text-light border-secondary fw-bold" required>
                    <option value="">-- Choose Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (!empty($prefilledSupplier) && (int)$s['id'] === (int)$prefilledSupplier) ? 'selected' : '' ?>><?= e($s['name']) ?> (Code: <?= e($s['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Select Approved PO Reference *</label>
                <select name="po_id" id="invPoSelect" class="form-select bg-dark bg-opacity-50 text-light border-secondary fw-bold">
                    <option value="">-- Choose Purchase Order --</option>
                    <?php foreach ($orders as $o): ?>
                        <option value="<?= $o['id'] ?>"
                                <?= (!empty($prefilledPo) && (int)$o['id'] === (int)$prefilledPo) ? 'selected' : '' ?>
                                data-supplier="<?= $o['supplier_id'] ?>"
                                data-amount="<?= number_format($o['billed_total'], 2, '.', '') ?>"
                                data-subtotal="<?= number_format($o['billed_subtotal'], 2, '.', '') ?>"
                                data-tax="<?= number_format($o['billed_tax'], 2, '.', '') ?>"
                                data-product="<?= e($o['product_name'] ?? 'General Catalog Item') ?>"
                                data-sku="<?= e($o['product_sku'] ?? 'N/A') ?>"
                                data-ordered-qty="<?= $o['ordered_qty'] ?>"
                                data-received-qty="<?= $o['received_qty'] ?>"
                                data-pending-qty="<?= $o['pending_qty'] ?>"
                                data-billed-qty="<?= $o['billed_qty'] ?>"
                                data-gst="<?= $o['gst_rate'] ?? 18.00 ?>">
                            <?= e($o['po_no']) ?> — <?= e($o['supplier_name']) ?> (<?= e($o['product_name'] ?? 'Item') ?> • Rec: <?= $o['received_qty'] ?>/<?= $o['ordered_qty'] ?> Pcs • Bill: ₹<?= number_format($o['billed_total'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-light small fw-bold">Payment Due Date *</label>
                <input type="date" name="due_date" class="form-control bg-dark bg-opacity-50 text-light border-secondary" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
            </div>
        </div>

        <!-- Live P2P Partial Shipment 3-Way Matching Breakdown Card -->
        <div id="invPoSummaryCard" class="card p-3 mb-4 rounded-3 d-none" style="background:rgba(15,23,42,0.85);border:1px solid #334155;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="small fw-bold text-uppercase text-warning" style="letter-spacing:0.06em;">
                    <i class="bi bi-shield-check me-1.5"></i> Partial Shipment 3-Way Match & Billing Breakdown
                </span>
                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Receipt Verified</span>
            </div>
            
            <div class="row g-3 align-items-center mb-3 text-center text-md-start">
                <div class="col-md-4 border-end border-secondary">
                    <small class="text-secondary d-block">Billed Item & SKU</small>
                    <strong class="text-white small d-block" id="invProductLabel">-</strong>
                </div>
                <div class="col-md-4 border-end border-secondary">
                    <small class="text-secondary d-block">Shipment Receipt Progress</small>
                    <div id="invShipmentProgressLabel" class="small fw-bold text-light">-</div>
                </div>
                <div class="col-md-4">
                    <small class="text-secondary d-block">This Invoice Billed Qty</small>
                    <strong class="text-success fs-6" id="invQtyLabel">-</strong>
                </div>
            </div>

            <div class="row g-3 align-items-center text-center text-md-start p-2 rounded-2" style="background:rgba(30,41,59,0.7)">
                <div class="col-md-4 border-end border-secondary">
                    <small class="text-secondary d-block">Proportional Subtotal</small>
                    <strong class="text-white small d-block" id="invSubtotalLabel">-</strong>
                </div>
                <div class="col-md-4 border-end border-secondary">
                    <small class="text-secondary d-block">GST Tax (<span id="invGstRateLabel">18%</span>)</small>
                    <strong class="text-warning small d-block" id="invTaxLabel">-</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-secondary d-block">Calculated Partial Invoice Total</small>
                    <strong class="text-success fs-6" id="invCalculatedTotalLabel">-</strong>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-light small fw-bold">Total Vendor Invoice Amount (₹) *</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-success"><i class="bi bi-currency-rupee"></i></span>
                <input type="number" step="0.01" name="total_amount" id="invTotalAmount" class="form-control bg-dark bg-opacity-50 text-light border-secondary fw-bold fs-5 text-success" placeholder="0.00" value="<?= !empty($prefilledAmount) ? number_format($prefilledAmount, 2, '.', '') : '' ?>" required>
            </div>
            <small class="text-info d-block mt-1"><i class="bi bi-shield-check me-1"></i>3-Way Match System verifies partial bill amount against actual physical GRN received quantities.</small>
        </div>

        <!-- 3-Way Match Verification Card -->
        <div class="p-3 mb-4 rounded-3 border border-success bg-success bg-opacity-10">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-success fs-4"></i>
                <div>
                    <div class="fw-bold text-white small">P2P Automated 3-Way Matching Active</div>
                    <div class="text-secondary small" style="font-size:0.76rem;">Calculates exact invoice value for partial received stock batches to prevent overbilling while keeping pending balances accurate.</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-warning fw-bold text-dark w-100 py-2.5 rounded-3">
            <i class="bi bi-receipt me-1.5"></i> Verify 3-Way Match & Book Vendor Invoice
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poSelect = document.getElementById('invPoSelect');
    const supplierSelect = document.getElementById('invSupplierSelect');
    const totalAmount = document.getElementById('invTotalAmount');
    const summaryCard = document.getElementById('invPoSummaryCard');
    const productLabel = document.getElementById('invProductLabel');
    const shipmentProgressLabel = document.getElementById('invShipmentProgressLabel');
    const qtyLabel = document.getElementById('invQtyLabel');
    const subtotalLabel = document.getElementById('invSubtotalLabel');
    const taxLabel = document.getElementById('invTaxLabel');
    const calculatedTotalLabel = document.getElementById('invCalculatedTotalLabel');
    const gstRateLabel = document.getElementById('invGstRateLabel');

    if (poSelect) {
        poSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) {
                if (summaryCard) summaryCard.classList.add('d-none');
                return;
            }

            if (opt.dataset.supplier && (!supplierSelect.value || supplierSelect.value === "")) {
                supplierSelect.value = opt.dataset.supplier;
            }
            
            const product = opt.dataset.product || 'Item Master';
            const sku = opt.dataset.sku ? `(SKU: ${opt.dataset.sku})` : '';
            const orderedQty = parseInt(opt.dataset.orderedQty || '1');
            const receivedQty = parseInt(opt.dataset.receivedQty || '0');
            const pendingQty = parseInt(opt.dataset.pendingQty || '0');
            const billedQty = parseInt(opt.dataset.billedQty || '1');

            const total = opt.dataset.amount ? parseFloat(opt.dataset.amount) : 0;
            const subtotal = opt.dataset.subtotal ? parseFloat(opt.dataset.subtotal) : (total / 1.18);
            const tax = opt.dataset.tax ? parseFloat(opt.dataset.tax) : (total - subtotal);
            const gstRate = opt.dataset.gst || '18';

            // Auto set total amount to calculated partial shipment total
            totalAmount.value = total.toFixed(2);

            const formatINR = (val) => '₹ ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            if (productLabel) productLabel.textContent = `${product} ${sku}`;
            if (shipmentProgressLabel) {
                shipmentProgressLabel.innerHTML = `
                    <span class="text-success">${receivedQty} Pcs Rec.</span> / ${orderedQty} Pcs
                    ${pendingQty > 0 ? `<span class="badge bg-warning text-dark ms-1">⏳ ${pendingQty} Pcs Pending</span>` : `<span class="badge bg-success ms-1">✅ Complete</span>`}
                `;
            }
            if (qtyLabel) qtyLabel.textContent = `${billedQty} Received Units`;
            if (subtotalLabel) subtotalLabel.textContent = formatINR(subtotal);
            if (taxLabel) taxLabel.textContent = formatINR(tax);
            if (calculatedTotalLabel) calculatedTotalLabel.textContent = formatINR(total);
            if (gstRateLabel) gstRateLabel.textContent = gstRate + '%';

            if (summaryCard) summaryCard.classList.remove('d-none');
        });

        if (poSelect.selectedIndex > 0) {
            poSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
