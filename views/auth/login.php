<form method="POST" action="<?= url('/login') ?>" novalidate id="loginForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">

    <div class="mb-3">
        <label class="form-label text-white-50 small fw-bold" for="email">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill text-primary"></i></span>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus style="font-size:0.95rem;">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label text-white-50 small fw-bold mb-0" for="password">Password</label>
            <a href="<?= url('/forgot-password') ?>" class="small text-info text-decoration-none fw-semibold" style="font-size:0.80rem;">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill text-primary"></i></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required style="font-size:0.95rem;">
            <button type="button" class="btn text-white-50 border-0 px-3 d-flex align-items-center justify-content-center" style="background:transparent;" onclick="togglePasswordVisibility('password', this)" title="Show / Hide Password">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="form-check">
            <input class="form-check-input bg-dark border-secondary" type="checkbox" id="remember" name="remember" checked style="cursor:pointer;">
            <label class="form-check-label text-white-50 small user-select-none" for="remember" style="cursor:pointer;">
                Keep me signed in
            </label>
        </div>
    </div>

    <button type="submit" class="btn btn-gradient-primary w-100 py-3 rounded-3 fw-bold text-white shadow-lg d-flex align-items-center justify-content-center gap-2" style="font-size:0.98rem;letter-spacing:0.02em;">
        <i class="bi bi-box-arrow-in-right fs-5"></i> <span>Sign In to ERP</span>
    </button>
</form>

<!-- 1-Click Quick Demo Login Chips -->
<div class="mt-4 pt-3 border-top border-white border-opacity-10">
    <div class="d-flex align-items-center justify-content-between mb-2.5">
        <span class="small fw-bold text-white-50 d-flex align-items-center gap-1.5">
            <i class="bi bi-lightning-charge-fill text-warning"></i>
            <span>1-Click Quick Demo Logins:</span>
        </span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="demo-chip-btn demo-chip-admin" onclick="quickFill('admin@erp.com','password123')">
            <span>👑</span> <span>Super Admin</span>
        </button>
        <button type="button" class="demo-chip-btn demo-chip-procurement" onclick="quickFill('procurement@erp.com','password123')">
            <span>📦</span> <span>Procurement</span>
        </button>
        <button type="button" class="demo-chip-btn demo-chip-warehouse" onclick="quickFill('warehouse@erp.com','password123')">
            <span>🏭</span> <span>Warehouse</span>
        </button>
        <button type="button" class="demo-chip-btn demo-chip-sales" onclick="quickFill('sales@erp.com','password123')">
            <span>💼</span> <span>Sales</span>
        </button>
        <button type="button" class="demo-chip-btn demo-chip-finance" onclick="quickFill('finance@erp.com','password123')">
            <span>💳</span> <span>Finance</span>
        </button>
    </div>
</div>

<div class="text-center mt-3 pt-2" style="font-size:0.80rem;color:#94A3B8;">
    <i class="bi bi-shield-lock text-info me-1"></i> Protected Enterprise System • Multi-Role RBAC Active
</div>

<script>
function quickFill(email, password) {
    const emailInput = document.getElementById('email');
    const passInput = document.getElementById('password');
    if (emailInput) emailInput.value = email;
    if (passInput) passInput.value = password;
    
    // Visual flash feedback
    if (emailInput) {
        emailInput.style.transition = 'all 0.3s ease';
        emailInput.style.borderColor = '#38bdf8';
        setTimeout(() => emailInput.style.borderColor = '', 1000);
    }
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}
</script>
