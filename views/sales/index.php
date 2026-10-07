<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold rbac-heading mb-0"><i class="bi bi-cart-check-fill text-warning me-2"></i>Sales Management & Order-to-Cash (O2C) Pipeline</h3>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('/sales/quotations/create') ?>" class="btn btn-outline-info btn-sm fw-bold">
            <i class="bi bi-file-earmark-plus me-1"></i> + Create Quotation
        </a>
        <a href="<?= url('/sales/create') ?>" class="btn btn-primary btn-sm text-white fw-bold shadow-sm" style="background-image:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="bi bi-cart-plus me-1"></i> + Create Sales Order
        </a>
        <a href="<?= url('/sales/returns/create') ?>" class="btn btn-outline-danger btn-sm fw-bold">
            <i class="bi bi-arrow-counterclockwise me-1"></i> + Create Sales Return
        </a>
    </div>
</div>

<?php $activeTab = $activeTab ?? 'orders'; ?>

<!-- Workflow Stepper Summary Matrix (Automated 7-Step O2C Process - Interactive Cards) -->
<div class="d-flex flex-wrap gap-2 mb-4 text-center justify-content-center">
    <!-- Step 1: Customer RFQ -->
    <a href="<?= url('/sales/quotations') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Customer RFQs & Quotations">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= ($activeTab === 'quotations') ? 'border border-2 border-info bg-info bg-opacity-10' : 'border-0' ?>">
            <div class="small text-info fw-bold mb-1"><i class="bi bi-chat-left-dots-fill me-1"></i>1. Customer RFQ</div>
            <div class="fw-extrabold rbac-heading fs-5"><?= count(array_filter($quotations, function($q) { return strpos($q['quotation_no'], 'REQ-') !== false; })) ?></div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>RFQ Inquiries</span>
                <i class="bi bi-arrow-right-short text-info"></i>
            </small>
        </div>
    </a>

    <!-- Step 2: Inquiry Alert -->
    <a href="<?= ($activeTab === 'orders') ? '#portal-inquiry-alert' : url('/sales#portal-inquiry-alert') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Pending Customer Requests & Inquiries">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm border-0">
            <div class="small text-warning fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i>2. Inquiry Alert</div>
            <div class="fw-extrabold rbac-heading fs-5">
                <?= count(array_filter($quotations, function($q) { 
                    $st = strtolower(trim($q['status'] ?? '')); 
                    return ($st === 'pending_quote' || $st === '' || $st === 'pending') && strpos($q['quotation_no'], 'REQ-') !== false; 
                })) ?>
            </div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Pending Action</span>
                <i class="bi bi-arrow-right-short text-warning"></i>
            </small>
        </div>
    </a>

    <!-- Step 3: Admin Quote -->
    <a href="<?= url('/sales/quotations') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Admin Quotations Issued">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= ($activeTab === 'quotations') ? 'border border-2 border-primary bg-primary bg-opacity-10' : 'border-0' ?>">
            <div class="small text-primary fw-bold mb-1"><i class="bi bi-file-earmark-text-fill me-1"></i>3. Admin Quote</div>
            <div class="fw-extrabold rbac-heading fs-5">
                <?= count(array_filter($quotations, function($q) { 
                    $st = strtolower(trim($q['status'] ?? '')); 
                    return $st === 'active' || $st === 'sent'; 
                })) ?>
            </div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Quotes Sent</span>
                <i class="bi bi-arrow-right-short text-primary"></i>
            </small>
        </div>
    </a>

    <!-- Step 4: Customer Order -->
    <a href="<?= url('/sales') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Customer Sales Orders Placed">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= ($activeTab === 'orders') ? 'border border-2 border-success bg-success bg-opacity-10' : 'border-0' ?>">
            <div class="small text-success fw-bold mb-1"><i class="bi bi-cart-check-fill me-1"></i>4. Customer Order</div>
            <div class="fw-extrabold rbac-heading fs-5">
                <?= count(array_filter($quotations, function($q) { 
                    $st = strtolower(trim($q['status'] ?? '')); 
                    return $st === 'converted' || $st === 'approved'; 
                })) ?>
            </div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Orders Placed</span>
                <i class="bi bi-arrow-right-short text-success"></i>
            </small>
        </div>
    </a>

    <!-- Step 5: Dispatch & Inv -->
    <a href="<?= url('/sales') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Dispatch Challans & Generated Invoices">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= ($activeTab === 'orders') ? 'border border-2 border-info bg-info bg-opacity-10' : 'border-0' ?>">
            <div class="small text-info fw-bold mb-1" style="color: #6366f1 !important;"><i class="bi bi-truck me-1"></i>5. Dispatch & Inv</div>
            <div class="fw-extrabold rbac-heading fs-5"><?= count($challans) ?> / <?= count($invoices) ?></div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Items / Invoices</span>
                <i class="bi bi-arrow-right-short text-indigo"></i>
            </small>
        </div>
    </a>

    <!-- Step 6: Cust Payment -->
    <a href="<?= url('/sales/invoices') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Customer Invoices & Paid Status">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= (in_array($activeTab, ['invoices', 'payments'])) ? 'border border-2 border-purple bg-purple bg-opacity-10' : 'border-0' ?>">
            <div class="small text-purple fw-bold mb-1" style="color: #a855f7 !important;"><i class="bi bi-credit-card-2-front-fill me-1"></i>6. Cust Payment</div>
            <div class="fw-extrabold rbac-heading fs-5">
                <?= count(array_filter($invoices, function($inv) { 
                    return strtolower(trim($inv['status'] ?? '')) === 'paid'; 
                })) ?>
            </div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Paid Invoices</span>
                <i class="bi bi-arrow-right-short" style="color: #a855f7;"></i>
            </small>
        </div>
    </a>

    <!-- Step 7: Settlement -->
    <a href="<?= url('/sales/invoices#settlements-section') ?>" class="flex-fill text-decoration-none pipeline-step-card" style="min-width: 130px;" title="View Bank Settlements & Payment Receipts">
        <div class="p-2.5 rounded-3 card h-100 shadow-sm <?= (in_array($activeTab, ['invoices', 'payments'])) ? 'border border-2 border-success bg-success bg-opacity-10' : 'border-0' ?>">
            <div class="small text-success fw-bold mb-1"><i class="bi bi-bank2 me-1"></i>7. Settlement</div>
            <div class="fw-extrabold rbac-heading fs-5"><?= count($payments) ?></div>
            <small class="rbac-subtext d-flex align-items-center justify-content-center gap-1">
                <span>Payments Stored</span>
                <i class="bi bi-arrow-right-short text-success"></i>
            </small>
        </div>
    </a>
