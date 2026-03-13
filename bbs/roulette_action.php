<?php
/**
 * Morgan Edition - 룰렛 벌칙 액션 (무효화/떠넘기기) AJAX
 */
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
include_once(G5_PLUGIN_PATH.'/morgan/roulette.php');

header('Content-Type: application/json');

if ($is_guest) {
    echo json_encode(array('error' => '로그인이 필요합니다.'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$rl_id = (int)($input['rl_id'] ?? 0);

if (!$rl_id || !in_array($action, array('nullify', 'transfer_random', 'transfer_target'))) {
    echo json_encode(array('error' => '잘못된 요청입니다.'));
    exit;
}

switch ($action) {
    case 'nullify':
        if (mg_roulette_nullify($rl_id, $member['mb_id'])) {
            echo json_encode(array('message' => '벌칙이 무효화되었습니다.'));
        } else {
            echo json_encode(array('error' => '무효화에 실패했습니다. 아이템을 확인해주세요.'));
        }
        break;

    case 'transfer_random':
        $target = mg_roulette_random_target($member['mb_id']);
        if (!$target) {
            echo json_encode(array('error' => '떠넘길 대상이 없습니다.'));
            break;
        }
        if (mg_roulette_transfer($rl_id, $member['mb_id'], $target, 'transfer_random')) {
            echo json_encode(array('message' => '벌칙을 랜덤 회원에게 떠넘겼습니다.'));
        } else {
            echo json_encode(array('error' => '떠넘기기에 실패했습니다. 아이템을 확인해주세요.'));
        }
        break;

    case 'transfer_target':
        $target_mb_id = trim($input['target_mb_id'] ?? '');
        if (!$target_mb_id) {
            echo json_encode(array('error' => '대상 아이디를 입력해주세요.'));
            break;
        }
        // 대상 검증
        $target_member = sql_fetch("SELECT mb_id FROM {$g5['member_table']}
            WHERE mb_id = '".sql_real_escape_string($target_mb_id)."'
            AND mb_leave_dt = '' AND mb_intercept_date = ''");
        if (!$target_member || !$target_member['mb_id']) {
            echo json_encode(array('error' => '존재하지 않거나 활동 불가한 회원입니다.'));
            break;
        }
        if ($target_mb_id === $member['mb_id']) {
            echo json_encode(array('error' => '자기 자신에게는 떠넘길 수 없습니다.'));
            break;
        }
        if (mg_roulette_transfer($rl_id, $member['mb_id'], $target_mb_id, 'transfer_target')) {
            echo json_encode(array('message' => '벌칙을 떠넘겼습니다.'));
        } else {
            echo json_encode(array('error' => '떠넘기기에 실패했습니다. 아이템을 확인해주세요.'));
        }
        break;
}
