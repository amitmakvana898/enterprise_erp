<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-building-fill-gear text-primary me-2"></i>Supplier Master & Performance Ratings</h3>
        <p class="text-secondary small mb-0">Vendor directory, GSTIN, credit limits, ratings, and payment terms</p>
    </div>
    <a href="<?= url('/suppliers/create') ?>" class="btn btn-primary fw-semibold"><i class="bi bi-plus-circle me-1"></i> Register New Supplier</a>
</div>

<div class="card shadow-sm p-4 border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Vendor Code & Name</th>
                    <th>Email & Phone</th>
                    <th>GSTIN</th>
                    <th>Credit Limit</th>
                    <th>Payment Terms</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($suppliers)): ?>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= e($s['name']) ?></div>
                                <small class="text-primary font-monospace"><?= e($s['code']) ?></small>
                            </td>
                            <td class="small">
                                <div><?= e($s['email']) ?></div>
                                <small class="text-secondary"><?= e($s['phone']) ?></small>
                            </td>
                            <td><span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 font-monospace"><?= e($s['gstin']) ?></span></td>
                            <td class="fw-semibold font-monospace"><?= format_currency($s['credit_limit']) ?></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= e($s['payment_terms']) ?></span></td>
                            <td><span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-40 fw-bold"><i class="bi bi-star-fill me-1"></i><?= e($s['rating']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No suppliers registered.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
