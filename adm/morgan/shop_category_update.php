<?php
/**
 * Morgan Edition - 상점 카테고리 저장 처리
 */

$sub_menu = "800800";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 저장 처리
if (isset($_POST['btn_submit'])) {
    // 기존 카테고리 수정
    $sc_ids = isset($_POST['sc_id']) ? $_POST['sc_id'] : array();
    $sc_orders = isset($_POST['sc_order']) ? $_POST['sc_order'] : array();
    $sc_names = isset($_POST['sc_name']) ? $_POST['sc_name'] : array();
    $sc_descs = isset($_POST['sc_desc']) ? $_POST['sc_desc'] : array();
    $sc_icons = isset($_POST['sc_icon']) ? $_POST['sc_icon'] : array();
    $sc_uses = isset($_POST['sc_use']) ? $_POST['sc_use'] : array();
    $del_icons = isset($_POST['del_icon']) ? $_POST['del_icon'] : array();

    foreach ($sc_ids as $i => $sc_id) {
        $sc_id = (int)$sc_id;
        $sc_order = (int)$sc_orders[$i];
        $sc_name = sql_real_escape_string(trim($sc_names[$i]));
        $sc_desc = sql_real_escape_string(trim($sc_descs[$i]));
        $sc_icon = trim($sc_icons[$i]);
        $sc_use = isset($sc_uses[$sc_id]) ? 1 : 0;

        // 아이콘 삭제 처리
        if (isset($del_icons[$sc_id]) && $sc_icon) {
            // 업로드된 이미지인 경우 파일 삭제
            if (strpos($sc_icon, G5_DATA_URL) !== false) {
                $icon_path = G5_DATA_PATH . '/shop/category_icons/' . basename($sc_icon);
                if (file_exists($icon_path)) {
                    unlink($icon_path);
                }
            }
            $sc_icon = '';
        }

        $sc_icon = sql_real_escape_string($sc_icon);

        if ($sc_name) {
            sql_query("UPDATE {$g5['mg_shop_category_table']} SET
                sc_order = {$sc_order},
                sc_name = '{$sc_name}',
                sc_desc = '{$sc_desc}',
                sc_icon = '{$sc_icon}',
                sc_use = {$sc_use}
                WHERE sc_id = {$sc_id}");
        }
    }

    // 새 카테고리 추가
    $new_sc_name = isset($_POST['new_sc_name']) ? trim($_POST['new_sc_name']) : '';
    if ($new_sc_name) {
        // 중복 체크
        $exists = sql_fetch("SELECT sc_id FROM {$g5['mg_shop_category_table']} WHERE sc_name = '" . sql_real_escape_string($new_sc_name) . "'");
        if ($exists['sc_id']) {
            alert('이미 존재하는 카테고리명입니다.');
        }

        $new_sc_order = (int)$_POST['new_sc_order'];
        $new_sc_desc = sql_real_escape_string(trim($_POST['new_sc_desc']));
        $new_sc_use = isset($_POST['new_sc_use']) ? 1 : 0;
        $new_sc_name_escaped = sql_real_escape_string($new_sc_name);

        // 아이콘 처리
        $new_sc_icon = '';
        $new_icon_type = isset($_POST['new_icon_type']) ? $_POST['new_icon_type'] : 'text';

        if ($new_icon_type === 'file' && isset($_FILES['new_sc_icon_file']) && $_FILES['new_sc_icon_file']['error'] == 0) {
            // 파일 업로드
            $upload_dir = G5_DATA_PATH . '/shop/category_icons';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $ext = strtolower(pathinfo($_FILES['new_sc_icon_file']['name'], PATHINFO_EXTENSION));
            $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
            if (in_array($ext, $allowed_ext)) {
                $new_filename = 'cat_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                $upload_path = $upload_dir . '/' . $new_filename;

                if (move_uploaded_file($_FILES['new_sc_icon_file']['tmp_name'], $upload_path)) {
                    $new_sc_icon = G5_DATA_URL . '/shop/category_icons/' . $new_filename;
                }
            }
        } else {
            // 텍스트 입력
            $new_sc_icon = isset($_POST['new_sc_icon']) ? trim($_POST['new_sc_icon']) : '';
        }

        $new_sc_icon = sql_real_escape_string($new_sc_icon);

        sql_query("INSERT INTO {$g5['mg_shop_category_table']}
            (sc_name, sc_desc, sc_icon, sc_order, sc_use) VALUES
            ('{$new_sc_name_escaped}', '{$new_sc_desc}', '{$new_sc_icon}', {$new_sc_order}, {$new_sc_use})");
    }

    goto_url('./shop_category.php');
}

// 삭제 처리
if (isset($_POST['btn_delete'])) {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (count($chk) > 0) {
        $deleted = 0;
        $skipped = 0;

        foreach ($chk as $sc_id) {
            $sc_id = (int)$sc_id;

            // 해당 카테고리의 상품 수 확인
            $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_shop_item_table']} WHERE sc_id = {$sc_id}");

            if ((int)$cnt['cnt'] === 0) {
                // 아이콘 파일 삭제
                $cat = sql_fetch("SELECT sc_icon FROM {$g5['mg_shop_category_table']} WHERE sc_id = {$sc_id}");
                if ($cat['sc_icon'] && strpos($cat['sc_icon'], G5_DATA_URL) !== false) {
                    $icon_path = G5_DATA_PATH . '/shop/category_icons/' . basename($cat['sc_icon']);
                    if (file_exists($icon_path)) {
                        unlink($icon_path);
                    }
                }

                sql_query("DELETE FROM {$g5['mg_shop_category_table']} WHERE sc_id = {$sc_id}");
                $deleted++;
            } else {
                $skipped++;
            }
        }

        if ($skipped > 0) {
            alert("상품이 있는 카테고리 {$skipped}개는 삭제되지 않았습니다.", './shop_category.php');
        }
    }

    goto_url('./shop_category.php');
}

goto_url('./shop_category.php');
