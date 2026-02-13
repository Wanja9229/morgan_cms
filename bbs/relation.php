<?php
/**
 * Morgan Edition - 관계 관리 (리다이렉트)
 * 관계 관리는 캐릭터 수정 페이지의 "관계" 탭으로 이동되었습니다.
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($is_member) {
    // 내 첫 번째 캐릭터의 관계 탭으로 리다이렉트
    $sql = "SELECT ch_id FROM {$g5['mg_character_table']}
            WHERE mb_id = '{$member['mb_id']}' AND ch_state != 'deleted'
            ORDER BY ch_main DESC, ch_id
            LIMIT 1";
    $my_char = sql_fetch($sql);
    if ($my_char['ch_id']) {
        goto_url(G5_BBS_URL.'/character_form.php?ch_id='.$my_char['ch_id'].'&tab=relation');
    }
}
// 캐릭터가 없거나 비로그인 시 캐릭터 관리로
goto_url(G5_BBS_URL.'/character.php');
