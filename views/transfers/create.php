<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title"><i class="bi bi-truck text-primary me-2.5"></i>Initiate Inter-Warehouse Dispatch</h3>
        <p class="page-header-sub">Dispatch stock items from source to destination warehouse with vehicle tracking</p>
    </div>
    <div class="page-header-actions d-flex gap-2">
        <button type="button" class="btn btn-outline-warning btn-sm fw-bold" onclick="quickFillSampleTransfer()">
            <i class="bi bi-lightning-charge-fill me-1"></i> Quick Fill Dispatch
        </button>
        <a href="<?= url('/transfers') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Transfers
        </a>
    </div>
</div>

<div class="card shadow-sm border p-4">
    <form action="<?= url('/transfers/store') ?>" method="POST" id="transferForm" onsubmit="return validateWarehouses()">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Validation Alert Banner -->
        <div id="wh_error_alert" class="p-3 mb-4 rounded-3 bg-danger bg-opacity-15 border border-danger border-opacity-40 d-none">
            <div class="small text-danger fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-1.5"></i> Invalid Selection: Source and Destination Warehouse cannot be the same facility! Please pick two distinct warehouses.
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-houses text-info me-2"></i>Source & Destination Warehouse Logistics</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Source Warehouse (Dispatch Origin) *</label>
                <select name="source_warehouse_id" id="sourceWhSelect" class="form-select fw-semibold" onchange="validateWarehouses()" required>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Destination Warehouse (Receiving Restock Hub) *</label>
                <select name="dest_warehouse_id" id="destWhSelect" class="form-select fw-semibold" onchange="validateWarehouses()" required>
                    <?php foreach ($warehouses as $idx => $w): ?>
                        <option value="<?= $w['id'] ?>" <?= $idx === 1 ? 'selected' : '' ?>><?= e($w['name']) ?> (<?= e($w['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-truck text-warning me-2"></i>Logistics Vehicle & Transport Driver</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Vehicle Registration Number *</label>
                <div class="input-group">
                    <span class="input-group-text text-warning"><i class="bi bi-truck"></i></span>
                    <input type="text" name="vehicle_no" id="vehicleNoInput" class="form-control font-monospace fw-bold" placeholder="DL-01-AB-1234" value="TRK-DEL-8821" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Driver Name & Contact Number *</label>
                <div class="input-group">
                    <span class="input-group-text text-info"><i class="bi bi-person-badge"></i></span>
                    <input type="text" name="driver_name" id="driverNameInput" class="form-control fw-semibold" placeholder="Driver Name & Phone" value="Ramesh Singh (+91 9811223344)" required>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-box-seam text-primary me-2"></i>Transfer Material & Stock Quantity</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Product Item to Transfer *</label>
                <select name="product_id" id="transferProductSelect" class="form-select fw-semibold" required>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (SKU: <?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Transfer Quantity *</label>
                <div class="input-group">
                    <input type="number" name="qty" id="transferQtyInput" class="form-control text-center fw-bold fs-6" placeholder="2" min="1" required value="2">
                    <span class="input-group-text text-muted">Units</span>
                </div>
            </div>
        </div>

        <div class="pt-3 border-top d-flex justify-content-end gap-2">
            <a href="<?= url('/transfers') ?>" class="btn btn-outline-secondary btn-sm px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                <i class="bi bi-send-fill me-1"></i> Dispatch Transfer Shipment
            </button>
        </div>
    </form>
</div>

<script>
function validateWarehouses() {
    const src = document.getElementById('sourceWhSelect').value;
    const dest = document.getElementById('destWhSelect').value;
    const alertBox = document.getElementById('wh_error_alert');

    if (src && dest && src === dest) {
        alertBox.classList.remove('d-none');
        return false;
    } else {
        alertBox.classList.add('d-none');
        return true;
    }
}

function quickFillSampleTransfer() {
    const srcSelect = document.getElementById('sourceWhSelect');
    const destSelect = document.getElementById('destWhSelect');
    
    if (srcSelect && destSelect && srcSelect.options.length > 1) {
        srcSelect.selectedIndex = 0;
        destSelect.selectedIndex = 1;
        document.getElementById('vehicleNoInput').value = 'TRK-DEL-9942';
        document.getElementById('driverNameInput').value = 'Vikram Sharma (+91 9876543210)';
        document.getElementById('transferQtyInput').value = 5;
        validateWarehouses();
    }
}
</script>
