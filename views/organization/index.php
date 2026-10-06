<?php
$activeTab = $_GET['tab'] ?? 'warehouses'; // 'warehouses' or 'companies'
?>

<!-- Page Header & Action Controls -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <?php if ($activeTab === 'companies'): ?>
                <i class="bi bi-building-fill text-primary me-2"></i>Enterprise Companies & Operating Branches
            <?php else: ?>
                <i class="bi bi-houses-fill text-warning me-2"></i>Warehouse Facilities & Bin Storage Hierarchy
            <?php endif; ?>
        </h3>
        <p class="text-secondary small mb-0">Traceability structure: Company ➔ Branch ➔ Warehouse ➔ Rack ➔ Bin Location</p>
    </div>

    <!-- Navigation View Switcher (Tabs) -->
    <div class="d-flex align-items-center gap-2">
        <div class="btn-group p-1 rounded-3 border bg-body-tertiary" role="group">
            <a href="<?= url('/organization?tab=warehouses') ?>" class="btn btn-sm <?= $activeTab !== 'companies' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary border-0' ?> px-3.5 py-2 rounded-2">
                <i class="bi bi-houses-fill me-1.5"></i> Warehouse Facilities
            </a>
            <a href="<?= url('/organization?tab=companies') ?>" class="btn btn-sm <?= $activeTab === 'companies' ? 'btn-primary fw-bold' : 'btn-outline-secondary border-0' ?> px-3.5 py-2 rounded-2">
                <i class="bi bi-building-fill me-1.5"></i> Companies & Branches
            </a>
        </div>

        <?php if (has_permission('organization.manage') || has_permission('organization.create_company') || has_permission('organization.create_branch') || has_permission('organization.create_warehouse') || has_permission('organization.create_rack') || has_permission('organization.create_bin')): ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary fw-bold dropdown-toggle px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-1.5" type="button" id="orgDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-plus-circle-fill text-info me-1"></i> Quick Setup
            </button>
            <ul class="dropdown-menu shadow-lg p-2" aria-labelledby="orgDropdownBtn" style="min-width: 240px;">
                <?php if (has_permission('organization.manage') || has_permission('organization.create_warehouse')): ?>
                <li><a class="dropdown-item py-2 fw-semibold rounded-2 d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addWarehouseModal"><i class="bi bi-houses-fill text-warning me-2.5 fs-6"></i> Add Warehouse</a></li>
                <?php endif; ?>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_rack')): ?>
                <li><a class="dropdown-item py-2 fw-semibold rounded-2 d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addRackModal"><i class="bi bi-grid-3x3-gap-fill text-info me-2.5 fs-6"></i> Add Storage Rack</a></li>
                <?php endif; ?>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_bin')): ?>
                <li><a class="dropdown-item py-2 fw-semibold rounded-2 d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addBinModal"><i class="bi bi-pin-map-fill text-secondary me-2.5 fs-6"></i> Add Bin Location</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider my-1"></li>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_company')): ?>
                <li><a class="dropdown-item py-2 fw-semibold rounded-2 d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addCompanyModal"><i class="bi bi-building-add text-primary me-2.5 fs-6"></i> Add Company</a></li>
                <?php endif; ?>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_branch')): ?>
                <li><a class="dropdown-item py-2 fw-semibold rounded-2 d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#addBranchModal"><i class="bi bi-geo-alt-fill text-success me-2.5 fs-6"></i> Add Branch</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($activeTab !== 'companies'): ?>
<!-- ═══════════════════════════════════ WAREHOUSES & STORAGE FACILITIES ONLY ═══════════════════════════════════ -->

