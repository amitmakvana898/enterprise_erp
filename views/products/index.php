<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-box-seam-fill me-2" style="color:var(--primary)"></i>Product Master & Catalog</h3>
        <p class="page-header-sub">Comprehensive product catalog with SKUs, barcodes, categories, valuation methods, and bin stock</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('/products/export') ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm" title="Export Complete Catalog to Excel / CSV">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
        <?php if (has_permission('products.create')): ?>
        <button type="button" class="btn btn-outline-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#importProductModal" title="Bulk Import Products via CSV">
            <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Import
        </button>
        <button type="button" class="btn btn-outline-info btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-folder-plus me-1"></i> + Create Category
        </button>
        <a href="<?= url('/products/create') ?>" class="btn btn-primary btn-sm fw-bold text-white shadow-sm" style="background-image:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <i class="bi bi-plus-lg me-1"></i> + Add New Product
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (($_GET['filter'] ?? '') !== 'low_stock'): ?>
<!-- Product Categories (Collapsible Toggle Button) -->
<div class="card mb-4 shadow-sm" style="background: var(--bg-page); border: 1px solid var(--border);">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-2.5">
        <div class="section-title mb-0">
            <div class="section-icon" style="background:var(--info-bg);color:var(--info);"><i class="bi bi-tags-fill"></i></div>
            <span class="fw-bold">Product Category Master Grid</span>
            <span class="badge bg-primary ms-2 rounded-pill px-2.5" style="font-size:0.75rem;"><?= count($categories ?? []) ?> Categories Registered</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-info btn-sm rounded-pill fw-bold px-3 py-1.5" data-bs-toggle="collapse" data-bs-target="#categoryGridCollapse" aria-expanded="false" aria-controls="categoryGridCollapse">
                <i class="bi bi-grid-3x3-gap-fill me-1.5"></i> View All Category Grid
            </button>
            <button type="button" class="btn btn-info btn-sm fw-bold rounded-3 px-3 py-1.5 text-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg me-1"></i> Create Category
            </button>
        </div>
    </div>
    
    <!-- Collapsible Container for Categories Grid -->
    <div class="collapse" id="categoryGridCollapse">
        <div class="card-body border-top border-secondary border-opacity-25 p-3">
            <div class="row g-3">
                <?php if (!empty($categories)): ?>
                    <?php 
                        $catIcons = [
                            'laptop' => 'bi-laptop text-info',
                            'electronics' => 'bi-laptop text-info',
                            'computer' => 'bi-pc-display text-info',
                            'clothing' => 'bi-tag-fill text-warning',
                            'apparel' => 'bi-bag-fill text-warning',
                            'footwear' => 'bi-box-seam-fill text-success',
                            'shoe' => 'bi-box-seam-fill text-success',
                            'furniture' => 'bi-house-heart-fill text-primary',
                            'home' => 'bi-houses-fill text-primary',
                            'tool' => 'bi-tools text-danger',
                            'hardware' => 'bi-gear-wide-connected text-danger',
                            'mobile' => 'bi-phone-fill text-info',
                            'smartphone' => 'bi-phone-fill text-info',
                            'automotive' => 'bi-car-front-fill text-danger',
                        ];
                    ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                            $cName = e($cat['name']);
                            $cSlug = strtolower(e($cat['slug'] ?? ''));
                            $matchedIcon = 'bi-folder-fill text-primary';
                            foreach ($catIcons as $k => $icon) {
                                if (strpos($cSlug, $k) !== false || strpos(strtolower($cName), $k) !== false) {
                                    $matchedIcon = $icon;
                                    break;
                                }
                            }
                        ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between position-relative" 
                                 style="background:var(--bg-surface);border:1px solid var(--border);transition:all 0.2s ease;">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2 rounded-circle" style="background:var(--bg-page);width:34px;height:34px;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi <?= $matchedIcon ?>" style="font-size:1.1rem;"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-truncate" style="color:var(--text-primary);max-width:130px;" title="<?= $cName ?>">
                                                <?= $cName ?>
                                            </h6>
                                        </div>
                                        <span class="badge bg-primary"><?= (int)($cat['product_count'] ?? 0) ?> Items</span>
                                    </div>
                                    <small class="text-muted d-block text-truncate" style="font-size:0.76rem;" title="slug: <?= $cSlug ?>">
                                        <i class="bi bi-link-45deg me-1"></i><?= $cSlug ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                                    <form action="<?= url('/products/categories/delete/' . $cat['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete Category: <?= e(addslashes($cat['name'])) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-0.5" style="font-size:0.75rem;" title="Delete Category">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-3" style="color:var(--text-muted);">
                        <i class="bi bi-folder2-open fs-3 d-block mb-1 opacity-50"></i>
                        No categories yet. Click <strong>Create Category</strong> to register product classifications.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Product Table -->
