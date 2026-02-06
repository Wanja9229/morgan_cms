<?php
/**
 * Morgan Edition - 세력/종족 수정
 */

$sub_menu = '400200';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'w');
check_admin_token();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

$redirect_url = './mg_side_list.php';

switch ($action) {
    case 'save_sides':
        // 기존 세력 수정
        $sides = isset($_POST['side']) ? $_POST['side'] : array();
        foreach ($sides as $side_id => $data) {
            $side_id = (int)$side_id;
            $name = sql_real_escape_string(trim($data['name']));
            $order = (int)($data['order'] ?? 0);
            $use = isset($data['use']) ? 1 : 0;

            if ($name) {
                sql_query("UPDATE {$g5['mg_side_table']} SET side_name = '{$name}', side_order = {$order}, side_use = {$use} WHERE side_id = {$side_id}");
            }
        }

        // 신규 세력
        $new_name = isset($_POST['new_side_name']) ? trim($_POST['new_side_name']) : '';
        if ($new_name) {
            $new_name = sql_real_escape_string($new_name);
            $new_order = (int)($_POST['new_side_order'] ?? 0);
            $new_use = isset($_POST['new_side_use']) ? 1 : 0;
            sql_query("INSERT INTO {$g5['mg_side_table']} (side_name, side_order, side_use) VALUES ('{$new_name}', {$new_order}, {$new_use})");
        }

        alert('세력이 저장되었습니다.', $redirect_url);
        break;

    case 'save_classes':
        // 기존 종족 수정
        $classes = isset($_POST['class']) ? $_POST['class'] : array();
        foreach ($classes as $class_id => $data) {
            $class_id = (int)$class_id;
            $name = sql_real_escape_string(trim($data['name']));
            $order = (int)($data['order'] ?? 0);
            $use = isset($data['use']) ? 1 : 0;

            if ($name) {
                sql_query("UPDATE {$g5['mg_class_table']} SET class_name = '{$name}', class_order = {$order}, class_use = {$use} WHERE class_id = {$class_id}");
            }
        }

        // 신규 종족
        $new_name = isset($_POST['new_class_name']) ? trim($_POST['new_class_name']) : '';
        if ($new_name) {
            $new_name = sql_real_escape_string($new_name);
            $new_order = (int)($_POST['new_class_order'] ?? 0);
            $new_use = isset($_POST['new_class_use']) ? 1 : 0;
            sql_query("INSERT INTO {$g5['mg_class_table']} (class_name, class_order, class_use) VALUES ('{$new_name}', {$new_order}, {$new_use})");
        }

        alert('종족이 저장되었습니다.', $redirect_url);
        break;

    case 'delete':
        $side_id = isset($_GET['side_id']) ? (int)$_GET['side_id'] : 0;
        if ($side_id) {
            // 사용 중인지 확인
            $count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE side_id = {$side_id}")['cnt'];
            if ($count > 0) {
                alert("이 세력을 사용하는 캐릭터가 {$count}개 있어 삭제할 수 없습니다.");
            }
            sql_query("DELETE FROM {$g5['mg_side_table']} WHERE side_id = {$side_id}");
            alert('세력이 삭제되었습니다.', $redirect_url);
        }
        break;

    case 'delete_class':
        $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
        if ($class_id) {
            // 사용 중인지 확인
            $count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE class_id = {$class_id}")['cnt'];
            if ($count > 0) {
                alert("이 종족을 사용하는 캐릭터가 {$count}개 있어 삭제할 수 없습니다.");
            }
            sql_query("DELETE FROM {$g5['mg_class_table']} WHERE class_id = {$class_id}");
            alert('종족이 삭제되었습니다.', $redirect_url);
        }
        break;

    default:
        alert('잘못된 요청입니다.');
}
