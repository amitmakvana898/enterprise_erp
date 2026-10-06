<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>Create Purchase Requisition (PR)</h3>
        <p class="text-muted small mb-0">Submit internal material requisition with dynamic product specifications, required delivery dates, and live budget estimations</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="quickFillSamplePr()">
            <i class="bi bi-lightning-charge-fill me-1"></i> Quick Fill Sample
        </button>
        <a href="<?= url('/procurement/requests') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Requisitions
        </a>
    </div>
</div>

<div class="card p-4 shadow-sm mb-4">
    <form action="<?= url('/procurement/requests/store') ?>" method="POST" id="prForm">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Header Requisition Information -->
        <h5 class="fw-bold mb-3"><i class="bi bi-person-badge text-info me-2"></i>Requisitioner & Department Information</h5>
        <div class="row g-3 mb-4 p-3 rounded-3 border bg-body-tertiary">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Requisitioner / Requesting Officer *</label>
                <div class="input-group">
                    <span class="input-group-text text-primary"><i class="bi bi-person-circle"></i></span>
                    <select name="requested_by" id="prRequesterSelect" class="form-select" required>
                        <?php $authUser = auth_user(); ?>
                        <?php
                        $deptMap = [
                            'Sales Manager'            => 'Sales & Marketing',
                            'Procurement Manager'      => 'Warehouse & Logistics',
                            'Warehouse Manager'        => 'Warehouse & Logistics',
                            'Finance Manager'          => 'Finance & Accounts',
                            'QC Inspector'             => 'Production & Manufacturing',
                            'Department Requisitioner' => 'IT Infrastructure',
                            'Company Administrator'    => 'HR & Administration',
                            'Super Administrator'      => 'IT Infrastructure',
                        ];
                        ?>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <?php $defaultDept = $deptMap[$u['name']] ?? 'IT Infrastructure'; ?>
                                <option value="<?= $u['id'] ?>" data-dept="<?= e($defaultDept) ?>" <?= ($u['id'] == ($authUser['id'] ?? 0)) ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?> (<?= e($u['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="<?= $authUser['id'] ?? 1 ?>" data-dept="IT Infrastructure"><?= e($authUser['name'] ?? 'Current User') ?></option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Requesting Department *</label>
                <div class="input-group">
                    <span class="input-group-text text-info"><i class="bi bi-building"></i></span>
                    <select name="department" id="prDepartmentSelect" class="form-select" required>
                        <option value="IT Infrastructure" selected>IT & Computer Infrastructure</option>
                        <option value="Sales & Marketing">Sales & Marketing</option>
                        <option value="Warehouse & Logistics">Warehouse & Logistics</option>
                        <option value="Production & Manufacturing">Production & Manufacturing</option>
                        <option value="HR & Administration">HR & Administration</option>
                        <option value="Finance & Accounts">Finance & Accounts</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Priority Level *</label>
                <div class="input-group">
                    <span class="input-group-text text-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <select name="priority" id="prPrioritySelect" class="form-select" required>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="high">High Priority</option>
                        <option value="urgent">Urgent Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Product Selection Section -->
        <h5 class="fw-bold mb-3"><i class="bi bi-box-seam text-warning me-2"></i>Material & Product Items Requisition</h5>
        
        <div class="card p-3 rounded-3 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="prItemsTable">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th style="width: 45%;">Select Product *</th>
                            <th style="width: 15%;">SKU</th>
                            <th style="width: 15%;">Purchase Rate</th>
                            <th style="width: 15%;">Qty *</th>
                            <th style="width: 10%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="prItemsContainer">
                        <!-- Dynamic rows injected here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm fw-bold" onclick="addPrItemRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Another Product Item
                </button>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Required By Target Date</label>
                <input type="date" name="required_by_date" id="prRequiredByDate" class="form-control fw-semibold" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
            </div>
        </div>

        <!-- Business Justification Notes -->
        <div class="mb-4">
            <label class="form-label small fw-semibold">Business Justification & Requisition Notes *</label>
            <textarea name="notes" id="prNotesTextarea" class="form-control" rows="3" placeholder="Explain the project or operational requirement..." required>Required for department operations & material inventory replenishment.</textarea>
        </div>

        <!-- Requisition Bill Summary Card (Footer format!) -->
        <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-file-earmark-spreadsheet text-info me-2"></i>Requisition Bill Summary</h5>
        <div class="card p-4 rounded-3 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th>Unit Rate</th>
                            <th class="text-center">Requested Qty</th>
                            <th class="text-end">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody id="prBillSummaryBody">
                        <!-- Dynamically populated in JS -->
                    </tbody>
                    <tfoot>
                        <tr class="border-top">
                            <td colspan="4" class="text-end fw-bold">Estimated Grand Total:</td>
                            <td class="text-end font-monospace text-success fw-bold fs-5" id="prEstBudgetValSide">₹0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/requests') ?>" class="btn btn-outline-secondary fw-bold px-4 py-2">Cancel</a>
            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                <i class="bi bi-send-fill me-1.5"></i> Submit Purchase Requisition
            </button>
        </div>
    </form>
</div>

<script>
let prItemCount = 0;
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

function addPrItemRow(productId = '', qty = 5) {
    const container = document.getElementById('prItemsContainer');
    if (!container) return;

    const rowId = prItemCount++;
    const tr = document.createElement('tr');
    tr.id = `pr-row-${rowId}`;
    
    let optionsHtml = '<option value="">-- Select Product --</option>';
    productsList.forEach(p => {
        const selected = (p.id == productId) ? 'selected' : '';
        optionsHtml += `<option value="${p.id}" data-sku="${escapeHtml(p.sku)}" data-rate="${p.purchase_rate}" ${selected}>${escapeHtml(p.name)}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${rowId}][product_id]" class="form-select pr-row-product" onchange="handleRowProductChange(${rowId})" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="text" class="form-control font-monospace pr-row-sku" id="pr-sku-${rowId}" readonly value="">
        </td>
        <td>
            <input type="text" class="form-control font-monospace text-success pr-row-rate" id="pr-rate-${rowId}" readonly value="₹0.00">
        </td>
        <td>
            <input type="number" name="items[${rowId}][requested_qty]" class="form-control text-center fw-bold pr-row-qty" min="1" value="${qty}" oninput="calculatePrTotal()" required>
        </td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
                <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="toggleAttrRow(${rowId})" title="Configure Product Attributes & Specifications">
                    <i class="bi bi-sliders me-1"></i> Specs
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePrItemRow(${rowId})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;

    const attrTr = document.createElement('tr');
    attrTr.id = `pr-attrs-row-${rowId}`;
    attrTr.className = 'd-none bg-body-tertiary';
    attrTr.innerHTML = `
        <td colspan="5" class="p-3">
            <div class="card border-warning p-3 rounded-3 mb-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="small fw-bold text-warning"><i class="bi bi-sliders me-1"></i>Configure Product Attributes & Specification Variants:</div>
                    <span class="badge bg-warning text-dark font-monospace" style="font-size:0.7rem;">Live Specification Mapping</span>
                </div>
                <div class="row g-3" id="pr-attrs-grid-${rowId}">
                    <!-- Loaded dynamically via AJAX -->
                </div>
            </div>
        </td>
    `;

    container.appendChild(tr);
    container.appendChild(attrTr);
    handleRowProductChange(rowId);
    calculatePrTotal();
}

function removePrItemRow(rowId) {
    const container = document.getElementById('prItemsContainer');
    // Keep at least one row
    if (container && container.querySelectorAll('tr[id^="pr-row-"]').length <= 1) {
        alert('Requisition must contain at least one product item.');
        return;
    }
    const row = document.getElementById(`pr-row-${rowId}`);
    const attrRow = document.getElementById(`pr-attrs-row-${rowId}`);
    if (row) row.remove();
    if (attrRow) attrRow.remove();
    calculatePrTotal();
}

function toggleAttrRow(rowId) {
    const attrRow = document.getElementById(`pr-attrs-row-${rowId}`);
    if (attrRow) {
        attrRow.classList.toggle('d-none');
    }
}

function handleRowProductChange(rowId) {
    const row = document.getElementById(`pr-row-${rowId}`);
    if (!row) return;

    const select = row.querySelector('.pr-row-product');
    const skuInput = row.querySelector('.pr-row-sku');
    const rateInput = row.querySelector('.pr-row-rate');
    const attrsRow = document.getElementById(`pr-attrs-row-${rowId}`);
    const attrsGrid = document.getElementById(`pr-attrs-grid-${rowId}`);

    if (!select || !skuInput || !rateInput) return;

    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        skuInput.value = opt.getAttribute('data-sku') || '';
        const rate = parseFloat(opt.getAttribute('data-rate')) || 0;
        rateInput.value = '₹' + rate.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Fetch attributes for this product
        fetch('<?= url('/products/attributes/') ?>' + opt.value)
            .then(response => response.json())
            .then(res => {
                if (res.success && res.attributes && res.attributes.length > 0) {
                    attrsRow.classList.remove('d-none');
                    attrsGrid.innerHTML = '';
                    
                    res.attributes.forEach(attr => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6';
                        
                        let inputHtml = '';
                        const lowerType = (attr.type || 'text').toLowerCase();
                        const options = attr.options || [];
                        
                        if (lowerType === 'select' && options.length > 0) {
                            inputHtml = `<div class="p-2.5 rounded bg-body-secondary border">
                                <div class="small text-info fw-bold mb-2"><i class="bi bi-grid-3x3-gap-fill me-1"></i>Enter Variant Quantities:</div>
                                <div class="row g-2">`;
                            options.forEach(optVal => {
                                inputHtml += `
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center justify-content-between gap-2 p-1.5 rounded bg-body border">
                                            <span class="small fw-semibold text-warning text-truncate" title="${optVal}">${optVal}</span>
                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                <input type="number" name="items[${rowId}][attributes][${attr.name}][${optVal}]" class="form-control text-center fw-bold pr-row-variant-qty" min="0" placeholder="0" oninput="recalcRowVariantQty(${rowId})">
                                                <span class="input-group-text text-muted" style="font-size:0.65rem; padding: 0 4px;">Pcs</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            inputHtml += `</div></div>`;
                        } else {
                            inputHtml = `<input type="text" name="items[${rowId}][attributes][${attr.name}]" class="form-control" placeholder="Specify ${attr.name}...">`;
                        }
                        
                        col.innerHTML = `
                            <label class="form-label small fw-bold mb-1"><i class="bi bi-tag-fill text-warning me-1"></i>${attr.name}</label>
                            ${inputHtml}
                        `;
                        attrsGrid.appendChild(col);
                    });
                } else {
                    attrsRow.classList.add('d-none');
                    attrsGrid.innerHTML = '';
                }
            })
            .catch(() => {
                attrsRow.classList.add('d-none');
            });
    } else {
        skuInput.value = '';
        rateInput.value = '₹0.00';
        attrsRow.classList.add('d-none');
    }
    calculatePrTotal();
}

