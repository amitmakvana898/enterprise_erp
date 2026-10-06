<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-people-fill text-primary me-2.5"></i>User Account Management & Control</h3>
        <p class="page-header-sub">System user credentials, enterprise role scopes, account blocking & permanent user deletion</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/users/create') ?>" class="btn btn-gradient-primary btn-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Add User Account
        </a>
    </div>
</div>

<div class="card p-0 overflow-hidden shadow-sm border">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr class="text-secondary text-nowrap">
                    <th>User & Work Email</th>
                    <th>Assigned Enterprise Role</th>
                    <th>Company & Branch</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-end">Admin User Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <?php 
                            $isSelf = ((int)auth_user()['id'] === (int)$u['id']);
                            $isActive = ($u['status'] === 'active');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold fs-6"><?= e($u['name']) ?> <?= $isSelf ? '<span class="badge bg-primary ms-1 small">YOU</span>' : '' ?></div>
                                <small class="text-primary font-monospace"><?= e($u['email']) ?></small>
                            </td>
                            <td><span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-2.5 py-1.5 fs-6"><?= e($u['role_display']) ?></span></td>
                            <td>
                                <div class="fw-semibold"><?= e($u['company_name'] ?? 'Apex Corp') ?></div>
                                <small class="text-secondary"><?= e($u['branch_name'] ?? 'Delhi HQ') ?></small>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1"><i class="bi bi-slash-circle me-1"></i> Blocked</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-secondary"><?= e($u['last_login_at'] ?? 'Never') ?></td>
                            <td class="text-end">
                                <?php if (!$isSelf): ?>
                                    <div class="d-flex justify-content-end gap-2">
                                        <!-- Block / Unblock Button -->
                                        <form action="<?= url('/users/toggle-status/' . $u['id']) ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                            <?php if ($isActive): ?>
                                                <button type="submit" class="btn btn-outline-warning btn-sm rounded-2 fw-semibold px-2.5" title="Block User Access">
                                                    <i class="bi bi-slash-circle me-1"></i> Block
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm rounded-2 fw-semibold px-2.5" title="Unblock User Access">
                                                    <i class="bi bi-check-circle me-1"></i> Unblock
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Delete Button -->
                                        <form action="<?= url('/users/delete/' . $u['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete user account \'<?= e(addslashes($u['name'])) ?>\'? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-2 fw-semibold px-2.5" title="Delete User Permanently">
                                                <i class="bi bi-trash3-fill me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Current Active Session</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