</div>

<?php
    $pendingCustomerRequests = [];
    foreach ($quotations as $tmpQ) {
        $tmpSt = strtolower(trim($tmpQ['status'] ?? ''));
        // Show only active pipeline requests needing admin action (pending_quote, sent, converted)
        if ($tmpSt !== 'dispatched' && $tmpSt !== 'completed' && $tmpSt !== 'rejected') {
            $pendingCustomerRequests[] = $tmpQ;
        }
    }
?>

<?php if (!empty($pendingCustomerRequests)): ?>
<div id="portal-inquiry-alert" class="card border-0 shadow-sm p-4 mb-4 rounded-3" style="border-left: 4px solid #f59e0b !important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <span class="badge bg-warning text-dark me-2 font-monospace">PORTAL INQUIRY ALERT</span>
            <i class="bi bi-cart4 text-warning me-2"></i>Incoming Customer Order Requests & Quotes Pipeline (<?= count($pendingCustomerRequests) ?> Active)
        </h5>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 fw-bold">
            <i class="bi bi-broadcast me-1"></i> Live Portal Feed
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Request Ref #</th>
                    <th>Customer Name</th>
                    <th>Requested Material / Item</th>
                    <th>Est Valuation</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingCustomerRequests as $req): ?>
                    <tr>
                        <td class="fw-bold text-warning font-monospace fs-6"><?= e($req['quotation_no']) ?></td>
                        <td class="fw-bold" style="color:var(--text-primary);"><?= e($req['customer_name']) ?></td>
                        <td>
                            <strong class="text-info"><?= e($req['product_name'] ?? 'Custom Material Item') ?></strong>
                            <span class="badge bg-warning text-dark ms-1.5 fw-bold"><?= $req['product_qty'] ?? 1 ?> Units</span>
                        </td>
                        <td class="fw-extrabold text-success font-monospace fs-6"><?= format_currency($req['total_amount']) ?></td>
                        <td>
                            <?php $st = strtolower(trim($req['status'] ?? '')); ?>
                            <?php if ($st === 'converted' || $st === 'approved'): ?>
                                <span class="badge bg-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Order Placed by Customer</span>
                            <?php elseif ($st === 'active' || $st === 'sent'): ?>
                                <span class="badge bg-info fw-bold"><i class="bi bi-send-check-fill me-1"></i> Quote Sent to Customer</span>
                            <?php elseif ($st === 'dispatched'): ?>
                                <span class="badge bg-primary fw-bold"><i class="bi bi-truck me-1"></i> Dispatched & Invoiced</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i> Pending Admin Quote</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1.5 align-items-center">
                                <?php if ($st === 'converted' || $st === 'approved'): ?>
                                    <a href="<?= url('/sales/orders/dispatch/' . $req['id']) ?>" class="btn btn-warning text-dark btn-sm fw-extrabold px-3.5 shadow-lg" onclick="if(confirm('Approve this customer order, dispatch shipment (Delivery Challan) and generate Tax Invoice?')){ this.style.pointerEvents='none'; this.style.opacity='0.6'; return true; } else return false;">
                                        <i class="bi bi-truck me-1.5"></i> 🚚 Approve & Dispatch
                                    </a>
                                    <a href="<?= url('/sales/requests/reject/' . $req['id']) ?>" class="btn btn-outline-danger btn-sm fw-bold px-2.5 shadow-sm" onclick="return confirm('Are you sure you want to REJECT this customer order request?');">
                                        <i class="bi bi-x-circle me-1"></i> Reject
                                    </a>
                                <?php elseif ($st === 'active' || $st === 'sent'): ?>
                                    <button class="btn btn-outline-info btn-sm fw-semibold px-3" disabled>
                                        <i class="bi bi-clock-history me-1 text-info"></i> Awaiting Customer Order
                                    </button>
                                    <a href="<?= url('/sales/requests/reject/' . $req['id']) ?>" class="btn btn-outline-danger btn-sm fw-bold px-2.5 shadow-sm" onclick="return confirm('Are you sure you want to REJECT / CANCEL this issued quotation?');">
                                        <i class="bi bi-x-circle me-1"></i> Reject
                                    </a>
                                <?php elseif ($st === 'dispatched'): ?>
                                    <span class="badge bg-success px-3 py-1.5 fw-bold"><i class="bi bi-check2-all me-1"></i> Order Dispatched & Tax Invoice Sent</span>
                                <?php else: ?>
                                    <a href="<?= url('/sales/create-quotation?customer_id=' . $req['customer_id'] . '&request_id=' . $req['id']) ?>" class="btn btn-outline-warning btn-sm fw-bold px-2.5 shadow-sm">
                                        <i class="bi bi-pencil-square me-1"></i> 1. Edit Quote
                                    </a>
                                    <a href="<?= url('/sales/quotations/send/' . $req['id']) ?>" class="btn btn-success text-white btn-sm fw-bold px-2.5 shadow-sm" onclick="this.style.pointerEvents='none'; this.style.opacity='0.6';">
                                        <i class="bi bi-send-fill me-1"></i> 2. Send Quotation
                                    </a>
                                    <a href="<?= url('/sales/requests/reject/' . $req['id']) ?>" class="btn btn-outline-danger btn-sm fw-bold px-2.5 shadow-sm" onclick="return confirm('Are you sure you want to REJECT this customer inquiry?');">
                                        <i class="bi bi-x-circle me-1"></i> Reject
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $activeTab = $activeTab ?? 'orders'; ?>

