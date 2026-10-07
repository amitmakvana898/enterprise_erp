<style>
@media print {
    #sidebar-wrapper, .erp-topbar, .no-print, .btn, form {
        display: none !important;
    }
    #page-content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        background: white !important;
        color: black !important;
    }
    table {
        color: black !important;
    }
    th, td {
        color: black !important;
        border-bottom: 1px solid #ddd !important;
    }
}
</style>

<!-- Page Header & Export Action Toolbar -->
<div class="page-header no-print">
    <div>
        <h3 class="page-header-title">
            <i class="bi bi-bar-chart-line-fill text-primary me-2.5"></i>Executive Filtering Reports & Export Suite
        </h3>
        <p class="page-header-sub">Multi-parameter filtered analytics with PDF, Excel, CSV, and Print formatting</p>
    </div>

    <!-- Export Action Options -->
    <div class="page-header-actions d-flex flex-wrap align-items-center gap-2">
        <a href="<?= url('/reports/gst') ?>" class="btn btn-warning text-dark btn-sm fw-bold shadow-sm" title="View GSTR-1, GSTR-3B and HSN Tax Breakdown">
            <i class="bi bi-receipt-cutoff me-1"></i> GST & Tax Returns (GSTR-1)
        </a>
        <a href="<?= url('/reports/export-pdf?' . http_build_query($_GET)) ?>" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
        </a>
        <a href="<?= url('/reports/export-excel?' . http_build_query($_GET)) ?>" class="btn btn-outline-success btn-sm fw-bold">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
        </a>
        <a href="<?= url('/reports/export-csv?' . http_build_query($_GET)) ?>" class="btn btn-outline-info btn-sm fw-bold">
            <i class="bi bi-file-earmark-text-fill me-1"></i> Export CSV
        </a>
        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════ MULTI-FILTER CONTROL PANEL ═══════════════════════════════════ -->
