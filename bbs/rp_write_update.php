<?php
/**
 * Morgan Edition - 역극 생성 처리
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (mg_config('rp_use', '1') != '1') { alert_close('역극 기능이 비활성화되어 있습니다.'); }

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

// 회원 레벨 체크
$_lv = mg_check_member_level('rp', $member['mb_level']);
if (!$_lv['allowed']) { alert_close("역극은 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다. (현재 레벨: {$_lv['current']})"); }

// Morgan: 개척 시스템 해금 체크
if (function_exists('mg_is_board_unlocked') && !mg_is_board_unlocked('roleplay')) {
    alert('역극은 아직 개척되지 않았습니다.');
}

$can_create = mg_can_create_rp($member['mb_id']);
if (!$can_create['can_create']) { alert_close($can_create['message']); }

// Morgan: 판 세우기 비용 차감
if (function_exists('mg_rp_deduct_create_cost')) {
    $cost_result = mg_rp_deduct_create_cost($member['mb_id']);
    if (!$cost_result['success']) {
        alert($cost_result['message']);
    }
}

// Validate
$rt_title = isset($_POST['rt_title']) ? trim(clean_xss_tags($_POST['rt_title'])) : '';
$rt_content = isset($_POST['rt_content']) ? trim($_POST['rt_content']) : '';
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
$rt_max_member = isset($_POST['rt_max_member']) ? (int)$_POST['rt_max_member'] : 0;

if (!$rt_title) { alert('제목을 입력해주세요.'); }
if (!$rt_content) { alert('내용을 입력해주세요.'); }
if (!$ch_id) { alert('캐릭터를 선택해주세요.'); }

// Verify character belongs to user
$char = mg_get_character($ch_id);
if (!$char || $char['mb_id'] != $member['mb_id'] || $char['ch_state'] != 'approved') {
    alert('유효하지 않은 캐릭터입니다.');
}

// Image upload
$rt_image = '';
if (isset($_FILES['rt_image']) && $_FILES['rt_image']['error'] == 0) {
    if ($_FILES['rt_image']['size'] > mg_upload_max_file()) {
        alert('파일 크기가 너무 큽니다.');
    }
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($_FILES['rt_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $upload_dir = MG_RP_DATA_PATH . '/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        $filename = 'rp_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['rt_image']['tmp_name'], $upload_dir . $filename)) {
            $rt_image = MG_RP_DATA_URL . '/' . $filename;
        }
    }
}

$result = mg_create_rp_thread(array(
    'rt_title' => $rt_title,
    'rt_content' => $rt_content,
    'rt_image' => $rt_image,
    'mb_id' => $member['mb_id'],
    'ch_id' => $ch_id,
    'rt_max_member' => $rt_max_member,
));

if ($result['success']) {
    goto_url(G5_BBS_URL . '/rp_list.php#rp-thread-' . $result['rt_id']);
} else {
    alert($result['message']);
}
