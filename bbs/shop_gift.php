<?php
/**
 * Morgan Edition - 선물 보내기 처리 (AJAX)
 */

include_once('./_common.php');

header('Content-Type: application/json');

// 로그인 체크
if ($is_guest) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// 레벨 3 이상만 상점 이용 가능
if ($member['mb_level'] < 3) {
    echo json_encode(['success' => false, 'message' => '캐릭터 승인 후 이용하실 수 있습니다.']);
    exit;
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

// 상점/선물 사용 여부 체크
$shop_use = mg_get_config('shop_use', '1');
$gift_use = mg_get_config('shop_gift_use', '1');
if ($shop_use != '1' || $gift_use != '1') {
    echo json_encode(['success' => false, 'message' => '선물 기능이 비활성화되어 있습니다.']);
    exit;
}

// 파라미터
$si_id = isset($_POST['si_id']) ? (int)$_POST['si_id'] : 0;
$mb_id_to = isset($_POST['mb_id_to']) ? trim($_POST['mb_id_to']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$si_id || !$mb_id_to) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 입력해주세요.']);
    exit;
}

// 자기 자신에게 선물 불가
if ($mb_id_to == $member['mb_id']) {
    echo json_encode(['success' => false, 'message' => '자기 자신에게는 선물할 수 없습니다.']);
    exit;
}

// 받는 사람 확인
$recipient = get_member($mb_id_to);
if (!$recipient['mb_id']) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 회원입니다.']);
    exit;
}

// 상품 정보
$item = mg_get_shop_item($si_id);
if (!$item) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 상품입니다.']);
    exit;
}

// 구매 가능 여부 체크 (선물도 구매와 동일한 조건 적용)
$can_buy = mg_can_buy_item($member['mb_id'], $si_id);
if ($can_buy !== true) {
    echo json_encode(['success' => false, 'message' => $can_buy]);
    exit;
}

// 선물 처리
$result = mg_send_gift($member['mb_id'], $mb_id_to, $si_id, $message);

if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => $recipient['mb_nick'] . '님에게 선물을 보냈습니다.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result]);
}
