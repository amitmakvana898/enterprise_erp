<div class="row justify-content-center align-items-center min-vh-100 g-0">
    <div class="col-md-5 col-lg-4">
        <div class="card p-4 shadow-lg bg-dark border-secondary rounded-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-20 text-primary rounded-circle mb-3" style="width:60px; height:60px;">
                    <i class="bi bi-person-badge-fill fs-2"></i>
                </div>
                <h3 class="fw-bold text-white mb-1">Customer Self-Service Portal</h3>
                <p class="text-secondary small mb-0">Track quotes, 1-click place sales orders, and view tax invoices</p>
            </div>

            <form action="<?= url('/customer-portal/login') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Customer Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control bg-dark text-light border-secondary" placeholder="customer@enterprise.com" required value="<?= e($customers[0]['email'] ?? 'customer@enterprise.com') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-bold">Portal Access Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="customer123" value="customer123" required>
                    </div>
                    <small class="text-secondary d-block mt-1" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1 text-info"></i> Demo Passcode: <strong class="text-warning font-monospace">customer123</strong></small>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow">
                    <i class="bi bi-box-arrow-in-right me-1.5"></i> Log In to Customer Portal
                </button>
            </form>

            <div class="mt-4 pt-3 border-top border-secondary text-center">
                <a href="<?= url('/dashboard') ?>" class="text-secondary small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Return to Staff ERP Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
