<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> — Enterprise ERP</title>
    <meta name="description" content="Enterprise Multi-Branch Inventory & Procurement ERP System">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= time() ?>">
    <style>
    /* Critical Responsive Layout Shield */
    @media (max-width: 991.98px) {
        #wrapper {
            display: block !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }
        #sidebar-wrapper {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            height: 100vh !important;
            width: 280px !important;
            min-width: 280px !important;
            max-width: 280px !important;
            transform: translateX(-105%) !important;
            visibility: hidden !important;
            opacity: 0 !important;
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.28s ease, opacity 0.2s ease !important;
            z-index: 1060 !important;
            box-shadow: 0 0 35px rgba(0, 0, 0, 0.55) !important;
            background: var(--bg-sidebar) !important;
        }
        #sidebar-wrapper.mobile-open {
            transform: translateX(0) !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .sidebar-overlay {
            display: none;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: rgba(11, 17, 32, 0.72) !important;
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            z-index: 1055 !important;
            opacity: 0;
            transition: opacity 0.25s ease !important;
            cursor: pointer;
        }
        .sidebar-overlay.active {
            display: block !important;
            opacity: 1 !important;
        }
        #page-content-wrapper {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
        }
        .main-content-area {
            padding: 1.15rem 0.95rem !important;
        }
        .erp-topbar {
            padding: 0 0.85rem !important;
        }
    }
    </style>
    <script>
        (function() {
            var theme = localStorage.getItem('erp_theme_mode');
            if (theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();

        window.toggleErpSidebar = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            var sb = document.getElementById('sidebar-wrapper');
            if (!sb) return;
            sb.classList.toggle('collapsed');
            var isCol = sb.classList.contains('collapsed');
            try {
                localStorage.setItem('erp_sidebar_collapsed', isCol ? 'true' : 'false');
            } catch(err) {}
        };

        window.toggleErpMobileSidebar = function(e) {
            if (e) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
            }
            var sb = document.getElementById('sidebar-wrapper');
            var overlay = document.getElementById('sidebarOverlay');
            if (!sb) return;
            sb.classList.toggle('mobile-open');
            if (overlay) overlay.classList.toggle('active');
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="erp-body">

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="window.toggleErpMobileSidebar(event)"></div>

<div class="d-flex" id="wrapper">

    <!-- ═══════════════════════════════════ SIDEBAR ═══════════════════════════════════ -->
    <aside id="sidebar-wrapper">

        <!-- Logo Section -->
        <div class="sidebar-logo">
            <a href="<?= url('/dashboard') ?>" class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="bi bi-cpu-fill"></i></div>
                <div class="sidebar-brand-text">
                    <div class="brand-main">Enterprise<span>ERP</span></div>
                    <div class="brand-sub">Management Suite</div>
                </div>
            </a>
            <button type="button" class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarToggleDesktop" onclick="window.toggleErpSidebar(event)" title="Collapse Sidebar (Ctrl+B)">
                <i class="bi bi-layout-sidebar-inset fs-6"></i>
            </button>
            <button type="button" class="sidebar-toggle-btn d-lg-none" id="sidebarToggleMobileClose" onclick="window.toggleErpMobileSidebar(event)" title="Close Navigation Drawer">
                <i class="bi bi-x-lg fs-6 text-danger"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <?php $currUser = auth_user(); ?>
            <?php if (($currUser['role_name'] ?? '') === 'customer'): ?>
            <!-- Customer Self-Service Portal -->
            <div class="sidebar-category-label">Customer Self-Service Portal</div>
            <a href="<?= url('/customer-portal/dashboard') ?>" class="sidebar-nav-item <?= is_active('/customer-portal/dashboard') ?>" title="Customer Dashboard">
                <span class="nav-item-icon nav-icon-dashboard"><i class="bi bi-grid-fill"></i></span>
                <span class="nav-item-label">My Dashboard</span>
                <?php if (is_active('/customer-portal/dashboard')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/customer-portal/requests/create') ?>" class="sidebar-nav-item <?= is_active('/customer-portal/requests/create') ?>" title="Submit Order Request">
                <span class="nav-item-icon nav-icon-pr"><i class="bi bi-cart-plus-fill"></i></span>
                <span class="nav-item-label">Submit Order Request</span>
                <?php if (is_active('/customer-portal/requests/create')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/customer-portal/quotations') ?>" class="sidebar-nav-item <?= is_active('/customer-portal/quotations') ?>" title="My Quotations">
                <span class="nav-item-icon nav-icon-rfq"><i class="bi bi-file-earmark-check-fill"></i></span>
                <span class="nav-item-label">My Quotations</span>
                <?php if (is_active('/customer-portal/quotations')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/customer-portal/invoices') ?>" class="sidebar-nav-item <?= is_active('/customer-portal/invoices') || is_active('/customer-portal/payments') ?>" title="My Invoices & Payments">
                <span class="nav-item-icon nav-icon-invoices"><i class="bi bi-credit-card-2-front-fill"></i></span>
                <span class="nav-item-label">My Invoices & Payments</span>
                <?php if (is_active('/customer-portal/invoices') || is_active('/customer-portal/payments')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php else: ?>
            <!-- Executive Core -->
            <div class="sidebar-category-label">Executive Core</div>
            <a href="<?= url('/dashboard') ?>" class="sidebar-nav-item <?= is_active('/dashboard') ?>" title="Executive Dashboard">
                <span class="nav-item-icon nav-icon-dashboard"><i class="bi bi-grid-fill"></i></span>
                <span class="nav-item-label">Dashboard</span>
                <?php if (is_active('/dashboard')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>

            <?php if (has_permission('products.read') || has_permission('products.attributes')): ?>
            <!-- Catalog & Products -->
            <div class="sidebar-category-label">Catalog & Products</div>
            <?php if (has_permission('products.read')): ?>
            <a href="<?= url('/products') ?>" class="sidebar-nav-item <?= is_active('/products') ?>" title="Product Master">
                <span class="nav-item-icon nav-icon-products"><i class="bi bi-box-seam-fill"></i></span>
                <span class="nav-item-label">Product Master</span>
                <?php if (is_active('/products')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('products.attributes')): ?>
            <a href="<?= url('/attributes') ?>" class="sidebar-nav-item <?= is_active('/attributes') ?>" title="Dynamic Attributes">
                <span class="nav-item-icon nav-icon-attributes"><i class="bi bi-sliders"></i></span>
                <span class="nav-item-label">Attributes</span>
                <?php if (is_active('/attributes')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission('procurement.view_requests') || has_permission('procurement.view_rfqs') || has_permission('procurement.create_po') || has_permission('inventory.grn') || has_permission('finance.payments') || has_permission('procurement.returns')): ?>
            <!-- Procurement -->
            <div class="sidebar-category-label">Procurement (P2P)</div>
            <?php if (has_permission('procurement.view_requests') || has_permission('procurement.create_request')): ?>
            <a href="<?= url('/procurement/requests') ?>" class="sidebar-nav-item <?= is_active('/procurement/requests') ?>" title="Purchase Requisitions">
                <span class="nav-item-icon nav-icon-pr"><i class="bi bi-file-earmark-text-fill"></i></span>
                <span class="nav-item-label">Create New Request</span>
                <?php if (is_active('/procurement/requests')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('procurement.view_rfqs') || has_permission('procurement.create_rfq')): ?>
            <a href="<?= url('/procurement/rfqs') ?>" class="sidebar-nav-item <?= is_active('/procurement/rfqs') ?>" title="RFQ & Quotations">
                <span class="nav-item-icon nav-icon-rfq"><i class="bi bi-file-earmark-diff-fill"></i></span>
                <span class="nav-item-label">RFQ & Quotations</span>
                <?php if (is_active('/procurement/rfqs')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('procurement.create_po') || has_permission('procurement.approve_po')): ?>
            <a href="<?= url('/procurement/orders') ?>" class="sidebar-nav-item <?= is_active('/procurement/orders') ?>" title="Purchase Orders">
                <span class="nav-item-icon nav-icon-po"><i class="bi bi-bag-check-fill"></i></span>
                <span class="nav-item-label">Purchase Orders</span>
                <?php if (is_active('/procurement/orders')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('inventory.grn')): ?>
            <a href="<?= url('/procurement/grns') ?>" class="sidebar-nav-item <?= is_active('/procurement/grns') ?>" title="Goods Receipt">
                <span class="nav-item-icon nav-icon-grn"><i class="bi bi-truck-front-fill"></i></span>
                <span class="nav-item-label">Goods Receipt (GRN)</span>
                <?php if (is_active('/procurement/grns')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('finance.payments') || has_permission('procurement.create_po')): ?>
            <a href="<?= url('/procurement/invoices') ?>" class="sidebar-nav-item <?= is_active('/procurement/invoices') ?>" title="Vendor Invoices (3-Way Match)">
                <span class="nav-item-icon nav-icon-invoices"><i class="bi bi-receipt-cutoff"></i></span>
                <span class="nav-item-label">Vendor Invoices</span>
                <?php if (is_active('/procurement/invoices')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('finance.payments')): ?>
            <a href="<?= url('/procurement/payments') ?>" class="sidebar-nav-item <?= is_active('/procurement/payments') ?>" title="Vendor Payments">
                <span class="nav-item-icon nav-icon-payments"><i class="bi bi-bank2"></i></span>
                <span class="nav-item-label">Vendor Payments</span>
                <?php if (is_active('/procurement/payments')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('procurement.returns')): ?>
            <a href="<?= url('/procurement/returns') ?>" class="sidebar-nav-item <?= is_active('/procurement/returns') ?>" title="Purchase Returns">
                <span class="nav-item-icon nav-icon-returns"><i class="bi bi-arrow-counterclockwise"></i></span>
                <span class="nav-item-label">Purchase Returns</span>
                <?php if (is_active('/procurement/returns')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission('inventory.read') || has_permission('organization.manage') || has_permission('inventory.valuation') || has_permission('inventory.transfer')): ?>
            <!-- Inventory & Logistics -->
            <div class="sidebar-category-label">Inventory & Logistics</div>
            <?php if (has_permission('organization.manage') || has_permission('inventory.read')): ?>
            <a href="<?= url('/organization') ?>" class="sidebar-nav-item <?= is_active('/organization') ?>" title="Warehouses & Storage">
                <span class="nav-item-icon nav-icon-warehouses"><i class="bi bi-houses-fill"></i></span>
                <span class="nav-item-label">Warehouses</span>
                <?php if (is_active('/organization')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('inventory.read')): ?>
            <a href="<?= url('/inventory') ?>" class="sidebar-nav-item <?= is_active('/inventory') ?>" title="Bin Stock Levels & Ops">
                <span class="nav-item-icon nav-icon-stock"><i class="bi bi-layers-fill"></i></span>
                <span class="nav-item-label">Bin Stock & Ops</span>
                <?php if (is_active('/inventory')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/inventory/batches') ?>" class="sidebar-nav-item <?= is_active('/inventory/batches') ?>" title="Batch & Expiry Manager">
                <span class="nav-item-icon nav-icon-batches"><i class="bi bi-tags-fill"></i></span>
                <span class="nav-item-label">Batches & Expiry</span>
                <?php if (is_active('/inventory/batches')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/inventory/physical-verification') ?>" class="sidebar-nav-item <?= is_active('/inventory/physical-verification') ?>" title="Physical Verification Audit">
                <span class="nav-item-icon nav-icon-audit-check"><i class="bi bi-clipboard-check-fill"></i></span>
                <span class="nav-item-label">Physical Audit</span>
                <?php if (is_active('/inventory/physical-verification')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/inventory/ledger') ?>" class="sidebar-nav-item <?= is_active('/inventory/ledger') ?>" title="Stock Audit Ledger">
                <span class="nav-item-icon nav-icon-ledger"><i class="bi bi-journal-text"></i></span>
                <span class="nav-item-label">Stock Ledger</span>
                <?php if (is_active('/inventory/ledger')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('inventory.valuation')): ?>
            <a href="<?= url('/inventory/valuation') ?>" class="sidebar-nav-item <?= is_active('/inventory/valuation') ?>" title="Valuation Engine">
                <span class="nav-item-icon nav-icon-valuation"><i class="bi bi-calculator-fill"></i></span>
                <span class="nav-item-label">Valuation Engine</span>
                <?php if (is_active('/inventory/valuation')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('inventory.transfer')): ?>
            <a href="<?= url('/transfers') ?>" class="sidebar-nav-item <?= is_active('/transfers') ?>" title="Warehouse Transfers">
                <span class="nav-item-icon nav-icon-transfers"><i class="bi bi-arrow-left-right"></i></span>
                <span class="nav-item-label">Transfers</span>
                <?php if (is_active('/transfers')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission('customers.manage') || has_permission('sales.read') || has_permission('sales.create')): ?>
            <!-- Sales -->
            <div class="sidebar-category-label">Sales & Customers</div>
            <?php if (has_permission('customers.manage')): ?>
            <a href="<?= url('/customers') ?>" class="sidebar-nav-item <?= is_active('/customers') ?>" title="Customers">
                <span class="nav-item-icon nav-icon-customers"><i class="bi bi-people-fill"></i></span>
                <span class="nav-item-label">Customers</span>
                <?php if (is_active('/customers')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('sales.read') || has_permission('sales.create')): ?>
            <a href="<?= url('/sales') ?>" class="sidebar-nav-item <?= (is_active('/sales') || is_active('/sales/quotations') || is_active('/sales/invoices') || is_active('/sales/payments') || is_active('/sales/returns')) ? 'active' : '' ?>" title="Sales Orders">
                <span class="nav-item-icon nav-icon-sales"><i class="bi bi-cart-check-fill"></i></span>
                <span class="nav-item-label">Sales Orders</span>
                <?php if (is_active('/sales') || is_active('/sales/quotations') || is_active('/sales/invoices') || is_active('/sales/payments') || is_active('/sales/returns')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission('suppliers.manage') || has_permission('products.barcode') || has_permission('reports.view')): ?>
            <!-- Operations -->
            <div class="sidebar-category-label">Operations</div>
            <?php if (has_permission('suppliers.manage')): ?>
            <a href="<?= url('/suppliers') ?>" class="sidebar-nav-item <?= is_active('/suppliers') ?>" title="Suppliers & Vendors">
                <span class="nav-item-icon nav-icon-suppliers"><i class="bi bi-building-fill-gear"></i></span>
                <span class="nav-item-label">Suppliers & Vendors</span>
                <?php if (is_active('/suppliers')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('products.barcode')): ?>
            <a href="<?= url('/barcode') ?>" class="sidebar-nav-item <?= is_active('/barcode') ?>" title="Barcode Generator">
                <span class="nav-item-icon nav-icon-barcode"><i class="bi bi-qr-code"></i></span>
                <span class="nav-item-label">Barcode Generator</span>
                <?php if (is_active('/barcode')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('reports.view')): ?>
            <a href="<?= url('/analytics') ?>" class="sidebar-nav-item <?= is_active('/analytics') ?>" title="Executive CFO & Telemetry Analytics">
                <span class="nav-item-icon nav-icon-analytics"><i class="bi bi-graph-up-arrow"></i></span>
                <span class="nav-item-label">Executive Analytics</span>
                <?php if (is_active('/analytics')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/reports') ?>" class="sidebar-nav-item <?= is_active('/reports') ?>" title="Reports & Analytics">
                <span class="nav-item-icon nav-icon-reports"><i class="bi bi-bar-chart-line-fill"></i></span>
                <span class="nav-item-label">Reports & Analytics</span>
                <?php if (is_active('/reports')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/emails') ?>" class="sidebar-nav-item <?= is_active('/emails') ?>" title="System Email Outbox & Role Alerts">
                <span class="nav-item-icon nav-icon-emails"><i class="bi bi-envelope-at-fill"></i></span>
                <span class="nav-item-label">Email Outbox Logs</span>
                <?php if (is_active('/emails')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <a href="<?= url('/notifications') ?>" class="sidebar-nav-item <?= is_active('/notifications') ?>" title="Real-Time Notification & Workflow Alert Hub">
                <span class="nav-item-icon nav-icon-notifications"><i class="bi bi-bell-fill"></i></span>
                <span class="nav-item-label">Notification Hub</span>
                <?php if (is_active('/notifications')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission('users.read') || has_permission('roles.manage') || has_permission('audit.view') || (auth_user()['role_name'] ?? '') === 'super_admin'): ?>
            <!-- Administration -->
            <div class="sidebar-category-label">Administration</div>
            <?php if (has_permission('organization.manage')): ?>
            <a href="<?= url('/organization?tab=companies') ?>" class="sidebar-nav-item" title="Companies & Operating Branches">
                <span class="nav-item-icon nav-icon-org"><i class="bi bi-building-fill"></i></span>
                <span class="nav-item-label">Companies & Branches</span>
            </a>
            <?php endif; ?>
            <?php if ((auth_user()['role_name'] ?? '') === 'super_admin'): ?>
            <a href="<?= url('/super-admin') ?>" class="sidebar-nav-item <?= is_active('/super-admin') ?>" title="Super Admin Console">
                <span class="nav-item-icon nav-icon-superadmin"><i class="bi bi-shield-fill-check"></i></span>
                <span class="nav-item-label">Super Admin</span>
                <?php if (is_active('/super-admin')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('users.read')): ?>
            <a href="<?= url('/users') ?>" class="sidebar-nav-item <?= is_active('/users') ?>" title="Users Management">
                <span class="nav-item-icon nav-icon-users"><i class="bi bi-person-lines-fill"></i></span>
                <span class="nav-item-label">Users</span>
                <?php if (is_active('/users')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('roles.manage')): ?>
            <a href="<?= url('/roles') ?>" class="sidebar-nav-item <?= is_active('/roles') ?>" title="RBAC Security">
                <span class="nav-item-icon nav-icon-roles"><i class="bi bi-shield-lock-fill"></i></span>
                <span class="nav-item-label">Roles & Permissions</span>
                <?php if (is_active('/roles')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (has_permission('audit.view')): ?>
            <a href="<?= url('/audit') ?>" class="sidebar-nav-item <?= is_active('/audit') ?>" title="Audit Log">
                <span class="nav-item-icon nav-icon-audit"><i class="bi bi-eye-fill"></i></span>
                <span class="nav-item-label">Audit Log Trail</span>
                <?php if (is_active('/audit')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>
            <?php endif; ?>
            <?php endif; ?>

            <!-- System & Preferences -->
            <div class="sidebar-category-label">System & Preferences</div>
            <a href="<?= url('/settings') ?>" class="sidebar-nav-item <?= is_active('/settings') ?>" title="Master System Settings & Configuration">
                <span class="nav-item-icon nav-icon-settings"><i class="bi bi-gear-wide-connected"></i></span>
                <span class="nav-item-label">Settings</span>
                <?php if (is_active('/settings')): ?><span class="nav-active-badge">●</span><?php endif; ?>
            </a>

        </nav>

        <!-- Sidebar Footer — User Info -->
        <div class="sidebar-footer">
            <a href="<?= url('/profile') ?>" class="sidebar-footer-user text-decoration-none">
                <div class="user-avatar-circle"><?= strtoupper(substr(auth_user()['name'] ?? 'U', 0, 1)) ?></div>
                <div class="fu-info">
                    <div class="fu-name"><?= e(auth_user()['name'] ?? 'User') ?></div>
                    <div class="fu-role"><?= e(auth_user()['role_display'] ?? 'ERP User') ?></div>
                </div>
            </a>
        </div>

    </aside>

    <!-- ═══════════════════════════════ PAGE CONTENT ════════════════════════════════ -->
    <div id="page-content-wrapper">

        <!-- ─── TOPBAR ─── -->
        <header class="erp-topbar">
            <!-- Left: Mobile toggle + Desktop Sidebar Toggle + Universal Back Button + Title -->
            <div class="topbar-left d-flex align-items-center gap-2">
                <button class="topbar-mobile-toggle d-lg-none" id="sidebarToggleMobile" onclick="window.toggleErpMobileSidebar(event)" title="Open Navigation">
                    <i class="bi bi-list"></i>
                </button>
                <button type="button" class="topbar-icon-btn d-none d-lg-flex align-items-center justify-content-center" id="sidebarToggleTopbar" onclick="window.toggleErpSidebar(event)" title="Expand / Collapse Sidebar Menu (Ctrl+B)">
                    <i class="bi bi-layout-sidebar-inset text-primary fs-5"></i>
                </button>
                <button type="button" class="btn btn-gradient-primary btn-sm rounded-pill fw-extrabold px-3 py-1.5 text-nowrap d-flex align-items-center gap-2 shadow-lg text-white border-0 ms-1" onclick="if(document.referrer && document.referrer.indexOf(window.location.host) !== -1 && window.location.pathname !== '/dashboard' && window.location.pathname !== '/enterprise_erp/dashboard'){ history.back(); } else { window.location.href='<?= url('/dashboard') ?>'; }" title="Go Back to Previous Page" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 0 12px rgba(37, 99, 235, 0.5); font-size: 0.85rem; letter-spacing: 0.02em;">
                    <i class="bi bi-arrow-left-circle-fill text-warning fs-6"></i> <span class="fw-bold">Back</span>
                </button>
                <div class="d-none d-lg-flex align-items-center gap-2">
                    <div class="live-indicator d-none d-xl-flex">
                        <div class="live-dot"></div> ERP Live Node
                    </div>
                </div>
            </div>

            <!-- Center: Search -->
            <div class="topbar-center d-none d-md-block">
                <form action="<?= url('/search') ?>" method="GET" class="topbar-search-wrap">
                    <i class="bi bi-search search-icon-left"></i>
                    <input type="text" id="globalSearchInput" name="q" class="topbar-search-input" placeholder="Search SKU, POs, vendors, stock...">
                    <kbd class="topbar-search-kbd">⌘K</kbd>
                </form>
            </div>

            <!-- Right: Actions + User -->
            <div class="topbar-right">
                <!-- ─── BRIGHTNESS & THEME CONTROLLER ─── -->
                <div class="dropdown">
                    <button class="topbar-icon-btn dropdown-toggle no-caret" type="button" id="brightnessDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Theme & Brightness Settings">
                        <i class="bi bi-brightness-high-fill text-warning" id="brightnessIconHeader"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end brightness-panel mt-2 shadow-lg" aria-labelledby="brightnessDropdown">
                        <!-- Theme Mode Switcher -->
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <span class="fw-bold small"><i class="bi bi-palette-fill text-primary me-1.5"></i>Appearance Theme</span>
                        </div>
                        <div class="theme-switch-wrap">
                            <button type="button" class="theme-switch-btn active" data-theme-mode="light" id="themeLightBtn">
                                <i class="bi bi-sun-fill text-warning"></i> Light
                            </button>
                            <button type="button" class="theme-switch-btn" data-theme-mode="dark" id="themeDarkBtn">
                                <i class="bi bi-moon-stars-fill text-info"></i> Dark
                            </button>
                            <button type="button" class="theme-switch-btn" data-theme-mode="auto" id="themeAutoBtn">
                                <i class="bi bi-display"></i> Auto
                            </button>
                        </div>

                        <!-- Brightness Slider -->
                        <div class="d-flex justify-content-between align-items-center mb-1 pt-2 border-top">
                            <span class="fw-bold small"><i class="bi bi-sun-fill text-warning me-1.5"></i>Display Brightness</span>
                            <span class="badge bg-primary" id="brightnessPercentLabel">100%</span>
                        </div>
                        <small class="text-secondary d-block mb-2" style="font-size:0.7rem;">Slide to adjust screen light level from high to low</small>
                        
                        <div class="brightness-slider-wrap">
                            <i class="bi bi-moon-stars text-secondary" style="font-size:12px;"></i>
                            <input type="range" id="brightnessRangeInput" min="60" max="100" value="100" step="1">
                            <i class="bi bi-brightness-high text-warning" style="font-size:16px;"></i>
                        </div>

                        <div class="d-flex justify-content-between gap-1 mt-2">
                            <button type="button" class="brightness-preset-btn active" data-preset="100" data-warm="0">
                                <i class="bi bi-sun"></i> High (100%)
                            </button>
                            <button type="button" class="brightness-preset-btn" data-preset="90" data-warm="0">
                                <i class="bi bi-cloud-sun"></i> Mid (90%)
                            </button>
                            <button type="button" class="brightness-preset-btn" data-preset="78" data-warm="0">
                                <i class="bi bi-moon"></i> Low (78%)
                            </button>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <button type="button" class="brightness-preset-btn w-100 justify-content-center" data-preset="92" data-warm="18">
                                <i class="bi bi-cup-hot-fill text-warning"></i> Soft Eye Comfort (Warm Tone)
                            </button>
                        </div>
                    </div>
                </div>

                <?php
                $db = \App\Core\Database::getInstance();
                $currUser = auth_user();
                $roleTarget = '';
                if ($currUser) {
                    if (($currUser['role_name'] ?? '') === 'customer') {
                        $roleTarget = 'customer';
                    } else {
                        $roleTarget = 'sales';
                    }
                }

                $notifList = [];
                $unreadCount = 0;
                if ($currUser) {
                    $notifQuery = $db->prepare("
                        SELECT * FROM notifications 
                        WHERE role_target = :role OR user_id = :uid
                        ORDER BY id DESC LIMIT 10
                    ");
                    $notifQuery->execute([
                        'role' => $roleTarget,
                        'uid' => $currUser['id'] ?? 0
                    ]);
                    $notifList = $notifQuery->fetchAll();
                    
                    foreach ($notifList as $n) {
                        if (!(int)$n['is_read']) {
                            $unreadCount++;
                        }
                    }
                }
                ?>
                <!-- ─── NOTIFICATIONS DROPDOWN ─── -->
                <div class="dropdown">
                    <button class="topbar-icon-btn dropdown-toggle no-caret" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($unreadCount > 0): ?>
                            <div class="notif-dot" id="notifDotBadge"></div>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notifications-panel mt-2 shadow-lg" aria-labelledby="notifDropdown">
                        <div class="notif-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold small mb-0"><i class="bi bi-bell-fill text-primary me-1"></i>Notifications</span>
                                <span class="badge bg-primary rounded-pill" id="notifUnreadBadge"><?= $unreadCount ?> New</span>
                            </div>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="markAllReadBtn" style="font-size:0.75rem;">
                                Mark all read
                            </button>
                        </div>
                        
                        <div class="notif-body" id="notifListContainer" style="max-height: 360px; overflow-y: auto;">
                            <?php if (!empty($notifList)): ?>
                                <?php foreach ($notifList as $n): ?>
                                    <?php 
                                    $isUnread = !(int)$n['is_read'];
                                    // Dynamic styling based on titles
                                    $bg = 'rgba(67,97,238,0.1)'; $color = '#4361EE'; $icon = 'bi-info-circle-fill';
                                    $titleLower = strtolower($n['title']);
                                    if (strpos($titleLower, 'reject') !== false || strpos($titleLower, 'cancel') !== false) {
                                        $bg = 'rgba(239,68,68,0.1)'; $color = '#EF4444'; $icon = 'bi-x-circle-fill';
                                    } elseif (strpos($titleLower, 'payment') !== false || strpos($titleLower, 'paid') !== false) {
                                        $bg = 'rgba(16,185,129,0.1)'; $color = '#10B981'; $icon = 'bi-check-circle-fill';
                                    } elseif (strpos($titleLower, 'dispatch') !== false || strpos($titleLower, 'delivery') !== false) {
                                        $bg = 'rgba(245,158,11,0.1)'; $color = '#F59E0B'; $icon = 'bi-truck';
                                    } elseif (strpos($titleLower, 'quote') !== false || strpos($titleLower, 'quotation') !== false || strpos($titleLower, 'inquiry') !== false) {
                                        $bg = 'rgba(6,182,212,0.1)'; $color = '#06B6D4'; $icon = 'bi-file-earmark-text-fill';
                                    }
                                    ?>
                                    <a href="<?= url('/notifications/read/' . $n['id']) ?>" class="notif-item <?= $isUnread ? 'unread' : '' ?>">
                                        <div class="notif-icon-circle" style="background:<?= $bg ?>;color:<?= $color ?>;">
                                            <i class="bi <?= $icon ?>"></i>
                                        </div>
                                        <div class="notif-text">
                                            <div class="notif-title fw-bold"><?= e($n['title']) ?></div>
                                            <div class="notif-desc text-muted small"><?= e($n['message']) ?></div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-secondary small">
                                    <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                    No notifications yet.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="notif-footer text-center">
                            <a href="<?= url('/notifications') ?>" class="small fw-bold text-primary text-decoration-none">
                                View Notification Alert Hub <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <a href="<?= url('/profile') ?>" class="topbar-icon-btn" title="My Profile">
                    <i class="bi bi-person-circle"></i>
                </a>
                <div class="dropdown">
                    <button class="topbar-user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar-circle"><?= strtoupper(substr(auth_user()['name'] ?? 'U', 0, 1)) ?></div>
                        <div class="topbar-user-info d-none d-md-block">
                            <div class="u-name"><?= e(auth_user()['name'] ?? 'User') ?></div>
                            <div class="u-role"><?= e(auth_user()['role_display'] ?? '') ?></div>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end mt-2">
                        <li><div class="dropdown-header"><?= e(auth_user()['email'] ?? '') ?></div></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person-badge"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= url('/settings') ?>"><i class="bi bi-gear-wide-connected"></i> System Settings</a></li>
                        <li><a class="dropdown-item" href="<?= url('/change-password') ?>"><i class="bi bi-key"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="bi bi-box-arrow-right"></i> Log Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ─── FLASH ALERTS ─── -->
        <div class="px-3 px-md-4 pt-3" id="flash-alerts-container">
            <?php $flash = \App\Core\Session::getFlash('error') ?: \App\Core\Session::getFlash('success') ?: \App\Core\Session::getFlash('info'); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : ($flash['type'] === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill') ?>"></i>
                    <?= e($flash['value']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- ─── MAIN CONTENT ─── -->
        <main class="main-content-area">
            {{content}}
        </main>

    </div><!-- /#page-content-wrapper -->

</div><!-- /#wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
<script>
function toggleSidebarGroup(btn) {
    const group = btn.closest('.sidebar-nav-group');
    const subitems = group.querySelector('.sidebar-nav-subitems');
    const caret = btn.querySelector('.group-caret');
    if (subitems.style.display === 'none' || !subitems.style.display) {
        subitems.style.display = 'block';
        group.classList.add('open');
        caret.classList.replace('bi-chevron-down', 'bi-chevron-up');
    } else {
        subitems.style.display = 'none';
        group.classList.remove('open');
        caret.classList.replace('bi-chevron-up', 'bi-chevron-down');
    }
}
</script>
</body>
</html>
