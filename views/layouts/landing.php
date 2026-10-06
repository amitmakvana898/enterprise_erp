<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Enterprise ERP System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <!-- AOS — simple scroll animations -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

<style>
/* ─── RESET & ROOT ─── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg:     #050810;
    --bg2:    #080C1A;
    --blue:   #3B82F6;
    --indigo: #6366F1;
    --purple: #A855F7;
    --cyan:   #06B6D4;
    --green:  #10B981;
    --text:   #F8FAFC;
    --muted:  #94A3B8;
    --sub:    #64748B;
    --border: rgba(255,255,255,0.08);
    --card:   rgba(255,255,255,0.04);
}
html { scroll-behavior: smooth; }
body.landing-body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

/* ─── SIMPLE CSS ANIMATIONS (no JS needed) ─── */
@keyframes fadeUp   { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:none; } }
@keyframes fadeIn   { from { opacity:0; }                             to { opacity:1; } }
@keyframes slideRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:none; } }
@keyframes pulse    { 0%,100% { box-shadow:0 0 0 0 rgba(16,185,129,.6); } 70% { box-shadow:0 0 0 8px rgba(16,185,129,0); } }
@keyframes gradMove { 0% { background-position:0% 50%; } 100% { background-position:200% 50%; } }
@keyframes floatY   { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-10px); } }
@keyframes shimmer  { 0% { left:-75%; } 100% { left:130%; } }

/* hero elements — animate in with CSS only */
.anim-fade-up   { animation: fadeUp 0.8s cubic-bezier(.16,1,.3,1) both; }
.anim-fade-in   { animation: fadeIn 0.8s ease both; }
.anim-slide-right { animation: slideRight 0.9s cubic-bezier(.16,1,.3,1) both; }
.d1 { animation-delay:.10s; }
.d2 { animation-delay:.25s; }
.d3 { animation-delay:.40s; }
.d4 { animation-delay:.55s; }
.d5 { animation-delay:.70s; }
.d6 { animation-delay:.85s; }

