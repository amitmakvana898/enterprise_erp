<?php 
$activeTab = $activeTab ?? 'company'; 
$settings = $settings ?? [];
$user = $user ?? auth_user();
$isSuperAdmin = ($user['role_name'] ?? '') === 'super_admin';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h3 class="page-header-title d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center p-2 rounded-3" style="background:rgba(99, 102, 241, 0.15);color:#4f46e5;">
                <i class="bi bi-gear-wide-connected fs-4"></i>
            </span>
            <span>Enterprise Settings & Configuration</span>
        </h3>
        <p class="page-header-sub">Manage global company identity, tax compliance, automated workflows, and system maintenance.</p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Return Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Navigation Pills -->
    <div class="col-lg-3 col-md-4">
        <div class="card border shadow-sm p-2 sticky-top" style="top: 80px; z-index: 10;">
            <div class="nav d-flex flex-row flex-md-column overflow-auto nav-pills gap-1 flex-nowrap pb-1 pb-md-0" id="settings-tabs" role="tablist">
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'company' ? 'active' : '' ?>" href="<?= url('/settings?tab=company') ?>">
                    <i class="bi bi-building-fill text-primary fs-5"></i>
                    <div>
                        <div class="fw-bold small">Company Profile</div>
                        <div class="text-muted" style="font-size:0.72rem;">Legal info & GSTIN</div>
                    </div>
                </a>
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'billing' ? 'active' : '' ?>" href="<?= url('/settings?tab=billing') ?>">
                    <i class="bi bi-receipt text-warning fs-5"></i>
                    <div>
                        <div class="fw-bold small">Tax & Invoicing</div>
                        <div class="text-muted" style="font-size:0.72rem;">GST rates & Bank accounts</div>
                    </div>
                </a>
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'notifications' ? 'active' : '' ?>" href="<?= url('/settings?tab=notifications') ?>">
                    <i class="bi bi-bell-fill text-info fs-5"></i>
                    <div>
                        <div class="fw-bold small">Email & Notifications</div>
                        <div class="text-muted" style="font-size:0.72rem;">SMTP & Low stock alerts</div>
                    </div>
                </a>
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'appearance' ? 'active' : '' ?>" href="<?= url('/settings?tab=appearance') ?>">
                    <i class="bi bi-palette-fill text-purple fs-5" style="color:#a855f7;"></i>
                    <div>
                        <div class="fw-bold small">Preferences & Display</div>
                        <div class="text-muted" style="font-size:0.72rem;">Theme & Date formatting</div>
                    </div>
                </a>
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'security' ? 'active' : '' ?>" href="<?= url('/settings?tab=security') ?>">
                    <i class="bi bi-shield-lock-fill text-success fs-5"></i>
                    <div>
                        <div class="fw-bold small">My Profile & Security</div>
                        <div class="text-muted" style="font-size:0.72rem;">Password & Account safety</div>
                    </div>
                </a>
                <?php if ($isSuperAdmin): ?>
                <a class="nav-link d-flex align-items-center gap-2.5 py-2.5 px-3 rounded-3 <?= $activeTab === 'backup' ? 'active' : '' ?>" href="<?= url('/settings?tab=backup') ?>">
                    <i class="bi bi-database-fill-gear text-danger fs-5"></i>
                    <div>
                        <div class="fw-bold small">System Health & Backup</div>
                        <div class="text-muted" style="font-size:0.72rem;">Database snapshots & Cache</div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Settings Content Area -->
    <div class="col-lg-9 col-md-8">
        
        <?php if ($activeTab === 'company'): ?>
        <!-- ─── 1. COMPANY PROFILE TAB ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-building me-2 text-primary"></i>Company Profile & Identity</h5>
                    <p class="text-muted small mb-0">Legal identity information printed on Purchase Orders, Invoices, and Official Statements.</p>
                </div>
            </div>

            <form action="<?= url('/settings/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="tab" value="company">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Operating Company Trade Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Legal Registered Entity Name</label>
                        <input type="text" name="company_legal_name" class="form-control" value="<?= e($settings['company_legal_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">GSTIN / Tax Registration ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <input type="text" name="company_gstin" class="form-control text-uppercase font-monospace" value="<?= e($settings['company_gstin'] ?? '') ?>" placeholder="24AAACE1234F1Z5">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Operating Currency</label>
                        <select name="currency_code" class="form-select">
                            <option value="INR" <?= ($settings['currency_code'] ?? 'INR') === 'INR' ? 'selected' : '' ?>>INR (Indian Rupee - ₹)</option>
                            <option value="USD" <?= ($settings['currency_code'] ?? '') === 'USD' ? 'selected' : '' ?>>USD (US Dollar - $)</option>
                            <option value="EUR" <?= ($settings['currency_code'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (Euro - €)</option>
                            <option value="GBP" <?= ($settings['currency_code'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (British Pound - £)</option>
                            <option value="AED" <?= ($settings['currency_code'] ?? '') === 'AED' ? 'selected' : '' ?>>AED (UAE Dirham)</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol'] ?? '₹') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Corporate Contact Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="company_email" class="form-control" value="<?= e($settings['company_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Corporate Contact Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="company_phone" class="form-control" value="<?= e($settings['company_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold">Registered Office Address</label>
                        <textarea name="company_address" class="form-control" rows="3"><?= e($settings['company_address'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Company Profile
                    </button>
                </div>
            </form>
        </div>

        <?php elseif ($activeTab === 'billing'): ?>
        <!-- ─── 2. TAX & INVOICING TAB ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-receipt me-2 text-warning"></i>Tax, Invoicing & Banking Details</h5>
                <p class="text-muted small mb-0">Configure default tax rules, document serial prefixes, and invoice payment instructions.</p>
            </div>

            <form action="<?= url('/settings/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="tab" value="billing">

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-percent me-1"></i>Tax & Compliance Configuration</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Default GST / Tax Rate (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="default_tax_rate" class="form-control" value="<?= e($settings['default_tax_rate'] ?? '18') ?>">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Default Credit / Payment Terms</label>
                        <div class="input-group">
                            <input type="number" name="payment_terms_days" class="form-control" value="<?= e($settings['payment_terms_days'] ?? '30') ?>">
                            <span class="input-group-text">Days</span>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-123 me-1"></i>Document Numbering Series Prefixes</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Sales Invoice Prefix</label>
                        <input type="text" name="invoice_prefix" class="form-control font-monospace" value="<?= e($settings['invoice_prefix'] ?? 'INV-2026-') ?>">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Purchase Order (PO)</label>
                        <input type="text" name="po_prefix" class="form-control font-monospace" value="<?= e($settings['po_prefix'] ?? 'PO-2026-') ?>">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Purchase Requisition (PR)</label>
                        <input type="text" name="pr_prefix" class="form-control font-monospace" value="<?= e($settings['pr_prefix'] ?? 'PR-2026-') ?>">
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label small fw-bold">Goods Receipt (GRN)</label>
                        <input type="text" name="grn_prefix" class="form-control font-monospace" value="<?= e($settings['grn_prefix'] ?? 'GRN-2026-') ?>">
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-bank me-1"></i>Invoice Bank & UPI Payment Instructions</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Official Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="<?= e($settings['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank Ltd.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Bank Account Number</label>
                        <input type="text" name="bank_account_no" class="form-control font-monospace" value="<?= e($settings['bank_account_no'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Bank IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="form-control font-monospace text-uppercase" value="<?= e($settings['bank_ifsc'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Official UPI ID / VPA</label>
                        <input type="text" name="bank_upi_id" class="form-control font-monospace" value="<?= e($settings['bank_upi_id'] ?? '') ?>" placeholder="company@upi">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Billing Settings
                    </button>
                </div>
            </form>
        </div>

        <?php elseif ($activeTab === 'notifications'): ?>
        <!-- ─── 3. NOTIFICATIONS & SMTP TAB ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-bell me-2 text-info"></i>Email & Automated System Alerts</h5>
                <p class="text-muted small mb-0">Configure SMTP email delivery credentials and automated inventory low-stock alert thresholds.</p>
            </div>

            <form action="<?= url('/settings/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="tab" value="notifications">

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-sliders me-1"></i>Inventory Threshold Alerts</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Critical Low-Stock Reorder Threshold</label>
                        <div class="input-group">
                            <input type="number" name="low_stock_threshold" class="form-control" value="<?= e($settings['low_stock_threshold'] ?? '10') ?>">
                            <span class="input-group-text">Units</span>
                        </div>
                        <small class="text-muted">Items with bin stock below this value trigger automated dashboard warnings.</small>
                    </div>
                </div>

                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-envelope-at me-1"></i>Outbound SMTP Email Server Configuration</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control font-monospace" value="<?= e($settings['smtp_host'] ?? 'smtp.gmail.com') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control font-monospace" value="<?= e($settings['smtp_port'] ?? '587') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Sender Email Address / Username</label>
                        <input type="email" name="smtp_username" class="form-control" value="<?= e($settings['smtp_username'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Display Sender Name</label>
                        <input type="text" name="smtp_sender_name" class="form-control" value="<?= e($settings['smtp_sender_name'] ?? 'Enterprise ERP System') ?>">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Notification Settings
                    </button>
                </div>
            </form>
        </div>

        <?php elseif ($activeTab === 'appearance'): ?>
        <!-- ─── 4. PREFERENCES & DISPLAY TAB ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-palette me-2 text-purple" style="color:#a855f7;"></i>Preferences & Display Settings</h5>
                <p class="text-muted small mb-0">Personalize system theme presets, regional date formatting, and user interface preferences.</p>
            </div>

            <form action="<?= url('/settings/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="tab" value="appearance">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Default System Theme Mode</label>
                        <select name="default_theme" class="form-select">
                            <option value="light" <?= ($settings['default_theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>☀️ Modern Light Mode (High Contrast)</option>
                            <option value="dark" <?= ($settings['default_theme'] ?? '') === 'dark' ? 'selected' : '' ?>>🌙 Cyber Dark Mode (OLED Slate)</option>
                            <option value="auto" <?= ($settings['default_theme'] ?? '') === 'auto' ? 'selected' : '' ?>>💻 System Auto (Follow OS Preference)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Date Format</label>
                        <select name="date_format" class="form-select font-monospace">
                            <option value="d-M-Y" <?= ($settings['date_format'] ?? 'd-M-Y') === 'd-M-Y' ? 'selected' : '' ?>>DD-MMM-YYYY (e.g. <?= date('d-M-Y') ?>)</option>
                            <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY (e.g. <?= date('d/m/Y') ?>)</option>
                            <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD (e.g. <?= date('Y-m-d') ?>)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Save Preferences
                    </button>
                </div>
            </form>
        </div>

        <?php elseif ($activeTab === 'security'): ?>
        <!-- ─── 5. MY PROFILE & SECURITY TAB ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-success"></i>My Profile & Password Security</h5>
                <p class="text-muted small mb-0">Update your user credentials, personal details, and workstation security settings.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-3 rounded-3 bg-light border">
                        <h6 class="fw-bold mb-3"><i class="bi bi-person-badge me-1 text-primary"></i>Personal Account Info</h6>
                        <div class="mb-2"><strong>Name:</strong> <?= e($user['name'] ?? 'User') ?></div>
                        <div class="mb-2"><strong>Email:</strong> <?= e($user['email'] ?? '') ?></div>
                        <div class="mb-2"><strong>Role Scope:</strong> <span class="badge bg-primary"><?= e($user['role_display'] ?? $user['role_name'] ?? 'Staff') ?></span></div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <form action="<?= url('/profile/change-password') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <h6 class="fw-bold mb-3"><i class="bi bi-key me-1 text-warning"></i>Change Password</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning px-3 py-2 fw-bold">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php elseif ($activeTab === 'backup' && $isSuperAdmin): ?>
        <!-- ─── 6. SYSTEM HEALTH & DATABASE BACKUP TAB (SUPER ADMIN) ─── -->
        <div class="card border-0 shadow-sm p-4">
            <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-database-fill-gear me-2 text-danger"></i>System Health & Database Snapshots</h5>
                    <p class="text-muted small mb-0">Automated SQL backups, server environment diagnostics, and cache management.</p>
                </div>
                <div class="d-flex gap-2">
                    <form action="<?= url('/settings/clear-cache') ?>" method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Clear temporary session artifacts and template cache?');">
                            <i class="bi bi-trash3 me-1"></i> Clear Cache
                        </button>
                    </form>
                    <form action="<?= url('/settings/backup') ?>" method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-success btn-sm fw-bold">
                            <i class="bi bi-download me-1"></i> Create 1-Click Backup
                        </button>
                    </form>
                </div>
            </div>

            <!-- Server Telemetry Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-3 bg-light border text-center">
                        <div class="text-muted small">Database Size</div>
                        <div class="fs-4 fw-extrabold text-primary font-monospace"><?= $sysInfo['database_size_mb'] ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-3 bg-light border text-center">
                        <div class="text-muted small">Total Tables</div>
                        <div class="fs-4 fw-extrabold text-success font-monospace"><?= $sysInfo['tables_count'] ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-3 bg-light border text-center">
                        <div class="text-muted small">PHP Version</div>
                        <div class="fs-4 fw-extrabold text-info font-monospace"><?= $sysInfo['php_version'] ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded-3 bg-light border text-center">
                        <div class="text-muted small">Memory Limit</div>
                        <div class="fs-4 fw-extrabold text-warning font-monospace"><?= $sysInfo['memory_limit'] ?></div>
                    </div>
                </div>
            </div>

            <!-- Backup History Table -->
            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-clock-history me-1"></i>Available SQL Database Snapshots</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Snapshot Filename</th>
                            <th>Filesize</th>
                            <th>Generated Timestamp</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($backups)): ?>
                            <?php foreach ($backups as $b): ?>
                                <tr>
                                    <td class="font-monospace fw-bold text-primary">
                                        <i class="bi bi-filetype-sql text-danger me-1.5 fs-5"></i><?= e($b['name']) ?>
                                    </td>
                                    <td class="font-monospace"><?= e($b['size']) ?></td>
                                    <td><?= e($b['created_at']) ?></td>
                                    <td><span class="badge bg-success">Ready on Disk</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-archive fs-3 d-block mb-1"></i>
                                    No database backup snapshots generated yet. Click "Create 1-Click Backup" above.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
