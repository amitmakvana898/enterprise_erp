<div class="text-center py-5">
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-circle" style="width:90px;height:90px;">
            <i class="bi bi-exclamation-octagon-fill" style="font-size:2.8rem;"></i>
        </div>
    </div>
    <h1 class="display-3 fw-extrabold text-danger mb-2 font-heading">500</h1>
    <h3 class="fw-bold text-white mb-2">Internal Server Exception</h3>
    <p class="text-secondary mb-4 mx-auto" style="max-width:480px;font-size:0.95rem;">
        An unexpected server error occurred during transaction processing. The diagnostic telemetry has been logged to the system audit repository.
    </p>
    <div class="d-flex justify-content-center gap-2">
        <a href="<?= url('/dashboard') ?>" class="btn btn-primary fw-bold px-4 py-2.5 rounded-3">
            <i class="bi bi-grid-fill me-1.5"></i> Return to Dashboard
        </a>
        <a href="javascript:location.reload()" class="btn btn-outline-secondary fw-semibold px-4 py-2.5 rounded-3">
            <i class="bi bi-arrow-clockwise me-1.5"></i> Retry Request
        </a>
    </div>
</div>
