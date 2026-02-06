<?php
/**
 * Morgan Edition - 프로필 양식 수정
 */

$sub_menu = '400300';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'w');
check_admin_token();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

$redirect_url = './mg_profile_field_list.php';

switch ($action) {
    case 'save':
        // 일괄 수정
        $fields = isset($_POST['field']) ? $_POST['field'] : array();
        foreach ($fields as $pf_id => $data) {
            $pf_id = (int)$pf_id;
            $name = sql_real_escape_string(trim($data['name'] ?? ''));
            $type = sql_real_escape_string($data['type'] ?? 'text');
            $category = sql_real_escape_string(trim($data['category'] ?? '기본정보'));
            $order = (int)($data['order'] ?? 0);
            $required = isset($data['required']) ? 1 : 0;
            $use = isset($data['use']) ? 1 : 0;

            if ($name) {
                sql_query("UPDATE {$g5['mg_profile_field_table']} SET
                    pf_name = '{$name}',
                    pf_type = '{$type}',
                    pf_category = '{$category}',
                    pf_order = {$order},
                    pf_required = {$required},
                    pf_use = {$use}
                    WHERE pf_id = {$pf_id}");
            }
        }
        alert('저장되었습니다.', $redirect_url);
        break;

    case 'insert':
        $code = preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['pf_code'] ?? ''));
        $name = sql_real_escape_string(trim($_POST['pf_name'] ?? ''));
        $type = sql_real_escape_string($_POST['pf_type'] ?? 'text');
        $options = sql_real_escape_string(trim($_POST['pf_options'] ?? ''));
        $placeholder = sql_real_escape_string(trim($_POST['pf_placeholder'] ?? ''));
        $help = sql_real_escape_string(trim($_POST['pf_help'] ?? ''));
        $category = sql_real_escape_string(trim($_POST['pf_category'] ?? '기본정보'));
        $order = (int)($_POST['pf_order'] ?? 0);
        $required = isset($_POST['pf_required']) ? 1 : 0;
        $use = isset($_POST['pf_use']) ? 1 : 0;

        if (!$code || !$name) {
            alert('코드와 표시명을 입력해주세요.');
        }

        // 중복 체크
        $exists = sql_fetch("SELECT pf_id FROM {$g5['mg_profile_field_table']} WHERE pf_code = '{$code}'");
        if ($exists['pf_id']) {
            alert('이미 존재하는 코드입니다.');
        }

        sql_query("INSERT INTO {$g5['mg_profile_field_table']} (pf_code, pf_name, pf_type, pf_options, pf_placeholder, pf_help, pf_category, pf_order, pf_required, pf_use)
            VALUES ('{$code}', '{$name}', '{$type}', '{$options}', '{$placeholder}', '{$help}', '{$category}', {$order}, {$required}, {$use})");

        alert('추가되었습니다.', $redirect_url);
        break;

    case 'update':
        $pf_id = (int)$_POST['pf_id'];
        $name = sql_real_escape_string(trim($_POST['pf_name'] ?? ''));
        $type = sql_real_escape_string($_POST['pf_type'] ?? 'text');
        $options = sql_real_escape_string(trim($_POST['pf_options'] ?? ''));
        $placeholder = sql_real_escape_string(trim($_POST['pf_placeholder'] ?? ''));
        $help = sql_real_escape_string(trim($_POST['pf_help'] ?? ''));
        $category = sql_real_escape_string(trim($_POST['pf_category'] ?? '기본정보'));
        $order = (int)($_POST['pf_order'] ?? 0);
        $required = isset($_POST['pf_required']) ? 1 : 0;
        $use = isset($_POST['pf_use']) ? 1 : 0;

        if (!$pf_id || !$name) {
            alert('표시명을 입력해주세요.');
        }

        sql_query("UPDATE {$g5['mg_profile_field_table']} SET
            pf_name = '{$name}',
            pf_type = '{$type}',
            pf_options = '{$options}',
            pf_placeholder = '{$placeholder}',
            pf_help = '{$help}',
            pf_category = '{$category}',
            pf_order = {$order},
            pf_required = {$required},
            pf_use = {$use}
            WHERE pf_id = {$pf_id}");

        alert('수정되었습니다.', $redirect_url);
        break;

    case 'delete':
        $pf_id = isset($_GET['pf_id']) ? (int)$_GET['pf_id'] : 0;
        if ($pf_id) {
            // 값이 있는지 확인
            $count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_profile_value_table']} WHERE pf_id = {$pf_id} AND pv_value != ''")['cnt'];
            if ($count > 0) {
                if (!isset($_GET['force'])) {
                    alert("이 항목에 {$count}개의 값이 입력되어 있습니다. 정말 삭제하시겠습니까?\\n\\n삭제하려면 확인을 다시 눌러주세요.", "./mg_profile_field_update.php?action=delete&pf_id={$pf_id}&token=".get_admin_token()."&force=1");
                }
            }
            sql_query("DELETE FROM {$g5['mg_profile_value_table']} WHERE pf_id = {$pf_id}");
            sql_query("DELETE FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id}");
            alert('삭제되었습니다.', $redirect_url);
        }
        break;

    default:
        alert('잘못된 요청입니다.');
}
