<?php
/**
 * Morgan Edition - Theme Index
 *
 * 메인 페이지 (테마 진입점)
 * 관리자 빌더로 구성된 레이아웃을 동적으로 렌더링
 */

if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

// head.php 포함 (HTML 시작 + 헤더 + 사이드바)
include_once(G5_THEME_PATH.'/head.php');
?>

<!-- 메인 페이지 콘텐츠 -->
<div class="max-w-6xl mx-auto">
    <?php echo mg_render_main(); ?>
</div>

<?php
// tail.php 포함 (푸터 + HTML 종료)
include_once(G5_THEME_PATH.'/tail.php');
