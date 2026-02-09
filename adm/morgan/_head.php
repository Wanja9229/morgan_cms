<?php
/**
 * Morgan Edition - Admin Header (Dark Theme)
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
if (file_exists(G5_PATH.'/plugin/morgan/morgan.php')) {
    include_once(G5_PATH.'/plugin/morgan/morgan.php');
}

// 메뉴 로드
$admin_menu = array();
$admin_menu_dir = G5_ADMIN_PATH;
$menu_files = glob($admin_menu_dir.'/admin.menu*.php');
foreach ($menu_files as $file) {
    include_once($file);
}
if (isset($menu)) {
    $admin_menu = $menu;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $g5['title']; ?> | Morgan Admin</title>
    <link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/style.css">
    <style>
        /* Morgan Admin Base Styles */
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--mg-bg-primary);
            color: var(--mg-text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }

        a {
            color: var(--mg-accent);
            text-decoration: none;
        }

        a:hover {
            color: var(--mg-accent-hover);
        }

        /* Admin Layout */
        .mg-admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .mg-admin-sidebar {
            width: 260px;
            background: var(--mg-bg-secondary);
            border-right: 1px solid var(--mg-bg-tertiary);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .mg-admin-sidebar.hidden {
            transform: translateX(-100%);
        }

        .mg-sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid var(--mg-bg-tertiary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mg-sidebar-logo {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--mg-accent);
        }

        .mg-sidebar-nav {
            padding: 0.5rem 0;
        }

        .mg-nav-section {
            margin-bottom: 0.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }

        .mg-nav-section:last-of-type {
            border-bottom: none;
        }

        .mg-nav-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--mg-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            user-select: none;
            transition: color 0.15s;
        }

        .mg-nav-title:hover {
            color: var(--mg-text-primary);
        }

        .mg-nav-title .mg-nav-arrow {
            width: 14px;
            height: 14px;
            transition: transform 0.2s;
            flex-shrink: 0;
            opacity: 0.5;
        }

        .mg-nav-section.collapsed .mg-nav-arrow {
            transform: rotate(-90deg);
        }

        .mg-nav-items {
            overflow: hidden;
            transition: max-height 0.25s ease;
        }

        .mg-nav-section.collapsed .mg-nav-items {
            max-height: 0 !important;
        }

        .mg-nav-item {
            display: block;
            padding: 0.5rem 1rem 0.5rem 1.25rem;
            color: var(--mg-text-secondary);
            transition: all 0.15s;
            font-size: 0.8125rem;
            border-left: 2px solid transparent;
        }

        .mg-nav-item:hover {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-primary);
            border-left-color: var(--mg-bg-tertiary);
        }

        .mg-nav-item.active {
            background: rgba(245, 159, 10, 0.12);
            color: var(--mg-accent);
            border-left-color: var(--mg-accent);
            font-weight: 500;
        }

        .mg-nav-item.active:hover {
            background: rgba(245, 159, 10, 0.18);
        }

        /* Main Content */
        .mg-admin-main {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .mg-admin-topbar {
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

        .mg-topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mg-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--mg-text-primary);
            cursor: pointer;
            padding: 0.5rem;
        }

        .mg-page-title {
            font-size: 1.125rem;
            font-weight: 600;
        }

        .mg-topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .mg-topbar-link {
            color: var(--mg-text-secondary);
            font-size: 0.875rem;
        }

        .mg-topbar-link:hover {
            color: var(--mg-text-primary);
        }

        /* Content Area */
        .mg-admin-content {
            flex: 1;
            padding: 1.5rem;
        }

        /* Cards */
        .mg-card {
            background: var(--mg-bg-secondary);
            border-radius: 0.5rem;
            border: 1px solid var(--mg-bg-tertiary);
            margin-bottom: 1.5rem;
        }

        .mg-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--mg-bg-tertiary);
            font-weight: 600;
        }

        .mg-card-body {
            padding: 1.25rem;
        }

        /* Tables */
        .mg-table {
            width: 100%;
            border-collapse: collapse;
        }

        .mg-table th,
        .mg-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--mg-bg-tertiary);
        }

        .mg-table th {
            background: var(--mg-bg-tertiary);
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--mg-text-muted);
        }

        .mg-table tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .mg-table td {
            font-size: 0.875rem;
        }

        /* Forms */
        .mg-form-group {
            margin-bottom: 1rem;
        }

        .mg-form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--mg-text-secondary);
        }

        .mg-form-input,
        .mg-form-select,
        .mg-form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: var(--mg-bg-primary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.375rem;
            color: var(--mg-text-primary);
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }

        .mg-form-input:focus,
        .mg-form-select:focus,
        .mg-form-textarea:focus {
            outline: none;
            border-color: var(--mg-accent);
        }

        .mg-form-input::placeholder {
            color: var(--mg-text-muted);
        }

        /* Buttons */
        .mg-btn {
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

        .mg-btn-primary {
            background: var(--mg-accent);
            color: #000;
        }

        .mg-btn-primary:hover {
            background: var(--mg-accent-hover);
        }

        .mg-btn-secondary {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-primary);
        }

        .mg-btn-secondary:hover {
            background: #3f4147;
        }

        .mg-btn-danger {
            background: var(--mg-error);
            color: #fff;
        }

        .mg-btn-danger:hover {
            background: #dc2626;
        }

        .mg-btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* Badges */
        .mg-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.25rem;
        }

        .mg-badge-success {
            background: rgba(34, 197, 94, 0.2);
            color: var(--mg-success);
        }

        .mg-badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: var(--mg-warning);
        }

        .mg-badge-error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--mg-error);
        }

        /* Alerts */
        .mg-alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .mg-alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
        }

        .mg-alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--mg-success);
        }

        .mg-alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--mg-error);
        }

        /* Tabs */
        .mg-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .mg-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--mg-text-secondary);
            background: var(--mg-bg-secondary);
            border-radius: 0.5rem;
            transition: all 0.15s;
            text-decoration: none;
        }

        .mg-tab:hover {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-primary);
        }

        .mg-tab.active {
            background: var(--mg-accent);
            color: #fff;
        }

        /* Pagination */
        .mg-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin-top: 1.5rem;
        }

        .mg-pagination a,
        .mg-pagination span {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            color: var(--mg-text-secondary);
        }

        .mg-pagination a:hover {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-primary);
        }

        .mg-pagination .active {
            background: var(--mg-accent);
            color: #000;
        }

        /* Checkbox & Radio */
        input[type="checkbox"],
        input[type="radio"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--mg-accent);
        }

        /* Stats Grid */
        .mg-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .mg-stat-card {
            background: var(--mg-bg-secondary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.5rem;
            padding: 1.25rem;
        }

        .mg-stat-label {
            font-size: 0.75rem;
            color: var(--mg-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .mg-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--mg-accent);
            margin-top: 0.25rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .mg-admin-sidebar {
                transform: translateX(-100%);
            }

            .mg-admin-sidebar.show {
                transform: translateX(0);
            }

            .mg-admin-main {
                margin-left: 0;
            }

            .mg-menu-toggle {
                display: flex;
            }

            .mg-sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 99;
            }

            .mg-sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .mg-admin-content {
                padding: 1rem;
            }

            .mg-table {
                display: block;
                overflow-x: auto;
            }

            .mg-topbar-right .mg-topbar-link span {
                display: none;
            }
        }

        /* Icon Size Utilities (Tailwind 호환) */
        .w-4 { width: 1rem; }
        .w-5 { width: 1.25rem; }
        .w-6 { width: 1.5rem; }
        .w-8 { width: 2rem; }
        .h-4 { height: 1rem; }
        .h-5 { height: 1.25rem; }
        .h-6 { height: 1.5rem; }
        .h-8 { height: 2rem; }
        .inline-block { display: inline-block; }
        .object-contain { object-fit: contain; }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--mg-bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--mg-bg-tertiary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4a4d55;
        }

        /* Modal */
        .mg-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
            overflow-y: auto;
        }

        .mg-modal-content {
            background: var(--mg-bg-secondary);
            border-radius: 0.5rem;
            border: 1px solid var(--mg-bg-tertiary);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .mg-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--mg-bg-tertiary);
        }

        .mg-modal-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .mg-modal-close {
            background: none;
            border: none;
            color: var(--mg-text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }

        .mg-modal-close:hover {
            color: var(--mg-text-primary);
        }

        .mg-modal-body {
            padding: 1.25rem;
        }

        .mg-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--mg-bg-tertiary);
        }

        /* Button Success */
        .mg-btn-success {
            background: var(--mg-success);
            color: #fff;
        }

        .mg-btn-success:hover {
            background: #16a34a;
        }

        /* Badge Primary */
        .mg-badge {
            background: var(--mg-bg-tertiary);
            color: var(--mg-text-secondary);
        }

        .mg-badge-primary {
            background: rgba(245, 159, 10, 0.2);
            color: var(--mg-accent);
        }
    </style>
