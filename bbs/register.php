<?php
include_once('./_common.php');

// 로그인중인 경우 회원가입 할 수 없습니다.
if ($is_member) {
    goto_url(G5_URL);
}

// 세션을 지웁니다.
set_session("ss_mb_reg", "");

// Morgan: 약관 페이지 스킵, 바로 회원가입 폼으로 이동
// 약관 동의는 register_form.skin.php에서 체크박스로 처리
goto_url(G5_BBS_URL.'/register_form.php?agree=1&agree2=1');