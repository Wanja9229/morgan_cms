<?php
/**
 * Morgan Edition - 역극 완결 처리
 *
 * 판장(역극 생성자)이 자신의 역극을 완결할 수 있습니다.
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// Morgan: 개척 시스템 해금 체크
if (function_exists('mg_is_board_unlocked') && !mg_is_board_unlocked('roleplay')) {
    alert('역극은 아직 개척되지 않았습니다.');
}

$rt_id = isset($_GET['rt_id']) ? (int)$_GET['rt_id'] : 0;
if (!$rt_id) {
    alert('잘못된 접근입니다.');
}

$thread = mg_get_rp_thread($rt_id);
if (!$thread || $thread['rt_status'] == 'deleted') {
    alert('존재하지 않는 역극입니다.');
}

// 판장 또는 최고관리자만 완결 가능
if ($thread['mb_id'] !== $member['mb_id'] && $is_admin !== 'super') {
    alert('역극 생성자만 완결할 수 있습니다.');
}

// 이미 완결된 역극
if ($thread['rt_status'] === 'closed') {
    alert('이미 완결된 역극입니다.', G5_BBS_URL.'/rp_list.php#rp-thread-'.$rt_id);
}

// 완결 처리
sql_query("UPDATE {$g5['mg_rp_thread_table']} SET rt_status = 'closed' WHERE rt_id = {$rt_id}");

// 판장에게 알림 (관리자가 완결한 경우)
if ($thread['mb_id'] !== $member['mb_id'] && function_exists('mg_notify')) {
    mg_notify(
        $thread['mb_id'],
        'system',
        '역극 "' . mb_substr(strip_tags($thread['rt_title']), 0, 30) . '"이(가) 완결되었습니다.',
        '',
        G5_BBS_URL.'/rp_list.php#rp-thread-'.$rt_id
    );
}

goto_url(G5_BBS_URL.'/rp_list.php#rp-thread-'.$rt_id);
