<?php
/**
 * Morgan Edition - 포스트잇 API (AJAX)
 *
 * save_position: 카드 위치 저장 (본인 글만)
 * new_panel: 새 판 열기 (관리자 전용)
 */

require_once __DIR__.'/../common.php';
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {

    // ── 카드 위치 저장 ──
    case 'save_position':
        if (!$is_member) {
            echo json_encode(array('error' => '로그인이 필요합니다.'));
            exit;
        }

        $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_POST['bo_table']) : '';
        $wr_id    = (int)($_POST['wr_id'] ?? 0);
        $pos_x    = max(0, min(95, (float)($_POST['pos_x'] ?? 0)));
        $pos_y    = max(0, (float)($_POST['pos_y'] ?? 0));

        if (!$bo_table || !$wr_id) {
            echo json_encode(array('error' => '잘못된 요청입니다.'));
            exit;
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // 글 존재 + 소유권 확인
        $row = sql_fetch("SELECT wr_id, mb_id FROM {$write_table} WHERE wr_id = {$wr_id} AND wr_is_comment = 0");
        if (!$row['wr_id']) {
            echo json_encode(array('error' => '글을 찾을 수 없습니다.'));
            exit;
        }

        // 본인 또는 관리자만 가능
        if ($row['mb_id'] !== $member['mb_id'] && $is_admin !== 'super') {
            echo json_encode(array('error' => '본인 글만 이동할 수 있습니다.'));
            exit;
        }

        sql_query("UPDATE {$write_table} SET wr_2 = '".sql_real_escape_string($pos_x)."', wr_3 = '".sql_real_escape_string($pos_y)."' WHERE wr_id = {$wr_id}");

        echo json_encode(array('success' => true));
        break;

    // ── 새 판 열기 (관리자 전용) ──
    case 'new_panel':
        if ($is_admin !== 'super') {
            echo json_encode(array('error' => '관리자만 사용할 수 있습니다.'));
            exit;
        }

        $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_POST['bo_table']) : '';
        if (!$bo_table) {
            echo json_encode(array('error' => '게시판을 지정해주세요.'));
            exit;
        }

        $board_row = sql_fetch("SELECT bo_table, bo_category_list FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
        if (!$board_row['bo_table']) {
            echo json_encode(array('error' => '존재하지 않는 게시판입니다.'));
            exit;
        }

        // 현재 판 목록 파싱
        $raw = $board_row['bo_category_list'] ?? '';
        $panels = array_values(array_filter(array_map('trim', explode('|', $raw))));

        // 다음 판 번호 계산
        $max_num = 0;
        foreach ($panels as $p) {
            $n = (int)$p;
            if ($n > $max_num) $max_num = $n;
        }
        $new_panel = (string)($max_num + 1);
        $panels[] = $new_panel;

        $new_list = implode('|', $panels);
        sql_query("UPDATE {$g5['board_table']} SET bo_use_category = 1, bo_category_list = '".sql_real_escape_string($new_list)."' WHERE bo_table = '".sql_real_escape_string($bo_table)."'");

        echo json_encode(array('success' => true, 'panel' => $new_panel, 'panels' => $panels));
        break;

    default:
        echo json_encode(array('error' => '알 수 없는 액션입니다.'));
        break;
}
