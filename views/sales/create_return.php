<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-arrow-counterclockwise text-danger me-2.5"></i>Sales Return & Inventory Restock</h3>
        <p class="page-header-sub">Record returned items from customers and automatically restock inventory balances</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales Hub
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4 col-lg-8 mx-auto">
    <form action="<?= url('/sales/returns/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="mb-3">
            <label class="form-label small fw-bold">Select Sales Order & Linked Tax Invoice *</label>
            <select name="order_id" class="form-select fw-semibold" required>
                <option value="">-- Choose Dispatched Order / Invoice --</option>
                <?php foreach ($orders as $o): ?>
                    <option value="<?= $o['id'] ?>">
                        <?= e($o['order_no']) ?> <?= !empty($o['invoice_no']) ? ' [Invoice: ' . e($o['invoice_no']) . ']' : '' ?> — Customer: <?= e($o['customer_name']) ?> (Total: ₹<?= number_format($o['total_amount'], 2) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Returned Quantity *</label>
            <input type="number" name="qty" class="form-control" placeholder="1" min="1" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Return Reason / Damage Inspection Note *</label>
            <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Wrong item ordered / Customer changed specs" required></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <a href="<?= url('/sales') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-danger btn-sm fw-bold text-white px-4 shadow-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Process Return & Restock Inventory
            </button>
        </div>
    </form>
</div>
