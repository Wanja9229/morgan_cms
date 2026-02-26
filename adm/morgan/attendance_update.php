<?php
/**
 * Morgan Edition - 출석 설정 저장
 */

$sub_menu = "800500";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 모드 판별
$mode = isset($_POST['mode']) ? $_POST['mode'] : 'settings';

// --- 운세 CRUD ---
if ($mode === 'fortune_add') {
    $gf_star = max(1, min(5, (int)$_POST['gf_star']));
    $gf_text = trim($_POST['gf_text'] ?? '');
    $gf_point = max(0, (int)$_POST['gf_point']);

    if ($gf_text) {
        $gf_text_esc = sql_real_escape_string($gf_text);
        $max_sort = sql_fetch("SELECT IFNULL(MAX(gf_sort),0) as ms FROM {$g5['mg_game_fortune_table']}");
        $new_sort = ((int)$max_sort['ms']) + 1;
        sql_query("INSERT INTO {$g5['mg_game_fortune_table']} (gf_star, gf_text, gf_point, gf_use, gf_sort) VALUES ($gf_star, '$gf_text_esc', $gf_point, 1, $new_sort)");
    }
    goto_url('./attendance.php?tab=fortune');

} elseif ($mode === 'fortune_edit') {
    $gf_id = (int)$_POST['gf_id'];
    $gf_star = max(1, min(5, (int)$_POST['gf_star']));
    $gf_text = trim($_POST['gf_text'] ?? '');
    $gf_point = max(0, (int)$_POST['gf_point']);

    if ($gf_id && $gf_text) {
        $gf_text_esc = sql_real_escape_string($gf_text);
        sql_query("UPDATE {$g5['mg_game_fortune_table']} SET gf_star=$gf_star, gf_text='$gf_text_esc', gf_point=$gf_point WHERE gf_id=$gf_id");
    }
    goto_url('./attendance.php?tab=fortune');

} elseif ($mode === 'fortune_delete') {
    $gf_id = (int)$_POST['gf_id'];
    if ($gf_id) {
        sql_query("DELETE FROM {$g5['mg_game_fortune_table']} WHERE gf_id=$gf_id");
    }
    goto_url('./attendance.php?tab=fortune');

} elseif ($mode === 'fortune_weights') {
    $total = 0;
    $weights = [];
    for ($s = 1; $s <= 5; $s++) {
        $w = isset($_POST['fortune_weight_' . $s]) ? max(0, min(100, (int)$_POST['fortune_weight_' . $s])) : 0;
        $weights[$s] = $w;
        $total += $w;
    }
    if ($total > 100) {
        alert('확률 합계가 100%를 초과합니다.');
    }
    foreach ($weights as $s => $w) {
        mg_set_config('fortune_weight_' . $s, $w);
    }
    goto_url('./attendance.php?tab=fortune');

} elseif ($mode === 'fortune_toggle') {
    $gf_id = (int)$_POST['gf_id'];
    if ($gf_id) {
        sql_query("UPDATE {$g5['mg_game_fortune_table']} SET gf_use = IF(gf_use=1, 0, 1) WHERE gf_id=$gf_id");
    }
    goto_url('./attendance.php?tab=fortune');
}

