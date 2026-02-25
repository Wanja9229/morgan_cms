<?php
/**
 * Morgan Edition - 재료 관리 처리
 */

$sub_menu = "801100";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$w = isset($_POST['w']) ? $_POST['w'] : '';
$mt_id = isset($_POST['mt_id']) ? (int)$_POST['mt_id'] : 0;

$redirect_url = G5_ADMIN_URL.'/morgan/pioneer_material.php';

global $mg;

// 삭제
if ($w === 'd' && $mt_id > 0) {
    // 유저 보유량 삭제
    sql_query("DELETE FROM {$mg['user_material_table']} WHERE mt_id = {$mt_id}");
    // 시설 비용에서 삭제
    sql_query("DELETE FROM {$mg['facility_material_cost_table']} WHERE mt_id = {$mt_id}");
    // 기여 기록에서 삭제
    sql_query("DELETE FROM {$mg['facility_contribution_table']} WHERE mt_id = {$mt_id}");
    // 재료 삭제
    sql_query("DELETE FROM {$mg['material_type_table']} WHERE mt_id = {$mt_id}");

    alert('삭제되었습니다.', $redirect_url);
}

// 재료 지급
if ($w === 'give') {
    $mb_id = isset($_POST['mb_id']) ? clean_xss_tags($_POST['mb_id']) : '';
    $mt_id = isset($_POST['mt_id']) ? (int)$_POST['mt_id'] : 0;
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

    if (empty($mb_id)) {
        alert('회원 ID를 입력해주세요.');
    }
    if ($mt_id < 1 || $amount < 1) {
        alert('재료와 수량을 올바르게 입력해주세요.');
    }

    // 회원 확인
    $member_check = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
    if (!$member_check) {
        alert('존재하지 않는 회원입니다.');
    }

    mg_add_material($mb_id, $mt_id, $amount);

    // 재료 이름
    $mt = sql_fetch("SELECT mt_name FROM {$mg['material_type_table']} WHERE mt_id = {$mt_id}");
    $mt_name = $mt['mt_name'] ?? '재료';

    alert("{$mb_id}님에게 {$mt_name} {$amount}개를 지급했습니다.", $redirect_url);
}

// 스테미나 지급
if ($w === 'stamina') {
    $mb_id = isset($_POST['mb_id']) ? clean_xss_tags($_POST['mb_id']) : '';
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;

    if (empty($mb_id)) {
        alert('회원 ID를 입력해주세요.');
    }
    if ($amount < 1) {
        alert('스테미나 수량을 올바르게 입력해주세요.');
    }

    // 회원 확인
    $member_check = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
    if (!$member_check) {
        alert('존재하지 않는 회원입니다.');
    }

    $mb_id_esc = sql_real_escape_string($mb_id);

    // 스테미나 레코드가 없으면 생성
    mg_get_stamina($mb_id);

    // 스테미나 추가
    sql_query("UPDATE {$mg['user_stamina_table']}
               SET us_current = us_current + {$amount}
               WHERE mb_id = '{$mb_id_esc}'");

    alert("{$mb_id}님에게 스테미나 {$amount}을(를) 지급했습니다.", $redirect_url);
}

// 재료 추가/수정
$mt_code = isset($_POST['mt_code']) ? clean_xss_tags($_POST['mt_code']) : '';
$mt_name = isset($_POST['mt_name']) ? clean_xss_tags($_POST['mt_name']) : '';
$mt_icon = isset($_POST['mt_icon']) ? clean_xss_tags($_POST['mt_icon']) : '';
$mt_desc = isset($_POST['mt_desc']) ? clean_xss_tags($_POST['mt_desc']) : '';
$mt_order = isset($_POST['mt_order']) ? (int)$_POST['mt_order'] : 0;
$del_icon = isset($_POST['del_icon']) ? true : false;

if (empty($mt_code) || empty($mt_name)) {
    alert('코드와 이름을 입력해주세요.');
}

// 코드 형식 검증
if (!preg_match('/^[a-z_]+$/', $mt_code)) {
    alert('코드는 영문 소문자와 언더스코어만 사용할 수 있습니다.');
}

// 아이콘 이미지 업로드 처리
$icon_upload_path = G5_DATA_PATH.'/morgan/material';
$icon_upload_url = G5_DATA_URL.'/morgan/material';

// 업로드 디렉토리 생성
if (!is_dir($icon_upload_path)) {
    @mkdir($icon_upload_path, 0755, true);
}

// 기존 아이콘 값 가져오기 (수정 시)
$old_icon = '';
if ($w === 'u' && $mt_id > 0) {
    $old_material = sql_fetch("SELECT mt_icon FROM {$mg['material_type_table']} WHERE mt_id = {$mt_id}");
    $old_icon = $old_material['mt_icon'] ?? '';
}

// 아이콘 삭제 체크
if ($del_icon && $old_icon && strpos($old_icon, '/') !== false) {
    // 기존 이미지 파일 삭제
    $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_icon);
    if (file_exists($old_file)) {
        @unlink($old_file);
    }
    $mt_icon = '';
}

// 새 이미지 업로드
if (isset($_FILES['mt_icon_file']) && $_FILES['mt_icon_file']['tmp_name']) {
    $file = $_FILES['mt_icon_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'svg', 'webp');

    if (in_array($ext, $allowed)) {
        // 기존 이미지 삭제
        if ($old_icon && strpos($old_icon, '/') !== false) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_icon);
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        $new_filename = 'mt_'.($mt_id ?: time()).'_'.time().'.'.$ext;
        $new_filepath = $icon_upload_path.'/'.$new_filename;

        if (move_uploaded_file($file['tmp_name'], $new_filepath)) {
            $mt_icon = $icon_upload_url.'/'.$new_filename;
        }
    }
} elseif (!$del_icon && empty($mt_icon) && $old_icon) {
    // 새 업로드 없고 삭제도 안 했으면 기존 값 유지
    $mt_icon = $old_icon;
}

$mt_code_esc = sql_real_escape_string($mt_code);
$mt_name_esc = sql_real_escape_string($mt_name);
$mt_icon_esc = sql_real_escape_string($mt_icon);
$mt_desc_esc = sql_real_escape_string($mt_desc);

if ($w === 'u' && $mt_id > 0) {
    // 수정 (코드는 변경 불가)
    sql_query("UPDATE {$mg['material_type_table']} SET
               mt_name = '{$mt_name_esc}',
               mt_icon = '{$mt_icon_esc}',
               mt_desc = '{$mt_desc_esc}',
               mt_order = {$mt_order}
               WHERE mt_id = {$mt_id}");

    alert('수정되었습니다.', $redirect_url);

} else {
    // 추가 - 코드 중복 확인
    $exists = sql_fetch("SELECT mt_id FROM {$mg['material_type_table']} WHERE mt_code = '{$mt_code_esc}'");
    if ($exists) {
        alert('이미 존재하는 코드입니다.');
    }

    sql_query("INSERT INTO {$mg['material_type_table']}
               (mt_code, mt_name, mt_icon, mt_desc, mt_order)
               VALUES ('{$mt_code_esc}', '{$mt_name_esc}', '{$mt_icon_esc}', '{$mt_desc_esc}', {$mt_order})");

    alert('등록되었습니다.', $redirect_url);
}
