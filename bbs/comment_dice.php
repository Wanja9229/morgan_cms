<?php
/**
 * Morgan Edition - 댓글 주사위 AJAX 엔드포인트
 * POST: bo_table, wr_id → 서버사이드 rand() → 댓글 자동 등록
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

// 로그인 필수
if (!$is_member) {
    echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
    exit;
}

$bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;

if (!$bo_table || !$wr_id) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 게시판 존재 확인
$board = get_board_db($bo_table, true);
if (!$board['bo_table']) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 게시판입니다.']);
    exit;
}

// 댓글 권한 확인
if ($member['mb_level'] < $board['bo_comment_level']) {
    echo json_encode(['success' => false, 'message' => '댓글 작성 권한이 없습니다.']);
    exit;
}

// 주사위 설정 확인
$br = mg_get_board_reward($bo_table);
if (!$br || !$br['br_dice_use']) {
    echo json_encode(['success' => false, 'message' => '이 게시판에서는 주사위를 사용할 수 없습니다.']);
    exit;
}

// 원글 존재 확인
$write_table = $g5['write_prefix'] . $bo_table;
$wr = sql_fetch("SELECT wr_id, wr_num, ca_name FROM {$write_table} WHERE wr_id = {$wr_id} AND wr_is_comment = 0");
if (!$wr['wr_id']) {
    echo json_encode(['success' => false, 'message' => '원글을 찾을 수 없습니다.']);
    exit;
}

// 1인 1회 제한
if ($br['br_dice_once']) {
    $mb_id_esc = sql_real_escape_string($member['mb_id']);
    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$write_table}
                      WHERE wr_parent = {$wr_id} AND wr_is_comment = 1
                      AND wr_1 = 'dice' AND mb_id = '{$mb_id_esc}'");
    if ($cnt['cnt'] > 0) {
        echo json_encode(['success' => false, 'message' => '이 글에서 이미 주사위를 굴렸습니다.']);
        exit;
    }
}

// 주사위 굴리기
$dice_max = (int)$br['br_dice_max'] ?: 100;
$dice_value = rand(0, $dice_max);
$dice_content = sql_real_escape_string('[주사위] ' . $dice_value);

// 댓글 번호 계산
$row = sql_fetch("SELECT MAX(wr_comment) as max_comment FROM {$write_table}
                  WHERE wr_parent = {$wr_id} AND wr_is_comment = 1");
$wr_comment = ((int)$row['max_comment']) + 1;

// 댓글 INSERT
$mb_id_esc = sql_real_escape_string($member['mb_id']);
$mb_nick_esc = sql_real_escape_string($member['mb_nick']);
$mb_email_esc = sql_real_escape_string($member['mb_email']);
$ip = $_SERVER['REMOTE_ADDR'];

$sql = "INSERT INTO {$write_table} SET
        ca_name = '{$wr['ca_name']}',
        wr_option = '',
        wr_num = '{$wr['wr_num']}',
        wr_reply = '',
        wr_parent = {$wr_id},
        wr_is_comment = 1,
        wr_comment = {$wr_comment},
        wr_comment_reply = '',
        wr_subject = '',
        wr_content = '{$dice_content}',
        mb_id = '{$mb_id_esc}',
        wr_password = '',
        wr_name = '{$mb_nick_esc}',
        wr_email = '{$mb_email_esc}',
        wr_homepage = '',
        wr_datetime = '".G5_TIME_YMDHIS."',
        wr_last = '',
        wr_ip = '{$ip}',
        wr_1 = 'dice',
        wr_2 = '{$dice_value}'";
sql_query($sql);

$comment_id = sql_insert_id();

// 원글 댓글수 +1, 마지막 시간 갱신
sql_query("UPDATE {$write_table} SET wr_comment = wr_comment + 1, wr_last = '".G5_TIME_YMDHIS."' WHERE wr_id = {$wr_id}");

// 새글 테이블
sql_query("INSERT INTO {$g5['board_new_table']} (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
           VALUES ('{$bo_table}', {$comment_id}, {$wr_id}, '".G5_TIME_YMDHIS."', '{$mb_id_esc}')");

// 게시판 댓글수 +1
sql_query("UPDATE {$g5['board_table']} SET bo_count_comment = bo_count_comment + 1 WHERE bo_table = '{$bo_table}'");

echo json_encode([
    'success' => true,
    'dice_value' => $dice_value,
    'dice_max' => $dice_max,
    'comment_id' => $comment_id
]);