<!-- Summary Metrics Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Registered Warehouses</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-houses-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-extrabold mb-2 font-monospace text-warning"><?= count($warehouses) ?> <span class="fs-6 fw-semibold text-secondary">Hubs</span></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold">100% Operational</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Storage Racks</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-extrabold mb-2 font-monospace text-info"><?= count($racks) ?> <span class="fs-6 fw-semibold text-secondary">Racks</span></div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 fw-bold">Mapped to Warehouses</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Storage Bins Mapped</span>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-pin-map-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-extrabold mb-2 font-monospace text-success"><?= count($bins) ?> <span class="fs-6 fw-semibold text-secondary">Bins</span></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 fw-bold">Traceability Enabled</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Total Capacity</span>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-building fs-5"></i>
                </div>
            </div>
            <?php 
                $totalCap = 0;
                foreach ($warehouses as $wh) { $totalCap += (float)($wh['capacity_sqft'] ?? 0); }
            ?>
            <div class="fs-3 fw-extrabold mb-2 font-monospace text-primary"><?= number_format($totalCap ?: 110000) ?> <span class="fs-6 fw-semibold text-secondary">Sq.Ft.</span></div>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 fw-bold">Multi-Branch Storage</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Registered Warehouses Master -->
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-houses-fill text-warning me-2"></i>Registered Warehouses Master</h5>
                    <small class="text-secondary">Physical fulfillment hubs linked to operating branches</small>
                </div>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_warehouse')): ?>
                <button type="button" class="btn btn-warning btn-sm rounded-3 fw-bold text-dark px-3" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Warehouse
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover small mb-0 align-middle">
                    <thead><tr class="text-secondary"><th>Warehouse Code</th><th>Warehouse Name</th><th>Operating Branch</th><th>Parent Company</th><th>Storage Capacity</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $wh): ?>
                                <tr>
                                    <td class="fw-bold text-warning fs-6"><i class="bi bi-house-door-fill me-1.5"></i><?= e($wh['code']) ?></td>
                                    <td class="fw-semibold"><?= e($wh['name']) ?></td>
                                    <td class="text-info fw-semibold"><?= e($wh['branch_name']) ?></td>
                                    <td><?= e($wh['company_name']) ?></td>
                                    <td class="text-secondary"><?= number_format($wh['capacity_sqft'] ?? 5000) ?> Sq. Ft.</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Active</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-secondary py-3">No warehouses registered. Click "Add Warehouse" above to add one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Registered Storage Racks -->
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-grid-3x3-gap-fill text-info me-2"></i>Storage Racks Master</h5>
                    <small class="text-secondary">High-density shelf racks inside warehouses</small>
                </div>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_rack')): ?>
                <button type="button" class="btn btn-outline-info btn-sm rounded-3 fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addRackModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Storage Rack
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover small mb-0 align-middle">
                    <thead><tr class="text-secondary"><th>Rack Code</th><th>Rack Name</th><th>Mapped Warehouse</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (!empty($racks)): ?>
                            <?php foreach ($racks as $rk): ?>
                                <tr>
                                    <td class="fw-bold text-info fs-6"><i class="bi bi-grid-3x3 me-1.5 text-info"></i><?= e($rk['code']) ?></td>
                                    <td class="fw-semibold"><?= e($rk['name']) ?></td>
                                    <td class="text-warning fw-semibold"><?= e($rk['warehouse_name']) ?> (<?= e($rk['warehouse_code']) ?>)</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Active</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-secondary py-3">No storage racks registered. Click "Add Storage Rack" above to create one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Storage Bins Locations -->
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-pin-map-fill text-success me-2"></i>Bin Storage Locations (Traceable Bins)</h5>
                    <small class="text-secondary">Exact physical storage bins for SKU allocation</small>
                </div>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_bin')): ?>
                <button type="button" class="btn btn-outline-success btn-sm rounded-3 fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#addBinModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Bin Location
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover small mb-0 align-middle">
                    <thead><tr class="text-secondary"><th>Bin Code</th><th>Bin Name</th><th>Rack Code</th><th>Warehouse</th><th>Max Capacity</th></tr></thead>
                    <tbody>
                        <?php if (!empty($bins)): ?>
                            <?php foreach ($bins as $bn): ?>
                                <tr>
                                    <td class="fw-bold text-success fs-6"><i class="bi bi-geo-fill me-1"></i><?= e($bn['code']) ?></td>
                                    <td class="fw-semibold"><?= e($bn['name']) ?></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary"><?= e($bn['rack_code']) ?></span></td>
                                    <td class="text-warning fw-semibold"><?= e($bn['warehouse_name']) ?></td>
                                    <td><?= number_format($bn['max_capacity']) ?> Units</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-secondary py-3">No bin storage locations registered. Click "Add Bin Location" above to create one.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════ COMPANIES & BRANCHES ONLY ═══════════════════════════════════ -->

