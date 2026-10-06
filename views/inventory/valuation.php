<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-calculator text-success me-2"></i>Inventory Valuation Engine</h3>
        <p class="text-secondary small mb-0">Real-time asset valuation reports computed across all product batches and warehouses</p>
    </div>
    <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Bin Stock</a>
</div>

<div class="card bg-dark border-secondary shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Product & SKU</th>
                    <th>Total Qty On Hand</th>
                    <th>Purchase Rate</th>
                    <th>Calculated Valuation Rate</th>
                    <th>Total Asset Valuation Value</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($valuations)): ?>
                    <?php foreach ($valuations as $v): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?= e($v['name']) ?></div>
                                <small class="text-primary"><?= e($v['sku']) ?></small>
                            </td>
                            <td class="fw-bold text-light"><?= $v['total_qty'] ?? 0 ?></td>
                            <td class="text-secondary"><?= format_currency($v['purchase_rate'] ?? 0) ?></td>
                            <td class="fw-semibold text-warning"><?= format_currency($v['valuation_rate'] ?? 0) ?></td>
                            <td class="fw-extrabold text-success fs-5"><?= format_currency($v['total_value'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No inventory valuation records computed.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
