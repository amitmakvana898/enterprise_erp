<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-bag-check-fill me-2.5" style="color:var(--primary)"></i>Purchase Orders (PO)</h3>
        <p class="page-header-sub">Issued supplier purchase orders, direct regular vendor POs, and P2P contract settlements</p>
    </div>
    <?php if (has_permission('procurement.create_po')): ?>
    <div class="d-flex gap-2">
        <a href="<?= url('/procurement/orders/create?mode=direct') ?>" class="btn btn-warning text-dark btn-sm fw-bold">
            <i class="bi bi-lightning-charge-fill me-1"></i> ⚡ Direct Express PO (Regular Vendor)
        </a>
        <a href="<?= url('/procurement/orders/create') ?>" class="btn btn-gradient-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Issue Purchase Order
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr class="text-nowrap">
                    <th>PO Number</th>
                    <th>Supplier / Vendor</th>
                    <th>Warehouse</th>
                    <th>Fulfillment Progress</th>
                    <th>Total Contract Amount</th>
                    <th>Status</th>
                    <th>Next P2P Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $po): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= e($po['po_no']) ?></td>
                            <td>
                                <div class="fw-semibold" style="color:var(--text-primary);"><?= e($po['supplier_name']) ?></div>
                            </td>
                            <td><?= e($po['warehouse_name']) ?></td>
                            <td>
                                <?php 
                                $ordered = (int)($po['total_ordered_qty'] ?? 0);
                                $received = (int)($po['total_received_qty'] ?? 0);
                                $pending = max(0, $ordered - $received);
                                ?>
                                <div class="d-flex flex-column gap-1">
                                    <div class="small fw-bold" style="color:var(--text-primary);">
                                        <span class="text-success"><?= $received ?> Received</span> / <?= $ordered ?> Pcs
                                    </div>
                                    <?php if ($pending > 0): ?>
                                        <span class="badge bg-warning text-dark font-monospace" style="font-size:0.72rem; max-width:fit-content;">
                                            ⏳ <?= $pending ?> Pcs Pending Shipment
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success font-monospace" style="font-size:0.72rem; max-width:fit-content;">
                                            ✅ 100% Fully Received
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="fw-bold text-success fs-6 font-monospace"><?= format_currency($po['total_amount']) ?></td>
                            <td>
                                <?php if (!empty($po['is_order_settled'])): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success px-2.5 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Completed & Settled
                                    </span>
                                <?php elseif (!empty($po['is_fully_received'])): ?>
                                    <span class="badge bg-info bg-opacity-20 text-info border border-info px-2.5 py-1">
                                        <i class="bi bi-box-seam me-1"></i> 100% Received
                                    </span>
                                <?php elseif ($pending > 0 && $received > 0): ?>
                                    <span class="badge bg-warning bg-opacity-20 text-warning border border-warning px-2.5 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i> Partially Received
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-20 text-primary border border-primary px-2.5 py-1">
                                        <i class="bi bi-check2-circle me-1"></i> Approved PO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                                    <?php if (!empty($po['is_order_settled'])): ?>
                                        <span class="badge bg-success bg-opacity-20 text-success border border-success px-3 py-1.5 fs-6">
                                            <i class="bi bi-check-circle-fill me-1.5"></i> Order 100% Completed & Settled
                                        </span>
                                    <?php elseif (!empty($po['is_fully_received']) && !empty($po['is_fully_invoiced'])): ?>
                                        <a href="<?= url('/procurement/payments/create?po_id=' . $po['id'] . '&supplier_id=' . $po['supplier_id']) ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                            <i class="bi bi-credit-card-fill me-1"></i> Process Vendor Payment
                                        </a>
                                        <span class="badge bg-info bg-opacity-20 text-info border border-info px-2.5 py-1">
                                            <i class="bi bi-receipt me-1"></i> Invoice Booked (Awaiting Payment)
                                        </span>
                                    <?php else: ?>
                                        <?php if ($pending > 0): ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="openExpediteModal(<?= $po['id'] ?>, '<?= e($po['po_no']) ?>', '<?= e($po['supplier_name']) ?>', '<?= e($po['supplier_email'] ?? 'supplier@vendor.com') ?>', <?= $ordered ?>, <?= $received ?>, <?= $pending ?>)">
                                                <i class="bi bi-envelope-at me-1"></i> Send Expedite Email Notice
                                            </button>
                                            <a href="<?= url('/procurement/grns/create?po_id=' . $po['id']) ?>" class="btn btn-warning btn-sm text-dark fw-bold">
                                                <i class="bi bi-truck me-1"></i> + Receive Shipment GRN
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($received > 0 && empty($po['is_fully_invoiced'])): ?>
                                            <a href="<?= url('/procurement/invoices/create?po_id=' . $po['id'] . '&supplier_id=' . $po['supplier_id']) ?>" class="btn btn-outline-info btn-sm fw-bold">
                                                <i class="bi bi-receipt-cutoff me-1"></i> Book Partial Invoice
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-inbox fs-2 opacity-50 mb-2 d-block"></i>
                            No Purchase Orders issued yet. Click <strong>Issue New Purchase Order</strong> to start.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Send Pending Delivery Reminder & Expedite Email -->
