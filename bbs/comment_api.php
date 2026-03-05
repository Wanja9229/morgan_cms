<?php
/**
 * Morgan Edition - 댓글 AJAX API
 *
 * action=list   — 댓글 목록 (GET)
 * action=write  — 댓글 등록 (POST)
 * action=edit   — 댓글 수정 (POST)
 * action=delete — 댓글 삭제 (POST)
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$bo_table = isset($_REQUEST['bo_table']) ? trim($_REQUEST['bo_table']) : '';

if (!$bo_table) {
    _cmt_api_error('게시판 정보가 없습니다.');
}

$board = get_board_db($bo_table, true);
if (!$board['bo_table']) {
    _cmt_api_error('존재하지 않는 게시판입니다.');
}

$write_table = $g5['write_prefix'] . $bo_table;

switch ($action) {
    case 'list':
        _cmt_api_list($board, $write_table);
        break;
    case 'write':
        _cmt_api_write($board, $write_table);
        break;
    case 'edit':
        _cmt_api_edit($board, $write_table);
        break;
    case 'delete':
        _cmt_api_delete($board, $write_table);
        break;
    default:
        _cmt_api_error('잘못된 요청입니다.');
}

// ──────────────────────────────────────
// action=list
// ──────────────────────────────────────
function _cmt_api_list($board, $write_table) {
    global $g5, $member, $is_member, $is_admin, $config;

    $bo_table = $board['bo_table'];
    $wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;
    if (!$wr_id) _cmt_api_error('글 번호가 없습니다.');

    // 원글 존재 확인
    $write = sql_fetch("SELECT wr_id, mb_id FROM {$write_table} WHERE wr_id = {$wr_id} AND wr_is_comment = 0");
    if (!$write['wr_id']) _cmt_api_error('원글을 찾을 수 없습니다.');

    // 댓글 조회
    $sql = "SELECT * FROM {$write_table}
            WHERE wr_parent = {$wr_id} AND wr_is_comment = 1
            ORDER BY wr_comment, wr_comment_reply";
    $result = sql_query($sql);

    $cmt_ids = array();
    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
        $cmt_ids[] = (int)$row['wr_id'];
    }

    // 캐릭터 정보 일괄 로드
    $chars = array();
    if ($cmt_ids) {
        $sql = "SELECT wc.wr_id, c.ch_id, c.ch_name, c.ch_thumb
                FROM {$g5['mg_write_character_table']} wc
                JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
                WHERE wc.bo_table = '".sql_real_escape_string($bo_table)."'
                AND wc.wr_id IN (".implode(',', $cmt_ids).")";
        $cr = sql_query($sql);
        while ($crow = sql_fetch_array($cr)) {
            $chars[$crow['wr_id']] = $crow;
        }
    }

    // 주사위 설정
    $dice_enabled = false;
    $dice_max = 100;
    if (function_exists('mg_get_board_reward')) {
        $br = mg_get_board_reward($bo_table);
        if ($br && $br['br_dice_use']) {
            $dice_enabled = true;
            $dice_max = (int)$br['br_dice_max'] ?: 100;
        }
    }

    // 최고 주사위값
    $dice_best_val = -1;
    foreach ($rows as $row) {
        if ($row['wr_1'] === 'dice' && (int)$row['wr_2'] > $dice_best_val) {
            $dice_best_val = (int)$row['wr_2'];
        }
    }

    // 댓글 목록 구성
    $comments = array();
    $is_comment_write = ($is_member && $member['mb_level'] >= $board['bo_comment_level']) || $is_admin;

    foreach ($rows as $i => $row) {
        $is_secret = strpos($row['wr_option'] ?? '', 'secret') !== false;
        $can_view = !$is_secret
            || $is_admin
            || ($is_member && $member['mb_id'] === $write['mb_id'])
            || ($is_member && $member['mb_id'] === $row['mb_id']);

        // 내용 처리
        $content_html = '비밀글입니다.';
        $content_raw = '';
        if ($can_view) {
            $content_raw = $row['wr_content'];
            $rendered = conv_content($row['wr_content'], 0);
            if (function_exists('mg_render_emoticons')) {
                $rendered = mg_render_emoticons($rendered);
            }
            $content_html = $rendered;
        }

        // 주사위
        $is_dice = ($row['wr_1'] === 'dice');
        $dice_value = $is_dice ? (int)$row['wr_2'] : 0;
        $is_best = ($is_dice && $dice_value === $dice_best_val && $dice_best_val > 0);

        // 권한
        $can_edit = false;
        $can_delete = false;
        $del_token = '';
        if ($is_admin || ($is_member && $member['mb_id'] === $row['mb_id'])) {
            $can_edit = true;
            $can_delete = true;
            $del_token = uniqid(time());
            set_session('ss_delete_comment_'.$row['wr_id'].'_token', $del_token);
        }

        // 답글이 있으면 수정/삭제 불가 (관리자 제외)
        if (!$is_admin && isset($rows[$i + 1])) {
            $next = $rows[$i + 1];
            if ($next['wr_comment_reply'] && $next['wr_comment'] == $row['wr_comment']) {
                $parent_reply = substr($next['wr_comment_reply'], 0, strlen($next['wr_comment_reply']) - 1);
                if ($parent_reply === $row['wr_comment_reply']) {
                    $can_edit = false;
                    $can_delete = false;
                }
            }
        }

        // 대댓글 가능 여부
        $can_reply = ($is_comment_write || $is_admin) && strlen($row['wr_comment_reply']) < 5;

        // 캐릭터 정보
        $char = isset($chars[$row['wr_id']]) ? $chars[$row['wr_id']] : null;

        // 닉네임/칭호 HTML
        $name_html = '';
        $nick_html = '';
        if ($char) {
            $name_html = mg_render_title($row['mb_id'], $char['ch_id']) . htmlspecialchars($char['ch_name']);
            $nick_html = mg_render_nickname($row['mb_id'], $row['wr_name'], $char['ch_id'], false);
        } else {
            $name_html = $row['mb_id'] ? mg_render_nickname($row['mb_id'], $row['wr_name']) : htmlspecialchars($row['wr_name']);
        }

        $depth = strlen($row['wr_comment_reply']);

        $comments[] = array(
            'wr_id' => (int)$row['wr_id'],
            'content_html' => $content_html,
            'content_raw' => $content_raw,
            'datetime' => substr($row['wr_datetime'], 2, 14),
            'mb_id' => $row['mb_id'],
            'wr_name' => $row['wr_name'],
            'is_secret' => $is_secret,
            'is_dice' => $is_dice,
            'dice_value' => $dice_value,
            'is_best' => $is_best,
            'depth' => $depth,
            'can_reply' => $can_reply,
            'can_edit' => $can_edit,
            'can_delete' => $can_delete,
            'del_token' => $del_token,
            'char' => $char ? array(
                'ch_id' => (int)$char['ch_id'],
                'ch_name' => $char['ch_name'],
                'ch_thumb' => $char['ch_thumb']
            ) : null,
            'name_html' => $name_html,
            'nick_html' => $nick_html,
        );
    }

    // 내 캐릭터 목록
    $my_chars = array();
    $default_ch_id = 0;
    if ($is_member) {
        $raw_chars = mg_get_usable_characters($member['mb_id']);
        foreach ($raw_chars as $ch) {
            $my_chars[] = array(
                'ch_id' => (int)$ch['ch_id'],
                'ch_name' => $ch['ch_name'],
                'ch_main' => (int)$ch['ch_main']
            );
            if ($ch['ch_main'] && !$default_ch_id) $default_ch_id = (int)$ch['ch_id'];
        }
    }

    // 토큰 생성
    $token = _token();
    set_session('ss_cmt_token_'.$wr_id, $token);

    echo json_encode(array(
        'success' => true,
        'comments' => $comments,
        'total' => count($comments),
        'dice_enabled' => $dice_enabled,
        'dice_max' => $dice_max,
        'my_chars' => $my_chars,
        'default_ch_id' => $default_ch_id,
        'can_write' => $is_comment_write,
        'use_secret' => (bool)$board['bo_use_secret'],
        'token' => $token,
    ));
    exit;
}

// ──────────────────────────────────────
// action=write
// ──────────────────────────────────────
function _cmt_api_write($board, $write_table) {
    global $g5, $member, $is_member, $is_admin, $config;

    if (!$is_member) _cmt_api_error('로그인이 필요합니다.');

    $bo_table = $board['bo_table'];
    $wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;
    $wr_content = isset($_POST['wr_content']) ? trim($_POST['wr_content']) : '';
    $post_token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $mg_ch_id = isset($_POST['mg_ch_id']) ? (int)$_POST['mg_ch_id'] : 0;
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0; // 대댓글 대상
    $wr_secret = isset($_POST['wr_secret']) ? 'secret' : '';

    // 토큰 검증
    $session_token = trim(get_session('ss_cmt_token_'.$wr_id));
    set_session('ss_cmt_token_'.$wr_id, '');
    if (!$post_token || !$session_token || $session_token !== $post_token) {
        _cmt_api_error('토큰이 만료되었습니다. 새로고침 후 다시 시도해주세요.');
    }

    // 권한 체크
    if (!$is_admin && $member['mb_level'] < $board['bo_comment_level']) {
        _cmt_api_error('댓글을 쓸 권한이 없습니다.');
    }

    // 내용 체크
    if ($wr_content === '') {
        _cmt_api_error('댓글 내용을 입력해주세요.');
    }

    // 연속 등록 방지
    if (!$is_admin && isset($_SESSION['ss_datetime']) && $_SESSION['ss_datetime'] >= (G5_SERVER_TIME - $config['cf_delay_sec'])) {
        _cmt_api_error('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');
    }
    set_session('ss_datetime', G5_SERVER_TIME);

    // 원글 확인
    $wr = sql_fetch("SELECT wr_id, wr_num, ca_name, wr_subject, mb_id FROM {$write_table} WHERE wr_id = {$wr_id} AND wr_is_comment = 0");
    if (!$wr['wr_id']) _cmt_api_error('원글을 찾을 수 없습니다.');

    // 댓글 번호 계산
    $reply_array = array();
    $tmp_comment_reply = '';

    if ($comment_id) {
        // 대댓글
        $reply_array = sql_fetch("SELECT wr_id, wr_comment, wr_comment_reply, mb_id FROM {$write_table} WHERE wr_id = {$comment_id}");
        if (!$reply_array['wr_id']) _cmt_api_error('부모 댓글을 찾을 수 없습니다.');

        $tmp_comment = $reply_array['wr_comment'];
        $len = strlen($reply_array['wr_comment_reply']);

        // 마지막 답변 문자 찾기
        $sql = "SELECT MAX(SUBSTRING(wr_comment_reply, ".($len+1).", 1)) as reply_char
                FROM {$write_table}
                WHERE wr_parent = {$wr_id}
                AND wr_comment = {$tmp_comment}
                AND SUBSTRING(wr_comment_reply, 1, {$len}) = '".sql_real_escape_string($reply_array['wr_comment_reply'])."'
                AND LENGTH(wr_comment_reply) = ".($len+1)."
                AND wr_is_comment = 1";
        $row = sql_fetch($sql);

        $reply_char = 'A';
        if (!empty($row['reply_char'])) {
            $reply_char = chr(ord($row['reply_char']) + 1);
        }
        if ($reply_char > 'Z') _cmt_api_error('더 이상 답변할 수 없습니다.');

        $tmp_comment_reply = $reply_array['wr_comment_reply'] . $reply_char;
    } else {
        // 일반 댓글
        $row = sql_fetch("SELECT MAX(wr_comment) as max_comment FROM {$write_table}
                          WHERE wr_parent = {$wr_id} AND wr_is_comment = 1");
        $tmp_comment = ((int)$row['max_comment']) + 1;
    }

    // XSS 방지
    $wr_content_escaped = clean_xss_tags($wr_content, 1);
    $mb_id_esc = sql_real_escape_string($member['mb_id']);
    $mb_nick_esc = sql_real_escape_string($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']);
    $mb_email_esc = sql_real_escape_string($member['mb_email']);
    $ip = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO {$write_table} SET
            ca_name = '".sql_real_escape_string($wr['ca_name'])."',
            wr_option = '{$wr_secret}',
            wr_num = '{$wr['wr_num']}',
            wr_reply = '',
            wr_parent = {$wr_id},
            wr_is_comment = 1,
            wr_comment = {$tmp_comment},
            wr_comment_reply = '".sql_real_escape_string($tmp_comment_reply)."',
            wr_subject = '',
            wr_content = '{$wr_content_escaped}',
            mb_id = '{$mb_id_esc}',
            wr_password = '',
            wr_name = '{$mb_nick_esc}',
            wr_email = '{$mb_email_esc}',
            wr_homepage = '',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '',
            wr_ip = '{$ip}',
            wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
            wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = ''";
    sql_query($sql);
    $new_comment_id = sql_insert_id();

    // 원글 댓글수 + 마지막 시간
    sql_query("UPDATE {$write_table} SET wr_comment = wr_comment + 1, wr_last = '".G5_TIME_YMDHIS."' WHERE wr_id = {$wr_id}");

    // 새글 테이블
    sql_query("INSERT INTO {$g5['board_new_table']} (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
               VALUES ('{$bo_table}', {$new_comment_id}, {$wr_id}, '".G5_TIME_YMDHIS."', '{$mb_id_esc}')");

    // 게시판 댓글수
    sql_query("UPDATE {$g5['board_table']} SET bo_count_comment = bo_count_comment + 1 WHERE bo_table = '{$bo_table}'");

    // 포인트
    insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id}-{$new_comment_id} 댓글쓰기", $bo_table, $new_comment_id, '댓글');

    // 재료 보상
    if (function_exists('mg_apply_board_comment_material') && $member['mb_id']) {
        mg_apply_board_comment_material($member['mb_id'], $bo_table);
    }

    // 알림
    if (function_exists('mg_notify')) {
        $noti_url = get_pretty_url($bo_table, $wr_id) . '#c_' . $new_comment_id;
        $noti_subject = get_text(stripslashes($wr['wr_subject'] ?? ''), 0, 50);

        if (!empty($reply_array['wr_id'])) {
            if ($reply_array['mb_id'] && $reply_array['mb_id'] !== $member['mb_id']) {
                mg_notify($reply_array['mb_id'], 'reply',
                    $member['mb_nick'] . '님이 회원님의 댓글에 답글을 남겼습니다.',
                    $noti_subject, $noti_url);
            }
        } else {
            if ($wr['mb_id'] && $wr['mb_id'] !== $member['mb_id']) {
                mg_notify($wr['mb_id'], 'comment',
                    $member['mb_nick'] . '님이 회원님의 글에 댓글을 남겼습니다.',
                    $noti_subject, $noti_url);
            }
        }
    }

    // 캐릭터 연결
    if (function_exists('mg_set_write_character')) {
        mg_set_write_character($bo_table, $new_comment_id, $mg_ch_id);
    }

    // 이벤트 훅 (extend/morgan.extend.php 등에서 사용)
    // $_POST['mg_ch_id']가 이미 설정되어 있으므로 comment_update_after 훅에서도 캐릭터 연결됨
    // 단, 우리가 이미 처리했으므로 중복 방지를 위해 직접 호출 대신 이벤트만 실행
    // run_event는 comment_update_after 시그니처: $board, $wr_id, $w, $qstr, $redirect_url, $comment_id, $reply_array
    // 여기서는 이벤트 훅의 mg_set_write_character 중복 호출이 문제없음 (DELETE + INSERT 방식이라 멱등)
    run_event('comment_update_after', $board, $wr_id, 'c', '', '', $new_comment_id, $reply_array);

    delete_cache_latest($bo_table);

    echo json_encode(array('success' => true, 'comment_id' => $new_comment_id));
    exit;
}

// ──────────────────────────────────────
// action=edit
// ──────────────────────────────────────
function _cmt_api_edit($board, $write_table) {
    global $g5, $member, $is_member, $is_admin;

    if (!$is_member) _cmt_api_error('로그인이 필요합니다.');

    $bo_table = $board['bo_table'];
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;
    $wr_content = isset($_POST['wr_content']) ? trim($_POST['wr_content']) : '';
    $post_token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $wr_secret = isset($_POST['wr_secret']) ? 'secret' : '';

    if (!$comment_id) _cmt_api_error('댓글 번호가 없습니다.');
    if ($wr_content === '') _cmt_api_error('내용을 입력해주세요.');

    // 토큰 검증
    $session_token = trim(get_session('ss_cmt_token_'.$wr_id));
    set_session('ss_cmt_token_'.$wr_id, '');
    if (!$post_token || !$session_token || $session_token !== $post_token) {
        _cmt_api_error('토큰이 만료되었습니다. 새로고침 후 다시 시도해주세요.');
    }

    $comment = sql_fetch("SELECT wr_id, mb_id, wr_parent, wr_comment, wr_comment_reply FROM {$write_table} WHERE wr_id = {$comment_id} AND wr_is_comment = 1");
    if (!$comment['wr_id']) _cmt_api_error('댓글을 찾을 수 없습니다.');

    // 권한 체크
    if (!$is_admin && (!$is_member || $member['mb_id'] !== $comment['mb_id'])) {
        _cmt_api_error('수정 권한이 없습니다.');
    }

    // 답변댓글 존재 시 수정 불가 (관리자 제외)
    if (!$is_admin) {
        $len = strlen($comment['wr_comment_reply']);
        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$write_table}
                          WHERE wr_comment_reply LIKE '".sql_real_escape_string($comment['wr_comment_reply'])."%'
                          AND wr_id <> {$comment_id}
                          AND wr_parent = {$comment['wr_parent']}
                          AND wr_comment = {$comment['wr_comment']}
                          AND wr_is_comment = 1");
        if ($cnt['cnt']) _cmt_api_error('답변댓글이 있어 수정할 수 없습니다.');
    }

    $wr_content_escaped = clean_xss_tags($wr_content, 1);
    $sql_ip = $is_admin ? '' : ", wr_ip = '{$_SERVER['REMOTE_ADDR']}'";

    sql_query("UPDATE {$write_table}
               SET wr_content = '{$wr_content_escaped}',
                   wr_option = '{$wr_secret}'
                   {$sql_ip}
               WHERE wr_id = {$comment_id}");

    delete_cache_latest($bo_table);

    echo json_encode(array('success' => true));
    exit;
}

// ──────────────────────────────────────
// action=delete
// ──────────────────────────────────────
function _cmt_api_delete($board, $write_table) {
    global $g5, $member, $is_member, $is_admin;

    if (!$is_member) _cmt_api_error('로그인이 필요합니다.');

    $bo_table = $board['bo_table'];
    $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
    $post_token = isset($_POST['token']) ? trim($_POST['token']) : '';

    if (!$comment_id) _cmt_api_error('댓글 번호가 없습니다.');

    // 토큰 검증
    $del_token = get_session('ss_delete_comment_'.$comment_id.'_token');
    set_session('ss_delete_comment_'.$comment_id.'_token', '');
    if (!$post_token || !$del_token || $del_token !== $post_token) {
        _cmt_api_error('토큰 에러로 삭제할 수 없습니다.');
    }

    $comment = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = {$comment_id} AND wr_is_comment = 1");
    if (!$comment['wr_id']) _cmt_api_error('댓글을 찾을 수 없습니다.');

    // 권한 체크
    if (!$is_admin && (!$is_member || $member['mb_id'] !== $comment['mb_id'])) {
        _cmt_api_error('삭제 권한이 없습니다.');
    }

    // 답변댓글 존재 시 삭제 불가 (관리자 제외)
    if (!$is_admin) {
        $len = strlen($comment['wr_comment_reply']);
        $comment_reply = substr($comment['wr_comment_reply'], 0, $len);
        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$write_table}
                          WHERE wr_comment_reply LIKE '".sql_real_escape_string($comment_reply)."%'
                          AND wr_id <> {$comment_id}
                          AND wr_parent = {$comment['wr_parent']}
                          AND wr_comment = {$comment['wr_comment']}
                          AND wr_is_comment = 1");
        if ($cnt['cnt']) _cmt_api_error('답변댓글이 있어 삭제할 수 없습니다.');
    }

    // 포인트 차감
    if (!delete_point($comment['mb_id'], $bo_table, $comment_id, '댓글')) {
        insert_point($comment['mb_id'], $board['bo_comment_point'] * (-1),
            "{$board['bo_subject']} {$comment['wr_parent']}-{$comment_id} 댓글삭제");
    }

    // 삭제
    sql_query("DELETE FROM {$write_table} WHERE wr_id = {$comment_id}");

    // 최근 시간 갱신
    $row = sql_fetch("SELECT MAX(wr_datetime) as wr_last FROM {$write_table} WHERE wr_parent = {$comment['wr_parent']}");
    sql_query("UPDATE {$write_table} SET wr_comment = wr_comment - 1, wr_last = '".($row['wr_last'] ?? G5_TIME_YMDHIS)."' WHERE wr_id = {$comment['wr_parent']}");

    // 게시판 댓글수 감소
    sql_query("UPDATE {$g5['board_table']} SET bo_count_comment = bo_count_comment - 1 WHERE bo_table = '{$bo_table}'");

    // 새글 삭제
    sql_query("DELETE FROM {$g5['board_new_table']} WHERE bo_table = '{$bo_table}' AND wr_id = {$comment_id}");

    // 캐릭터 연결 삭제
    if (isset($g5['mg_write_character_table'])) {
        sql_query("DELETE FROM {$g5['mg_write_character_table']} WHERE bo_table = '{$bo_table}' AND wr_id = {$comment_id}");
    }

    delete_cache_latest($bo_table);
    run_event('bbs_delete_comment', $comment_id, $board);

    echo json_encode(array('success' => true));
    exit;
}

// ──────────────────────────────────────
// 에러 헬퍼
// ──────────────────────────────────────
function _cmt_api_error($msg) {
    echo json_encode(array('success' => false, 'message' => $msg));
    exit;
}
