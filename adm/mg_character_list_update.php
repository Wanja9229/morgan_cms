<?php
/**
 * Morgan Edition - 캐릭터 일괄 처리
 */

$sub_menu = '400100';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'w');
check_admin_token();

$chk = isset($_POST['chk']) ? $_POST['chk'] : array();
$action = isset($_POST['action']) ? $_POST['action'] : '';
$state = isset($_POST['state']) ? $_POST['state'] : '';

if (empty($chk) || !is_array($chk)) {
    alert('선택된 캐릭터가 없습니다.');
}

if (!$action) {
    alert('작업을 선택해주세요.');
}

$redirect_url = './mg_character_list.php'.($state ? '?state='.$state : '');

$success_count = 0;

foreach ($chk as $ch_id) {
    $ch_id = (int)$ch_id;
    if (!$ch_id) continue;

    // 캐릭터 정보 확인
    $char = sql_fetch("SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}");
    if (!$char['ch_id']) continue;

    switch ($action) {
        case 'approve':
            if ($char['ch_state'] != 'pending') continue 2;

            sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'approved', ch_update = NOW() WHERE ch_id = {$ch_id}");

            // 회원 레벨 업그레이드 (3 미만이면 3으로)
            $char_member = sql_fetch("SELECT mb_level FROM {$g5['member_table']} WHERE mb_id = '{$char['mb_id']}'");
            if ($char_member['mb_level'] < 3) {
                sql_query("UPDATE {$g5['member_table']} SET mb_level = 3 WHERE mb_id = '{$char['mb_id']}'");
            }

            sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'approve', '일괄 승인', '{$member['mb_id']}')");
            mg_notify($char['mb_id'], 'character_approved', '캐릭터 승인', "'{$char['ch_name']}' 캐릭터가 승인되었습니다.", G5_BBS_URL.'/character_view.php?ch_id='.$ch_id);
            $success_count++;
            break;

        case 'reject':
            if ($char['ch_state'] != 'pending') continue 2;

            sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'editing', ch_update = NOW() WHERE ch_id = {$ch_id}");
            sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'reject', '일괄 반려', '{$member['mb_id']}')");
            mg_notify($char['mb_id'], 'character_rejected', '캐릭터 반려', "'{$char['ch_name']}' 캐릭터가 반려되었습니다.", G5_BBS_URL.'/character_form.php?ch_id='.$ch_id);
            $success_count++;
            break;

        case 'delete':
            sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'deleted', ch_update = NOW() WHERE ch_id = {$ch_id}");
            sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo, admin_id) VALUES ({$ch_id}, 'edit', '일괄 삭제', '{$member['mb_id']}')");
            mg_notify($char['mb_id'], 'character_deleted', '캐릭터 삭제', "'{$char['ch_name']}' 캐릭터가 관리자에 의해 삭제되었습니다.", '');
            $success_count++;
            break;
    }
}

$action_names = array(
    'approve' => '승인',
    'reject' => '반려',
    'delete' => '삭제',
);
$action_name = $action_names[$action] ?? $action;

alert("{$success_count}개의 캐릭터가 {$action_name}되었습니다.", $redirect_url);
