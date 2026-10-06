<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="fw-extrabold rbac-header-title mb-1"><i class="bi bi-shield-lock-fill text-danger me-2.5"></i>Role-Based Access Control (RBAC) & Security Manager</h3>
        <p class="rbac-subtext small mb-0">Configure granular operational scopes, module authorizations, and security access levels per role</p>
    </div>
    
    <!-- Master Actions -->
    <div class="d-flex gap-2">
        <form action="<?= url('/roles/grant-all') ?>" method="POST" onsubmit="return confirm('GRANT ALL PERMISSIONS across ALL system roles? This unlocks unrestricted access.');">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="role_id" value="all">
            <button type="submit" class="btn btn-warning text-dark btn-sm fw-bold px-3 shadow-sm">
                <i class="bi bi-shield-check me-1.5"></i> Unlock All System Roles
            </button>
        </form>
    </div>
</div>

<!-- Toast Alert Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="rbacToast" class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold text-white" id="rbacToastMsg">
                <i class="bi bi-check-circle-fill me-2"></i> Permission updated.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<?php
    $totalPermissionsCount = count($permissions);
    
    // Group permissions by module cleanly
    $groupedPerms = [];
    foreach ($permissions as $p) {
        $mod = ucfirst($p['module']);
        $groupedPerms[$mod][] = $p;
    }

    $moduleIcons = [
        'Products' => 'bi-box-seam-fill text-info',
        'Procurement' => 'bi-bag-check-fill text-warning',
        'Inventory' => 'bi-layers-fill text-danger',
        'Sales' => 'bi-cart-check-fill text-success',
        'Finance' => 'bi-bank2 text-primary',
        'Suppliers' => 'bi-building-fill-gear text-secondary',
        'Reports' => 'bi-bar-chart-line-fill text-info',
        'Users' => 'bi-people-fill text-primary',
        'Roles' => 'bi-shield-lock-fill text-danger',
        'Organization' => 'bi-diagram-3-fill text-info',
        'Audit' => 'bi-journal-text text-warning'
    ];

    $roleIcons = [
        'super_admin' => 'bi-shield-fill-check text-danger',
        'company_admin' => 'bi-building-fill text-primary',
        'procurement_manager' => 'bi-bag-check-fill text-warning',
        'warehouse_manager' => 'bi-box-seam-fill text-info',
        'sales_manager' => 'bi-cart-check-fill text-success',
        'finance_manager' => 'bi-bank2 text-primary',
        'dept_manager' => 'bi-diagram-3-fill text-secondary',
        'qc_inspector' => 'bi-patch-check-fill text-info'
    ];
?>

<!-- View Switcher & Live Search Bar -->
<div class="card rbac-card border-0 shadow-sm p-3 mb-4 rounded-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- View Mode Tabs -->
        <ul class="nav nav-pills custom-role-pills" id="rbacViewTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-4 py-2" id="focused-mode-tab" data-bs-toggle="tab" data-bs-target="#focused-mode" type="button" role="tab">
                    <i class="bi bi-person-gear me-2 text-warning"></i>Role-by-Role Focused Manager
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2" id="matrix-mode-tab" data-bs-toggle="tab" data-bs-target="#matrix-mode" type="button" role="tab">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-info"></i>Full System Comparison Matrix
                </button>
            </li>
        </ul>

        <!-- Search Bar -->
        <div class="input-group input-group-sm" style="max-width: 320px;">
            <span class="input-group-text bg-body-tertiary border text-secondary"><i class="bi bi-search"></i></span>
            <input type="text" id="rbacSearchInput" class="form-control border fw-semibold" placeholder="Search scope or keyword...">
        </div>
    </div>
</div>

