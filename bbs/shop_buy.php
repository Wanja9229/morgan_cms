<?php
/**
 * Morgan Edition - 상품 구매 처리 (AJAX)
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

// 상점 사용 여부 체크
$shop_use = mg_get_config('shop_use', '1');
if ($shop_use != '1') {
    echo json_encode(['success' => false, 'message' => '상점 기능이 비활성화되어 있습니다.']);
    exit;
}

// 상품 ID
$si_id = isset($_POST['si_id']) ? (int)$_POST['si_id'] : 0;
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

// 구매 가능 여부 체크
$_can_buy = mg_can_buy_item($member['mb_id'], $si_id);
if (!$_can_buy['can_buy']) {
    echo json_encode(['success' => false, 'message' => $_can_buy['message']]);
    exit;
}

// 구매 처리
$result = mg_buy_item($member['mb_id'], $si_id);

if ($result['success']) {
    // 회원 정보 다시 조회
    $member = get_member($member['mb_id']);

    // Morgan: 업적 트리거 (상점 구매)
    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($member['mb_id'], 'shop_buy_count');
    }

    $response = array(
        'success' => true,
        'message' => $result['message'] ?? '구매가 완료되었습니다.',
        'new_point' => number_format($member['mb_point'])
    );
    // 칭호 뽑기 결과 전달
    if (isset($result['title_draw'])) {
        $response['title_draw'] = $result['title_draw'];
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
