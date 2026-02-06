<?php
/**
 * Morgan Edition - 역극 보기 페이지 (Chat UI)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

$rt_id = isset($_GET['rt_id']) ? (int)$_GET['rt_id'] : 0;
if (!$rt_id) { alert_close('잘못된 접근입니다.'); }

$thread = mg_get_rp_thread($rt_id);
if (!$thread || $thread['rt_status'] == 'deleted') { alert_close('존재하지 않는 역극입니다.'); }

$replies = mg_get_rp_replies($rt_id);
$members = mg_get_rp_members($rt_id);
$my_characters = mg_get_usable_characters($member['mb_id']);

// Check if user can join
$join_check = mg_can_join_rp($rt_id, $member['mb_id']);

// Is owner
$is_owner = ($thread['mb_id'] == $member['mb_id']);

$g5['title'] = $thread['rt_title'] . ' - 역극';
include_once(G5_THEME_PATH.'/head.php');
include_once(G5_THEME_PATH.'/skin/rp/view.skin.php');
include_once(G5_THEME_PATH.'/tail.php');
