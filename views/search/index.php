<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-search text-primary me-2"></i>Global ERP Search & Intelligence Hub
        </h3>
        <p class="text-secondary small mb-0">Fuzzy search across Products, POs, Suppliers, Customers, Sales Orders, Invoices & Barcodes</p>
    </div>
    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 fs-6 px-3 py-2">
        <i class="bi bi-cpu-fill me-1"></i> Total Matches Found: <?= number_format($totalMatches ?? 0) ?>
    </span>
</div>

<!-- ═══════════════════════════════════ SEARCH & FILTER CONTROL BAR ═══════════════════════════════════ -->
<div class="card p-4 shadow-sm mb-4 border-0">
    <form action="<?= url('/search') ?>" method="GET">
        <div class="row g-3 align-items-center">
            <!-- Search Query Input -->
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text text-primary"><i class="bi bi-search fs-5"></i></span>
                    <input type="text" name="q" class="form-control fw-bold py-2.5" placeholder="Enter product name, SKU, PO#, supplier, barcode..." value="<?= e($q) ?>" required autofocus>
                </div>
            </div>

            <!-- Scope Entity Filter -->
            <div class="col-md-3">
                <select name="type" class="form-select py-2.5 fw-bold">
                    <option value="all" <?= ($entityType ?? 'all') === 'all' ? 'selected' : '' ?>>🔍 All Entity Types</option>
                    <option value="products" <?= ($entityType ?? '') === 'products' ? 'selected' : '' ?>>📦 Products & SKUs (<?= $counts['products'] ?>)</option>
                    <option value="purchase_orders" <?= ($entityType ?? '') === 'purchase_orders' ? 'selected' : '' ?>>🛒 Purchase Orders (<?= $counts['purchase_orders'] ?>)</option>
                    <option value="suppliers" <?= ($entityType ?? '') === 'suppliers' ? 'selected' : '' ?>>🏬 Suppliers & Vendors (<?= $counts['suppliers'] ?>)</option>
                    <option value="customers" <?= ($entityType ?? '') === 'customers' ? 'selected' : '' ?>>👥 Customers & Clients (<?= $counts['customers'] ?>)</option>
                    <option value="sales_orders" <?= ($entityType ?? '') === 'sales_orders' ? 'selected' : '' ?>>💰 Sales Orders (<?= $counts['sales_orders'] ?>)</option>
                    <option value="invoices" <?= ($entityType ?? '') === 'invoices' ? 'selected' : '' ?>>🧾 Invoices (<?= $counts['invoices'] ?>)</option>
                    <option value="barcode" <?= ($entityType ?? '') === 'barcode' ? 'selected' : '' ?>>🔍 Barcode Direct Lookup (<?= $counts['barcode'] ?>)</option>
                </select>
            </div>

            <!-- Sorting Options -->
            <div class="col-md-2">
                <select name="sort" class="form-select py-2.5 fw-bold">
                    <option value="relevance" <?= ($sort ?? 'relevance') === 'relevance' ? 'selected' : '' ?>>⚡ Relevance</option>
                    <option value="name_asc" <?= ($sort ?? '') === 'name_asc' ? 'selected' : '' ?>>A to Z (Ascending)</option>
                    <option value="name_desc" <?= ($sort ?? '') === 'name_desc' ? 'selected' : '' ?>>Z to A (Descending)</option>
                    <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary fw-bold w-100 py-2.5 rounded-3">
                    <i class="bi bi-search me-1"></i> Search ERP
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════ ENTITY MATCH RESULTS SECTIONS ═══════════════════════════════════ -->
<?php if (empty($q)): ?>
    <div class="card p-5 text-center shadow-sm border-0">
        <i class="bi bi-search text-primary fs-1 mb-3"></i>
        <h4 class="fw-bold">Global ERP Fuzzy Search Engine</h4>
        <p class="text-secondary mb-0">Search across products, barcodes, purchase orders, vendors, clients, sales orders, and invoices.</p>
    </div>
