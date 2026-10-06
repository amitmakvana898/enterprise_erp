<?php 
    $user = auth_user();
    $canQc = has_permission('inventory.qc');
    $canPay = has_permission('finance.payments');
    $canReceiveGrn = has_permission('inventory.grn');
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-truck-front-fill me-2.5" style="color:var(--warning)"></i>Goods Receipt Notes (GRN) & Quality Check</h3>
        <p class="page-header-sub">Warehouse material receiving, inspection, and bin stock posting</p>
    </div>
    <?php if ($canReceiveGrn): ?>
        <a href="<?= url('/procurement/grns/create') ?>" class="btn btn-gradient-primary btn-sm">
            <i class="bi bi-truck me-1"></i> Receive Material GRN
        </a>
    <?php endif; ?>
</div>

<?php if (!empty($pendingOrders)): ?>
    <div class="card p-3 mb-4 rounded-3 border-warning border-opacity-40">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-warning mb-0"><i class="bi bi-clock-history me-2"></i>Active Purchase Orders Awaiting Next Shipment Batch</h6>
            <span class="badge bg-warning text-dark font-monospace"><?= count($pendingOrders) ?> Orders Pending Receipt</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr class="text-uppercase small">
                        <th>PO Reference</th>
                        <th>Supplier / Vendor</th>
                        <th>Warehouse</th>
                        <th class="text-center">Order Progress</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $po): ?>
                        <?php 
                        $ord = (int)$po['total_ordered_qty'];
                        $rec = (int)$po['total_received_qty'];
                        $pend = max(0, $ord - $rec);
                        ?>
                        <tr>
                            <td><strong class="text-primary font-monospace"><?= e($po['po_no']) ?></strong></td>
                            <td class="fw-semibold"><?= e($po['supplier_name']) ?></td>
                            <td class="text-muted"><?= e($po['warehouse_name']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-success me-1"><?= $rec ?> Received</span>
                                <span class="badge bg-warning text-dark">⏳ <?= $pend ?> Pcs Pending</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="openGrnNoticeModal(null, 'PO-REF-<?= $po['id'] ?>', '<?= e($po['po_no']) ?>', '<?= e($po['supplier_name']) ?>', '<?= e($po['supplier_email'] ?? 'supplier@vendor.com') ?>', '<?= e($po['supplier_phone'] ?? '9876543210') ?>', 'PARTIAL-GRN', 'Main Warehouse', <?= $ord ?>, <?= $rec ?>, <?= $pend ?>)">
                                        <i class="bi bi-whatsapp me-1 text-success"></i> Send Notice (<?= $pend ?> Pcs)
                                    </button>
                                    <a href="<?= url('/procurement/grns/create?po_id=' . $po['id']) ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                        <i class="bi bi-truck me-1"></i> Receive <?= $pend ?> Pcs
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr>
                    <th>GRN No</th>
                    <th>Ref PO</th>
                    <th>Supplier</th>
                    <th>Warehouse</th>
                    <th>Challan No</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grns)): ?>
                    <?php foreach ($grns as $g): ?>
                        <?php 
                        $poOrd = (int)($g['po_ordered_qty'] ?? 0);
                        $poRec = (int)($g['po_received_qty'] ?? 0);
                        $poPend = max(0, $poOrd - $poRec);
                        ?>
                        <tr>
                            <td class="fw-bold text-primary font-monospace"><?= e($g['grn_no']) ?></td>
                            <td class="fw-semibold font-monospace"><?= e($g['po_no']) ?></td>
                            <td><?= e($g['supplier_name']) ?></td>
                            <td><?= e($g['warehouse_name']) ?></td>
                            <td class="text-warning fw-semibold font-monospace"><?= e($g['challan_no'] ?? 'CH-N/A') ?></td>
                            <td>
                                <span class="badge bg-<?= $g['status'] === 'completed' ? 'success' : ($g['status'] === 'pending_qc' ? 'warning' : 'info') ?> text-capitalize">
                                    <?= str_replace('_', ' ', e($g['status'])) ?>
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php if ($g['status'] === 'pending_qc'): ?>
                                    <?php if ($canQc): ?>
                                        <a href="<?= url('/procurement/grns/qc/' . $g['id']) ?>" class="btn btn-warning btn-sm fw-bold px-3"><i class="bi bi-shield-check me-1"></i> Conduct QC & Post Stock</a>
                                    <?php else: ?>
                                        <span class="badge bg-warning"><i class="bi bi-hourglass-split me-1"></i> Pending QC Inspection</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-all me-1"></i> Stock Bin Committed
                                        </span>

                                        <?php if (!empty($g['invoice_count']) && ($g['invoice_status'] === 'paid' || ($g['payment_count'] ?? 0) > 0)): ?>
                                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-40 px-2.5 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i> Invoice Paid & Settled
                                            </span>
                                        <?php elseif (!empty($g['invoice_count'])): ?>
                                            <a href="<?= url('/procurement/invoices') ?>" class="btn btn-outline-warning btn-sm fw-bold">
                                                <i class="bi bi-receipt me-1"></i> Invoice Booked (Unpaid)
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= url('/procurement/invoices/create?po_id=' . $g['po_id'] . '&supplier_id=' . $g['supplier_id']) ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                                <i class="bi bi-receipt-cutoff me-1"></i> Book Vendor Invoice
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($poPend > 0): ?>
                                            <a href="<?= url('/procurement/grns/create?po_id=' . $g['po_id']) ?>" class="btn btn-outline-warning btn-sm fw-bold" title="Receive next batch for this order">
                                                <i class="bi bi-truck me-1"></i> + Receive Next Batch (<?= $poPend ?> Pcs Pending)
                                            </a>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-outline-success btn-sm fw-bold ms-1" onclick="openGrnNoticeModal(<?= $g['id'] ?>, '<?= e($g['grn_no']) ?>', '<?= e($g['po_no']) ?>', '<?= e($g['supplier_name']) ?>', '<?= e($g['supplier_email'] ?? 'supplier@vendor.com') ?>', '<?= e($g['supplier_phone'] ?? '9876543210') ?>', '<?= e($g['challan_no'] ?? 'CH-N/A') ?>', '<?= e($g['warehouse_name']) ?>', <?= $poOrd ?>, <?= $poRec ?>, <?= $poPend ?>)">
                                            <i class="bi bi-whatsapp me-1 text-success"></i> WhatsApp & Email Notice
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2 d-block"></i>
                            No Goods Receipt Notes found. Click <strong>Receive Material GRN</strong> to record a receipt.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Send Goods Receipt Confirmation Modal (WhatsApp & Email) -->