<!-- Navigation Links (Separate Pages) -->
<div class="card rbac-card border-0 shadow-sm p-3 mb-4 rounded-3">
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('/sales') ?>" class="sales-nav-tab <?= ($activeTab === 'orders') ? 'active' : '' ?>">
            <i class="bi bi-cart-check-fill text-warning me-1.5"></i> Sales Orders & Challans (Steps 4-5)
        </a>
        <a href="<?= url('/sales/quotations') ?>" class="sales-nav-tab <?= ($activeTab === 'quotations') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text-fill text-info me-1.5"></i> Quotations (Steps 1-3)
        </a>
        <a href="<?= url('/sales/invoices') ?>" class="sales-nav-tab <?= (in_array($activeTab, ['invoices', 'payments'])) ? 'active' : '' ?>">
            <i class="bi bi-receipt-cutoff me-1.5" style="color:#A855F7;"></i> Invoices & Payments (Steps 6-7)
        </a>
        <a href="<?= url('/sales/returns') ?>" class="sales-nav-tab <?= ($activeTab === 'returns') ? 'active' : '' ?>">
            <i class="bi bi-arrow-counterclockwise text-danger me-1.5"></i> Sales Returns
        </a>
    </div>
</div>

<div class="sales-page-content mt-2">
    <?php if ($activeTab === 'orders'): ?>
        <!-- Section 1: Sales Orders & Challans -->
        <div class="card rbac-card border-0 shadow-sm p-4 rounded-3">
            <div class="table-responsive">
                <table class="table rbac-matrix-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Auto Inventory Status</th>
                            <th>Action / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-primary fs-6"><i class="bi bi-cart-fill me-1.5"></i><?= e($o['order_no']) ?></td>
                                    <td class="fw-semibold rbac-heading"><?= e($o['customer_name']) ?></td>
                                    <td class="text-info fw-semibold"><?= e($o['warehouse_name']) ?></td>
                                    <td class="rbac-subtext small"><?= date('d M Y', strtotime($o['order_date'])) ?></td>
                                    <td class="fw-bold text-success fs-6"><?= format_currency($o['total_amount']) ?></td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold">
                                            <i class="bi bi-box-seam me-1"></i>Deducted (-1 Out)
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (!empty($o['invoice_id'])): ?>
                                            <a href="<?= url('/sales/invoices/show/' . $o['invoice_id']) ?>" class="btn btn-outline-success btn-sm fw-bold px-3 py-1 me-1 shadow-sm">
                                                <i class="bi bi-file-earmark-text-fill me-1"></i> Tax Invoice
                                            </a>
                                        <?php endif; ?>
                                        <span class="badge bg-primary px-2.5 py-1.5 align-middle"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 rbac-subtext">No Sales Orders found. Click "+ Create Sales Order" above to issue one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($activeTab === 'quotations'): ?>
        <!-- Section 2: Quotations -->
        <div class="card rbac-card border-0 shadow-sm p-4 rounded-3">
            <div class="table-responsive">
                <table class="table rbac-matrix-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Quotation No</th>
                            <th>Customer Name</th>
                            <th>Quotation Date</th>
                            <th>Valid Until</th>
                            <th>Total Quote (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($quotations)): ?>
                            <?php foreach ($quotations as $q): ?>
                                <tr>
                                    <td class="fw-bold text-info font-monospace fs-6"><?= e($q['quotation_no']) ?></td>
                                    <td class="fw-semibold rbac-heading"><?= e($q['customer_name']) ?></td>
                                    <td class="rbac-subtext"><?= e($q['quotation_date']) ?></td>
                                    <td class="text-warning"><?= e($q['valid_until']) ?></td>
                                    <td class="fw-bold text-success fs-6"><?= format_currency($q['total_amount']) ?></td>
                                    <td>
                                        <?php if ($q['status'] === 'converted'): ?>
                                            <span class="badge bg-success">Converted to Order</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Active Quote</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 rbac-subtext">No Sales Quotations created yet. Click "+ Create Quotation" above to issue one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif (in_array($activeTab, ['invoices', 'payments'])): ?>
        <!-- Section 3: Invoices & Payments -->
        <div class="card rbac-card border-0 shadow-sm p-4 rounded-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-receipt-cutoff me-2" style="color:#A855F7;"></i>Stage 6: Customer Invoices (<?= count($invoices) ?> Total)
                </h5>
                <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 px-2.5 py-1.5 fw-bold" style="color:#A855F7 !important; border-color: rgba(168,85,247,0.3) !important;">
                    <i class="bi bi-credit-card-2-front me-1"></i> Billing & Receivables
                </span>
            </div>
            <div class="table-responsive">
                <table class="table rbac-matrix-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Order No</th>
                            <th>Customer Name</th>
                            <th>Invoice Date</th>
                            <th>Total Amount</th>
                            <th>Paid Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($invoices)): ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td class="fw-bold fs-6" style="color:#A855F7;"><i class="bi bi-receipt me-1.5"></i><?= e($inv['invoice_no']) ?></td>
                                    <td class="text-warning fw-semibold font-monospace"><?= e($inv['order_no']) ?></td>
                                    <td class="fw-semibold rbac-heading"><?= e($inv['customer_name']) ?></td>
                                    <td class="rbac-subtext small"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                    <td class="fw-bold text-success fs-6"><?= format_currency($inv['total_amount']) ?></td>
                                    <td>
                                        <?php if ($inv['status'] === 'paid'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Paid (100%)</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 fw-bold"><i class="bi bi-clock-history me-1"></i>Payment Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= url('/sales/invoices/show/' . $inv['id']) ?>" class="btn btn-outline-info btn-sm fw-bold">
                                            <i class="bi bi-file-earmark-text me-1"></i> Tax Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 rbac-subtext">No Invoices found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 3.2: Stage 7 Settlement & Payment Receipts -->
        <div id="settlements-section" class="card rbac-card border-0 shadow-sm p-4 rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bank2 text-success me-2"></i>Stage 7: Settlement & Payment Receipts (<?= count($payments) ?> Stored)
                </h5>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 fw-bold">
                    <i class="bi bi-shield-check me-1"></i> Bank Reconciled
                </span>
            </div>
            <div class="table-responsive">
                <table class="table rbac-matrix-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Payment Ref #</th>
                            <th>Invoice Ref</th>
                            <th>Customer Name</th>
                            <th>Payment Date</th>
                            <th>Payment Mode</th>
                            <th>Amount Settled</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="fw-bold text-success font-monospace fs-6">
                                        <i class="bi bi-shield-fill-check me-1.5"></i><?= e($p['payment_no'] ?? ('PAY-' . $p['id'])) ?>
                                    </td>
                                    <td class="fw-semibold text-info font-monospace"><?= e($p['invoice_no'] ?? '-') ?></td>
                                    <td class="fw-semibold rbac-heading"><?= e($p['customer_name']) ?></td>
                                    <td class="rbac-subtext small"><?= !empty($p['payment_date']) ? date('d M Y', strtotime($p['payment_date'])) : '-' ?></td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-20 text-light border border-secondary border-opacity-25 px-2.5 py-1 fw-bold">
                                            <i class="bi bi-wallet2 me-1"></i><?= strtoupper(e($p['payment_mode'] ?? 'Online / Bank')) ?>
                                        </span>
                                    </td>
                                    <td class="fw-extrabold text-success fs-6"><?= format_currency($p['amount'] ?? 0) ?></td>
                                    <td>
                                        <span class="badge bg-success px-2.5 py-1.5 fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i> Settlement Complete
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4 rbac-subtext">No Payment settlements recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($activeTab === 'returns'): ?>
        <!-- Section 4: Sales Returns -->
        <div class="card rbac-card border-0 shadow-sm p-4 rounded-3">
            <div class="table-responsive">
                <table class="table rbac-matrix-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Return No</th>
                            <th>Sales Order</th>
                            <th>Customer Name</th>
                            <th>Return Reason / Inspection Note</th>
                            <th>Return Date</th>
                            <th>Returned Qty</th>
                            <th>Refund Amount</th>
                            <th>Inventory Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($returns)): ?>
                            <?php foreach ($returns as $ret): ?>
                                <tr>
                                    <td class="fw-bold text-danger font-monospace fs-6"><i class="bi bi-arrow-counterclockwise me-1"></i><?= e($ret['return_no']) ?></td>
                                    <td class="text-warning fw-semibold font-monospace"><?= e($ret['order_no']) ?></td>
                                    <td class="fw-semibold rbac-heading"><?= e($ret['customer_name']) ?></td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1.5 fw-bold text-wrap text-start" style="max-width:250px;">
                                            <i class="bi bi-chat-left-text me-1"></i><?= e($ret['reason'] ?: 'Customer Return Inspection') ?>
                                        </span>
                                    </td>
                                    <td class="rbac-subtext small"><?= date('d M Y', strtotime($ret['return_date'])) ?></td>
                                    <td class="fw-extrabold text-danger fs-6"><?= $ret['returned_qty'] ?> Units</td>
                                    <td class="fw-bold text-success fs-6"><?= format_currency($ret['refund_amount']) ?></td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold">
                                            <i class="bi bi-box-arrow-in-down me-1"></i>Restocked (+<?= $ret['returned_qty'] ?> In)
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-4 rbac-subtext">No Sales Returns logged yet. Click "+ Create Sales Return" above to process one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* ─── DUAL THEME ADAPTIVE STYLING (LIGHT & DARK MODE COMPATIBLE) ─── */

