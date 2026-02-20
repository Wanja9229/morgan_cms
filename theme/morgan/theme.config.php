<?php
/**
 * Morgan Edition Theme Config
 *
 * 자캐 커뮤니티 특화형 CMS 테마
 */

if (!defined('_GNUBOARD_')) exit;

// 테마 정보
define('MG_THEME_NAME', 'Morgan Edition');
define('MG_THEME_VERSION', '1.0.0');

// 테마 경로 (G5_THEME_PATH는 이미 테마 폴더 경로를 포함함)
define('MG_THEME_PATH', G5_THEME_PATH);
define('MG_THEME_URL', G5_THEME_URL);

// Morgan Edition 전용 설정
define('MG_USE_DARK_MODE', true);  // 다크모드 기본 사용
define('MG_SIDEBAR_WIDTH', 56);    // 사이드바 너비 (px)

// 스킨 경로 설정 (테마 내 스킨 우선 사용)
$theme_skin_path = MG_THEME_PATH.'/skin';

// 게시판 스킨
if (is_dir($theme_skin_path.'/board')) {
    define('MG_BOARD_SKIN_PATH', $theme_skin_path.'/board');
    define('MG_BOARD_SKIN_URL', MG_THEME_URL.'/skin/board');
}

// 회원 스킨
if (is_dir($theme_skin_path.'/member')) {
    define('MG_MEMBER_SKIN_PATH', $theme_skin_path.'/member');
    define('MG_MEMBER_SKIN_URL', MG_THEME_URL.'/skin/member');
}

// 콘텐츠 스킨
if (is_dir($theme_skin_path.'/content')) {
    define('MG_CONTENT_SKIN_PATH', $theme_skin_path.'/content');
    define('MG_CONTENT_SKIN_URL', MG_THEME_URL.'/skin/content');
}

// 회원 스킨 경로 덮어쓰기 (그누보드 기본 스킨 대신 테마 스킨 사용)
if (is_dir($theme_skin_path.'/member')) {
    $member_skin_path = $theme_skin_path.'/member';
    $member_skin_url = MG_THEME_URL.'/skin/member';
}

// CSS 변수 기본값 (테마 시스템용)
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
    'button-text'   => '#ffffff',
);

// 배경 이미지 설정
$mg_theme_bg = array(
    'image'   => '',
    'opacity' => 20
);

// DB에서 디자인 설정 로드 (설정이 있으면 덮어쓰기)
// 테이블명 직접 지정 (morgan.php 로드 전에도 동작하도록)
$mg_config_table = isset($g5['mg_config_table']) ? $g5['mg_config_table'] : 'mg_config';

if (function_exists('sql_query') && isset($g5['connect_db'])) {
    $sql = "SELECT cf_key, cf_value FROM {$mg_config_table}
            WHERE cf_key IN ('color_accent', 'color_button', 'color_button_text', 'color_text', 'color_border', 'color_bg_primary', 'color_bg_secondary', 'bg_image', 'bg_opacity')";
    $result = sql_query($sql, false);

    if ($result) {
        while ($row = sql_fetch_array($result)) {
            switch ($row['cf_key']) {
                case 'color_accent':
                    $mg_theme_colors['accent'] = $row['cf_value'];
                    $mg_theme_colors['accent-hover'] = mg_darken_color($row['cf_value'], 15);
                    break;
                case 'color_button':
                    $mg_theme_colors['button'] = $row['cf_value'];
                    $mg_theme_colors['button-hover'] = mg_darken_color($row['cf_value'], 15);
                    break;
                case 'color_button_text':
                    $mg_theme_colors['button-text'] = $row['cf_value'];
                    break;
                case 'color_text':
                    $mg_theme_colors['text-primary'] = $row['cf_value'];
                    $mg_theme_colors['text-secondary'] = mg_darken_color($row['cf_value'], 20);
                    $mg_theme_colors['text-muted'] = mg_darken_color($row['cf_value'], 38);
                    break;
                case 'color_border':
                    $mg_theme_colors['bg-tertiary'] = $row['cf_value'];
                    break;
                case 'color_bg_primary':
                    $mg_theme_colors['bg-primary'] = $row['cf_value'];
                    break;
                case 'color_bg_secondary':
                    $mg_theme_colors['bg-secondary'] = $row['cf_value'];
                    break;
                case 'bg_image':
                    $mg_theme_bg['image'] = $row['cf_value'];
                    break;
                case 'bg_opacity':
                    $mg_theme_bg['opacity'] = (int)$row['cf_value'];
                    break;
            }
        }
    }
}

// 색상 어둡게 만드는 헬퍼 함수
function mg_darken_color($hex, $percent) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, $r - ($r * $percent / 100));
    $g = max(0, $g - ($g * $percent / 100));
    $b = max(0, $b - ($b * $percent / 100));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
