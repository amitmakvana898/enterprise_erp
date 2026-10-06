<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-cart-plus text-primary me-2.5"></i>Submit New Customer Order Request</h3>
        <p class="page-header-sub">Submit a product order inquiry or material request directly to your account manager</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/customer-portal/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card p-4 shadow-sm mb-4 border">
    <form action="<?= url('/customer-portal/requests/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Customer Account</label>
                <input type="text" class="form-control bg-body-tertiary fw-semibold" readonly value="<?= e($customer['name']) ?> (Code: <?= e($customer['code']) ?>)">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Select Requested Product *</label>
                <select name="product_id" class="form-select fw-semibold" required>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?> • Est Rate: <?= format_currency($p['selling_rate']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled selected>No products available in catalog</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Requested Quantity *</label>
                <div class="input-group">
                    <input type="number" name="qty" class="form-control text-center fw-bold" min="1" value="1" required>
                    <span class="input-group-text text-muted">Units</span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Order Requirement Type *</label>
                <select name="requirement_type" class="form-select fw-semibold" required>
                    <option value="Standard Order" selected>Standard Product Order</option>
                    <option value="Urgent Delivery">Urgent / Priority Delivery</option>
                    <option value="Bulk Packaging">Bulk Packaging & Export Specs</option>
                    <option value="Custom Spec">Custom Specification Request</option>
                    <option value="Sample Order">Sample / Trial Order</option>
                    <option value="Recurring Order">Contractual Recurring Order</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold">Order Inquiry Notes</label>
                <input type="text" name="inquiry_notes" class="form-control" placeholder="e.g. Special handling or delivery time instructions">
            </div>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/customer-portal/dashboard') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm" <?= empty($products) ? 'disabled' : '' ?>>
                <i class="bi bi-send-fill me-1"></i> Submit Order Request & Notify Manager
            </button>
        </div>
    </form>
</div>