<div class="modal fade" id="grnNoticeModal" tabindex="-1" aria-labelledby="grnNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border">
            <div class="modal-header border-bottom bg-success bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-success" id="grnNoticeModalLabel">
                    <i class="bi bi-box-seam-fill me-2"></i>Send Goods Receipt & Pending Delivery Notice to Vendor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/procurement/grns/send-receipt-notice') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                <input type="hidden" name="grn_id" id="modal_grn_id">
                
                <div class="modal-body p-4">
                    <!-- Quick WhatsApp Web Dispatch Alert Box -->
                    <div class="p-3 mb-3 rounded-3 border border-success bg-success bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <small class="text-success fw-bold d-block text-uppercase" style="letter-spacing:0.05em;"><i class="bi bi-whatsapp me-1"></i> Instant WhatsApp Dispatch</small>
                                <span class="small text-muted">Send pre-formatted receipt & pending delivery notice directly to vendor's WhatsApp</span>
                            </div>
                            <a id="modal_whatsapp_link" href="#" target="_blank" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                <i class="bi bi-whatsapp me-1"></i> Send via WhatsApp Web
                            </a>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mb-3 bg-body-tertiary border">
                        <div class="row g-2 small">
                            <div class="col-md-6"><strong class="text-muted">GRN Ref:</strong> <span id="modal_grn_no" class="text-primary font-monospace fw-semibold"></span></div>
                            <div class="col-md-6"><strong class="text-muted">PO Ref:</strong> <span id="modal_po_no" class="text-info font-monospace fw-semibold"></span></div>
                            <div class="col-md-6"><strong class="text-muted">Vendor:</strong> <span id="modal_supplier_name" class="fw-semibold"></span></div>
                            <div class="col-md-6"><strong class="text-muted">Challan / LR:</strong> <span id="modal_challan_no" class="text-warning font-monospace fw-semibold"></span></div>
                        </div>
                    </div>

                    <!-- Pending Quantity & Target Expected Delivery Date Configuration Box -->
                    <div id="modal_pending_container" class="p-3 mb-3 rounded-3 border border-warning bg-warning bg-opacity-10">
                        <div class="fw-bold text-warning mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Partial Shipment & Pending Delivery Configuration:</div>
                        <div class="row g-2 mb-2 small">
                            <div class="col-4">PO Ordered: <strong id="modal_po_ordered" class="fw-bold">0</strong> Pcs</div>
                            <div class="col-4">Received So Far: <strong id="modal_po_received" class="text-success fw-bold">0</strong> Pcs</div>
                            <div class="col-4">Remaining Pending: <strong id="modal_po_pending" class="text-danger fw-bold fs-6">0</strong> Pcs</div>
                        </div>

                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label text-warning small fw-bold mb-1">📅 Target Expected Delivery Date for Remaining Quantity *</label>
                                <input type="date" name="target_delivery_date" id="modal_target_delivery_date" class="form-control form-control-sm border-warning fw-bold" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" onchange="updateWaNoticeUrl()">
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block mt-md-3">Vendor will be asked to dispatch remaining pending quantity on or before this date.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vendor Official Email Address *</label>
                        <input type="email" name="recipient_email" id="modal_recipient_email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Warehouse Inspector Remarks / Expedite Notes</label>
                        <textarea name="custom_notes" id="modal_custom_notes" class="form-control small" rows="3" placeholder="e.g. Received 50 pcs batch in good condition. Remaining 50 pcs are urgently required for project assembly." oninput="updateWaNoticeUrl()"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> Dispatch Official Email & Expedite Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var currentNoticeData = {};

