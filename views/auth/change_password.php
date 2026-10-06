<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-key-fill text-primary me-2"></i>Change Account Password</h3>
        <p class="text-muted small mb-0">Update your account security credentials and master login password</p>
    </div>
    <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
</div>

<div class="card p-4 shadow-sm" style="max-width: 500px;">
    <form action="<?= url('/change-password/store') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="mb-3">
            <label class="form-label small fw-semibold">Current Password *</label>
            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold">New Password *</label>
            <input type="password" name="new_password" class="form-control" placeholder="Enter new strong password" required>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm"><i class="bi bi-shield-check me-1"></i> Update Password</button>
            <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary px-4 fw-bold">Cancel</a>
        </div>
    </form>
</div>
