<?php
/**
 * Morgan Edition - Tail Sub (HTML 종료 부분)
 *
 * head.sub.php와 짝을 이루는 최소 종료 파일
 */

if (!defined('_GNUBOARD_')) exit;

// AJAX 요청이면 레이아웃 없이 종료
$is_ajax_request = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_GET['_ajax']) && $_GET['_ajax'] === '1')
);
if ($is_ajax_request) {
    return;
}
?>

</div><!-- /#app -->

<!-- Morgan Edition JS -->
<script src="<?php echo G5_THEME_URL; ?>/js/app.js?ver=<?php echo G5_JS_VER; ?>"></script>

<?php
// 그누보드 JS 출력
if (function_exists('get_javascript_file')) {
    echo get_javascript_file();
}
?>

</body>
</html>
