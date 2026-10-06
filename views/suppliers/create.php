<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-building-add text-primary me-2.5"></i>Register Supplier Vendor</h3>
        <p class="page-header-sub">Record bank details, credit limits, GSTIN profile, and official contacts</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/suppliers') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4">
    <form action="<?= url('/suppliers/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Supplier Code *</label>
                <input type="text" name="code" class="form-control font-monospace" placeholder="SUPP-VENDOR01" required>
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Vendor Company Name *</label>
                <input type="text" name="name" class="form-control" placeholder="Acme Global Tech Ltd" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="orders@vendor.com" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Phone Number *</label>
                <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">GSTIN</label>
                <input type="text" name="gstin" maxlength="15" class="form-control text-uppercase font-monospace" placeholder="07AAAAA0000A1Z5">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Credit Limit (₹)</label>
                <input type="number" step="0.01" name="credit_limit" class="form-control" placeholder="500000.00" value="500000.00">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Payment Terms</label>
                <select name="payment_terms" class="form-select">
                    <option value="Net 30">Net 30 Days</option>
                    <option value="Net 15">Net 15 Days</option>
                    <option value="Immediate">Immediate / Advance</option>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" placeholder="HDFC Bank">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Bank Account Number</label>
                <input type="text" name="bank_account" class="form-control font-monospace" placeholder="50200012345678">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">IFSC Code</label>
                <input type="text" name="bank_ifsc" class="form-control text-uppercase font-monospace" placeholder="HDFC0000123">
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <a href="<?= url('/suppliers') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> Register Supplier
            </button>
        </div>
    </form>
</div>
