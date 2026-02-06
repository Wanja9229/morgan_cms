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
    // 타이틀 정보는 JSON 헤더로 전달
    if (!isset($g5['title'])) {
        $g5['title'] = $config['cf_title'];
    }
    header('X-Page-Title: ' . rawurlencode(strip_tags($g5['title'])));
    header('Content-Type: text/html; charset=utf-8');
    return; // head.sub.php 나머지 건너뛰기
}

if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
} else {
    $g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
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

    <!-- Morgan Edition CSS (Tailwind) -->
    <link rel="stylesheet" href="<?php echo G5_THEME_URL; ?>/css/style.css?ver=<?php echo G5_CSS_VER; ?>">

    <!-- CSS 변수 (테마 컬러) -->
    <style>
        :root {
            --mg-bg-primary: <?php echo $mg_theme_colors['bg-primary']; ?>;
            --mg-bg-secondary: <?php echo $mg_theme_colors['bg-secondary']; ?>;
            --mg-bg-tertiary: <?php echo $mg_theme_colors['bg-tertiary']; ?>;
            --mg-text-primary: <?php echo $mg_theme_colors['text-primary']; ?>;
            --mg-text-secondary: <?php echo $mg_theme_colors['text-secondary']; ?>;
            --mg-text-muted: <?php echo $mg_theme_colors['text-muted']; ?>;
            --mg-accent: <?php echo $mg_theme_colors['accent']; ?>;
            --mg-accent-hover: <?php echo $mg_theme_colors['accent-hover']; ?>;
            --mg-button: <?php echo isset($mg_theme_colors['button']) ? $mg_theme_colors['button'] : $mg_theme_colors['accent']; ?>;
            --mg-button-hover: <?php echo isset($mg_theme_colors['button-hover']) ? $mg_theme_colors['button-hover'] : $mg_theme_colors['accent-hover']; ?>;
        }
        /* 버튼 색상 오버라이드 */
        .btn-primary, .mg-btn-primary {
            background-color: var(--mg-button) !important;
        }
        .btn-primary:hover, .mg-btn-primary:hover {
            background-color: var(--mg-button-hover) !important;
        }
        <?php if (!empty($mg_theme_bg['image'])): ?>
        /* 배경 이미지 */
        #main-content {
            position: relative;
        }
        #main-content::before {
            content: '';
            position: fixed;
            top: 48px; /* 헤더 높이 */
            left: 56px; /* 사이드바 너비 */
            right: 0;
            bottom: 0;
            background-image: url('<?php echo htmlspecialchars($mg_theme_bg['image']); ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: <?php echo $mg_theme_bg['opacity'] / 100; ?>;
            pointer-events: none;
            z-index: 0;
        }
        #main-content > * {
            position: relative;
            z-index: 1;
        }
        <?php endif; ?>
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
