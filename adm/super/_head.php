<?php
/**
 * Morgan Super Admin — Layout Header
 */
if (!defined('_GNUBOARD_') || !defined('G5_IS_SUPER_ADMIN')) exit;

sa_check_auth();

$_sa_admin = sa_get_admin();
$_sa_current = basename($_SERVER['SCRIPT_NAME']);
$_sa_flash = sa_flash();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sa_h($sa_page_title ?: 'Super Admin'); ?> | Morgan Super Admin</title>
    <style>
        :root {
            --mg-bg-primary: #1e1f22;
            --mg-bg-secondary: #2b2d31;
            --mg-bg-tertiary: #313338;
            --mg-text-primary: #f2f3f5;
            --mg-text-secondary: #b5bac1;
            --mg-text-muted: #949ba4;
            --mg-accent: #f59f0a;
            --mg-accent-hover: #d97706;
            --mg-success: #22c55e;
            --mg-error: #ef4444;
            --mg-warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--mg-bg-primary);
            color: var(--mg-text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        a { color: var(--mg-accent); text-decoration: none; }
        a:hover { color: var(--mg-accent-hover); }

        /* Layout */
        .sa-wrapper { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sa-sidebar {
            width: 240px;
            background: var(--mg-bg-secondary);
            border-right: 1px solid var(--mg-bg-tertiary);
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sa-sidebar-header {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--mg-bg-tertiary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sa-sidebar-logo {
            font-size: 1rem;
            font-weight: bold;
            color: var(--mg-accent);
        }

        .sa-sidebar-logo small {
            display: block;
            font-size: 0.625rem;
            font-weight: 400;
            color: var(--mg-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .sa-nav { padding: 0.5rem 0; }

        .sa-nav-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 1rem;
            color: var(--mg-text-secondary);
            font-size: 0.8125rem;
            border-left: 2px solid transparent;
            transition: all 0.15s;
        }

        .sa-nav-item:hover {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-primary);
        }

        .sa-nav-item.active {
            background: rgba(245, 159, 10, 0.12);
            color: var(--mg-accent);
            border-left-color: var(--mg-accent);
            font-weight: 500;
        }

        .sa-nav-item svg { flex-shrink: 0; }

        .sa-nav-divider {
            height: 1px;
            background: var(--mg-bg-tertiary);
            margin: 0.5rem 1rem;
        }

        /* Main */
        .sa-main {
            flex: 1;
            margin-left: 240px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sa-topbar {
            background: var(--mg-bg-secondary);
            border-bottom: 1px solid var(--mg-bg-tertiary);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .sa-topbar-left { display: flex; align-items: center; gap: 1rem; }

        .sa-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--mg-text-primary);
            cursor: pointer;
            padding: 0.5rem;
        }

        .sa-topbar h1 { font-size: 1.125rem; font-weight: 600; }

        .sa-topbar-right { display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; }
        .sa-topbar-right a { color: var(--mg-text-secondary); }
        .sa-topbar-right a:hover { color: var(--mg-text-primary); }

        .sa-content { flex: 1; padding: 1.5rem; }

        /* Cards */
        .sa-card {
            background: var(--mg-bg-secondary);
            border-radius: 0.5rem;
            border: 1px solid var(--mg-bg-tertiary);
            margin-bottom: 1.5rem;
        }
        .sa-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--mg-bg-tertiary);
            font-weight: 600;
        }
        .sa-card-body { padding: 1.25rem; }

        /* Stats Grid */
        .sa-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .sa-stat-card {
            background: var(--mg-bg-secondary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.5rem;
            padding: 1.25rem;
        }
        .sa-stat-label {
            font-size: 0.75rem;
            color: var(--mg-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .sa-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--mg-accent);
            margin-top: 0.25rem;
        }

        /* Tables */
        .sa-table { width: 100%; border-collapse: collapse; }
        .sa-table th, .sa-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--mg-bg-tertiary);
        }
        .sa-table th {
            background: var(--mg-bg-tertiary);
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--mg-text-muted);
        }
        .sa-table tr:hover { background: rgba(255,255,255,0.02); }
        .sa-table td { font-size: 0.875rem; }

        /* Forms */
        .sa-form-group { margin-bottom: 1rem; }
        .sa-form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--mg-text-secondary);
        }
        .sa-form-input, .sa-form-select, .sa-form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: var(--mg-bg-primary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.375rem;
            color: var(--mg-text-primary);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .sa-form-input:focus, .sa-form-select:focus, .sa-form-textarea:focus {
            outline: none;
            border-color: var(--mg-accent);
        }
        .sa-form-input::placeholder { color: var(--mg-text-muted); }
        .sa-form-help { font-size: 0.75rem; color: var(--mg-text-muted); margin-top: 0.25rem; }

        /* Buttons */
        .sa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            gap: 0.5rem;
        }
        .sa-btn-primary { background: var(--mg-accent); color: #fff; }
        .sa-btn-primary:hover { background: var(--mg-accent-hover); }
        .sa-btn-secondary { background: var(--mg-bg-tertiary); color: var(--mg-text-primary); }
        .sa-btn-secondary:hover { background: #3f4147; }
        .sa-btn-danger { background: var(--mg-error); color: #fff; }
        .sa-btn-danger:hover { background: #dc2626; }
        .sa-btn-success { background: var(--mg-success); color: #fff; }
        .sa-btn-success:hover { background: #16a34a; }
        .sa-btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }

        /* Badges */
        .sa-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.25rem;
        }
        .sa-badge-success { background: rgba(34,197,94,0.2); color: var(--mg-success); }
        .sa-badge-warning { background: rgba(245,158,11,0.2); color: var(--mg-warning); }
        .sa-badge-error { background: rgba(239,68,68,0.2); color: var(--mg-error); }
        .sa-badge-info { background: rgba(59,130,246,0.2); color: #60a5fa; }

        /* Alerts */
        .sa-alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .sa-alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: var(--mg-success); }
        .sa-alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--mg-error); }
        .sa-alert-info { background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.3); color: #60a5fa; }

        /* Pagination */
        .sa-pagination { display: flex; align-items: center; justify-content: center; gap: 0.25rem; margin-top: 1.5rem; }
        .sa-pagination a, .sa-pagination span { padding: 0.5rem 0.75rem; font-size: 0.875rem; border-radius: 0.25rem; color: var(--mg-text-secondary); }
        .sa-pagination a:hover { background: var(--mg-bg-tertiary); color: var(--mg-text-primary); }
        .sa-pagination .active { background: var(--mg-accent); color: #000; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--mg-bg-primary); }
        ::-webkit-scrollbar-thumb { background: var(--mg-bg-tertiary); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4a4d55; }

        /* Responsive */
        @media (max-width: 768px) {
            .sa-sidebar { transform: translateX(-100%); }
            .sa-sidebar.show { transform: translateX(0); }
            .sa-main { margin-left: 0; }
            .sa-menu-toggle { display: flex; }
            .sa-content { padding: 1rem; }
            .sa-sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 99;
            }
            .sa-sidebar-overlay.show { display: block; }
        }
    </style>
</head>
<body>
    <div class="sa-sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sa-wrapper">
        <aside class="sa-sidebar" id="saSidebar">
            <div class="sa-sidebar-header">
                <a href="<?php echo sa_url('index.php'); ?>" class="sa-sidebar-logo">
                    Morgan
                    <small>Super Admin</small>
                </a>
            </div>

            <nav class="sa-nav">
                <a href="<?php echo sa_url('index.php'); ?>" class="sa-nav-item <?php echo $_sa_current === 'index.php' ? 'active' : ''; ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    대시보드
                </a>

                <div class="sa-nav-divider"></div>

                <a href="<?php echo sa_url('tenants.php'); ?>" class="sa-nav-item <?php echo $_sa_current === 'tenants.php' ? 'active' : ''; ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    테넌트 관리
                </a>
                <a href="<?php echo sa_url('tenant_form.php'); ?>" class="sa-nav-item <?php echo $_sa_current === 'tenant_form.php' && empty($_GET['id']) ? 'active' : ''; ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    테넌트 생성
                </a>

                <div class="sa-nav-divider"></div>

                <a href="<?php echo sa_url('provision_log.php'); ?>" class="sa-nav-item <?php echo $_sa_current === 'provision_log.php' ? 'active' : ''; ?>">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    프로비저닝 로그
                </a>

                <div class="sa-nav-divider"></div>

                <a href="<?php echo sa_url('logout.php'); ?>" class="sa-nav-item">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    로그아웃
                </a>
            </nav>
        </aside>

        <main class="sa-main">
            <header class="sa-topbar">
                <div class="sa-topbar-left">
                    <button type="button" class="sa-menu-toggle" onclick="toggleSidebar()">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1><?php echo sa_h($sa_page_title ?: 'Super Admin'); ?></h1>
                </div>
                <div class="sa-topbar-right">
                    <span style="color:var(--mg-text-muted)"><?php echo sa_h($_sa_admin['username']); ?></span>
                </div>
            </header>

            <div class="sa-content">
                <?php if ($_sa_flash): ?>
                <div class="sa-alert sa-alert-success"><?php echo sa_h($_sa_flash); ?></div>
                <?php endif; ?>
