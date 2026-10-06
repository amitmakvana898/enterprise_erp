// Enterprise ERP — Modern SaaS Client Engine v3.0

// Global Toggle Handlers
window.toggleErpSidebar = function() {
  const sb = document.getElementById('sidebar-wrapper');
  if (!sb) return;
  sb.classList.toggle('collapsed');
  const isCollapsed = sb.classList.contains('collapsed');
  localStorage.setItem('erp_sidebar_collapsed', isCollapsed ? 'true' : 'false');
};

window.toggleErpMobileSidebar = function() {
  const sb = document.getElementById('sidebar-wrapper');
  const overlay = document.getElementById('sidebarOverlay');
  if (!sb) return;
  sb.classList.toggle('mobile-open');
  if (overlay) overlay.classList.toggle('active');
};

document.addEventListener('DOMContentLoaded', function () {
  console.log('Enterprise ERP v3.0 — SaaS Engine Loaded');

  const sidebar = document.getElementById('sidebar-wrapper');
  const overlay = document.getElementById('sidebarOverlay');
  const desktopBtn = document.getElementById('sidebarToggleDesktop');
  const topbarBtn = document.getElementById('sidebarToggleTopbar');

  // Restore desktop collapsed state
  if (sidebar && window.innerWidth >= 992) {
    if (localStorage.getItem('erp_sidebar_collapsed') === 'true') {
      sidebar.classList.add('collapsed');
    }
  }

  // Universal Document-level Delegated Click for Mobile Overlay
  document.addEventListener('click', function (e) {
    if (overlay && e.target === overlay) {
      if (sidebar) sidebar.classList.remove('mobile-open');
      overlay.classList.remove('active');
    }
  });

  // ─── KEYBOARD SHORTCUTS ──────────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    // Ctrl/Cmd + K → Focus Search
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      const searchEl = document.getElementById('globalSearchInput');
      if (searchEl) {
        e.preventDefault();
        searchEl.focus();
        searchEl.select();
      }
    }
    // Ctrl/Cmd + B → Toggle Sidebar (desktop)
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
      if (sidebar && window.innerWidth >= 992) {
        e.preventDefault();
        window.toggleErpSidebar();
      }
    }
    // Escape → Close modals / sidebar overlay
    if (e.key === 'Escape') {
      if (sidebar && overlay && overlay.classList.contains('active')) {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
      }
    }
  });

  // ─── AUTO-DISMISS FLASH ALERTS (5s) ──────────────────────────────
  document.querySelectorAll('#flash-alerts-container .alert').forEach(function (el) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });

  // ─── BOOTSTRAP TOOLTIPS ───────────────────────────────────────────
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
  });

  // Sidebar collapsed item tooltips & Scroll persistence
  if (sidebar) {
    document.querySelectorAll('.sidebar-nav-item').forEach(function (item) {
      const label = item.querySelector('.nav-item-label');
      if (label) {
        item.setAttribute('data-bs-toggle-sidebar-tooltip', label.textContent.trim());
      }
    });

    // ─── PERSIST SIDEBAR SCROLL POSITION & AUTO-SCROLL ACTIVE ITEM ───
    const sidebarNav = sidebar.querySelector('.sidebar-nav') || sidebar;
    const savedScrollPos = localStorage.getItem('erp_sidebar_scroll');

    if (savedScrollPos !== null) {
      sidebar.scrollTop = parseInt(savedScrollPos, 10);
      if (sidebarNav && sidebarNav !== sidebar) {
        sidebarNav.scrollTop = parseInt(savedScrollPos, 10);
      }
    }

    const activeItem = sidebar.querySelector('.sidebar-nav-item.active');
    if (activeItem) {
      setTimeout(function () {
        activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }, 50);
    }

    function saveSidebarScroll() {
      const scrollPos = sidebarNav && sidebarNav !== sidebar && sidebarNav.scrollTop > 0 ? sidebarNav.scrollTop : sidebar.scrollTop;
      localStorage.setItem('erp_sidebar_scroll', scrollPos);
    }

    sidebar.querySelectorAll('.sidebar-nav-item').forEach(function (item) {
      item.addEventListener('click', saveSidebarScroll);
    });

    sidebar.addEventListener('scroll', saveSidebarScroll, { passive: true });
    if (sidebarNav && sidebarNav !== sidebar) {
      sidebarNav.addEventListener('scroll', saveSidebarScroll, { passive: true });
    }
  }

  // ─── CHART.JS LIGHT THEME DEFAULTS ───────────────────────────────
  if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family    = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size      = 12;
    Chart.defaults.color          = '#64748B';
    Chart.defaults.plugins.tooltip.backgroundColor = '#1A1D2E';
    Chart.defaults.plugins.tooltip.titleColor      = '#FFFFFF';
    Chart.defaults.plugins.tooltip.bodyColor       = '#CBD5E1';
    Chart.defaults.plugins.tooltip.padding         = 12;
    Chart.defaults.plugins.tooltip.cornerRadius    = 10;
    Chart.defaults.plugins.tooltip.displayColors   = true;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle    = 'circle';
    Chart.defaults.plugins.legend.labels.padding       = 16;
    Chart.defaults.animation.duration = 600;
    Chart.defaults.animation.easing   = 'easeInOutQuart';
  }

  // ─── THEME MODE ENGINE (Light / Dark / Auto) ─────────────────────
  const themeBtns = document.querySelectorAll('[data-theme-mode]');
  const headerIcon = document.getElementById('brightnessIconHeader');

  function applyTheme(mode) {
    let activeTheme = mode;
    if (mode === 'auto') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      activeTheme = prefersDark ? 'dark' : 'light';
    }

    if (activeTheme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      if (headerIcon) {
        headerIcon.className = 'bi bi-moon-stars-fill text-info';
      }
    } else {
      document.documentElement.removeAttribute('data-theme');
      if (headerIcon) {
        headerIcon.className = 'bi bi-brightness-high-fill text-warning';
      }
    }

    themeBtns.forEach(btn => {
      if (btn.getAttribute('data-theme-mode') === mode) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    localStorage.setItem('erp_theme_mode', mode);
    window.dispatchEvent(new CustomEvent('erp_theme_changed', { detail: { theme: activeTheme, mode: mode } }));
  }

  // Restore saved theme mode (default 'light')
  const savedTheme = localStorage.getItem('erp_theme_mode') || 'light';
  applyTheme(savedTheme);

  // Theme button listeners
  themeBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      applyTheme(this.getAttribute('data-theme-mode'));
    });
  });

  // Listen to OS theme changes if on auto
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
    if (localStorage.getItem('erp_theme_mode') === 'auto') {
      applyTheme('auto');
    }
  });

  // ─── BRIGHTNESS & DISPLAY ENGINE (High to Low) ───────────────────
  const brightnessInput = document.getElementById('brightnessRangeInput');
  const brightnessLabel = document.getElementById('brightnessPercentLabel');
  const presetBtns      = document.querySelectorAll('.brightness-preset-btn');

  function setBrightness(level, warm = 0) {
    const norm = Math.min(100, Math.max(60, parseInt(level, 10) || 100));
    const factor = (norm / 100).toFixed(2);
    const warmFactor = ((parseInt(warm, 10) || 0) / 100).toFixed(2);

    document.documentElement.style.setProperty('--app-brightness', factor);
    document.documentElement.style.setProperty('--app-warmth', warmFactor);

    if (brightnessInput) brightnessInput.value = norm;
    if (brightnessLabel) brightnessLabel.textContent = norm + '%';

    // Highlight active preset
    presetBtns.forEach(btn => {
      const pLevel = parseInt(btn.getAttribute('data-preset'), 10);
      const pWarm = parseInt(btn.getAttribute('data-warm') || '0', 10);
      if (pLevel === norm && pWarm === parseInt(warm, 10)) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    localStorage.setItem('erp_brightness_level', norm);
    localStorage.setItem('erp_brightness_warm', warm);
  }

  // Restore saved brightness
  const savedLevel = localStorage.getItem('erp_brightness_level') || '100';
  const savedWarm  = localStorage.getItem('erp_brightness_warm') || '0';
  setBrightness(savedLevel, savedWarm);

  // Slider change event
  if (brightnessInput) {
    brightnessInput.addEventListener('input', function (e) {
      setBrightness(e.target.value, 0);
    });
  }

  // Preset button clicks
  presetBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const preset = this.getAttribute('data-preset');
      const warm   = this.getAttribute('data-warm') || '0';
      setBrightness(preset, warm);
    });
  });

  // ─── NOTIFICATIONS ENGINE ─────────────────────────────────────────
  const markAllReadBtn  = document.getElementById('markAllReadBtn');
  const notifList       = document.getElementById('notifListContainer');
  const notifBadge      = document.getElementById('notifUnreadBadge');
  const notifDot        = document.getElementById('notifDotBadge');

  if (markAllReadBtn && notifList) {
    markAllReadBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      
      const baseUrl = window.location.pathname.startsWith('/enterprise_erp') ? '/enterprise_erp' : '';
      fetch(baseUrl + '/notifications/mark-all-read', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).catch(err => console.error('Failed to mark notifications read:', err));

      notifList.querySelectorAll('.notif-item.unread').forEach(item => {
        item.classList.remove('unread');
      });
      if (notifBadge) notifBadge.textContent = '0 New';
      if (notifDot) notifDot.style.display = 'none';
      markAllReadBtn.textContent = 'All Caught Up';
      markAllReadBtn.classList.add('text-success');
    });
  }

  if (notifList) {
    notifList.querySelectorAll('.notif-item').forEach(item => {
      item.addEventListener('click', function () {
        if (this.classList.contains('unread')) {
          this.classList.remove('unread');
          const remaining = notifList.querySelectorAll('.notif-item.unread').length;
          if (notifBadge) notifBadge.textContent = remaining > 0 ? remaining + ' New' : '0 New';
          if (remaining === 0 && notifDot) notifDot.style.display = 'none';
        }
      });
    });
  }

  // ─── TABLE ROW HOVER EFFECT ───────────────────────────────────────
  document.querySelectorAll('.table tbody tr').forEach(function (row) {
    row.style.transition = 'background 0.15s ease';
  });
});
