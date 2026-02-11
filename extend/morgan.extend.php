<?php
/**
 * Morgan Edition - Extend
 *
 * 그누보드 확장 파일 (자동 로드)
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan Edition 플러그인 로드
$morgan_plugin = G5_PLUGIN_PATH.'/morgan/morgan.php';
if (file_exists($morgan_plugin)) {
    include_once($morgan_plugin);
}

/**
 * 글 작성/수정 후 캐릭터 연결 저장
 */
add_event('write_update_after', 'mg_write_update_after', 1);

function mg_write_update_after($board, $wr_id, $w, $qstr, $redirect_url) {
    global $member, $is_member;

    // 로그인 회원만
    if (!$is_member) return;

    $bo_table = $board['bo_table'];

    // 캐릭터 ID 확인
    $ch_id = isset($_POST['mg_ch_id']) ? (int)$_POST['mg_ch_id'] : 0;

    // 캐릭터 연결 저장
    mg_set_write_character($bo_table, $wr_id, $ch_id);

    // 프롬프트 미션 엔트리 생성 (신규 작성 시에만)
    if ($w == '' && function_exists('mg_prompt_after_write') && mg_config('prompt_enable', '1') == '1') {
        $wr = sql_fetch("SELECT wr_content FROM g5_write_{$bo_table} WHERE wr_id = {$wr_id}");
        $wr_content = isset($wr['wr_content']) ? $wr['wr_content'] : '';
        mg_prompt_after_write($bo_table, $wr_id, $member['mb_id'], $wr_content);
    }
}

/**
 * 댓글 작성/수정 후 캐릭터 연결 저장
 */
add_event('comment_update_after', 'mg_comment_update_after', 1);

function mg_comment_update_after($bo_table, $wr_id, $w, $comment_id, $member) {
    global $is_member;

    // 로그인 회원만
    if (!$is_member) return;

    // 캐릭터 ID 확인
    $ch_id = isset($_POST['mg_ch_id']) ? (int)$_POST['mg_ch_id'] : 0;

    // 신규 댓글의 경우 comment_id가 새로 생성된 댓글의 wr_id
    // 기존 댓글 수정의 경우 comment_id가 수정 대상 wr_id
    $target_wr_id = $comment_id;

    // 캐릭터 연결 저장
    mg_set_write_character($bo_table, $target_wr_id, $ch_id);
}
