<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-truck-front-fill me-2.5" style="color:var(--warning)"></i>Process Goods Receipt Note (GRN)</h3>
        <p class="page-header-sub">Record incoming shipment against active Purchase Order and queue for Quality Control (QC)</p>
    </div>
    <a href="<?= url('/procurement/grns') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to GRNs
    </a>
</div>

<div class="card p-4">
    <form action="<?= url('/procurement/grns/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Select Approved Purchase Order (PO) *</label>
            <select name="po_id" id="grnPoSelect" class="form-select fw-bold" required onchange="loadPoItems(this.value)">
                <option value="">-- Choose Approved PO --</option>
                <?php foreach ($orders as $po): ?>
                    <option value="<?= $po['id'] ?>"
                            <?= (!empty($prefilledPo) && (int)$po['id'] === (int)$prefilledPo) ? 'selected' : '' ?>
                            data-no="<?= e($po['po_no']) ?>"
                            data-supplier="<?= e($po['supplier_name'] ?? 'Supplier') ?>"
                            data-warehouse="<?= e($po['warehouse_name'] ?? 'Main Warehouse') ?>">
                        <?= e($po['po_no']) ?> - <?= e($po['supplier_name']) ?> (<?= (int)$po['item_count'] ?> items)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Delivery Challan / LR No</label>
            <input type="text" name="challan_no" class="form-control fw-bold" placeholder="e.g. CH-2026-9901 / LR-8842">
        </div>
        <div class="col-md-3">
            <label class="form-label text-warning"><i class="bi bi-calendar-event me-1"></i>Next Scheduled Delivery Date</label>
            <input type="date" name="next_scheduled_delivery_date" class="form-control fw-bold border-warning" min="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <!-- PO Info Card -->
    <div id="poInfoCard" class="card p-3.5 mb-4 rounded-3 d-none" style="background:var(--bg-page);border:1px solid var(--border);">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-uppercase" style="color:var(--text-muted);letter-spacing:0.06em;">
                <i class="bi bi-info-circle-fill text-primary me-1.5"></i> Purchase Order Shipment Summary
            </span>
            <span class="badge bg-success">APPROVED PO</span>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Supplier / Vendor</small>
                <span class="fw-bold" style="color:var(--text-primary);" id="poSupplierLabel">-</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Destination Warehouse</small>
                <span class="fw-bold" style="color:var(--text-primary);" id="poWarehouseLabel">-</span>
            </div>
        </div>
    </div>

    <!-- GRN Items Table -->
    <h5 class="fw-bold text-white mb-3 mt-4"><i class="bi bi-box-seam text-warning me-2"></i>Shipment Verification & Partial Delivery Tracker</h5>
    <div class="card p-3 rounded-3 bg-dark border-secondary mb-4">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" id="grnItemsTable">
                <thead>
                    <tr class="text-secondary small">
                        <th style="width: 40%;">Product / Item Name & Specs</th>
                        <th style="width: 15%; text-align: center;">PO Ordered</th>
                        <th style="width: 15%; text-align: center;">Already Received</th>
                        <th style="width: 15%; text-align: center;">Remaining Qty</th>
                        <th style="width: 15%; text-align: center;">This Shipment Qty *</th>
                    </tr>
                </thead>
                <tbody id="grnItemsContainer">
                    <!-- Dynamic product rows go here -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="pt-3 border-top d-flex justify-content-end gap-2">
        <a href="<?= url('/procurement/grns') ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-gradient-primary">
            <i class="bi bi-box-seam me-1.5"></i> Receive Material & Queue Quality Check
        </button>
    </div>
</form>
</div>

<script>
let grnItemCount = 0;

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

function formatAttributes(jsonStr) {
    if (!jsonStr) return '';
    try {
        const obj = JSON.parse(jsonStr);
        if (Object.keys(obj).length === 0) return '';
        let html = '<div class="mt-1.5 d-flex flex-wrap gap-1">';
        for (const [key, val] of Object.entries(obj)) {
            html += `<span class="badge bg-secondary text-light small" style="font-size:0.75rem;"><strong class="text-warning">${escapeHtml(key)}:</strong> ${escapeHtml(val)}</span>`;
        }
        html += '</div>';
        return html;
    } catch (e) {
        if (jsonStr.trim() !== '') {
            return `<div class="text-secondary small mt-1">Config: ${escapeHtml(jsonStr)}</div>`;
        }
        return '';
    }
}

