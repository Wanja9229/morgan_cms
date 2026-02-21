<?php
/**
 * Morgan Edition - 선물 수락/거절 처리 (AJAX)
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
$gf_id = isset($_POST['gf_id']) ? (int)$_POST['gf_id'] : 0;

if (!$gf_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 수락 처리
if ($action == 'accept') {
    $result = mg_accept_gift($gf_id, $member['mb_id']);
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => '선물을 수락했습니다. 인벤토리를 확인해주세요.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
}
// 거절 처리
else if ($action == 'reject') {
    $result = mg_reject_gift($gf_id, $member['mb_id']);
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => '선물을 거절했습니다.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
}
