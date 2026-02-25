<?php
/**
 * Morgan Edition - 칭호 관리 API (AJAX)
 */

include_once('./_common.php');

header('Content-Type: application/json');

if ($is_guest) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// 프로필 칭호 설정
if ($action === 'set_profile') {
    $prefix_tp_id = isset($_POST['prefix_tp_id']) ? (int)$_POST['prefix_tp_id'] : 0;
    $suffix_tp_id = isset($_POST['suffix_tp_id']) ? (int)$_POST['suffix_tp_id'] : 0;

    // 보유 확인
    if ($prefix_tp_id) {
        $owns = sql_fetch("SELECT mt_id FROM {$g5['mg_member_title_table']}
            WHERE mb_id = '".sql_real_escape_string($member['mb_id'])."' AND tp_id = {$prefix_tp_id}");
        if (!$owns['mt_id']) {
            echo json_encode(['success' => false, 'message' => '보유하지 않은 접두칭호입니다.']);
            exit;
        }
    }
    if ($suffix_tp_id) {
        $owns = sql_fetch("SELECT mt_id FROM {$g5['mg_member_title_table']}
            WHERE mb_id = '".sql_real_escape_string($member['mb_id'])."' AND tp_id = {$suffix_tp_id}");
        if (!$owns['mt_id']) {
            echo json_encode(['success' => false, 'message' => '보유하지 않은 접미칭호입니다.']);
            exit;
        }
    }

    mg_set_title_setting($member['mb_id'], $prefix_tp_id ?: null, $suffix_tp_id ?: null);
    echo json_encode(['success' => true]);
    exit;
}

// 캐릭터 칭호 설정
if ($action === 'set_character') {
    $ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
    $prefix_tp_id = isset($_POST['prefix_tp_id']) ? (int)$_POST['prefix_tp_id'] : 0;
    $suffix_tp_id = isset($_POST['suffix_tp_id']) ? (int)$_POST['suffix_tp_id'] : 0;

    if (!$ch_id) {
        echo json_encode(['success' => false, 'message' => '캐릭터를 선택해주세요.']);
        exit;
    }

    // 내 캐릭터 확인
    $char = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']}
        WHERE ch_id = {$ch_id} AND mb_id = '".sql_real_escape_string($member['mb_id'])."'");
    if (!$char['ch_id']) {
        echo json_encode(['success' => false, 'message' => '권한이 없는 캐릭터입니다.']);
        exit;
    }

    // 보유 확인
    $mb_esc = sql_real_escape_string($member['mb_id']);
    if ($prefix_tp_id) {
        $owns = sql_fetch("SELECT mt_id FROM {$g5['mg_member_title_table']}
            WHERE mb_id = '{$mb_esc}' AND tp_id = {$prefix_tp_id}");
        if (!$owns['mt_id']) {
            echo json_encode(['success' => false, 'message' => '보유하지 않은 접두칭호입니다.']);
            exit;
        }
    }
    if ($suffix_tp_id) {
        $owns = sql_fetch("SELECT mt_id FROM {$g5['mg_member_title_table']}
            WHERE mb_id = '{$mb_esc}' AND tp_id = {$suffix_tp_id}");
        if (!$owns['mt_id']) {
            echo json_encode(['success' => false, 'message' => '보유하지 않은 접미칭호입니다.']);
            exit;
        }
    }

    mg_set_character_title($ch_id, $prefix_tp_id ?: null, $suffix_tp_id ?: null);
    echo json_encode(['success' => true]);
    exit;
}

// 칭호 삭제
if ($action === 'delete') {
    $tp_id = isset($_POST['tp_id']) ? (int)$_POST['tp_id'] : 0;
    if (!$tp_id) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        exit;
    }

    mg_delete_member_title($member['mb_id'], $tp_id);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => '알 수 없는 요청입니다.']);
