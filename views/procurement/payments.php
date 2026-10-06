<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-bank2 me-2.5" style="color:var(--primary)"></i>Vendor Invoices & Payments</h3>
        <p class="page-header-sub">3-Way Match payment processing and vendor financial settlement records (Initiated from Goods Receipt GRN)</p>
    </div>
    <?php if (has_permission('finance.payments')): ?>
    <a href="<?= url('/procurement/payments/create') ?>" class="btn btn-gradient-primary btn-sm">
        <i class="bi bi-credit-card-fill me-1"></i> Process Vendor Payment
    </a>
    <?php endif; ?>
</div>

<div class="card p-0 overflow-hidden shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small payments-table">
            <thead>
                <tr class="payments-header-row">
                    <th>1st Payment No & Date</th>
                    <th>Supplier / Vendor</th>
                    <th>Invoice Reference & Contract</th>
                    <th>1st Payment Mode & UTR #</th>
                    <th class="text-end">1st Installment Paid</th>
                    <th>Settlement Status</th>
                    <th class="text-end" style="padding-right: 20px;">Other Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($groupedPayments)): ?>
                    <?php foreach ($groupedPayments as $invGroup): ?>
                        <?php 
                        $invTotal = $invGroup['invoice_total'];
                        $invPaid = $invGroup['invoice_paid'];
                        $pendingBal = max(0, $invTotal - $invPaid);
                        $isFullyPaid = ($invGroup['invoice_status'] === 'paid' || $pendingBal <= 0.01);
                        
                        $allInstallments = $invGroup['installments'];
                        $firstPay = $allInstallments[0];
                        $subsequentPayments = array_slice($allInstallments, 1);
                        $subsequentCount = count($subsequentPayments);
                        
                        $groupId = 'subsequent-pay-group-' . $invGroup['invoice_id'];
                        
                        $firstAmt = (float)$firstPay['amount'];
                        $firstShare = $invTotal > 0 ? round(($firstAmt / $invTotal) * 100, 1) : 0;
                        $firstTs = !empty($firstPay['exact_timestamp']) ? $firstPay['exact_timestamp'] : $firstPay['payment_date'];
                        $firstDtFormatted = date('d M Y, h:i:s A', strtotime($firstTs));
                        if (date('H:i:s', strtotime($firstTs)) === '00:00:00') {
                            $firstDtFormatted = date('d M Y', strtotime($firstTs));
                        }
                        ?>
                        <!-- Master Invoice Row displaying 1st Payment Details -->
                        <tr class="payments-master-row border-bottom">
                            <td>
                                <div class="fw-bold text-primary font-monospace fs-6"><?= e($firstPay['payment_no']) ?></div>
                                <small class="text-warning"><i class="bi bi-clock me-1"></i><?= $firstDtFormatted ?></small>
                                <div><span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary font-monospace" style="font-size:0.68rem;">1st Payment (<?= $firstShare ?>%)</span></div>
                            </td>
                            <td>
                                <strong class="text-theme-primary d-block" style="font-size: 0.95rem;"><?= e($invGroup['supplier_name']) ?></strong>
                                <small class="text-theme-muted" style="font-size:0.75rem;">Payer: <?= e($firstPay['payer_name']) ?></small>
                            </td>
                            <td>
                                <a href="<?= url('/procurement/invoices/show/' . $invGroup['invoice_id']) ?>" class="badge bg-info bg-opacity-10 text-info border border-info font-monospace text-decoration-none fs-6 px-2.5 py-1" title="View Original Tax Invoice">
                                    <i class="bi bi-receipt me-1"></i><?= e($invGroup['invoice_no']) ?>
                                </a>
                                <div class="small text-theme-muted mt-0.5" style="font-size:0.75rem;">
                                    Contract: <strong class="text-theme-primary"><?= format_currency($invTotal) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary text-capitalize me-1">
                                    <?= str_replace('_', ' ', e($firstPay['payment_mode'])) ?>
                                </span>
                                <div class="fw-bold font-monospace text-theme-secondary small mt-0.5"><?= e($firstPay['reference_no'] ?: 'UTR-N/A') ?></div>
                            </td>
                            <td class="text-end fw-bold text-success fs-6 font-monospace">
                                <?= format_currency($firstAmt) ?>
                            </td>
                            <td>
                                <?php if ($isFullyPaid): ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success px-2.5 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> 100% Fully Settled
                                    </span>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge bg-warning bg-opacity-15 text-warning border border-warning px-2 py-1" style="width: fit-content;">
                                            ⏳ Partially Paid
                                        </span>
                                        <small class="text-warning font-monospace fw-bold" style="font-size:0.75rem;">
                                            Due Bal: <?= format_currency($pendingBal) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" style="padding-right: 20px;">
                                <div class="d-flex gap-2 justify-content-end align-items-center">
                                    <?php if (!$isFullyPaid): ?>
                                        <a href="<?= url('/procurement/payments/create?invoice_id=' . $invGroup['invoice_id'] . '&supplier_id=' . $invGroup['supplier_id']) ?>" class="btn btn-warning btn-sm text-dark fw-bold px-2.5" style="font-size: 0.78rem;">
                                            <i class="bi bi-credit-card-fill me-1"></i> + Pay Next
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($subsequentCount > 0): ?>
                                        <button class="btn btn-outline-primary btn-sm fw-bold px-2.5" style="font-size: 0.78rem;" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $groupId ?>" aria-expanded="false" aria-controls="<?= $groupId ?>">
                                            <i class="bi bi-layers-fill me-1"></i> View Other Payments (<?= $subsequentCount ?>)
                                        </button>
                                    <?php else: ?>
                                        <span class="text-theme-muted small fst-italic">Single Payment Only</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- Collapsible Child Accordion Row containing 2nd and Subsequent Payments -->
                        <?php if ($subsequentCount > 0): ?>
                        <tr>
                            <td colspan="7" class="p-0 border-0">
                                <div class="collapse" id="<?= $groupId ?>">
                                    <div class="p-3 my-2 rounded-3 border payments-nested-box">
                                        <div class="small fw-bold text-primary mb-2">
                                            <i class="bi bi-layers-half me-1.5"></i>Subsequent Payment Installments (2nd Payment Onwards) for <?= e($invGroup['invoice_no']) ?>:
                                        </div>
                                        <div class="table-responsive rounded-3 border">
                                            <table class="table table-sm table-hover align-middle mb-0 small payments-table">
                                                <thead>
                                                    <tr class="payments-header-row">
                                                        <th style="padding: 8px 12px;">Installment #</th>
                                                        <th style="padding: 8px 12px;">Payment No</th>
                                                        <th style="padding: 8px 12px;">Dispatched Date & Time</th>
                                                        <th style="padding: 8px 12px;">Payment Channel</th>
                                                        <th style="padding: 8px 12px;">Reference UTR #</th>
                                                        <th style="padding: 8px 12px;">Verified Payer</th>
                                                        <th class="text-end" style="padding: 8px 12px;">Amount Paid (₹)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $seq = 2; // Starts from 2nd Payment
                                                    foreach ($subsequentPayments as $p): 
                                                        $amt = (float)$p['amount'];
                                                        $pShare = $invTotal > 0 ? round(($amt / $invTotal) * 100, 1) : 0;
                                                        $tsRaw = !empty($p['exact_timestamp']) ? $p['exact_timestamp'] : $p['payment_date'];
                                                        $dtFormatted = date('d M Y, h:i:s A', strtotime($tsRaw));
                                                        if (date('H:i:s', strtotime($tsRaw)) === '00:00:00') {
                                                            $dtFormatted = date('d M Y', strtotime($tsRaw));
                                                        }
                                                    ?>
                                                        <tr>
                                                            <td style="padding: 10px 12px;">
                                                                <span class="badge bg-primary bg-opacity-20 text-primary font-monospace fw-bold">Installment #<?= $seq++ ?></span>
                                                                <small class="text-theme-muted d-block mt-0.5" style="font-size: 0.7rem;"><?= $pShare ?>% Contract</small>
                                                            </td>
                                                            <td style="padding: 10px 12px;" class="fw-bold font-monospace text-primary">
                                                                <?= e($p['payment_no']) ?>
                                                            </td>
                                                            <td style="padding: 10px 12px;" class="text-theme-primary">
                                                                <i class="bi bi-clock text-warning me-1"></i><?= $dtFormatted ?>
                                                            </td>
                                                            <td style="padding: 10px 12px;">
                                                                <span class="badge bg-info bg-opacity-15 text-info border border-info text-capitalize">
                                                                    <?= str_replace('_', ' ', e($p['payment_mode'])) ?>
                                                                </span>
                                                            </td>
                                                            <td style="padding: 10px 12px;" class="font-monospace fw-bold text-warning">
                                                                <?= e($p['reference_no'] ?: 'UTR-N/A') ?>
                                                            </td>
                                                            <td style="padding: 10px 12px;" class="text-theme-muted">
                                                                <strong class="text-theme-primary"><?= e($p['payer_name']) ?></strong>
                                                            </td>
                                                            <td style="padding: 10px 12px;" class="text-end fw-bold font-monospace text-success fs-6">
                                                                <?= format_currency($amt) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-theme-muted">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2 d-block"></i>
                            No vendor payment records found. Click <strong>Process Vendor Payment</strong> to record a payment.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
