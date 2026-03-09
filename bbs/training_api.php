<?php
/**
 * Morgan Edition - 수업 스케줄 API
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');
include_once(G5_PATH.'/plugin/morgan/training.php');

header('Content-Type: application/json; charset=utf-8');

if (mg_config('battle_use', '1') != '1' || mg_config('battle_training_use', '1') != '1') {
    echo json_encode(array('success' => false, 'message' => '수업 스케줄 기능이 비활성화되어 있습니다.'));
    exit;
}
if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$mb_id = $member['mb_id'];
$action = isset($_POST['action']) ? clean_xss_tags($_POST['action']) : '';

switch ($action) {
    case 'save_schedule':
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $year  = (int)($_POST['year'] ?? 0);
        $week  = (int)($_POST['week'] ?? 0);
        $slots_json = isset($_POST['slots']) ? $_POST['slots'] : '[]';
        $slots = json_decode($slots_json, true);

        if (!$ch_id || !$year || !$week) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        // 캐릭터 소유 확인
        $ch = sql_fetch("SELECT ch_id, mb_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id}' AND ch_status = 'approved'");
        if (!$ch) {
            echo json_encode(array('success' => false, 'message' => '캐릭터를 찾을 수 없습니다.'));
            exit;
        }

        $result = mg_training_save_schedule($ch_id, $mb_id, $year, $week, $slots);
        echo json_encode($result);
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
        break;
}