<div class="row g-4">
    <!-- Companies -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-building-fill text-primary me-2"></i>Registered Enterprise Companies</h5>
                    <small class="text-secondary">Parent corporate entities</small>
                </div>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_company')): ?>
                <button type="button" class="btn btn-primary btn-sm rounded-3 fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Company
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover small mb-0 align-middle">
                    <thead><tr class="text-secondary"><th>Code</th><th>Company Name</th><th>Tax ID</th></tr></thead>
                    <tbody>
                        <?php foreach ($companies as $c): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= e($c['code']) ?></td>
                                <td class="fw-semibold"><?= e($c['name']) ?></td>
                                <td class="text-secondary"><?= e($c['tax_id']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Branches -->
    <div class="col-md-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i>Operating Branches</h5>
                    <small class="text-secondary">Localized branch offices</small>
                </div>
                <?php if (has_permission('organization.manage') || has_permission('organization.create_branch')): ?>
                <button type="button" class="btn btn-success btn-sm rounded-3 fw-bold text-white px-3" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Branch
                </button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover small mb-0 align-middle">
                    <thead><tr class="text-secondary"><th>Code</th><th>Branch Name</th><th>Location</th></tr></thead>
                    <tbody>
                        <?php foreach ($branches as $b): ?>
                            <tr>
                                <td class="fw-bold text-success"><?= e($b['code']) ?></td>
                                <td class="fw-semibold"><?= e($b['name']) ?></td>
                                <td class="text-secondary"><?= e($b['address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════ MODALS ═══════════════════════════════════ -->

<!-- Modal: Add New Company -->
<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addCompanyModalLabel">
                    <i class="bi bi-building-add text-primary me-2"></i>Register New Enterprise Company
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/organization/companies/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Company Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tata Enterprise Tech Ltd" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Company Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. COMP-TATA" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">GSTIN / Tax Registration ID</label>
                        <input type="text" name="tax_id" class="form-control" placeholder="e.g. GSTIN-27AAAAA0000A1Z5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Corporate Office Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Corporate Tower, Nariman Point, Mumbai"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save & Create Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Branch -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addBranchModalLabel">
                    <i class="bi bi-geo-alt-fill text-success me-2"></i>Create New Operating Branch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/organization/branches/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Enterprise Company *</label>
                        <select name="company_id" class="form-select" required>
                            <?php foreach ($companies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Branch Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Pune Regional Technology Center" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Branch Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BR-PUNE" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Physical Location Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Hinjewadi IT Park, Phase 1, Pune, Maharashtra"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save & Create Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Warehouse -->
<div class="modal fade" id="addWarehouseModal" tabindex="-1" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addWarehouseModalLabel">
                    <i class="bi bi-houses-fill text-warning me-2"></i>Register New Warehouse
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/organization/warehouses/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mapped Operating Branch *</label>
                        <select name="branch_id" class="form-select" required>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?> (<?= e($b['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Warehouse Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Central Logistics Hub Delhi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Warehouse Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. WH-DELHI-01" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Storage Capacity (Sq. Ft.)</label>
                        <input type="number" name="capacity_sqft" class="form-control" placeholder="15000" value="10000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Warehouse Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Sector 18, Logistics Corridor, Delhi NCR"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save & Register Warehouse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Storage Rack -->
<div class="modal fade" id="addRackModal" tabindex="-1" aria-labelledby="addRackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addRackModalLabel">
                    <i class="bi bi-grid-3x3-gap-fill text-info me-2"></i>Create Storage Rack
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/organization/racks/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Warehouse *</label>
                        <select name="warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= e($wh['name']) ?> (<?= e($wh['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rack Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. RACK-A1" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rack Name / Description *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Row A High Density Heavy Material Rack" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-dark rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save & Create Storage Rack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Storage Bin Location -->
<div class="modal fade" id="addBinModal" tabindex="-1" aria-labelledby="addBinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addBinModalLabel">
                    <i class="bi bi-pin-map-fill text-success me-2"></i>Create Storage Bin Location
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/organization/bins/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Storage Rack *</label>
                        <select name="rack_id" class="form-select" required>
                            <?php
                                $db = \App\Core\Database::getInstance();
                                $allRacks = $db->query("SELECT r.*, w.name AS warehouse_name FROM racks r JOIN warehouses w ON r.warehouse_id = w.id")->fetchAll();
                                foreach ($allRacks as $rk):
                            ?>
                                <option value="<?= $rk['id'] ?>"><?= e($rk['code']) ?> (<?= e($rk['name']) ?> - <?= e($rk['warehouse_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bin Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BIN-A1-01" required style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bin Name / Description *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Top Shelf Electronics Bin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Max Item Capacity</label>
                        <input type="number" name="max_capacity" class="form-control" placeholder="1000" value="500">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save Bin Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
