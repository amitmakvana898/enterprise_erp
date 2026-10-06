<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-bag-plus-fill me-2.5" style="color:var(--primary)"></i>Issue Purchase Order (PO)</h3>
        <p class="page-header-sub">Authorize contractual purchase order agreement with real-time GST tax & valuation calculations</p>
    </div>
    <a href="<?= url('/procurement/orders') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Orders
    </a>
</div>

<?php if (!empty($selectedQuotation)): ?>
    <!-- Winning Bid Award Banner -->
    <div class="card p-3 mb-4 rounded-3" style="background:var(--success-bg);border:1px solid var(--success-border);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-success mb-1">
                    <i class="bi bi-award-fill me-1"></i> P2P Requisition Attached — Winning Quotation Award
                </span>
                <h5 class="fw-bold mb-0.5" style="color:var(--text-primary);"><?= e($selectedQuotation['supplier_name']) ?> <span class="text-muted small">(Ref: <?= e($selectedQuotation['quotation_no']) ?>)</span></h5>
                <p class="text-secondary small mb-0">
                    Linked RFQ: <strong><?= e($selectedQuotation['rfq_no']) ?> - <?= e($selectedQuotation['rfq_title']) ?></strong> | Requisitioned Item: <strong class="text-info"><?= e($selectedQuotation['product_name'] ?? 'Item') ?></strong> | Requisitioned Qty: <strong class="text-success"><?= $selectedQuotation['requested_qty'] ?? $prefilledQty ?> Units</strong> | Agreed Bid Rate: <strong class="text-warning"><?= format_currency($selectedQuotation['unit_price']) ?></strong>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-warning">
                    <i class="bi bi-lock-fill me-1"></i> P2P Product & Qty Locked
                </span>
            </div>
        </div>
    </div>
<?php elseif (!empty($prRecord)): ?>
    <!-- Requisition PR Locked Banner -->
    <div class="card p-3 mb-4 rounded-3" style="background:rgba(59,130,246,0.15);border:1px solid #3B82F6;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="badge bg-info text-dark mb-1">
                    <i class="bi bi-file-earmark-check-fill me-1"></i> P2P Requisition Active — <?= e($prRecord['request_no']) ?>
                </span>
                <h5 class="fw-bold mb-0.5 text-white"><?= e($prRecord['product_name']) ?> <span class="text-secondary small">(SKU: <?= e($prRecord['product_sku']) ?>)</span></h5>
                <p class="text-secondary small mb-0">
                    Department: <strong><?= e($prRecord['department']) ?></strong> | Priority: <strong class="text-warning"><?= strtoupper(e($prRecord['priority'])) ?></strong> | Locked Requisition Qty: <strong class="text-success fs-6"><?= $prRecord['requested_qty'] ?> Units</strong>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-primary">
                    <i class="bi bi-shield-lock-fill me-1"></i> P2P Item & Quantity Locked
                </span>
            </div>
        </div>
    </div>
<?php elseif (isset($_GET['mode']) && $_GET['mode'] === 'direct'): ?>
    <!-- Direct Express PO Fast-Track Banner -->
    <div class="card p-3.5 mb-4 rounded-3 bg-dark border-warning border-opacity-50" style="background: rgba(245, 158, 11, 0.1) !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="badge bg-warning text-dark fw-bold mb-1">
                    <i class="bi bi-lightning-charge-fill me-1"></i> DIRECT EXPRESS PO (FAST-TRACK MODE)
                </span>
                <h5 class="fw-bold text-white mb-0.5">Direct Purchase Order for Contracted & Regular Vendors</h5>
                <p class="text-secondary small mb-0">
                    Bypassing PR & RFQ bidding workflow for established regular suppliers. Simply select your vendor, add items, and click <strong>Authorize & Issue Purchase Order</strong> below.
                </p>
            </div>
            <div>
                <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 fs-6">
                    <i class="bi bi-shield-check me-1.5"></i> Direct Fast-Track Active
                </span>
            </div>
        </div>
    </div>
<?php elseif (!empty($approvedPRs)): ?>
    <!-- Quick-Load Approved PR Selection Bar -->
    <div class="card p-3 mb-4 rounded-3 bg-dark border-secondary">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <label class="form-label text-warning fw-bold mb-0 small">
                <i class="bi bi-lightning-charge-fill text-warning me-1.5"></i> Load Approved Purchase Requisition (PR):
            </label>
            <select id="poPrSelect" class="form-select bg-dark text-light border-warning form-select-sm fw-bold" style="max-width:550px;" onchange="loadPrItems(this.value)">
                <option value="">-- Optional: Select Approved PR to Auto-Fill Products --</option>
                <?php foreach ($approvedPRs as $pr): ?>
                    <option value="<?= $pr['id'] ?>">
                        📋 <?= e($pr['request_no']) ?> — <?= e($pr['department']) ?> (<?= $pr['item_count'] ?> items)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
<?php endif; ?>