<?php if (($_GET['filter'] ?? '') === 'low_stock'): ?>
<div class="card p-3 mb-4 border border-danger border-opacity-30 bg-danger bg-opacity-10 shadow-sm rounded-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-danger bg-opacity-15 text-danger">
                <i class="bi bi-exclamation-triangle-fill fs-3"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1"><i class="bi bi-funnel-fill text-danger me-1.5"></i>Low Stock Items Filter Active</h5>
                <p class="text-secondary small mb-0">Showing strictly items where bin stock is $\le$ reorder threshold. Total <strong><?= count($products) ?></strong> items requiring urgent reorder.</p>
            </div>
        </div>
        <a href="<?= url('/products') ?>" class="btn btn-outline-danger fw-bold rounded-3 px-3 py-1.5 small">
            <i class="bi bi-x-circle-fill me-1.5 text-danger"></i> Clear Filter & View All SKUs
        </a>
    </div>
</div>
<?php endif; ?>

<div class="card p-0" style="overflow: visible;">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div class="section-title mb-0">
            <div class="section-icon" style="background:var(--primary-light);color:var(--primary);"><i class="bi bi-table"></i></div>
            <span><?= (($_GET['filter'] ?? '') === 'low_stock') ? 'Low Stock Reorder Items Catalog' : 'Master Item Catalog' ?></span>
        </div>
        <!-- Live Search & Category Filter Controls -->
        <div class="d-flex align-items-center gap-2 flex-wrap" style="max-width: 600px; width: 100%;">
            <div class="input-group input-group-sm" style="flex: 1; min-width: 220px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="catalogSearchInput" class="form-control" placeholder="Search SKU, Product Name, Category...">
            </div>
            <select id="catalogCategoryFilter" class="form-select form-select-sm" style="width: auto; min-width: 180px;">
                <option value="">-- Filter by Category --</option>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e(strtolower($cat['name'])) ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
    <div class="table-responsive" style="border:none; border-radius:0; overflow-x:auto; overflow-y:visible;">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>SKU & Barcode</th>
                    <th>Product Name</th>
                    <th>Category & Brand</th>
                    <th>Purchase Rate</th>
                    <th>Selling Rate</th>
                    <th>Bin Stock</th>
                    <th class="text-end" style="padding-right: 20px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <tr class="product-catalog-row" 
                            data-category="<?= strtolower(e($p['category_name'])) ?>" 
                            data-search="<?= strtolower(e($p['sku'] . ' ' . $p['name'] . ' ' . $p['category_name'] . ' ' . ($p['brand_name'] ?? ''))) ?>">
                            <td>
                                <div style="font-weight:700;color:var(--primary);"><?= e($p['sku']) ?></div>
                                <small style="color:var(--text-muted);"><i class="bi bi-barcode me-1"></i><?= e($p['barcode']) ?></small>
                            </td>
                            <td style="font-weight:600;color:var(--text-primary);"><?= e($p['name']) ?></td>
                            <td>
                                <span class="badge bg-info text-dark fw-bold" style="font-size:0.8rem; padding: 4px 10px; border-radius: 6px;">
                                    <i class="bi bi-folder-fill me-1"></i><?= e($p['category_name']) ?>
                                </span>
                                <div><small style="color:var(--text-muted);"><i class="bi bi-tag-fill me-1 opacity-75"></i><?= e($p['brand_name'] ?? 'Generic') ?></small></div>
                            </td>
                            <td style="font-weight:600;color:var(--warning);"><?= format_currency($p['purchase_rate']) ?></td>
                            <td style="font-weight:600;color:var(--success);"><?= format_currency($p['selling_rate']) ?></td>
                            <td>
                                <span class="badge bg-<?= $p['total_stock'] <= $p['reorder_level'] ? 'danger' : 'success' ?>" style="font-size:0.8rem;">
                                    <?= $p['total_stock'] ?> <?= e($p['unit_code']) ?>
                                </span>
                            </td>
                            <td class="text-end" style="padding-right: 15px; white-space: nowrap;">
                                <div class="d-inline-flex gap-1.5 align-items-center">
                                    <a href="<?= url('/products/edit/' . $p['id']) ?>" class="btn btn-outline-warning btn-sm fw-bold px-2.5" title="Edit Product Master">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>

                                    <button type="button" class="btn btn-outline-info btn-sm fw-bold px-2.5" data-bs-toggle="modal" data-bs-target="#productSpecsModal<?= $p['id'] ?>" title="View Technical Specs & Attributes">
                                        <i class="bi bi-sliders me-1"></i> Specs
                                    </button>

                                    <button type="button" class="btn btn-outline-light btn-sm fw-bold px-2.5" data-bs-toggle="modal" data-bs-target="#productBarcodeModal<?= $p['id'] ?>" title="Instant Barcode & QR Label Preview">
                                        <i class="bi bi-qr-code text-warning me-1"></i> Barcode
                                    </button>

                                    <form action="<?= url('/products/delete/' . $p['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete <?= e(addslashes($p['name'])) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="Delete Product">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-box-seam d-block mb-2" style="font-size:2rem;color:var(--border);"></i>
                            No products in catalog. Click "New Product" to add one.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('catalogSearchInput');
    const categoryFilter = document.getElementById('catalogCategoryFilter');
    const rows = document.querySelectorAll('.product-catalog-row');

    function filterCatalogTable() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const catVal = categoryFilter ? categoryFilter.value.trim().toLowerCase() : '';

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowCat = row.getAttribute('data-category') || '';

            const matchesQuery = !query || rowSearch.includes(query);
            const matchesCat = !catVal || rowCat === catVal || rowCat.includes(catVal);

            if (matchesQuery && matchesCat) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterCatalogTable);
    if (categoryFilter) categoryFilter.addEventListener('change', filterCatalogTable);
});
</script>

