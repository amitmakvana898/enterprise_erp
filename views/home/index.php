<style>
/* ════════════════════════════════════════════════════════════════════
   SUPER ULTRA-MODERN SAAS DUAL-THEME LANDING SUITE
   ════════════════════════════════════════════════════════════════════ */

/* Hero Canvas Background with Soft Mesh Glows */
.ultra-hero-section {
    position: relative;
    padding: 6.5rem 0 5.5rem;
    background: linear-gradient(180deg, var(--bg-surface) 0%, var(--bg-page) 50%, var(--bg-page) 100%);
    overflow: hidden;
}

.ultra-mesh-glow-blue {
    position: absolute;
    top: -100px;
    left: 50%;
    transform: translateX(-50%);
    width: 900px;
    height: 500px;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.14) 0%, rgba(99, 102, 241, 0.08) 50%, rgba(0,0,0,0) 80%);
    filter: blur(80px);
    pointer-events: none;
}

.ultra-mesh-glow-cyan {
    position: absolute;
    bottom: -50px;
    right: 5%;
    width: 700px;
    height: 450px;
    background: radial-gradient(circle, rgba(14, 165, 233, 0.12) 0%, rgba(16, 185, 129, 0.06) 50%, rgba(0,0,0,0) 80%);
    filter: blur(90px);
    pointer-events: none;
}

