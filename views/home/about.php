<!-- Hero About Header -->
<section class="py-5 text-center position-relative overflow-hidden">
    <div class="container py-4 position-relative z-1">
        <div class="d-inline-flex align-items-center gap-2 bg-primary bg-opacity-10 border border-primary border-opacity-25 px-4 py-2 rounded-pill text-primary fw-bold small mb-4 shadow-glow">
            <i class="bi bi-shield-check"></i> Enterprise Platform Overview & System Specification
        </div>
        <h1 class="display-3 font-heading fw-extrabold text-white mb-3 brand-gradient" style="line-height: 1.15; max-width: 960px; margin: 0 auto;">
            About Enterprise ERP Management Suite
        </h1>
        <p class="lead text-secondary mx-auto mb-5 fw-normal" style="max-width: 850px; font-size: 1.2rem;">
            Engineered using modern Object-Oriented PHP (MVC Pattern) to handle multi-company organizational hierarchies, automated Procure-to-Pay workflows, real-time bin level stock tracking, and RESTful API integrations.
        </p>

        <div class="row g-4 text-start justify-content-center">
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 text-center h-100">
                    <h2 class="font-heading fw-extrabold text-primary display-5 mb-1">100%</h2>
                    <div class="fw-bold text-white small">Custom PHP MVC</div>
                    <small class="text-secondary">No heavy third-party framework overhead</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 text-center h-100">
                    <h2 class="font-heading fw-extrabold text-info display-5 mb-1">3NF</h2>
                    <div class="fw-bold text-white small">Relational Database</div>
                    <small class="text-secondary">Normalized MySQL schema with foreign keys</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 text-center h-100">
                    <h2 class="font-heading fw-extrabold text-warning display-5 mb-1">8+</h2>
                    <div class="fw-bold text-white small">Role Security (RBAC)</div>
                    <small class="text-secondary">Granular permissions with 1-click controls</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 rounded-4 text-center h-100">
                    <h2 class="font-heading fw-extrabold text-success display-5 mb-1">JSON</h2>
                    <div class="fw-bold text-white small">REST API Engine</div>
                    <small class="text-secondary">HMAC SHA-256 JWT Authentication</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Architecture Deep Dive -->
<section class="py-5 bg-black bg-opacity-40 border-top border-bottom border-secondary border-opacity-25">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="font-heading fw-bold text-white mb-2 fs-2"><i class="bi bi-cpu-fill text-primary me-2"></i>System Architectural Foundation</h2>
            <p class="text-secondary">Designed adhering to SOLID design principles for high maintainability and security</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-primary text-white"><i class="bi bi-layers-fill"></i></div>
                        <h4 class="fw-bold text-white mb-0">Custom MVC Framework</h4>
                    </div>
                    <p class="text-secondary small">
                        The application is built on a clean, light Model-View-Controller pattern. HTTP Requests are captured by `App\Core\Request`, routed through `App\Core\Router` with CSRF and Auth Middleware layers, processed by dedicated Controllers, and rendered via `App\Core\View` with template buffer layouts.
                    </p>
                    <ul class="text-secondary small mb-0 ps-3">
                        <li><strong>Router</strong>: Handles RESTful path parameters (`/users/delete/{id}`).</li>
                        <li><strong>Middleware</strong>: Enforces session checks and anti-CSRF token verification.</li>
                        <li><strong>Database Layer</strong>: PDO prepared statements preventing SQL Injection.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-info text-white"><i class="bi bi-diagram-3-fill"></i></div>
                        <h4 class="fw-bold text-white mb-0">Multi-Level Storage Hierarchy</h4>
                    </div>
                    <p class="text-secondary small">
                        Eliminates warehouse ambiguity by tracking inventory across a 5-tier organizational structure:
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2">1. Company</span>
                        <span class="badge bg-info-subtle text-info border border-info px-3 py-2">2. Branch</span>
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2">3. Warehouse</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">4. Rack</span>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2">5. Bin Location</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-warning text-dark"><i class="bi bi-sliders"></i></div>
                        <h4 class="fw-bold text-white mb-0">Entity-Attribute-Value (EAV) Engine</h4>
                    </div>
                    <p class="text-secondary small">
                        Supports unlimited dynamic product specifications (RAM, SSD, Color, IMEI, Material, Operating Voltage) without requiring schema alteration. Attributes are mapped through `product_attribute_values` for flexible product cataloging.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle bg-danger text-white"><i class="bi bi-shield-lock-fill"></i></div>
                        <h4 class="fw-bold text-white mb-0">Enterprise Security & Audit Trail</h4>
                    </div>
                    <p class="text-secondary small">
                        Security is enforced at every layer using Password Hashing (Bcrypt), XSS escaping (`e()`), CSRF token validation (`CsrfMiddleware`), and real-time JSON diff audit logging (`audit_logs`) tracking IP address, User ID, Module, and Timestamps.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 text-center">
    <div class="container py-4">
        <div class="glass-card p-5 rounded-4 border border-primary border-opacity-25 shadow-glow" style="max-width: 850px; margin: 0 auto;">
            <h2 class="font-heading fw-extrabold text-white mb-3">Ready to Experience Enterprise ERP?</h2>
            <p class="text-secondary mb-4">Access executive dashboards, manage procurement workflows, and inspect bin stock locations in real-time.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= url('/login') ?>" class="btn btn-gradient-primary btn-lg px-5 py-3 rounded-3 fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i> Launch ERP Workspace</a>
                <a href="<?= url('/register') ?>" class="btn btn-outline-light btn-lg px-5 py-3 rounded-3 fw-bold">Create Account</a>
            </div>
        </div>
    </div>
</section>
