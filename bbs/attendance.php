<?php
/**
 * Morgan Edition - 출석체크 페이지
 */

include_once('./_common.php');

// 로그인 체크
if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
include_once(G5_PLUGIN_PATH.'/morgan/games/MG_Game_Factory.php');

$g5['title'] = '출석체크';
include_once(G5_THEME_PATH.'/head.php');

// 현재 활성 게임 가져오기
$game = MG_Game_Factory::getActiveGame();

// 오늘 출석 여부 확인
$today = date('Y-m-d');
$sql = "SELECT * FROM {$g5['mg_attendance_table']}
        WHERE mb_id = '{$member['mb_id']}' AND at_date = '{$today}'";
$todayAttendance = sql_fetch($sql);
$hasAttended = !empty($todayAttendance);

// 이번 달 출석 현황
$thisMonth = date('Y-m');
$sql = "SELECT at_date, at_point, at_game_result FROM {$g5['mg_attendance_table']}
        WHERE mb_id = '{$member['mb_id']}'
        AND at_date LIKE '{$thisMonth}%'
        ORDER BY at_date ASC";
$result = sql_query($sql);
$monthlyAttendance = [];
while ($row = sql_fetch_array($result)) {
    $monthlyAttendance[$row['at_date']] = $row;
}

// 연속 출석 일수
$sql = "SELECT at_date FROM {$g5['mg_attendance_table']}
        WHERE mb_id = '{$member['mb_id']}'
        ORDER BY at_date DESC
        LIMIT 30";
$result = sql_query($sql);
$streakDays = 0;
$checkDate = $hasAttended ? $today : date('Y-m-d', strtotime('-1 day'));

while ($row = sql_fetch_array($result)) {
    if ($row['at_date'] == $checkDate) {
        $streakDays++;
        $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
    } else {
        break;
    }
}

// 이번 달 총 출석
$monthlyCount = count($monthlyAttendance);
$monthlyPoint = array_sum(array_column($monthlyAttendance, 'at_point'));

// 스킨 파일 경로
$skin_path = G5_THEME_PATH.'/skin/attendance';
if (!is_dir($skin_path)) {
    $skin_path = G5_SKIN_PATH.'/attendance/basic';
}

include_once($skin_path.'/attendance.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
