<?php
/**
 * Morgan Edition - Head Sub (HTML 시작 부분)
 *
 * 그누보드 표준 구조 준수
 */

if (!defined('_GNUBOARD_')) exit;

// 테마 설정 로드 (CSS 변수용 색상 등)
if (!isset($mg_theme_colors) || empty($mg_theme_colors)) {
    @include_once(G5_THEME_PATH.'/theme.config.php');
}

// 기본값 보장
if (!isset($mg_theme_colors) || empty($mg_theme_colors)) {
    $mg_theme_colors = array(
        'bg-primary'    => '#1e1f22',
        'bg-secondary'  => '#2b2d31',
        'bg-tertiary'   => '#313338',
        'text-primary'  => '#f2f3f5',
        'text-secondary'=> '#b5bac1',
        'text-muted'    => '#949ba4',
        'accent'        => '#f59f0a',
        'accent-hover'  => '#d97706',
        'button'        => '#f59f0a',
        'button-hover'  => '#d97706',
    );
}
if (!isset($mg_theme_bg)) {
    $mg_theme_bg = array('image' => '', 'opacity' => 20);
}

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

// SPA-like: AJAX 요청 감지
$is_ajax_request = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_GET['_ajax']) && $_GET['_ajax'] === '1')
);

// AJAX 요청이면 레이아웃 없이 콘텐츠만 출력
if ($is_ajax_request) {
    // 페이지명만 헤더로 전달 (JS에서 사이트명 조합)
    $page_title = isset($g5['title']) ? strip_tags($g5['title']) : '';
    header('X-Page-Title: ' . rawurlencode($page_title));
    header('Content-Type: text/html; charset=utf-8');
    return; // head.sub.php 나머지 건너뛰기
}

// Morgan 사이트명 통합
$mg_site_name = function_exists('mg_config') ? mg_config('site_name', $config['cf_title']) : $config['cf_title'];

