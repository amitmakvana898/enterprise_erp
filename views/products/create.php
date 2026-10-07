<!-- Page Header -->
<div class="page-header d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i>Create Master Product Item</h3>
        <p class="text-muted small mb-0">Smart item catalog engine: Auto-generate SKUs, barcodes, HSN tax codes, and dynamic variant attributes</p>
    </div>
    <a href="<?= url('/products') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Back to Product Master
    </a>
</div>

<div class="card shadow-sm border p-4 mb-4">
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
                    <span class="input-group-text bg-body-tertiary border" id="name_status_icon"><i class="bi bi-magic text-info"></i></span>
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
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label small fw-semibold mb-0">Category *</label>
                        <span id="category_detected_success" class="badge rounded-pill bg-success-subtle text-success border border-success border-opacity-25 d-none px-2 py-0.5" style="font-size: 0.72rem; font-weight: 600;">
                            <i class="bi bi-magic me-1"></i> Auto-Matched: <span id="detected_cat_name"></span>
                        </span>
                    </div>
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

                <!-- Category Not Found Warning & 1-Click Quick Create Helper -->
                <div id="category_not_found_alert" class="mt-1 d-flex align-items-center justify-content-between px-2 py-1 rounded bg-warning bg-opacity-10 border border-warning border-opacity-20 small d-none" style="font-size: 0.75rem;">
                    <span class="text-warning-emphasis fw-medium">
                        <i class="bi bi-info-circle me-1"></i> New: "<span id="suggested_cat_name" class="fw-bold">...</span>"
                    </span>
                    <button type="button" class="btn btn-link p-0 text-warning fw-bold text-decoration-none ms-2" onclick="quickCreateSuggestedCategory()" style="font-size:0.75rem;">
                        <i class="bi bi-plus-circle-fill me-1"></i> Quick Add
                    </button>
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
        <div id="dynamic_attributes_card" class="card shadow-sm border p-4 mb-4 rounded-3 bg-body-tertiary">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-5"></i>
                    <h6 class="fw-bold mb-0">Dynamic Product Attributes & Variant Specifications</h6>
                </div>
                <span id="auto_matched_badge" class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30">
                    <i class="bi bi-check-circle-fill me-1"></i> Dynamic Specifications
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
                <input type="text" name="hsn_code" id="hsn_code_input" class="form-control font-monospace" placeholder="84713010" value="84713010">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tax Bracket % *</label>
                <select name="gst_rate" id="gst_rate_select" class="form-select fw-bold">
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
        <div class="row g-3 mb-4 p-3 rounded-3 border bg-body-tertiary">
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
            <textarea name="description" id="product_description" class="form-control" rows="3" placeholder="Enter technical specifications, warranty, material composition..."></textarea>
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
const masterAttributesList = <?= json_encode($allAttributes ?? []) ?>;

