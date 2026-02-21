<?php
/**
 * Morgan Edition - 아이템 사용/해제 처리 (AJAX)
 */

include_once('./_common.php');

header('Content-Type: application/json');

// 로그인 체크
if ($is_guest) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

// 파라미터
$action = isset($_POST['action']) ? $_POST['action'] : '';
$si_id = isset($_POST['si_id']) ? (int)$_POST['si_id'] : 0;
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : null;

if (!$si_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 상품 정보
$item = mg_get_shop_item($si_id);
if (!$item) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 상품입니다.']);
    exit;
}

// 보유 확인
$my_count = mg_get_inventory_count($member['mb_id'], $si_id);
if ($my_count <= 0) {
    echo json_encode(['success' => false, 'message' => '보유하지 않은 아이템입니다.']);
    exit;
}

// 사용/해제 처리
if ($action == 'use') {
    $result = mg_use_item($member['mb_id'], $si_id, $ch_id);
    echo json_encode($result);
} else if ($action == 'unuse') {
    $result = mg_unuse_item($member['mb_id'], $si_id);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
}
