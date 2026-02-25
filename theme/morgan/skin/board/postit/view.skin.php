<?php
/**
 * Morgan Edition - Postit Board View Skin (Renewed)
 *
 * 포스트잇 게시판도 표준 뷰 레이아웃 사용.
 * 익명 게시판이면 목록으로 리다이렉트.
 */

if (!defined('_GNUBOARD_')) exit;

// 익명 게시판이면 목록으로 리다이렉트
if (($board['bo_1'] ?? '') === 'anonymous') {
    goto_url(get_pretty_url($bo_table));
    exit;
}

// 표준 뷰 스킨 사용
include_once(G5_THEME_PATH.'/skin/board/basic/view.skin.php');
