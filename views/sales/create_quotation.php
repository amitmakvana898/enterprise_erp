<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-file-earmark-text-fill text-info me-2.5"></i>Create Sales Quotation</h3>
        <p class="page-header-sub">Generate formal price quotation / proforma for prospective customer</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales Hub
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4 col-lg-8 mx-auto">
    <form action="<?= url('/sales/quotations/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="request_id" value="<?= e($_GET['request_id'] ?? '') ?>">

        <div class="mb-3">
            <label class="form-label small fw-bold">Select Customer *</label>
            <select name="customer_id" class="form-select fw-semibold" required>
                <option value="">-- Choose Customer --</option>
                <?php $selCust = (int)($_GET['customer_id'] ?? 0); ?>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($selCust === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> (Code: <?= e($c['code']) ?> • Credit Limit: ₹<?= number_format($c['credit_limit'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Select Product *</label>
            <select name="product_id" id="qProductSelect" class="form-select fw-bold" required>
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" 
                            data-price="<?= $p['selling_rate'] ?>"
                            data-gst="<?= $p['gst_rate'] ?? 18.00 ?>"
                            data-sku="<?= e($p['sku']) ?>">
                        <?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?> — Rate: ₹<?= number_format($p['selling_rate'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Quotation Quantity *</label>
                <input type="number" name="qty" id="qQtyInput" class="form-control text-center fw-bold fs-6" placeholder="1" min="1" value="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Unit Selling Price (₹) *</label>
                <div class="input-group">
                    <span class="input-group-text text-success"><i class="bi bi-currency-rupee"></i></span>
                    <input type="number" step="0.01" min="0.01" name="unit_price" id="qUnitPriceInput" class="form-control fw-bold fs-6" placeholder="0.00" required>
                </div>
            </div>
        </div>

        <!-- Real-Time Quotation Valuation Summary Card -->
        <div class="card p-3 rounded-3 mb-4 bg-body-tertiary border">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase text-info" style="letter-spacing:0.06em;">
                    <i class="bi bi-calculator-fill me-1.5"></i> Live Quotation Valuation Summary
                </span>
                <span class="badge bg-info text-dark font-monospace"><i class="bi bi-lightning-fill me-1"></i>Real-Time Calc</span>
            </div>
            <div class="row g-3 align-items-center text-center text-md-start">
                <div class="col-md-4 border-end">
                    <small class="text-muted d-block">Taxable Subtotal (Base)</small>
                    <strong class="fs-6" id="qSubtotalLabel">₹ 0.00</strong>
                </div>
                <div class="col-md-4 border-end">
                    <small class="text-muted d-block">Estimated GST (<span id="qGstRateLabel">18%</span>)</small>
                    <strong class="text-warning fs-6" id="qTaxLabel">₹ 0.00</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Total Quotation Value</small>
                    <strong class="text-success fs-5 font-monospace" id="qTotalLabel">₹ 0.00</strong>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-info fw-bold text-dark w-100 py-2.5 rounded-3 shadow-sm">
            <i class="bi bi-file-earmark-plus-fill me-1.5"></i> Issue Validated Sales Quotation
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('qProductSelect');
    const qtyInput = document.getElementById('qQtyInput');
    const unitPriceInput = document.getElementById('qUnitPriceInput');
    const subtotalLabel = document.getElementById('qSubtotalLabel');
    const taxLabel = document.getElementById('qTaxLabel');
    const totalLabel = document.getElementById('qTotalLabel');
    const gstRateLabel = document.getElementById('qGstRateLabel');

    function updateCalculations() {
        if (!productSelect || productSelect.selectedIndex <= 0) {
            subtotalLabel.textContent = '₹ 0.00';
            taxLabel.textContent = '₹ 0.00';
            totalLabel.textContent = '₹ 0.00';
            return;
        }

        const opt = productSelect.options[productSelect.selectedIndex];
        const defaultPrice = parseFloat(opt.dataset.price) || 0;
        const gstRate = parseFloat(opt.dataset.gst) || 18.00;

        if (unitPriceInput && (!unitPriceInput.value || unitPriceInput.dataset.autoSet === '1')) {
            unitPriceInput.value = defaultPrice.toFixed(2);
            unitPriceInput.dataset.autoSet = '1';
        }

        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(unitPriceInput.value) || 0;

        const subtotal = qty * price;
        const tax = subtotal * (gstRate / 100.0);
        const total = subtotal + tax;

        const formatINR = (val) => '₹ ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (gstRateLabel) gstRateLabel.textContent = gstRate + '%';
        if (subtotalLabel) subtotalLabel.textContent = formatINR(subtotal);
        if (taxLabel) taxLabel.textContent = formatINR(tax);
        if (totalLabel) totalLabel.textContent = formatINR(total);
    }

    if (productSelect) {
        productSelect.addEventListener('change', function() {
            if (unitPriceInput) unitPriceInput.dataset.autoSet = '1';
            updateCalculations();
        });
    }

    if (qtyInput) {
        ['input', 'change', 'keyup'].forEach(evt => qtyInput.addEventListener(evt, updateCalculations));
    }

    if (unitPriceInput) {
        unitPriceInput.addEventListener('input', function() {
            this.dataset.autoSet = '0';
            updateCalculations();
        });
        unitPriceInput.addEventListener('keyup', updateCalculations);
    }

    if (productSelect && productSelect.selectedIndex > 0) {
        updateCalculations();
    }
});
</script>
