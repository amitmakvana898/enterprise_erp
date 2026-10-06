<?php
$outstandingBalance = 0;
$totalBilled = 0;
$paidBilled = 0;
if (!empty($invoices)) {
    foreach ($invoices as $inv) {
        $totalBilled += (float)$inv['total_amount'];
        $paidBilled += (float)$inv['paid_amount'];
        if ($inv['status'] !== 'paid') {
            $outstandingBalance += ((float)$inv['total_amount'] - (float)$inv['paid_amount']);
        }
    }
}
$creditLimit = (float)($customer['credit_limit'] ?? 5000000);
$availableCredit = max(0, $creditLimit - $outstandingBalance);
$creditUsedPercent = $creditLimit > 0 ? min(100, max(0, ($outstandingBalance / $creditLimit) * 100)) : 0;
?>
<!-- Customer Portal Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-person-badge text-primary me-2"></i>Welcome, <?= e($customer['name']) ?></h3>
        <p class="text-secondary small mb-0">Code: <span class="font-monospace text-warning"><?= e($customer['code']) ?></span> • Credit Limit: <strong class="text-success"><?= format_currency($customer['credit_limit']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/customer-portal/requests/create') ?>" class="btn btn-primary fw-bold btn-sm px-3 rounded-3 shadow-sm">
            <i class="bi bi-cart-plus-fill me-1.5"></i> Submit New Order Request
        </a>
        <a href="<?= url('/customer-portal/quotations') ?>" class="btn btn-warning text-dark fw-bold btn-sm px-3 rounded-3 shadow-sm">
            <i class="bi bi-file-earmark-check-fill me-1.5"></i> View Quotations & Accept Orders
        </a>
    </div>
</div>

<!-- Unified Customer Dashboard Overview Grid -->
<div class="row g-4 mb-4">
    <!-- LEFT: Key Performance Indicators (KPIs) -->
    <div class="col-lg-8">
        <div class="row g-3 h-100">
            <!-- Metric 1: Active Quotes -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Active Price Quotes</span>
                        <div class="p-2 rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-file-earmark-text-fill fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-white mb-1 font-monospace"><?= count($quotations) ?> Quotes</h3>
                        <small class="text-info"><i class="bi bi-lightning-fill me-1"></i>Ready for acceptance</small>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Authorised Orders -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Authorised Sales Orders</span>
                        <div class="p-2 rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-cart-check-fill fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-warning mb-1 font-monospace"><?= count($orders) ?> Orders</h3>
                        <small class="text-secondary"><i class="bi bi-truck me-1"></i>Fulfillment pipeline</small>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Total Tax Invoices -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Tax Invoices</span>
                        <div class="p-2 rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-receipt fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-success mb-1 font-monospace"><?= count($invoices) ?> Invoices</h3>
                        <small class="text-success"><i class="bi bi-shield-fill-check me-1"></i>O2C Verified</small>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Paid Settlements -->
            <div class="col-md-6">
                <div class="card bg-dark border-secondary p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary small fw-bold text-uppercase tracking-wider">Total Settled</span>
                        <div class="p-2 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; color:#A855F7 !important; background:rgba(168,85,247,0.1) !important;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-extrabold text-white mb-1 font-monospace"><?= format_currency($paidBilled) ?></h3>
                        <small class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Fully Reconciled</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: B2B Credit Profile & Support Manager -->
    <div class="col-lg-4">
        <div class="card bg-dark border-secondary p-4 shadow-sm rounded-3 h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="pb-3 border-bottom border-secondary mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold text-uppercase tracking-wider mb-2" style="font-size:0.7rem;">B2B Credit Profile</span>
                    <h5 class="fw-bold text-white mb-1"><?= e($customer['name']) ?></h5>
                    <div class="small text-secondary">Credit Limit: <span class="text-success fw-bold"><?= format_currency($customer['credit_limit']) ?></span></div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold">Credit Limit Utilisation</span>
                        <span class="badge bg-<?= $creditUsedPercent > 80 ? 'danger' : 'info bg-opacity-20 text-info' ?> fw-bold font-monospace"><?= number_format($creditUsedPercent, 1) ?>%</span>
                    </div>
                    <div class="progress mb-2 bg-secondary bg-opacity-20" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-<?= $creditUsedPercent > 80 ? 'danger' : 'info' ?>" role="progressbar" style="width: <?= $creditUsedPercent ?>%; border-radius: 5px;"></div>
                    </div>
                    <div class="d-flex justify-content-between pt-1">
                        <small class="text-secondary">Used: <strong class="text-white"><?= format_currency($outstandingBalance) ?></strong></small>
                        <small class="text-secondary">Available: <strong class="text-success"><?= format_currency($availableCredit) ?></strong></small>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded bg-dark border border-secondary mt-2">
                <span class="text-secondary small fw-bold d-block mb-2 text-uppercase tracking-wider" style="font-size:0.7rem;"><i class="bi bi-headset text-primary me-1"></i>Dedicated Sales Support</span>
                <div class="fw-bold text-light mb-0" style="font-size: 0.9rem;">Sales Support Desk</div>
                <div class="text-secondary small mt-1" style="font-size: 0.8rem;"><i class="bi bi-envelope me-1"></i>b2b.support@enterprise.com</div>
                <div class="text-secondary small" style="font-size: 0.8rem;"><i class="bi bi-telephone-fill me-1"></i>+1 (800) 555-0199</div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Quotations Section -->
<div class="card bg-dark border-secondary p-4 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-file-earmark-text text-info me-2"></i>Pending Price Quotations (Awaiting Acceptance)</h5>
        <a href="<?= url('/customer-portal/quotations') ?>" class="btn btn-outline-info btn-sm fw-bold">View All Quotes</a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Quotation #</th>
                    <th>Quotation Date</th>
                    <th>Valid Until</th>
                    <th>Total Quote Amount (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($quotations)): ?>
                    <?php foreach ($quotations as $q): ?>
                        <tr>
                            <td class="fw-bold text-info font-monospace fs-6"><?= e($q['quotation_no']) ?></td>
                            <td class="text-secondary small"><?= e($q['quotation_date']) ?></td>
                            <td class="text-warning small"><?= e($q['valid_until']) ?></td>
                            <td class="fw-bold text-success font-monospace fs-6"><?= format_currency($q['total_amount']) ?></td>
                            <td>
                                <?php 
                                $st = strtolower(trim($q['status'] ?? ''));
                                $isRfq = (strpos($q['quotation_no'], 'REQ-') === 0);
                                if ($st === 'converted'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Accepted & Ordered</span>
                                <?php elseif ($st === 'dispatched'): ?>
                                    <span class="badge bg-primary"><i class="bi bi-truck me-1"></i> Approved & Dispatched</span>
                                <?php elseif ($st === 'rejected'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> Quotation Rejected</span>
                                <?php elseif ($st === 'pending_quote' || $st === 'pending' || $st === '' || ($isRfq && $st !== 'active' && $st !== 'sent')): ?>
                                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i> Sent to Admin / Awaiting Quote</span>
                                <?php else: ?>
                                    <span class="badge bg-info"><i class="bi bi-check2-square me-1"></i> Quote Ready for Order</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($st === 'converted'): ?>
                                    <span class="badge bg-secondary px-3 py-1.5"><i class="bi bi-bag-check-fill me-1"></i> Order Placed</span>
                                <?php elseif ($st === 'dispatched'): ?>
                                    <span class="badge bg-success px-3 py-1.5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Order Approved</span>
                                <?php elseif ($st === 'rejected'): ?>
                                    <span class="badge bg-outline-danger px-3 py-1.5 fw-bold text-danger border border-danger"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>
                                <?php elseif ($st === 'pending_quote' || $st === 'pending' || $st === '' || ($isRfq && $st !== 'active' && $st !== 'sent')): ?>
                                    <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-3" disabled>
                                        <i class="bi bi-clock-history me-1"></i> Awaiting Admin Quotation
                                    </button>
                                <?php else: ?>
                                    <a href="<?= url('/customer-portal/quotations/show/' . $q['id']) ?>" class="btn btn-info btn-sm fw-bold px-3 text-dark shadow-sm">
                                        <i class="bi bi-eye-fill me-1"></i> View Quotation
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary">No pending quotations found. Contact your account manager to issue a new price quote.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tax Invoices & Payment Section (STEP 6 & 7) -->
<div class="card bg-dark border-secondary p-4 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0"><i class="bi bi-receipt text-success me-2"></i>My Tax Invoices & Payments</h5>
        <span class="badge bg-success px-2.5 py-1.5 fw-bold"><i class="bi bi-shield-check me-1"></i> Secure Portal Payments</span>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Invoice #</th>
                    <th>Order #</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total Amount (₹)</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td class="fw-bold text-warning font-monospace"><?= e($inv['invoice_no']) ?></td>
                            <td class="text-info font-monospace small"><?= e($inv['order_no']) ?></td>
                            <td class="text-secondary small"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                            <td class="text-<?= (strtotime($inv['due_date']) < time() && $inv['status'] !== 'paid') ? 'danger' : 'warning' ?> small"><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
                            <td class="fw-bold text-success font-monospace fs-6"><?= format_currency($inv['total_amount']) ?></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                <?php elseif ($inv['status'] === 'overdue'): ?>
                                    <span class="badge bg-danger fw-bold"><i class="bi bi-exclamation-circle-fill me-1"></i> Overdue</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-clock-fill me-1"></i> Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/customer-portal/invoices/show/' . $inv['id']) ?>" class="btn btn-info btn-sm fw-bold px-3 text-dark shadow-sm">
                                    <i class="bi bi-eye-fill me-1"></i> View Invoice
                                </a>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success ms-1.5 px-2.5 py-1.5 fw-bold small"><i class="bi bi-check-circle-fill me-1"></i> Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">
                            <i class="bi bi-receipt fs-3 d-block mb-2"></i>
                            No tax invoices yet. Invoices appear here once Admin dispatches your order.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
