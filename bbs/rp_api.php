<?php
/**
 * Morgan Edition - 역극 API (AJAX)
 *
 * GET  ?action=replies&rt_id=X&ch_id=Y  - 캐릭터별 이음 목록
 * GET  ?action=members&rt_id=X           - 참여자 목록
 * POST ?action=edit_reply                - 이음 수정
 * POST ?action=delete_reply              - 이음 삭제
 * POST ?action=delete_thread             - 판 삭제 (관리자)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

// Morgan: 개척 시스템 해금 체크
if (function_exists('mg_is_board_unlocked') && !mg_is_board_unlocked('roleplay')) {
    echo json_encode(array('success' => false, 'message' => '역극은 아직 개척되지 않았습니다.'));
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'replies':
        $rt_id = isset($_GET['rt_id']) ? (int)$_GET['rt_id'] : 0;
        $ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

        if (!$rt_id || !$ch_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $replies = mg_get_rp_replies_by_character($rt_id, $ch_id);

        // 이모티콘 렌더링 적용
        foreach ($replies as &$r) {
            $content_html = htmlspecialchars($r['rr_content']);
            $content_html = nl2br($content_html);
            if (function_exists('mg_render_emoticons')) {
                $content_html = mg_render_emoticons($content_html);
            }
            $r['rr_content_html'] = $content_html;
        }
        unset($r);

        echo json_encode(array('success' => true, 'replies' => $replies));
        break;

    case 'members':
        $rt_id = isset($_GET['rt_id']) ? (int)$_GET['rt_id'] : 0;

        if (!$rt_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $members = mg_get_rp_members($rt_id);
        echo json_encode(array('success' => true, 'members' => $members));
        break;

    case 'edit_reply':
        $rr_id = isset($_POST['rr_id']) ? (int)$_POST['rr_id'] : 0;
        $rr_content = isset($_POST['rr_content']) ? trim($_POST['rr_content']) : '';

        if (!$rr_id || !$rr_content) {
            echo json_encode(array('success' => false, 'message' => '필수 항목을 입력해주세요.'));
            exit;
        }

        // 이음 조회 + 권한 체크 (본인 작성분만)
        $reply = sql_fetch("SELECT * FROM {$g5['mg_rp_reply_table']} WHERE rr_id = {$rr_id}");
        if (!$reply['rr_id'] || $reply['mb_id'] !== $member['mb_id']) {
            echo json_encode(array('success' => false, 'message' => '수정 권한이 없습니다.'));
            exit;
        }

        // 최소 글자 수 체크
        $min_len = (int)mg_config('rp_content_min', 20);
        if (mb_strlen(strip_tags($rr_content)) < $min_len) {
            echo json_encode(array('success' => false, 'message' => "내용을 {$min_len}자 이상 입력해주세요."));
            exit;
        }

        sql_query("UPDATE {$g5['mg_rp_reply_table']} SET rr_content = '".sql_real_escape_string($rr_content)."' WHERE rr_id = {$rr_id}");

        // 렌더링된 HTML 반환
        $content_html = htmlspecialchars($rr_content);
        $content_html = nl2br($content_html);
        if (function_exists('mg_render_emoticons')) {
            $content_html = mg_render_emoticons($content_html);
        }

        echo json_encode(array('success' => true, 'message' => '수정되었습니다.', 'rr_content_html' => $content_html));
        break;

    case 'delete_reply':
        $rr_id = isset($_POST['rr_id']) ? (int)$_POST['rr_id'] : 0;

        if (!$rr_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        // 이음 조회
        $reply = sql_fetch("SELECT r.*, t.mb_id as thread_owner FROM {$g5['mg_rp_reply_table']} r
            LEFT JOIN {$g5['mg_rp_thread_table']} t ON r.rt_id = t.rt_id
            WHERE r.rr_id = {$rr_id}");

        if (!$reply['rr_id']) {
            echo json_encode(array('success' => false, 'message' => '존재하지 않는 댓글입니다.'));
            exit;
        }

        // 권한 체크: 본인 작성, 판장, 또는 관리자
        if ($reply['mb_id'] !== $member['mb_id'] && $reply['thread_owner'] !== $member['mb_id'] && $is_admin !== 'super') {
            echo json_encode(array('success' => false, 'message' => '삭제 권한이 없습니다.'));
            exit;
        }

        $rt_id = (int)$reply['rt_id'];
        $ch_id = (int)$reply['ch_id'];

        sql_query("DELETE FROM {$g5['mg_rp_reply_table']} WHERE rr_id = {$rr_id}");

        // 역극 이음수 감소
        sql_query("UPDATE {$g5['mg_rp_thread_table']} SET rt_reply_count = GREATEST(rt_reply_count - 1, 0) WHERE rt_id = {$rt_id}");

        // 참여자 이음수 감소
        sql_query("UPDATE {$g5['mg_rp_member_table']} SET rm_reply_count = GREATEST(rm_reply_count - 1, 0) WHERE rt_id = {$rt_id} AND ch_id = {$ch_id}");

        echo json_encode(array('success' => true, 'message' => '삭제되었습니다.', 'ch_id' => $ch_id));
        break;

    case 'delete_thread':
        if ($is_admin !== 'super') {
            echo json_encode(array('success' => false, 'message' => '관리자만 삭제할 수 있습니다.'));
            exit;
        }

        $rt_id = isset($_POST['rt_id']) ? (int)$_POST['rt_id'] : 0;
        if (!$rt_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            exit;
        }

        $thread = sql_fetch("SELECT * FROM {$g5['mg_rp_thread_table']} WHERE rt_id = {$rt_id}");
        if (!$thread['rt_id']) {
            echo json_encode(array('success' => false, 'message' => '존재하지 않는 역극입니다.'));
            exit;
        }

        // 댓글 삭제
        sql_query("DELETE FROM {$g5['mg_rp_reply_table']} WHERE rt_id = {$rt_id}");
        // 참여자 삭제
        sql_query("DELETE FROM {$g5['mg_rp_member_table']} WHERE rt_id = {$rt_id}");
        // 스레드 삭제
        sql_query("DELETE FROM {$g5['mg_rp_thread_table']} WHERE rt_id = {$rt_id}");

        echo json_encode(array('success' => true, 'message' => '역극이 삭제되었습니다.'));
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
}