function addGrnItemRow(productId, productName, productSku, orderedQty, alreadyReceivedQty = 0, remainingQty = null, receivedQty = '', attributeValues = '') {
    const container = document.getElementById('grnItemsContainer');
    if (!container) return;

    if (remainingQty === null || remainingQty === undefined) {
        remainingQty = Math.max(0, orderedQty - alreadyReceivedQty);
    }
    
    if (receivedQty === '') {
        receivedQty = remainingQty > 0 ? remainingQty : 0;
    }

    const rowId = grnItemCount++;
    const tr = document.createElement('tr');
    tr.id = `grn-row-${rowId}`;

    const attrHtml = formatAttributes(attributeValues);

    tr.innerHTML = `
        <td>
            <div class="fw-semibold text-white">${escapeHtml(productName)}</div>
            <div class="small text-secondary font-monospace">${escapeHtml(productSku)}</div>
            ${attrHtml}
            <input type="hidden" name="items[${rowId}][product_id]" value="${productId}">
            <input type="hidden" name="items[${rowId}][attribute_values]" value="${escapeHtml(attributeValues)}">
        </td>
        <td class="text-center font-monospace fw-bold text-info">
            ${orderedQty} Units
            <input type="hidden" name="items[${rowId}][ordered_qty]" value="${orderedQty}">
        </td>
        <td class="text-center font-monospace fw-bold text-secondary">
            ${alreadyReceivedQty} Units
        </td>
        <td class="text-center font-monospace fw-bold text-warning">
            ${remainingQty} Units
        </td>
        <td>
            <div class="input-group input-group-sm mx-auto" style="width: 140px;">
                <input type="number" name="items[${rowId}][received_qty]" class="form-control bg-dark text-light border-secondary text-center fw-bold fs-6 text-success" min="1" max="${remainingQty > 0 ? remainingQty : orderedQty}" value="${receivedQty}" required>
                <span class="input-group-text bg-dark border-secondary text-secondary">Units</span>
            </div>
        </td>
    `;

    container.appendChild(tr);
}

function loadPoItems(poId) {
    const infoCard = document.getElementById('poInfoCard');
    const supplierLabel = document.getElementById('poSupplierLabel');
    const warehouseLabel = document.getElementById('poWarehouseLabel');
    const poSelect = document.getElementById('grnPoSelect');

    if (!poId) {
        infoCard.classList.add('d-none');
        return;
    }

    const selectedOpt = poSelect.options[poSelect.selectedIndex];
    supplierLabel.textContent = selectedOpt.dataset.supplier || '-';
    warehouseLabel.textContent = selectedOpt.dataset.warehouse || '-';
    infoCard.classList.remove('d-none');

    fetch('<?= url('/procurement/orders/items/') ?>' + poId)
        .then(response => response.json())
        .then(res => {
            if (res.success && res.items && res.items.length > 0) {
                const container = document.getElementById('grnItemsContainer');
                if (container) container.innerHTML = '';
                
                res.items.forEach(item => {
                    addGrnItemRow(item.product_id, item.product_name, item.product_sku, item.qty, item.already_received_qty || 0, item.remaining_qty, '', item.attribute_values || '');
                });
            }
        })
        .catch(err => {
            console.error('Error loading PO items:', err);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($poItems)): ?>
        const container = document.getElementById('grnItemsContainer');
        if (container) container.innerHTML = '';
        <?php foreach ($poItems as $item): ?>
            addGrnItemRow(<?= $item['product_id'] ?>, '<?= e($item['product_name']) ?>', '<?= e($item['product_sku']) ?>', <?= $item['qty'] ?>, <?= $item['already_received_qty'] ?? 0 ?>, <?= $item['remaining_qty'] ?? $item['qty'] ?>, '', '<?= e($item['attribute_values'] ?? '') ?>');
        <?php endforeach; ?>
        
        // Trigger info card populate
        const poSelect = document.getElementById('grnPoSelect');
        if (poSelect && poSelect.selectedIndex > 0) {
            const selectedOpt = poSelect.options[poSelect.selectedIndex];
            document.getElementById('poSupplierLabel').textContent = selectedOpt.dataset.supplier || '-';
            document.getElementById('poWarehouseLabel').textContent = selectedOpt.dataset.warehouse || '-';
            document.getElementById('poInfoCard').classList.remove('d-none');
        }
    <?php endif; ?>
});
</script>
