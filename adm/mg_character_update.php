<?php
/**
 * Morgan Edition - 캐릭터 승인/반려/삭제 처리
 */

$sub_menu = '400100';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'w');
check_admin_token();

$ch_id = isset($_REQUEST['ch_id']) ? (int)$_REQUEST['ch_id'] : 0;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$log_memo = isset($_POST['log_memo']) ? trim($_POST['log_memo']) : '';

if (!$ch_id || !$action) {
    alert('잘못된 접근입니다.');
}

// 캐릭터 정보 확인
$char = sql_fetch("SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.');
}

$redirect_url = './mg_character_form.php?ch_id='.$ch_id;

switch ($action) {
    case 'approve':
        // 승인
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'approved', ch_update = NOW() WHERE ch_id = {$ch_id}");

        // 회원 레벨 업그레이드 (3 미만이면 3으로)
        $char_member = sql_fetch("SELECT mb_level FROM {$g5['member_table']} WHERE mb_id = '{$char['mb_id']}'");
        if ($char_member['mb_level'] < 3) {
            sql_query("UPDATE {$g5['member_table']} SET mb_level = 3 WHERE mb_id = '{$char['mb_id']}'");
        }

        // 로그
        $log_memo_escaped = sql_real_escape_string($log_memo);
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'approve', '{$log_memo_escaped}', '{$member['mb_id']}')");

        // 알림
        mg_notify($char['mb_id'], 'character_approved', '캐릭터 승인', "'{$char['ch_name']}' 캐릭터가 승인되었습니다.", G5_BBS_URL.'/character_view.php?ch_id='.$ch_id);

        alert('캐릭터가 승인되었습니다.', $redirect_url);
        break;

    case 'reject':
        // 반려 (editing 상태로 되돌림)
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'editing', ch_update = NOW() WHERE ch_id = {$ch_id}");

        // 로그
        $log_memo_escaped = sql_real_escape_string($log_memo);
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'reject', '{$log_memo_escaped}', '{$member['mb_id']}')");

        // 알림
        $noti_content = "'{$char['ch_name']}' 캐릭터가 반려되었습니다.";
        if ($log_memo) {
            $noti_content .= "\n사유: ".$log_memo;
        }
        mg_notify($char['mb_id'], 'character_rejected', '캐릭터 반려', $noti_content, G5_BBS_URL.'/character_form.php?ch_id='.$ch_id);

        alert('캐릭터가 반려되었습니다.', $redirect_url);
        break;

    case 'unapprove':
        // 승인 취소 (editing 상태로)
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'editing', ch_update = NOW() WHERE ch_id = {$ch_id}");

        // 로그
        $log_memo_escaped = sql_real_escape_string($log_memo);
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'reject', '승인 취소: {$log_memo_escaped}', '{$member['mb_id']}')");

        // 알림
        mg_notify($char['mb_id'], 'character_unapproved', '캐릭터 승인 취소', "'{$char['ch_name']}' 캐릭터의 승인이 취소되었습니다.", G5_BBS_URL.'/character_form.php?ch_id='.$ch_id);

        alert('캐릭터 승인이 취소되었습니다.', $redirect_url);
        break;

    case 'delete':
        // 삭제 (soft delete)
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'deleted', ch_update = NOW() WHERE ch_id = {$ch_id}");

        // 로그
        $log_memo_escaped = sql_real_escape_string($log_memo);
        sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'edit', '관리자 삭제: {$log_memo_escaped}', '{$member['mb_id']}')");

        // 알림
        mg_notify($char['mb_id'], 'character_deleted', '캐릭터 삭제', "'{$char['ch_name']}' 캐릭터가 관리자에 의해 삭제되었습니다.", '');

        alert('캐릭터가 삭제되었습니다.', './mg_character_list.php');
        break;

    default:
        alert('잘못된 액션입니다.');
}
