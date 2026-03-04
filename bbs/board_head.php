<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 게시판 관리의 상단 내용
if (G5_IS_MOBILE) {
    // 모바일의 경우 설정을 따르지 않는다.
    include_once(G5_BBS_PATH.'/_head.php');
    echo run_replace('board_mobile_content_head', html_purifier(stripslashes($board['bo_mobile_content_head'])), $board);
} else {
    if (trim($board['bo_include_head'])) {
        if (is_include_path_check($board['bo_include_head'])) {
            @include ($board['bo_include_head']);
        } else {
            include_once(G5_BBS_PATH.'/_head.php');
        }
    } else {
        // Morgan: head.sub.php는 HTML head만 출력하므로 head.php(헤더/사이드바)도 로드 필요
        include_once(G5_BBS_PATH.'/_head.php');
    }
    echo run_replace('board_content_head', html_purifier(stripslashes($board['bo_content_head'])), $board);
}