// --- 종이뽑기 CRUD ---
if ($mode === 'lottery_board') {
    $glb_size = max(10, min(200, (int)$_POST['glb_size']));
    $glb_bonus_point = max(0, (int)$_POST['glb_bonus_point']);

    // 기존 판이 있으면 UPDATE, 없으면 INSERT
    $existing = sql_fetch("SELECT glb_id FROM {$g5['mg_game_lottery_board_table']} WHERE glb_id = 1");
    if (!empty($existing['glb_id'])) {
        sql_query("UPDATE {$g5['mg_game_lottery_board_table']} SET glb_size = {$glb_size}, glb_bonus_point = {$glb_bonus_point} WHERE glb_id = 1");
    } else {
        sql_query("INSERT INTO {$g5['mg_game_lottery_board_table']} (glb_id, glb_size, glb_bonus_point, glb_use) VALUES (1, {$glb_size}, {$glb_bonus_point}, 1)");
    }
    goto_url('./attendance.php?tab=lottery');

} elseif ($mode === 'lottery_add') {
    $glp_rank = max(1, min(10, (int)$_POST['glp_rank']));
    $glp_name = trim($_POST['glp_name'] ?? '');
    $glp_count = max(1, (int)$_POST['glp_count']);
    $glp_point = max(0, (int)$_POST['glp_point']);

    if ($glp_name) {
        $glp_name_esc = sql_real_escape_string($glp_name);
        sql_query("INSERT INTO {$g5['mg_game_lottery_prize_table']} (glp_rank, glp_name, glp_count, glp_point, glp_use) VALUES ({$glp_rank}, '{$glp_name_esc}', {$glp_count}, {$glp_point}, 1)");
    }
    goto_url('./attendance.php?tab=lottery');

} elseif ($mode === 'lottery_edit') {
    $glp_id = (int)$_POST['glp_id'];
    $glp_rank = max(1, min(10, (int)$_POST['glp_rank']));
    $glp_name = trim($_POST['glp_name'] ?? '');
    $glp_count = max(1, (int)$_POST['glp_count']);
    $glp_point = max(0, (int)$_POST['glp_point']);

    if ($glp_id && $glp_name) {
        $glp_name_esc = sql_real_escape_string($glp_name);
        sql_query("UPDATE {$g5['mg_game_lottery_prize_table']} SET glp_rank={$glp_rank}, glp_name='{$glp_name_esc}', glp_count={$glp_count}, glp_point={$glp_point} WHERE glp_id={$glp_id}");
    }
    goto_url('./attendance.php?tab=lottery');

} elseif ($mode === 'lottery_delete') {
    $glp_id = (int)$_POST['glp_id'];
    if ($glp_id) {
        sql_query("DELETE FROM {$g5['mg_game_lottery_prize_table']} WHERE glp_id={$glp_id}");
    }
    goto_url('./attendance.php?tab=lottery');

} elseif ($mode === 'lottery_toggle') {
    $glp_id = (int)$_POST['glp_id'];
    if ($glp_id) {
        sql_query("UPDATE {$g5['mg_game_lottery_prize_table']} SET glp_use = IF(glp_use=1, 0, 1) WHERE glp_id={$glp_id}");
    }
    goto_url('./attendance.php?tab=lottery');
}

// --- 주사위 설정 저장 ---
if ($mode === 'dice_settings') {
    $configs = array(
        'dice_reroll_count' => isset($_POST['dice_reroll_count']) ? (int)$_POST['dice_reroll_count'] : 2,
    );

    if ($configs['dice_reroll_count'] < 0) $configs['dice_reroll_count'] = 0;
    if ($configs['dice_reroll_count'] > 5) $configs['dice_reroll_count'] = 5;

    // 족보별 포인트
    $combo_keys = array(
        'dice_combo_yahtzee', 'dice_combo_four_kind', 'dice_combo_large_straight',
        'dice_combo_full_house', 'dice_combo_small_straight', 'dice_combo_triple',
    );
    foreach ($combo_keys as $ck) {
        if (isset($_POST[$ck])) {
            $configs[$ck] = max(0, (int)$_POST[$ck]);
        }
    }

    foreach ($configs as $key => $value) {
        mg_set_config($key, $value);
    }
    goto_url('./attendance.php?tab=dice');
}

// --- 공통 설정 저장 ---
$configs = array(
    'attendance_game' => isset($_POST['attendance_game']) ? $_POST['attendance_game'] : 'dice',
    'dice_bonus_multiplier' => isset($_POST['dice_bonus_multiplier']) ? (float)$_POST['dice_bonus_multiplier'] : 2,
    'attendance_streak_bonus_days' => isset($_POST['attendance_streak_bonus_days']) ? (int)$_POST['attendance_streak_bonus_days'] : 7,
);

// 출석 재료 보상 JSON
if (isset($_POST['attendance_material_reward'])) {
    $att_mat = trim($_POST['attendance_material_reward']);
    if ($att_mat) {
        $decoded = json_decode($att_mat, true);
        if (!is_array($decoded)) $att_mat = '';
    }
    $configs['attendance_material_reward'] = $att_mat;
}

// 유효성 검사
if ($configs['dice_bonus_multiplier'] < 1) $configs['dice_bonus_multiplier'] = 1;
if ($configs['attendance_streak_bonus_days'] < 2) $configs['attendance_streak_bonus_days'] = 2;

foreach ($configs as $key => $value) {
    mg_set_config($key, $value);
}

goto_url('./attendance.php?tab=settings');
