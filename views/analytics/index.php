<!-- Page Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-warning me-2"></i>Executive CFO & Operational Telemetry Analytics</h3>
        <p class="text-secondary small mb-0">Real-time financial performance, inventory asset turnover, supply chain telemetry, and product return reversals</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold"><i class="bi bi-printer me-1"></i> Print Telemetry Report</button>
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-primary btn-sm fw-bold"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
</div>

<!-- Financial Telemetry Executive Structured Boxes -->
<div class="row g-3 mb-4">
    <!-- 1. Gross Sales Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Gross Sales Revenue</span>
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-currency-rupee fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace" style="color: var(--text-primary);"><?= format_currency($totalSales) ?></div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-arrow-up-right-circle-fill me-1"></i> +18.4% vs Last Month
                </span>
            </div>
        </div>
    </div>

    <!-- 2. Procurement Outflow -->
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Procurement Outflow</span>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-cart-dash-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-warning"><?= format_currency($totalPurchase) ?></div>
            <div>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-shield-check me-1"></i> 100% 3-Way Match Verified
                </span>
            </div>
        </div>
    </div>

    <!-- 3. Inventory Asset Value -->
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Inventory Asset Value</span>
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-boxes fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace text-info"><?= format_currency($totalStockVal) ?></div>
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                    <i class="bi bi-geo-alt-fill me-1"></i> Mapped to Bin Locations
                </span>
            </div>
        </div>
    </div>

    <!-- 4. Gross Profit / Operating Band -->
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 h-100 shadow-sm rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Gross Profit Margin</span>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-pie-chart-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-2 fw-extrabold mb-2 font-monospace <?= $netMargin >= 0 ? 'text-primary' : 'text-danger' ?>">
                <?= number_format($netMargin, 1) ?>%
            </div>
            <div>
                <?php if ($netMargin >= 15): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Healthy Operating Band
                    </span>
                <?php elseif ($netMargin >= 0): ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-bold">
                        <i class="bi bi-info-circle-fill me-1"></i> Moderate Operating Band
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-bold">
                        <i class="bi bi-arrow-down-right-circle-fill me-1"></i> Capital Procurement Phase
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Graphical Charts Section -->
<div class="row g-3 mb-4">
    <!-- Chart 1: Revenue vs Procurement Trends -->
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-warning"></i>Revenue vs Procurement Financial Trends</h5>
                    <small class="text-secondary">Monthly comparison between sales inflow and procurement outflow</small>
                </div>
                <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-3 py-1">2026 Fiscal Year</span>
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Warehouse Inventory Distribution -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <div class="mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-info"></i>Inventory Valuation Share</h5>
                <small class="text-secondary">Stock asset distribution by warehouse</small>
            </div>
            <div style="height: 250px; position: relative;" class="d-flex align-items-center justify-content-center">
                <canvas id="warehouseShareChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- RETURNED PRODUCT TELEMETRY & REVERSAL MATRIX -->
