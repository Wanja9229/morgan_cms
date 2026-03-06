<?php
global $lo_location;
global $lo_url;

if (!defined('_GNUBOARD_')) {
    include_once('./_common.php');
}

$msg = isset($msg) ? strip_tags($msg) : '';

$url = isset($url) ? clean_xss_tags($url, 1) : '';
if (!$url) $url = isset($_SERVER['HTTP_REFERER']) ? clean_xss_tags($_SERVER['HTTP_REFERER'], 1) : '';

$url = preg_replace("/[\<\>\'\"\\\'\\\"\(\)]/", "", $url);
$url = preg_replace('/\r\n|\r|\n|[^\x20-\x7e]/','', $url);

// url 체크
check_url_host($url, $msg);

// 플래시 토스트: 쿠키에 메시지 저장 → 리다이렉트 → 다음 페이지에서 표시
$toast_type = $error ? 'error' : 'success';
$toast_data = json_encode(array('msg' => $msg, 'type' => $toast_type), JSON_UNESCAPED_UNICODE);
setcookie('mg_flash_toast', $toast_data, time() + 10, '/');

$redirect_url = str_replace('&amp;', '&', $url);

if ($redirect_url) {
    header('Location: ' . $redirect_url);
    exit;
}

// URL 없으면 JS history.back 사용
if($error) {
    $g5['title'] = "오류안내 페이지";
} else {
    $g5['title'] = "결과안내 페이지";
}
include_once(G5_PATH.'/head.sub.php');
?>

<script>
if (typeof mgToast === 'function') {
    mgToast("<?php echo addslashes($msg); ?>", "<?php echo $toast_type; ?>", 3000);
}
setTimeout(function() { history.back(); }, 1500);
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
