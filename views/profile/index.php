<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-person-badge-fill text-primary me-2.5"></i>My Account Profile & Settings</h3>
        <p class="page-header-sub">Manage your personal credentials, contact details, security password, and enterprise workspace assignment</p>
    </div>
    <div class="page-header-actions d-flex gap-2 align-items-center">
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
        <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1.5"><i class="bi bi-shield-check me-1"></i> Verified Account</span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: User Summary Card -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm border text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fs-1 fw-bold mx-auto mb-3 shadow-sm" style="width: 85px; height: 85px;">
                <?= strtoupper(substr($profile['name'] ?? 'U', 0, 1)) ?>
            </div>
            <h4 class="fw-extrabold mb-1" style="color:var(--text-primary);"><?= e($profile['name']) ?></h4>
            <p class="text-primary small fw-semibold mb-2"><?= e($profile['email']) ?></p>
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-1.5 rounded-pill mb-3">
                <?= e($profile['role_display'] ?? 'User') ?>
            </span>

            <hr class="my-3">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Company:</span>
                    <span class="fw-semibold" style="color:var(--text-primary);"><?= e($profile['company_name'] ?? 'Apex Corp') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Assigned Branch:</span>
                    <span class="fw-semibold" style="color:var(--text-primary);"><?= e($profile['branch_name'] ?? 'HQ Branch') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Account Status:</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                </div>
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Email Verification:</span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle">Verified</span>
                </div>
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Member Since:</span>
                    <span class="fw-semibold" style="color:var(--text-primary);"><?= date('M d, Y', strtotime($profile['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1.5">
                    <span class="text-muted">Last Login:</span>
                    <span class="fw-semibold" style="color:var(--text-primary);"><?= e($profile['last_login_at'] ?? 'Just Now') ?></span>
                </div>
            </div>
        </div>

        <!-- Personal Activity Audit History -->
        <div class="card p-4 shadow-sm border">
            <h6 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-clock-history text-warning me-2"></i>Recent Personal Activity</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <tbody>
                        <?php if (!empty($user_logs)): ?>
                            <?php foreach ($user_logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-info"><?= e($log['action']) ?></div>
                                        <small class="text-muted"><?= e($log['created_at']) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td class="text-muted py-2">No recent audit log history.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Account Details & Security Forms -->
    <div class="col-lg-8">
        <!-- Form 1: Edit Personal Details -->
        <div class="card p-4 shadow-sm border mb-4">
            <h5 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-person-lines-fill text-info me-2"></i>Edit Personal Information</h5>
            <form action="<?= url('/profile/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= e($profile['name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Work Email Address (Read-only)</label>
                        <input type="email" class="form-control bg-body-tertiary" value="<?= e($profile['email']) ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Contact Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>" placeholder="+91 9876543210">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Assigned Enterprise Role</label>
                        <input type="text" class="form-control bg-body-tertiary" value="<?= e($profile['role_display'] ?? 'User') ?>" readonly disabled>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-gradient-primary btn-sm fw-bold shadow-sm px-4 py-2">
                        <i class="bi bi-floppy-fill me-1.5"></i> Save Profile Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Change Password Security -->
        <div class="card p-4 shadow-sm border">
            <h5 class="fw-bold mb-3" style="color:var(--text-primary);"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Account Security & Password</h5>
            <form action="<?= url('/profile/change-password') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Current Password *</label>
                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">New Password *</label>
                        <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Confirm New Password *</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-outline-warning btn-sm fw-bold px-4 py-2">
                        <i class="bi bi-key-fill me-1.5"></i> Update Account Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