/* ─── NAVBAR ─── */
.erp-navbar {
    position: sticky; top: 0; z-index: 999;
    background: rgba(5,8,16,.82);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    padding: .8rem 0;
    transition: background .3s, box-shadow .3s;
}
.erp-navbar.scrolled { background: rgba(5,8,16,.98); box-shadow: 0 4px 24px rgba(0,0,0,.5); }
.brand-text { font-family:'Outfit',sans-serif; font-weight:900; font-size:1.3rem; color:#fff; letter-spacing:-.04em; }
.brand-text span { color:#60A5FA; }
.brand-sub { font-size:.6rem; font-weight:700; letter-spacing:.1em; color:var(--sub); }
.nav-lnk { color:var(--muted)!important; font-size:.88rem; font-weight:500; padding:.4rem .9rem!important; border-radius:8px; transition:all .2s; }
.nav-lnk:hover { color:#fff!important; background:rgba(255,255,255,.07)!important; }
.btn-signin { background:transparent; border:1px solid rgba(255,255,255,.15); color:var(--muted); font-size:.88rem; font-weight:600; padding:.45rem 1.1rem; border-radius:9px; transition:all .25s; text-decoration:none; }
.btn-signin:hover { border-color:rgba(255,255,255,.4); color:#fff; background:rgba(255,255,255,.06); }
.btn-cta-nav { background:linear-gradient(135deg,#3B82F6,#6366F1); border:none; color:#fff; font-size:.88rem; font-weight:700; padding:.45rem 1.25rem; border-radius:9px; box-shadow:0 0 18px rgba(99,102,241,.35); transition:all .25s; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.btn-cta-nav:hover { transform:translateY(-1px); box-shadow:0 0 28px rgba(99,102,241,.55); color:#fff; }

/* ─── HERO ─── */
.hero-wrap {
    position:relative; min-height:90vh; display:flex; align-items:center; overflow:hidden;
    background: var(--bg);
    padding: 5rem 0 4rem;
}
.hero-bg {
    position:absolute; inset:0;
    background:
        radial-gradient(ellipse 80% 55% at 50% -5%, rgba(99,102,241,.32) 0%, transparent 70%),
        radial-gradient(ellipse 45% 35% at 80% 55%, rgba(168,85,247,.18) 0%, transparent 70%),
        radial-gradient(ellipse 35% 30% at 15% 75%, rgba(59,130,246,.18) 0%, transparent 70%),
        url('https://images.unsplash.com/photo-1639762681485-074b7f938ba0?q=80&w=2064&auto=format&fit=crop') center/cover no-repeat;
    pointer-events:none;
    transition:transform .08s linear;
}
.hero-overlay {
    position:absolute; inset:0;
    background:linear-gradient(180deg, rgba(5,8,16,.6) 0%, rgba(5,8,16,.75) 45%, rgba(5,8,16,.95) 80%, #050810 100%);
    pointer-events:none;
}
.hero-dots {
    position:absolute; inset:0;
    background-image:radial-gradient(rgba(255,255,255,.1) 1px, transparent 1px);
    background-size:30px 30px;
    mask-image:radial-gradient(ellipse 75% 75% at 50% 50%, black 40%, transparent 100%);
    -webkit-mask-image:radial-gradient(ellipse 75% 75% at 50% 50%, black 40%, transparent 100%);
    pointer-events:none;
}
.hero-inner { position:relative; z-index:2; }

/* Status badge */
.status-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.28);
    padding:5px 15px; border-radius:100px;
    font-size:.82rem; font-weight:600; color:#93C5FD;
}
.pulse-dot { width:8px; height:8px; background:#10B981; border-radius:50%; animation:pulse 2s ease infinite; }

/* Hero title */
.hero-h1 {
    font-family:'Outfit',sans-serif;
    font-size:clamp(2.6rem,5.5vw,4.6rem);
    font-weight:900;
    letter-spacing:-.04em;
    line-height:1.06;
    color:#fff;
}
.grad-word {
    background:linear-gradient(135deg,#60A5FA,#A78BFA,#F472B6);
    background-size:200% 200%;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    animation:gradMove 3s linear infinite alternate;
}
.hero-sub { font-size:1.08rem; color:var(--muted); line-height:1.7; max-width:620px; }

/* Buttons */
.btn-primary-hero {
    background:linear-gradient(135deg,#3B82F6,#6366F1,#8B5CF6);
    color:#fff; border:none; font-size:.98rem; font-weight:700;
    padding:.85rem 2rem; border-radius:13px;
    box-shadow:0 8px 28px rgba(99,102,241,.45);
    transition:all .3s; text-decoration:none;
    display:inline-flex; align-items:center; gap:7px;
    position:relative; overflow:hidden;
}
.btn-primary-hero:hover { transform:translateY(-2px); box-shadow:0 14px 40px rgba(99,102,241,.65); color:#fff; }
.btn-primary-hero::after {
    content:''; position:absolute; top:-50%; left:-75%; width:50%; height:200%;
    background:linear-gradient(120deg,transparent,rgba(255,255,255,.2),transparent);
    transform:skewX(-25deg); transition:left .55s;
}
.btn-primary-hero:hover::after { left:130%; }
.btn-outline-hero {
    background:rgba(255,255,255,.05); color:#CBD5E1;
    border:1px solid rgba(255,255,255,.14); font-size:.98rem; font-weight:600;
    padding:.85rem 1.8rem; border-radius:13px; backdrop-filter:blur(8px);
    transition:all .3s; text-decoration:none;
    display:inline-flex; align-items:center; gap:7px;
}
.btn-outline-hero:hover { background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.3); color:#fff; transform:translateY(-2px); }

/* Hero preview window */
.preview-win {
    background:var(--bg2); border:1px solid var(--border); border-radius:20px; overflow:hidden;
    box-shadow:0 0 0 1px rgba(255,255,255,.04), 0 32px 64px rgba(0,0,0,.55), 0 0 60px rgba(99,102,241,.12);
    animation: floatY 5s ease-in-out infinite;
}
.win-bar { background:rgba(255,255,255,.03); border-bottom:1px solid var(--border); padding:.85rem 1.2rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; }
.win-dots { display:flex; gap:5px; }
.win-dot { width:10px; height:10px; border-radius:50%; }
.url-bar { flex:1; max-width:360px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.07); border-radius:7px; padding:5px 12px; font-size:.78rem; color:var(--muted); }
.win-body { padding:1.8rem; background:#080C1A; }
.sc { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:13px; padding:1.1rem 1.2rem; position:relative; overflow:hidden; transition:all .3s; }
.sc:hover { border-color:rgba(99,102,241,.35); background:rgba(99,102,241,.05); }
.sc::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; border-radius:13px 13px 0 0; }
.sc-blue::before  { background:linear-gradient(90deg,#3B82F6,#6366F1); }
.sc-amber::before { background:linear-gradient(90deg,#F59E0B,#EF4444); }
.sc-green::before { background:linear-gradient(90deg,#10B981,#06B6D4); }
.sc-purple::before{ background:linear-gradient(90deg,#A855F7,#EC4899); }
.sc-num { font-family:'Outfit',sans-serif; font-size:1.7rem; font-weight:900; color:#fff; letter-spacing:-.03em; }
.sc-lbl { font-size:.75rem; color:var(--muted); }
.sc-ico { font-size:1.4rem; opacity:.6; }
.mini-tbl { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.05); border-radius:11px; overflow:hidden; }
.mini-row { padding:9px 14px; border-bottom:1px solid rgba(255,255,255,.04); display:flex; justify-content:space-between; align-items:center; }
.mini-row:last-child { border-bottom:0; }
.mini-lbl { font-size:.79rem; color:var(--muted); }
.badge-pill { font-size:.68rem; font-weight:700; padding:2px 9px; border-radius:100px; }

/* ─── STATS BAR ─── */
.stats-wrap { background:var(--bg2); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:2.2rem 0; }
.stat-block { text-align:center; }
.stat-num { font-family:'Outfit',sans-serif; font-size:2.2rem; font-weight:900; letter-spacing:-.04em; color:#fff; line-height:1; }
.stat-lbl { font-size:.82rem; color:var(--muted); margin-top:3px; }

/* ─── SECTION SHARED ─── */
.eyebrow {
    display:inline-flex; align-items:center; gap:5px;
    font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#60A5FA;
    background:rgba(59,130,246,.09); border:1px solid rgba(59,130,246,.22); padding:4px 13px; border-radius:100px;
}
.sec-title { font-family:'Outfit',sans-serif; font-size:clamp(1.8rem,3vw,2.6rem); font-weight:900; letter-spacing:-.04em; color:#fff; }
.sec-sub { font-size:1rem; color:var(--muted); line-height:1.65; }

/* ─── FEATURE CARDS ─── */
.feat-sec { padding:5.5rem 0; background:linear-gradient(180deg,var(--bg),var(--bg2)); }
.feat-card {
    background:var(--card); border:1px solid var(--border); border-radius:18px;
    padding:1.8rem; height:100%;
    transition:all .35s cubic-bezier(.16,1,.3,1);
    position:relative; overflow:hidden;
}
.feat-card::after { content:''; position:absolute; inset:0; border-radius:18px; background:radial-gradient(ellipse at 30% 30%,rgba(99,102,241,.12),transparent 70%); opacity:0; transition:opacity .35s; }
.feat-card:hover { border-color:rgba(99,102,241,.35); transform:translateY(-5px); box-shadow:0 18px 36px rgba(0,0,0,.38); }
.feat-card:hover::after { opacity:1; }
.feat-icon { width:48px; height:48px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:1.1rem; position:relative; z-index:1; transition:transform .3s; }
.feat-card:hover .feat-icon { transform:scale(1.1) rotate(4deg); }
.feat-card h5 { font-size:1rem; font-weight:700; color:#fff; margin-bottom:.4rem; position:relative; z-index:1; }
.feat-card p { font-size:.87rem; color:var(--muted); line-height:1.6; margin:0; position:relative; z-index:1; }
.feat-tag { font-size:.72rem; font-weight:700; padding:3px 10px; border-radius:100px; display:inline-block; margin-top:.75rem; position:relative; z-index:1; }

/* ─── WORKFLOW ─── */
.wf-sec { padding:5.5rem 0; background:var(--bg2); }
.wf-step { display:flex; align-items:flex-start; gap:1.1rem; padding:1.2rem; border-radius:14px; border:1px solid rgba(255,255,255,.05); background:rgba(255,255,255,.02); transition:all .3s; }
.wf-step:hover { border-color:rgba(99,102,241,.28); background:rgba(99,102,241,.04); }
.wf-num { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:800; flex-shrink:0; background:linear-gradient(135deg,#6366F1,#8B5CF6); color:#fff; box-shadow:0 4px 10px rgba(99,102,241,.38); }
.wf-step h6 { font-size:.92rem; font-weight:700; color:#fff; margin-bottom:3px; }
.wf-step p { font-size:.83rem; color:var(--muted); margin:0; line-height:1.5; }

/* ─── CTA ─── */
.cta-sec { padding:6rem 0; background:var(--bg); position:relative; overflow:hidden; }
.cta-glow { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:600px; height:350px; background:radial-gradient(ellipse,rgba(99,102,241,.18),transparent 70%); pointer-events:none; }
.cta-box {
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.09); border-radius:24px; padding:3.5rem;
    position:relative; z-index:1; overflow:hidden;
}
.cta-box::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(99,102,241,.5),transparent); }
.cta-box::after { content:''; position:absolute; top:-60%; right:-10%; width:350px; height:350px; background:radial-gradient(circle,rgba(168,85,247,.15),transparent 70%); pointer-events:none; }

/* ─── FOOTER ─── */
.ft { background:#030508; border-top:1px solid rgba(255,255,255,.06); padding:3rem 0; }
.ft-brand { font-family:'Outfit',sans-serif; font-size:1.1rem; font-weight:900; color:#fff; letter-spacing:-.03em; }
.ft-brand span { color:#60A5FA; }
.ft-link { color:#475569; font-size:.87rem; font-weight:500; text-decoration:none; transition:color .2s; }
.ft-link:hover { color:#94A3B8; }
.ft-copy { font-size:.8rem; color:#374151; }

/* ─── SCROLL PROGRESS BAR ─── */
#scroll-bar { position:fixed; top:0; left:0; height:2.5px; background:linear-gradient(90deg,#3B82F6,#A855F7,#EC4899); z-index:9999999; width:0; pointer-events:none; transition:width .1s; }
</style>
</head>
<body class="landing-body">

<!-- Scroll progress -->
<div id="scroll-bar"></div>

<!-- ─── NAVBAR ─── -->
<nav class="erp-navbar" id="navbar">
    <div class="container-xl d-flex align-items-center justify-content-between gap-3">
        <a href="<?= url('/') ?>" class="text-decoration-none d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#3B82F6,#6366F1);display:flex;align-items:center;justify-content:center;font-size:16px;color:#fff;box-shadow:0 0 18px rgba(99,102,241,.45);">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div>
                <div class="brand-text">Enterprise<span>ERP</span></div>
                <div class="brand-sub">SUITE v3.0</div>
            </div>
        </a>
        <div class="d-none d-lg-flex align-items-center gap-1">
            <a class="nav-lnk" href="<?= url('/') ?>">Home</a>
            <a class="nav-lnk" href="#features">Modules</a>
            <a class="nav-lnk" href="#workflow">Workflow</a>
            <a class="nav-lnk" href="#security">Security</a>
            <a class="nav-lnk" href="<?= url('/about') ?>">Architecture</a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (is_logged_in()): ?>
                <a href="<?= url('/dashboard') ?>" class="btn-cta-nav"><i class="bi bi-grid-fill"></i> Dashboard</a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="btn-cta-nav"><i class="bi bi-box-arrow-in-right"></i> Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ─── HERO ─── -->
<section class="hero-wrap">
    <div class="hero-bg" id="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-dots"></div>
    <div class="container-xl hero-inner">
        <div class="row align-items-center g-5">
            <!-- Left -->
            <div class="col-lg-6">
                <div class="status-badge mb-4 anim-fade-in d1">
                    <span class="pulse-dot"></span>
                    <span id="badge-text">Enterprise ERP Suite v3.0 — Live Production Node</span>
                </div>
                <h1 class="hero-h1 mb-4 anim-fade-up d2">
                    The Complete<br>
                    <span class="grad-word">Enterprise Operating</span><br>
                    System for Business
                </h1>
                <p class="hero-sub mb-5 anim-fade-up d3">
                    Unified multi-branch logistics, 11-stage Procure-to-Pay automation, 6-stage Order-to-Cash pipeline, real-time bin traceability, and 41 granular RBAC security scopes — all in one platform.
                </p>
                <div class="d-flex flex-wrap gap-3 anim-fade-up d4">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= url('/dashboard') ?>" class="btn-primary-hero"><i class="bi bi-grid-fill"></i> Open Dashboard</a>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn-primary-hero"><i class="bi bi-rocket-takeoff-fill"></i> Access Workspace</a>
                    <?php endif; ?>
                    <a href="<?= url('/about') ?>" class="btn-outline-hero"><i class="bi bi-diagram-3"></i> Architecture</a>
                </div>
                <div class="d-flex align-items-center gap-4 mt-5 anim-fade-up d5">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success"></i>
                        <span style="font-size:.82rem;color:var(--sub);font-weight:500;">41 RBAC Scopes</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                        <span style="font-size:.82rem;color:var(--sub);font-weight:500;">11-Stage P2P</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-info"></i>
                        <span style="font-size:.82rem;color:var(--sub);font-weight:500;">5-Tier Bin Trace</span>
                    </div>
                </div>
            </div>

            <!-- Right — Dashboard preview -->
            <div class="col-lg-6 anim-slide-right d3">
                <div class="preview-win">
                    <div class="win-bar">
                        <div class="win-dots">
                            <div class="win-dot" style="background:#EF4444;"></div>
                            <div class="win-dot" style="background:#F59E0B;"></div>
                            <div class="win-dot" style="background:#10B981;"></div>
                        </div>
                        <div class="url-bar"><i class="bi bi-lock-fill text-success me-1" style="font-size:.65rem;"></i>localhost/enterprise_erp/dashboard</div>
                        <span class="badge-pill" style="background:rgba(16,185,129,.12);color:#10B981;border:1px solid rgba(16,185,129,.25);"><i class="bi bi-shield-check me-1"></i>Secure</span>
                    </div>
                    <div class="win-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="sc sc-blue">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div><div class="sc-num">410</div><div class="sc-lbl">Units In Stock</div></div>
                                        <div class="sc-ico text-primary"><i class="bi bi-box-seam"></i></div>
                                    </div>
                                    <small class="text-success" style="font-size:.68rem;"><i class="bi bi-arrow-up-short"></i>Auto Restocked</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sc sc-amber">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div><div class="sc-num">20</div><div class="sc-lbl">Purchase Orders</div></div>
                                        <div class="sc-ico text-warning"><i class="bi bi-bag-check"></i></div>
                                    </div>
                                    <small class="text-info" style="font-size:.68rem;"><i class="bi bi-check2-all"></i>All Approved</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sc sc-green">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div><div class="sc-num">18</div><div class="sc-lbl">Sales Orders</div></div>
                                        <div class="sc-ico text-success"><i class="bi bi-cart-check"></i></div>
                                    </div>
                                    <small class="text-warning" style="font-size:.68rem;"><i class="bi bi-truck"></i>Dispatched</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sc sc-purple">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div><div class="sc-num">41</div><div class="sc-lbl">RBAC Scopes</div></div>
                                        <div class="sc-ico" style="color:#A855F7;"><i class="bi bi-shield-lock"></i></div>
                                    </div>
                                    <small style="font-size:.68rem;color:#A855F7;"><i class="bi bi-lock"></i>Protected</small>
                                </div>
                            </div>
                        </div>
                        <div class="mini-tbl">
                            <div style="padding:8px 14px;border-bottom:1px solid rgba(255,255,255,.05);font-size:.76rem;font-weight:600;color:var(--muted);"><i class="bi bi-clock-history me-1" style="color:#818CF8;"></i>Recent Transactions</div>
                            <div class="mini-row"><span class="mini-lbl">SO-2026-4914 — abc customer</span><span class="badge-pill" style="background:rgba(16,185,129,.12);color:#10B981;border:1px solid rgba(16,185,129,.25);">Delivered</span></div>
                            <div class="mini-row"><span class="mini-lbl">PO-2026-7823 — Dell Supplier</span><span class="badge-pill" style="background:rgba(245,158,11,.12);color:#F59E0B;border:1px solid rgba(245,158,11,.25);">Approved</span></div>
                            <div class="mini-row"><span class="mini-lbl">GRN-2026-0192 — QC Passed</span><span class="badge-pill" style="background:rgba(59,130,246,.12);color:#60A5FA;border:1px solid rgba(59,130,246,.25);">Received</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── STATS BAR ─── -->
<div class="stats-wrap">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-6 col-md-3 stat-block" data-aos="fade-up" data-aos-delay="0"><div class="stat-num" data-count="51" data-suffix="">51</div><div class="stat-lbl">Database Tables</div></div>
            <div class="col-6 col-md-3 stat-block" data-aos="fade-up" data-aos-delay="80"><div class="stat-num" data-count="65" data-suffix="+">65+</div><div class="stat-lbl">Application Pages</div></div>
            <div class="col-6 col-md-3 stat-block" data-aos="fade-up" data-aos-delay="160"><div class="stat-num" data-count="8" data-suffix="">8</div><div class="stat-lbl">User Role Levels</div></div>
            <div class="col-6 col-md-3 stat-block" data-aos="fade-up" data-aos-delay="240"><div class="stat-num" data-count="100" data-suffix="%">100%</div><div class="stat-lbl">Custom PHP MVC</div></div>
        </div>
    </div>
</div>

<!-- ─── FEATURES ─── -->
<section id="features" class="feat-sec">
    <div class="container-xl">
        <div class="text-center mb-5">
            <div class="eyebrow mb-3" data-aos="fade-down"><i class="bi bi-grid-3x3-gap-fill me-1"></i>Module Suite</div>
            <h2 class="sec-title mb-3" data-aos="fade-up" data-aos-delay="80" style="max-width:560px;margin:auto;">Everything your enterprise needs, unified</h2>
            <p class="sec-sub" data-aos="fade-up" data-aos-delay="150" style="max-width:520px;margin:auto;">From raw material procurement to customer delivery — every process automated and secured.</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon'=>'bi-bag-check-fill','color'=>'rgba(245,158,11,.12)','border'=>'rgba(245,158,11,.2)','ico_color'=>'#F59E0B','title'=>'11-Stage Procure-to-Pay (P2P)','desc'=>'PR → RFQ → Vendor Comparison → PO → GRN → Quality Inspection → Invoice → Payment — fully automated pipeline.','tag'=>'P2P Workflow','tag_bg'=>'rgba(245,158,11,.1)','tag_color'=>'#F59E0B','tag_border'=>'rgba(245,158,11,.2)'],
                ['icon'=>'bi-cart-check-fill','color'=>'rgba(16,185,129,.12)','border'=>'rgba(16,185,129,.2)','ico_color'=>'#10B981','title'=>'6-Stage Order-to-Cash (O2C)','desc'=>'Quotation → Sales Order → Delivery Challan → Tax Invoice → Payment → Sales Return with auto inventory restock.','tag'=>'O2C Pipeline','tag_bg'=>'rgba(16,185,129,.1)','tag_color'=>'#10B981','tag_border'=>'rgba(16,185,129,.2)'],
                ['icon'=>'bi-layers-fill','color'=>'rgba(59,130,246,.12)','border'=>'rgba(59,130,246,.2)','ico_color'=>'#60A5FA','title'=>'5-Tier Bin Traceability','desc'=>'Track every SKU to Company → Branch → Warehouse → Rack → Bin. Zero stock leakage with full ledger history.','tag'=>'Inventory Intelligence','tag_bg'=>'rgba(59,130,246,.1)','tag_color'=>'#60A5FA','tag_border'=>'rgba(59,130,246,.2)'],
                ['icon'=>'bi-shield-lock-fill','color'=>'rgba(168,85,247,.12)','border'=>'rgba(168,85,247,.2)','ico_color'=>'#A855F7','title'=>'41-Scope RBAC Security','desc'=>'8 distinct user roles with 41 individual permission scopes. Toggle access per module, grant/revoke all, audit every action.','tag'=>'RBAC Security','tag_bg'=>'rgba(168,85,247,.1)','tag_color'=>'#A855F7','tag_border'=>'rgba(168,85,247,.2)'],
                ['icon'=>'bi-sliders','color'=>'rgba(6,182,212,.12)','border'=>'rgba(6,182,212,.2)','ico_color'=>'#06B6D4','title'=>'Dynamic EAV Attribute Engine','desc'=>'Define unlimited product specs (RAM, Color, IMEI, Voltage) without schema changes. Built for multi-SKU catalogs.','tag'=>'EAV Catalog','tag_bg'=>'rgba(6,182,212,.1)','tag_color'=>'#06B6D4','tag_border'=>'rgba(6,182,212,.2)'],
                ['icon'=>'bi-journal-text','color'=>'rgba(239,68,68,.12)','border'=>'rgba(239,68,68,.2)','ico_color'=>'#F87171','title'=>'Immutable Audit Trail','desc'=>'Every create/update/delete logged with IP, user ID, old & new payload. 771+ events. Export to CSV/Excel/PDF.','tag'=>'Compliance','tag_bg'=>'rgba(239,68,68,.1)','tag_color'=>'#F87171','tag_border'=>'rgba(239,68,68,.2)'],
            ];
            foreach ($features as $i => $f):
            ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $i * 70 ?>">
                <div class="feat-card">
                    <div class="feat-icon" style="background:<?= $f['color'] ?>;border:1px solid <?= $f['border'] ?>;"><i class="<?= $f['icon'] ?>" style="color:<?= $f['ico_color'] ?>;"></i></div>
                    <h5><?= $f['title'] ?></h5>
                    <p><?= $f['desc'] ?></p>
                    <span class="feat-tag" style="background:<?= $f['tag_bg'] ?>;color:<?= $f['tag_color'] ?>;border:1px solid <?= $f['tag_border'] ?>;"><?= $f['tag'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ─── WORKFLOW ─── -->
<section id="workflow" class="wf-sec">
    <div class="container-xl">
        <div class="row g-5 align-items-start">
            <div class="col-lg-4">
                <div class="eyebrow mb-3" data-aos="fade-down"><i class="bi bi-diagram-3 me-1"></i>Workflow Engine</div>
                <h2 class="sec-title mb-3" data-aos="fade-up" data-aos-delay="80">Fully automated end-to-end pipelines</h2>
                <p class="sec-sub mb-4" data-aos="fade-up" data-aos-delay="150">From vendor selection to final payment — every stage automated, every document generated, inventory synced in real-time.</p>
                <a href="<?= url('/login') ?>" class="btn-primary-hero" data-aos="fade-up" data-aos-delay="220"><i class="bi bi-rocket-takeoff-fill"></i> Try the Workflows</a>
            </div>
            <div class="col-lg-8">
                <div class="row g-3">
                    <?php
                    $steps = [
                        ['01','Purchase Request (PR)','Department raises material demand with required quantity and date'],
                        ['02','RFQ → Vendor Quotations','Send RFQ to multiple vendors, collect & compare supplier quotations'],
                        ['03','Purchase Order (PO)','Auto-generate PO from the approved winning vendor quotation'],
                        ['04','Goods Receipt Note (GRN)','Receive goods, scan into warehouse bins, log batch & expiry data'],
                        ['05','Quality Inspection','QC check per GRN line item — approve, reject, or quarantine stock'],
                        ['06','Invoice & Payment','Match invoice to PO, schedule payment, generate gate pass on exit'],
                    ];
                    foreach ($steps as $i => $s):
                    ?>
                    <div class="col-12" data-aos="fade-left" data-aos-delay="<?= $i * 75 ?>">
                        <div class="wf-step">
                            <div class="wf-num"><?= $s[0] ?></div>
                            <div><h6><?= $s[1] ?></h6><p><?= $s[2] ?></p></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ─── CTA ─── -->
<section id="security" class="cta-sec">
    <div class="cta-glow"></div>
    <div class="container-xl">
        <div class="cta-box text-center p-5 rounded-4 shadow-lg position-relative overflow-hidden" data-aos="zoom-in" data-aos-duration="700" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%); border: 1px solid rgba(99, 102, 241, 0.3); box-shadow: 0 20px 50px rgba(0,0,0,0.6), inset 0 0 30px rgba(99, 102, 241, 0.15) !important;">
            
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3);">
                <i class="bi bi-rocket-takeoff-fill text-warning"></i>
                <span style="color:#A5B4FC; font-size:0.88rem; font-weight:700; letter-spacing:0.03em;">ENTERPRISE OPERATING SYSTEM v3.0</span>
            </div>

            <h2 class="sec-title mb-3 mx-auto" style="max-width: 750px; font-size: 2.5rem; font-weight: 800; color: #FFFFFF;">
                Ready to Automate Your Supply Chain & Sales Workflows?
            </h2>

            <p class="mx-auto mb-4" style="color: #94A3B8; font-size: 1.1rem; max-width: 650px; line-height: 1.6;">
                Experience seamless multi-warehouse logistics, automated 3-way invoice matching, 6-stage order-to-cash pipeline, and real-time inventory tracking.
            </p>

            <!-- Role Access Quick Badges -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4 max-w-800 mx-auto">
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.3); color:#93C5FD;"><i class="bi bi-shield-check me-1"></i> Super Admin</span>
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#6EE7B7;"><i class="bi bi-cart-check me-1"></i> Sales Manager</span>
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color:#FDE047;"><i class="bi bi-box-seam me-1"></i> Procurement Manager</span>
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(168,85,247,0.12); border:1px solid rgba(168,85,247,0.3); color:#E9D5FF;"><i class="bi bi-houses me-1"></i> Warehouse Manager</span>
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#FCA5A5;"><i class="bi bi-cash-stack me-1"></i> Finance Manager</span>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="<?= url('/login') ?>" class="btn-primary-hero px-5 py-3 rounded-pill fw-bold shadow-lg" style="font-size: 1.05rem;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Launch Application Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ─── FOOTER ─── -->
<footer class="ft py-4" style="background:#090d16; border-top:1px solid rgba(255,255,255,0.08);">
    <div class="container-xl">
        <div class="row align-items-center justify-content-between g-3 mb-3">
            <div class="col-auto">
                <div class="ft-brand d-flex align-items-center gap-2" style="font-size:1.4rem; font-weight:800; color:#FFF;">
                    <div style="width:28px; height:28px; border-radius:7px; background:linear-gradient(135deg,#3B82F6,#6366F1); display:flex; align-items:center; justify-content:center; font-size:13px; color:#fff;">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    Enterprise<span style="color:#60A5FA;">ERP</span>
                </div>
                <div style="font-size:.78rem; color:#64748B; margin-top:2px;">Multi-Branch Supply Chain & ERP Management Suite</div>
            </div>
            <div class="col-auto d-flex gap-4 flex-wrap">
                <a href="<?= url('/') ?>" class="ft-link">Home</a>
                <a href="#features" class="ft-link">Modules</a>
                <a href="#workflow" class="ft-link">Workflow</a>
                <a href="<?= url('/about') ?>" class="ft-link">Architecture</a>
                <a href="<?= url('/login') ?>" class="ft-link fw-bold text-info">Sign In</a>
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,.06); margin:1.2rem 0;"></div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="ft-copy" style="color:#475569; font-size:0.82rem;">© 2026 Enterprise ERP Suite. All rights reserved.</div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-dark text-secondary border border-secondary border-opacity-25 px-2.5 py-1">51 DB Tables</span>
                <span class="badge bg-dark text-secondary border border-secondary border-opacity-25 px-2.5 py-1">65+ Views</span>
                <span class="badge bg-dark text-secondary border border-secondary border-opacity-25 px-2.5 py-1">41 RBAC Scopes</span>
                <span class="badge bg-dark text-secondary border border-secondary border-opacity-25 px-2.5 py-1">PHP MVC Engine</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
<!-- AOS — simple scroll animations -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<!-- CountUp — animated numbers -->
<script src="https://cdn.jsdelivr.net/npm/countup.js@2.8.0/dist/countUp.umd.js"></script>

<script>
/* ── AOS Init ── */
AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 70 });

/* ── Navbar scroll effect ── */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
});

/* ── Scroll progress bar ── */
const sb = document.getElementById('scroll-bar');
window.addEventListener('scroll', () => {
    const pct = window.scrollY / (document.documentElement.scrollHeight - innerHeight) * 100;
    sb.style.width = Math.min(pct, 100) + '%';
});

/* ── CountUp on stats ── */
const statsIO = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.querySelectorAll('[data-count]').forEach(el => {
                const cu = new countUp.CountUp(el, +el.dataset.count, {
                    duration: 2, suffix: el.dataset.suffix || '', useEasing: true
                });
                if (!cu.error) cu.start();
            });
            statsIO.unobserve(e.target);
        }
    });
}, { threshold: 0.5 });
const statsEl = document.querySelector('.stats-wrap');
if (statsEl) statsIO.observe(statsEl);

/* ── Typewriter on badge ── */
(function() {
    const el = document.getElementById('badge-text');
    if (!el) return;
    const msgs = [
        'Enterprise ERP Suite v3.0 \u2014 Live Production Node',
        'Multi-Branch Logistics & P2P Automation Engine',
        '41 RBAC Security Scopes \u2014 System Secured',
        '6-Stage Order-to-Cash Pipeline Ready',
    ];
    let ti = 0, ci = msgs[0].length, del = false;
    function tick() {
        const cur = msgs[ti];
        el.textContent = cur.slice(0, ci);
        if (!del) { ci++; if (ci > cur.length) { del = true; setTimeout(tick, 2000); return; } }
        else { ci--; if (ci < 0) { ci = 0; del = false; ti = (ti + 1) % msgs.length; } }
        setTimeout(tick, del ? 22 : 50);
    }
    setTimeout(tick, 2500);
})();

/* ── Hero parallax on mouse ── */
(function() {
    const bg = document.getElementById('hero-bg');
    if (!bg || window.matchMedia('(pointer:coarse)').matches) return;
    document.addEventListener('mousemove', e => {
        const x = (e.clientX / innerWidth  - .5) * 15;
        const y = (e.clientY / innerHeight - .5) * 10;
        bg.style.transform = `translate(${x}px,${y}px) scale(1.04)`;
    });
})();

/* ── Smooth anchor scroll ── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const id = a.getAttribute('href').slice(1);
        const t  = document.getElementById(id);
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
});
</script>
</body>
</html>
