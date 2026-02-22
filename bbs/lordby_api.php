<?php
/**
 * Morgan Edition - 로드비 게시판 API (AJAX)
 *
 * comment: 인라인 댓글 등록
 */

require_once __DIR__.'/../common.php';
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {

    // ── 인라인 댓글 등록 ──
    case 'comment':
        $bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', $_POST['bo_table']) : '';
        $wr_parent = (int)($_POST['wr_parent'] ?? 0);
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if (!$bo_table || !$wr_parent) {
            echo json_encode(array('error' => '잘못된 요청입니다.'));
            exit;
        }

        if (!$content) {
            echo json_encode(array('error' => '내용을 입력해주세요.'));
            exit;
        }

        if (mb_strlen($content) > 1000) {
            echo json_encode(array('error' => '댓글은 1000자 이내로 입력해주세요.'));
            exit;
        }

        // 게시판 존재 확인
        $board = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
        if (!$board['bo_table']) {
            echo json_encode(array('error' => '존재하지 않는 게시판입니다.'));
            exit;
        }

        // 댓글 권한 체크
        if ($board['bo_comment_level'] > 1 && !$is_member) {
            echo json_encode(array('error' => '로그인이 필요합니다.'));
            exit;
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // 부모글 존재 확인
        $parent = sql_fetch("SELECT wr_id, wr_num, wr_comment FROM {$write_table} WHERE wr_id = {$wr_parent} AND wr_is_comment = 0");
        if (!$parent['wr_id']) {
            echo json_encode(array('error' => '원글을 찾을 수 없습니다.'));
            exit;
        }

        // 댓글 작성자 정보
        $wr_name = '';
        $mb_id = '';
        $wr_password = '';
        $wr_email = '';

        if ($is_member) {
            $wr_name = $member['mb_nick'];
            $mb_id = $member['mb_id'];
            $wr_email = $member['mb_email'];
        } else {
            $wr_name = isset($_POST['wr_name']) ? strip_tags(trim($_POST['wr_name'])) : '';
            $wr_password = isset($_POST['wr_password']) ? $_POST['wr_password'] : '';
            if (!$wr_name) {
                echo json_encode(array('error' => '이름을 입력해주세요.'));
                exit;
            }
            if (!$wr_password) {
                echo json_encode(array('error' => '비밀번호를 입력해주세요.'));
                exit;
            }
            $wr_password = sql_password($wr_password);
        }

        // 내용 정리
        $content = strip_tags($content);
        $content_esc = sql_real_escape_string($content);
        $wr_name_esc = sql_real_escape_string($wr_name);
        $mb_id_esc = sql_real_escape_string($mb_id);
        $wr_email_esc = sql_real_escape_string($wr_email);
        $wr_password_esc = sql_real_escape_string($wr_password);
        $wr_ip = $_SERVER['REMOTE_ADDR'];
        $datetime = G5_TIME_YMDHIS;

        // 댓글 INSERT
        sql_query("INSERT INTO {$write_table}
            (wr_num, wr_parent, wr_is_comment, wr_comment, wr_subject, wr_content, wr_name, wr_password, wr_email, mb_id, wr_datetime, wr_ip, wr_last, ca_name)
            VALUES
            ({$parent['wr_num']}, {$wr_parent}, 1, 0, '', '{$content_esc}', '{$wr_name_esc}', '{$wr_password_esc}', '{$wr_email_esc}', '{$mb_id_esc}', '{$datetime}', '{$wr_ip}', '{$datetime}', '')
        ");

        $new_cmt_id = sql_insert_id();

        // 부모글 댓글 수 갱신
        $new_count = $parent['wr_comment'] + 1;
        sql_query("UPDATE {$write_table} SET wr_comment = {$new_count}, wr_last = '{$datetime}' WHERE wr_id = {$wr_parent}");

        // 댓글 포인트 지급
        if ($is_member && $board['bo_comment_point']) {
            insert_point($mb_id, $board['bo_comment_point'], '댓글 작성 ('.$board['bo_subject'].')', $bo_table, $new_cmt_id, '댓글');
        }

        echo json_encode(array(
            'success' => true,
            'comment' => array(
                'wr_id' => $new_cmt_id,
                'wr_name' => $wr_name,
                'wr_content' => htmlspecialchars($content),
                'datetime' => date('Y.m.d H:i', strtotime($datetime)),
            ),
        ));
        break;

    default:
        echo json_encode(array('error' => '알 수 없는 액션입니다.'));
        break;
}