<div class="card p-4">
    <form action="<?= url('/procurement/orders/store') ?>" method="POST" class="needs-validation">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <?php if (!empty($selectedQuotation)): ?>
            <input type="hidden" name="quotation_id" value="<?= $selectedQuotation['id'] ?>">
            <input type="hidden" name="rfq_id" value="<?= $selectedQuotation['rfq_id'] ?>">
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Select Vendor / Supplier *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                    <?php if (!empty($prefilledSupplier)): ?>
                        <input type="hidden" name="supplier_id" value="<?= $prefilledSupplier ?>">
                        <select class="form-select fw-bold" disabled>
                            <?php foreach ($suppliers as $s): ?>
                                <?php if ((int)$s['id'] === (int)$prefilledSupplier): ?>
                                    <option value="<?= $s['id'] ?>" selected>🔒 <?= e($s['name']) ?> (Code: <?= e($s['code']) ?> • ⭐ <?= e($s['rating']) ?>) — WINNING VENDOR</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="supplier_id" class="form-select" required>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Code: <?= e($s['code']) ?> • ⭐ <?= e($s['rating']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <?php if (!empty($prefilledSupplier)): ?>
                    <small class="text-success d-block mt-1" style="font-size:0.78rem;"><i class="bi bi-shield-check me-1"></i> Supplier is automatically locked from the approved winning quotation bid.</small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Destination Warehouse *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-house-gear"></i></span>
                    <select name="warehouse_id" class="form-select" required>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- PO Items Table -->
        <h5 class="fw-bold text-white mb-3 mt-4"><i class="bi bi-box-seam text-warning me-2"></i>Purchase Order Items</h5>
        <div class="card p-3 rounded-3 bg-dark border-secondary mb-4">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" id="poItemsTable">
                    <thead>
                        <tr class="text-secondary small">
                            <th style="width: 40%;">Select Product *</th>
                            <th style="width: 15%;">SKU</th>
                            <th style="width: 15%;">Unit Price (₹) *</th>
                            <th style="width: 15%;">GST Rate</th>
                            <th style="width: 10%;">Qty *</th>
                            <th style="width: 10%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="poItemsContainer">
                        <!-- Prefilled or dynamic items will go here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-info btn-sm fw-bold" onclick="addPoItemRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Product Item
                </button>
            </div>
        </div>

        <!-- Live Valuation Breakdown Card -->
        <div class="card p-4 rounded-3 mb-4 shadow-lg" style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid rgba(59, 130, 246, 0.3); backdrop-filter: blur(10px);">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-30">
                <span class="small fw-bold text-uppercase text-info" style="letter-spacing:0.07em;">
                    <i class="bi bi-calculator-fill me-2"></i> Purchase Order Valuation Summary
                </span>
                <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                    <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Real-Time Live Calculation
                </span>
            </div>
            <div class="row g-4 text-center align-items-center">
                <div class="col-md-3 border-end border-secondary border-opacity-30">
                    <small class="text-secondary d-block fw-semibold mb-1">Base Price (Qty × Unit Price)</small>
                    <span class="fs-4 fw-bold text-white font-monospace" id="calcSubtotal">₹ 0.00</span>
                </div>
                <div class="col-md-3 border-end border-secondary border-opacity-30">
                    <small class="text-secondary d-block fw-semibold mb-1">Estimated GST Tax</small>
                    <span class="fs-4 fw-bold text-warning font-monospace" id="calcTax">₹ 0.00</span>
                </div>
                <div class="col-md-6">
                    <small class="text-secondary d-block fw-semibold mb-1">Final Authorizing Purchase Order Total</small>
                    <div class="p-2 px-3 rounded-3 bg-success bg-opacity-15 border border-success border-opacity-40 d-inline-block">
                        <span class="fs-2 fw-extrabold text-success font-monospace" id="calcTotal">₹ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/orders') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-gradient-primary">
                <i class="bi bi-file-earmark-check me-1.5"></i> Authorize & Issue Purchase Order
            </button>
        </div>
    </form>
</div>

<script>
let poItemCount = 0;
const productsList = <?= json_encode($products) ?>;

function escapeHtml(string) {
    return String(string).replace(/[&<>"']/g, function (s) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
        }[s];
    });
}