</head>
<body>
    <div class="mg-sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="mg-admin-wrapper">
        <!-- Sidebar -->
        <aside class="mg-admin-sidebar" id="adminSidebar">
            <div class="mg-sidebar-header">
                <a href="<?php echo G5_ADMIN_URL; ?>/morgan/dashboard.php" class="mg-sidebar-logo">Morgan Admin</a>
                <button type="button" class="mg-menu-toggle" onclick="toggleSidebar()" style="display:none;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="mg-sidebar-nav">
                <!-- Morgan Edition 메뉴 (그룹별, 여닫기) -->
                <?php if (isset($admin_menu['menu800']) && !empty($admin_menu['menu800'])) {
                    $items = $admin_menu['menu800'];
                    $section_open = false;
                    $items_open = false;
                    $section_idx = 0;
                    // 현재 활성 메뉴가 속한 그룹 찾기
                    $active_group = '';
                    $cur_grp = '';
                    for ($i = 1; $i < count($items); $i++) {
                        if (isset($items[$i][4]) && $items[$i][4]) $cur_grp = $items[$i][4];
                        if (isset($sub_menu) && $items[$i][0] == $sub_menu) { $active_group = $cur_grp; break; }
                    }
                    $cur_grp = '';
                    for ($i = 1; $i < count($items); $i++) {
                        $item = $items[$i];
                        $item_id = $item[0] ?? '';
                        $item_name = $item[1] ?? '';
                        $item_url = $item[2] ?? '';
                        $item_group = isset($item[4]) ? $item[4] : '';
                        $is_active = (isset($sub_menu) && $sub_menu == $item_id) ? 'active' : '';

                        if ($item_group) {
                            // 이전 섹션 닫기
                            if ($items_open) { echo '</div>'; $items_open = false; }
                            if ($section_open) { echo '</div>'; $section_open = false; }
                            $cur_grp = $item_group;
                            $section_idx++;
                            $is_section_active = ($cur_grp === $active_group);
                ?>
                <div class="mg-nav-section<?php echo $is_section_active ? '' : ' collapsed'; ?>" data-section="<?php echo $section_idx; ?>">
                    <div class="mg-nav-title" onclick="toggleNavSection(this)">
                        <span><?php echo $item_group; ?></span>
                        <svg class="mg-nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="mg-nav-items" style="<?php echo $is_section_active ? '' : 'max-height:0;'; ?>">
                <?php
                            $section_open = true;
                            $items_open = true;
                        }
                ?>
                    <a href="<?php echo $item_url; ?>" class="mg-nav-item <?php echo $is_active; ?>"><?php echo $item_name; ?></a>
                <?php } // end for
                    if ($items_open) echo '</div>';
                    if ($section_open) echo '</div>';
                } ?>

                <!-- 구분선 + 바로가기 -->
                <div class="mg-nav-section" style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--mg-bg-tertiary);">
                    <div class="mg-nav-title">바로가기</div>
                    <a href="<?php echo G5_URL; ?>/" class="mg-nav-item" target="_blank">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:0.5rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        사이트 홈
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="mg-admin-main">
            <!-- Top Bar -->
            <header class="mg-admin-topbar">
                <div class="mg-topbar-left">
                    <button type="button" class="mg-menu-toggle" onclick="toggleSidebar()">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="mg-page-title"><?php echo $g5['title']; ?></h1>
                </div>
                <div class="mg-topbar-right">
                    <a href="<?php echo G5_URL; ?>/" class="mg-topbar-link" target="_blank">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>사이트 보기</span>
                    </a>
                    <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="mg-topbar-link">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>로그아웃</span>
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="mg-admin-content">
