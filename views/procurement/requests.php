<?php $requests = $requests ?? []; ?>
<?php 
    $user = auth_user();
    $canApprovePr = has_permission('procurement.approve_request');
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-file-earmark-text-fill me-2" style="color:var(--primary)"></i>Purchase Request (PR)</h3>
        <p class="page-header-sub">Internal material requests with specification attributes & approval tracking</p>
    </div>
    <?php if (has_permission('procurement.create_request')): ?>
    <a href="<?= url('/procurement/requests/create') ?>" class="btn btn-gradient-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Submit New Request
    </a>
    <?php endif; ?>
</div>

<!-- Requests Table -->
<div class="card p-0 overflow-hidden">
    <div class="table-responsive" style="border:none;border-radius:0;">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr>
                    <th>PR Number</th>
                    <th>Requester & Dept</th>
                    <th>Product & Qty</th>
                    <th>Specifications</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $r): ?>
                        <?php 
                            $attrJson = $r['attribute_values'] ?? null;
                            $attrs = !empty($attrJson) ? json_decode($attrJson, true) : [];
                            $attrCount = is_array($attrs) ? count($attrs) : 0;
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700;color:var(--success);"><?= e($r['request_no']) ?></div>
                                <small style="color:var(--text-muted);"><?= date('d-M-Y', strtotime($r['created_at'])) ?></small>
                                <?php if ($r['status'] === 'approved'): ?>
                                    <a href="<?= url('/procurement/invoices') ?>" class="d-block text-warning text-decoration-none mt-1 font-monospace" style="font-size:0.74rem;font-weight:700;" title="View P2P Vendor Tax Invoices">
                                        <i class="bi bi-receipt me-1"></i> Tax Invoice ➔
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight:600;color:var(--text-primary);">
                                    <i class="bi bi-person-circle text-primary me-1"></i><?= e($r['requester_name'] ?? 'System User') ?>
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-secondary"><?= html_entity_decode(e($r['department'])) ?></span>
                                    <?php if (!empty($r['requester_email'])): ?>
                                        <small class="d-block text-muted" style="font-size:0.75rem;"><?= e($r['requester_email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= e($r['product_name'] ?? 'Generic Item') ?></div>
                                <?php if (($r['item_count'] ?? 1) > 1): ?>
                                    <small class="badge bg-dark border border-secondary text-info fw-bold py-1 mt-1">
                                        + <?= (int)($r['item_count'] - 1) ?> other product items
                                    </small>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <small style="color:var(--primary);font-weight:700;">
                                        Total Qty: <?= (int)($r['total_qty'] ?? $r['requested_qty'] ?? 1) ?> Units
                                    </small>
                                </div>
                            </td>
                            <td>
                                <?php if (($r['item_count'] ?? 1) > 1): ?>
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill font-monospace py-1 px-3" data-bs-toggle="modal" data-bs-target="#itemsModal_<?= $r['id'] ?>" style="font-size:0.78rem;">
                                        <i class="bi bi-list-task me-1 text-warning"></i> View All Items (<?= (int)$r['item_count'] ?>)
                                    </button>
                                <?php elseif ($attrCount > 0): ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill font-monospace py-1 px-3" data-bs-toggle="modal" data-bs-target="#specModal_<?= $r['id'] ?>" style="font-size:0.78rem;">
                                        <i class="bi bi-sliders me-1 text-warning"></i> View Specs (<?= $attrCount ?>)
                                    </button>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);font-style:italic;font-size:0.8rem;">Standard Specifications</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= in_array($r['priority'], ['urgent', 'high']) ? 'danger' : 'info' ?>" style="text-transform:uppercase;">
                                    <?= e($r['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning' : 'danger') ?>" style="text-transform:capitalize;">
                                    <?= e($r['status']) ?>
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <?php if ($canApprovePr): ?>
                                        <div class="d-flex gap-1">
                                            <a href="<?= url('/procurement/requests/approve/' . $r['id']) ?>" class="btn btn-success btn-sm"><i class="bi bi-check-circle-fill me-1"></i>Approve</a>
                                            <a href="<?= url('/procurement/requests/reject/' . $r['id']) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject this PR?');"><i class="bi bi-x-circle-fill"></i></a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:0.8rem;">Pending Approval</span>
                                    <?php endif; ?>
                                <?php elseif ($r['status'] === 'approved'): ?>
                                    <span style="color:var(--success);font-weight:600;font-size:0.8rem;"><i class="bi bi-shield-check me-1"></i>Approved</span>
                                <?php elseif ($r['status'] === 'rejected'): ?>
                                    <span style="color:var(--danger);font-weight:600;font-size:0.8rem;"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                                <?php else: ?>
                                    <span style="color:var(--text-muted);font-size:0.8rem;"><i class="bi bi-lock-fill me-1"></i>Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-file-earmark-text d-block mb-2" style="font-size:2rem;color:var(--border);"></i>
                            No requisitions found. Click <strong style="color:var(--primary);">Submit New Requisition</strong> to create one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Specification Detail Modals -->
<?php if (!empty($requests)): ?>
    <?php foreach ($requests as $r): ?>
        <?php 
            $attrJson = $r['attribute_values'] ?? null;
            $attrs = !empty($attrJson) ? json_decode($attrJson, true) : [];
        ?>
        <?php if (!empty($attrs) && is_array($attrs)): ?>
            <div class="modal fade" id="specModal_<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-heading fw-bold">
                                <i class="bi bi-sliders me-2" style="color:var(--primary);"></i>Specifications — <?= e($r['request_no']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 rounded-3 mb-3" style="background:var(--bg-page);border:1px solid var(--border);">
                                <div class="fw-bold fs-6" style="color:var(--text-primary);"><?= e($r['product_name'] ?? 'Generic Item') ?></div>
                                <small class="text-primary fw-bold"><i class="bi bi-boxes me-1"></i>Total Requested: <?= (int)($r['requested_qty'] ?? 1) ?> Units</small>
                            </div>
                            
                            <div class="d-flex flex-column gap-2.5">
                                <?php foreach ($attrs as $k => $v): ?>
                                    <div class="p-3 rounded-3" style="background:var(--bg-surface);border:1px solid var(--border);">
                                        <div class="small fw-bold mb-1 text-uppercase" style="color:var(--text-muted);letter-spacing:0.05em;">
                                            <i class="bi bi-tag-fill me-1 text-warning"></i><?= e($k) ?>
                                        </div>
                                        <div class="fw-bold" style="color:var(--text-primary);font-size:0.92rem;">
                                            <?= e($v) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Items Breakdown Modals for Multi-Item Requisitions -->
<?php if (!empty($requests)): ?>
    <?php foreach ($requests as $r): ?>
        <?php if (($r['item_count'] ?? 1) > 1): ?>
            <?php
            $db = \App\Core\Database::getInstance();
            $itemsStmt = $db->prepare("
                SELECT pri.*, p.name AS product_name, p.sku AS product_sku, p.purchase_rate
                FROM purchase_request_items pri
                JOIN products p ON pri.product_id = p.id
                WHERE pri.request_id = :rid
            ");
            $itemsStmt->execute(['rid' => $r['id']]);
            $prItems = $itemsStmt->fetchAll();
            ?>
            <div class="modal fade" id="itemsModal_<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content shadow-lg border">
                        <div class="modal-header">
                            <h5 class="modal-title font-heading fw-bold">
                                <i class="bi bi-list-task me-2 text-info"></i>Items Breakdown — <?= e($r['request_no']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead>
                                        <tr class="text-muted">
                                            <th>Product Name</th>
                                            <th>SKU</th>
                                            <th>Rate</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $grandTotal = 0;
                                        foreach ($prItems as $item): 
                                            $subtotal = (float)$item['purchase_rate'] * (int)$item['requested_qty'];
                                            $grandTotal += $subtotal;
                                        ?>
                                            <tr>
                                                <td class="fw-bold" style="color:var(--text-primary);">
                                                    <?= e($item['product_name']) ?>
                                                    <?php 
                                                    $itemAttrJson = $item['attribute_values'] ?? null;
                                                    $itemAttrs = !empty($itemAttrJson) ? json_decode($itemAttrJson, true) : [];
                                                    ?>
                                                    <?php if (!empty($itemAttrs) && is_array($itemAttrs)): ?>
                                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                                            <?php foreach ($itemAttrs as $k => $v): ?>
                                                                <span class="badge bg-secondary text-light fw-semibold" style="font-size: 0.72rem; border: 1px solid rgba(255, 255, 255, 0.15) !important;">
                                                                    <strong><?= e($k) ?>:</strong> <?= e($v) ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="font-monospace text-muted"><?= e($item['product_sku']) ?></td>
                                                <td class="font-monospace text-success"><?= format_currency($item['purchase_rate']) ?></td>
                                                <td class="text-center font-monospace fw-bold text-warning"><?= (int)$item['requested_qty'] ?> Units</td>
                                                <td class="text-end font-monospace fw-bold" style="color:var(--text-primary);"><?= format_currency($subtotal) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-top">
                                            <td colspan="4" class="text-end text-muted fw-bold">Estimated Total:</td>
                                            <td class="text-end font-monospace text-info fw-extrabold fs-6"><?= format_currency($grandTotal) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
