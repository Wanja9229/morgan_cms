<?php
/**
 * Morgan Edition - 스태프 관리 처리
 */

$sub_menu = "800060";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

check_admin_token();

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$sr_id = isset($_POST['sr_id']) ? (int)$_POST['sr_id'] : 0;
$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';

// ==========================================
// 역할 추가
// ==========================================
if ($mode == 'role_add') {
    $sr_name = trim($_POST['sr_name'] ?? '');
    if (!$sr_name) {
        alert('역할명을 입력해주세요.');
    }

    $sr_description = trim($_POST['sr_description'] ?? '');
    $sr_color = trim($_POST['sr_color'] ?? '#f59f0a');

    // 권한 체크박스 → JSON 변환 (단일 체크 = 전체 권한)
    $perms_input = isset($_POST['perms']) ? $_POST['perms'] : array();
    $permissions = array();
    foreach ($perms_input as $pkey => $val) {
        if ($val) $permissions[$pkey] = 'r,w,d';
    }

    $perm_json = json_encode($permissions, JSON_UNESCAPED_UNICODE);
    $sr_name = sql_real_escape_string($sr_name);
    $sr_description = sql_real_escape_string($sr_description);
    $sr_color = sql_real_escape_string($sr_color);
    $perm_json = sql_real_escape_string($perm_json);

    sql_query("INSERT INTO {$g5['mg_staff_role_table']}
               SET sr_name = '{$sr_name}',
                   sr_description = '{$sr_description}',
                   sr_color = '{$sr_color}',
                   sr_permissions = '{$perm_json}',
                   sr_created = NOW()");

    goto_url('./staff.php?tab=roles');
}

// ==========================================
// 역할 수정
// ==========================================
if ($mode == 'role_edit') {
    if (!$sr_id) alert('잘못된 요청입니다.');

    $sr_name = trim($_POST['sr_name'] ?? '');
    if (!$sr_name) {
        alert('역할명을 입력해주세요.');
    }

    $sr_description = trim($_POST['sr_description'] ?? '');
    $sr_color = trim($_POST['sr_color'] ?? '#f59f0a');

    $perms_input = isset($_POST['perms']) ? $_POST['perms'] : array();
    $permissions = array();
    foreach ($perms_input as $pkey => $val) {
        if ($val) $permissions[$pkey] = 'r,w,d';
    }

    $perm_json = json_encode($permissions, JSON_UNESCAPED_UNICODE);
    $sr_name = sql_real_escape_string($sr_name);
    $sr_description = sql_real_escape_string($sr_description);
    $sr_color = sql_real_escape_string($sr_color);
    $perm_json = sql_real_escape_string($perm_json);

    sql_query("UPDATE {$g5['mg_staff_role_table']}
               SET sr_name = '{$sr_name}',
                   sr_description = '{$sr_description}',
                   sr_color = '{$sr_color}',
                   sr_permissions = '{$perm_json}',
                   sr_updated = NOW()
               WHERE sr_id = {$sr_id}");

    // 소속 멤버 전원 g5_auth 동기화
    mg_staff_sync_role($sr_id);

    goto_url('./staff.php?tab=roles');
}

// ==========================================
// 역할 삭제
// ==========================================
if ($mode == 'role_delete') {
    if (!$sr_id) alert('잘못된 요청입니다.');

    // 소속 멤버가 있으면 먼저 해제
    $result = sql_query("SELECT mb_id FROM {$g5['mg_staff_member_table']} WHERE sr_id = {$sr_id}");
    $affected_members = array();
    while ($row = sql_fetch_array($result)) {
        $affected_members[] = $row['mb_id'];
    }

    sql_query("DELETE FROM {$g5['mg_staff_member_table']} WHERE sr_id = {$sr_id}");
    sql_query("DELETE FROM {$g5['mg_staff_role_table']} WHERE sr_id = {$sr_id}");

    // 영향받는 멤버들 g5_auth 동기화
    foreach ($affected_members as $affected_mb_id) {
        mg_staff_sync_auth($affected_mb_id);
    }

    goto_url('./staff.php?tab=roles');
}

// ==========================================
// 스태프 추가 (역할 배정)
// ==========================================
if ($mode == 'member_add') {
    if (!$mb_id) alert('회원을 선택해주세요.');
    if (!$sr_id) alert('역할을 선택해주세요.');

    // 회원 존재 확인
    $member_check = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'");
    if (!$member_check['mb_id']) {
        alert('존재하지 않는 회원입니다.');
    }

    // 역할 존재 확인
    $role_check = mg_get_staff_role($sr_id);
    if (!$role_check['sr_id']) {
        alert('존재하지 않는 역할입니다.');
    }

    mg_staff_assign($mb_id, $sr_id);

    goto_url('./staff.php?tab=members');
}

// ==========================================
// 역할 해제 (특정 역할)
// ==========================================
if ($mode == 'member_remove') {
    if (!$mb_id) alert('잘못된 요청입니다.');
    if (!$sr_id) alert('잘못된 요청입니다.');

    mg_staff_unassign($mb_id, $sr_id);

    goto_url('./staff.php?tab=members');
}

// ==========================================
// 전체 역할 해제
// ==========================================
if ($mode == 'member_remove_all') {
    if (!$mb_id) alert('잘못된 요청입니다.');

    mg_staff_remove_member($mb_id);

    goto_url('./staff.php?tab=members');
}

alert('잘못된 요청입니다.', './staff.php');
