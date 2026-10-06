<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-eye-fill text-info me-2"></i>System Audit Trail & Security Log</h3>
        <p class="text-secondary small mb-0">Immutable records of user actions, module operations, IP addresses, and JSON diffs</p>
    </div>
</div>

<div class="card shadow-sm p-4 border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr class="text-secondary">
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>New Values Snapshot</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-secondary font-monospace">#<?= $log['id'] ?></td>
                            <td class="text-secondary"><?= e($log['created_at']) ?></td>
                            <td class="fw-semibold"><?= e($log['user_name']) ?></td>
                            <td><span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30"><?= e($log['module']) ?></span></td>
                            <td class="fw-bold text-info"><?= e($log['action']) ?></td>
                            <td class="text-secondary font-monospace"><?= e($log['ip_address']) ?></td>
                            <td><code class="text-warning small"><?= e(substr($log['new_values'] ?? '', 0, 50)) ?>...</code></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No audit logs recorded.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