<div class="tab-content" id="rbacTabContent">
    <!-- 🎯 MODE 1: ROLE-BY-ROLE FOCUSED MANAGER -->
    <div class="tab-pane fade show active" id="focused-mode" role="tabpanel">
        <!-- Role Selector Cards Carousel / Grid -->
        <div class="row g-3 mb-4">
            <?php foreach ($roles as $idx => $r): ?>
                <?php 
                    $isSuperAdmin = ($r['name'] === 'super_admin');
                    $userCount = (int)($r['user_count'] ?? 0);
                    $iconClass = $roleIcons[$r['name']] ?? 'bi-person-badge-fill text-primary';
                    
                    // Count granted permissions for this role
                    $grantedCount = 0;
                    if ($isSuperAdmin) {
                        $grantedCount = $totalPermissionsCount;
                    } else {
                        foreach ($permissions as $p) {
                            if (isset($matrix[$r['id']][$p['code']])) {
                                $grantedCount++;
                            }
                        }
                    }
                    $percent = $totalPermissionsCount > 0 ? round(($grantedCount / $totalPermissionsCount) * 100) : 0;
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 role-card-col">
                    <div class="card rbac-card role-select-card p-3 h-100 <?= $idx === 0 ? 'active-role-card' : '' ?>" 
                         onclick="selectActiveRole(<?= $r['id'] ?>, this)" 
                         id="roleCard_<?= $r['id'] ?>" 
                         data-role-search="<?= e(strtolower($r['display_name'] . ' ' . $r['name'] . ' ' . $r['description'])) ?>"
                         style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi <?= $iconClass ?> fs-4"></i>
                                <div>
                                    <h6 class="fw-bold rbac-heading mb-0"><?= e($r['display_name']) ?></h6>
                                    <small class="text-primary font-monospace" style="font-size:0.75rem;"><?= e($r['name']) ?></small>
                                </div>
                            </div>
                            <span class="badge bg-body-tertiary border text-secondary px-2.5 py-1" title="Assigned Users">
                                <i class="bi bi-people-fill me-1 text-primary"></i><?= $userCount ?>
                            </span>
                        </div>
                        <p class="small rbac-subtext mb-2 text-truncate" title="<?= e($r['description']) ?>"><?= e($r['description']) ?></p>
                        
                        <div class="mt-auto pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="rbac-subtext">Permissions:</span>
                                <span class="fw-bold text-success" id="roleGrantedLabel_<?= $r['id'] ?>"><?= $grantedCount ?> / <?= $totalPermissionsCount ?> (<?= $percent ?>%)</span>
                            </div>
                            <div class="progress bg-body-tertiary border" style="height:6px;">
                                <div class="progress-bar bg-<?= $isSuperAdmin ? 'danger' : 'success' ?>" role="progressbar" id="roleProgressBar_<?= $r['id'] ?>" style="width: <?= $percent ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Focused Role Permission Panel -->
        <?php foreach ($roles as $idx => $r): ?>
            <?php $isSuperAdmin = ($r['name'] === 'super_admin'); ?>
            <div class="role-permission-panel d-none" id="rolePanel_<?= $r['id'] ?>">
                <!-- Role Detail Banner -->
                <div class="card rbac-banner-card border-0 shadow-sm p-4 mb-4 rounded-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 rounded-circle border rbac-icon-container">
                                <i class="bi <?= $roleIcons[$r['name']] ?? 'bi-person-badge-fill' ?> fs-2 text-warning"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h4 class="fw-extrabold rbac-heading mb-0"><?= e($r['display_name']) ?> Permissions</h4>
                                    <span class="badge bg-primary text-uppercase font-monospace text-white"><?= e($r['name']) ?></span>
                                </div>
                                <p class="small rbac-subtext mb-0 mt-1"><?= e($r['description']) ?></p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($isSuperAdmin): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fw-bold fs-6"><i class="bi bi-shield-check me-1.5"></i> Super Admin (Unrestricted System Access)</span>
                            <?php else: ?>
                                <form action="<?= url('/roles/grant-all') ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <input type="hidden" name="role_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-outline-success btn-sm fw-bold px-3">
                                        <i class="bi bi-check-all me-1"></i> Grant All Scopes
                                    </button>
                                </form>
                                <form action="<?= url('/roles/revoke-all') ?>" method="POST" class="d-inline" onsubmit="return confirm('Revoke all permissions for <?= e(addslashes($r['display_name'])) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                    <input type="hidden" name="role_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm fw-bold px-3">
                                        <i class="bi bi-slash-circle me-1"></i> Revoke All Scopes
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Module Accordions / Grid Cards -->
                <div class="row g-4">
                    <?php foreach ($groupedPerms as $moduleName => $pList): ?>
                        <?php $iconClass = $moduleIcons[$moduleName] ?? 'bi-gear-fill text-secondary'; ?>
                        <div class="col-12 col-lg-6 module-scope-card">
                            <div class="card rbac-card border-0 shadow-sm p-4 h-100 rounded-3">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h6 class="fw-bold rbac-heading mb-0">
                                        <i class="bi <?= $iconClass ?> me-2"></i><?= e($moduleName) ?> Module Scopes
                                    </h6>
                                    <span class="badge bg-body-tertiary border text-primary font-monospace fw-bold px-2.5 py-1"><?= count($pList) ?> Scopes</span>
                                </div>

                                <div class="d-flex flex-column gap-2.5">
                                    <?php foreach ($pList as $p): ?>
                                        <?php 
                                            $isGranted = isset($matrix[$r['id']][$p['code']]) || $isSuperAdmin;
                                        ?>
                                        <div class="p-3 rounded-3 rbac-perm-row d-flex align-items-center justify-content-between" data-scope="<?= e(strtolower($p['code'] . ' ' . $p['description'])) ?>">
                                            <div>
                                                <div class="fw-bold font-monospace rbac-scope-code mb-0.5" style="font-size:0.92rem;">
                                                    <i class="bi bi-key-fill me-1.5" style="color:#D97706;"></i><?= e($p['code']) ?>
                                                </div>
                                                <small class="d-block rbac-subtext" style="font-size:0.82rem;"><?= e($p['description'] ?? $p['code']) ?></small>
                                            </div>

                                            <div>
                                                <?php if ($isSuperAdmin): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-1.5 fw-bold"><i class="bi bi-shield-check me-1"></i> Full Access</span>
                                                <?php else: ?>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input role-switch-input" 
                                                               type="checkbox" 
                                                               role="switch" 
                                                               data-role-id="<?= $r['id'] ?>"
                                                               data-perm-id="<?= $p['id'] ?>"
                                                               style="width: 2.8em; height: 1.4em; cursor: pointer;"
                                                               <?= $isGranted ? 'checked' : '' ?>
                                                               onchange="togglePermissionAjax(<?= $r['id'] ?>, <?= $p['id'] ?>, this)">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 📊 MODE 2: FULL SYSTEM COMPARISON MATRIX -->
    <div class="tab-pane fade" id="matrix-mode" role="tabpanel">
        <div class="card rbac-card border-0 shadow-sm p-4 mb-4 rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold rbac-heading mb-0"><i class="bi bi-table text-primary me-2"></i>Global Enterprise Access Comparison Matrix</h5>
                <span class="badge bg-body-tertiary border text-primary font-monospace fw-bold">All Roles Side-by-Side</span>
            </div>

            <?php foreach ($groupedPerms as $moduleName => $pList): ?>
                <?php $iconClass = $moduleIcons[$moduleName] ?? 'bi-gear-fill text-secondary'; ?>
                <div class="card rbac-card border mb-4 overflow-hidden rounded-3">
                    <div class="card-header border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold rbac-heading">
                            <i class="bi <?= $iconClass ?> me-2"></i><?= e($moduleName) ?> Module Scopes
                        </span>
                        <span class="badge bg-body-tertiary border text-secondary font-monospace"><?= count($pList) ?> Permissions</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table rbac-matrix-table table-hover align-middle mb-0 small">
                            <thead>
                                <tr>
                                    <th style="min-width:180px;">Permission Scope</th>
                                    <th style="min-width:200px;">Description</th>
                                    <?php foreach ($roles as $r): ?>
                                        <th class="text-center" style="min-width:120px;">
                                            <div class="fw-bold rbac-heading"><?= e($r['display_name']) ?></div>
                                            <small class="text-primary font-monospace">(<?= e($r['name']) ?>)</small>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pList as $p): ?>
                                    <tr class="perm-item-row-table" data-scope="<?= e(strtolower($p['code'] . ' ' . $p['description'])) ?>">
                                        <td><div class="fw-bold font-monospace" style="color:#D97706;"><i class="bi bi-key me-1"></i><?= e($p['code']) ?></div></td>
                                        <td class="rbac-subtext"><?= e($p['description'] ?? $p['code']) ?></td>
                                        <?php foreach ($roles as $r): ?>
                                            <?php 
                                                $isSuperAdmin = ($r['name'] === 'super_admin');
                                                $isGranted = isset($matrix[$r['id']][$p['code']]) || $isSuperAdmin;
                                            ?>
                                            <td class="text-center">
                                                <?php if ($isSuperAdmin): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1"><i class="bi bi-shield-check me-1"></i> Locked</span>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-link p-0 text-decoration-none matrix-toggle-btn" 
                                                            data-role-id="<?= $r['id'] ?>" 
                                                            data-perm-id="<?= $p['id'] ?>" 
                                                            onclick="togglePermissionAjax(<?= $r['id'] ?>, <?= $p['id'] ?>, this)">
                                                        <?php if ($isGranted): ?>
                                                            <span class="badge bg-success px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Granted</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-body-tertiary text-secondary border px-2.5 py-1"><i class="bi bi-dash-circle me-1"></i> Restricted</span>
                                                        <?php endif; ?>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* ─── DUAL THEME ADAPTIVE STYLING (LIGHT & DARK MODE COMPATIBLE) ─── */