function addPoItemRow(productId = '', qty = 1, unitPrice = '', attributeValues = '') {
    const container = document.getElementById('poItemsContainer');
    if (!container) return;

    const rowId = poItemCount++;
    const tr = document.createElement('tr');
    tr.id = `po-row-${rowId}`;

    let optionsHtml = '<option value="">-- Select Product --</option>';
    productsList.forEach(p => {
        const selected = (p.id == productId) ? 'selected' : '';
        optionsHtml += `<option value="${p.id}" data-sku="${escapeHtml(p.sku)}" data-price="${p.purchase_rate}" data-gst="${p.gst_rate || 18.00}" ${selected}>${escapeHtml(p.name)}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${rowId}][product_id]" class="form-select bg-dark text-light border-secondary po-row-product" onchange="handleRowProductChange(${rowId})" required>
                ${optionsHtml}
            </select>
            <input type="hidden" name="items[${rowId}][attribute_values]" class="po-row-attributes" value="${escapeHtml(attributeValues)}">
        </td>
        <td>
            <input type="text" class="form-control bg-dark text-secondary border-secondary font-monospace po-row-sku" id="po-sku-${rowId}" readonly value="">
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowId}][unit_price]" class="form-control bg-dark text-light border-secondary font-monospace text-warning fw-semibold po-row-price" id="po-price-${rowId}" value="${unitPrice}" oninput="updateCalculations()" required>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control bg-dark text-secondary border-secondary text-center po-row-gst" id="po-gst-${rowId}" readonly value="18%">
            </div>
        </td>
        <td>
            <input type="number" name="items[${rowId}][qty]" class="form-control bg-dark text-light border-secondary text-center fw-bold po-row-qty" min="1" value="${qty}" oninput="updateCalculations()" required>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePoItemRow(${rowId})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    container.appendChild(tr);
    handleRowProductChange(rowId);
    updateCalculations();
}

function removePoItemRow(rowId) {
    const container = document.getElementById('poItemsContainer');
    if (container && container.querySelectorAll('tr[id^="po-row-"]').length <= 1) {
        alert('Purchase Order must contain at least one item.');
        return;
    }
    const row = document.getElementById(`po-row-${rowId}`);
    if (row) row.remove();
    updateCalculations();
}

function handleRowProductChange(rowId) {
    const row = document.getElementById(`po-row-${rowId}`);
    if (!row) return;

    const select = row.querySelector('.po-row-product');
    const skuInput = row.querySelector('.po-row-sku');
    const priceInput = row.querySelector('.po-row-price');
    const gstInput = row.querySelector('.po-row-gst');

    if (!select || !skuInput || !priceInput || !gstInput) return;

    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        skuInput.value = opt.getAttribute('data-sku') || '';
        const price = opt.getAttribute('data-price') || '0';
        if (priceInput.value === '') {
            priceInput.value = parseFloat(price).toFixed(2);
        }
        const gst = opt.getAttribute('data-gst') || '18.00';
        gstInput.value = parseFloat(gst) + '%';
    } else {
        skuInput.value = '';
        gstInput.value = '18%';
    }
    updateCalculations();
}

function updateCalculations() {
    const container = document.getElementById('poItemsContainer');
    const calcSubtotal = document.getElementById('calcSubtotal');
    const calcTax = document.getElementById('calcTax');
    const calcTotal = document.getElementById('calcTotal');

    if (!container || !calcSubtotal || !calcTax || !calcTotal) return;

    let subtotalSum = 0;
    let taxSum = 0;

    container.querySelectorAll('tr[id^="po-row-"]').forEach(row => {
        const select = row.querySelector('.po-row-product');
        const priceInput = row.querySelector('.po-row-price');
        const qtyInput = row.querySelector('.po-row-qty');
        if (!select || !priceInput || !qtyInput) return;

        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const price = parseFloat(priceInput.value) || 0;
            const qty = parseFloat(qtyInput.value) || 0;
            const gstPercent = parseFloat(opt.getAttribute('data-gst')) || 18.00;

            const rowSubtotal = price * qty;
            const rowTax = rowSubtotal * (gstPercent / 100.0);

            subtotalSum += rowSubtotal;
            taxSum += rowTax;
        }
    });

    const grandTotal = subtotalSum + taxSum;
    const formatINR = (val) => '₹ ' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    calcSubtotal.textContent = formatINR(subtotalSum);
    calcTax.textContent = formatINR(taxSum);
    calcTotal.textContent = formatINR(grandTotal);
}

function loadPrItems(prId) {
    if (!prId) return;
    
    fetch('<?= url('/procurement/requests/items/') ?>' + prId)
        .then(response => response.json())
        .then(res => {
            if (res.success && res.items && res.items.length > 0) {
                const container = document.getElementById('poItemsContainer');
                if (container) container.innerHTML = '';
                
                res.items.forEach(item => {
                    addPoItemRow(item.product_id, item.requested_qty, parseFloat(item.purchase_rate).toFixed(2), item.attribute_values || '');
                });
            }
        })
        .catch(err => {
            console.error('Error loading PR items:', err);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($prItems)): ?>
        <?php foreach ($prItems as $item): ?>
            <?php 
                $prefilledItemPrice = !empty($prefilledPrice) ? $prefilledPrice : $item['purchase_rate']; 
            ?>
            addPoItemRow(<?= $item['product_id'] ?>, <?= $item['requested_qty'] ?>, <?= number_format($prefilledItemPrice, 2, '.', '') ?>, '<?= e($item['attribute_values'] ?? '') ?>');
        <?php endforeach; ?>
    <?php else: ?>
        addPoItemRow();
    <?php endif; ?>
});
</script>
