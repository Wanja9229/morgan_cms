<?php
/**
 * Morgan Edition - 훈련 과정 처리
 */

$sub_menu = "801930";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

global $g5;

$w = isset($_POST['w']) ? $_POST['w'] : '';
$tc_id = isset($_POST['tc_id']) ? (int)$_POST['tc_id'] : 0;

$redirect_url = G5_ADMIN_URL.'/morgan/training_class.php';

// 삭제
if ($w === 'd' && $tc_id > 0) {
    sql_query("DELETE FROM {$g5['mg_training_class_table']} WHERE tc_id = {$tc_id}");
    alert('삭제되었습니다.', $redirect_url);
}

// 파라미터
$tc_name       = isset($_POST['tc_name']) ? clean_xss_tags($_POST['tc_name']) : '';
$tc_desc       = isset($_POST['tc_desc']) ? clean_xss_tags($_POST['tc_desc'], 0, 0, 0, 0) : '';
$tc_icon       = isset($_POST['tc_icon']) ? clean_xss_tags($_POST['tc_icon']) : '';
$tc_icon_color = isset($_POST['tc_icon_color']) ? clean_xss_tags($_POST['tc_icon_color']) : '#ffffff';
$tc_stat       = isset($_POST['tc_stat']) ? clean_xss_tags($_POST['tc_stat']) : 'none';
$tc_stat_amount = isset($_POST['tc_stat_amount']) ? (int)$_POST['tc_stat_amount'] : 0;
$tc_required   = isset($_POST['tc_required']) ? max(1, (int)$_POST['tc_required']) : 1;
$tc_cost       = isset($_POST['tc_cost']) ? max(0, (int)$_POST['tc_cost']) : 0;
$tc_stress     = isset($_POST['tc_stress']) ? (int)$_POST['tc_stress'] : 0;
$tc_max_repeat = isset($_POST['tc_max_repeat']) ? max(0, (int)$_POST['tc_max_repeat']) : 0;
$tc_order      = isset($_POST['tc_order']) ? (int)$_POST['tc_order'] : 0;
$tc_use        = isset($_POST['tc_use']) ? (int)$_POST['tc_use'] : 1;

// 유효성 검사
if (empty($tc_name)) {
    alert('이름을 입력해주세요.');
}

// 스탯 유효성
$valid_stats = array('none', 'stat_hp', 'stat_str', 'stat_dex', 'stat_int', 'stat_con', 'stat_luk');
if (!in_array($tc_stat, $valid_stats)) {
    $tc_stat = 'none';
}

// tc_use는 0 또는 1
$tc_use = $tc_use ? 1 : 0;

if ($w === 'u' && $tc_id > 0) {
    // 수정
    sql_query("UPDATE {$g5['mg_training_class_table']} SET
               tc_name = '{$tc_name}',
               tc_desc = '{$tc_desc}',
               tc_icon = '{$tc_icon}',
               tc_icon_color = '{$tc_icon_color}',
               tc_stat = '{$tc_stat}',
               tc_stat_amount = {$tc_stat_amount},
               tc_required = {$tc_required},
               tc_cost = {$tc_cost},
               tc_stress = {$tc_stress},
               tc_max_repeat = {$tc_max_repeat},
               tc_order = {$tc_order},
               tc_use = {$tc_use},
               tc_updated = NOW()
               WHERE tc_id = {$tc_id}");

    alert('수정되었습니다.', $redirect_url);
} else {
    // 등록
    sql_query("INSERT INTO {$g5['mg_training_class_table']}
               (tc_name, tc_desc, tc_icon, tc_icon_color, tc_stat, tc_stat_amount, tc_required, tc_cost, tc_stress, tc_max_repeat, tc_order, tc_use, tc_created, tc_updated)
               VALUES ('{$tc_name}', '{$tc_desc}', '{$tc_icon}', '{$tc_icon_color}', '{$tc_stat}', {$tc_stat_amount}, {$tc_required}, {$tc_cost}, {$tc_stress}, {$tc_max_repeat}, {$tc_order}, {$tc_use}, NOW(), NOW())");

    alert('등록되었습니다.', $redirect_url);
}
