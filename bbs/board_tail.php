<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 게시판 관리의 하단 파일 경로
if (G5_IS_MOBILE) {
    echo run_replace('board_mobile_content_tail', html_purifier(stripslashes($board['bo_mobile_content_tail'])), $board);
    // 모바일의 경우 설정을 따르지 않는다.
    include_once(G5_BBS_PATH.'/_tail.php');
} else {
    echo run_replace('board_content_tail', html_purifier(stripslashes($board['bo_content_tail'])), $board);
    if (trim($board['bo_include_tail'])) {
        if (is_include_path_check($board['bo_include_tail'])) {
            @include ($board['bo_include_tail']);
        } else {
            include_once(G5_BBS_PATH.'/_tail.php');
        }
    } else {
        // Morgan: tail.php(위젯 사이드바/푸터) 로드 필요
        include_once(G5_BBS_PATH.'/_tail.php');
    }
}