/* ─── DUAL THEME ADAPTIVE PAYMENTS TABLE STYLING ─── */
.payments-table {
    color: var(--text-primary, #0F172A) !important;
}
.payments-header-row {
    background-color: rgba(148, 163, 184, 0.12) !important;
    color: var(--text-secondary, #475569) !important;
}
.payments-header-row th {
    font-weight: 700 !important;
    letter-spacing: 0.03em;
    border-bottom: 1px solid var(--border-color, #E2E8F0) !important;
}

.payments-master-row {
    background-color: var(--card-bg, #FFFFFF) !important;
    transition: background-color 0.15s ease;
}
.payments-master-row:hover {
    background-color: rgba(37, 99, 235, 0.04) !important;
}

.payments-nested-box {
    background-color: rgba(148, 163, 184, 0.08) !important;
    border-color: var(--border-color, #E2E8F0) !important;
}

.text-theme-primary { color: var(--text-primary, #0F172A) !important; }
.text-theme-secondary { color: var(--text-secondary, #475569) !important; }
.text-theme-muted { color: var(--text-muted, #64748B) !important; }
.border-theme { border-color: var(--border-color, #E2E8F0) !important; }

/* DARK MODE ADAPTATION ([data-theme="dark"]) */
[data-theme="dark"] .payments-header-row {
    background-color: rgba(15, 23, 42, 0.85) !important;
    color: #94A3B8 !important;
}
[data-theme="dark"] .payments-master-row {
    background-color: #1E293B !important;
}
[data-theme="dark"] .payments-master-row:hover {
    background-color: rgba(30, 41, 59, 0.9) !important;
}
[data-theme="dark"] .payments-nested-box {
    background-color: rgba(15, 23, 42, 0.7) !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
}
[data-theme="dark"] .text-theme-primary { color: #F8FAFC !important; }
[data-theme="dark"] .text-theme-secondary { color: #CBD5E1 !important; }
[data-theme="dark"] .text-theme-muted { color: #94A3B8 !important; }
[data-theme="dark"] .border-theme { border-color: rgba(255, 255, 255, 0.12) !important; }
</style>
