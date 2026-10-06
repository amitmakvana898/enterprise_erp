<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-journal-text text-warning me-2"></i>Stock Audit Ledger (Movement Trail)</h3>
        <p class="text-secondary small mb-0">Immutable transaction log of all inward, outward, and transfer movements</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Bin Stock</a>
</div>

<div class="card bg-dark border-secondary shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Txn No & Time</th>
                    <th>Product & SKU</th>
                    <th>Warehouse & Bin</th>
                    <th>Type & Ref</th>
                    <th>In Qty</th>
                    <th>Out Qty</th>
                    <th>Balance</th>
                    <th>Total Cost</th>
                    <th>Logged By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?= e($t['transaction_no']) ?></div>
                                <small class="text-secondary"><?= e($t['created_at']) ?></small>
                            </td>
                            <td>
                                <div class="fw-semibold text-light"><?= e($t['product_name']) ?></div>
                                <small class="text-primary"><?= e($t['sku']) ?></small>
                            </td>
                            <td>
                                <div class="text-light"><?= e($t['warehouse_name']) ?></div>
                                <span class="badge bg-secondary"><?= e($t['bin_code']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= strpos($t['transaction_type'], 'ADD') !== false || strpos($t['transaction_type'], 'IN') !== false ? 'success' : 'danger' ?>">
                                    <?= e($t['transaction_type']) ?>
                                </span>
                                <div class="small text-secondary mt-1"><?= e($t['reference_type']) ?></div>
                            </td>
                            <td class="text-success fw-bold"><?= $t['in_qty'] ?></td>
                            <td class="text-danger fw-bold"><?= $t['out_qty'] ?></td>
                            <td class="text-warning fw-bold fs-6"><?= $t['balance_qty'] ?></td>
                            <td class="fw-bold text-light"><?= format_currency($t['total_cost']) ?></td>
                            <td class="text-secondary small"><?= e($t['user_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No stock transaction ledger logs recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
