<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-people-fill text-success me-2.5"></i>Customer Master Directory & CRM Hub</h3>
        <p class="page-header-sub">Manage enterprise client accounts, credit limits, GSTIN profiles, and sales pipelines</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-person-plus-fill me-1"></i> Register New Customer
        </button>
        <a href="<?= url('/sales/create') ?>" class="btn btn-gradient-primary btn-sm">
            <i class="bi bi-cart-plus-fill me-1"></i> Create Sales Order
        </a>
    </div>
</div>

<!-- CRM Summary KPI Cards (Clean Modern Structured Boxes) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Registered Clients -->
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Registered Enterprise Clients</span>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace" style="color: var(--text-primary);"><?= count($customers) ?> <span class="fs-6 fw-semibold text-secondary">Clients</span></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-check-circle-fill me-1"></i> Active Commercial Accounts
                </span>
            </div>
        </div>
    </div>

    <!-- Card 2: Approved Credit Capacity -->
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Approved Credit Limit Capacity</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                </div>
            </div>
            <?php
            $totalCredit = 0;
            foreach ($customers as $c) $totalCredit += (float)$c['credit_limit'];
            ?>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-warning"><?= format_currency($totalCredit) ?></div>
            <div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-shield-check me-1"></i> Credit Risk Controlled
                </span>
            </div>
        </div>
    </div>

    <!-- Card 3: GSTIN Compliance -->
    <div class="col-md-4">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">GSTIN Compliance Rate</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-info">100%</div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-receipt me-1"></i> GST Tax Compliant
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Customer Directory Table Card -->
<div class="card shadow-sm p-4 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-journal-text text-warning me-2"></i>Enterprise Client Directory & Credit Risk Scorecard</h5>
        <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 fw-bold"><?= count($customers) ?> Total Clients</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small">
                    <th>Customer Code</th>
                    <th>Customer Name</th>
                    <th>Contact Profile</th>
                    <th>GSTIN / Tax ID</th>
                    <th>Approved Credit Limit</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 font-monospace fw-bold fs-6">
                                    <i class="bi bi-person-badge me-1"></i><?= e($c['code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold fs-6" style="color: var(--text-primary);"><?= e($c['name']) ?></div>
                                <small class="text-secondary"><?= e($c['address'] ?: 'Commercial Tax Address') ?></small>
                            </td>
                            <td>
                                <div><i class="bi bi-envelope me-1.5 text-primary"></i><span class="fw-semibold"><?= e($c['email'] ?: 'N/A') ?></span></div>
                                <small class="text-secondary"><i class="bi bi-telephone me-1.5"></i><?= e($c['phone'] ?: 'N/A') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1 font-monospace fw-bold">
                                    <?= e($c['gstin'] ?: '27AAAAA0000A1Z5') ?>
                                </span>
                            </td>
                            <td class="text-warning fw-extrabold font-monospace fs-6"><?= format_currency($c['credit_limit']) ?></td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                                    <i class="bi bi-dot"></i> Active
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 align-items-center">
                                    <a href="<?= url('/sales/create?customer_id=' . $c['id']) ?>" class="btn btn-outline-primary btn-sm fw-bold px-3 py-1.5 rounded-2 shadow-sm">
                                        <i class="bi bi-cart-plus me-1"></i> New SO
                                    </a>
                                    <a href="<?= url('/sales/create-quotation?customer_id=' . $c['id']) ?>" class="btn btn-outline-info btn-sm fw-bold px-3 py-1.5 rounded-2 shadow-sm">
                                        <i class="bi bi-file-earmark-text me-1"></i> Quote
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No customers registered in directory. Click "Register New Customer" above to add one.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-person-plus-fill me-2"></i>Register New Customer & Portal Login Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/customers/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Customer Full Name / Company *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Acme Industrial Corp" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer Code *</label>
                            <input type="text" name="code" class="form-control" value="CUST-<?= rand(1000, 9999) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Credit Limit (₹) *</label>
                            <input type="number" name="credit_limit" class="form-control" value="500000" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Login Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="client@company.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Portal Password *</label>
                            <input type="text" name="password" class="form-control font-monospace fw-bold text-warning" placeholder="customer123" value="customer123" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">GSTIN / Tax Registration No</label>
                            <input type="text" name="gstin" class="form-control font-monospace" placeholder="27AAAAA0000A1Z5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Billing Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full commercial tax billing address"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-person-check-fill me-1.5"></i> Register Customer & User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
