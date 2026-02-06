<?php
/**
 * Morgan Edition - 프로필 필드 처리
 */

$sub_menu = "800300";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$redirect_url = G5_ADMIN_URL.'/morgan/profile_field.php';

/**
 * 콤마 구분 텍스트를 JSON 배열로 변환
 */
function options_to_json($text) {
    if (!$text) return '';
    $parts = array_map('trim', explode(',', $text));
    $parts = array_filter($parts, function($v) { return $v !== ''; });
    if (empty($parts)) return '';
    return json_encode(array_values($parts), JSON_UNESCAPED_UNICODE);
}

// 새 섹션 추가 (빈 필드와 함께)
if (isset($_POST['btn_add_section'])) {
    $category = isset($_POST['new_category']) ? trim(clean_xss_tags($_POST['new_category'])) : '';

    if (!$category) {
        alert('섹션명을 입력해주세요.');
    }

    // 섹션에 기본 필드 하나 추가 (섹션만 따로 저장할 수 없으므로)
    $code = 'pf_'.time().'_'.mt_rand(100, 999);
    sql_query("INSERT INTO {$g5['mg_profile_field_table']} (pf_code, pf_name, pf_type, pf_category, pf_use, pf_order)
               VALUES ('{$code}', '새 필드', 'text', '".sql_real_escape_string($category)."', 1, 0)");

    alert('섹션이 추가되었습니다.', $redirect_url);
}

// 섹션 이름 변경
if (isset($_POST['btn_rename_section'])) {
    $old_category = isset($_POST['old_category']) ? trim(clean_xss_tags($_POST['old_category'])) : '';
    $new_category = isset($_POST['new_category_name']) ? trim(clean_xss_tags($_POST['new_category_name'])) : '';

    if (!$old_category || !$new_category) {
        alert('섹션명을 입력해주세요.');
    }

    sql_query("UPDATE {$g5['mg_profile_field_table']} SET pf_category = '".sql_real_escape_string($new_category)."'
               WHERE pf_category = '".sql_real_escape_string($old_category)."'");

    alert('섹션명이 변경되었습니다.', $redirect_url);
}

// 새 필드 추가
if (isset($_POST['btn_add_field'])) {
    $category = isset($_POST['field_category']) ? trim(clean_xss_tags($_POST['field_category'])) : '기본정보';
    $name = isset($_POST['new_pf_name']) ? trim(clean_xss_tags($_POST['new_pf_name'])) : '';
    $type = isset($_POST['new_pf_type']) ? $_POST['new_pf_type'] : 'text';
    $options_text = isset($_POST['new_pf_options']) ? trim($_POST['new_pf_options']) : '';
    $placeholder = isset($_POST['new_pf_placeholder']) ? trim(clean_xss_tags($_POST['new_pf_placeholder'])) : '';
    $required = isset($_POST['new_pf_required']) ? 1 : 0;
    $use = isset($_POST['new_pf_use']) ? 1 : 0;

    if (!$name) {
        alert('필드명을 입력해주세요.');
    }

    // 코드 자동 생성 (타임스탬프 + 랜덤)
    $code = 'pf_'.time().'_'.mt_rand(100, 999);

    // 순서: 해당 카테고리의 마지막 + 1
    $max_order = sql_fetch("SELECT MAX(pf_order) as max_order FROM {$g5['mg_profile_field_table']} WHERE pf_category = '".sql_real_escape_string($category)."'");
    $order = ($max_order['max_order'] ?? 0) + 1;

    $valid_types = array('text', 'textarea', 'select', 'multiselect', 'url', 'image');
    if (!in_array($type, $valid_types)) {
        $type = 'text';
    }

    // 콤마 구분 옵션을 JSON으로 변환
    $options = options_to_json($options_text);

    sql_query("INSERT INTO {$g5['mg_profile_field_table']}
               (pf_code, pf_name, pf_type, pf_options, pf_placeholder, pf_required, pf_order, pf_category, pf_use)
               VALUES (
                   '".sql_real_escape_string($code)."',
                   '".sql_real_escape_string($name)."',
                   '{$type}',
                   '".sql_real_escape_string($options)."',
                   '".sql_real_escape_string($placeholder)."',
                   {$required},
                   {$order},
                   '".sql_real_escape_string($category)."',
                   {$use}
               )");

    alert('필드가 추가되었습니다.', $redirect_url);
}

// 필드 상세 편집
if (isset($_POST['btn_edit_field'])) {
    $pf_id = (int)$_POST['edit_pf_id'];

    if (!$pf_id) {
        alert('필드를 선택해주세요.');
    }

    $name = isset($_POST['edit_pf_name']) ? trim(clean_xss_tags($_POST['edit_pf_name'])) : '';
    $type = isset($_POST['edit_pf_type']) ? $_POST['edit_pf_type'] : 'text';
    $options_text = isset($_POST['edit_pf_options']) ? trim($_POST['edit_pf_options']) : '';
    $placeholder = isset($_POST['edit_pf_placeholder']) ? trim(clean_xss_tags($_POST['edit_pf_placeholder'])) : '';
    $help = isset($_POST['edit_pf_help']) ? trim(clean_xss_tags($_POST['edit_pf_help'])) : '';
    $category = isset($_POST['edit_pf_category']) ? trim(clean_xss_tags($_POST['edit_pf_category'])) : '기본정보';
    $required = isset($_POST['edit_pf_required']) ? 1 : 0;
    $use = isset($_POST['edit_pf_use']) ? 1 : 0;

    $valid_types = array('text', 'textarea', 'select', 'multiselect', 'url', 'image');
    if (!in_array($type, $valid_types)) {
        $type = 'text';
    }

    // 콤마 구분 옵션을 JSON으로 변환
    $options = options_to_json($options_text);

    sql_query("UPDATE {$g5['mg_profile_field_table']} SET
               pf_name = '".sql_real_escape_string($name)."',
               pf_type = '{$type}',
               pf_options = '".sql_real_escape_string($options)."',
               pf_placeholder = '".sql_real_escape_string($placeholder)."',
               pf_help = '".sql_real_escape_string($help)."',
               pf_category = '".sql_real_escape_string($category)."',
               pf_required = {$required},
               pf_use = {$use}
               WHERE pf_id = {$pf_id}");

    alert('필드가 수정되었습니다.', $redirect_url);
}

// 일괄 저장
if (isset($_POST['btn_save'])) {
    $pf_ids = isset($_POST['pf_id']) ? $_POST['pf_id'] : array();
    $pf_orders = isset($_POST['pf_order']) ? $_POST['pf_order'] : array();
    $pf_names = isset($_POST['pf_name']) ? $_POST['pf_name'] : array();
    $pf_types = isset($_POST['pf_type']) ? $_POST['pf_type'] : array();
    $pf_options_arr = isset($_POST['pf_options']) ? $_POST['pf_options'] : array();
    $pf_requireds = isset($_POST['pf_required']) ? $_POST['pf_required'] : array();
    $pf_uses = isset($_POST['pf_use']) ? $_POST['pf_use'] : array();

    foreach ($pf_ids as $i => $pf_id) {
        $pf_id = (int)$pf_id;
        if (!$pf_id) continue;

        $order = (int)($pf_orders[$i] ?? 0);
        $name = trim(clean_xss_tags($pf_names[$i] ?? ''));
        $type = $pf_types[$i] ?? 'text';
        $options_text = trim($pf_options_arr[$i] ?? '');
        $required = isset($pf_requireds[$pf_id]) ? 1 : 0;
        $use = isset($pf_uses[$pf_id]) ? 1 : 0;

        $valid_types = array('text', 'textarea', 'select', 'multiselect', 'url', 'image');
        if (!in_array($type, $valid_types)) {
            $type = 'text';
        }

        // 콤마 구분 옵션을 JSON으로 변환
        $options = options_to_json($options_text);

        sql_query("UPDATE {$g5['mg_profile_field_table']} SET
                   pf_order = {$order},
                   pf_name = '".sql_real_escape_string($name)."',
                   pf_type = '{$type}',
                   pf_options = '".sql_real_escape_string($options)."',
                   pf_required = {$required},
                   pf_use = {$use}
                   WHERE pf_id = {$pf_id}");
    }

    alert('저장되었습니다.', $redirect_url);
}

// 선택 삭제
if (isset($_POST['btn_delete'])) {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (empty($chk)) {
        alert('삭제할 필드를 선택해주세요.');
    }

    foreach ($chk as $pf_id) {
        $pf_id = (int)$pf_id;
        if (!$pf_id) continue;

        // 해당 필드의 값들도 삭제
        sql_query("DELETE FROM {$g5['mg_profile_value_table']} WHERE pf_id = {$pf_id}");
        sql_query("DELETE FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id}");
    }

    alert(count($chk).'개 필드가 삭제되었습니다.', $redirect_url);
}

alert('잘못된 접근입니다.', $redirect_url);