if (!isset($g5['title']) || $g5['title'] === $config['cf_title'] || $g5['title'] === $mg_site_name) {
    $g5['title'] = $mg_site_name;
    $g5_head_title = $mg_site_name;
} else {
    $g5_head_title = $mg_site_name . ' | ' . $g5['title'];
}

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 현재 접속자
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/'.G5_ADMIN_DIR.'/') || $is_admin == 'super') $g5['lo_url'] = '';
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="mg-bbs-url" content="<?php echo G5_BBS_URL; ?>">
    <?php if($config['cf_add_meta']) echo $config['cf_add_meta'].PHP_EOL; ?>

    <title><?php echo $g5_head_title; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo G5_THEME_URL; ?>/img/favicon.ico">

    <!-- Google Fonts (사이트 폰트) -->
    <?php
    $mg_site_font = function_exists('mg_config') ? mg_config('site_font', 'Noto Sans KR') : 'Noto Sans KR';
    if ($mg_site_font === 'Pretendard Variable') {
        // Pretendard는 별도 CDN
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">';
    } else {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
        echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . urlencode($mg_site_font) . ':wght@300;400;500;700&display=swap">';
    }
    ?>

    <!-- Morgan Edition CSS (Tailwind) -->
    <link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/style.css?ver=<?php echo G5_CSS_VER; ?>">

    <!-- CSS 변수 (테마 컬러) -->
    <style>
        :root {
            /* Tailwind v4 연동 (--color- 접두사) */
            --color-mg-bg-primary: <?php echo $mg_theme_colors['bg-primary']; ?>;
            --color-mg-bg-secondary: <?php echo $mg_theme_colors['bg-secondary']; ?>;
            --color-mg-bg-tertiary: <?php echo $mg_theme_colors['bg-tertiary']; ?>;
            --color-mg-text-primary: <?php echo $mg_theme_colors['text-primary']; ?>;
            --color-mg-text-secondary: <?php echo $mg_theme_colors['text-secondary']; ?>;
            --color-mg-text-muted: <?php echo $mg_theme_colors['text-muted']; ?>;
            --color-mg-accent: <?php echo $mg_theme_colors['accent']; ?>;
            --color-mg-accent-hover: <?php echo $mg_theme_colors['accent-hover']; ?>;
            --color-mg-success: #22c55e;
            --color-mg-warning: #eab308;
            --color-mg-error: #ef4444;

            /* 호환 별칭 — var(--mg-*) 직접 참조용 (관리자·위젯·인라인 등 47개 파일) */
            --mg-bg-primary: var(--color-mg-bg-primary);
            --mg-bg-secondary: var(--color-mg-bg-secondary);
            --mg-bg-tertiary: var(--color-mg-bg-tertiary);
            --mg-text-primary: var(--color-mg-text-primary);
            --mg-text-secondary: var(--color-mg-text-secondary);
            --mg-text-muted: var(--color-mg-text-muted);
            --mg-accent: var(--color-mg-accent);
            --mg-accent-hover: var(--color-mg-accent-hover);
            --mg-success: var(--color-mg-success);
            --mg-warning: var(--color-mg-warning);
            --mg-error: var(--color-mg-error);
            --mg-button: <?php echo isset($mg_theme_colors['button']) ? $mg_theme_colors['button'] : $mg_theme_colors['accent']; ?>;
            --mg-button-hover: <?php echo isset($mg_theme_colors['button-hover']) ? $mg_theme_colors['button-hover'] : $mg_theme_colors['accent-hover']; ?>;
            --mg-button-text: <?php echo isset($mg_theme_colors['button-text']) ? $mg_theme_colors['button-text'] : '#ffffff'; ?>;
            --mg-content-width: <?php echo function_exists('mg_config') ? mg_config('content_max_width', '72rem') : '72rem'; ?>;
            --mg-font-family: '<?php echo $mg_site_font; ?>', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        body { font-family: var(--mg-font-family) !important; }
        .mg-inner { max-width: var(--mg-content-width); margin-left: auto; margin-right: auto; }
        /* 반응형 유틸리티 보완 (Tailwind 빌드 누락분) */
        @media (min-width: 40rem) {
            .sm\:inline-flex { display: inline-flex !important; }
            .sm\:inline { display: inline !important; }
        }
        @media (min-width: 64rem) {
            .lg\:ml-14 { margin-left: calc(var(--spacing) * 14); }
            #sidebar-backdrop { display: none !important; }
        }
        .px-2\.5 { padding-left: 0.625rem; padding-right: 0.625rem; }
        /* 버튼 색상 오버라이드 */
        .btn-primary, .mg-btn-primary {
            background-color: var(--mg-button) !important;
            color: var(--mg-button-text) !important;
        }
        .btn-primary:hover, .mg-btn-primary:hover {
            background-color: var(--mg-button-hover) !important;
        }
        <?php if (!empty($mg_theme_bg['image'])): ?>
        /* 배경 이미지 */
        #main-content {
            position: relative;
            isolation: isolate; /* stacking context 생성 (fixed 위치에 영향 없음) */
        }
        #main-content::before {
            content: '';
            position: fixed;
            top: 48px; /* 헤더 높이 */
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo htmlspecialchars($mg_theme_bg['image']); ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: <?php echo (100 - $mg_theme_bg['opacity']) / 100; ?>;
            pointer-events: none;
            z-index: -1; /* isolate 내에서 콘텐츠 뒤로 배치 */
        }
        @media (min-width: 1024px) {
            #main-content::before {
                left: 56px; /* 사이드바 너비 */
            }
        }
        <?php endif; ?>
        /* 메인 페이지 그리드 캔버스 — 모바일 반응형 */
        @media (max-width: 768px) {
            .mg-grid-canvas {
                display: flex !important;
                flex-direction: column;
                gap: 0.5rem;
            }
            .mg-grid-widget {
                grid-column: unset !important;
                grid-row: unset !important;
                width: 100% !important;
                min-height: 200px;
            }
        }

        /* 프로필 필드 값 렌더링 */
        .mg-pf-link { color: var(--mg-accent); text-decoration: none; }
        .mg-pf-link:hover { text-decoration: underline; }
        .mg-pf-tags { display: inline-flex; flex-wrap: wrap; gap: 0.25rem; }
        .mg-pf-tag { background: var(--mg-bg-tertiary); color: var(--mg-text-secondary); padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.8125rem; }
        .mg-pf-image { max-width: 100%; max-height: 20rem; border-radius: 0.5rem; object-fit: contain; }
    </style>

    <!-- 그누보드 전역 JS 변수 -->
    <script>
    var g5_url       = "<?php echo G5_URL ?>";
    var g5_bbs_url   = "<?php echo G5_BBS_URL ?>";
    var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
    var g5_is_admin  = "<?php echo isset($is_admin)?$is_admin:''; ?>";
    var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
    var g5_bo_table  = "<?php echo isset($bo_table)?$bo_table:''; ?>";
    var g5_sca       = "<?php echo isset($sca)?$sca:''; ?>";
    var g5_editor    = "<?php echo ($config['cf_editor'] && isset($board['bo_use_dhtml_editor']) && $board['bo_use_dhtml_editor'])?$config['cf_editor']:''; ?>";
    var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
    var g5_site_title = "<?php echo addslashes($mg_site_name); ?>";
    <?php if(defined('G5_IS_ADMIN')) { ?>
    var g5_admin_url = "<?php echo G5_ADMIN_URL; ?>";
    <?php } ?>
    </script>

    <!-- 그누보드 기본 JS -->
    <script src="<?php echo G5_JS_URL; ?>/jquery-1.12.4.min.js"></script>
    <script src="<?php echo G5_JS_URL; ?>/jquery-migrate-1.4.1.min.js"></script>
    <script src="<?php echo G5_JS_URL; ?>/common.js?ver=<?php echo G5_JS_VER; ?>"></script>
    <script src="<?php echo G5_JS_URL; ?>/wrest.js?ver=<?php echo G5_JS_VER; ?>"></script>

    <!-- Morgan JS -->
    <script src="<?php echo G5_THEME_URL; ?>/js/notification.js?ver=<?php echo G5_JS_VER; ?>"></script>

    <?php if(!defined('G5_IS_ADMIN')) echo $config['cf_add_script']; ?>
</head>
<body class="bg-mg-bg-primary text-mg-text-primary min-h-screen"<?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?>>

<!-- Skip Navigation -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 bg-mg-accent text-white px-4 py-2 rounded z-[100]">
    본문 바로가기
</a>

<!-- App Container -->
<div id="app" class="flex flex-col min-h-screen">
