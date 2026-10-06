<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-heading fw-bold mb-1"><i class="bi bi-sliders text-primary me-2.5"></i>Dynamic Attribute Engine (EAV Master)</h2>
        <p class="text-secondary small mb-0">Define dynamic product specifications (Color, Size, RAM, Storage, Voltage) without database schema changes</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary fw-bold px-3.5 py-2 shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
            <i class="bi bi-plus-circle-fill me-1.5"></i> Register New Attribute
        </button>
        <button type="button" class="btn btn-outline-secondary fw-bold px-3.5 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#addGroupModal">
            <i class="bi bi-folder-plus me-1.5"></i> Add Attribute Group
        </button>
    </div>
</div>

<!-- Attribute Groups Summary Cards -->
<div class="card p-4 mb-4 shadow-sm border-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-folder-symlink-fill text-primary me-2"></i>Attribute Groups / Families</h5>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#addGroupModal">
            <i class="bi bi-plus-lg me-1"></i> Create Group
        </button>
    </div>
    <div class="row g-3">
        <?php if (!empty($groups)): ?>
            <?php foreach ($groups as $g): ?>
                <div class="col-md-3">
                    <div class="p-3 rounded-3 border" style="background:var(--bg-page);border-color:var(--border) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0"><?= e($g['name']) ?></h6>
                            <span class="badge bg-primary text-white font-monospace fw-bold"><?= (int)($g['attr_count'] ?? 0) ?> Attrs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-secondary">Attribute Family Group</small>
                            <form action="<?= url('/attributes/groups/delete/' . $g['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete attribute group family: <?= e($g['name']) ?>?');">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1.5" title="Delete Group Family"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-secondary small py-2">No custom groups created yet. Attributes will be grouped under General.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Full-Width Dynamic Attributes Catalog Table -->
