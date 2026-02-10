<?php
/**
 * Morgan Edition - 역극 완결 처리
 *
 * 판장(역극 생성자)이 캐릭터별 또는 전체 완결할 수 있습니다.
 *
 * 파라미터:
 *   rt_id  - 역극 ID (필수)
 *   ch_id  - 완결할 캐릭터 ID (선택, 없으면 전체 완결)
 *   force  - 보상 조건 미충족 시 강제 완결 (선택)
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
$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
$force = isset($_GET['force']) ? (int)$_GET['force'] : 0;

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

$redirect_url = G5_BBS_URL.'/rp_list.php#rp-thread-'.$rt_id;

if ($ch_id > 0) {
    // === 캐릭터별 개별 완결 ===
    $result = mg_rp_complete_character($rt_id, $ch_id, 'manual', $member['mb_id'], (bool)$force);
    if (!$result['success']) {
        alert($result['message'], $redirect_url);
    }
} else {
    // === 전체 완결: 미완결 참여자 전원 처리 ===
    $owner_ch_id = (int)$thread['ch_id'];
    $members = mg_get_rp_members($rt_id);

    foreach ($members as $mem) {
        if ((int)$mem['ch_id'] === $owner_ch_id) continue;
        // 이미 완결된 캐릭터 skip
        $existing = sql_fetch("SELECT rc_id FROM {$g5['mg_rp_completion_table']} WHERE rt_id = {$rt_id} AND ch_id = ".(int)$mem['ch_id']);
        if ($existing['rc_id']) continue;

        mg_rp_complete_character($rt_id, (int)$mem['ch_id'], 'manual', $member['mb_id'], true);
    }

    // 스레드 closed
    sql_query("UPDATE {$g5['mg_rp_thread_table']} SET rt_status = 'closed' WHERE rt_id = {$rt_id}");

    // 판장에게 알림 (관리자가 완결한 경우)
    if ($thread['mb_id'] !== $member['mb_id'] && function_exists('mg_notify')) {
        mg_notify(
            $thread['mb_id'],
            'system',
            '역극 "' . mb_substr(strip_tags($thread['rt_title']), 0, 30) . '"이(가) 완결되었습니다.',
            '',
            $redirect_url
        );
    }
}

goto_url($redirect_url);