<!-- Product Specs & Barcode Preview Modals -->
<?php if (!empty($products)): ?>
    <?php foreach ($products as $p): ?>
        <?php $attrs = $productAttributes[$p['id']] ?? []; ?>
        
        <!-- Modal 1: Product Specs -->
        <div class="modal fade" id="productSpecsModal<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title"><i class="bi bi-box-seam-fill me-2" style="color:var(--info)"></i><?= e($p['name']) ?></h5>
                            <small style="color:var(--text-muted);">SKU: <strong style="color:var(--primary)"><?= e($p['sku']) ?></strong> · Barcode: <?= e($p['barcode']) ?></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-4 p-3 rounded-3" style="background:var(--bg-page);border:1px solid var(--border);">
                            <div class="col-md-4">
                                <small style="color:var(--text-muted);font-weight:600;font-size:0.72rem;text-transform:uppercase;">Category & Brand</small>
                                <div><span class="badge bg-info mt-1"><?= e($p['category_name']) ?></span></div>
                                <small style="color:var(--text-secondary);"><?= e($p['brand_name'] ?? 'Generic') ?></small>
                            </div>
                            <div class="col-md-4">
                                <small style="color:var(--text-muted);font-weight:600;font-size:0.72rem;text-transform:uppercase;">Pricing Rates</small>
                                <div style="color:var(--warning);font-weight:700;">Purchase: <?= format_currency($p['purchase_rate']) ?></div>
                                <div style="color:var(--success);font-weight:700;">Selling: <?= format_currency($p['selling_rate']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <small style="color:var(--text-muted);font-weight:600;font-size:0.72rem;text-transform:uppercase;">Available Stock</small>
                                <div><span class="badge bg-<?= $p['total_stock'] <= $p['reorder_level'] ? 'danger' : 'success' ?> mt-1"><?= $p['total_stock'] ?> <?= e($p['unit_code']) ?></span></div>
                            </div>
                        </div>
                        <h6 style="font-weight:700;color:var(--warning);margin-bottom:12px;"><i class="bi bi-sliders me-2"></i>Dynamic Attributes & Specifications</h6>
                        <?php if (!empty($attrs)): ?>
                            <div class="table-responsive" style="border-radius:var(--radius-sm);">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead><tr><th>Attribute</th><th>Value</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($attrs as $a): ?>
                                            <tr>
                                                <td style="font-weight:600;color:var(--warning);"><i class="bi bi-tag-fill me-1 opacity-75"></i><?= e($a['name']) ?></td>
                                                <td><span class="badge attr-spec-pill"><span class="attr-pill-key"><?= e($a['name']) ?>:</span> <span class="attr-pill-val"><?= e($a['value']) ?></span></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p style="color:var(--text-muted);font-size:0.85rem;"><i class="bi bi-info-circle me-1"></i>No dynamic attributes assigned yet.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <a href="<?= url('/products/' . $p['id']) ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-diagram-3 me-1"></i>Full Traceability</a>
                        <div>
                            <a href="<?= url('/products/edit/' . $p['id']) ?>" class="btn btn-warning btn-sm me-2"><i class="bi bi-pencil-square me-1"></i>Edit Master</a>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 2: INSTANT BARCODE & QR PREVIEW MODAL -->
        <div class="modal fade" id="productBarcodeModal<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border">
                    <div class="modal-header">
                        <h5 class="modal-title font-heading fw-bold">
                            <i class="bi bi-qr-code-scan text-warning me-2"></i>Product Barcode & QR Label
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="p-3.5 bg-white text-dark rounded-3 shadow-md mb-3 d-inline-block border" style="max-width: 100%; border-color: rgba(0,0,0,0.12) !important;">
                            <div class="fw-bold text-uppercase fs-6 mb-1 text-truncate" style="max-width: 320px; font-family: sans-serif; color: #0f172a;">
                                <?= e($p['name']) ?>
                            </div>
                            <div class="text-secondary small font-monospace mb-2" style="font-size: 0.78rem;">
                                SKU: <?= e($p['sku']) ?> | Rate: <?= format_currency($p['selling_rate']) ?>
                            </div>
                            <!-- Live Generated Barcode Image -->
                            <div class="my-2 p-2 bg-white rounded border border-light" style="min-height: 90px;">
                                <img src="<?= url('/barcode/generate?product_id=' . $p['id']) ?>" 
                                     alt="Barcode EAN-13" 
                                     class="img-fluid" 
                                     style="max-height: 110px; min-width: 220px;"
                                     onerror="this.onerror=null; this.src='https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($p['barcode'] ?? $p['sku']) ?>';">
                            </div>
                            <div class="font-monospace fw-bold fs-6 mt-1 text-dark" style="letter-spacing: 0.15em;">
                                <?= e($p['barcode']) ?>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                            <span class="badge bg-primary px-3 py-1.5 font-monospace">EAN-13 Standard</span>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <a href="<?= url('/barcode?product_id=' . $p['id']) ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-printer me-1"></i> Full Print Engine
                        </a>
                        <div>
                            <button type="button" class="btn btn-warning btn-sm fw-bold me-2" onclick="window.print();">
                                <i class="bi bi-printer-fill me-1"></i> Quick Print Label
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-folder-plus me-2" style="color:var(--info)"></i>Create New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/products/categories/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Electronics, Raw Materials" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (Optional)</label>
                        <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Category details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient-primary btn-sm"><i class="bi bi-check-circle-fill me-1"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Brand -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-tag-fill me-2" style="color:var(--warning)"></i>Register New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/products/brands/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Brand Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Dell, Samsung, Godrej" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Brand Code *</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. BRAND-DELL" required style="text-transform:uppercase;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Bulk Import Products from CSV -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cloud-arrow-up-fill me-2" style="color:var(--primary)"></i>Bulk Import Products from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/products/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle-fill me-1"></i> Download sample template first to format your data:
                            </div>
                            <a href="<?= url('/products/import/template') ?>" class="btn btn-sm btn-info text-dark fw-bold ms-2 px-2.5 py-1">
                                <i class="bi bi-download me-1"></i> Sample CSV
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select CSV File (.csv) *</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text small">Supports columns: Product Name, SKU, Barcode, Category, Brand, Unit, Purchase Rate, Sale Rate, Tax Rate, Opening Stock, HSN Code.</div>
                    </div>
                    <div class="p-2.5 rounded-3 bg-light border small text-secondary">
                        <i class="bi bi-magic text-primary me-1"></i> <strong>Smart Auto-Handling:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                            <li>Auto-creates categories/brands if not already in system</li>
                            <li>Auto-generates missing SKUs and Barcodes</li>
                            <li>Automatically populates opening inventory stocks</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold text-white" style="background-image:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-cloud-upload-fill me-1"></i> Upload & Import Catalog
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
