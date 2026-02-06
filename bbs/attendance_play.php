<?php
/**
 * Morgan Edition - 출석체크 게임 API
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

// 현재 활성 게임 가져오기
$game = MG_Game_Factory::getActiveGame();

// 게임 실행
$result = $game->play($member['mb_id']);

// Morgan: 출석 재료 보상
if ($result['success'] && function_exists('mg_pioneer_enabled') && mg_pioneer_enabled()) {
    $mat_reward = mg_reward_material($member['mb_id'], 'attendance');
    if ($mat_reward) {
        $result['material_reward'] = $mat_reward;
    }
}

// 결과에 렌더링된 HTML 추가
$result['html'] = $game->renderResult($result);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
