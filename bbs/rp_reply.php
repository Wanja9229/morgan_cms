<?php
/**
 * Morgan Edition - 역극 이음 (Reply) AJAX 엔드포인트
 *
 * JSON 응답을 반환합니다.
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$rt_id = isset($_POST['rt_id']) ? (int)$_POST['rt_id'] : 0;
$rr_content = isset($_POST['rr_content']) ? trim($_POST['rr_content']) : '';
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;

if (!$rt_id || !$rr_content || !$ch_id) {
    echo json_encode(array('success' => false, 'message' => '필수 항목을 입력해주세요.'));
    exit;
}

// Verify character
$char = mg_get_character($ch_id);
if (!$char || $char['mb_id'] != $member['mb_id'] || $char['ch_state'] != 'approved') {
    echo json_encode(array('success' => false, 'message' => '유효하지 않은 캐릭터입니다.'));
    exit;
}

// Check join permission
$join_check = mg_can_join_rp($rt_id, $member['mb_id']);
if (!$join_check['can_join']) {
    echo json_encode(array('success' => false, 'message' => $join_check['message']));
    exit;
}

$result = mg_create_rp_reply(array(
    'rt_id' => $rt_id,
    'rr_content' => $rr_content,
    'rr_image' => '',
    'mb_id' => $member['mb_id'],
    'ch_id' => $ch_id,
));

// Morgan: RP 이음 알림 (역극 생성자에게)
if ($result['success']) {
    $thread = mg_get_rp_thread($rt_id);
    if ($thread && $thread['mb_id'] && $thread['mb_id'] !== $member['mb_id']) {
        $noti_url = G5_BBS_URL . '/rp_view.php?rt_id=' . $rt_id;
        mg_notify(
            $thread['mb_id'],
            'rp_reply',
            $char['ch_name'] . '님이 "' . mb_substr(strip_tags($thread['rt_title']), 0, 30) . '" 역극에 이음했습니다.',
            '',
            $noti_url
        );
    }

    // Morgan: RP 이음 재료 보상
    if (function_exists('mg_pioneer_enabled') && mg_pioneer_enabled()) {
        mg_reward_material($member['mb_id'], 'rp');
    }
}

echo json_encode($result);
