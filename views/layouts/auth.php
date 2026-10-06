<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sign In') ?> — Enterprise ERP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= time() ?>">
    <style>
    /* ════════════════════════════════════════════════════════════════════
       ULTRA-LUXURY GLASSMORPHIC ENTERPRISE AUTH LAYOUT
       ════════════════════════════════════════════════════════════════════ */
    body.auth-body {
        min-height: 100vh !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #080d1a radial-gradient(ellipse 80% 60% at 50% -20%, rgba(99, 102, 241, 0.28), rgba(0, 0, 0, 0)) !important;
        padding: 2rem 1rem !important;
        position: relative !important;
        overflow-x: hidden !important;
        margin: 0 !important;
    }

    /* Ambient Glow Orbs */
    .landing-hero-glow-1 {
        position: absolute;
        top: -100px;
        left: 15%;
        width: 600px;
        height: 450px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.28) 0%, rgba(147, 51, 234, 0.15) 50%, rgba(0,0,0,0) 80%);
        filter: blur(80px);
        pointer-events: none;
        z-index: 0;
    }

    .landing-hero-glow-2 {
        position: absolute;
        bottom: -100px;
        right: 15%;
        width: 550px;
        height: 400px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.22) 0%, rgba(16, 185, 129, 0.1) 50%, rgba(0,0,0,0) 80%);
        filter: blur(90px);
        pointer-events: none;
        z-index: 0;
    }

    /* Centered Split Card Container */
    .auth-split-wrapper {
        display: grid !important;
        grid-template-columns: 1.15fr 1fr !important;
        max-width: 1040px !important;
        width: 100% !important;
        min-height: 580px !important;
        background: rgba(15, 23, 42, 0.88) !important;
        backdrop-filter: blur(24px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(24px) saturate(180%) !important;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55), 0 0 35px rgba(59, 130, 246, 0.2) !important;
        border-radius: 24px !important;
        position: relative !important;
        overflow: hidden !important;
        z-index: 10 !important;
        margin: auto !important;
    }

    @media (max-width: 991.98px) {
        .auth-split-wrapper {
            grid-template-columns: 1fr !important;
            max-width: 480px !important;
        }
        .auth-left-pane {
            display: none !important;
        }
    }

    /* Left Pane */
    .auth-left-pane {
        padding: 2.75rem 2.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        background: linear-gradient(135deg, rgba(24, 20, 68, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.12) !important;
        position: relative !important;
    }

    .auth-left-pane h2 {
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 1.75rem !important;
        line-height: 1.25 !important;
    }

    .auth-left-pane p {
        color: #cbd5e1 !important;
        font-size: 0.92rem !important;
        line-height: 1.6 !important;
    }

    .brand-glow-text {
        background: linear-gradient(135deg, #ffffff 0%, #60a5fa 50%, #c084fc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .auth-feature-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .auth-feature-card:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(96, 165, 250, 0.45) !important;
        transform: translateX(4px) !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25) !important;
    }
    .auth-feature-card span {
        color: #f1f5f9 !important;
        font-weight: 600 !important;
        font-size: 0.88rem !important;
    }
    .auth-feature-icon {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.05rem !important;
        flex-shrink: 0 !important;
    }

    /* Right Pane */
    .auth-right-pane {
        padding: 2.75rem 2.5rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        background: rgba(15, 23, 42, 0.6) !important;
    }

    /* Form Overrides inside Auth Layout */
    .auth-split-wrapper .form-label {
        color: #e2e8f0 !important;
        font-weight: 600 !important;
        font-size: 0.82rem !important;
        margin-bottom: 0.35rem !important;
    }

    .auth-split-wrapper .input-group {
        border-radius: 12px !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        background: rgba(15, 23, 42, 0.75) !important;
        overflow: hidden !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .auth-split-wrapper .input-group:focus-within {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35), 0 4px 12px rgba(99, 102, 241, 0.2) !important;
        background: rgba(15, 23, 42, 0.9) !important;
    }

    .auth-split-wrapper .input-group-text {
        background: rgba(30, 41, 59, 0.8) !important;
        border: none !important;
        color: #94a3b8 !important;
        padding: 0.7rem 0.95rem !important;
    }

    .auth-split-wrapper .form-control {
        background: transparent !important;
        border: none !important;
        color: #ffffff !important;
        font-size: 0.92rem !important;
        padding: 0.7rem 0.95rem !important;
    }

    .auth-split-wrapper .form-control:focus {
        background: transparent !important;
        box-shadow: none !important;
        color: #ffffff !important;
    }

    .auth-split-wrapper .form-control::placeholder {
        color: #64748b !important;
        opacity: 0.9 !important;
    }

    .auth-split-wrapper .btn-gradient-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #3b82f6 100%) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .auth-split-wrapper .btn-gradient-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6) !important;
        filter: brightness(1.08) !important;
    }

    /* Demo Quick Fill Buttons */
    .demo-chip-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        user-select: none;
        border: 1px solid transparent;
        line-height: 1.2;
    }
    .demo-chip-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        filter: brightness(1.15);
    }
    .demo-chip-btn:active {
        transform: scale(0.96);
    }

    .demo-chip-admin {
        background: rgba(56, 189, 248, 0.12);
        border-color: rgba(56, 189, 248, 0.35);
        color: #7dd3fc;
    }
    .demo-chip-admin:hover {
        background: rgba(56, 189, 248, 0.22);
        border-color: #38bdf8;
        color: #ffffff;
    }

    .demo-chip-procurement {
        background: rgba(129, 140, 248, 0.12);
        border-color: rgba(129, 140, 248, 0.35);
        color: #a5b4fc;
    }
    .demo-chip-procurement:hover {
        background: rgba(129, 140, 248, 0.22);
        border-color: #818cf8;
        color: #ffffff;
    }

    .demo-chip-warehouse {
        background: rgba(250, 204, 21, 0.12);
        border-color: rgba(250, 204, 21, 0.35);
        color: #fde047;
    }
    .demo-chip-warehouse:hover {
        background: rgba(250, 204, 21, 0.22);
        border-color: #facc15;
        color: #ffffff;
    }

    .demo-chip-sales {
        background: rgba(74, 222, 128, 0.12);
        border-color: rgba(74, 222, 128, 0.35);
        color: #86efac;
    }
    .demo-chip-sales:hover {
        background: rgba(74, 222, 128, 0.22);
        border-color: #4ade80;
        color: #ffffff;
    }

    .demo-chip-finance {
        background: rgba(236, 72, 153, 0.12);
        border-color: rgba(236, 72, 153, 0.35);
        color: #f472b6;
    }
    .demo-chip-finance:hover {
        background: rgba(236, 72, 153, 0.22);
        border-color: #ec4899;
        color: #ffffff;
    }

    #authCanvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }
    </style>