// Category Attribute Templates
const categoryAttributeTemplates = {
    'clothing': [
        { name: 'Size', code: 'size', type: 'select', options: ['S', 'M', 'L', 'XL', 'XXL', '3XL'] },
        { name: 'Color', code: 'color', type: 'select', options: ['Black', 'Navy Blue', 'White', 'Charcoal Grey', 'Olive Green', 'Red'] },
        { name: 'Fabric / Material', code: 'material', type: 'select', options: ['100% Combed Cotton', 'Polyester Blend', 'Denim', 'Linen', 'Silk Blend'] },
        { name: 'Fit Type', code: 'fit', type: 'select', options: ['Regular Fit', 'Slim Fit', 'Oversized', 'Tailored Fit'] }
    ],
    'electronics': [
        { name: 'Processor / Chipset', code: 'processor', type: 'text', placeholder: 'e.g. Intel Core i7 13th Gen / Apple M3' },
        { name: 'RAM Memory', code: 'ram', type: 'select', options: ['8 GB DDR5', '16 GB DDR5', '32 GB DDR5', '64 GB Unified'] },
        { name: 'Storage Capacity', code: 'storage', type: 'select', options: ['256 GB NVMe SSD', '512 GB NVMe SSD', '1 TB NVMe SSD', '2 TB SSD'] },
        { name: 'Warranty Period', code: 'warranty', type: 'select', options: ['1 Year Manufacturer Warranty', '2 Years Extended Warranty', '3 Years On-Site'] }
    ],
    'hardware': [
        { name: 'Power Rating (Watts/Volts)', code: 'power', type: 'text', placeholder: 'e.g. 750W / 240V' },
        { name: 'Material Grade', code: 'material_grade', type: 'select', options: ['Heavy Duty Carbon Steel', 'Stainless Steel 304', 'Reinforced Polymer'] },
        { name: 'Certification Standard', code: 'cert', type: 'select', options: ['ISO 9001:2015', 'CE Certified', 'ISI Marked'] },
        { name: 'Warranty', code: 'warranty', type: 'select', options: ['6 Months', '1 Year Industrial Warranty', '2 Years Warranty'] }
    ],
    'footwear': [
        { name: 'Shoe Size (UK/India)', code: 'shoe_size', type: 'select', options: ['UK 6', 'UK 7', 'UK 8', 'UK 9', 'UK 10', 'UK 11'] },
        { name: 'Primary Color', code: 'color', type: 'select', options: ['Black', 'Brown', 'Tan', 'White', 'Grey'] },
        { name: 'Sole Material', code: 'sole', type: 'select', options: ['EVA Cushion Sole', 'Anti-Skid Rubber', 'Polyurethane (PU)', 'Leather Sole'] },
        { name: 'Upper Material', code: 'upper', type: 'select', options: ['Genuine Leather', 'Synthetic Mesh', 'Suede', 'Canvas'] }
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('product_name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            autoDetectCategory(this.value.trim());
        });
    }

    // Initialize default attributes
    renderDynamicAttributes();
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

    // Auto-generate SKU & Barcode if currently empty
    const skuInput = document.getElementById('product_sku');
    if (skuInput && !skuInput.value.trim()) {
        generateAutoSku();
    }
    const barcodeInput = document.getElementById('product_barcode');
    if (barcodeInput && !barcodeInput.value.trim()) {
        generateAutoBarcode();
    }

    if (matchedOptionValue) {
        select.value = matchedOptionValue;
        document.getElementById('category_not_found_alert').classList.add('d-none');
        const successEl = document.getElementById('category_detected_success');
        document.getElementById('detected_cat_name').innerText = matchedCategoryName;
        successEl.classList.remove('d-none');

        // Suggest HSN Code based on category
        autoSetHsnCode(nameLower, matchedCategoryName);

        // Render dynamic attributes
        renderDynamicAttributes(matchedCategoryName);
    } else {
        // Category Not Found! Extract keyword to suggest creation
        const words = name.split(/\s+/).filter(w => w.length > 2);
        const suggestedWord = words[words.length - 1] || name;
        lastSuggestedCategory = capitalizeFirstLetter(suggestedWord);

        document.getElementById('suggested_cat_name').innerText = lastSuggestedCategory;
        document.getElementById('category_not_found_alert').classList.remove('d-none');
        document.getElementById('category_detected_success').classList.add('d-none');
        renderDynamicAttributes(name);
    }
}

function autoSetHsnCode(nameLower, catName) {
    const hsnInput = document.getElementById('hsn_code_input');
    if (!hsnInput) return;

    const catLower = (catName || '').toLowerCase();
    if (nameLower.includes('shirt') || nameLower.includes('cloth') || nameLower.includes('apparel') || catLower.includes('cloth')) {
        hsnInput.value = '61091000'; // Cotton Apparel
    } else if (nameLower.includes('laptop') || nameLower.includes('computer') || catLower.includes('electronic')) {
        hsnInput.value = '84713010'; // Personal Computers & Laptops
    } else if (nameLower.includes('shoe') || catLower.includes('footwear')) {
        hsnInput.value = '64039990'; // Footwear
    } else if (nameLower.includes('drill') || nameLower.includes('tool') || catLower.includes('hardware')) {
        hsnInput.value = '84672100'; // Electric Power Tools
    }
}

