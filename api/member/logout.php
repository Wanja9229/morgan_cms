<?php
/**
 * Morgan Edition API - Member Logout
 *
 * POST /api/member/logout
 */

if (!defined('MG_API')) exit;

api_require_method('POST');

// 세션 삭제
set_session('ss_mb_id', '');
set_session('ss_mb_key', '');

// 자동 로그인 쿠키 삭제
setcookie('ck_mb_id', '', G5_SERVER_TIME - 86400, '/');
setcookie('ck_auto', '', G5_SERVER_TIME - 86400, '/');

api_success(null, '로그아웃 성공');
