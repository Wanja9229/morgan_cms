<?php
/**
 * Morgan Edition API - Member Me
 *
 * GET /api/member/me - 내 정보 조회
 * PUT /api/member/me - 내 정보 수정
 */

if (!defined('MG_API')) exit;

api_require_method(['GET', 'PUT']);

// 로그인 필수
$member = api_require_login();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 내 정보 조회
    $data = [
        'mb_id' => $member['mb_id'],
        'mb_nick' => $member['mb_nick'],
        'mb_email' => $member['mb_email'],
        'mb_homepage' => $member['mb_homepage'],
        'mb_level' => (int)$member['mb_level'],
        'mb_point' => (int)$member['mb_point'],
        'mb_datetime' => $member['mb_datetime'],
        'mb_today_login' => $member['mb_today_login'],
        'mb_login_ip' => $member['mb_login_ip'],
    ];

    api_success($data, '회원 정보 조회 성공');

} elseif ($method === 'PUT') {
    // 내 정보 수정
    $input = api_get_json_body();

    // 수정 가능한 필드
    $allowed_fields = ['mb_nick', 'mb_email', 'mb_homepage', 'mb_signature', 'mb_profile'];
    $update_data = [];

    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $update_data[$field] = clean_xss_tags($input[$field]);
        }
    }

    if (empty($update_data)) {
        api_error('NO_DATA', '수정할 데이터가 없습니다.', 400);
    }

    // 닉네임 중복 체크
    if (isset($update_data['mb_nick']) && $update_data['mb_nick'] !== $member['mb_nick']) {
        $exists = sql_exists(" SELECT mb_id FROM {$g5['member_table']} WHERE mb_nick = '".sql_real_escape_string($update_data['mb_nick'])."' AND mb_id <> '{$member['mb_id']}' ");
        if ($exists) {
            api_error('DUPLICATE_NICK', '이미 사용 중인 닉네임입니다.', 400);
        }
    }

    // 업데이트 쿼리 생성
    $set_parts = [];
    foreach ($update_data as $key => $value) {
        $set_parts[] = "{$key} = '".sql_real_escape_string($value)."'";
    }

    $sql = " UPDATE {$g5['member_table']} SET ".implode(', ', $set_parts)." WHERE mb_id = '{$member['mb_id']}' ";
    sql_query($sql);

    api_success($update_data, '회원 정보 수정 성공');
}
