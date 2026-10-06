<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="font-heading fw-extrabold mb-0">
                <i class="bi bi-shield-fill-check text-primary me-2"></i>Super Admin Master Control Center
            </h2>
            <span class="badge bg-primary bg-opacity-20 text-info border border-info border-opacity-30 rounded-pill px-3 py-1 font-monospace">
                <i class="bi bi-cpu-fill me-1"></i> SYSTEM CONSOLE v3.0
            </span>
        </div>
        <p class="text-secondary small mb-0">System maintenance, database backup triggers, server diagnostic health, and multi-tenant environment controls</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3.5 py-2">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Dashboard
        </a>
        <form action="<?= url('/super-admin/backup') ?>" method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <button type="submit" class="btn btn-gradient-primary btn-sm fw-bold shadow-lg rounded-pill px-4 py-2" style="background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);">
                <i class="bi bi-database-fill-down me-1.5 text-warning"></i> Create Database Backup
            </button>
        </form>
        <form action="<?= url('/super-admin/clear-cache') ?>" method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <button type="submit" class="btn btn-warning btn-sm fw-bold rounded-pill px-3.5 py-2">
                <i class="bi bi-arrow-repeat me-1.5"></i> Flush System Cache
            </button>
        </form>
    </div>
</div>

<!-- System KPI Stats Grid -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">Companies</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(59,130,246,0.12);width:34px;height:34px;">
                    <i class="bi bi-building-fill text-primary fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['companies'] ?></div>
            <div class="small text-primary mt-1" style="font-size:0.75rem;">Enterprise Entities</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">Branches</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(16,185,129,0.12);width:34px;height:34px;">
                    <i class="bi bi-diagram-3-fill text-success fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['branches'] ?></div>
            <div class="small text-success mt-1" style="font-size:0.75rem;">Operating Hubs</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">Warehouses</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(245,158,11,0.12);width:34px;height:34px;">
                    <i class="bi bi-houses-fill text-warning fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['warehouses'] ?></div>
            <div class="small text-warning mt-1" style="font-size:0.75rem;">Bin Facilities</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">System Users</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(14,165,233,0.12);width:34px;height:34px;">
                    <i class="bi bi-people-fill text-info fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['users'] ?></div>
            <div class="small text-info mt-1" style="font-size:0.75rem;">Active Accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">RBAC Roles</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(239,68,68,0.12);width:34px;height:34px;">
                    <i class="bi bi-shield-lock-fill text-danger fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['roles'] ?></div>
            <div class="small text-danger mt-1" style="font-size:0.75rem;">Security Profiles</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-surface);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fw-extrabold small text-uppercase">Audit Logs</span>
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background:rgba(139,92,246,0.12);width:34px;height:34px;">
                    <i class="bi bi-journal-text text-purple fs-6"></i>
                </div>
            </div>
            <div class="font-heading fw-extrabold fs-3"><?= $counts['audits'] ?></div>
            <div class="small text-purple mt-1" style="font-size:0.75rem;">Recorded Trails</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Server & Database Environment Health -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-cpu-fill text-info me-2"></i>Server & Database Environment Health</h5>
                <span class="badge bg-success bg-opacity-20 text-success rounded-pill px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i> Operational
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.9rem;">
                    <tbody>
                        <tr>
                            <td class="text-secondary fw-semibold py-3" style="width:45%;">
                                <i class="bi bi-code-square me-2 text-info"></i>PHP Engine Version
                            </td>
                            <td class="py-3">
                                <span class="badge bg-success fw-bold fs-6"><?= $sys_info['php_version'] ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-semibold py-3">
                                <i class="bi bi-database-fill me-2 text-warning"></i>MySQL Database Size
                            </td>
                            <td class="py-3">
                                <span class="badge bg-warning fw-bold fs-6"><?= $sys_info['database_size_mb'] ?></span>
                                <span class="small text-secondary ms-1">(<?= $sys_info['tables_count'] ?> Tables 3NF)</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-semibold py-3">
                                <i class="bi bi-hdd-network-fill me-2 text-primary"></i>Server Environment
                            </td>
                            <td class="py-3 font-monospace small">
                                <?= e($sys_info['server_software']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-semibold py-3">
                                <i class="bi bi-speedometer2 me-2 text-danger"></i>Memory Limit / Max Exec
                            </td>
                            <td class="py-3">
                                <span class="badge bg-primary me-1"><?= $sys_info['memory_limit'] ?></span>
                                <span class="badge bg-secondary"><?= $sys_info['max_execution_time'] ?>s</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-semibold py-3">
                                <i class="bi bi-display-fill me-2 text-purple"></i>Operating System
                            </td>
                            <td class="py-3 fw-semibold">
                                <?= $sys_info['os'] ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary fw-semibold py-3">
                                <i class="bi bi-shield-check me-2 text-success"></i>DB Driver & Connection
                            </td>
                            <td class="py-3">
                                <span class="text-success fw-bold"><i class="bi bi-circle-fill me-1" style="font-size:8px;"></i> PDO MySQL Connected</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Database Backups List -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-archive-fill text-warning me-2"></i>Database Backups & Snapshots</h5>
                <span class="badge bg-secondary rounded-pill px-3 py-1"><?= count($backups) ?> Snapshots Available</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                    <thead>
                        <tr class="text-secondary">
                            <th>FILENAME</th>
                            <th>FILE SIZE</th>
                            <th>CREATED DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($backups)): ?>
                            <?php foreach ($backups as $b): ?>
                                <tr>
                                    <td class="fw-bold text-warning font-monospace py-3">
                                        <i class="bi bi-file-earmark-zip-fill text-warning me-2 fs-6"></i><?= e($b['name']) ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-secondary"><?= $b['size'] ?></span>
                                    </td>
                                    <td class="text-secondary py-3">
                                        <i class="bi bi-clock me-1"></i><?= $b['created_at'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-secondary">
                                    <i class="bi bi-inbox fs-1 opacity-50 mb-2 d-block text-warning"></i>
                                    No database backup snapshots found.<br>Click <strong class="text-primary">Create Database Backup</strong> to generate a new snapshot.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
