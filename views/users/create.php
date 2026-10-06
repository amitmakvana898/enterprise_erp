<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-person-plus text-primary me-2.5"></i>Create New User Account</h3>
        <p class="page-header-sub">Assign system role, company, branch, and credentials</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4">
    <form action="<?= url('/users/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Full Name *</label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="user@erp.com" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Password *</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Role *</label>
                <select name="role_id" class="form-select" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= e($r['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <a href="<?= url('/users') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> Create User Account
            </button>
        </div>
    </form>
</div>