/* Micro Tech Grid Lines */
.ultra-grid-pattern {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: 
        linear-gradient(to right, rgba(148, 163, 184, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
    background-size: 50px 50px;
    pointer-events: none;
}

/* Gradient Title Accent */
.ultra-gradient-text {
    background: linear-gradient(135deg, #2563EB 0%, #4F46E5 50%, #7C3AED 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Hero Status Pill Badge */
.ultra-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 20px;
    border-radius: 50px;
    background: var(--primary-light);
    border: 1px solid var(--border);
    color: var(--primary);
    font-size: 0.88rem;
    font-weight: 700;
    box-shadow: var(--shadow-sm);
}

.ultra-pulse-dot {
    width: 9px;
    height: 9px;
    background-color: #10B981;
    border-radius: 50%;
    box-shadow: 0 0 10px #10B981;
    position: relative;
}

.ultra-pulse-dot::after {
    content: '';
    position: absolute;
    top: -3px; left: -3px; right: -3px; bottom: -3px;
    border-radius: 50%;
    border: 2px solid #10B981;
    animation: pillPulse 1.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}

@keyframes pillPulse {
    0% { transform: scale(0.6); opacity: 1; }
    100% { transform: scale(2.2); opacity: 0; }
}

/* Theme Adaptive SaaS Cards */
.ultra-saas-card {
    background: var(--bg-surface) !important;
    border: 1px solid var(--border) !important;
    border-radius: 20px !important;
    box-shadow: var(--shadow-sm) !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.ultra-saas-card:hover {
    border-color: var(--primary) !important;
    transform: translateY(-4px);
    box-shadow: var(--shadow-md) !important;
}

/* Icon Container */
.ultra-icon-box {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    transition: transform 0.3s ease;
}

.ultra-saas-card:hover .ultra-icon-box {
    transform: scale(1.12) rotate(4deg);
}

/* Mockup Window Container */
.ultra-mockup-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 22px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.ultra-mockup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 22px;
    background: var(--bg-page);
    border-bottom: 1px solid var(--border);
}

.ultra-dot {
    width: 11px;
    height: 11px;
    border-radius: 50%;
}

.mockup-stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
}
</style>

<!-- ═══════════════════════════════════ HERO SECTION ═══════════════════════════════════ -->
<section class="ultra-hero-section">
    <div class="ultra-grid-pattern"></div>
    <div class="ultra-mesh-glow-blue"></div>
    <div class="ultra-mesh-glow-cyan"></div>

    <div class="container position-relative z-2">
        <div class="text-center max-w-950 mx-auto mb-5">
            <!-- Status Badge -->
            <div class="ultra-status-pill mb-4">
                <span class="ultra-pulse-dot"></span>
                <span>Enterprise ERP Suite v3.0 • Multi-Branch Operations Engine</span>
            </div>

            <!-- Main Headline -->
            <h1 style="font-family:'Outfit',sans-serif;font-size:clamp(2.5rem,5.6vw,4.5rem);font-weight:800;color:var(--text-primary);letter-spacing:-0.04em;line-height:1.08;margin-bottom:24px;">
                Real-Time Multi-Branch Logistics &<br><span class="ultra-gradient-text">Procure-to-Pay Engine</span>
            </h1>

            <!-- Subtitle -->
            <p style="font-size:1.2rem;color:var(--text-secondary);max-width:780px;margin:0 auto 40px;line-height:1.7;font-weight:500;">
                Empower your business with multi-company structure, 5-tier warehouse bin traceability, 11-stage automated Procure-to-Pay, 6-stage Order-to-Cash, and 41 granular RBAC security scopes.
            </p>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap justify-content-center gap-3.5 mb-5">
                <?php if (is_logged_in()): ?>
                    <a href="<?= url('/dashboard') ?>" class="btn btn-saas-primary btn-lg rounded-pill px-5 py-3.5" style="font-size:1.05rem;">
                        <i class="bi bi-grid-fill me-2 text-warning"></i> Open ERP Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="btn btn-saas-primary btn-lg rounded-pill px-5 py-3.5" style="font-size:1.05rem;">
                        <i class="bi bi-rocket-takeoff-fill me-2 text-warning"></i> Access ERP Workspace
                    </a>
                <?php endif; ?>
                <a href="<?= url('/about') ?>" class="btn btn-saas-outline btn-lg rounded-pill px-4.5 py-3.5" style="font-size:1.05rem;">
                    <i class="bi bi-cpu me-2 text-info"></i> System Architecture
                </a>
            </div>
        </div>

        <!-- Live System Telemetry Mockup Preview -->
        <div class="max-w-1000 mx-auto">
            <div class="ultra-mockup-card">
                <div class="ultra-mockup-header">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="ultra-dot" style="background:#EF4444;"></div>
                        <div class="ultra-dot" style="background:#F59E0B;"></div>
                        <div class="ultra-dot" style="background:#10B981;"></div>
                        <span class="ms-2.5 small font-monospace" style="font-size:0.8rem;color:var(--text-muted)!important;">enterprise_erp_suite_v3.0.node // Production Matrix</span>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 font-monospace" style="font-size:0.78rem;">
                        <i class="bi bi-shield-check me-1"></i>System Status: 100% Operational
                    </span>
                </div>
                <div class="p-4 p-md-5" style="background:var(--bg-page);">
                    <div class="row g-4 text-center">
                        <div class="col-6 col-md-3">
                            <div class="p-4 rounded-4 mockup-stat-card shadow-sm">
                                <small class="text-secondary d-block mb-1.5 fw-semibold">Available Stock</small>
                                <strong class="fs-3 text-primary font-monospace">410 Units</strong>
                                <small class="d-block text-success mt-1" style="font-size:0.75rem;"><i class="bi bi-check-circle me-1"></i>Auto Restocked</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-4 rounded-4 mockup-stat-card shadow-sm">
                                <small class="text-secondary d-block mb-1.5 fw-semibold">P2P Purchase Orders</small>
                                <strong class="fs-3 text-warning font-monospace">20 Orders</strong>
                                <small class="d-block text-info mt-1" style="font-size:0.75rem;"><i class="bi bi-check2-all me-1"></i>Approved POs</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-4 rounded-4 mockup-stat-card shadow-sm">
                                <small class="text-secondary d-block mb-1.5 fw-semibold">Sales Orders (O2C)</small>
                                <strong class="fs-3 text-success font-monospace">18 Orders</strong>
                                <small class="d-block text-warning mt-1" style="font-size:0.75rem;"><i class="bi bi-truck me-1"></i>Dispatched</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-4 rounded-4 mockup-stat-card shadow-sm">
                                <small class="text-secondary d-block mb-1.5 fw-semibold">Security Scopes</small>
                                <strong class="fs-3 text-info font-monospace">41 RBAC</strong>
                                <small class="d-block text-primary mt-1" style="font-size:0.75rem;"><i class="bi bi-shield-lock me-1"></i>100% Protected</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════ CORE MODULES SHOWCASE ═══════════════════════════════════ -->
<section id="modules" class="py-5 position-relative" style="background:var(--bg-page); border-top:1px solid var(--border);">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3.5 py-1.5 rounded-pill fw-bold mb-2">
                <i class="bi bi-grid-3x3-gap-fill me-1.5"></i> Comprehensive Enterprise Modules
            </span>
            <h2 style="font-family:'Outfit',sans-serif;font-weight:800;font-size:2.5rem;color:var(--text-primary);" class="mb-3">
                Engineered for Complete Operational Control
            </h2>
            <p style="color:var(--text-secondary);font-size:1.08rem;">
                Explore integrated modules designed for seamless logistics, procurement, sales, and RBAC security.
            </p>
        </div>

        <div class="row g-4">
            <!-- Module 1: Procurement P2P -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-warning" style="background:rgba(245, 158, 11, 0.15);border:1px solid rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">11-Stage Procure-to-Pay (P2P)</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Automates Purchase Requests, RFQs, Vendor Quotation Comparisons, Purchase Orders, GRNs, Quality Checks, Invoices, Payments, and Gate Passes.
                    </p>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1.5 fw-bold">P2P Workflow Engine</span>
                </div>
            </div>

            <!-- Module 2: Sales O2C -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-success" style="background:rgba(16, 185, 129, 0.15);border:1px solid rgba(16, 185, 129, 0.3);">
                        <i class="bi bi-cart-check-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">6-Stage Order-to-Cash (O2C)</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Seamless sales pipeline: Sales Quotations, Approved Orders, Delivery Challans, Tax Invoices, Payment Collections, and Automatic Return Restocking.
                    </p>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1.5 fw-bold">O2C Sales Pipeline</span>
                </div>
            </div>

            <!-- Module 3: 5-Tier Warehouse Bins -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-info" style="background:rgba(14, 165, 233, 0.15);border:1px solid rgba(14, 165, 233, 0.3);">
                        <i class="bi bi-layers-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">5-Tier Bin Storage Traceability</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Granular storage hierarchy tracking stock across Company ➔ Branch ➔ Warehouse ➔ Rack ➔ Bin Locations with zero stock leakage.
                    </p>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-1.5 fw-bold">Bin Level Inventory</span>
                </div>
            </div>

            <!-- Module 4: Dynamic EAV Attributes -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-primary" style="background:rgba(79, 110, 247, 0.15);border:1px solid rgba(79, 110, 247, 0.3);">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">Dynamic EAV Attribute Catalog</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Supports unlimited product specifications (RAM, Storage, Voltage, Material, IMEI) without requiring database schema alterations.
                    </p>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 fw-bold">EAV Specifications Engine</span>
                </div>
            </div>

            <!-- Module 5: RBAC Security Manager -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-danger" style="background:rgba(239, 68, 68, 0.15);border:1px solid rgba(239, 68, 68, 0.3);">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">41 Security Scopes (RBAC)</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Role-Based Access Control matrix supporting 8 distinct system roles with granular permission toggles and 1-click master overrides.
                    </p>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1.5 fw-bold">RBAC Access Control</span>
                </div>
            </div>

            <!-- Module 6: Audit Trail & Analytics -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="ultra-saas-card p-4.5 h-100">
                    <div class="ultra-icon-box mb-3 text-purple" style="background:rgba(168, 85, 247, 0.15);border:1px solid rgba(168, 85, 247, 0.3);color:#A855F7;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color:var(--text-primary);">Immutable Audit Trail</h5>
                    <p style="color:var(--text-secondary);font-size:0.94rem;line-height:1.6;" class="mb-3">
                        Logs every system event with IP address, user ID, module name, and payload details. Export audit reports instantly to CSV, Excel, or PDF.
                    </p>
                    <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 px-3 py-1.5 fw-bold" style="color:#A855F7;">Compliance & Auditing</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════ CALL TO ACTION ═══════════════════════════════════ -->
<section class="py-5 position-relative" style="background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%);">
    <div class="container text-center py-4">
        <div class="p-5 max-w-950 mx-auto rounded-4 shadow-lg border border-slate-700" style="background:rgba(255,255,255,0.03);">
            <h2 style="font-family:'Outfit',sans-serif;font-weight:800;font-size:2.6rem;color:#FFFFFF;" class="mb-3">
                Ready to Experience Enterprise ERP Suite v3.0?
            </h2>
            <p style="color:#CBD5E1;font-size:1.15rem;max-width:680px;" class="mx-auto mb-4">
                Sign in with system administrator credentials to access full ERP logistics and procurement pipelines.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3.5">
                <a href="<?= url('/login') ?>" class="btn btn-saas-primary btn-lg rounded-pill px-5 py-3.5 fw-bold text-white shadow-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Launch Application
                </a>
                <a href="<?= url('/about') ?>" class="btn btn-saas-outline btn-lg rounded-pill px-4.5 py-3.5 fw-bold" style="background:var(--bg-surface)!important;color:var(--text-primary)!important;border-color:var(--border)!important;">
                    <i class="bi bi-diagram-3 me-2 text-primary"></i> View System Blueprint
                </a>
            </div>
        </div>
    </div>
</section>
