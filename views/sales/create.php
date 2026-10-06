<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-cart-plus-fill text-success me-2"></i>Create New Customer Sales Order</h3>
        <p class="page-header-sub">Select customer, product variant options, warehouse stock check, and live auto-count valuation</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="quickFillSampleSo()">
            <i class="bi bi-lightning-charge-fill me-1"></i> Quick Fill Sample Order
        </button>
        <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary btn-sm fw-bold"><i class="bi bi-arrow-left me-1"></i> Back to Sales Orders</a>
    </div>
</div>

<div class="card p-4">
    <form action="<?= url('/sales/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold">Select Customer *</label>
                    <button type="button" class="btn btn-link p-0 text-success small text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Add New Customer
                    </button>
                </div>
                <select name="customer_id" class="form-select fw-semibold" required>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (Code: <?= e($c['code']) ?> • Limit: ₹<?= number_format($c['credit_limit'], 2) ?>)</option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No customers registered. Click '+ Add New Customer' to add one.</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Fulfillment Warehouse *</label>
                <select name="warehouse_id" id="soWarehouseSelect" class="form-select fw-semibold" required>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-info d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-magic me-1"></i>Warehouse auto-selects based on product stock location</small>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Select Product Item *</label>
                <select name="product_id" id="soProductSelect" class="form-select fw-bold py-2" required>
                    <?php foreach ($products as $p): ?>
                        <?php 
                            $attrs = $productAttributes[$p['id']] ?? []; 
                            $attrJson = htmlspecialchars(json_encode($attrs), ENT_QUOTES, 'UTF-8');
                        ?>
                        <option value="<?= $p['id'] ?>" 
                                data-id="<?= $p['id'] ?>"
                                data-price="<?= $p['selling_rate'] ?>" 
                                data-gst="<?= $p['gst_rate'] ?? 18.00 ?>"
                                data-category="<?= e($p['category_name'] ?: 'General') ?>"
                                data-brand="<?= e($p['brand_name'] ?: 'Generic') ?>"
                                data-unit="<?= e($p['unit_code'] ?: 'Pcs') ?>"
                                data-attributes='<?= $attrJson ?>'>
                            <?= e($p['name']) ?> [<?= e($p['category_name'] ?: 'General') ?> • <?= e($p['brand_name'] ?: 'Generic') ?>] — ₹<?= number_format($p['selling_rate'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Total Order Quantity *</label>
                <input type="number" name="qty" id="soQtyInput" class="form-control text-center fw-bold fs-6" placeholder="1" min="1" required value="1">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Selling Rate (₹) *</label>
                <div class="input-group">
                    <span class="input-group-text text-success"><i class="bi bi-currency-rupee"></i></span>
                    <input type="number" step="0.01" name="unit_price" id="soUnitPriceInput" class="form-control fw-bold" placeholder="0.00" required style="font-size:1.1rem;">
                </div>
            </div>
        </div>

        <!-- Product Metadata & Dynamic Variant Options Card -->
        <div id="soProductMetaCard" class="card p-4 rounded-3 mb-4 d-none bg-body-tertiary border-secondary shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary" id="soMetaCategory">Category</span>
                    <span class="badge bg-warning text-dark" id="soMetaBrand">Brand</span>
                    <span class="badge bg-secondary" id="soMetaUnit">Unit</span>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-1.5 fw-bold fs-6" id="soMetaStock">
                        <i class="bi bi-box-seam me-1"></i> Checking Stock...
                    </span>
                </div>
            </div>

            <!-- Dynamic Multi-Variant Options Container -->
            <div id="soVariantContainer" class="row g-3 pt-2">
                <!-- Dynamically populated via JS -->
            </div>
        </div>

        <!-- Real-Time Auto-Count Sales Order Valuation Breakdown Card -->
        <div class="card p-4 rounded-3 mb-4 shadow-lg bg-body-tertiary border-success" style="border: 1.5px solid rgba(16, 185, 129, 0.45);">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-35">
                <span class="small fw-bold text-uppercase text-success" style="letter-spacing:0.07em;">
                    <i class="bi bi-calculator-fill me-2"></i> Sales Order Valuation Summary (Auto-Count)
                </span>
                <span class="badge bg-success text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                    <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Real-Time Live Calculation
                </span>
            </div>
            <div class="row g-4 text-center align-items-center">
                <div class="col-md-3 border-end border-secondary border-opacity-35">
                    <small class="text-secondary d-block fw-semibold mb-1">Base Subtotal (Qty × Selling Rate)</small>
                    <span class="fs-4 fw-bold text-body-emphasis font-monospace" id="calcSubtotal">₹ 0.00</span>
                </div>
                <div class="col-md-3 border-end border-secondary border-opacity-35">
                    <small class="text-secondary d-block fw-semibold mb-1">Estimated GST Tax (<span id="gstRateLabel" class="text-warning">18%</span>)</small>
                    <span class="fs-4 fw-bold text-warning font-monospace" id="calcTax">₹ 0.00</span>
                </div>
                <div class="col-md-6">
                    <small class="text-secondary d-block fw-semibold mb-1">Final Authorizing Sales Order Total</small>
                    <div class="p-2 px-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-40 d-inline-block">
                        <span class="fs-2 fw-extrabold text-success font-monospace" id="calcTotal">₹ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-3 border-top border-secondary d-flex justify-content-end gap-2">
            <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-gradient-primary fw-bold px-4">
                <i class="bi bi-cart-check-fill me-1.5"></i> Authorize & Process Sales Order
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('soProductSelect');
    const warehouseSelect = document.getElementById('soWarehouseSelect');
    const qtyInput = document.getElementById('soQtyInput');
    const unitPriceInput = document.getElementById('soUnitPriceInput');
    const metaCard = document.getElementById('soProductMetaCard');
    const metaCategory = document.getElementById('soMetaCategory');
    const metaBrand = document.getElementById('soMetaBrand');
    const metaUnit = document.getElementById('soMetaUnit');
    const metaStock = document.getElementById('soMetaStock');
    const variantContainer = document.getElementById('soVariantContainer');
    const calcSubtotal = document.getElementById('calcSubtotal');
    const calcTax = document.getElementById('calcTax');
    const calcTotal = document.getElementById('calcTotal');
    const gstRateLabel = document.getElementById('gstRateLabel');

    const stockMap = <?= json_encode($stockMap ?? []) ?>;

    function updateProductMetaAndVariants() {
        if (!productSelect || productSelect.selectedIndex < 0) {
            if (metaCard) metaCard.classList.add('d-none');
            return;
        }

        const opt = productSelect.options[productSelect.selectedIndex];
        const pid = opt.dataset.id;
        const price = parseFloat(opt.dataset.price) || 0;
        const category = opt.dataset.category || 'General';
        const brand = opt.dataset.brand || 'Generic';
        const unit = opt.dataset.unit || 'Pcs';
        const attributes = JSON.parse(opt.dataset.attributes || '[]');

        if (metaCategory) metaCategory.textContent = category;
        if (metaBrand) metaBrand.textContent = brand;
        if (metaUnit) metaUnit.textContent = `Unit: ${unit}`;

        // Set Unit Price if not manually overridden
        if (unitPriceInput && (!unitPriceInput.value || unitPriceInput.dataset.autoSet === '1')) {
            unitPriceInput.value = price.toFixed(2);
            unitPriceInput.dataset.autoSet = '1';
        }

        // AUTO-SELECT WAREHOUSE THAT HAS STOCK FOR THIS PRODUCT
        if (stockMap[pid] && warehouseSelect) {
            let maxQty = -1;
            let bestWarehouseId = null;
            for (let wid in stockMap[pid]) {
                const qty = parseInt(stockMap[pid][wid], 10);
                if (qty > maxQty) {
                    maxQty = qty;
                    bestWarehouseId = wid;
                }
            }
            if (bestWarehouseId) {
                warehouseSelect.value = bestWarehouseId;
            }
        }

        // Live Stock Lookup for current warehouse
        const wid = warehouseSelect ? warehouseSelect.value : 0;
        const availQty = (stockMap[pid] && stockMap[pid][wid]) ? stockMap[pid][wid] : 0;
        if (metaStock) {
            if (availQty > 0) {
                metaStock.className = 'badge bg-success px-3 py-1.5 fw-bold fs-6';
                metaStock.innerHTML = `<i class="bi bi-box-seam me-1"></i> Stock Available: ${availQty} ${unit}`;
            } else {
                metaStock.className = 'badge bg-danger px-3 py-1.5 fw-bold fs-6';
                metaStock.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Out of Stock in Selected Warehouse (${availQty} ${unit})`;
            }
        }

        if (metaCard) metaCard.classList.remove('d-none');

        // Render Clickable Attribute Options with Per-Option Quantity Inputs
        if (variantContainer && attributes && attributes.length > 0) {
            let html = '';
            attributes.forEach(attr => {
                let valArr = [];
                if (attr.value) {
                    valArr = attr.value.split(',').map(s => s.trim()).filter(s => s !== '');
                }


                if (valArr.length > 0) {
                    html += `<div class="col-12 mb-3">`;
                    html += `<div class="d-flex justify-content-between align-items-center mb-2">`;
                    html += `<label class="form-label fw-bold mb-0 small text-body-emphasis"><i class="bi bi-sliders text-warning me-1.5"></i> Select ${escapeHtml(attr.name)} Options & Quantity Breakdown</label>`;
                    html += `<span class="badge bg-secondary font-monospace" style="font-size:0.7rem;">Enter Qty Per Option</span>`;
                    html += `</div>`;
                    
                    html += `<div class="p-3 rounded-3 border border-secondary bg-body-tertiary">`;
                    html += `<div class="row g-3">`;

                    valArr.forEach((val, idx) => {
                        const pillId = `so_attr_${attr.code}_${idx}`;
                        html += `
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-between border border-secondary transition-all attr-item-card bg-body shadow-sm">
                                    <div class="form-check me-2 mb-0">
                                        <input type="checkbox" class="form-check-input attr-check-toggle" id="${pillId}" name="selected_attributes[${escapeHtml(attr.code)}][]" value="${escapeHtml(val)}" checked autocomplete="off" onchange="recalculateTotalQuantityFromAttributes()">
                                        <label class="form-check-label fw-bold text-body small text-truncate" for="${pillId}" style="max-width: 110px;" title="${escapeHtml(val)}">
                                            ${escapeHtml(val)}
                                        </label>
                                    </div>
                                    <div class="input-group input-group-sm" style="width: 95px;">
                                        <span class="input-group-text bg-body-secondary border-secondary text-body-secondary px-1.5" style="font-size:0.72rem;">Qty</span>
                                        <input type="number" name="attribute_qty[${escapeHtml(attr.code)}][${escapeHtml(val)}]" class="form-control bg-body text-body border-secondary text-center fw-bold attr-qty-input" min="0" value="1" placeholder="1" style="font-size:0.85rem;" oninput="recalculateTotalQuantityFromAttributes()" onchange="recalculateTotalQuantityFromAttributes()">
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div></div></div>`;
                }
            });
            variantContainer.innerHTML = html;
        } else if (variantContainer) {
            variantContainer.innerHTML = `<div class="col-12"><small class="text-secondary"><i class="bi bi-info-circle me-1"></i>Standard Single-Variant Product (No custom color/size choices configured).</small></div>`;
        }

        recalculateTotalQuantityFromAttributes();
    }

    // Listen for attribute quantity or checkbox changes to auto-update total order quantity & calculations
    if (variantContainer) {
        ['input', 'change', 'keyup'].forEach(evtType => {
            variantContainer.addEventListener(evtType, function(e) {
                if (e.target && (e.target.classList.contains('attr-qty-input') || e.target.classList.contains('attr-check-toggle'))) {
                    recalculateTotalQuantityFromAttributes();
                }
            });
        });
    }

    function recalculateTotalQuantityFromAttributes() {
        if (!variantContainer) return;
        let totalSum = 0;
        let hasActiveAttributes = false;

        const qtyInputs = variantContainer.querySelectorAll('.attr-qty-input');
        qtyInputs.forEach(qtyInp => {
            const cardItem = qtyInp.closest('.attr-item-card') || qtyInp.closest('.col-12') || qtyInp.parentElement;
            const checkbox = cardItem ? cardItem.querySelector('.attr-check-toggle') : null;
            if (!checkbox || checkbox.checked) {
                hasActiveAttributes = true;
                const val = parseInt(qtyInp.value, 10);
                if (!isNaN(val) && val > 0) {
                    totalSum += val;
                }
            }
        });

        // Automatically sum all active sub-quantities into Total Order Quantity
        if (hasActiveAttributes && totalSum > 0 && qtyInput) {
            qtyInput.value = totalSum;
        }

        updateCalculations();
    }

    function updateCalculations() {
        if (!productSelect || !qtyInput || !unitPriceInput) return;

        const qty = parseFloat(qtyInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;

        const selectedOpt = productSelect.options[productSelect.selectedIndex];
        const gstRatePercent = selectedOpt && selectedOpt.dataset.gst ? parseFloat(selectedOpt.dataset.gst) : 18.0;
        
        if (gstRateLabel) gstRateLabel.textContent = gstRatePercent + '%';

        const subtotal = qty * unitPrice;
        const taxAmount = subtotal * (gstRatePercent / 100.0);
        const totalAmount = subtotal + taxAmount;

        const formatINR = (val) => '₹ ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (calcSubtotal) calcSubtotal.textContent = formatINR(subtotal);
        if (calcTax) calcTax.textContent = formatINR(taxAmount);
        if (calcTotal) calcTotal.textContent = formatINR(totalAmount);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    if (productSelect) {
        productSelect.addEventListener('change', function() {
            unitPriceInput.dataset.autoSet = '1';
            updateProductMetaAndVariants();
        });
    }

    if (warehouseSelect) {
        warehouseSelect.addEventListener('change', function() {
            // When user manually changes warehouse, re-lookup stock for selected warehouse
            const pid = productSelect ? productSelect.options[productSelect.selectedIndex].dataset.id : 0;
            const wid = this.value;
            const unit = productSelect ? productSelect.options[productSelect.selectedIndex].dataset.unit : 'Pcs';
            const availQty = (stockMap[pid] && stockMap[pid][wid]) ? stockMap[pid][wid] : 0;
            if (metaStock) {
                if (availQty > 0) {
                    metaStock.className = 'badge bg-success px-3 py-1.5 fw-bold fs-6';
                    metaStock.innerHTML = `<i class="bi bi-box-seam me-1"></i> Stock Available: ${availQty} ${unit}`;
                } else {
                    metaStock.className = 'badge bg-danger px-3 py-1.5 fw-bold fs-6';
                    metaStock.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Out of Stock in Selected Warehouse (${availQty} ${unit})`;
                }
            }
        });
    }

    if (qtyInput) {
        qtyInput.addEventListener('input', updateCalculations);
        qtyInput.addEventListener('keyup', updateCalculations);
        qtyInput.addEventListener('change', updateCalculations);
    }

    if (unitPriceInput) {
        unitPriceInput.addEventListener('input', function() {
            this.dataset.autoSet = '0';
            updateCalculations();
        });
        unitPriceInput.addEventListener('keyup', updateCalculations);
    }

    // Initial Trigger
    updateProductMetaAndVariants();
});
</script>

