<?php
/**
 * Morgan Edition - 파견 이벤트 처리
 */

$sub_menu = "801115";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

global $g5;

$w     = isset($_POST['w']) ? $_POST['w'] : '';
$ee_id = isset($_POST['ee_id']) ? (int)$_POST['ee_id'] : 0;

$redirect_url = G5_ADMIN_URL.'/morgan/expedition_event.php';

// 삭제
if ($w === 'd') {
    if (!$ee_id) alert('잘못된 접근입니다.', $redirect_url);

    // 연결된 매칭도 삭제
    sql_query("DELETE FROM {$g5['mg_expedition_event_area_table']} WHERE ee_id = {$ee_id}");
    sql_query("DELETE FROM {$g5['mg_expedition_event_table']} WHERE ee_id = {$ee_id}");

    alert('이벤트가 삭제되었습니다.', $redirect_url);
}

// 입력값 정리
$ee_name        = isset($_POST['ee_name']) ? trim($_POST['ee_name']) : '';
$ee_desc        = isset($_POST['ee_desc']) ? trim($_POST['ee_desc']) : '';
$ee_icon        = isset($_POST['ee_icon']) ? trim($_POST['ee_icon']) : '';
$ee_order       = isset($_POST['ee_order']) ? (int)$_POST['ee_order'] : 0;
$ee_effect_type = isset($_POST['ee_effect_type']) ? $_POST['ee_effect_type'] : 'point_bonus';

// 아이콘 삭제 처리
if (!empty($_POST['del_icon'])) {
    $ee_icon = '';
}

// 유효성 검사
if (!$ee_name) alert('이벤트명을 입력하세요.', $redirect_url);

$valid_types = array('point_bonus', 'point_penalty', 'material_bonus', 'material_penalty', 'reward_loss');
if (!in_array($ee_effect_type, $valid_types)) {
    alert('올바른 효과 유형을 선택하세요.', $redirect_url);
}

// 효과 JSON 구성
$effect = array();
switch ($ee_effect_type) {
    case 'point_bonus':
    case 'point_penalty':
        $effect['amount'] = max(1, (int)($_POST['effect_amount'] ?? 100));
        break;
    case 'material_bonus':
        $effect['mt_id'] = (int)($_POST['effect_mt_id'] ?? 0);
        $effect['count'] = max(1, (int)($_POST['effect_count'] ?? 1));
        break;
    case 'material_penalty':
        $effect['count'] = max(1, (int)($_POST['effect_loss_count'] ?? 1));
        break;
    case 'reward_loss':
        $effect['point_loss'] = max(0, (int)($_POST['effect_point_loss'] ?? 0));
        $effect['material_loss_pct'] = max(0, min(100, (int)($_POST['effect_material_loss_pct'] ?? 50)));
        break;
}
$ee_effect_json = json_encode($effect, JSON_UNESCAPED_UNICODE);

// SQL 이스케이프
$s_name   = sql_real_escape_string($ee_name);
$s_desc   = sql_real_escape_string($ee_desc);
$s_icon   = sql_real_escape_string($ee_icon);
$s_type   = sql_real_escape_string($ee_effect_type);
$s_effect = sql_real_escape_string($ee_effect_json);

if ($w === 'u') {
    // 수정
    if (!$ee_id) alert('잘못된 접근입니다.', $redirect_url);

    sql_query("UPDATE {$g5['mg_expedition_event_table']} SET
        ee_name = '{$s_name}',
        ee_desc = '{$s_desc}',
        ee_icon = '{$s_icon}',
        ee_effect_type = '{$s_type}',
        ee_effect = '{$s_effect}',
        ee_order = {$ee_order}
        WHERE ee_id = {$ee_id}");

    alert('이벤트가 수정되었습니다.', $redirect_url);
} else {
    // 추가
    sql_query("INSERT INTO {$g5['mg_expedition_event_table']}
        (ee_name, ee_desc, ee_icon, ee_effect_type, ee_effect, ee_order)
        VALUES ('{$s_name}', '{$s_desc}', '{$s_icon}', '{$s_type}', '{$s_effect}', {$ee_order})");

    alert('이벤트가 추가되었습니다.', $redirect_url);
}
