<?php
/**
 * Morgan Edition - 탐색 파견 API (AJAX)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$mb_id = $member['mb_id'];

switch ($action) {

    // 현재 상태: 스태미나 + 진행 중 파견 + 슬롯 정보
    case 'status':
        $stamina = mg_get_stamina($mb_id);
        $active = mg_get_active_expeditions($mb_id);
        $max_slots = (int)mg_config('expedition_max_slots', 1);

        echo json_encode(array(
            'success' => true,
            'stamina' => $stamina,
            'active' => $active,
            'max_slots' => $max_slots,
            'used_slots' => count($active),
        ));
        break;

    // 파견지 목록
    case 'areas':
        $areas = mg_get_expedition_areas('active', $mb_id);

        echo json_encode(array(
            'success' => true,
            'areas' => $areas,
        ));
        break;

    // 내 캐릭터 목록
    case 'my_characters':
        $characters = mg_get_usable_characters($mb_id);

        echo json_encode(array(
            'success' => true,
            'characters' => $characters,
        ));
        break;

    // 파트너 후보 (관계 기반)
    case 'partner_candidates':
        $ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
        if (!$ch_id) {
            echo json_encode(array('success' => false, 'message' => '캐릭터를 선택해주세요.'));
            exit;
        }

        // 소유 확인
        $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']}
                         WHERE ch_id = {$ch_id} AND mb_id = '".sql_real_escape_string($mb_id)."' AND ch_state = 'approved'");
        if (!$ch) {
            echo json_encode(array('success' => false, 'message' => '사용할 수 없는 캐릭터입니다.'));
            exit;
        }

        $candidates = mg_get_expedition_partner_candidates($ch_id);

        echo json_encode(array(
            'success' => true,
            'candidates' => $candidates,
        ));
        break;

    // 파견 시작
    case 'start':
        $ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
        $ea_id = isset($_POST['ea_id']) ? (int)$_POST['ea_id'] : 0;
        $partner_ch_id = isset($_POST['partner_ch_id']) ? (int)$_POST['partner_ch_id'] : 0;

        if (!$ch_id || !$ea_id) {
            echo json_encode(array('success' => false, 'message' => '캐릭터와 파견지를 선택해주세요.'));
            exit;
        }

        $result = mg_start_expedition($mb_id, $ch_id, $ea_id, $partner_ch_id ?: null);
        echo json_encode($result);
        break;

    // 보상 수령
    case 'claim':
        $el_id = isset($_POST['el_id']) ? (int)$_POST['el_id'] : 0;
        if (!$el_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $result = mg_claim_expedition($mb_id, $el_id);
        echo json_encode($result);
        break;

    // 파견 취소
    case 'cancel':
        $el_id = isset($_POST['el_id']) ? (int)$_POST['el_id'] : 0;
        if (!$el_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $result = mg_cancel_expedition($mb_id, $el_id);
        echo json_encode($result);
        break;

    // 파견 이력
    case 'history':
        $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
        $history = mg_get_expedition_history($mb_id, $limit);

        echo json_encode(array(
            'success' => true,
            'history' => $history,
        ));
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
}
