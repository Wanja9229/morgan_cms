<?php
/**
 * Morgan Edition - 룰렛 스핀 AJAX
 */
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
include_once(G5_PLUGIN_PATH.'/morgan/roulette.php');

header('Content-Type: application/json');

if ($is_guest) {
    echo json_encode(array('error' => '로그인이 필요합니다.'));
    exit;
}

$can = mg_roulette_can_spin($member['mb_id']);
if (!$can['ok']) {
    echo json_encode(array('error' => $can['reason']));
    exit;
}

// 포인트 차감
$cost = (int)mg_config('roulette_cost', 100);
insert_point($member['mb_id'], -$cost, '룰렛 사용', 'roulette', '', 'spin');

// 잭팟 풀 누적
$pool = (int)mg_config('roulette_jackpot_pool', 0) + $cost;
mg_set_config('roulette_jackpot_pool', (string)$pool);

// 추첨
$prize = mg_roulette_pick_prize();
if (!$prize) {
    // 환불
    insert_point($member['mb_id'], $cost, '룰렛 환불 (항목 없음)', 'roulette', '', 'refund');
    $pool -= $cost;
    mg_set_config('roulette_jackpot_pool', (string)$pool);
    echo json_encode(array('error' => '룰렛 항목이 없습니다.'));
    exit;
}

$mb_esc = sql_real_escape_string($member['mb_id']);

// 상태 결정
if ($prize['rp_type'] === 'penalty') {
    $status = 'pending';
} else {
    $status = 'completed';
}

// 로그 INSERT
sql_query("INSERT INTO {$g5['mg_roulette_log_table']}
    (mb_id, rp_id, rl_source, rl_status, rl_cost, rl_datetime)
    VALUES ('{$mb_esc}', {$prize['rp_id']}, 'spin', '{$status}', {$cost}, NOW())");
$rl_id = sql_insert_id();

// 보상/벌칙 처리
if ($prize['rp_type'] === 'reward') {
    mg_roulette_apply_reward($member['mb_id'], $prize, $rl_id);
} elseif ($prize['rp_type'] === 'jackpot') {
    mg_roulette_jackpot($member['mb_id'], $rl_id);
    $pool = 0;
} elseif ($prize['rp_type'] === 'penalty') {
    mg_notify($member['mb_id'], 'roulette', '룰렛 벌칙 당첨: ' . $prize['rp_name'],
        $prize['rp_desc'] ?? '', G5_BBS_URL . '/roulette.php');
}

// 응답
$my_point = (int)sql_fetch("SELECT mb_point FROM {$g5['member_table']} WHERE mb_id = '{$mb_esc}'")['mb_point'];
$today_count = mg_roulette_today_count($member['mb_id']);
$can_next = mg_roulette_can_spin($member['mb_id']);

echo json_encode(array(
    'prize' => array(
        'rp_id' => (int)$prize['rp_id'],
        'rp_name' => $prize['rp_name'],
        'rp_type' => $prize['rp_type'],
        'rp_color' => $prize['rp_color'],
    ),
    'rl_id' => $rl_id,
    'pool' => $pool,
    'my_point' => $my_point,
    'today_count' => $today_count,
    'can_spin' => $can_next['ok'],
), JSON_UNESCAPED_UNICODE);
