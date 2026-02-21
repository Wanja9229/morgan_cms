<?php
/**
 * Morgan Edition - 캐릭터 일괄 처리
 */

$sub_menu = "800200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$state = isset($_POST['state']) ? clean_xss_tags($_POST['state']) : '';
$redirect_url = G5_ADMIN_URL.'/morgan/character_list.php?page='.$page.($state ? '&state='.$state : '');

// 개별 승인
if (isset($_POST['btn_approve'])) {
    $ch_id = (int)$_POST['btn_approve'];

    if ($ch_id) {
        // 캐릭터 승인
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'approved' WHERE ch_id = {$ch_id}");

        // 로그 기록
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, admin_id)
                   VALUES ({$ch_id}, 'approve', '{$member['mb_id']}')");

        // 회원 레벨 업그레이드 (레벨 3으로)
        $char = sql_fetch("SELECT mb_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
        if ($char['mb_id']) {
            sql_query("UPDATE {$g5['member_table']} SET mb_level = 3 WHERE mb_id = '{$char['mb_id']}' AND mb_level < 3");
        }

        // 알림 발송
        if (function_exists('mg_notify')) {
            mg_notify($char['mb_id'], 'character_approved', '캐릭터가 승인되었습니다', '등록하신 캐릭터가 승인되어 활동이 가능합니다.', G5_BBS_URL.'/character.php');
        }
    }

    alert('캐릭터가 승인되었습니다.', $redirect_url);
}

// 선택 승인
if (isset($_POST['btn_approve_selected'])) {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (empty($chk)) {
        alert('승인할 캐릭터를 선택해주세요.');
    }

    $approved_count = 0;
    foreach ($chk as $ch_id) {
        $ch_id = (int)$ch_id;
        if (!$ch_id) continue;

        // 대기 상태인지 확인
        $char = sql_fetch("SELECT mb_id, ch_state FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
        if ($char['ch_state'] != 'pending') continue;

        // 캐릭터 승인
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'approved' WHERE ch_id = {$ch_id}");

        // 로그 기록
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, admin_id)
                   VALUES ({$ch_id}, 'approve', '{$member['mb_id']}')");

        // 회원 레벨 업그레이드
        if ($char['mb_id']) {
            sql_query("UPDATE {$g5['member_table']} SET mb_level = 3 WHERE mb_id = '{$char['mb_id']}' AND mb_level < 3");
        }

        // 알림 발송
        if (function_exists('mg_notify')) {
            mg_notify($char['mb_id'], 'character_approved', '캐릭터가 승인되었습니다', '등록하신 캐릭터가 승인되어 활동이 가능합니다.', G5_BBS_URL.'/character.php');
        }

        $approved_count++;
    }

    alert($approved_count.'개 캐릭터가 승인되었습니다.', $redirect_url);
}

// 선택 삭제
if (isset($_POST['btn_delete'])) {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (empty($chk)) {
        alert('삭제할 캐릭터를 선택해주세요.');
    }

    foreach ($chk as $ch_id) {
        $ch_id = (int)$ch_id;
        if (!$ch_id) continue;

        // 캐릭터 이미지 삭제
        $char = sql_fetch("SELECT ch_thumb, ch_image FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
        if ($char['ch_thumb'] && defined('MG_CHAR_IMAGE_PATH')) {
            $image_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_thumb'];
            if (file_exists($image_path)) @unlink($image_path);
            // 썸네일(th_) 파일도 삭제
            $dir = dirname($char['ch_thumb']);
            $base = basename($char['ch_thumb']);
            $th_path = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
            if (file_exists($th_path)) @unlink($th_path);
        }
        if (!empty($char['ch_image']) && defined('MG_CHAR_IMAGE_PATH')) {
            $image_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_image'];
            if (file_exists($image_path)) @unlink($image_path);
        }

        // 프로필 값 삭제
        sql_query("DELETE FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}");

        // 글-캐릭터 연결 삭제
        sql_query("DELETE FROM {$g5['mg_write_character_table']} WHERE ch_id = {$ch_id}");

        // 로그 삭제
        sql_query("DELETE FROM {$g5['mg_character_log_table']} WHERE ch_id = {$ch_id}");

        // 캐릭터 삭제 (또는 상태 변경)
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'deleted' WHERE ch_id = {$ch_id}");
    }

    alert(count($chk).'개 캐릭터가 삭제되었습니다.', $redirect_url);
}

alert('잘못된 접근입니다.', $redirect_url);