/* 1. LIGHT MODE DEFAULTS */
.rbac-header-title { color: #0F172A !important; }
.rbac-heading { color: #0F172A !important; }
.rbac-scope-code { color: #1E293B !important; }
.rbac-subtext { color: #64748B !important; }

.rbac-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    color: #0F172A !important;
}
.rbac-banner-card {
    background: #F8FAFC !important;
    border: 1px solid #E2E8F0 !important;
}
.rbac-icon-container {
    background: #EFF6FF !important;
    border-color: #BFDBFE !important;
}

.rbac-perm-row {
    background: #F8FAFC !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 10px;
    transition: all 0.2s ease-in-out;
}
.rbac-perm-row:hover {
    border-color: #2563EB !important;
    background: #FFFFFF !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
}

.role-select-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease-in-out;
}
.role-select-card:hover {
    border-color: #2563EB !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
}
.active-role-card {
    border-color: #2563EB !important;
    background: #EFF6FF !important;
    box-shadow: 0 0 15px rgba(37, 99, 235, 0.2) !important;
}

.custom-role-pills .nav-link {
    color: #64748B !important;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.custom-role-pills .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    border-color: #2563EB !important;
}

.rbac-matrix-table {
    color: #0F172A !important;
    background-color: #FFFFFF !important;
}
.rbac-matrix-table th {
    background-color: #F1F5F9 !important;
    color: #334155 !important;
}

/* 2. DARK MODE OVERRIDES ([data-theme="dark"]) */
[data-theme="dark"] .rbac-header-title { color: #F8FAFC !important; }
[data-theme="dark"] .rbac-heading { color: #F8FAFC !important; }
[data-theme="dark"] .rbac-scope-code { color: #F8FAFC !important; }
[data-theme="dark"] .rbac-subtext { color: #94A3B8 !important; }

[data-theme="dark"] .rbac-card {
    background: #1E293B !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    color: #F8FAFC !important;
}
[data-theme="dark"] .rbac-banner-card {
    background: #0F172A !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
}
[data-theme="dark"] .rbac-icon-container {
    background: #1E293B !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}

[data-theme="dark"] .rbac-perm-row {
    background: #0F172A !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
[data-theme="dark"] .rbac-perm-row:hover {
    border-color: #38BDF8 !important;
    background: #1E293B !important;
    box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15);
}

[data-theme="dark"] .role-select-card {
    background: #1E293B !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}
[data-theme="dark"] .role-select-card:hover {
    border-color: #38BDF8 !important;
    background: #26334D !important;
}
[data-theme="dark"] .role-select-card.active-role-card {
    border-color: #38BDF8 !important;
    background: linear-gradient(145deg, #1E3A8A 0%, #0F172A 100%) !important;
    box-shadow: 0 0 20px rgba(56, 189, 248, 0.3) !important;
}

[data-theme="dark"] .rbac-matrix-table {
    color: #F8FAFC !important;
    background-color: #1E293B !important;
}
[data-theme="dark"] .rbac-matrix-table th {
    background-color: #0F172A !important;
    color: #94A3B8 !important;
}
</style>

<script>
let currentActiveRoleId = null;

function selectActiveRole(roleId, cardElement) {
    currentActiveRoleId = roleId;

    // Update active card styling
    document.querySelectorAll('.role-select-card').forEach(c => c.classList.remove('active-role-card'));
    if (cardElement) {
        cardElement.classList.add('active-role-card');
    }

    // Show panel
    document.querySelectorAll('.role-permission-panel').forEach(p => p.classList.add('d-none'));
    const targetPanel = document.getElementById('rolePanel_' + roleId);
    if (targetPanel) {
        targetPanel.classList.remove('d-none');
    }
}

function togglePermissionAjax(roleId, permissionId, element) {
    const formData = new FormData();
    formData.append('role_id', roleId);
    formData.append('permission_id', permissionId);

    fetch('<?= url('/roles/toggle-permission') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const isNowGranted = data.granted;

            // 1. Live update Matrix View buttons
            const matrixBtns = document.querySelectorAll(`.matrix-toggle-btn[data-role-id="${roleId}"][data-perm-id="${permissionId}"]`);
            matrixBtns.forEach(btn => {
                if (isNowGranted) {
                    btn.innerHTML = `<span class="badge bg-success px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Granted</span>`;
                } else {
                    btn.innerHTML = `<span class="badge bg-body-tertiary text-secondary border px-2.5 py-1"><i class="bi bi-dash-circle me-1"></i> Restricted</span>`;
                }
            });

            // 2. Live update Focused View switches
            const focusedSwitches = document.querySelectorAll(`.role-switch-input[data-role-id="${roleId}"][data-perm-id="${permissionId}"]`);
            focusedSwitches.forEach(sw => {
                sw.checked = isNowGranted;
            });

            showToast(data.message, 'success');
        } else {
            if (element.type === 'checkbox') {
                element.checked = !element.checked;
            }
            showToast(data.message || 'Failed to update permission', 'danger');
        }
    })
    .catch(err => {
        if (element.type === 'checkbox') {
            element.checked = !element.checked;
        }
        showToast('Network error while updating permission', 'danger');
    });
}

function showToast(message, type) {
    const toastEl = document.getElementById('rbacToast');
    const toastMsg = document.getElementById('rbacToastMsg');
    toastEl.className = 'toast align-items-center border-0 shadow-lg text-bg-' + type;
    toastMsg.innerHTML = (type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : '<i class="bi bi-exclamation-triangle-fill me-2"></i>') + message;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Live Search Engine for Roles & Permission Scopes
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('rbacSearchInput');

    function performRbacSearch() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();

        // 1. Filter Role Selector Cards at top
        const roleCols = document.querySelectorAll('.role-card-col');
        roleCols.forEach(col => {
            const card = col.querySelector('.role-select-card');
            const searchData = card ? (card.dataset.roleSearch || card.textContent.toLowerCase()) : '';
            if (query === '' || searchData.includes(query)) {
                col.style.display = '';
            } else {
                col.style.display = 'none';
            }
        });

        // 2. Filter Permission Items in Active Panel (Focused View)
        const activePanel = document.querySelector('.role-permission-panel:not(.d-none)');
        if (activePanel) {
            const moduleCards = activePanel.querySelectorAll('.module-scope-card');
            moduleCards.forEach(mc => {
                const moduleHeader = mc.querySelector('h6');
                const moduleTitle = moduleHeader ? moduleHeader.textContent.toLowerCase() : '';
                const permRows = mc.querySelectorAll('.rbac-perm-row');
                let matchCount = 0;

                permRows.forEach(r => {
                    const scopeText = (r.dataset.scope || r.textContent).toLowerCase();
                    if (query === '' || scopeText.includes(query) || moduleTitle.includes(query)) {
                        r.style.display = 'flex';
                        matchCount++;
                    } else {
                        r.style.display = 'none';
                    }
                });

                if (query !== '' && matchCount === 0) {
                    mc.style.display = 'none';
                } else {
                    mc.style.display = '';
                }
            });
        }

        // 3. Filter Comparison Matrix View
        const matrixTab = document.getElementById('matrix-mode');
        if (matrixTab) {
            const matrixModules = matrixTab.querySelectorAll('.card.overflow-hidden');
            matrixModules.forEach(mm => {
                const header = mm.querySelector('.card-header');
                const moduleTitle = header ? header.textContent.toLowerCase() : '';
                const permRows = mm.querySelectorAll('.perm-item-row-table');
                let matchCount = 0;

                permRows.forEach(r => {
                    const scopeText = (r.dataset.scope || r.textContent).toLowerCase();
                    if (query === '' || scopeText.includes(query) || moduleTitle.includes(query)) {
                        r.style.display = '';
                        matchCount++;
                    } else {
                        r.style.display = 'none';
                    }
                });

                if (query !== '' && matchCount === 0) {
                    mm.style.display = 'none';
                } else {
                    mm.style.display = '';
                }
            });
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', performRbacSearch);
    }

    // Wrap selectActiveRole to re-run search filter on active panel when switching role cards
    const origSelectActiveRole = window.selectActiveRole;
    window.selectActiveRole = function(roleId, cardElement) {
        if (typeof origSelectActiveRole === 'function') {
            origSelectActiveRole(roleId, cardElement);
        }
        performRbacSearch();
    };

    // Select first role by default
    const firstCard = document.querySelector('.role-select-card');
    if (firstCard) {
        firstCard.click();
    }
});
</script>
