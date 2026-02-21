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
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$redirect_url = './side_class.php';

// === AJAX 정렬 요청 처리 ===
if ($mode === 'reorder') {
    $order = isset($_POST['order']) ? $_POST['order'] : array();
    if ($type === 'side') {
        foreach ($order as $i => $id) {
            $id = (int)$id;
            if ($id) sql_query("UPDATE {$g5['mg_side_table']} SET side_order = {$i} WHERE side_id = {$id}");
        }
    } elseif ($type === 'class') {
        foreach ($order as $i => $id) {
            $id = (int)$id;
            if ($id) sql_query("UPDATE {$g5['mg_class_table']} SET class_order = {$i} WHERE class_id = {$id}");
        }
    }
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
    exit;
}

if ($type === 'side') {
    $table = $g5['mg_side_table'];
    $id_field = 'side_id';
    $name_field = 'side_name';
    $icon_field = 'side_image';
    $order_field = 'side_order';
    $use_field = 'side_use';
    $upload_dir = G5_DATA_PATH . '/morgan/side_icons';
    $upload_url = G5_DATA_URL . '/morgan/side_icons';
} elseif ($type === 'class') {
    $table = $g5['mg_class_table'];
    $id_field = 'class_id';
    $name_field = 'class_name';
    $icon_field = 'class_image';
    $order_field = 'class_order';
    $use_field = 'class_use';
    $upload_dir = G5_DATA_PATH . '/morgan/class_icons';
    $upload_url = G5_DATA_URL . '/morgan/class_icons';
} else {
    alert('잘못된 접근입니다.', $redirect_url);
}

/**
 * 아이콘 이미지 파일 삭제
 */
function _delete_icon_file($icon_val) {
    if (!$icon_val) return;
    if (strpos($icon_val, '/') === false && strpos($icon_val, 'http') !== 0) return;
    if (strpos($icon_val, G5_DATA_URL) !== false) {
        $path = G5_DATA_PATH . str_replace(G5_DATA_URL, '', $icon_val);
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

// === 삭제 처리 ===
if (isset($_POST['btn_delete']) && isset($_POST['chk'])) {
    foreach ($_POST['chk'] as $item_id) {
        $item_id = (int)$item_id;
        if (!$item_id) continue;

        // 아이콘 파일 삭제
        $row = sql_fetch("SELECT {$icon_field} FROM {$table} WHERE {$id_field} = {$item_id}");
        if ($row[$icon_field]) {
            _delete_icon_file($row[$icon_field]);
        }

        sql_query("DELETE FROM {$table} WHERE {$id_field} = {$item_id}");
    }
    goto_url($redirect_url);
}

// === 저장 처리 ===
if (isset($_POST['btn_submit'])) {
    // --- 기존 항목 업데이트 ---
    $item_ids = isset($_POST['item_id']) ? $_POST['item_id'] : array();
    $item_orders = isset($_POST['item_order']) ? $_POST['item_order'] : array();
    $item_names = isset($_POST['item_name']) ? $_POST['item_name'] : array();
    $item_icons = isset($_POST['item_icon']) ? $_POST['item_icon'] : array();
    $item_uses = isset($_POST['item_use']) ? $_POST['item_use'] : array();
    $del_icons = isset($_POST['del_icon']) ? $_POST['del_icon'] : array();

    // 클래스 전용: 소속 진영
    $item_side_ids = ($type === 'class' && isset($_POST['item_side_id'])) ? $_POST['item_side_id'] : array();

    foreach ($item_ids as $i => $item_id) {
        $item_id = (int)$item_id;
        if (!$item_id) continue;

        $order = (int)($item_orders[$i] ?? 0);
        $name = sql_real_escape_string(trim($item_names[$i] ?? ''));
        $icon = trim($item_icons[$i] ?? '');
        $use = isset($item_uses[$item_id]) ? 1 : 0;

        // 아이콘 삭제 처리
        if (isset($del_icons[$item_id])) {
            _delete_icon_file($icon);
            $icon = '';
        }

        $icon_escaped = sql_real_escape_string($icon);

        $sql = "UPDATE {$table} SET
                    {$name_field} = '{$name}',
                    {$icon_field} = '{$icon_escaped}',
                    {$order_field} = {$order},
                    {$use_field} = {$use}";

        // 클래스 전용: 소속 진영
        if ($type === 'class' && isset($item_side_ids[$i])) {
            $side_id = (int)$item_side_ids[$i];
            $sql .= ", side_id = {$side_id}";
        }

        $sql .= " WHERE {$id_field} = {$item_id}";
        sql_query($sql);
    }

    // --- 새 항목 추가 ---
    $new_name = isset($_POST['new_item_name']) ? trim($_POST['new_item_name']) : '';
    if ($new_name) {
        $new_order = (int)($_POST['new_item_order'] ?? 0);
        $new_use = isset($_POST['new_item_use']) ? 1 : 0;
        $new_name_escaped = sql_real_escape_string($new_name);

        // 아이콘 처리
        $new_icon = '';
        $icon_type_key = 'new_' . $type . '_icon_type';
        $new_icon_type = isset($_POST[$icon_type_key]) ? $_POST[$icon_type_key] : 'text';

        if ($new_icon_type === 'file' && isset($_FILES['new_item_icon_file']) && $_FILES['new_item_icon_file']['error'] == 0) {
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['new_item_icon_file']['name'], PATHINFO_EXTENSION));
            $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
            if (in_array($ext, $allowed_ext)) {
                $filename = $type . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $filepath = $upload_dir . '/' . $filename;
                if (move_uploaded_file($_FILES['new_item_icon_file']['tmp_name'], $filepath)) {
                    $new_icon = $upload_url . '/' . $filename;
                }
            }
        } else {
            $new_icon = isset($_POST['new_item_icon']) ? trim($_POST['new_item_icon']) : '';
        }

        $new_icon_escaped = sql_real_escape_string($new_icon);

        if ($type === 'class') {
            $new_side_id = (int)($_POST['new_item_side_id'] ?? 0);
            sql_query("INSERT INTO {$table} ({$name_field}, {$icon_field}, side_id, {$order_field}, {$use_field})
                        VALUES ('{$new_name_escaped}', '{$new_icon_escaped}', {$new_side_id}, {$new_order}, {$new_use})");
        } else {
            sql_query("INSERT INTO {$table} ({$name_field}, {$icon_field}, {$order_field}, {$use_field})
                        VALUES ('{$new_name_escaped}', '{$new_icon_escaped}', {$new_order}, {$new_use})");
        }
    }

    goto_url($redirect_url);
}

goto_url($redirect_url);
