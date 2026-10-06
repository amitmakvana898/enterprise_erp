<div class="mb-4">
    <h3 class="fw-extrabold text-white mb-1 font-heading">Reset Account Password</h3>
    <p class="text-white-50 small mb-0">Enter your registered email address and set your new password below.</p>
</div>

<form method="POST" action="<?= url('/forgot-password') ?>" novalidate id="forgotPasswordForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token ?? '') ?>">

    <div class="mb-3">
        <label class="form-label text-white-50 small fw-bold" for="email">Registered Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill text-primary"></i></span>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com"
                   value="<?= e($_POST['email'] ?? '') ?>" required autofocus style="font-size:0.95rem;">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label text-white-50 small fw-bold" for="new_password">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key-fill text-primary"></i></span>
            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min. 6 chars)" required style="font-size:0.95rem;">
            <button type="button" class="btn text-white-50 border-0 px-3 d-flex align-items-center justify-content-center" style="background:transparent;" onclick="togglePasswordVisibility('new_password', this)" title="Show / Hide Password">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label text-white-50 small fw-bold" for="confirm_password">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check text-primary"></i></span>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required style="font-size:0.95rem;">
            <button type="button" class="btn text-white-50 border-0 px-3 d-flex align-items-center justify-content-center" style="background:transparent;" onclick="togglePasswordVisibility('confirm_password', this)" title="Show / Hide Password">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-gradient-primary w-100 py-3 rounded-3 fw-bold text-white shadow-lg d-flex align-items-center justify-content-center gap-2" style="font-size:0.98rem;letter-spacing:0.02em;">
        <i class="bi bi-arrow-repeat fs-5"></i> <span>Update Account Password</span>
    </button>
</form>

<div class="text-center mt-4 pt-2 border-top border-white border-opacity-10" style="font-size:0.85rem;color:#94A3B8;">
    Remembered your password? <a href="<?= url('/login') ?>" class="text-info fw-bold text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Back to Sign In</a>
</div>

<script>
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