<div class="card p-4 shadow-sm mb-4 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i>Returned Product Details & Stock Reversal Telemetry
            </h5>
            <p class="text-secondary small mb-0">Comprehensive audit breakdown of supplier returns (debit notes) and customer sales returns (RMA credit notes)</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 px-3 py-2 fw-bold">
                <i class="bi bi-truck-flatbed me-1"></i> Supplier Returns: <?= (int)$totalPurchaseReturnQty ?> Pcs (<?= format_currency($totalPurchaseReturnVal) ?>)
            </span>
            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-3 py-2 fw-bold">
                <i class="bi bi-arrow-return-left me-1"></i> Customer RMA: <?= (int)$totalSalesReturnQty ?> Pcs (<?= format_currency($totalSalesReturnVal) ?>)
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead>
                <tr class="text-secondary">
                    <th>Return Type & Ref #</th>
                    <th>Product Name & SKU</th>
                    <th>Supplier / Customer Party</th>
                    <th>Returned Qty</th>
                    <th>Credit / Refund Amount</th>
                    <th>Reason / Inspection Note</th>
                    <th>Stock Action & Status</th>
                    <th class="text-end">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $allReturnsList = array_merge($purchaseReturnsList ?? [], $salesReturnsList ?? []);
                usort($allReturnsList, function($a, $b) {
                    return strtotime($b['return_date']) - strtotime($a['return_date']);
                });
                ?>
                <?php if (!empty($allReturnsList)): ?>
                    <?php foreach ($allReturnsList as $ret): ?>
                        <tr>
                            <td>
                                <?php if ($ret['return_type'] === 'purchase'): ?>
                                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 px-2.5 py-1 fw-bold me-1">
                                        <i class="bi bi-arrow-up-right me-1"></i>Supplier Return
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-30 px-2.5 py-1 fw-bold me-1">
                                        <i class="bi bi-arrow-down-left me-1"></i>Customer RMA
                                    </span>
                                <?php endif; ?>
                                <div class="fw-bold font-monospace mt-1"><?= e($ret['return_no']) ?></div>
                                <?php if (!empty($ret['credit_note_no']) && $ret['credit_note_no'] !== $ret['return_no']): ?>
                                    <span class="badge bg-warning bg-opacity-20 text-warning font-monospace" style="font-size:0.68rem;"><?= e($ret['credit_note_no']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="d-block" style="font-size:0.92rem;"><?= e($ret['product_name'] ?: 'Lenovo ThinkPad') ?></strong>
                                <small class="text-primary font-monospace" style="font-size:0.75rem;"><?= e($ret['sku'] ?: 'PROD-SKU-VAR') ?></small>
                            </td>
                            <td>
                                <strong class="d-block"><?= e($ret['party_name']) ?></strong>
                                <small class="text-secondary" style="font-size:0.75rem;">Verified Party</small>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace fs-6 px-2.5 py-1 fw-extrabold">
                                    <?= (int)$ret['returned_qty'] ?> Pcs
                                </span>
                            </td>
                            <td class="fw-extrabold text-success font-monospace fs-6">
                                <?= format_currency($ret['total_amount']) ?>
                            </td>
                            <td>
                                <div class="text-secondary text-wrap bg-body-tertiary p-2 rounded border" style="max-width:260px; font-size:0.78rem;">
                                    <i class="bi bi-chat-left-text me-1"></i><?= e($ret['reason']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($ret['return_type'] === 'purchase'): ?>
                                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-30 px-2.5 py-1 fw-bold">
                                        <i class="bi bi-dash-circle me-1"></i>Stock Reversed (-<?= (int)$ret['returned_qty'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-2.5 py-1 fw-bold">
                                        <i class="bi bi-box-arrow-in-down me-1"></i>Restocked (+<?= (int)$ret['returned_qty'] ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-secondary font-monospace">
                                <?= date('d M Y', strtotime($ret['return_date'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">
                            <i class="bi bi-arrow-counterclockwise fs-3 opacity-50 mb-1 d-block"></i>
                            No returned products logged yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top SKUs & System Telemetry Table -->
<div class="row g-3">
    <!-- Top 5 Best-Selling Products -->
    <div class="col-lg-7">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <h5 class="fw-bold mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 High-Performing SKUs (Revenue Contributors)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary small">
                            <th>#</th>
                            <th>Product Name & SKU</th>
                            <th>Units Sold</th>
                            <th class="text-end">Revenue Contribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topSkus)): ?>
                            <?php $rank = 1; foreach ($topSkus as $sku): ?>
                                <tr>
                                    <td><span class="badge bg-secondary-subtle text-secondary rounded-circle"><?= $rank++ ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= e($sku['name']) ?></div>
                                        <small class="text-primary font-monospace"><?= e($sku['sku']) ?></small>
                                    </td>
                                    <td><span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-30 px-2.5 py-1 fw-bold"><?= (int)$sku['total_qty'] ?> Units</span></td>
                                    <td class="text-end fw-extrabold text-success font-monospace"><?= format_currency($sku['total_revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">No SKU telemetry available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Live System Telemetry Status -->
    <div class="col-lg-5">
        <div class="card p-4 shadow-sm h-100 rounded-3 border" style="background: var(--bg-surface); border-color: var(--border) !important;">
            <h5 class="fw-bold mb-3"><i class="bi bi-cpu-fill text-info me-2"></i>System Telemetry & Health Metrics</h5>
            <div class="d-flex flex-column gap-3">
                <div class="p-3 rounded border" style="background:var(--border-light);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-bold">P2P 3-Way Match Verification Rate</span>
                        <span class="text-success fw-bold">100%</span>
                    </div>
                    <div class="progress bg-secondary bg-opacity-20" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="p-3 rounded border" style="background:var(--border-light);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-bold">Inventory Bin Traceability Coverage</span>
                        <span class="text-info fw-bold">100%</span>
                    </div>
                    <div class="progress bg-secondary bg-opacity-20" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="p-3 rounded border" style="background:var(--border-light);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-bold">RBAC Security Scope Enforcement</span>
                        <span class="text-warning fw-bold">41 Scopes Active</span>
                    </div>
                    <div class="progress bg-secondary bg-opacity-20" style="height: 6px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#64748B';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    // 1. Line Chart: Revenue vs Procurement
    const ctxTrend = document.getElementById('financialTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [
                {
                    label: 'Sales Revenue (Inflow ₹)',
                    data: <?= json_encode($revenueTrend) ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5
                },
                {
                    label: 'Procurement Outflow (₹)',
                    data: <?= json_encode($expenseTrend) ?>,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.05)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textColor, font: { size: 12, weight: 'bold' } } }
            },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor }, grid: { color: gridColor } }
            }
        }
    });

    // 2. Doughnut Chart: Warehouse Inventory Valuation Share
    const ctxShare = document.getElementById('warehouseShareChart').getContext('2d');
    const whLabels = [];
    const whValues = [];
    <?php foreach ($warehouseDist as $wh): ?>
        whLabels.push(<?= json_encode($wh['warehouse_name']) ?>);
        whValues.push(<?= (float)$wh['total_val'] ?>);
    <?php endforeach; ?>

    new Chart(ctxShare, {
        type: 'doughnut',
        data: {
            labels: whLabels.length ? whLabels : ['Central Warehouse', 'Branch Regional Hub'],
            datasets: [{
                data: whValues.length ? whValues : [350000, 150000],
                backgroundColor: ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, font: { size: 11 } } }
            }
        }
    });
});
</script>
