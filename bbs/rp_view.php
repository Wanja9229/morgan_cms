<?php
/**
 * Morgan Edition - 역극 보기 → 목록 리다이렉트
 *
 * View 페이지가 List로 통합되었습니다.
 * 기존 알림/북마크 링크 호환을 위해 리다이렉트 처리합니다.
 */

include_once('./_common.php');

$rt_id = isset($_GET['rt_id']) ? (int)$_GET['rt_id'] : 0;
goto_url(G5_BBS_URL . '/rp_list.php' . ($rt_id ? '#rp-thread-' . $rt_id : ''));
