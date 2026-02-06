<?php
/**
 * Morgan Edition - 역극 상태 변경
 */

$sub_menu = "800650";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$rt_id = isset($_POST['rt_id']) ? (int)$_POST['rt_id'] : 0;
$rt_status = isset($_POST['rt_status']) ? clean_xss_tags($_POST['rt_status']) : '';
$redirect = isset($_POST['redirect']) ? clean_xss_tags($_POST['redirect']) : './rp_list.php';

if (!$rt_id || !in_array($rt_status, array('open', 'closed', 'deleted'))) {
    alert('잘못된 요청입니다.');
}

// 역극 존재 여부 확인
$thread = sql_fetch("SELECT rt_id, rt_status FROM {$g5['mg_rp_thread_table']} WHERE rt_id = {$rt_id}");
if (!$thread['rt_id']) {
    alert('존재하지 않는 역극입니다.');
}

$rt_status_esc = sql_real_escape_string($rt_status);
sql_query("UPDATE {$g5['mg_rp_thread_table']} SET rt_status = '{$rt_status_esc}' WHERE rt_id = {$rt_id}");

goto_url($redirect);
