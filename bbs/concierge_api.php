<?php
/**
 * Morgan Edition - 의뢰 매칭 API (AJAX)
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

// 회원 레벨 체크 (등록/지원만)
if (in_array($action, array('create', 'apply'))) {
    $_lv = mg_check_member_level('concierge', $member['mb_level']);
    if (!$_lv['allowed']) {
        echo json_encode(array('success' => false, 'message' => "의뢰는 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다."));
        exit;
    }
}

switch ($action) {

    // 의뢰 목록
    case 'list':
        $status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : null;
        $type = isset($_GET['type']) ? clean_xss_tags($_GET['type']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $result = mg_get_concierge_list($status, $type, $page, 20);
        echo json_encode(array('success' => true, 'data' => $result));
        break;

    // 의뢰 상세
    case 'detail':
        $cc_id = isset($_GET['cc_id']) ? (int)$_GET['cc_id'] : 0;
        if (!$cc_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }
        $cc = mg_get_concierge($cc_id);
        if (!$cc) {
            echo json_encode(array('success' => false, 'message' => '의뢰를 찾을 수 없습니다.'));
            exit;
        }
        echo json_encode(array('success' => true, 'data' => $cc));
        break;

    // 의뢰 등록
    case 'create':
        $data = array(
            'ch_id' => isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0,
            'cc_title' => isset($_POST['cc_title']) ? trim($_POST['cc_title']) : '',
            'cc_content' => isset($_POST['cc_content']) ? trim($_POST['cc_content']) : '',
            'cc_type' => isset($_POST['cc_type']) ? $_POST['cc_type'] : 'collaboration',
            'cc_max_members' => isset($_POST['cc_max_members']) ? (int)$_POST['cc_max_members'] : 1,
            'cc_match_mode' => isset($_POST['cc_match_mode']) ? $_POST['cc_match_mode'] : 'direct',
            'cc_deadline' => isset($_POST['cc_deadline']) ? $_POST['cc_deadline'] : '',
        );

        $result = mg_create_concierge($mb_id, $data);
        echo json_encode($result);
        break;

    // 지원
    case 'apply':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        $ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
        $message = isset($_POST['ca_message']) ? trim($_POST['ca_message']) : '';

        if (!$cc_id || !$ch_id) {
            echo json_encode(array('success' => false, 'message' => '의뢰와 캐릭터를 선택해주세요.'));
            exit;
        }

        $result = mg_apply_concierge($mb_id, $cc_id, $ch_id, $message);
        echo json_encode($result);
        break;

    // 매칭 (직접 선택)
    case 'match':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        $selected = isset($_POST['selected_ca_ids']) ? $_POST['selected_ca_ids'] : array();
        if (is_string($selected)) {
            $selected = json_decode($selected, true) ?: array();
        }

        if (!$cc_id || empty($selected)) {
            echo json_encode(array('success' => false, 'message' => '지원자를 선택해주세요.'));
            exit;
        }

        $result = mg_match_concierge($mb_id, $cc_id, $selected);
        echo json_encode($result);
        break;

    // 추첨
    case 'lottery':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        if (!$cc_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $result = mg_lottery_concierge($mb_id, $cc_id);
        echo json_encode($result);
        break;

    // 미이행 강제 종료
    case 'force_close':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        if (!$cc_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $result = mg_force_close_concierge($mb_id, $cc_id);
        echo json_encode($result);
        break;

    // 취소
    case 'cancel':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        if (!$cc_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $result = mg_cancel_concierge($mb_id, $cc_id);
        echo json_encode($result);
        break;

    // 내 캐릭터 목록
    case 'my_characters':
        $characters = mg_get_usable_characters($mb_id);
        echo json_encode(array('success' => true, 'characters' => $characters));
        break;

    // 수행 중인 의뢰 (write hook용)
    case 'my_matched':
        $list = mg_get_my_matched_concierges($mb_id);
        echo json_encode(array('success' => true, 'matched' => $list));
        break;

    // 의뢰 완료 (결과물 연결)
    case 'complete':
        $cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;
        $bo_table = isset($_POST['bo_table']) ? clean_xss_tags($_POST['bo_table']) : '';
        $wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;

        if (!$cc_id || !$bo_table || !$wr_id) {
            echo json_encode(array('success' => false, 'message' => '필수 정보가 부족합니다.'));
            exit;
        }

        $result = mg_complete_concierge($mb_id, $cc_id, $bo_table, $wr_id);
        echo json_encode($result);
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
}