<div class="row">
    <div class="col-12">
        <div class="card p-4 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i>Configured System Attributes Catalog</h5>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-1.5 rounded-2" data-bs-toggle="modal" data-bs-target="#addAttributeModal">
                    <i class="bi bi-plus-circle-fill me-1"></i> Register New Attribute
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                        <tr class="text-secondary">
                            <th>Code & Name</th>
                            <th>Group</th>
                            <th>Input Control Type</th>
                            <th>Requirement Rule</th>
                            <th>Defined Options Pills (Default Pre-selected)</th>
                            <th>Products Usage</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attributes)): ?>
                            <?php foreach ($attributes as $attr): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary fs-6"><?= e($attr['name']) ?></div>
                                        <code class="text-secondary font-monospace" style="font-size:0.78rem;">code: <?= e($attr['code']) ?></code>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1 fw-semibold"><i class="bi bi-folder2 me-1"></i><?= e($attr['group_name'] ?? 'General') ?></span></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase px-2.5 py-1 font-monospace fw-bold"><?= e($attr['type']) ?></span></td>
                                    <td>
                                        <?php if (!empty($attr['is_required'])): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-asterisk me-1"></i>Required</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border px-2 py-1">Optional</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $opts = !empty($attr['options_json']) ? json_decode($attr['options_json'], true) : [];
                                            if (!is_array($opts) && !empty($attr['options_json'])) {
                                                $opts = array_map('trim', explode(',', $attr['options_json']));
                                            }
                                            $defVal = !empty($attr['default_value']) ? trim($attr['default_value']) : (!empty($opts[0]) ? $opts[0] : null);
                                        ?>
                                        <?php if (!empty($opts) && is_array($opts)): ?>
                                            <div class="d-flex flex-wrap gap-1.5">
                                                <?php foreach (array_slice($opts, 0, 6) as $idx => $o): ?>
                                                    <?php $isDef = ($defVal !== null && strtolower($defVal) === strtolower($o)) || ($defVal === null && $idx === 0); ?>
                                                    <?php if ($isDef): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold py-1 px-2.5" title="Pre-selected Default Option">
                                                            <i class="bi bi-check-circle-fill me-1"></i><?= e($o) ?> (Default)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-secondary border py-1 px-2.5">
                                                            <i class="bi bi-tag-fill opacity-50 me-1"></i><?= e($o) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <?php if (count($opts) > 6): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.72rem;">+<?= count($opts) - 6 ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?php $t = strtolower($attr['type']); ?>
                                            <?php if ($t === 'number'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1"><i class="bi bi-123 me-1"></i> Numeric Field <?= !empty($defVal) ? '(Default: ' . e($defVal) . ')' : '' ?></span>
                                            <?php elseif ($t === 'textarea'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1"><i class="bi bi-card-text me-1"></i> Text Area <?= !empty($defVal) ? '(Default: ' . e($defVal) . ')' : '' ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1"><i class="bi bi-fonts me-1"></i> Text Input <?= !empty($defVal) ? '(Default: ' . e($defVal) . ')' : '' ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-semibold">
                                            <i class="bi bi-box-seam me-1"></i><?= (int)($attr['usage_count'] ?? 0) ?> Products
                                        </span>
                                    </td>
                                     <td class="text-end">
                                         <div class="d-inline-flex gap-1.5">
                                             <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" data-bs-toggle="modal" data-bs-target="#editAttrModal<?= $attr['id'] ?>" title="Edit Attribute">
                                                 <i class="bi bi-pencil-square"></i>
                                             </button>
                                             <form action="<?= url('/attributes/delete/' . $attr['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete attribute: <?= e($attr['name']) ?>?');">
                                                 <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                                 <button type="submit" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Delete Attribute"><i class="bi bi-trash-fill"></i></button>
                                             </form>
                                         </div>

                                         <!-- Modal: Edit Attribute -->
                                         <div class="modal fade text-start" id="editAttrModal<?= $attr['id'] ?>" tabindex="-1" aria-hidden="true">
                                             <div class="modal-dialog modal-dialog-centered">
                                                 <div class="modal-content">
                                                     <div class="modal-header">
                                                         <h5 class="modal-title fw-bold text-warning"><i class="bi bi-pencil-square me-2"></i>Edit Attribute: <?= e($attr['name']) ?></h5>
                                                         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                     </div>
                                                     <form action="<?= url('/attributes/update/' . $attr['id']) ?>" method="POST">
                                                         <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                                         <div class="modal-body">
                                                             <div class="mb-3">
                                                                 <label class="form-label small fw-semibold">Attribute Name *</label>
                                                                 <input type="text" name="name" class="form-control" value="<?= e($attr['name']) ?>" required>
                                                             </div>
                                                             <div class="row g-3 mb-3">
                                                                 <div class="col-md-6">
                                                                     <label class="form-label small fw-semibold">Attribute Code (Key)</label>
                                                                     <input type="text" class="form-control font-monospace text-secondary" value="<?= e($attr['code']) ?>" readonly>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                     <label class="form-label small fw-semibold">Attribute Family Group</label>
                                                                     <select name="group_id" class="form-select">
                                                                         <option value="">-- General Group --</option>
                                                                         <?php foreach ($groups as $g): ?>
                                                                             <option value="<?= $g['id'] ?>" <?= ($attr['group_id'] ?? null) == $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                                                                         <?php endforeach; ?>
                                                                     </select>
                                                                 </div>
                                                             </div>
                                                             <div class="row g-3 mb-3">
                                                                 <div class="col-md-6">
                                                                     <label class="form-label small fw-semibold">Input Type</label>
                                                                     <select name="type" class="form-select">
                                                                         <option value="select" <?= $attr['type'] === 'select' ? 'selected' : '' ?>>Dropdown Select</option>
                                                                         <option value="text" <?= $attr['type'] === 'text' ? 'selected' : '' ?>>Text Input</option>
                                                                         <option value="number" <?= $attr['type'] === 'number' ? 'selected' : '' ?>>Numeric Number</option>
                                                                         <option value="textarea" <?= $attr['type'] === 'textarea' ? 'selected' : '' ?>>Text Area</option>
                                                                     </select>
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                     <label class="form-label small fw-semibold">Requirement Rule</label>
                                                                     <div class="form-check mt-2">
                                                                         <input class="form-check-input" type="checkbox" name="is_required" value="1" id="chkReq<?= $attr['id'] ?>" <?= !empty($attr['is_required']) ? 'checked' : '' ?>>
                                                                         <label class="form-check-label small" for="chkReq<?= $attr['id'] ?>">Required Attribute *</label>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                             <div class="mb-3">
                                                                 <label class="form-label small fw-semibold">Option Values (Comma Separated)</label>
                                                                 <?php
                                                                     $optsClean = !empty($attr['options_json']) ? json_decode($attr['options_json'], true) : [];
                                                                     $optsStr = is_array($optsClean) ? implode(', ', $optsClean) : e($attr['options_json'] ?? '');
                                                                 ?>
                                                                 <input type="text" name="options_json" class="form-control" value="<?= e($optsStr) ?>" placeholder="e.g. Red, Blue, Green, Yellow">
                                                             </div>
                                                             <div class="mb-3">
                                                                 <label class="form-label small fw-semibold">Default Selected Value</label>
                                                                 <input type="text" name="default_value" class="form-control" value="<?= e($attr['default_value'] ?? '') ?>" placeholder="e.g. Red">
                                                             </div>
                                                         </div>
                                                         <div class="modal-footer">
                                                             <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                             <button type="submit" class="btn btn-warning fw-bold">Update Attribute</button>
                                                         </div>
                                                     </form>
                                                 </div>
                                             </div>
                                         </div>
                                     </td>
                                 </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary">
                                    No dynamic attributes configured yet. Click <strong>Register New Attribute</strong> above to add one.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Register New Dynamic Attribute -->
<div class="modal fade" id="addAttributeModal" tabindex="-1" aria-labelledby="addAttributeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addAttributeModalLabel">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Register New Dynamic Attribute
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/attributes/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Select Attribute Family Group</label>
                            <select name="group_id" class="form-select">
                                <option value="">-- General (Default Group) --</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Input Control Type *</label>
                            <select name="type" id="createAttrTypeSelect" class="form-select" onchange="renderDynamicValueContainer()" required>
                                <option value="select">Interactive Pills / Dropdown Options</option>
                                <option value="text">Single Line Text</option>
                                <option value="number">Numeric Value</option>
                                <option value="textarea">Multi-line Description</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Attribute Name *</label>
                            <input type="text" name="name" id="attrNameInput" class="form-control" placeholder="e.g. Primary Color, Storage Capacity, RAM, Voltage" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">System Identifier Code (Key) *</label>
                            <div class="input-group">
                                <input type="text" name="code" id="attrCodeInput" class="form-control font-monospace" placeholder="color, storage, ram, voltage" required style="text-transform: lowercase;">
                                <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3" onclick="generateAutoAttrCode()" title="Auto-Generate Code Slug from Name">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> Auto Code
                                </button>
                            </div>
                            <small class="text-secondary" style="font-size: 0.75rem;">Unique identifier key used by system & product catalog.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Requirement Setting *</label>
                            <select name="is_required" class="form-select">
                                <option value="1" selected>Required Field * (Default - User MUST select/enter a value)</option>
                                <option value="0">Optional Field (Can be left blank)</option>
                            </select>
                        </div>

                        <!-- Dynamic Predefined Option Values / Default Value Builder (New Line Container for ALL Control Types) -->
                        <div class="col-12" id="options_builder_container">
                            <!-- Dynamically rendered via JS based on selected Input Control Type -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Register Attribute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Group -->
<div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fw-bold" id="addGroupModalLabel">
                    <i class="bi bi-folder-plus text-primary me-2"></i>Create Attribute Family Group
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('/attributes/groups/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Group / Family Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Apparel & Fashion, IT Hardware, Footwear" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Save Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateAutoAttrCode() {
    const name = document.getElementById('attrNameInput').value.trim();
    if (name) {
        const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        document.getElementById('attrCodeInput').value = slug;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('attrNameInput');
    const codeInput = document.getElementById('attrCodeInput');
    if (nameInput && codeInput) {
        nameInput.addEventListener('input', function() {
            if (!codeInput.dataset.userEdited) {
                generateAutoAttrCode();
            }
        });
        codeInput.addEventListener('input', function() {
            codeInput.dataset.userEdited = 'true';
        });
    }

let optionCounter = 0;

function renderDynamicValueContainer() {
    const typeSelect = document.getElementById('createAttrTypeSelect');
    const optionsContainer = document.getElementById('options_builder_container');
    if (!optionsContainer || !typeSelect) return;
    const type = typeSelect.value;

    if (type === 'select') {
        optionsContainer.innerHTML = `
            <div class="p-3.5 rounded-3 shadow-sm mb-2" style="background: rgba(245, 158, 11, 0.08); border: 1.5px solid rgba(245, 158, 11, 0.3);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label text-warning small fw-bold mb-0">
                        <i class="bi bi-list-stars me-1.5"></i>Interactive Dropdown Options & Choice Pills Builder
                    </label>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold shadow-sm" id="addOptionRowBtn">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add Option Value Choice
                    </button>
                </div>
                <div id="optionRowsList" class="d-flex flex-column gap-2 mb-2"></div>
                <small class="text-secondary d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i>Add option choices for product forms. Radio button sets the <strong>Pre-selected Default Option</strong>.
                </small>
            </div>
            <input type="hidden" name="default_value" id="hiddenDefaultValueInput">
        `;
        const addBtn = document.getElementById('addOptionRowBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                addOptionRow('', false);
            });
        }
        optionCounter = 0;
        addOptionRow('Standard Option 1', true);
        addOptionRow('Option Choice 2', false);
    } else if (type === 'number') {
        optionsContainer.innerHTML = `
            <div class="p-3.5 rounded-3 shadow-sm mb-2" style="background: rgba(59, 130, 246, 0.08); border: 1.5px solid rgba(59, 130, 246, 0.3);">
                <label class="form-label text-info small fw-bold mb-2">
                    <i class="bi bi-123 me-1.5"></i>Numeric Value Input Field Preview
                </label>
                <input type="number" step="any" name="default_value" class="form-control mb-2 fw-bold" placeholder="e.g. 100, 220.50 (Numeric default value)">
                <small class="text-secondary d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i>Single numeric number field rendered when creating products.
                </small>
            </div>
        `;
    } else if (type === 'textarea') {
        optionsContainer.innerHTML = `
            <div class="p-3.5 rounded-3 shadow-sm mb-2" style="background: rgba(168, 85, 247, 0.08); border: 1.5px solid rgba(168, 85, 247, 0.3);">
                <label class="form-label text-purple small fw-bold mb-2" style="color: #c084fc;">
                    <i class="bi bi-card-text me-1.5"></i>Multi-Line Description Textarea Preview (3 Lines)
                </label>
                <textarea name="default_value" class="form-control mb-2" rows="3" placeholder="Type multi-line description or notes template here..."></textarea>
                <small class="text-secondary d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i>Multi-line text area box rendered when creating products.
                </small>
            </div>
        `;
    } else {
        // text (Single Line Text)
        optionsContainer.innerHTML = `
            <div class="p-3.5 rounded-3 shadow-sm mb-2" style="background: rgba(16, 185, 129, 0.08); border: 1.5px solid rgba(16, 185, 129, 0.3);">
                <label class="form-label text-success small fw-bold mb-2">
                    <i class="bi bi-fonts me-1.5"></i>Single Line Text Input Field Preview
                </label>
                <input type="text" name="default_value" class="form-control mb-2" placeholder="e.g. Standard Text, Model Name, N/A">
                <small class="text-secondary d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i>Single line text input field rendered when creating products.
                </small>
            </div>
        `;
    }
}

    function addOptionRow(val = '', isDefault = false) {
        const optionRowsList = document.getElementById('optionRowsList');
        if (!optionRowsList) return;
        optionCounter++;

        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 option-row-item';
        row.innerHTML = `
            <div class="form-check mb-0 me-1" title="Set as Default Selected Option">
                <input class="form-check-input default-radio-choice" type="radio" name="default_option_radio" value="${optionCounter}" ${isDefault || optionCounter === 1 ? 'checked' : ''}>
            </div>
            <input type="text" name="options[]" class="form-control form-control-sm option-value-input text-light" placeholder="e.g. Option Choice ${optionCounter}" value="${escapeHtml(val)}" required>
            <button type="button" class="btn btn-outline-danger btn-sm remove-option-btn px-2" title="Remove Option">
                <i class="bi bi-trash-fill"></i>
            </button>
        `;

        row.querySelector('.remove-option-btn').addEventListener('click', function() {
            if (optionRowsList.querySelectorAll('.option-row-item').length > 1) {
                row.remove();
                updateDefaultSelectedValue();
            } else {
                alert('At least one option row is required for interactive dropdown choices.');
            }
        });

        row.querySelector('.option-value-input').addEventListener('input', updateDefaultSelectedValue);
        row.querySelector('.default-radio-choice').addEventListener('change', updateDefaultSelectedValue);

        optionRowsList.appendChild(row);
        updateDefaultSelectedValue();
    }

    function updateDefaultSelectedValue() {
        const optionRowsList = document.getElementById('optionRowsList');
        const defaultInput = document.getElementById('hiddenDefaultValueInput');
        if (!optionRowsList || !defaultInput) return;
        const selectedRadio = optionRowsList.querySelector('.default-radio-choice:checked');
        if (selectedRadio) {
            const parentRow = selectedRadio.closest('.option-row-item');
            if (parentRow) {
                const textInput = parentRow.querySelector('.option-value-input');
                if (textInput) {
                    defaultInput.value = textInput.value.trim();
                }
            }
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    const createTypeSel = document.getElementById('createAttrTypeSelect');
    if (createTypeSel) {
        createTypeSel.addEventListener('change', renderDynamicValueContainer);
    }

    if (nameInput && codeInput) {
        nameInput.addEventListener('input', function() {
            if (!codeInput.dataset.manuallyEdited) {
                codeInput.value = this.value.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_');
            }
        });

        codeInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = '1';
        });
    }

    // Initial render of dynamic container
    renderDynamicValueContainer();
});
</script>
