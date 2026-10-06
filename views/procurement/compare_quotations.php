<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-extrabold text-white mb-1"><i class="bi bi-sliders text-warning me-2.5"></i>Quotation Comparison Matrix</h2>
        <p class="text-secondary small mb-0">Multi-supplier side-by-side evaluation of rates, lead times, warranties, and payment terms</p>
    </div>
    <a href="<?= url('/procurement/rfqs') ?>" class="btn btn-outline-light btn-sm rounded-3 px-3 py-2 fw-semibold">
        <i class="bi bi-arrow-left me-1.5"></i> Back to RFQs
    </a>
</div>

<!-- RFQ Details Banner -->
<div class="card p-4 shadow-sm mb-4 border-start border-warning border-4 bg-dark">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold font-monospace"><?= e($rfq['rfq_no']) ?></span>
            <h4 class="fw-bold text-white mb-1"><?= e($rfq['title']) ?></h4>
            <p class="text-secondary small mb-0">Issued Date: <?= e($rfq['created_at']) ?> • Status: <span class="text-success fw-bold">Active Bids Compared</span></p>
        </div>
        <?php 
            $isRfqCompleted = in_array(strtolower($rfq['status']), ['completed', 'closed', 'po_issued']);
            $winningQuotation = null;
            $minPrice = null;
            $minLead = null;

            foreach ($quotations as $tmpQ) {
                $p = (float)$tmpQ['unit_price'];
                $l = (int)$tmpQ['lead_time_days'];
                if ($minPrice === null || $p < $minPrice) $minPrice = $p;
                if ($minLead === null || $l < $minLead) $minLead = $l;

                if ($tmpQ['status'] === 'selected') {
                    $winningQuotation = $tmpQ;
                }
            }
            if (!$winningQuotation && !empty($quotations)) {
                $winningQuotation = $quotations[0];
            }
        ?>
        <?php if ($isRfqCompleted): ?>
            <span class="badge bg-success px-4 py-2.5 rounded-3 fs-6 fw-bold">
                <i class="bi bi-check-circle-fill me-2"></i> Purchase Order Issued (Process Completed)
            </span>
        <?php elseif ($winningQuotation): ?>
            <a href="<?= url('/procurement/orders/create?quotation_id=' . $winningQuotation['id'] . '&rfq_id=' . $rfq['id'] . '&supplier_id=' . $winningQuotation['supplier_id']) ?>" class="btn btn-primary fw-bold shadow-lg px-4 py-2.5 rounded-3">
                <i class="bi bi-bag-plus-fill me-2"></i> Convert Winning Bid to Purchase Order (PO)
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Side-by-Side Comparison Matrix Cards -->
<div class="row g-4 mb-4">
    <?php foreach ($quotations as $q): ?>
        <?php 
            $isSelected = ($q['status'] === 'selected');
            $isLowestPrice = (abs((float)$q['unit_price'] - $minPrice) < 0.01);
            $isFastestLead = ((int)$q['lead_time_days'] === $minLead);
        ?>
        <div class="col-md-4">
            <div class="card p-4 shadow-sm h-100 bg-dark border-secondary <?= $isSelected ? 'border-success border-2 bg-success bg-opacity-10' : '' ?>">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-1">
                    <?php if ($isSelected): ?>
                        <span class="badge bg-success px-3 py-1 fw-bold"><i class="bi bi-trophy-fill me-1"></i> ★ WINNING BEST BID</span>
                    <?php elseif ($isLowestPrice): ?>
                        <span class="badge bg-info text-dark px-3 py-1 fw-bold"><i class="bi bi-tag-fill me-1"></i> L1 LOWEST PRICE</span>
                    <?php else: ?>
                        <span class="badge bg-secondary px-3 py-1 fw-bold">SUBMITTED BID</span>
                    <?php endif; ?>

                    <?php if ($isFastestLead): ?>
                        <span class="badge bg-primary px-2.5 py-1 text-white fw-bold"><i class="bi bi-lightning-fill me-1"></i> FASTEST</span>
                    <?php endif; ?>

                    <span class="text-warning fw-bold small ms-auto"><i class="bi bi-star-fill me-1"></i> <?= e($q['rating']) ?> / 5</span>
                </div>

                <h5 class="fw-bold text-white mb-1"><?= e($q['supplier_name']) ?></h5>
                <p class="text-secondary small mb-3">Code: <span class="font-monospace text-light"><?= e($q['supplier_code']) ?></span> • Ref: <?= e($q['quotation_no']) ?></p>

                <div class="p-3 bg-dark bg-opacity-60 rounded-3 mb-3 border border-secondary border-opacity-25">
                    <div class="text-secondary small">Quoted Unit Rate</div>
                    <div class="fs-4 fw-extrabold text-success mb-1"><?= format_currency($q['unit_price']) ?> <span class="fs-6 text-secondary fw-normal">/ unit</span></div>
                    <div class="text-secondary small">Total Contract Price (incl 18% GST): <strong class="text-light"><?= format_currency($q['total_amount']) ?></strong></div>
                </div>

                <ul class="list-unstyled small text-light mb-4">
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-truck me-2 text-info"></i>Delivery Lead Time:</span>
                        <strong class="text-white"><?= $q['lead_time_days'] ?> Days</strong>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-shield-check me-2 text-warning"></i>Warranty Coverage:</span>
                        <strong class="text-white"><?= $q['warranty_months'] ?> Months Warranty</strong>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-secondary"><i class="bi bi-credit-card me-2 text-primary"></i>Payment Terms:</span>
                        <strong class="text-white"><?= e($q['payment_terms']) ?></strong>
                    </li>
                </ul>

                <div class="mt-auto">
                    <?php if ($isRfqCompleted): ?>
                        <?php if ($isSelected): ?>
                            <button type="button" class="btn btn-success w-100 py-2.5 fw-bold shadow" disabled>
                                <i class="bi bi-check-circle-fill me-1.5"></i> Contract Awarded & PO Issued
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-secondary w-100 py-2.5 fw-semibold" disabled>
                                Quotation Archival
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($isSelected): ?>
                            <a href="<?= url('/procurement/orders/create?quotation_id=' . $q['id'] . '&rfq_id=' . $rfq['id'] . '&supplier_id=' . $q['supplier_id']) ?>" class="btn btn-success w-100 py-2.5 fw-bold shadow">
                                <i class="bi bi-check-circle-fill me-1.5"></i> Selected Vendor • Issue PO
                            </a>
                        <?php else: ?>
                            <a href="<?= url('/procurement/quotations/select/' . $q['id']) ?>" class="btn btn-outline-warning w-100 py-2.5 fw-bold" onclick="return confirm('Select <?= e($q['supplier_name']) ?> as the winning bidder for this RFQ?');">
                                <i class="bi bi-trophy-fill me-1.5"></i> Select as Winning Bid
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
