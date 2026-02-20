<?php
/**
 * Morgan Edition - 출석체크 게임 API
 *
 * 멀티스텝 지원: roll → reroll(×N) → finalize
 * 기존 단일 play() 호환 유지
 */

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if ($is_guest) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

// AJAX 요청 체크
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
include_once(G5_PLUGIN_PATH.'/morgan/games/MG_Game_Factory.php');

// JSON 바디 파싱
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = [];

$action = isset($input['action']) ? $input['action'] : 'play';

// 현재 활성 게임 가져오기
$game = MG_Game_Factory::getActiveGame();
$mb_id = $member['mb_id'];

// 멀티스텝 액션 분기
switch ($action) {
    case 'roll':
        // 주사위 게임: 초기 롤
        if (method_exists($game, 'startRoll')) {
            $result = $game->startRoll($mb_id);
        } else {
            // 멀티스텝 미지원 게임 → 즉시 play
            $result = $game->play($mb_id);
            $result['phase'] = 'finalize';
        }
        break;

    case 'reroll':
        // 주사위 게임: 리롤
        if (method_exists($game, 'doReroll')) {
            $held = isset($input['held']) ? $input['held'] : [];
            // held 배열 정수 변환
            $held = array_map('intval', $held);
            $result = $game->doReroll($mb_id, $held);
        } else {
            $result = ['success' => false, 'message' => '이 게임은 리롤을 지원하지 않습니다.'];
        }
        break;

    case 'finalize':
        // 주사위 게임: 최종 확정
        if (method_exists($game, 'finalize')) {
            $result = $game->finalize($mb_id);
        } else {
            $result = ['success' => false, 'message' => '잘못된 요청입니다.'];
        }
        break;

    case 'play':
    default:
        // 기존 호환: 즉시 실행
        // 종이뽑기: 유저가 선택한 번호 전달
        $number = isset($input['number']) ? (int)$input['number'] : 0;
        if ($number > 0) {
            $result = $game->play($mb_id, $number);
        } else {
            $result = $game->play($mb_id);
        }
        $result['phase'] = 'finalize';
        break;
}

// finalize 단계에서만 보상/업적 처리
$isFinalized = (isset($result['phase']) && $result['phase'] === 'finalize' && !empty($result['success']));

if ($isFinalized) {
    // Morgan: 출석 재료 보상
    if (function_exists('mg_pioneer_enabled') && mg_pioneer_enabled()) {
        $mat_reward = mg_reward_material($mb_id, 'attendance');
        if ($mat_reward) {
            $result['material_reward'] = $mat_reward;
        }
    }

    // Morgan: 업적 트리거 (출석)
    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($mb_id, 'attendance_total');
    }

    // 결과에 렌더링된 HTML 추가
    $result['html'] = $game->renderResult($result);
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
