<?php
/**
 * Morgan Edition - 역극 생성 처리
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

$can_create = mg_can_create_rp($member['mb_id']);
if (!$can_create['can_create']) { alert_close($can_create['message']); }

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
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($_FILES['rt_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $upload_dir = G5_DATA_PATH . '/rp/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        $filename = 'rp_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['rt_image']['tmp_name'], $upload_dir . $filename)) {
            $rt_image = G5_DATA_URL . '/rp/' . $filename;
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
    goto_url(G5_BBS_URL . '/rp_view.php?rt_id=' . $result['rt_id']);
} else {
    alert($result['message']);
}
