<?php
/**
 * Morgan Edition - 역극 이음 (Reply) AJAX 엔드포인트
 *
 * JSON 응답을 반환합니다.
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (mg_config('rp_use', '1') != '1') {
    echo json_encode(array('success' => false, 'message' => '역극 기능이 비활성화되어 있습니다.'));
    exit;
}

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

// 회원 레벨 체크
$_lv = mg_check_member_level('rp', $member['mb_level']);
if (!$_lv['allowed']) {
    echo json_encode(array('success' => false, 'message' => "역극은 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다."));
    exit;
}

// Morgan: 개척 시스템 해금 체크
if (function_exists('mg_is_board_unlocked') && !mg_is_board_unlocked('roleplay')) {
    echo json_encode(array('success' => false, 'message' => '역극은 아직 개척되지 않았습니다.'));
    exit;
}

$rt_id = isset($_POST['rt_id']) ? (int)$_POST['rt_id'] : 0;
$rr_content = isset($_POST['rr_content']) ? trim($_POST['rr_content']) : '';
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
$context_ch_id = isset($_POST['context_ch_id']) ? (int)$_POST['context_ch_id'] : 0;

$has_image = (isset($_FILES['rr_image']) && $_FILES['rr_image']['error'] == 0);
if (!$rt_id || !$ch_id || (!$rr_content && !$has_image)) {
    echo json_encode(array('success' => false, 'message' => '내용 또는 이미지를 입력해주세요.'));
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

// Image upload
$rr_image = '';
if (isset($_FILES['rr_image']) && $_FILES['rr_image']['error'] == 0) {
    if ($_FILES['rr_image']['size'] > mg_upload_max_file()) {
        echo json_encode(array('success' => false, 'message' => '파일 크기가 너무 큽니다.'));
        exit;
    }
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($_FILES['rr_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $upload_dir = G5_DATA_PATH . '/rp/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        $filename = 'rp_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['rr_image']['tmp_name'], $upload_dir . $filename)) {
            $rr_image = G5_DATA_URL . '/rp/' . $filename;
        }
    }
}

$result = mg_create_rp_reply(array(
    'rt_id' => $rt_id,
    'rr_content' => $rr_content,
    'rr_image' => $rr_image,
    'mb_id' => $member['mb_id'],
    'ch_id' => $ch_id,
    'rr_context_ch_id' => $context_ch_id,
));

// Morgan: RP 이음 알림 (역극 생성자에게)
if ($result['success']) {
    $thread = mg_get_rp_thread($rt_id);
    if ($thread && $thread['mb_id'] && $thread['mb_id'] !== $member['mb_id']) {
        $noti_url = G5_BBS_URL . '/rp_list.php#rp-thread-' . $rt_id;
        mg_notify(
            $thread['mb_id'],
            'rp_reply',
            $char['ch_name'] . '님이 "' . mb_substr(strip_tags($thread['rt_title']), 0, 30) . '" 역극에 댓글을 남겼습니다.',
            '',
            $noti_url
        );
    }

    // Morgan: RP 이음 재료 보상
    if (function_exists('mg_pioneer_enabled') && mg_pioneer_enabled()) {
        mg_reward_material($member['mb_id'], 'rp');
    }

    // Morgan: 잇기 누적 보상 체크
    if (function_exists('mg_rp_check_reply_reward')) {
        mg_rp_check_reply_reward($rt_id);
    }

    // Morgan: 업적 트리거 (RP 이음)
    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($member['mb_id'], 'rp_reply_count');
    }
}

echo json_encode($result);
