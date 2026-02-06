<?php
/**
 * Morgan Edition - 이모티콘 구매 처리 (AJAX)
 */

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

if ($is_guest) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

if (!mg_config('emoticon_use', '1')) {
    echo json_encode(array('success' => false, 'message' => '이모티콘 기능이 비활성화되어 있습니다.'));
    exit;
}

$es_id = isset($_POST['es_id']) ? (int)$_POST['es_id'] : 0;

if ($es_id <= 0) {
    echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
    exit;
}

$result = mg_buy_emoticon_set($member['mb_id'], $es_id);

echo json_encode($result);
