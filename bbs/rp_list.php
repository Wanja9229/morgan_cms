<?php
/**
 * Morgan Edition - 역극 목록 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

// Check if RP is enabled
if (!mg_config('rp_use', '1')) { alert_close('역극 기능이 비활성화되어 있습니다.'); }

// Params
$status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : 'all';
$my = isset($_GET['my']) ? 1 : 0;
$owner = isset($_GET['owner']) ? clean_xss_tags($_GET['owner']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 20;

// Get threads
// owner 파라미터가 있으면 해당 회원이 판장인 역극만 표시
$mb_filter = $my ? $member['mb_id'] : ($owner ?: '');
$result = mg_get_rp_threads($status, $mb_filter, $page, $rows);

$g5['title'] = '역극';
include_once(G5_THEME_PATH.'/head.php');
include_once(G5_THEME_PATH.'/skin/rp/list.skin.php');
include_once(G5_THEME_PATH.'/tail.php');