function isKeywordMatch(nameLower, catName) {
    const map = {
        'shirt': 'cloth', 'tshirt': 'cloth', 't-shirt': 'cloth', 'jean': 'cloth', 'pant': 'cloth', 'apparel': 'cloth', 'dress': 'cloth', 'hoodie': 'cloth',
        'phone': 'electron', 'laptop': 'electron', 'tv': 'electron', 'computer': 'electron', 'monitor': 'electron', 'mobile': 'electron',
        'shoe': 'footwear', 'sneaker': 'footwear', 'boot': 'footwear', 'sandal': 'footwear',
        'drill': 'tool', 'wrench': 'tool', 'hammer': 'tool', 'hardware': 'tool', 'saw': 'tool',
        'chair': 'furniture', 'table': 'furniture', 'desk': 'furniture', 'sofa': 'furniture',
        'car': 'automotive', 'tire': 'automotive', 'oil': 'automotive', 'battery': 'automotive'
    };
    for (let k in map) {
        if (nameLower.includes(k) && catName.includes(map[k])) return true;
    }
    return false;
}

function renderDynamicAttributes(categoryHint = '') {
    const container = document.getElementById('attributes_container');
    if (!container) return;

    const hintLower = (categoryHint || '').toLowerCase();
    let matchedKey = null;

    if (hintLower.includes('cloth') || hintLower.includes('apparel') || hintLower.includes('fashion') || hintLower.includes('shirt')) {
        matchedKey = 'clothing';
    } else if (hintLower.includes('electron') || hintLower.includes('computer') || hintLower.includes('laptop') || hintLower.includes('phone')) {
        matchedKey = 'electronics';
    } else if (hintLower.includes('tool') || hintLower.includes('hardware') || hintLower.includes('machin') || hintLower.includes('drill')) {
        matchedKey = 'hardware';
    } else if (hintLower.includes('foot') || hintLower.includes('shoe')) {
        matchedKey = 'footwear';
    }

    let attrsToRender = [];
    if (matchedKey && categoryAttributeTemplates[matchedKey]) {
        attrsToRender = categoryAttributeTemplates[matchedKey];
    } else if (masterAttributesList && masterAttributesList.length > 0) {
        attrsToRender = masterAttributesList.map(a => ({
            name: a.name,
            code: a.code,
            type: a.type || 'text',
            options: typeof a.options === 'string' ? JSON.parse(a.options || '[]') : (a.options || [])
        }));
    } else {
        // Fallback default generic template
        attrsToRender = [
            { name: 'Model / Variant', code: 'model_variant', type: 'text', placeholder: 'e.g. Standard V2.0 / Pro Edition' },
            { name: 'Color / Finish', code: 'color', type: 'select', options: ['Standard Black', 'Silver Metallic', 'Matte White', 'Navy Blue'] },
            { name: 'Item Weight / Dimension', code: 'dimension', type: 'text', placeholder: 'e.g. 500g / 15x10x5 cm' },
            { name: 'Warranty / Shelf Life', code: 'warranty', type: 'select', options: ['6 Months Warranty', '1 Year Standard Warranty', '2 Years Warranty', 'No Warranty'] }
        ];
    }

    let html = '';
    attrsToRender.forEach(attr => {
        let inputHtml = '';
        if (attr.type === 'select' && attr.options && attr.options.length > 0) {
            inputHtml = `<select name="attributes[${attr.code}]" class="form-select fw-semibold">`;
            inputHtml += `<option value="">-- Select ${attr.name} --</option>`;
            attr.options.forEach(opt => {
                inputHtml += `<option value="${opt}">${opt}</option>`;
            });
            inputHtml += `</select>`;
        } else {
            inputHtml = `<input type="text" name="attributes[${attr.code}]" class="form-control" placeholder="${attr.placeholder || 'Specify ' + attr.name + '...'}">`;
        }

        html += `
            <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">
                    <i class="bi bi-tag-fill text-warning me-1"></i>${attr.name}
                </label>
                ${inputHtml}
            </div>
        `;
    });

    container.innerHTML = html;
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
    const select = document.getElementById('category_select');
    const selectedText = select.options[select.selectedIndex]?.text || '';
    document.getElementById('category_not_found_alert').classList.add('d-none');
    document.getElementById('category_detected_success').classList.add('d-none');
    if (selectedText) {
        renderDynamicAttributes(selectedText);
    }
}

function generateAutoSku() {
    const name = document.getElementById('product_name').value.trim();
    let prefix = 'SKU';
    if (name.length >= 3) {
        prefix = name.substring(0, 4).replace(/[^a-zA-Z]/g, '').toUpperCase();
    }
    if (!prefix || prefix.length < 2) prefix = 'ITEM';
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