<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border">
            <div class="modal-header border-bottom bg-success bg-opacity-10 py-3">
                <h5 class="modal-title font-heading fw-bold text-success">
                    <i class="bi bi-person-plus-fill me-2"></i>Register New Customer / Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/customers/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="redirect" value="<?= url('/sales/create') ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Customer Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Acme Global Logistics Pvt Ltd" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Customer Code (Optional)</label>
                            <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. CUST-ACME" style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="billing@acmeglobal.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">GSTIN / Tax ID</label>
                            <input type="text" name="gstin" maxlength="15" class="form-control text-uppercase font-monospace" placeholder="27AAAAA0000A1Z5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Approved Credit Limit (₹)</label>
                            <input type="number" step="0.01" name="credit_limit" class="form-control font-monospace" placeholder="100000.00" value="100000.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Billing Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Suite 402, Enterprise Park, Mumbai..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Save & Register Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickFillSampleSo() {
    const prodSelect = document.getElementById('soProductSelect');
    const qtyInput = document.getElementById('soQtyInput');

    if (prodSelect && prodSelect.options.length > 0) {
        prodSelect.selectedIndex = 0;
        if (qtyInput) qtyInput.value = 2;

        const event = new Event('change');
        prodSelect.dispatchEvent(event);
    }
}
</script>
