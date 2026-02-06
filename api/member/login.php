<?php
/**
 * Morgan Edition API - Member Login
 *
 * POST /api/member/login
 */

if (!defined('MG_API')) exit;

api_require_method('POST');

// 이미 로그인된 경우
if ($is_member) {
    api_success([
        'mb_id' => $member['mb_id'],
        'mb_nick' => $member['mb_nick'],
    ], '이미 로그인되어 있습니다.');
}

$input = api_get_json_body();

$mb_id = isset($input['mb_id']) ? trim($input['mb_id']) : '';
$mb_password = isset($input['mb_password']) ? $input['mb_password'] : '';
$auto_login = isset($input['auto_login']) ? (bool)$input['auto_login'] : false;

// 필수값 체크
if (empty($mb_id)) {
    api_error('REQUIRED_ID', '아이디를 입력해주세요.', 400);
}

if (empty($mb_password)) {
    api_error('REQUIRED_PASSWORD', '비밀번호를 입력해주세요.', 400);
}

// 회원 조회
$sql = " SELECT * FROM {$g5['member_table']}
         WHERE mb_id = '".sql_real_escape_string($mb_id)."' ";
$mb = sql_fetch($sql);

if (!$mb['mb_id']) {
    api_error('INVALID_CREDENTIALS', '아이디 또는 비밀번호가 일치하지 않습니다.', 401);
}

// 비밀번호 확인
if (!check_password($mb_password, $mb['mb_password'])) {
    api_error('INVALID_CREDENTIALS', '아이디 또는 비밀번호가 일치하지 않습니다.', 401);
}

// 차단 회원 체크
if ($mb['mb_intercept_date'] && $mb['mb_intercept_date'] <= date('Ymd', G5_SERVER_TIME)) {
    api_error('BLOCKED_MEMBER', '접근이 차단된 회원입니다.', 403);
}

// 탈퇴 회원 체크
if ($mb['mb_leave_date']) {
    api_error('LEFT_MEMBER', '탈퇴한 회원입니다.', 403);
}

// 메일 인증 체크 (설정된 경우)
if ($config['cf_use_email_certify'] && !preg_match('/[1-9]/', $mb['mb_email_certify'])) {
    api_error('EMAIL_NOT_VERIFIED', '메일인증을 받지 않은 회원입니다.', 403);
}

// 로그인 처리
set_session('ss_mb_id', $mb['mb_id']);
set_session('ss_mb_key', md5($mb['mb_datetime'].$mb['mb_ip'].$mb['mb_password']));

// 자동 로그인 쿠키 설정
if ($auto_login) {
    $key = md5($mb['mb_datetime'].$mb['mb_ip'].$mb['mb_password'].G5_TOKEN_ENCRYPTION_KEY);
    setcookie('ck_mb_id', $mb['mb_id'], G5_SERVER_TIME + 86400 * 30, '/');
    setcookie('ck_auto', $key, G5_SERVER_TIME + 86400 * 30, '/');
}

// 로그인 시간 업데이트
$sql = " UPDATE {$g5['member_table']}
         SET mb_today_login = '".G5_TIME_YMDHIS."',
             mb_login_ip = '{$_SERVER['REMOTE_ADDR']}'
         WHERE mb_id = '{$mb['mb_id']}' ";
sql_query($sql);

api_success([
    'mb_id' => $mb['mb_id'],
    'mb_nick' => $mb['mb_nick'],
    'mb_level' => (int)$mb['mb_level'],
    'mb_point' => (int)$mb['mb_point'],
], '로그인 성공');
