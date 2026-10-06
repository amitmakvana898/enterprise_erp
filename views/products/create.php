<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="page-title mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>Create Master Product Item</h3>
        <p class="text-muted small mb-0">Smart item catalog engine: Auto-generate SKUs, barcodes, HSN tax codes, and dynamic variant attributes</p>
    </div>
    <a href="<?= url('/products') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to Product Master
    </a>
</div>

<div class="card shadow-sm border p-4">
    <form action="<?= url('/products/store') ?>" method="POST" id="product_form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Section 1: Item Identity -->
        <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-info me-2"></i>Product Identity & SKU Codes</h5>
        <div class="row g-3 mb-4">
            <!-- Product Name -->
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Product Name *</label>
                <div class="input-group">
                    <input type="text" name="name" id="product_name" class="form-control fw-semibold" placeholder="e.g. Industrial Power Drill, Cotton T-Shirt, XPS 15 Laptop" required autofocus>
                    <span class="input-group-text text-info" id="name_status_icon"><i class="bi bi-magic"></i></span>
                </div>
                <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Type item name to auto-detect category & attributes</small>
            </div>

            <!-- SKU with Auto-Generate Button -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Product SKU *</label>
                <div class="input-group">
                    <input type="text" name="sku" id="product_sku" class="form-control font-monospace fw-bold" placeholder="PROD-ITEM-001" style="text-transform: uppercase;" required>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold" onclick="generateAutoSku()" title="Auto-Generate SKU Code">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Auto SKU
                    </button>
                </div>
            </div>

            <!-- Barcode with Auto-Generate Button -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Barcode (Code128 / EAN)</label>
                <div class="input-group">
                    <input type="text" name="barcode" id="product_barcode" class="form-control font-monospace" placeholder="890123456789">
                    <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="generateAutoBarcode()" title="Auto-Generate Barcode">
                        <i class="bi bi-upc-scan me-1"></i> Auto
                    </button>
                </div>
            </div>
        </div>

        <!-- Section 2: Category, Brand & UOM -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">Category *</label>
                    <button type="button" class="btn btn-link p-0 text-info small text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Category
                    </button>
                </div>
                <select name="category_id" id="category_select" class="form-select" required onchange="onCategoryManualSelect()">
                    <option value="">-- Select Product Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-name="<?= strtolower(e($cat['name'])) ?>" data-slug="<?= strtolower(e($cat['slug'] ?? '')) ?>">
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Category Not Found Warning & 1-Click Quick Create Banner -->
                <div id="category_not_found_alert" class="mt-2.5 p-3 rounded-3 shadow-lg d-none" style="background: rgba(245, 158, 11, 0.12); border: 1.5px solid rgba(245, 158, 11, 0.4); backdrop-filter: blur(8px);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="small fw-semibold" style="color:var(--text-primary);">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-1.5 fs-6"></i> Category for "<strong class="text-warning font-monospace" id="suggested_cat_name">...</strong>" not found!
                        </div>
                        <button type="button" class="btn btn-warning text-dark btn-sm fw-bold px-3 py-1.5 rounded-pill shadow-sm" onclick="quickCreateSuggestedCategory()">
                            <i class="bi bi-plus-circle-fill me-1"></i> Create Category Now
                        </button>
                    </div>
                </div>
                <!-- Category Auto-Detected Success Badge -->
                <div id="category_detected_success" class="mt-2.5 p-2 px-3 rounded-3 bg-success bg-opacity-15 border border-success border-opacity-30 text-success small fw-bold d-none">
                    <i class="bi bi-check-circle-fill me-1"></i> Category Auto-Detected!
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold mb-0">Brand</label>
                    <button type="button" class="btn btn-link p-0 text-warning small text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Brand
                    </button>
                </div>
                <select name="brand_id" id="brand_select" class="form-select">
                    <option value="">-- Generic / Unbranded --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Unit of Measure (UOM) *</label>
                <select name="unit_id" class="form-select" required>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Dynamic Category-Specific Attributes Card -->
        <div id="dynamic_attributes_card" class="card shadow-sm border p-4 mb-4 rounded-3" style="background:var(--bg-page);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-5"></i>
                    <h6 class="fw-bold mb-0">Dynamic Product Attributes & Variant Specifications</h6>
                </div>
                <span id="auto_matched_badge" class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30">
                    <i class="bi bi-check-circle-fill me-1"></i> Active Attribute Template
                </span>
            </div>
            <div id="attributes_container" class="row g-3">
                <!-- Dynamically rendered via JavaScript based on category selection -->
            </div>
        </div>

        <!-- Section 3: Pricing, GST & Reorder Thresholds -->
        <h5 class="fw-bold mb-3"><i class="bi bi-currency-dollar text-warning me-2"></i>Pricing, GST Rates & Inventory Thresholds</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Purchase Rate (₹) *</label>
                <input type="number" step="0.01" name="purchase_rate" id="purchase_rate_input" class="form-control fw-bold font-monospace" placeholder="450.00" oninput="calcMargin()" required>
                <small class="text-warning d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-bag-check me-1"></i>Vendor PO Cost</small>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Selling Rate (₹) *</label>
                <input type="number" step="0.01" name="selling_rate" id="selling_rate_input" class="form-control fw-bold font-monospace" placeholder="899.00" oninput="calcMargin()" required>
                <small class="text-success d-block mt-1" style="font-size:0.75rem;" id="margin_indicator"><i class="bi bi-cart-check me-1"></i>Customer SO Rate</small>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">HSN / SAC Code</label>
                <input type="text" name="hsn_code" class="form-control font-monospace" placeholder="84713010" value="84713010">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tax Bracket % *</label>
                <select name="gst_rate" class="form-select fw-bold">
                    <option value="0.00">0% (Exempt)</option>
                    <option value="5.00">5% GST</option>
                    <option value="12.00">12% GST</option>
                    <option value="18.00" selected>18% GST (Standard)</option>
                    <option value="28.00">28% GST</option>
                </select>
            </div>
            <input type="hidden" name="valuation_method" value="AVCO">
        </div>

        <!-- Section 4: Stock Re-order Thresholds -->
        <div class="row g-3 mb-4 p-3 rounded-3 border" style="background:var(--bg-page);">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Reorder Threshold Level *</label>
                <input type="number" name="reorder_level" class="form-control fw-bold font-monospace" value="10" placeholder="10" required>
                <small class="text-danger d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-bell-fill me-1"></i>Triggers Low Stock Alert</small>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Minimum Safety Stock</label>
                <input type="number" name="min_stock" class="form-control font-monospace" value="5" placeholder="5">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Maximum Storage Capacity</label>
                <input type="number" name="max_stock" class="form-control font-monospace" value="100" placeholder="100">
            </div>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="form-label small fw-semibold">Item Specification & Notes</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Enter technical specifications, warranty, material composition..."></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/products') ?>" class="btn btn-outline-secondary fw-bold px-4 py-2">Cancel</a>
            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                <i class="bi bi-check-circle-fill me-1.5"></i> Save Master Product Item
            </button>
        </div>
    </form>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border">
            <div class="modal-header border-bottom bg-info bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-info"><i class="bi bi-folder-plus me-2"></i>Create New Product Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/products/categories/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="redirect" value="<?= url('/products/create') ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Electronics, Tools & Hardware" required>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-dark fw-bold px-3 shadow-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Brand -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border">
            <div class="modal-header border-bottom bg-warning bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-tag-fill me-2"></i>Register New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/products/brands/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="redirect" value="<?= url('/products/create') ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Brand Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Bosch, Dell, Nike" required>
                    </div>
                </div>
                <div class="modal-footer border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold px-3 shadow-sm">Register Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let lastSuggestedCategory = '';

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('product_name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            autoDetectCategory(this.value.trim());
        });
    }
});

