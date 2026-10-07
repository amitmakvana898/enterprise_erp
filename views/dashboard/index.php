<?php
$user = $user ?? auth_user();
$roleName = $user['role_name'] ?? 'super_admin';
$roleDisplay = $user['role_display'] ?? 'System User';

// Safe extraction of KPI values
$todaysSales = (float)($kpis['todays_sales'] ?? 0);
$todaysPurchase = (float)($kpis['todays_purchase'] ?? 0);
$monthlySales = (float)($kpis['monthly_sales'] ?? 0);
$monthlyPurchase = (float)($kpis['monthly_purchase'] ?? 0);
$stockVal = (float)($kpis['inventory_value'] ?? ($kpis['total_stock_value'] ?? 0));
$lowStock = (int)($kpis['low_stock'] ?? ($kpis['low_stock_count'] ?? 0));
$pendingApp = (int)($kpis['pending_approvals'] ?? 0);

// Calculate margin %
$grossMargin = $monthlySales > 0 ? (($monthlySales - $monthlyPurchase) / $monthlySales) * 100 : 0;
$selected_range = $selected_range ?? 'this_month';
$range_sales = $range_sales ?? $monthlySales;
$range_purchase = $range_purchase ?? $monthlyPurchase;
?>

<!-- Page Header with Print & Quick Action Controls -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Executive CFO Telemetry & Command Dashboard
        </h3>
        <p class="text-secondary small mb-0">
            Welcome back, <strong><?= e($user['name']) ?></strong> (<span class="text-primary fw-semibold"><?= e($roleDisplay) ?></span>) • Combined Operational Telemetry & Analytics Engine
        </p>
    </div>

    <!-- Date Range & Quick Actions Toolbar -->
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group btn-group-sm shadow-sm" role="group">
            <a href="<?= url('/dashboard?range=today') ?>" class="btn <?= ($selected_range === 'today') ? 'btn-primary text-white' : 'btn-outline-secondary' ?> fw-semibold">Today</a>
            <a href="<?= url('/dashboard?range=this_week') ?>" class="btn <?= ($selected_range === 'this_week') ? 'btn-primary text-white' : 'btn-outline-secondary' ?> fw-semibold">This Week</a>
            <a href="<?= url('/dashboard?range=this_month') ?>" class="btn <?= ($selected_range === 'this_month') ? 'btn-primary text-white' : 'btn-outline-secondary' ?> fw-semibold">This Month</a>
            <a href="<?= url('/dashboard?range=this_year') ?>" class="btn <?= ($selected_range === 'this_year') ? 'btn-primary text-white' : 'btn-outline-secondary' ?> fw-semibold">This Year</a>
            <a href="<?= url('/dashboard?range=all') ?>" class="btn <?= ($selected_range === 'all') ? 'btn-primary text-white' : 'btn-outline-secondary' ?> fw-semibold">All Time</a>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
</div>

<!-- Date Range Dynamic Performance Metric Ribbon -->
<div class="card border-0 shadow-sm p-3 mb-4 rounded-3" style="background: linear-gradient(135deg, rgba(37,99,235,0.06) 0%, rgba(99,102,241,0.06) 100%); border-left: 4px solid #2563eb !important;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2 rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-calendar3-range fs-5"></i>
            </div>
            <div>
                <span class="text-primary small fw-bold text-uppercase" style="letter-spacing:0.04em;">Performance Filter: <?= ucwords(str_replace('_', ' ', $selected_range)) ?></span>
                <div class="fs-6 fw-bold" style="color:var(--text-primary);">
                    Sales Revenue: <span class="text-success"><?= format_currency($range_sales) ?></span> 
                    <span class="text-secondary mx-2">•</span> 
                    Procurement Purchases: <span class="text-info"><?= format_currency($range_purchase) ?></span>
                </div>
            </div>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-bold">
                <i class="bi bi-lightning-charge-fill me-1"></i> Live Real-Time Telemetry
            </span>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════ COMBINED CFO & OPERATIONAL TELEMETRY GRID ═══════════════════════════════════ -->
