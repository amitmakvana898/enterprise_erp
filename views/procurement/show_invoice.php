<!-- Page Header Actions -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-receipt-cutoff text-warning me-2"></i>Vendor Tax Invoice Details</h3>
        <p class="text-muted small mb-0">Invoice Reference: <strong><?= e($inv['invoice_no']) ?></strong> | Status: <strong class="text-uppercase text-<?= $inv['status'] === 'paid' ? 'success' : 'warning' ?>"><?= e($inv['status']) ?></strong></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button onclick="window.print();" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
        <?php if ($inv['status'] !== 'paid'): ?>
            <a href="<?= url('/procurement/payments/create?invoice_id=' . $inv['id'] . '&supplier_id=' . $inv['supplier_id']) ?>" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-credit-card me-1"></i> Pay Partial / Milestone
            </a>
        <?php endif; ?>
        <a href="<?= url('/procurement/invoices') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Invoices
        </a>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .sidebar-wrapper, .erp-topbar, .d-print-none { display: none !important; }
    .main-content-area { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .card { border: 1px solid #000 !important; background: #fff !important; color: #000 !important; box-shadow: none !important; }
    .card * { color: #000 !important; }
    .badge { border: 1px solid #000 !important; color: #000 !important; }
    .table { color: #000 !important; border-color: #000 !important; }
}
</style>

<?php if ($inv['status'] !== 'paid'): ?>
<!-- Outstanding Payment Action Banner -->
<div class="card p-3 mb-4 border-warning bg-warning bg-opacity-10 d-print-none">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-circle bg-warning text-dark">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-0" style="color:var(--text-primary);">Payment Pending for <?= e($inv['invoice_no']) ?> <span class="badge bg-info text-dark font-monospace ms-2" style="font-size:0.75rem;"><?= str_replace('_', ' ', strtoupper($inv['status'])) ?></span></h5>
                <small class="text-warning">Paid So Far: <strong><?= format_currency($inv['paid_amount']) ?></strong> | Outstanding Balance: <strong class="fs-6"><?= format_currency($inv['total_amount'] - $inv['paid_amount']) ?></strong> • Due Date: <?= date('d M Y', strtotime($inv['due_date'])) ?></small>
            </div>
        </div>
        <div>
            <a href="<?= url('/procurement/payments/create?invoice_id=' . $inv['id'] . '&supplier_id=' . $inv['supplier_id']) ?>" class="btn btn-warning btn-md fw-bold text-dark px-4 shadow">
                <i class="bi bi-credit-card-fill me-1.5"></i> Pay Partial / Milestone Installment
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tax Invoice Card Document -->
<div class="card shadow-sm border p-4 mb-4">
    <!-- Header: Supplier & Company GSTIN Details -->
    <div class="row pb-4 mb-4 border-bottom g-4">
        <div class="col-md-6">
            <span class="badge bg-warning text-dark font-monospace mb-2">ORIGINAL TAX INVOICE</span>
            <h4 class="fw-bold mb-1" style="color:var(--text-primary);"><?= e($inv['supplier_name']) ?></h4>
            <div class="text-muted small">
                <div><strong>Vendor Code:</strong> <?= e($inv['supplier_code']) ?> | <strong>GSTIN:</strong> <?= e($inv['supplier_gstin'] ?: '27AAAAA0000A1Z5') ?></div>
                <div><strong>Address:</strong> <?= e($inv['supplier_address'] ?: 'Commercial Tax Division, Industrial Estate') ?></div>
                <div><strong>Contact:</strong> <?= e($inv['supplier_phone'] ?: 'N/A') ?> | <?= e($inv['supplier_email'] ?: 'billing@vendor.com') ?></div>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <h3 class="fw-extrabold text-warning mb-1 font-monospace"><?= e($inv['invoice_no']) ?></h3>
            <div class="text-muted small mb-2">
                <div><strong>Invoice Date:</strong> <?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                <div><strong>Payment Due Date:</strong> <span class="text-warning"><?= date('d M Y', strtotime($inv['due_date'])) ?></span></div>
                <div><strong>Payment Terms:</strong> <?= e($inv['payment_terms'] ?: 'Net 30') ?></div>
            </div>
            <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 fw-bold">
                <i class="bi bi-shield-check me-1"></i>3-Way Match Verified (PO + GRN + Invoice)
            </span>
        </div>
    </div>

    <!-- P2P Linked References Bar -->
    <div class="row g-3 mb-4 p-3.5 rounded-3 border" style="background:var(--bg-page);">
        <div class="col-md-3 border-end">
            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing:0.05em; font-size:0.75rem;">Linked Purchase Order (PO)</small>
            <strong class="text-info font-monospace fs-6"><?= e($inv['po_no'] ?: 'Direct Invoice') ?></strong>
            <?php if (!empty($inv['po_date'])): ?>
                <span class="text-muted small d-block mt-0.5">(Issued: <?= date('d M Y', strtotime($inv['po_date'])) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="col-md-3 border-end">
            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing:0.05em; font-size:0.75rem;">Linked Goods Receipt Note (GRN)</small>
            <strong class="text-primary font-monospace fs-6"><?= e($inv['grn_no'] ?: 'GRN-VERIFIED') ?></strong>
            <?php if (!empty($inv['challan_no'])): ?>
                <span class="text-warning small d-block mt-0.5">(Challan: <?= e($inv['challan_no']) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="col-md-3 border-end">
            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing:0.05em; font-size:0.75rem;">Quantity Fulfillment Progress</small>
            <div class="small fw-bold mb-1">
                <span class="text-success"><?= number_format($totalBilledQty ?? 1) ?> Billed Units</span> / <span style="color:var(--text-primary);"><?= number_format($inv['total_ordered_qty'] ?? $totalBilledQty ?? 1) ?> PO Contract</span>
            </div>
            <?php 
            $totOrd = (int)($inv['total_ordered_qty'] ?? 0);
            $totRec = (int)($inv['total_received_qty'] ?? 0);
            $pendingBal = max(0, $totOrd - $totRec);
            ?>
            <?php if ($pendingBal > 0): ?>
                <span class="badge font-monospace px-2 py-1" style="background:rgba(245, 158, 11, 0.15); border:1px solid rgba(245, 158, 11, 0.4); color:var(--warning) !important; font-size:0.72rem;">
                    ⏳ <?= number_format($pendingBal) ?> Units Pending Shipment
                </span>
            <?php else: ?>
                <span class="badge font-monospace px-2 py-1" style="background:rgba(16, 185, 129, 0.15); border:1px solid rgba(16, 185, 129, 0.4); color:var(--success) !important; font-size:0.72rem;">
                    ✅ 100% Fully Received
                </span>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block text-uppercase fw-bold mb-1" style="letter-spacing:0.05em; font-size:0.75rem;">Destination Warehouse / Billed To</small>
            <strong class="d-block" style="color:var(--text-primary);"><?= e($inv['warehouse_name'] ?: 'Central Operations Warehouse') ?></strong>
            <span class="text-muted small"><?= e($inv['company_name'] ?: 'Enterprise ERP Suite') ?></span>
        </div>
    </div>

    <!-- Billed Item Line Items Table -->
    <div class="table-responsive mb-4 rounded-3 border">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-muted small" style="background:var(--bg-page);">
                    <th style="width:40px; padding:12px 16px;">#</th>
                    <th style="padding:12px 16px;">Product / Item Description</th>
                    <th style="padding:12px 16px;">SKU / Code</th>
                    <th style="padding:12px 16px;">HSN / SAC</th>
                    <th class="text-end" style="padding:12px 16px;">Billed Qty</th>
                    <th class="text-end" style="padding:12px 16px;">Unit Price (₹)</th>
                    <th class="text-end" style="padding:12px 16px;">GST Tax</th>
                    <th class="text-end" style="padding:12px 16px;">Total Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php 
                    $sumTax = 0;
                    $sumTotal = 0;
                    $idx = 1; 
                    foreach ($items as $item): 
                        $sumTax += (float)($item['tax_amount'] ?? 0);
                        $sumTotal += (float)($item['total_price'] ?? 0);
                    ?>
                        <tr>
                            <td style="padding:14px 16px;" class="text-muted"><?= $idx++ ?></td>
                            <td style="padding:14px 16px;">
                                <strong style="color:var(--text-primary); font-size:0.95rem;"><?= e($item['product_name']) ?></strong>
                            </td>
                            <td class="font-monospace small text-info" style="padding:14px 16px;"><?= e($item['product_sku']) ?></td>
                            <td class="font-monospace small text-muted" style="padding:14px 16px;"><?= e($item['hsn_code'] ?: '84713010') ?></td>
                            <td class="text-end fw-bold font-monospace fs-6 text-success" style="padding:14px 16px;"><?= (int)$item['qty'] ?> <?= e($item['unit_name'] ?: 'Units') ?></td>
                            <td class="text-end font-monospace" style="padding:14px 16px;"><?= number_format($item['unit_price'], 2) ?></td>
                            <td class="text-end font-monospace text-warning" style="padding:14px 16px;"><?= number_format($item['gst_rate'] ?? 18.00, 2) ?>% (₹<?= number_format($item['tax_amount'] ?? 0, 2) ?>)</td>
                            <td class="text-end fw-bold font-monospace" style="padding:14px 16px; color:var(--text-primary); font-size:1rem;">₹<?= number_format($item['total_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">Line items referenced directly from Purchase Order <?= e($inv['po_no']) ?>. Total Taxable Invoice Value: ₹<?= number_format($inv['total_amount'], 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:var(--bg-page); border-top:2px solid var(--border);">
                    <td colspan="4" class="text-end text-uppercase fw-bold small py-3 text-warning" style="letter-spacing:0.06em;">
                        Total Billed Quantity & Invoice Summary:
                    </td>
                    <td class="text-end fw-bold font-monospace fs-6 py-3 text-success">
                        <?= number_format($totalBilledQty ?? 1) ?> Units
                    </td>
                    <td></td>
                    <td class="text-end fw-bold font-monospace py-3 text-warning">
                        ₹<?= number_format($sumTax ?? 0, 2) ?>
                    </td>
                    <td class="text-end fw-bold font-monospace fs-6 py-3" style="color:var(--text-primary);">
                        ₹<?= number_format($inv['total_amount'], 2) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Valuation & Tax Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-3 rounded-3 border h-100" style="background:var(--bg-page);">
                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-bank me-1.5"></i>Vendor Remittance Bank Details</h6>
                <div class="small text-muted">
                    <div><strong>Bank Name:</strong> <?= e($inv['bank_name'] ?: 'HDFC Bank Corporate Branch') ?></div>
                    <div><strong>Account No:</strong> <span class="font-monospace" style="color:var(--text-primary);"><?= e($inv['bank_account'] ?: '99881122334455') ?></span></div>
                    <div><strong>IFSC Code:</strong> <span class="font-monospace text-info"><?= e($inv['bank_ifsc'] ?: 'HDFC0001234') ?></span></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3 border" style="background:var(--bg-page);">
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Taxable Subtotal (Base Amount):</span>
                    <span class="font-monospace" style="color:var(--text-primary);">₹<?= number_format($inv['total_amount'] / 1.18, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>CGST (9%):</span>
                    <span class="text-warning font-monospace">₹<?= number_format(($inv['total_amount'] - ($inv['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>SGST (9%):</span>
                    <span class="text-warning font-monospace">₹<?= number_format(($inv['total_amount'] - ($inv['total_amount'] / 1.18)) / 2, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top fs-5 fw-extrabold">
                    <span style="color:var(--text-primary);">Grand Total Invoice Amount:</span>
                    <span class="text-success font-monospace">₹<?= number_format($inv['total_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-2 pt-2 border-top small">
                    <span class="text-muted">Amount Settled / Paid:</span>
                    <span class="fw-bold text-success">₹<?= number_format($inv['paid_amount'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1 small">
                    <span class="text-muted">Outstanding Due Balance:</span>
                    <span class="fw-bold text-danger">₹<?= number_format($inv['total_amount'] - $inv['paid_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Payment Audit History -->
    <?php if (!empty($payments)): ?>
        <?php 
        $invoiceGrandTotal = (float)$inv['total_amount'];
        $settledTotalSoFar = (float)$inv['paid_amount'];
        $settledPercent = $invoiceGrandTotal > 0 ? min(100.0, round(($settledTotalSoFar / $invoiceGrandTotal) * 100, 1)) : 0;
        $totalPaymentsCount = count($payments);
        ?>
        <div class="border-top pt-4 mt-2">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold text-success mb-1">
                        <i class="bi bi-clock-history me-1.5"></i>Payment Settlement Ledger & Audit History
                    </h6>
                    <small class="text-muted">Official Banking Remittance Audit Trail (<?= $totalPaymentsCount ?> Milestone Installment Transactions)</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-<?= $settledPercent >= 100 ? 'success' : 'warning' ?> text-capitalize font-monospace px-3 py-1.5 fs-6">
                        <?= $settledPercent >= 100 ? '✅ 100% Fully Settled' : '⏳ ' . $settledPercent . '% Paid (Partially Settled)' ?>
                    </span>
                </div>
            </div>

            <!-- Settlement Progress Bar -->
            <div class="p-3 rounded-3 mb-3 border" style="background:var(--bg-page);">
                <div class="d-flex justify-content-between text-muted small fw-bold mb-1.5">
                    <span>Cumulative Remittance Settlement Progress</span>
                    <span class="font-monospace" style="color:var(--text-primary);">₹<?= number_format($settledTotalSoFar, 2) ?> / ₹<?= number_format($invoiceGrandTotal, 2) ?> (<?= $settledPercent ?>%)</span>
                </div>
                <div class="progress bg-secondary bg-opacity-20" style="height: 10px; border-radius: 6px; overflow: hidden;">
                    <div class="progress-bar bg-gradient-<?= $settledPercent >= 100 ? 'success' : 'warning' ?>" role="progressbar" style="width: <?= $settledPercent ?>%; transition: width 0.6s ease;" aria-valuenow="<?= $settledPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                        <tr class="text-muted" style="background:var(--bg-page);">
                            <th style="padding: 10px 14px;">Milestone #</th>
                            <th style="padding: 10px 14px;">Voucher No & Status</th>
                            <th style="padding: 10px 14px;">Transaction Date & Time</th>
                            <th style="padding: 10px 14px;">Payment Channel</th>
                            <th style="padding: 10px 14px;">Bank Reference / UTR #</th>
                            <th style="padding: 10px 14px;">Dispatched By</th>
                            <th class="text-end" style="padding: 10px 14px;">Amount Paid (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $calcSum = 0;
                        $seq = 1;
                        foreach ($payments as $p): 
                            $amt = (float)$p['amount'];
                            $calcSum += $amt;
                            $pShare = $invoiceGrandTotal > 0 ? round(($amt / $invoiceGrandTotal) * 100, 1) : 0;
                            
                            // Format timestamp nicely
                            $tsRaw = !empty($p['exact_timestamp']) ? $p['exact_timestamp'] : $p['payment_date'];
                            $dtFormatted = date('d M Y, h:i:s A', strtotime($tsRaw));
                            // If timestamp has midnight 12:00:00 AM fallback, show clean date
                            if (date('H:i:s', strtotime($tsRaw)) === '00:00:00') {
                                $dtFormatted = date('d M Y', strtotime($tsRaw)) . ' <span class="text-muted small">(Processed)</span>';
                            }
                        ?>
                            <tr>
                                <td style="padding: 12px 14px;">
                                    <span class="badge bg-secondary font-monospace" style="font-size: 0.72rem;">Installment #<?= $seq++ ?></span>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;"><?= $pShare ?>% Contract</small>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <div class="fw-bold font-monospace text-primary"><?= e($p['payment_no']) ?></div>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success px-2 py-0.5 mt-0.5" style="font-size: 0.68rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Bank Cleared
                                    </span>
                                </td>
                                <td style="padding: 12px 14px;" class="fw-medium">
                                    <i class="bi bi-clock text-warning me-1.5"></i><?= $dtFormatted ?>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <span class="badge bg-info bg-opacity-20 text-info border border-info text-capitalize">
                                        <?= str_replace('_', ' ', e($p['payment_mode'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 14px;" class="font-monospace fw-bold text-warning">
                                    <?= e($p['reference_no'] ?: 'UTR-REF-N/A') ?>
                                </td>
                                <td style="padding: 12px 14px;" class="text-muted">
                                    <strong class="d-block" style="color:var(--text-primary);"><?= e($p['payer_name']) ?></strong>
                                    <small class="text-muted" style="font-size: 0.7rem;">Verified Officer</small>
                                </td>
                                <td style="padding: 12px 14px;" class="text-end fw-bold font-monospace text-success fs-6">
                                    ₹<?= number_format($amt, 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--bg-page); border-top: 2px solid var(--border);">
                            <td colspan="6" class="text-end text-uppercase fw-bold small py-3 text-warning" style="letter-spacing: 0.05em;">
                                Total Cumulative Payments Dispatched:
                            </td>
                            <td class="text-end fw-bold font-monospace fs-6 py-3 text-success">
                                ₹<?= number_format($calcSum, 2) ?>
                            </td>
                        </tr>
                        <?php $dueBal = max(0, $invoiceGrandTotal - $calcSum); ?>
                        <tr style="background:var(--bg-page);">
                            <td colspan="6" class="text-end text-uppercase fw-bold small py-2.5 text-muted">
                                Remaining Outstanding Due Balance:
                            </td>
                            <td class="text-end fw-bold font-monospace py-2.5 <?= $dueBal <= 0.01 ? 'text-success' : 'text-danger' ?>">
                                <?= $dueBal <= 0.01 ? '✅ ₹0.00 (Fully Settled)' : '₹' . number_format($dueBal, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
