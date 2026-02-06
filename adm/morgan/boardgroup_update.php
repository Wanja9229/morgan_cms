<?php
/**
 * Morgan Edition - 게시판 그룹 처리
 */

$sub_menu = "800180";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

$w = isset($_POST['w']) ? $_POST['w'] : '';
$gr_id = isset($_POST['gr_id']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['gr_id'])) : '';
$old_gr_id = isset($_POST['old_gr_id']) ? $_POST['old_gr_id'] : '';

$redirect_url = G5_ADMIN_URL.'/morgan/boardgroup_list.php';

// 삭제
if ($w === 'd') {
    if (!$gr_id) {
        alert('그룹을 선택해주세요.');
    }

    // 게시판이 있는지 체크
    $board_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['board_table']} WHERE gr_id = '".sql_real_escape_string($gr_id)."'");
    if ($board_count['cnt'] > 0) {
        alert('이 그룹에 속한 게시판이 있어 삭제할 수 없습니다.');
    }

    sql_query("DELETE FROM {$g5['group_table']} WHERE gr_id = '".sql_real_escape_string($gr_id)."'");

    alert('그룹이 삭제되었습니다.', $redirect_url);
}

// 수정
if ($w === 'u') {
    if (!$old_gr_id) {
        alert('그룹을 선택해주세요.');
    }

    $gr_subject = isset($_POST['gr_subject']) ? clean_xss_tags($_POST['gr_subject']) : '';
    $gr_admin = isset($_POST['gr_admin']) ? clean_xss_tags($_POST['gr_admin']) : '';
    $gr_order = isset($_POST['gr_order']) ? (int)$_POST['gr_order'] : 0;

    if (!$gr_subject) {
        alert('그룹명을 입력해주세요.');
    }

    sql_query("UPDATE {$g5['group_table']} SET
        gr_subject = '".sql_real_escape_string($gr_subject)."',
        gr_admin = '".sql_real_escape_string($gr_admin)."',
        gr_order = {$gr_order}
        WHERE gr_id = '".sql_real_escape_string($old_gr_id)."'");

    alert('그룹이 수정되었습니다.', $redirect_url);
}

// 추가
if (!$gr_id) {
    alert('그룹 ID를 입력해주세요.');
}

if (strlen($gr_id) > 20) {
    alert('그룹 ID는 20자 이내로 입력해주세요.');
}

$gr_subject = isset($_POST['gr_subject']) ? clean_xss_tags($_POST['gr_subject']) : '';
$gr_admin = isset($_POST['gr_admin']) ? clean_xss_tags($_POST['gr_admin']) : '';
$gr_order = isset($_POST['gr_order']) ? (int)$_POST['gr_order'] : 0;

if (!$gr_subject) {
    alert('그룹명을 입력해주세요.');
}

// 중복 체크
$exists = sql_fetch("SELECT gr_id FROM {$g5['group_table']} WHERE gr_id = '".sql_real_escape_string($gr_id)."'");
if ($exists['gr_id']) {
    alert('이미 존재하는 그룹 ID입니다.');
}

sql_query("INSERT INTO {$g5['group_table']} (gr_id, gr_subject, gr_admin, gr_order)
    VALUES ('".sql_real_escape_string($gr_id)."', '".sql_real_escape_string($gr_subject)."', '".sql_real_escape_string($gr_admin)."', {$gr_order})");

alert('그룹이 추가되었습니다.', $redirect_url);
