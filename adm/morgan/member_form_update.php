<?php
/**
 * Morgan Edition - 회원 수정 처리
 */

$sub_menu = "800190";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mb_id = isset($_POST['mb_id']) ? clean_xss_tags($_POST['mb_id']) : '';
if (!$mb_id) { alert('잘못된 접근입니다.'); }

$mb = sql_fetch("SELECT * FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
if (!$mb['mb_id']) { alert('존재하지 않는 회원입니다.'); }

// 최고관리자는 자기 자신만 수정 가능 (다른 최고관리자 수정 방지)
if ($mb['mb_id'] == $config['cf_admin'] && $member['mb_id'] != $config['cf_admin']) {
    alert('최고관리자 정보는 수정할 수 없습니다.');
}

$mb_nick = isset($_POST['mb_nick']) ? trim(clean_xss_tags($_POST['mb_nick'])) : '';
$mb_name = isset($_POST['mb_name']) ? trim(clean_xss_tags($_POST['mb_name'])) : '';
$mb_email = isset($_POST['mb_email']) ? trim(clean_xss_tags($_POST['mb_email'])) : '';
$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_point = isset($_POST['mb_point']) ? (int)$_POST['mb_point'] : 0;
$mb_level = isset($_POST['mb_level']) ? max(1, min(10, (int)$_POST['mb_level'])) : 1;
$mb_memo = isset($_POST['mb_memo']) ? trim($_POST['mb_memo']) : '';
$mb_intercept_date = isset($_POST['mb_intercept_date']) ? trim(clean_xss_tags($_POST['mb_intercept_date'])) : '';

if (!$mb_nick) { alert('닉네임을 입력해주세요.'); }
if (!$mb_email) { alert('이메일을 입력해주세요.'); }

// 닉네임 중복 체크 (변경된 경우)
if ($mb_nick != $mb['mb_nick']) {
    $dup = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_nick = '".sql_real_escape_string($mb_nick)."' AND mb_id != '".sql_real_escape_string($mb_id)."'");
    if ($dup['mb_id']) { alert('이미 사용 중인 닉네임입니다.'); }
}

// 업데이트 쿼리
$sql_set = "mb_nick = '".sql_real_escape_string($mb_nick)."',
            mb_name = '".sql_real_escape_string($mb_name)."',
            mb_email = '".sql_real_escape_string($mb_email)."',
            mb_point = {$mb_point},
            mb_level = {$mb_level},
            mb_memo = '".sql_real_escape_string($mb_memo)."',
            mb_intercept_date = '".sql_real_escape_string($mb_intercept_date)."'";

// 비밀번호 변경
if ($mb_password) {
    $mb_password_hash = password_hash($mb_password, PASSWORD_DEFAULT);
    if (!$mb_password_hash) {
        $mb_password_hash = md5($mb_password);
    }
    $sql_set .= ", mb_password = '".sql_real_escape_string($mb_password_hash)."'";
}

sql_query("UPDATE {$g5['member_table']} SET {$sql_set} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");

goto_url(G5_ADMIN_URL . '/morgan/member_form.php?mb_id=' . urlencode($mb_id) . '&msg=' . urlencode('회원 정보가 수정되었습니다.'));
