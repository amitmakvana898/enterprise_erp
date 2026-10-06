<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-shield-check-fill me-2.5 text-warning"></i>Perform Quality Control (QC) Inspection</h3>
        <p class="page-header-sub">Verify received shipment quantities, set acceptance/rejection criteria, and log batches.</p>
    </div>
    <a href="<?= url('/procurement/grns') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to GRNs
    </a>
</div>

<div class="card p-4 shadow-sm border">
    <form action="<?= url('/procurement/grns/qc/store/' . $grn['id']) ?>" method="POST" class="needs-validation">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Header GRN/PO Info Summary -->
        <div class="card p-3.5 mb-4 rounded-3" style="background:var(--bg-page); border:1px solid var(--border);">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">GRN Reference</small>
                    <span class="fw-bold text-warning fs-5">📋 <?= e($grn['grn_no']) ?></span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Purchase Order Reference</small>
                    <span class="fw-bold text-info">🛒 <?= e($grn['po_no']) ?></span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Supplier / Vendor</small>
                    <span class="fw-bold" style="color:var(--text-primary);"><?= e($grn['supplier_name']) ?></span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Destination Warehouse</small>
                    <span class="fw-bold" style="color:var(--text-primary);"><?= e($grn['warehouse_name']) ?></span>
                </div>
            </div>
        </div>

        <!-- QC Items Table -->
        <h5 class="fw-bold mb-3"><i class="bi bi-list-check text-warning me-2"></i>Shipment Inspection Checklist</h5>
        <div class="card p-3 rounded-3 border mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="qcItemsTable">
                    <thead>
                        <tr class="text-muted small">
                            <th style="width: 35%;">Product / Item Name</th>
                            <th style="width: 15%; text-align: center;">Received Qty</th>
                            <th style="width: 15%; text-align: center;">Accepted Qty *</th>
                            <th style="width: 15%; text-align: center;">Rejected Qty</th>
                            <th style="width: 20%;">Batch & Expiry Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grnItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold" style="color:var(--text-primary);"><?= e($item['product_name']) ?></div>
                                    <div class="small text-muted font-monospace"><?= e($item['product_sku']) ?></div>
                                    <?php if (!empty($item['attribute_values'])): ?>
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            <?php 
                                             $attrs = json_decode($item['attribute_values'], true);
                                            if (is_array($attrs)): 
                                                foreach ($attrs as $key => $val): ?>
                                                    <span class="badge bg-secondary text-light small" style="font-size:0.75rem;"><strong class="text-warning"><?= e($key) ?>:</strong> <?= e($val) ?></span>
                                                <?php endforeach; 
                                            endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-monospace fw-bold text-info">
                                    <span id="rec-qty-<?= $item['id'] ?>"><?= (int)$item['received_qty'] ?></span> Units
                                </td>
                                <td>
                                    <div class="input-group input-group-sm mx-auto" style="max-width: 120px;">
                                        <input type="number" 
                                               name="items[<?= $item['id'] ?>][accepted_qty]" 
                                               class="form-control text-center fw-bold text-success font-monospace" 
                                               min="0" 
                                               max="<?= (int)$item['received_qty'] ?>" 
                                               value="<?= (int)$item['received_qty'] ?>" 
                                               id="accept-input-<?= $item['id'] ?>"
                                               oninput="calculateRejected(<?= $item['id'] ?>)"
                                               required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm mx-auto" style="max-width: 120px;">
                                        <input type="number" 
                                               name="items[<?= $item['id'] ?>][rejected_qty]" 
                                               class="form-control text-center fw-bold text-danger font-monospace" 
                                               value="0" 
                                               id="reject-input-<?= $item['id'] ?>"
                                               readonly>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1.5">
                                        <small class="text-muted d-block" style="font-size:0.7rem;">Batch Number</small>
                                        <input type="text" 
                                               name="items[<?= $item['id'] ?>][batch_no]" 
                                               class="form-control form-control-sm font-monospace" 
                                               value="<?= e($item['batch_no'] ?: 'BATCH-' . date('Ym') . '-' . rand(10,99)) ?>" 
                                               required>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block" style="font-size:0.7rem;">Expiry Date</small>
                                        <input type="date" 
                                               name="items[<?= $item['id'] ?>][exp_date]" 
                                               class="form-control form-control-sm font-monospace" 
                                               value="<?= e($item['exp_date'] ?: date('Y-m-d', strtotime('+2 years'))) ?>" 
                                               required>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Overall Inspection Metadata Section -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <label class="form-label text-warning fw-bold">Overall Inspection Status *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-activity"></i></span>
                    <select name="overall_status" class="form-select fw-semibold" required>
                        <option value="passed" selected>✅ Passed (Inspection Satisfactory)</option>
                        <option value="conditional">⚠️ Conditional (Minor Issues / Approved)</option>
                        <option value="failed">❌ Failed (Shipment Rejected)</option>
                    </select>
                </div>
                <small class="text-muted">Overall verdict determining stock bin release.</small>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">Inspector Remarks & Notes</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Describe QC observations, seal verification details, package checks, etc..."></textarea>
            </div>
        </div>

        <!-- Submit & Control Buttons -->
        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/grns') ?>" class="btn btn-outline-secondary">Cancel Inspection</a>
            <button type="submit" class="btn btn-gradient-primary">
                <i class="bi bi-file-earmark-check-fill me-1.5 text-warning"></i> Authorize & Release Stock
            </button>
        </div>
    </form>
</div>

<script>
function calculateRejected(itemId) {
    const recSpan = document.getElementById(`rec-qty-${itemId}`);
    const acceptInput = document.getElementById(`accept-input-${itemId}`);
    const rejectInput = document.getElementById(`reject-input-${itemId}`);

    if (!recSpan || !acceptInput || !rejectInput) return;

    const recQty = parseInt(recSpan.textContent) || 0;
    let acceptQty = parseInt(acceptInput.value) || 0;

    if (acceptQty > recQty) {
        acceptQty = recQty;
        acceptInput.value = recQty;
    } else if (acceptQty < 0) {
        acceptQty = 0;
        acceptInput.value = 0;
    }

    rejectInput.value = recQty - acceptQty;
}
</script>
