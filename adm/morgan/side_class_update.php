<?php
/**
 * Morgan Edition - 진영/클래스 저장
 */

$sub_menu = "800400";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$type = isset($_POST['type']) ? $_POST['type'] : '';

if ($type == 'side') {
    $table = $g5['mg_side_table'];
    $id_field = 'side_id';
    $name_field = 'side_name';
    $extra_field = 'side_color';
    $extra_post = 'item_color';
} else if ($type == 'class') {
    $table = $g5['mg_class_table'];
    $id_field = 'class_id';
    $name_field = 'class_name';
    $extra_field = 'class_icon';
    $extra_post = 'item_icon';
} else {
    alert('잘못된 접근입니다.');
}

// 삭제 처리
if (isset($_POST['btn_delete']) && isset($_POST['chk'])) {
    foreach ($_POST['chk'] as $item_id) {
        $item_id = (int)$item_id;
        sql_query("DELETE FROM $table WHERE $id_field = $item_id");
    }
    goto_url('./side_class.php');
}

// 기존 항목 업데이트
if (isset($_POST['item_id'])) {
    foreach ($_POST['item_id'] as $i => $item_id) {
        $item_id = (int)$item_id;
        $sort_order = (int)$_POST['sort_order'][$i];
        $item_name = trim($_POST['item_name'][$i]);
        $extra_value = isset($_POST[$extra_post][$i]) ? trim($_POST[$extra_post][$i]) : '';

        $sql = "UPDATE $table SET
                    $name_field = '$item_name',
                    $extra_field = '$extra_value',
                    sort_order = $sort_order
                WHERE $id_field = $item_id";
        sql_query($sql);
    }
}

// 새 항목 추가
if (isset($_POST['new_item_name']) && trim($_POST['new_item_name'])) {
    $new_item_name = trim($_POST['new_item_name']);
    $new_sort_order = (int)$_POST['new_sort_order'];
    $new_extra_value = isset($_POST['new_' . $extra_post]) ? trim($_POST['new_' . $extra_post]) : '';

    $sql = "INSERT INTO $table ($name_field, $extra_field, sort_order)
            VALUES ('$new_item_name', '$new_extra_value', $new_sort_order)";
    sql_query($sql);
}

goto_url('./side_class.php');
