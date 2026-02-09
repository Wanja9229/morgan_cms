<?php
/**
 * Morgan Edition - 캐릭터 폼 처리
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 입력값
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
$ch_name = isset($_POST['ch_name']) ? clean_xss_tags(trim($_POST['ch_name'])) : '';
$side_id = isset($_POST['side_id']) ? (int)$_POST['side_id'] : 0;
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$ch_main = isset($_POST['ch_main']) ? 1 : 0;
$profile = isset($_POST['profile']) ? $_POST['profile'] : array();

$btn_save = isset($_POST['btn_save']);
$btn_submit = isset($_POST['btn_submit']);
$btn_delete = isset($_POST['btn_delete']);

$is_edit = $ch_id > 0;

// 기본 검증
if (empty($ch_name)) {
    alert('캐릭터명을 입력해주세요.');
}

if (mb_strlen($ch_name) > 100) {
    alert('캐릭터명은 100자를 초과할 수 없습니다.');
}

// 수정 모드: 권한 체크
if ($is_edit) {
    $char = sql_fetch("SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$member['mb_id']}'");
    if (!$char['ch_id']) {
        alert('존재하지 않거나 권한이 없는 캐릭터입니다.');
    }
}

// 삭제 처리
if ($btn_delete && $is_edit) {
    // 두상 이미지 삭제
    if ($char['ch_thumb']) {
        $thumb_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_thumb'];
        if (file_exists($thumb_path)) {
            @unlink($thumb_path);
        }
        // 썸네일도 삭제
        $dir = dirname($char['ch_thumb']);
        $base = basename($char['ch_thumb']);
        $th_path = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
        if (file_exists($th_path)) {
            @unlink($th_path);
        }
    }

    // 전신 이미지 삭제
    if ($char['ch_image'] ?? '') {
        $img_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_image'];
        if (file_exists($img_path)) {
            @unlink($img_path);
        }
    }

    // 소프트 삭제
    sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'deleted', ch_update = NOW() WHERE ch_id = {$ch_id}");

    // 프로필 값 삭제
    sql_query("DELETE FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}");

    // 로그 기록
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo) VALUES ({$ch_id}, 'edit', '캐릭터 삭제')");

    goto_url(G5_BBS_URL.'/character.php');
}

// 두상 이미지 처리
$ch_thumb = $is_edit ? ($char['ch_thumb'] ?? '') : '';

// 두상 이미지 삭제 요청
if (isset($_POST['del_thumb']) && $is_edit && $ch_thumb) {
    $thumb_path = MG_CHAR_IMAGE_PATH.'/'.$ch_thumb;
    if (file_exists($thumb_path)) {
        @unlink($thumb_path);
    }
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

    $upload = mg_upload_character_image($_FILES['ch_thumb'], $member['mb_id'], 'thumb');
    if ($upload['success']) {
        $ch_thumb = $upload['filename'];
    }
}

// 전신 이미지 처리
$ch_image = $is_edit ? ($char['ch_image'] ?? '') : '';

// 전신 이미지 삭제 요청
if (isset($_POST['del_image']) && $is_edit && $ch_image) {
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

    $upload = mg_upload_character_image($_FILES['ch_image'], $member['mb_id'], 'image');
    if ($upload['success']) {
        $ch_image = $upload['filename'];
    }
}

// 대표 캐릭터 처리 (하나만 허용)
if ($ch_main) {
    sql_query("UPDATE {$g5['mg_character_table']} SET ch_main = 0 WHERE mb_id = '{$member['mb_id']}'");
}

// 상태 결정
$ch_state = 'editing';
if ($btn_submit) {
    $ch_state = 'pending';
}

if ($is_edit) {
    // 수정
    $sql = "UPDATE {$g5['mg_character_table']} SET
            ch_name = '".sql_real_escape_string($ch_name)."',
            side_id = ".($side_id ?: 'NULL').",
            class_id = ".($class_id ?: 'NULL').",
            ch_main = {$ch_main},
            ch_thumb = '".sql_real_escape_string($ch_thumb)."',
            ch_image = '".sql_real_escape_string($ch_image)."',
            ch_state = '{$ch_state}',
            ch_update = NOW()
            WHERE ch_id = {$ch_id}";
    sql_query($sql);

    // 로그
    $log_action = $btn_submit ? 'submit' : 'edit';
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action) VALUES ({$ch_id}, '{$log_action}')");
} else {
    // 신규 생성
    $sql = "INSERT INTO {$g5['mg_character_table']} SET
            mb_id = '{$member['mb_id']}',
            ch_name = '".sql_real_escape_string($ch_name)."',
            side_id = ".($side_id ?: 'NULL').",
            class_id = ".($class_id ?: 'NULL').",
            ch_main = {$ch_main},
            ch_thumb = '".sql_real_escape_string($ch_thumb)."',
            ch_image = '".sql_real_escape_string($ch_image)."',
            ch_state = '{$ch_state}',
            ch_datetime = NOW()";
    sql_query($sql);
    $ch_id = sql_insert_id();

    // 로그
    $log_action = $btn_submit ? 'submit' : 'edit';
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action) VALUES ({$ch_id}, '{$log_action}')");
}

// 프로필 값 저장
foreach ($profile as $pf_id => $pv_value) {
    $pf_id = (int)$pf_id;

    // 다중선택 처리
    if (is_array($pv_value)) {
        $pv_value = json_encode($pv_value, JSON_UNESCAPED_UNICODE);
    } else {
        $pv_value = trim($pv_value);
    }

    // 필드 존재 여부 확인
    $field = sql_fetch("SELECT pf_id FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id} AND pf_use = 1");
    if (!$field['pf_id']) continue;

    // UPSERT
    $exists = sql_fetch("SELECT pv_id FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");

    if (isset($exists['pv_id']) && $exists['pv_id']) {
        sql_query("UPDATE {$g5['mg_profile_value_table']} SET pv_value = '".sql_real_escape_string($pv_value)."' WHERE pv_id = {$exists['pv_id']}");
    } else {
        sql_query("INSERT INTO {$g5['mg_profile_value_table']} (ch_id, pf_id, pv_value) VALUES ({$ch_id}, {$pf_id}, '".sql_real_escape_string($pv_value)."')");
    }
}

// 완료 메시지
$msg = $is_edit ? '캐릭터가 수정되었습니다.' : '캐릭터가 생성되었습니다.';
if ($btn_submit) {
    $msg .= ' 승인 심사 후 활동이 가능합니다.';
}

alert($msg, G5_BBS_URL.'/character.php');
