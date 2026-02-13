<?php
/**
 * Morgan Edition - 관계도 (리다이렉트)
 * 관계도는 캐릭터 뷰 페이지의 인라인 관계도로 이동되었습니다.
 */
include_once('./_common.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if ($ch_id) {
    goto_url(G5_BBS_URL.'/character_view.php?ch_id='.$ch_id);
} else {
    goto_url(G5_BBS_URL.'/character_list.php');
}