function openGrnNoticeModal(grnId, grnNo, poNo, supplierName, supplierEmail, supplierPhone, challanNo, warehouseName, poOrd, poRec, poPend) {
    currentNoticeData = {
        grnId: grnId,
        grnNo: grnNo,
        poNo: poNo,
        supplierName: supplierName,
        supplierEmail: supplierEmail,
        supplierPhone: supplierPhone,
        challanNo: challanNo,
        warehouseName: warehouseName,
        poOrd: poOrd || 0,
        poRec: poRec || 0,
        poPend: poPend || 0
    };

    document.getElementById('modal_grn_id').value = grnId || 0;
    document.getElementById('modal_grn_no').innerText = grnNo;
    document.getElementById('modal_po_no').innerText = poNo;
    document.getElementById('modal_supplier_name').innerText = supplierName;
    document.getElementById('modal_challan_no').innerText = challanNo;
    document.getElementById('modal_recipient_email').value = supplierEmail;

    document.getElementById('modal_po_ordered').innerText = currentNoticeData.poOrd;
    document.getElementById('modal_po_received').innerText = currentNoticeData.poRec;
    document.getElementById('modal_po_pending').innerText = currentNoticeData.poPend;

    updateWaNoticeUrl();

    var modal = new bootstrap.Modal(document.getElementById('grnNoticeModal'));
    modal.show();
}

function updateWaNoticeUrl() {
    var data = currentNoticeData;
    if (!data.supplierName) return;

    var cleanPhone = (data.supplierPhone || '').replace(/[^0-9]/g, '');
    if (cleanPhone.length === 10) {
        cleanPhone = '91' + cleanPhone; // Default +91
    }

    var targetDateVal = document.getElementById('modal_target_delivery_date').value;
    var targetFormatted = targetDateVal ? new Date(targetDateVal).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : 'As per PO terms';
    var notes = document.getElementById('modal_custom_notes').value;

    var msg = "📦 *GOODS RECEIPT & PENDING DELIVERY NOTICE*\n\n" +
        "Dear " + data.supplierName + ",\n" +
        "This is to confirm that our warehouse (" + data.warehouseName + ") has received a batch of *" + data.poRec + " Pcs* under PO: *" + data.poNo + "* (GRN: *" + data.grnNo + "*).\n\n";

    if (data.poPend > 0) {
        msg += "⚠️ *PENDING BALANCE:* *" + data.poPend + " Pcs* remain pending.\n" +
            "📅 *TARGET EXPECTED DELIVERY DATE:* *" + targetFormatted + "*\n\n" +
            "Please confirm dispatch of the remaining " + data.poPend + " Pcs on or before *" + targetFormatted + "*.\n\n";
    }

    if (notes) {
        msg += "📝 *Remarks:* " + notes + "\n\n";
    }

    msg += "Thank you!\n— Enterprise ERP Procurement & Warehouse Division";

    var waUrl = "https://api.whatsapp.com/send?phone=" + cleanPhone + "&text=" + encodeURIComponent(msg);
    document.getElementById('modal_whatsapp_link').setAttribute('href', waUrl);
}
</script>
