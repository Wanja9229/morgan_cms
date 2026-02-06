<?php
/**
 * Morgan Edition - 개척 자원 투입 처리 (AJAX)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

// 로그인 확인
if (!$is_member) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// 개척 시스템 활성화 확인
if (!mg_pioneer_enabled()) {
    echo json_encode(['success' => false, 'message' => '개척 시스템이 비활성화되어 있습니다.']);
    exit;
}

// 파라미터
$fc_id = isset($_POST['fc_id']) ? (int)$_POST['fc_id'] : 0;
$type = isset($_POST['type']) ? clean_xss_tags($_POST['type']) : '';
$mt_id = isset($_POST['mt_id']) ? (int)$_POST['mt_id'] : 0;
$amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

if ($fc_id < 1 || $amount < 1) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 시설 확인
$facility = mg_get_facility($fc_id);
if (!$facility) {
    echo json_encode(['success' => false, 'message' => '시설을 찾을 수 없습니다.']);
    exit;
}

if ($facility['fc_status'] !== 'building') {
    echo json_encode(['success' => false, 'message' => '건설 중인 시설에만 투입할 수 있습니다.']);
    exit;
}

// 투입 처리
if ($type === 'stamina') {
    $result = mg_contribute_stamina($fc_id, $member['mb_id'], $amount);
} elseif ($type === 'material' && $mt_id > 0) {
    $result = mg_contribute_material($fc_id, $member['mb_id'], $mt_id, $amount);
} else {
    $result = ['success' => false, 'message' => '잘못된 투입 유형입니다.'];
}

echo json_encode($result);
