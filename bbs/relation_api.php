<?php
/**
 * Morgan Edition - 관계 AJAX API
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {

    // 관계 신청
    case 'request':
        $from_ch_id = (int)($_POST['from_ch_id'] ?? 0);
        $to_ch_id = (int)($_POST['to_ch_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $color = trim($_POST['color'] ?? '#95a5a6');
        $memo = trim($_POST['memo'] ?? '');

        if (!$from_ch_id || !$to_ch_id || !$label) {
            echo json_encode(array('success' => false, 'message' => '필수 항목을 입력해주세요.'));
            exit;
        }

        // 내 캐릭터인지 확인
        $from_char = mg_get_character($from_ch_id);
        if (!$from_char || $from_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        // 대상 캐릭터 존재 확인
        $to_char = mg_get_character($to_ch_id);
        if (!$to_char || $to_char['ch_state'] !== 'approved') {
            echo json_encode(array('success' => false, 'message' => '대상 캐릭터를 찾을 수 없습니다.'));
            exit;
        }

        $result = mg_request_relation($from_ch_id, $to_ch_id, $label, $color, $memo);
        echo json_encode($result);
        break;

    // 관계 승인
    case 'accept':
        $cr_id = (int)($_POST['cr_id'] ?? 0);
        $label_b = trim($_POST['label_b'] ?? '');
        $memo_b = trim($_POST['memo_b'] ?? '');
        $color = trim($_POST['color'] ?? '');

        if (!$cr_id) {
            echo json_encode(array('success' => false, 'message' => '관계 ID가 없습니다.'));
            exit;
        }

        // 권한 확인: 내 캐릭터가 대상인지
        $rel = mg_get_relation($cr_id);
        if (!$rel || $rel['cr_status'] !== 'pending') {
            echo json_encode(array('success' => false, 'message' => '유효하지 않은 관계입니다.'));
            exit;
        }
        $approver_ch = ($rel['ch_id_from'] == $rel['ch_id_a']) ? $rel['ch_id_b'] : $rel['ch_id_a'];
        $approver_char = mg_get_character($approver_ch);
        if (!$approver_char || $approver_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        $result = mg_accept_relation($cr_id, $label_b, $memo_b, $color);
        echo json_encode($result);
        break;

    // 관계 거절
    case 'reject':
        $cr_id = (int)($_POST['cr_id'] ?? 0);
        $rel = mg_get_relation($cr_id);
        if (!$rel || $rel['cr_status'] !== 'pending') {
            echo json_encode(array('success' => false, 'message' => '유효하지 않은 관계입니다.'));
            exit;
        }
        $approver_ch = ($rel['ch_id_from'] == $rel['ch_id_a']) ? $rel['ch_id_b'] : $rel['ch_id_a'];
        $approver_char = mg_get_character($approver_ch);
        if (!$approver_char || $approver_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }
        $result = mg_reject_relation($cr_id);
        echo json_encode($result);
        break;

    // 관계 해제
    case 'delete':
        $cr_id = (int)($_POST['cr_id'] ?? 0);
        $my_ch_id = (int)($_POST['my_ch_id'] ?? 0);

        $rel = mg_get_relation($cr_id);
        if (!$rel) {
            echo json_encode(array('success' => false, 'message' => '존재하지 않는 관계입니다.'));
            exit;
        }
        // 내 캐릭터가 당사자인지 확인
        $my_char = mg_get_character($my_ch_id);
        if (!$my_char || $my_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }
        if ($my_ch_id != $rel['ch_id_a'] && $my_ch_id != $rel['ch_id_b']) {
            echo json_encode(array('success' => false, 'message' => '해당 관계의 당사자가 아닙니다.'));
            exit;
        }

        $result = mg_delete_relation($cr_id, $my_ch_id);
        echo json_encode($result);
        break;

    // 자기쪽 수정
    case 'update':
        $cr_id = (int)($_POST['cr_id'] ?? 0);
        $my_ch_id = (int)($_POST['my_ch_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $memo = trim($_POST['memo'] ?? '');
        $color = trim($_POST['color'] ?? '');

        $my_char = mg_get_character($my_ch_id);
        if (!$my_char || $my_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        $result = mg_update_relation_side($cr_id, $my_ch_id, $label, $memo, $color);
        echo json_encode($result);
        break;

    // 캐릭터 검색 (자동완성)
    case 'search_character':
        $keyword = trim($_GET['keyword'] ?? '');
        if (mb_strlen($keyword) < 1) {
            echo json_encode(array());
            exit;
        }
        $kw = sql_real_escape_string($keyword);
        $sql = "SELECT ch_id, ch_name, ch_thumb, mb_id
                FROM {$g5['mg_character_table']}
                WHERE ch_state = 'approved'
                AND ch_name LIKE '%{$kw}%'
                ORDER BY ch_name
                LIMIT 20";
        $result = sql_query($sql);
        $chars = array();
        while ($row = sql_fetch_array($result)) {
            $chars[] = array(
                'ch_id' => (int)$row['ch_id'],
                'ch_name' => $row['ch_name'],
                'ch_thumb' => $row['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$row['ch_thumb'] : '',
                'mb_id' => $row['mb_id'],
            );
        }
        echo json_encode($chars);
        break;

    // 관계도 배치 저장
    case 'save_layout':
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $layout_raw = stripslashes(trim($_POST['layout'] ?? ''));

        if (!$ch_id || !$layout_raw) {
            echo json_encode(array('success' => false, 'message' => '필수 항목이 없습니다.'));
            exit;
        }

        // 내 캐릭터인지 확인
        $my_char = mg_get_character($ch_id);
        if (!$my_char || $my_char['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        // JSON 유효성 체크
        $decoded = json_decode($layout_raw, true);
        if (!is_array($decoded)) {
            echo json_encode(array('success' => false, 'message' => '잘못된 데이터입니다.'));
            exit;
        }

        // 정규화된 JSON으로 저장
        $layout_clean = json_encode($decoded, JSON_UNESCAPED_UNICODE);
        $layout_esc = sql_real_escape_string($layout_clean);
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_graph_layout = '{$layout_esc}' WHERE ch_id = {$ch_id}");
        echo json_encode(array('success' => true, 'message' => '배치를 저장했습니다.'));
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 요청입니다.'));
}