function autoDetectCategory(name) {
    if (!name || name.length < 2) {
        document.getElementById('category_not_found_alert').classList.add('d-none');
        document.getElementById('category_detected_success').classList.add('d-none');
        return;
    }

    const nameLower = name.toLowerCase();
    const select = document.getElementById('category_select');
    let matchedOptionValue = null;
    let matchedCategoryName = '';

    // Check if product name matches any category option
    for (let i = 0; i < select.options.length; i++) {
        const opt = select.options[i];
        const catName = (opt.getAttribute('data-name') || '').toLowerCase();
        if (catName && (nameLower.includes(catName) || catName.includes(nameLower) || isKeywordMatch(nameLower, catName))) {
            matchedOptionValue = opt.value;
            matchedCategoryName = opt.text;
            break;
        }
    }

    if (matchedOptionValue) {
        select.value = matchedOptionValue;
        document.getElementById('category_not_found_alert').classList.add('d-none');
        document.getElementById('category_detected_success').classList.remove('d-none');
        document.getElementById('category_detected_success').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Auto-Detected Category: <strong>' + matchedCategoryName + '</strong>';
        if (typeof renderDynamicAttributes === 'function') {
            renderDynamicAttributes(matchedCategoryName);
        }
    } else {
        // Category Not Found! Extract keyword to suggest creation
        const words = name.split(/\s+/).filter(w => w.length > 2);
        const suggestedWord = words[words.length - 1] || name;
        lastSuggestedCategory = capitalizeFirstLetter(suggestedWord);

        document.getElementById('suggested_cat_name').innerText = lastSuggestedCategory;
        document.getElementById('category_not_found_alert').classList.remove('d-none');
        document.getElementById('category_detected_success').classList.add('d-none');
    }
}

