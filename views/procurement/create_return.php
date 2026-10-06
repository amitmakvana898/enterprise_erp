<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Process Purchase Return & Supplier Debit Note</h3>
        <p class="text-muted small mb-0">Issue vendor return, reverse warehouse stock, generate Debit/Credit Memo, and update accounts ledger</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="quickFillSampleReturn()">
            <i class="bi bi-lightning-charge-fill me-1"></i> Quick Fill Defective Return
        </button>
        <a href="<?= url('/procurement/returns') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Back to Returns
        </a>
    </div>
</div>

<div class="card p-4 shadow-sm mb-4">
    <form action="<?= url('/procurement/returns/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Vendor & Warehouse Section -->
        <h5 class="fw-bold mb-3"><i class="bi bi-building-dash text-warning me-2"></i>Vendor & Warehouse Information</h5>
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Select Vendor / Supplier *</label>
                <div class="input-group">
                    <span class="input-group-text text-warning"><i class="bi bi-building"></i></span>
                    <select name="supplier_id" class="form-select fw-semibold" required>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Code: <?= e($s['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">Select Source Warehouse (Reversal Origin) *</label>
                <div class="input-group">
                    <span class="input-group-text text-info"><i class="bi bi-house-gear"></i></span>
                    <select name="warehouse_id" class="form-select fw-semibold" required>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (Code: <?= e($w['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Returned Item Details -->
        <h5 class="fw-bold mb-3"><i class="bi bi-box-seam-fill text-danger me-2"></i>Returned Material Items</h5>
        <div class="card p-3 rounded-3 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="returnItemsTable">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th style="width: 35%;">Select Product *</th>
                            <th style="width: 15%; text-align: center;">Qty *</th>
                            <th style="width: 20%;">Purchase Rate (₹) *</th>
                            <th style="width: 25%;">Reason *</th>
                            <th style="width: 5%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="returnItemsContainer">
                        <!-- Dynamic items will go here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm fw-bold" onclick="addReturnItemRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Product Item
                </button>
            </div>
        </div>

        <!-- Live Credit Note Valuation Banner -->
        <div id="returnBudgetBanner" class="p-3 mb-4 rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-2 shadow-sm border border-danger border-opacity-25" style="background: rgba(239, 68, 68, 0.08);">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-calculator-fill text-danger fs-3"></i>
                <div>
                    <span class="text-muted small d-block">Total Supplier Credit Note Valuation (incl 18% GST):</span>
                    <h3 class="fw-bold text-danger mb-0 font-monospace" id="returnTotalVal">₹0.00</h3>
                </div>
            </div>
            <span class="badge bg-danger text-white px-3 py-2 fw-bold font-monospace">Stock Reversal Active</span>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/returns') ?>" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Cancel</a>
            <button type="submit" class="btn btn-danger px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-check-circle-fill me-1.5"></i> Process Return & Issue Credit Note
            </button>
        </div>
    </form>
</div>

<script>
let returnItemCount = 0;
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

function addReturnItemRow(productId = '', qty = 1, unitPrice = '', reason = '') {
    const container = document.getElementById('returnItemsContainer');
    if (!container) return;

    const rowId = returnItemCount++;
    const tr = document.createElement('tr');
    tr.id = `return-row-${rowId}`;

    let optionsHtml = '<option value="">-- Select Product --</option>';
    productsList.forEach(p => {
        const selected = (p.id == productId) ? 'selected' : '';
        optionsHtml += `<option value="${p.id}" data-rate="${p.purchase_rate}" ${selected}>${escapeHtml(p.name)}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select name="items[${rowId}][product_id]" class="form-select return-row-product" onchange="handleRowProductChange(${rowId})" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowId}][qty]" class="form-control text-center fw-bold return-row-qty" min="1" value="${qty}" oninput="calculateReturnTotal()" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${rowId}][unit_price]" class="form-control text-center font-monospace text-warning fw-semibold return-row-price" id="return-price-${rowId}" value="${unitPrice}" oninput="calculateReturnTotal()" required>
        </td>
        <td>
            <input type="text" name="items[${rowId}][reason]" class="form-control return-row-reason" placeholder="Reason for return" value="${escapeHtml(reason || '[QC Rejected] Damaged components')}" required>
        </td>
        <td class="text-end">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeReturnItemRow(${rowId})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    container.appendChild(tr);
    handleRowProductChange(rowId);
    calculateReturnTotal();
}

function removeReturnItemRow(rowId) {
    const container = document.getElementById('returnItemsContainer');
    if (container && container.querySelectorAll('tr[id^="return-row-"]').length <= 1) {
        alert('Return must contain at least one product item.');
        return;
    }
    const row = document.getElementById(`return-row-${rowId}`);
    if (row) row.remove();
    calculateReturnTotal();
}

function handleRowProductChange(rowId) {
    const row = document.getElementById(`return-row-${rowId}`);
    if (!row) return;

    const select = row.querySelector('.return-row-product');
    const priceInput = row.querySelector('.return-row-price');

    if (!select || !priceInput) return;

    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        const price = opt.getAttribute('data-rate') || '0';
        if (priceInput.value === '') {
            priceInput.value = parseFloat(price).toFixed(2);
        }
    }
    calculateReturnTotal();
}

function calculateReturnTotal() {
    const container = document.getElementById('returnItemsContainer');
    const totalVal = document.getElementById('returnTotalVal');
    if (!container || !totalVal) return;

    let subtotalSum = 0;

    container.querySelectorAll('tr[id^="return-row-"]').forEach(row => {
        const select = row.querySelector('.return-row-product');
        const priceInput = row.querySelector('.return-row-price');
        const qtyInput = row.querySelector('.return-row-qty');
        if (!select || !priceInput || !qtyInput) return;

        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const price = parseFloat(priceInput.value) || 0;
            const qty = parseFloat(qtyInput.value) || 0;
            subtotalSum += price * qty;
        }
    });

    const totalWithGst = subtotalSum * 1.18;
    totalVal.innerText = '₹' + totalWithGst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function quickFillSampleReturn() {
    const container = document.getElementById('returnItemsContainer');
    if (container) container.innerHTML = '';
    
    if (productsList.length > 0) {
        addReturnItemRow(productsList[0].id, 5, parseFloat(productsList[0].purchase_rate).toFixed(2), '[QC Inspector Rejected] Hardware component failed electrical tolerance test.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addReturnItemRow();
});
</script>