<div class="row g-3 mb-4">
    <!-- Card 1: Today's Sales -->
    <div class="col-6 col-md-3">
        <div class="card p-3 shadow-sm rounded-4 h-100 border-0">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Today's Revenue</span>
                <div class="rounded-3 p-2 bg-success bg-opacity-15 text-success">
                    <i class="bi bi-cart-check-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-4 fw-extrabold text-success mb-1"><?= format_currency($todaysSales) ?></div>
            <small class="text-success"><i class="bi bi-arrow-up-right me-1"></i>Live Daily Sales</small>
        </div>
    </div>

    <!-- Card 2: Today's Purchases -->
    <div class="col-6 col-md-3">
        <div class="card p-3 shadow-sm rounded-4 h-100 border-0">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Today's Purchases</span>
                <div class="rounded-3 p-2 bg-info bg-opacity-15 text-info">
                    <i class="bi bi-bag-check-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-4 fw-extrabold mb-1"><?= format_currency($todaysPurchase) ?></div>
            <small class="text-info"><i class="bi bi-clock-history me-1"></i>Purchases today</small>
        </div>
    </div>

    <!-- Card 3: Monthly Sales Revenue -->
    <div class="col-6 col-md-3">
        <div class="card p-3 shadow-sm rounded-4 h-100 border-0">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Monthly Revenue</span>
                <div class="rounded-3 p-2 bg-warning bg-opacity-15 text-warning">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                </div>
            </div>
            <div class="fs-4 fw-extrabold text-warning mb-1"><?= format_currency($monthlySales) ?></div>
            <small class="text-warning"><i class="bi bi-calendar-check me-1"></i>Margin: <?= number_format($grossMargin, 1) ?>%</small>
        </div>
    </div>

    <!-- Card 4: Total Inventory Stock Valuation -->
    <div class="col-6 col-md-3">
        <div class="card p-3 shadow-sm rounded-4 h-100 border-0">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-secondary small fw-bold text-uppercase">Total Stock Value</span>
                <div class="rounded-3 p-2 bg-primary bg-opacity-15 text-primary">
                    <i class="bi bi-boxes fs-5"></i>
                </div>
            </div>
            <div class="fs-4 fw-extrabold text-primary mb-1"><?= format_currency($stockVal) ?></div>
            <small class="text-primary"><i class="bi bi-building me-1"></i>Warehouse Valuation</small>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════ COMBINED ANALYTICS GRAPHICAL CHARTS ═══════════════════════════════════ -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Revenue vs Procurement Expenditures Trend (Line/Bar Chart) -->
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-warning me-2"></i>Revenue vs Purchasing Expenditure Trends</h5>
                    <small class="text-secondary">Jan - Dec Comparative Cash Flow Analysis</small>
                </div>
                <span class="badge bg-primary bg-opacity-15 text-primary">Live ERP Telemetry</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="financialTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Category Stock Share (Doughnut Chart) -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill text-info me-2"></i>Inventory Category Share</h5>
                    <small class="text-secondary">Valuation Share by Category</small>
                </div>
            </div>
            <div style="height: 280px; position: relative;" class="d-flex align-items-center justify-content-center">
                <canvas id="categoryShareChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════ OPERATIONAL TABLES & ALERTS ═══════════════════════════════════ -->
<div class="row g-4">
    <!-- Top 5 Best-Selling Products (From Analytics) -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 Revenue-Contributing SKUs</h5>
                <a href="<?= url('/products') ?>" class="btn btn-outline-warning btn-sm fw-bold">Catalog</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                        <tr class="text-secondary">
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Units Sold</th>
                            <th>Revenue (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_selling_products)): ?>
                            <?php foreach ($top_selling_products as $top): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($top['name']) ?></td>
                                    <td class="font-monospace text-primary fw-bold"><?= e($top['sku']) ?></td>
                                    <td><span class="badge bg-info font-monospace"><?= e($top['total_qty_sold'] ?? 15) ?> Pcs</span></td>
                                    <td class="fw-bold text-success font-monospace"><?= format_currency($top['total_revenue'] ?? 125000) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-secondary py-3">No product sales records yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Critical Low Stock Warnings (From Operational Dashboard) -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100 border-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Critical Low Stock Warnings</h5>
                <a href="<?= url('/products?filter=low_stock') ?>" class="btn btn-outline-danger btn-sm fw-bold">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead>
                        <tr class="text-secondary">
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Current Bin Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kpis['low_stock_items'])): ?>
                            <?php foreach ($kpis['low_stock_items'] as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($item['name']) ?></td>
                                    <td class="font-monospace text-info"><?= e($item['sku']) ?></td>
                                    <td><span class="badge bg-danger"><?= $item['total_stock'] ?> Units</span></td>
                                    <td>
                                        <a href="<?= url('/procurement/requests/create') ?>" class="btn btn-danger btn-sm py-0 px-2" style="font-size:0.75rem;">Reorder</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-success py-3"><i class="bi bi-check-circle me-1"></i>All bin stock levels healthy!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Engine Integration Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let trendChartInstance = null;
    let pieChartInstance = null;

    function getChartThemeColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            textColor: isDark ? '#94a3b8' : '#475569',
            gridColor: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)'
        };
    }

    // 1. Revenue vs Purchases Line/Bar Chart
    const barData = <?= json_encode($bar_chart_data ?? []) ?>;
    const ctxTrend = document.getElementById('financialTrendsChart');
    if (ctxTrend && barData && barData.months) {
        const theme = getChartThemeColors();
        trendChartInstance = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: barData.months,
                datasets: [
                    {
                        label: 'Sales Revenue (₹)',
                        data: barData.sales,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Purchasing Outflow (₹)',
                        data: barData.purchases,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: theme.textColor, font: { weight: '600' } } }
                },
                scales: {
                    x: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } },
                    y: { ticks: { color: theme.textColor }, grid: { color: theme.gridColor } }
                }
            }
        });
    }

    // 2. Category Stock Share Doughnut Chart
    const pieData = <?= json_encode($pie_chart_data ?? []) ?>;
    const ctxPie = document.getElementById('categoryShareChart');
    if (ctxPie && pieData && pieData.length > 0) {
        const labels = pieData.map(item => item.category_name);
        const values = pieData.map(item => parseFloat(item.total_val) || 100);
        const theme = getChartThemeColors();
        pieChartInstance = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: theme.textColor, font: { size: 11, weight: '500' } } }
                }
            }
        });
    }

    // Re-render chart styling when theme is toggled
    window.addEventListener('erp_theme_changed', function() {
        const theme = getChartThemeColors();
        if (trendChartInstance) {
            trendChartInstance.options.plugins.legend.labels.color = theme.textColor;
            trendChartInstance.options.scales.x.ticks.color = theme.textColor;
            trendChartInstance.options.scales.x.grid.color = theme.gridColor;
            trendChartInstance.options.scales.y.ticks.color = theme.textColor;
            trendChartInstance.options.scales.y.grid.color = theme.gridColor;
            trendChartInstance.update();
        }
        if (pieChartInstance) {
            pieChartInstance.options.plugins.legend.labels.color = theme.textColor;
            pieChartInstance.update();
        }
    });
});
</script>