<?php else: ?>

    <div class="row g-4">
        <!-- 1. Products Section -->
        <?php if ($entityType === 'all' || $entityType === 'products' || $entityType === 'barcode'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-box-seam-fill text-info me-2"></i>Products & Barcodes</h5>
                    <span class="badge bg-info bg-opacity-15 text-info fw-bold"><?= count($results['products']) ?> Results</span>
                </div>

                <?php if (!empty($results['products'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>SKU / Barcode</th><th>Product Name</th><th>Price</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['products'] as $p): ?>
                                    <tr>
                                        <td class="fw-bold text-info font-monospace"><?= e($p['sku']) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= e($p['name']) ?></div>
                                            <small class="text-secondary"><?= e($p['category_name'] ?: 'General') ?></small>
                                        </td>
                                        <td class="fw-bold text-success">₹<?= number_format($p['selling_rate'], 2) ?></td>
                                        <td>
                                            <a href="<?= url('/products/' . $p['id']) ?>" class="btn btn-outline-info btn-sm py-0 px-2 fw-bold"><i class="bi bi-eye"></i> View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No products matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 2. Purchase Orders Section -->
        <?php if ($entityType === 'all' || $entityType === 'purchase_orders'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bag-check-fill text-primary me-2"></i>Purchase Orders</h5>
                    <span class="badge bg-primary bg-opacity-15 text-primary fw-bold"><?= count($results['purchase_orders']) ?> Results</span>
                </div>

                <?php if (!empty($results['purchase_orders'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>PO Number</th><th>Supplier</th><th>Amount</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['purchase_orders'] as $po): ?>
                                    <tr>
                                        <td class="fw-bold text-primary font-monospace"><?= e($po['po_no']) ?></td>
                                        <td><?= e($po['supplier_name']) ?></td>
                                        <td class="fw-bold text-success">₹<?= number_format($po['total_amount'], 2) ?></td>
                                        <td><span class="badge bg-info bg-opacity-15 text-info text-capitalize"><?= e($po['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No purchase orders matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. Suppliers Section -->
        <?php if ($entityType === 'all' || $entityType === 'suppliers'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-building-fill-gear text-warning me-2"></i>Suppliers & Vendors</h5>
                    <span class="badge bg-warning bg-opacity-15 text-warning fw-bold"><?= count($results['suppliers']) ?> Results</span>
                </div>

                <?php if (!empty($results['suppliers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>Vendor Code</th><th>Name</th><th>Phone / Email</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['suppliers'] as $s): ?>
                                    <tr>
                                        <td class="fw-bold text-warning font-monospace"><?= e($s['code']) ?></td>
                                        <td class="fw-bold"><?= e($s['name']) ?></td>
                                        <td class="text-secondary"><?= e($s['phone'] ?: $s['email']) ?></td>
                                        <td><a href="<?= url('/suppliers') ?>" class="btn btn-outline-warning btn-sm py-0 px-2 fw-bold"><i class="bi bi-eye"></i> View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No suppliers matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 4. Customers Section -->
        <?php if ($entityType === 'all' || $entityType === 'customers'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-success me-2"></i>Customers & Clients</h5>
                    <span class="badge bg-success bg-opacity-15 text-success fw-bold"><?= count($results['customers']) ?> Results</span>
                </div>

                <?php if (!empty($results['customers'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>Customer Code</th><th>Name</th><th>Credit Limit</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['customers'] as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-success font-monospace"><?= e($c['code']) ?></td>
                                        <td class="fw-bold"><?= e($c['name']) ?></td>
                                        <td class="text-info">₹<?= number_format($c['credit_limit'], 2) ?></td>
                                        <td><a href="<?= url('/customers') ?>" class="btn btn-outline-success btn-sm py-0 px-2 fw-bold"><i class="bi bi-eye"></i> View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No customers matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 5. Sales Orders Section -->
        <?php if ($entityType === 'all' || $entityType === 'sales_orders'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-cart-check-fill text-purple me-2" style="color:#A855F7;"></i>Sales Orders</h5>
                    <span class="badge bg-purple bg-opacity-15 text-purple fw-bold" style="color:#A855F7;"><?= count($results['sales_orders']) ?> Results</span>
                </div>

                <?php if (!empty($results['sales_orders'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>Order No</th><th>Customer</th><th>Warehouse</th><th>Total Amount</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['sales_orders'] as $so): ?>
                                    <tr>
                                        <td class="fw-bold text-purple font-monospace" style="color:#A855F7;"><?= e($so['order_no']) ?></td>
                                        <td><?= e($so['customer_name']) ?></td>
                                        <td class="text-info"><?= e($so['warehouse_name']) ?></td>
                                        <td class="fw-extrabold text-success">₹<?= number_format($so['total_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No sales orders matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 6. Invoices Section -->
        <?php if ($entityType === 'all' || $entityType === 'invoices'): ?>
        <div class="col-lg-6">
            <div class="card p-4 shadow-sm h-100 border-0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-receipt-cutoff text-danger me-2"></i>Invoices</h5>
                    <span class="badge bg-danger bg-opacity-15 text-danger fw-bold"><?= count($results['invoices']) ?> Results</span>
                </div>

                <?php if (!empty($results['invoices'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr class="text-secondary"><th>Invoice No</th><th>Order Ref</th><th>Customer</th><th>Total Amount</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results['invoices'] as $inv): ?>
                                    <tr>
                                        <td class="fw-bold text-danger font-monospace"><?= e($inv['invoice_no']) ?></td>
                                        <td class="text-info"><?= e($inv['order_no']) ?></td>
                                        <td><?= e($inv['customer_name']) ?></td>
                                        <td class="fw-bold text-success">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-secondary">No invoices matching "<?= e($q) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══════════════════════════════════ PAGINATION CONTROLS ═══════════════════════════════════ -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center align-items-center gap-2 mt-4 pt-3 border-top">
            <?php if ($page > 1): ?>
                <a href="<?= url('/search?q=' . urlencode($q) . '&type=' . urlencode($entityType) . '&sort=' . urlencode($sort) . '&page=' . ($page - 1)) ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="bi bi-chevron-left me-1"></i> Previous Page
                </a>
            <?php endif; ?>

            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 fw-bold font-monospace">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>

            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/search?q=' . urlencode($q) . '&type=' . urlencode($entityType) . '&sort=' . urlencode($sort) . '&page=' . ($page + 1)) ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    Next Page <i class="bi bi-chevron-right ms-1"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
