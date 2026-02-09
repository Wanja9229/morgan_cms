<?php
/**
 * Morgan Edition - 역극 목록 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

// Check if RP is enabled
if (!mg_config('rp_use', '1')) { alert_close('역극 기능이 비활성화되어 있습니다.'); }

// Morgan: 개척 시스템 해금 체크
if (function_exists('mg_is_board_unlocked') && !mg_is_board_unlocked('roleplay')) {
    $unlock_info = mg_get_unlock_info('board', 'roleplay');
    $facility_name = $unlock_info ? $unlock_info['fc_name'] : '역극 게시판';
    alert("역극은 아직 개척되지 않았습니다.\\n\\n'{$facility_name}' 개척에 참여해주세요!", G5_BBS_URL.'/pioneer.php');
}

// Params
$status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : 'all';
$my = isset($_GET['my']) ? 1 : 0;
$owner = isset($_GET['owner']) ? clean_xss_tags($_GET['owner']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 20;

// Get threads
$mb_filter = $my ? $member['mb_id'] : ($owner ?: '');
$result = mg_get_rp_threads($status, $mb_filter, $page, $rows);

// 각 스레드에 참여자 데이터 첨부
foreach ($result['threads'] as &$thread) {
    $thread['members'] = mg_get_rp_members($thread['rt_id']);
}
unset($thread);

// 유저 데이터 (이음 폼 + 글쓰기 모달용)
$my_characters = $is_member ? mg_get_usable_characters($member['mb_id']) : array();
$can_create = $is_member ? mg_can_create_rp($member['mb_id']) : array('can_create' => false);
$max_member_default = (int)mg_config('rp_max_member_default', 0);
$max_member_limit = (int)mg_config('rp_max_member_limit', 20);

$g5['title'] = '역극';
include_once(G5_THEME_PATH.'/head.php');
include_once(G5_THEME_PATH.'/skin/rp/list.skin.php');
include_once(G5_THEME_PATH.'/tail.php');