<div class="modal fade" id="expediteModal" tabindex="-1" aria-labelledby="expediteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <form action="<?= url('/procurement/orders/send-reminder') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::generateCsrfToken() ?>">
                <input type="hidden" name="po_id" id="modalPoId" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning" id="expediteModalLabel">
                        <i class="bi bi-envelope-at-fill me-2"></i> Send Pending Shipment Delivery Reminder Email
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="p-3 rounded-3 mb-4 bg-body-tertiary border">
                        <div class="row g-3 small">
                            <div class="col-md-4">
                                <span class="text-secondary d-block">PO Reference:</span>
                                <strong class="text-info fs-6 font-monospace" id="modalPoNo">PO-2026-6407</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-secondary d-block">Supplier / Vendor:</span>
                                <strong class="fs-6" id="modalSupplierName">abc</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-secondary d-block">Pending Balance Quantity:</span>
                                <strong class="text-warning fs-6 font-monospace" id="modalPendingQty">300 Pcs Pending</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Recipient Supplier Email *</label>
                            <div class="input-group">
                                <span class="input-group-text text-info"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="recipient_email" id="modalRecipientEmail" class="form-control" placeholder="supplier@vendor.com" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Target Expected Next Delivery Date *</label>
                            <div class="input-group">
                                <span class="input-group-text text-warning"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="expected_delivery_date" id="modalDeliveryDate" class="form-control fw-bold" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Urgency Level *</label>
                        <select name="urgency_level" class="form-select">
                            <option value="HIGH - EXPEDITE REQUESTED" selected>🚨 High - Expedite Delivery Requested</option>
                            <option value="URGENT - PRODUCTION CRITICAL">⚡ Urgent - Production Critical Blocked</option>
                            <option value="MEDIUM - SCHEDULED FOLLOW UP">📅 Medium - Standard Follow-Up Reminder</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Buyer Remarks & Expedite Message for Vendor *</label>
                        <textarea name="custom_notes" id="modalCustomNotes" class="form-control" rows="4" required>Dear Vendor, Please expedite shipment of the remaining pending items by the selected target date as our inventory demands depend on this fulfillment.</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
                        <i class="bi bi-send-fill me-1.5"></i> Dispatch Pending Shipment Reminder Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openExpediteModal(poId, poNo, supplierName, supplierEmail, ordered, received, pending) {
    document.getElementById('modalPoId').value = poId;
    document.getElementById('modalPoNo').innerText = poNo;
    document.getElementById('modalSupplierName').innerText = supplierName;
    document.getElementById('modalPendingQty').innerText = pending + ' Pcs Pending (Received ' + received + ' / ' + ordered + ' Pcs)';
    document.getElementById('modalRecipientEmail').value = supplierEmail || 'supplier@vendor.com';
    document.getElementById('modalCustomNotes').value = 'Dear ' + supplierName + ',\n\nWe have received ' + received + ' units so far out of ' + ordered + ' units for Order ' + poNo + '. Please expedite shipment for the remaining ' + pending + ' pending units on or before the selected target delivery date.';
    
    var myModal = new bootstrap.Modal(document.getElementById('expediteModal'));
    myModal.show();
}
</script>
