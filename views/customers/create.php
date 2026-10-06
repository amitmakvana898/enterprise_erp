<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-person-plus-fill me-2.5 text-success"></i>Register Enterprise Customer</h3>
        <p class="page-header-sub">Add a new commercial client to the ERP customer master with credit limit & GSTIN profile</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/customers') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Customer Directory
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4 col-lg-8 mx-auto">
    <form action="<?= url('/customers/store') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Company / Customer Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Apex Global Industries" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Customer Code</label>
                <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. CUST-1001 (auto-generated if blank)">
            </div>
            
            <div class="col-md-6">
                <label class="form-label small fw-bold">Official Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="procurement@client.com" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Primary Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-bold">GSTIN / Tax ID</label>
                <input type="text" name="gstin" maxlength="15" class="form-control text-uppercase font-monospace" placeholder="24AAACC1206D1ZM" style="letter-spacing:0.05em;">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Credit Limit (₹)</label>
                <input type="number" step="0.01" name="credit_limit" class="form-control" value="100000.00">
            </div>

            <div class="col-md-12">
                <label class="form-label small fw-bold">Portal Access Password</label>
                <input type="password" name="password" class="form-control" placeholder="Default: customer123 (if left blank)">
                <small class="text-muted">Used for B2B Customer Portal login (Default: customer123)</small>
            </div>

            <div class="col-12">
                <label class="form-label small fw-bold">Billing & Shipping Address</label>
                <textarea name="address" rows="3" class="form-control" placeholder="Full commercial registered address..."></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="<?= url('/customers') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-success btn-sm fw-bold px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Customer Profile
            </button>
        </div>
    </form>
</div>