<div class="card p-4 shadow-sm mb-4 no-print border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-funnel-fill text-warning me-2"></i>Report Parameters & Filter Engine</h5>
        <a href="<?= url('/reports') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset All Filters</a>
    </div>

    <form action="<?= url('/reports') ?>" method="GET">
        <div class="row g-3 mb-3">
            <!-- Report Type -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Report Type *</label>
                <select name="report_type" class="form-select fw-bold" onchange="this.form.submit()">
                    <option value="inventory" <?= ($reportType ?? '') === 'inventory' ? 'selected' : '' ?>>📦 Inventory Valuation & Stock</option>
                    <option value="procurement" <?= ($reportType ?? '') === 'procurement' ? 'selected' : '' ?>>🛒 Procurement & PO Orders</option>
                    <option value="sales" <?= ($reportType ?? '') === 'sales' ? 'selected' : '' ?>>💰 Sales Orders & Revenue</option>
                    <option value="ledger" <?= ($reportType ?? '') === 'ledger' ? 'selected' : '' ?>>📋 Stock Audit Movement Ledger</option>
                </select>
            </div>

            <!-- Company Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">-- All Companies --</option>
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ((int)($filters['company_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Branch Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">-- All Branches --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ((int)($filters['branch_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Warehouse Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Warehouse Facility</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">-- All Warehouses --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= ((int)($filters['warehouse_id'] ?? 0) === (int)$w['id']) ? 'selected' : '' ?>><?= e($w['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Supplier Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Supplier / Vendor</label>
                <select name="supplier_id" class="form-select">
                    <option value="">-- All Suppliers --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ((int)($filters['supplier_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Product Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Product Item</label>
                <select name="product_id" class="form-select">
                    <option value="">-- All Products --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ((int)($filters['product_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Product Category</label>
                <select name="category_id" class="form-select">
                    <option value="">-- All Categories --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ((int)($filters['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-bold">Status Code</label>
                <select name="status" class="form-select">
                    <option value="">-- All Statuses --</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="delivered" <?= ($filters['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
            </div>
        </div>

        <div class="row g-3 align-items-end">
            <!-- Start Date -->
            <div class="col-md-4">
                <label class="form-label small fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= e($filters['start_date'] ?? '') ?>">
            </div>

            <!-- End Date -->
            <div class="col-md-4">
                <label class="form-label small fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= e($filters['end_date'] ?? '') ?>">
            </div>

            <!-- Submit Button -->
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary fw-bold w-100 py-2 rounded-3">
                    <i class="bi bi-funnel-fill me-1.5"></i> Apply Report Filters
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════ REPORT DATA RESULTS TABLE ═══════════════════════════════════ -->
<div class="card shadow-sm p-4 border-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-table text-info me-2"></i>
            <?= strtoupper(e($reportType)) ?> REPORT RESULTS (<?= count($reportData) ?> Records)
        </h5>
        <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-3 py-1.5 fs-6">Generated <?= date('d M Y, h:i A') ?></span>
    </div>

    <div class="table-responsive">
        <?php if ($reportType === 'procurement'): ?>
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr class="text-secondary">
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Created Date</th>
                        <th>Total Amount (₹)</th>
                        <th>Status</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary font-monospace"><?= e($row['po_no']) ?></td>
                                <td class="fw-semibold"><?= e($row['supplier_name']) ?></td>
                                <td class="text-secondary"><?= e($row['created_at']) ?></td>
                                <td class="fw-bold text-success fs-6"><?= format_currency($row['total_amount']) ?></td>
                                <td><span class="badge bg-info bg-opacity-15 text-info text-capitalize"><?= e($row['status']) ?></span></td>
                                <td><?= e($row['creator_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-secondary">No procurement order records match your selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php elseif ($reportType === 'sales'): ?>
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr class="text-secondary">
                        <th>SO Number</th>
                        <th>Customer Name</th>
                        <th>Warehouse</th>
                        <th>Order Date</th>
                        <th>Subtotal (₹)</th>
                        <th>GST Tax (₹)</th>
                        <th>Total Amount (₹)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td class="fw-bold text-warning font-monospace"><?= e($row['order_no']) ?></td>
                                <td class="fw-semibold"><?= e($row['customer_name']) ?></td>
                                <td class="text-info"><?= e($row['warehouse_name']) ?></td>
                                <td class="text-secondary"><?= e($row['order_date']) ?></td>
                                <td><?= format_currency($row['subtotal']) ?></td>
                                <td class="text-warning"><?= format_currency($row['tax_amount']) ?></td>
                                <td class="fw-bold text-success fs-6"><?= format_currency($row['total_amount']) ?></td>
                                <td><span class="badge bg-success bg-opacity-15 text-success text-capitalize"><?= e($row['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-4 text-secondary">No sales order records match your selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php elseif ($reportType === 'ledger'): ?>
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr class="text-secondary">
                        <th>Txn No</th>
                        <th>Product SKU</th>
                        <th>Warehouse & Bin</th>
                        <th>Txn Type</th>
                        <th>In Qty</th>
                        <th>Out Qty</th>
                        <th>Balance Qty</th>
                        <th>Unit Cost (₹)</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary font-monospace"><?= e($row['transaction_no']) ?></td>
                                <td class="fw-semibold"><?= e($row['product_name']) ?> <small class="text-primary font-monospace d-block"><?= e($row['sku']) ?></small></td>
                                <td><?= e($row['warehouse_name']) ?> (<span class="text-warning"><?= e($row['bin_code']) ?></span>)</td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($row['transaction_type']) ?></span></td>
                                <td class="fw-bold text-success"><?= $row['in_qty'] ?: '-' ?></td>
                                <td class="fw-bold text-danger"><?= $row['out_qty'] ?: '-' ?></td>
                                <td class="fw-bold fs-6"><?= $row['balance_qty'] ?></td>
                                <td><?= format_currency($row['unit_cost']) ?></td>
                                <td class="text-secondary"><?= e($row['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-4 text-secondary">No stock movement ledger records match your selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- Inventory Valuation (Default) -->
            <table class="table table-hover align-middle mb-0 small">
                <thead>
                    <tr class="text-secondary">
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Bin Location</th>
                        <th>Batch No</th>
                        <th>Total Quantity</th>
                        <th>Purchase Rate (₹)</th>
                        <th>Total Valuation (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary font-monospace"><?= e($row['sku']) ?></td>
                                <td class="fw-semibold"><?= e($row['product_name']) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary"><?= e($row['category_name'] ?: 'General') ?></span></td>
                                <td class="text-info"><?= e($row['warehouse_name']) ?></td>
                                <td><span class="badge bg-primary bg-opacity-15 text-primary"><?= e($row['bin_code']) ?></span></td>
                                <td class="text-warning fw-semibold font-monospace"><?= e($row['batch_no']) ?></td>
                                <td class="fw-bold text-success fs-6"><?= number_format($row['total_qty']) ?> Units</td>
                                <td class="text-warning"><?= format_currency($row['avg_unit_cost'] ?? 0) ?></td>
                                <td class="fw-extrabold text-success fs-6"><?= format_currency($row['total_valuation'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center py-4 text-secondary">No inventory valuation records match your selected filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
