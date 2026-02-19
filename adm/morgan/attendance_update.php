<?php
/**
 * Morgan Edition - 출석 설정 저장
 */

$sub_menu = "800500";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 설정값 저장
$configs = array(
    'attendance_game' => isset($_POST['attendance_game']) ? $_POST['attendance_game'] : 'dice',
    'dice_count' => isset($_POST['dice_count']) ? (int)$_POST['dice_count'] : 5,
    'dice_sides' => isset($_POST['dice_sides']) ? (int)$_POST['dice_sides'] : 6,
    'dice_bonus_multiplier' => isset($_POST['dice_bonus_multiplier']) ? (float)$_POST['dice_bonus_multiplier'] : 2,
    'attendance_streak_bonus_days' => isset($_POST['attendance_streak_bonus_days']) ? (int)$_POST['attendance_streak_bonus_days'] : 7,
    'dice_combo_enabled' => isset($_POST['dice_combo_enabled']) ? (int)$_POST['dice_combo_enabled'] : 1,
    'dice_reroll_count' => isset($_POST['dice_reroll_count']) ? (int)$_POST['dice_reroll_count'] : 2,
);

// 족보별 포인트 설정
$combo_keys = array(
    'dice_combo_yahtzee', 'dice_combo_four_kind', 'dice_combo_large_straight',
    'dice_combo_full_house', 'dice_combo_small_straight', 'dice_combo_triple',
);
foreach ($combo_keys as $ck) {
    if (isset($_POST[$ck])) {
        $configs[$ck] = max(0, (int)$_POST[$ck]);
    }
}

// 유효성 검사
if ($configs['dice_count'] < 1) $configs['dice_count'] = 1;
if ($configs['dice_count'] > 5) $configs['dice_count'] = 5;
if ($configs['dice_sides'] < 6) $configs['dice_sides'] = 6;
if ($configs['dice_bonus_multiplier'] < 1) $configs['dice_bonus_multiplier'] = 1;
if ($configs['attendance_streak_bonus_days'] < 2) $configs['attendance_streak_bonus_days'] = 2;
if ($configs['dice_combo_enabled'] < 0) $configs['dice_combo_enabled'] = 0;
if ($configs['dice_combo_enabled'] > 1) $configs['dice_combo_enabled'] = 1;
if ($configs['dice_reroll_count'] < 0) $configs['dice_reroll_count'] = 0;
if ($configs['dice_reroll_count'] > 5) $configs['dice_reroll_count'] = 5;

foreach ($configs as $key => $value) {
    mg_set_config($key, $value);
}

goto_url('./attendance.php?tab=settings');
