<?php
/**
 * Morgan Edition - 캐릭터 수정 처리 (관리자)
 */

$sub_menu = "800200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
$redirect_url = G5_ADMIN_URL.'/morgan/character_list.php';

if (!$ch_id) {
    alert('잘못된 접근입니다.', $redirect_url);
}

// 캐릭터 정보 조회
$char = sql_fetch("SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.', $redirect_url);
}

// 입력값
$ch_name = isset($_POST['ch_name']) ? trim(clean_xss_tags($_POST['ch_name'])) : '';
$side_id = isset($_POST['side_id']) ? (int)$_POST['side_id'] : 0;
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$ch_state = isset($_POST['ch_state']) ? $_POST['ch_state'] : $char['ch_state'];
$ch_main = isset($_POST['ch_main']) ? (int)$_POST['ch_main'] : 0;
$profile = isset($_POST['profile']) ? $_POST['profile'] : array();

$btn_save = isset($_POST['btn_save']);
$btn_approve = isset($_POST['btn_approve']);

// 승인 버튼 클릭 시
if ($btn_approve) {
    $ch_state = 'approved';
}

// 상태 유효성
$valid_states = array('editing', 'pending', 'approved');
if (!in_array($ch_state, $valid_states)) {
    $ch_state = $char['ch_state'];
}

// 기본 검증
if (empty($ch_name)) {
    alert('캐릭터명을 입력해주세요.');
}

// 두상 이미지 처리
$ch_thumb = $char['ch_thumb'] ?? '';

// 이미지 삭제 요청
if (isset($_POST['del_thumb']) && $ch_thumb) {
    $thumb_path = MG_CHAR_IMAGE_PATH.'/'.$ch_thumb;
    if (file_exists($thumb_path)) {
        @unlink($thumb_path);
    }
    // 썸네일도 삭제
    $dir = dirname($ch_thumb);
    $base = basename($ch_thumb);
    $th_path = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
    if (file_exists($th_path)) {
        @unlink($th_path);
    }
    $ch_thumb = '';
}

// 새 두상 이미지 업로드
if (isset($_FILES['ch_thumb']) && $_FILES['ch_thumb']['error'] == UPLOAD_ERR_OK) {
    // 기존 이미지 삭제
    if ($ch_thumb) {
        $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_thumb;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
        $dir = dirname($ch_thumb);
        $base = basename($ch_thumb);
        $old_th = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
        if (file_exists($old_th)) {
            @unlink($old_th);
        }
    }

    $upload = mg_upload_character_image($_FILES['ch_thumb'], $char['mb_id'], 'thumb');
    if ($upload['success']) {
        $ch_thumb = $upload['filename'];
    }
}

// 전신 이미지 처리
$ch_image = $char['ch_image'] ?? '';

// 전신 이미지 삭제 요청
if (isset($_POST['del_image']) && $ch_image) {
    $img_path = MG_CHAR_IMAGE_PATH.'/'.$ch_image;
    if (file_exists($img_path)) {
        @unlink($img_path);
    }
    $ch_image = '';
}

// 새 전신 이미지 업로드
if (isset($_FILES['ch_image']) && $_FILES['ch_image']['error'] == UPLOAD_ERR_OK) {
    if ($ch_image) {
        $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_image;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }

    $upload = mg_upload_character_image($_FILES['ch_image'], $char['mb_id'], 'image');
    if ($upload['success']) {
        $ch_image = $upload['filename'];
    }
}

// 대표 캐릭터 설정 시 다른 캐릭터 해제
if ($ch_main) {
    sql_query("UPDATE {$g5['mg_character_table']} SET ch_main = 0 WHERE mb_id = '{$char['mb_id']}' AND ch_id != {$ch_id}");
}

// 캐릭터 정보 업데이트
$sql = "UPDATE {$g5['mg_character_table']} SET
        ch_name = '".sql_real_escape_string($ch_name)."',
        side_id = ".($side_id ? $side_id : 'NULL').",
        class_id = ".($class_id ? $class_id : 'NULL').",
        ch_state = '{$ch_state}',
        ch_main = {$ch_main},
        ch_thumb = '".sql_real_escape_string($ch_thumb)."',
        ch_image = '".sql_real_escape_string($ch_image)."',
        ch_update = NOW()
        WHERE ch_id = {$ch_id}";
sql_query($sql);

// 상태가 approved로 변경됐을 때 회원 레벨 업그레이드
if ($ch_state == 'approved' && $char['ch_state'] != 'approved') {
    sql_query("UPDATE {$g5['member_table']} SET mb_level = 3 WHERE mb_id = '{$char['mb_id']}' AND mb_level < 3");

    // 로그 기록
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, admin_id)
               VALUES ({$ch_id}, 'approve', '{$member['mb_id']}')");

    // 알림 발송
    if (function_exists('mg_notify')) {
        mg_notify($char['mb_id'], 'character_approved', '캐릭터가 승인되었습니다', '등록하신 캐릭터가 승인되어 활동이 가능합니다.', G5_BBS_URL.'/character.php');
    }
}

// 프로필 값 저장
foreach ($profile as $pf_id => $value) {
    $pf_id = (int)$pf_id;
    if (!$pf_id) continue;

    // 다중선택 처리
    if (is_array($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
    } else {
        $value = trim($value);
    }

    // 기존 값 확인
    $exists = sql_fetch("SELECT pv_id FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");

    if ($exists['pv_id']) {
        sql_query("UPDATE {$g5['mg_profile_value_table']} SET pv_value = '".sql_real_escape_string($value)."' WHERE pv_id = {$exists['pv_id']}");
    } else {
        sql_query("INSERT INTO {$g5['mg_profile_value_table']} (ch_id, pf_id, pv_value) VALUES ({$ch_id}, {$pf_id}, '".sql_real_escape_string($value)."')");
    }
}

// 로그 기록
sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, admin_id, log_memo)
           VALUES ({$ch_id}, 'edit', '{$member['mb_id']}', '관리자 수정')");

$msg = $btn_approve ? '캐릭터가 승인되었습니다.' : '캐릭터가 수정되었습니다.';
alert($msg, G5_ADMIN_URL.'/morgan/character_form.php?ch_id='.$ch_id);