.rbac-heading { color: #0F172A !important; }
.rbac-subtext { color: #64748B !important; }

.rbac-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    color: #0F172A !important;
}

/* ─── SALES NAVIGATION TABS (VIBRANT SAAS PILLS) ─── */
.sales-nav-tab {
    display: inline-flex;
    align-items: center;
    padding: 0.55rem 1.15rem;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 8px;
    color: #334155;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.sales-nav-tab:hover {
    color: #2563EB !important;
    background-color: #EFF6FF !important;
    border-color: #93C5FD !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15) !important;
}

.sales-nav-tab.active {
    color: #FFFFFF !important;
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
    border-color: #2563EB !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
}

.sales-nav-tab.active:hover {
    background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%) !important;
    color: #FFFFFF !important;
}

/* ─── PIPELINE STEP CARDS HOVER ─── */
.pipeline-step-card {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease;
    cursor: pointer;
}
.pipeline-step-card:hover {
    transform: translateY(-3px);
}
.pipeline-step-card:hover .card {
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.18) !important;
    border: 1px solid rgba(37, 99, 235, 0.4) !important;
    background-color: #F8FAFC !important;
}
.pipeline-step-card:active {
    transform: translateY(-1px);
}

/* ─── MODERN VIBRANT OUTLINE BUTTON HOVERS (PREVENT BLACK HOVER) ─── */
.btn-outline-info:hover {
    background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%) !important;
    border-color: #0284C7 !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3) !important;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%) !important;
    border-color: #EF4444 !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
}

