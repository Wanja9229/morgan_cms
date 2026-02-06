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
    'dice_min' => isset($_POST['dice_min']) ? (int)$_POST['dice_min'] : 10,
    'dice_max' => isset($_POST['dice_max']) ? (int)$_POST['dice_max'] : 100,
    'dice_bonus_multiplier' => isset($_POST['dice_bonus_multiplier']) ? (float)$_POST['dice_bonus_multiplier'] : 2,
    'attendance_streak_bonus_days' => isset($_POST['attendance_streak_bonus_days']) ? (int)$_POST['attendance_streak_bonus_days'] : 7,
);

// 유효성 검사
if ($configs['dice_min'] < 1) $configs['dice_min'] = 1;
if ($configs['dice_max'] < $configs['dice_min']) $configs['dice_max'] = $configs['dice_min'];
if ($configs['dice_bonus_multiplier'] < 1) $configs['dice_bonus_multiplier'] = 1;
if ($configs['attendance_streak_bonus_days'] < 2) $configs['attendance_streak_bonus_days'] = 2;

foreach ($configs as $key => $value) {
    mg_set_config($key, $value);
}

goto_url('./attendance.php?tab=settings');
