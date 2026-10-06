<div class="text-center py-5">
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-circle" style="width:90px;height:90px;">
            <i class="bi bi-shield-lock-fill" style="font-size:2.8rem;"></i>
        </div>
    </div>
    <h1 class="display-3 fw-extrabold text-warning mb-2 font-heading">403</h1>
    <h3 class="fw-bold text-white mb-2">Access Forbidden</h3>
    <p class="text-secondary mb-4 mx-auto" style="max-width:480px;font-size:0.95rem;">
        You do not have the required RBAC security clearance or role permissions to access this enterprise workstation module.
    </p>
    <div class="d-flex justify-content-center gap-2">
        <a href="<?= url('/dashboard') ?>" class="btn btn-primary fw-bold px-4 py-2.5 rounded-3">
            <i class="bi bi-grid-fill me-1.5"></i> Return to Dashboard
        </a>
        <a href="javascript:history.back()" class="btn btn-outline-secondary fw-semibold px-4 py-2.5 rounded-3">
            <i class="bi bi-arrow-left me-1.5"></i> Go Back
        </a>
    </div>
</div>