</head>
<body class="auth-body">

    <!-- Background Particle Canvas -->
    <canvas id="authCanvas"></canvas>

    <!-- Glowing Background Orbs -->
    <div class="landing-hero-glow-1"></div>
    <div class="landing-hero-glow-2"></div>

    <div class="auth-split-wrapper" id="authSplitWrapper">
        <!-- Left Pane: Showcase Banner -->
        <div class="auth-left-pane d-none d-md-flex">
            <!-- Header Brand -->
            <div class="d-flex align-items-center justify-content-between z-2">
                <a href="<?= url('/') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                    <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#4F46E5,#6366F1);display:flex;align-items:center;justify-content:center;color:white;font-size:16px;box-shadow:0 0 12px rgba(79,70,229,0.5);">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <span style="font-family:'Outfit',sans-serif;font-size:1.15rem;font-weight:800;color:white;letter-spacing:-0.02em;">Enterprise<span class="brand-glow-text">ERP</span></span>
                </a>
                <a href="<?= url('/') ?>" class="btn btn-outline-light btn-sm rounded-pill px-2.5 py-0.5 text-white-50 border-secondary" style="font-size:0.75rem;">
                    <i class="bi bi-arrow-left me-1"></i> Home
                </a>
            </div>

            <!-- Middle Feature Spotlight -->
            <div class="my-auto z-2 py-3">
                <div class="badge bg-primary bg-opacity-20 text-info rounded-pill px-3 py-1 mb-2 font-monospace" style="font-size:0.72rem;">ENTERPRISE WORKSTATION</div>
                <h2 class="mb-2">Empowering Operations Across All Branches</h2>
                <p class="mb-3">
                    Multi-warehouse bin traceability, automated P2P procurement cycles, and real-time ledger accounting.
                </p>

                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex align-items-center gap-3 auth-feature-card">
                        <div class="auth-feature-icon bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <span>41 Site-Wide RBAC Permission Scopes</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 auth-feature-card">
                        <div class="auth-feature-icon bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                            <i class="bi bi-houses-fill"></i>
                        </div>
                        <span>5-Tier Multi-Warehouse Bin Locations</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 auth-feature-card">
                        <div class="auth-feature-icon bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <span>100% Audit Log Differential Trail</span>
                    </div>
                </div>
            </div>

            <!-- Footer Status -->
            <div class="d-flex align-items-center justify-content-between z-2 pt-2 border-top border-white border-opacity-10" style="font-size:0.72rem;color:#cbd5e1;">
                <span><i class="bi bi-circle-fill text-success me-1" style="font-size:7px;"></i> ERP Node Active</span>
                <span>© 2026 Enterprise ERP</span>
            </div>
        </div>

        <!-- Right Pane: Form Area -->
        <div class="auth-right-pane">
            <div class="mb-3">
                <h4 class="fw-bold text-white mb-1">Sign In</h4>
                <p class="text-white-50 small mb-0">Access your ERP management dashboard</p>
            </div>

            <?php $flash = \App\Core\Session::getFlash('error') ?: \App\Core\Session::getFlash('success') ?: \App\Core\Session::getFlash('info'); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible fade show mb-3 py-2 px-3 rounded-3" role="alert" style="font-size:0.85rem;">
                    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-1.5"></i> <?= e($flash['value']) ?>
                    <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            {{content}}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('authCanvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let width = canvas.width = window.innerWidth;
            let height = canvas.height = window.innerHeight;

            window.addEventListener('resize', () => {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            });

            const particles = [];
            for (let i = 0; i < 28; i++) {
                particles.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    vx: (Math.random() - 0.5) * 0.35,
                    vy: (Math.random() - 0.5) * 0.35,
                    radius: Math.random() * 2 + 1,
                    alpha: Math.random() * 0.4 + 0.15
                });
            }

            function draw() {
                ctx.clearRect(0, 0, width, height);
                particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;

                    if (p.x < 0 || p.x > width) p.vx *= -1;
                    if (p.y < 0 || p.y > height) p.vy *= -1;

                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(99, 102, 241, ${p.alpha})`;
                    ctx.fill();
                });
                requestAnimationFrame(draw);
            }
            draw();
        }
    });
    </script>
</body>
</html>
