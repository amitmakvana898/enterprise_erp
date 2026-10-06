<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-file-earmark-plus-fill me-2.5" style="color:var(--warning)"></i>Create Request For Quotation (RFQ)</h3>
        <p class="page-header-sub">Invite multi-supplier bids to compare unit prices, lead times, and warranties</p>
    </div>
    <a href="<?= url('/procurement/rfqs') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to RFQs
    </a>
</div>

<div class="card p-4">
    <form action="<?= url('/procurement/rfqs/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Link to Approved Purchase Request (PR) *</label>
                <select name="request_id" id="rfqPrSelect" class="form-select fw-semibold" required>
                    <option value="">-- Select Approved Purchase Requisition --</option>
                    <?php foreach ($requests as $pr): ?>
                        <?php 
                            $deptClean = html_entity_decode(e($pr['department']));
                            $totalEstCost = (float)($pr['total_est_cost'] ?? 0);
                        ?>
                        <option value="<?= $pr['id'] ?>" 
                                data-no="<?= e($pr['request_no']) ?>"
                                data-items-count="<?= (int)$pr['item_count'] ?>"
                                data-qty="<?= (int)($pr['total_qty'] ?? 0) ?>"
                                data-rate="<?= number_format($totalEstCost, 2, '.', '') ?>"
                                data-dept="<?= e($deptClean) ?>"
                                data-priority="<?= e($pr['priority']) ?>">
                            <?= e($pr['request_no']) ?> — <?= e($deptClean) ?> (<?= (int)$pr['item_count'] ?> items • Est Total: <?= format_currency($totalEstCost) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">RFQ Title / Tender Scope *</label>
                <input type="text" name="title" id="rfqTitleInput" class="form-control fw-bold" placeholder="Select a PR above to auto-generate tender title..." required>
            </div>
        </div>

        <!-- Selected PR Details Info Card -->
        <div id="prInfoCard" class="card p-3.5 mb-4 rounded-3 d-none" style="background:var(--bg-page);border:1px solid var(--border);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase" style="color:var(--text-muted);letter-spacing:0.06em;">
                    <i class="bi bi-info-circle-fill text-primary me-1.5"></i> Linked Requisition Details
                </span>
                <span class="badge bg-success" id="prPriorityBadge">APPROVED</span>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Department</small>
                    <span class="fw-bold" style="color:var(--text-primary);" id="prDeptLabel">-</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Requisition Quantity</small>
                    <span class="fw-bold text-primary" id="prQtyLabel">-</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Est. Base Rate from Product Master</small>
                    <span class="fw-bold text-success" id="prRateLabel">-</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label">Estimated Base Unit Price (₹) *</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-currency-rupee"></i></span>
                    <input type="number" step="0.01" name="estimated_price" id="rfqPriceInput" class="form-control fw-bold" placeholder="Auto-populated from Product Master" required>
                </div>
                <small class="text-muted d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-shield-check text-success me-1"></i>Auto-populated from Product Master benchmark rate.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Bidding Deadline Date *</label>
                <input type="date" name="deadline_date" class="form-control fw-bold" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Default deadline set to 7 days from today.</small>
            </div>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/procurement/rfqs') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-gradient-primary">
                <i class="bi bi-send-fill me-1.5"></i> Issue RFQ & Generate Supplier Quotation Bids
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prSelect = document.getElementById('rfqPrSelect');
    const titleInput = document.getElementById('rfqTitleInput');
    const priceInput = document.getElementById('rfqPriceInput');
    const infoCard = document.getElementById('prInfoCard');
    const deptLabel = document.getElementById('prDeptLabel');
    const qtyLabel = document.getElementById('prQtyLabel');
    const rateLabel = document.getElementById('prRateLabel');
    const priorityBadge = document.getElementById('prPriorityBadge');

    if (!prSelect) return;

    prSelect.addEventListener('change', function() {
        const selectedOpt = prSelect.options[prSelect.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            infoCard.classList.add('d-none');
            return;
        }

        const no = selectedOpt.dataset.no || '';
        const itemsCount = selectedOpt.dataset.itemsCount || '1';
        const qty = selectedOpt.dataset.qty || '1';
        const rate = parseFloat(selectedOpt.dataset.rate) || 0;
        const dept = selectedOpt.dataset.dept || 'General';
        const priority = selectedOpt.dataset.priority || 'medium';

        // Auto-generate Title
        titleInput.value = `Request for Quotation - Requisition ${no} (${itemsCount} items)`;

        // Auto-fill Base Rate
        priceInput.value = rate.toFixed(2);

        // Fill Info Card
        deptLabel.textContent = dept;
        qtyLabel.textContent = `${qty} Units (${itemsCount} Items)`;
        rateLabel.textContent = `₹ ${rate.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        priorityBadge.textContent = `${priority.toUpperCase()} PRIORITY`;
        priorityBadge.className = `badge bg-${['urgent','high'].includes(priority) ? 'danger' : 'info'}`;

        infoCard.classList.remove('d-none');
    });

    // Run on initial load if preselected
    if (prSelect.selectedIndex > 0) {
        prSelect.dispatchEvent(new Event('change'));
    }
});
</script>