function recalcRowVariantQty(rowId) {
    const row = document.getElementById(`pr-row-${rowId}`);
    const attrsRow = document.getElementById(`pr-attrs-row-${rowId}`);
    if (!row || !attrsRow) return;

    const qtyInput = row.querySelector('.pr-row-qty');
    const inputs = attrsRow.querySelectorAll('.pr-row-variant-qty');
    let totalVariantQty = 0;
    inputs.forEach(input => {
        const val = parseInt(input.value) || 0;
        if (val > 0) {
            totalVariantQty += val;
        }
    });

    if (totalVariantQty > 0 && qtyInput) {
        qtyInput.value = totalVariantQty;
        calculatePrTotal();
    }
}

function calculatePrTotal() {
    const container = document.getElementById('prItemsContainer');
    const budgetValSide = document.getElementById('prEstBudgetValSide');
    const billBody = document.getElementById('prBillSummaryBody');

    if (!container || !budgetValSide || !billBody) return;

    let grandTotal = 0;
    let billHtml = '';

    container.querySelectorAll('tr[id^="pr-row-"]').forEach(row => {
        const select = row.querySelector('.pr-row-product');
        const qtyInput = row.querySelector('.pr-row-qty');
        if (!select || !qtyInput) return;

        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const name = opt.text.split('(')[0].trim();
            const sku = opt.getAttribute('data-sku') || '';
            const rate = parseFloat(opt.getAttribute('data-rate')) || 0;
            const qty = parseInt(qtyInput.value) || 0;
            const total = rate * qty;
            grandTotal += total;

            billHtml += `
                <tr class="border-bottom border-secondary border-opacity-10">
                    <td class="fw-semibold">${escapeHtml(name)}</td>
                    <td class="font-monospace text-muted">${escapeHtml(sku)}</td>
                    <td class="font-monospace text-muted">₹${rate.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="text-center font-monospace text-warning fw-bold">${qty}</td>
                    <td class="text-end font-monospace fw-bold">₹${total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `;
        }
    });

    if (billHtml === '') {
        billHtml = `
            <tr>
                <td colspan="5" class="text-center text-muted py-3">No products selected.</td>
            </tr>
        `;
    }

    billBody.innerHTML = billHtml;
    budgetValSide.innerText = '₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function quickFillSamplePr() {
    const container = document.getElementById('prItemsContainer');
    if (container) container.innerHTML = '';
    
    // Fill 2 sample rows
    addPrItemRow(1, 10);
    addPrItemRow(2, 5);
    
    document.getElementById('prPrioritySelect').value = 'high';
    document.getElementById('prNotesTextarea').value = 'Urgent material requirement for Q3 production order fulfillment.';
}

document.addEventListener('DOMContentLoaded', function() {
    addPrItemRow();

    const requesterSelect = document.getElementById('prRequesterSelect');
    const deptSelect = document.getElementById('prDepartmentSelect');

    if (requesterSelect && deptSelect) {
        requesterSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            const dept = selectedOpt ? selectedOpt.getAttribute('data-dept') : '';
            if (dept) {
                for (let i = 0; i < deptSelect.options.length; i++) {
                    if (deptSelect.options[i].value === dept) {
                        deptSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    }
});
</script>