function isKeywordMatch(nameLower, catName) {
    const map = {
        'shirt': 'clothing', 't-shirt': 'clothing', 'jean': 'clothing', 'pant': 'clothing', 'apparel': 'clothing',
        'phone': 'electronics', 'laptop': 'electronics', 'tv': 'electronics', 'computer': 'electronics',
        'shoe': 'footwear', 'sneaker': 'footwear', 'boot': 'footwear',
        'drill': 'tool', 'wrench': 'tool', 'hammer': 'tool', 'hardware': 'tool',
        'chair': 'furniture', 'table': 'furniture', 'desk': 'furniture',
        'car': 'automotive', 'tire': 'automotive', 'oil': 'automotive'
    };
    for (let k in map) {
        if (nameLower.includes(k) && catName.includes(map[k])) return true;
    }
    return false;
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function quickCreateSuggestedCategory() {
    const modalInput = document.querySelector('#addCategoryModal input[name="name"]');
    if (modalInput) {
        modalInput.value = lastSuggestedCategory || 'General Category';
    }
    const modalEl = document.getElementById('addCategoryModal');
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
    }
}

function onCategoryManualSelect() {
    document.getElementById('category_not_found_alert').classList.add('d-none');
    document.getElementById('category_detected_success').classList.add('d-none');
}

function generateAutoSku() {
    const name = document.getElementById('product_name').value.trim();
    let prefix = 'SKU';
    if (name.length >= 3) {
        prefix = name.substring(0, 4).replace(/[^a-zA-Z]/g, '').toUpperCase();
    }
    const rand = Math.floor(1000 + Math.random() * 9000);
    document.getElementById('product_sku').value = 'PROD-' + prefix + '-' + rand;
}

function generateAutoBarcode() {
    const rand = Math.floor(100000000000 + Math.random() * 900000000000);
    document.getElementById('product_barcode').value = rand;
}

function calcMargin() {
    const cost = parseFloat(document.getElementById('purchase_rate_input').value) || 0;
    const sell = parseFloat(document.getElementById('selling_rate_input').value) || 0;
    if (sell > 0 && cost > 0) {
        const margin = ((sell - cost) / sell) * 100;
        document.getElementById('margin_indicator').innerHTML = '<i class="bi bi-cart-check me-1"></i>Margin: <strong>' + margin.toFixed(1) + '%</strong>';
    }
}
</script>