.btn-outline-warning:hover {
    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important;
    border-color: #F59E0B !important;
    color: #0F172A !important;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
}

.btn-outline-success:hover {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
    border-color: #10B981 !important;
    color: #FFFFFF !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
}

.btn-outline-secondary:hover {
    background-color: #EFF6FF !important;
    border-color: #93C5FD !important;
    color: #2563EB !important;
}

.rbac-matrix-table {
    color: #0F172A !important;
    background-color: #FFFFFF !important;
}
.rbac-matrix-table th {
    background-color: #F1F5F9 !important;
    color: #334155 !important;
}

/* ─── DARK MODE OVERRIDES ([data-theme="dark"]) ─── */
[data-theme="dark"] .rbac-heading { color: #F8FAFC !important; }
[data-theme="dark"] .rbac-subtext { color: #94A3B8 !important; }

[data-theme="dark"] .rbac-card {
    background: #1E293B !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #F8FAFC !important;
}

[data-theme="dark"] .sales-nav-tab {
    color: #94A3B8;
    background: #0F172A;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .sales-nav-tab:hover {
    color: #60A5FA !important;
    background-color: rgba(37, 99, 235, 0.18) !important;
    border-color: rgba(96, 165, 250, 0.5) !important;
}

[data-theme="dark"] .sales-nav-tab.active {
    color: #FFFFFF !important;
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
    border-color: #2563EB !important;
}

[data-theme="dark"] .pipeline-step-card:hover .card {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important;
    border: 1px solid rgba(99, 102, 241, 0.5) !important;
    background-color: #1E293B !important;
}

[data-theme="dark"] .rbac-matrix-table {
    color: #F8FAFC !important;
    background-color: #1E293B !important;
}
[data-theme="dark"] .rbac-matrix-table th {
    background-color: #0F172A !important;
    color: #94A3B8 !important;
}
</style>
