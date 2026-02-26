<?php
/**
 * Morgan Edition - 파견지 관리 처리
 */

$sub_menu = "801110";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// UI 모드 AJAX 전환
if ($action === 'set_ui_mode') {
    header('Content-Type: application/json; charset=utf-8');
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'list';
    if (!in_array($mode, array('list', 'map'))) $mode = 'list';
    mg_set_config('expedition_ui_mode', $mode);
    echo json_encode(array('success' => true, 'mode' => $mode));
    exit;
}

// 파견 지도 이미지 업로드
if ($action === 'upload_map_image') {
    $upload_dir = G5_DATA_PATH.'/expedition';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
        @chmod($upload_dir, 0755);
    }

    if (!isset($_FILES['map_image_file']) || $_FILES['map_image_file']['error'] !== UPLOAD_ERR_OK) {
        alert('이미지 파일을 선택해주세요.');
    }

    $file = $_FILES['map_image_file'];
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        alert('허용되지 않는 파일 형식입니다. (JPG, PNG, GIF, WebP)');
    }
    if ($file['size'] > 20 * 1024 * 1024) {
        alert('파일 크기가 20MB를 초과합니다.');
    }

    // 기존 이미지 삭제
    $old_map = mg_config('expedition_map_image', '');
    if ($old_map) {
        $old_file = G5_PATH . $old_map;
        if (file_exists($old_file)) @unlink($old_file);
    }

    $new_filename = 'expedition_map_' . date('Ymd_His') . '.' . $ext;
    $target_path = $upload_dir . '/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        @chmod($target_path, 0644);
        $new_url = '/data/expedition/' . $new_filename;
        mg_set_config('expedition_map_image', $new_url);
        alert('파견 지도 이미지가 등록되었습니다.', G5_ADMIN_URL.'/morgan/expedition_area.php');
    } else {
        alert('파일 업로드에 실패했습니다.');
    }
    exit; // 안전장치
}

// 파견 지도 이미지 삭제 (AJAX)
if ($action === 'delete_map_image') {
    header('Content-Type: application/json; charset=utf-8');
    $old_map = mg_config('expedition_map_image', '');
    if ($old_map) {
        $old_file = G5_PATH . $old_map;
        if (file_exists($old_file)) @unlink($old_file);
    }
    mg_set_config('expedition_map_image', '');
    mg_set_config('expedition_ui_mode', 'list');
    echo json_encode(array('success' => true));
    exit;
}

// 좌표만 업데이트 (AJAX — 맵에서 기존 파견지 배치)
if ($action === 'set_coords') {
    header('Content-Type: application/json; charset=utf-8');
    $ea_id = isset($_POST['ea_id']) ? (int)$_POST['ea_id'] : 0;
    $ea_map_x = isset($_POST['ea_map_x']) && $_POST['ea_map_x'] !== '' ? (float)$_POST['ea_map_x'] : null;
    $ea_map_y = isset($_POST['ea_map_y']) && $_POST['ea_map_y'] !== '' ? (float)$_POST['ea_map_y'] : null;

    if (!$ea_id) {
        echo json_encode(array('success' => false, 'message' => '파견지를 선택해주세요.'));
        exit;
    }

    $x_val = $ea_map_x !== null ? $ea_map_x : 'NULL';
    $y_val = $ea_map_y !== null ? $ea_map_y : 'NULL';
    sql_query("UPDATE {$g5['mg_expedition_area_table']} SET ea_map_x = {$x_val}, ea_map_y = {$y_val} WHERE ea_id = {$ea_id}");

    // 업데이트된 데이터 반환
    $row = sql_fetch("SELECT ea_id, ea_name, ea_status, ea_map_x, ea_map_y FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id}");
    echo json_encode(array('success' => true, 'area' => $row));
    exit;
}

$w = isset($_POST['w']) ? $_POST['w'] : '';
$ea_id = isset($_POST['ea_id']) ? (int)$_POST['ea_id'] : 0;

$redirect_url = G5_ADMIN_URL.'/morgan/expedition_area.php';

global $g5;

// 삭제
if ($w === 'd' && $ea_id > 0) {
    // 이미지 파일도 함께 삭제
    $del_row = sql_fetch("SELECT ea_image FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id}");
    if (!empty($del_row['ea_image'])) {
        $del_file = G5_PATH . $del_row['ea_image'];
        if (file_exists($del_file)) @unlink($del_file);
    }
    sql_query("DELETE FROM {$g5['mg_expedition_drop_table']} WHERE ea_id = {$ea_id}");
    sql_query("DELETE FROM {$g5['mg_expedition_event_area_table']} WHERE ea_id = {$ea_id}");
    sql_query("DELETE FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id}");
    alert('삭제되었습니다.', $redirect_url);
}

// 파라미터
$ea_name = isset($_POST['ea_name']) ? clean_xss_tags($_POST['ea_name']) : '';
$ea_desc = isset($_POST['ea_desc']) ? clean_xss_tags($_POST['ea_desc']) : '';
$ea_icon = isset($_POST['ea_icon']) ? clean_xss_tags($_POST['ea_icon']) : '';
$ea_stamina_cost = isset($_POST['ea_stamina_cost']) ? max(1, (int)$_POST['ea_stamina_cost']) : 2;
$ea_duration = isset($_POST['ea_duration']) ? max(1, (int)$_POST['ea_duration']) : 60;
$ea_status = isset($_POST['ea_status']) ? clean_xss_tags($_POST['ea_status']) : 'active';
$ea_unlock_facility = isset($_POST['ea_unlock_facility']) ? (int)$_POST['ea_unlock_facility'] : 0;
$ea_partner_point = isset($_POST['ea_partner_point']) ? max(0, (int)$_POST['ea_partner_point']) : 10;
$ea_point_min = isset($_POST['ea_point_min']) ? max(0, (int)$_POST['ea_point_min']) : 0;
$ea_point_max = isset($_POST['ea_point_max']) ? max(0, (int)$_POST['ea_point_max']) : 0;
if ($ea_point_max < $ea_point_min) $ea_point_max = $ea_point_min;
$ea_order = isset($_POST['ea_order']) ? (int)$_POST['ea_order'] : 0;
$ea_map_x = isset($_POST['ea_map_x']) && $_POST['ea_map_x'] !== '' ? (float)$_POST['ea_map_x'] : null;
$ea_map_y = isset($_POST['ea_map_y']) && $_POST['ea_map_y'] !== '' ? (float)$_POST['ea_map_y'] : null;
$del_icon = isset($_POST['del_ea_icon']);

if (empty($ea_name)) {
    alert('파견지명을 입력해주세요.');
}

if (!in_array($ea_status, array('active', 'hidden', 'locked'))) {
    $ea_status = 'active';
}

// 아이콘 처리 (Heroicons명 or 이미지 업로드)
$old_icon = '';
if ($w === 'u' && $ea_id > 0) {
    $old_row = sql_fetch("SELECT ea_icon FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id}");
    $old_icon = $old_row['ea_icon'] ?? '';
}

if ($del_icon && $old_icon && strpos($old_icon, '/') !== false) {
    $old_file = G5_PATH . $old_icon;
    if (file_exists($old_file)) @unlink($old_file);
    $ea_icon = '';
}

$icon_uploaded = mg_handle_icon_upload('ea_icon_file', 'expedition', 'ea_icon');
if ($icon_uploaded) {
    if ($old_icon && strpos($old_icon, '/') !== false) {
        $old_file = G5_PATH . $old_icon;
        if (file_exists($old_file)) @unlink($old_file);
    }
    $ea_icon = $icon_uploaded;
} elseif (!$del_icon && empty($ea_icon) && $old_icon) {
    $ea_icon = $old_icon;
}

$ea_name_esc = sql_real_escape_string($ea_name);
$ea_desc_esc = sql_real_escape_string($ea_desc);
$ea_icon_esc = sql_real_escape_string($ea_icon);
$ea_status_esc = sql_real_escape_string($ea_status);
$ea_unlock_val = $ea_unlock_facility > 0 ? $ea_unlock_facility : 'NULL';
$ea_map_x_val = $ea_map_x !== null ? $ea_map_x : 'NULL';
$ea_map_y_val = $ea_map_y !== null ? $ea_map_y : 'NULL';

if ($w === 'u' && $ea_id > 0) {
    sql_query("UPDATE {$g5['mg_expedition_area_table']} SET
               ea_name = '{$ea_name_esc}',
               ea_desc = '{$ea_desc_esc}',
               ea_icon = '{$ea_icon_esc}',
               ea_stamina_cost = {$ea_stamina_cost},
               ea_duration = {$ea_duration},
               ea_status = '{$ea_status_esc}',
               ea_unlock_facility = {$ea_unlock_val},
               ea_partner_point = {$ea_partner_point},
               ea_point_min = {$ea_point_min},
               ea_point_max = {$ea_point_max},
               ea_order = {$ea_order},
               ea_map_x = {$ea_map_x_val},
               ea_map_y = {$ea_map_y_val}
               WHERE ea_id = {$ea_id}");
} else {
    sql_query("INSERT INTO {$g5['mg_expedition_area_table']}
               (ea_name, ea_desc, ea_icon, ea_stamina_cost, ea_duration, ea_status, ea_unlock_facility, ea_partner_point, ea_point_min, ea_point_max, ea_order, ea_map_x, ea_map_y)
               VALUES ('{$ea_name_esc}', '{$ea_desc_esc}', '{$ea_icon_esc}', {$ea_stamina_cost}, {$ea_duration},
                       '{$ea_status_esc}', {$ea_unlock_val}, {$ea_partner_point}, {$ea_point_min}, {$ea_point_max}, {$ea_order}, {$ea_map_x_val}, {$ea_map_y_val})");
    $ea_id = sql_insert_id();
}

// --- 파견지 이미지 처리 ---
$upload_dir = G5_DATA_PATH.'/expedition';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
    @chmod($upload_dir, 0755);
}

// 기존 이미지 URL 가져오기
$old_image = '';
if ($ea_id > 0) {
    $img_row = sql_fetch("SELECT ea_image FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id}");
    $old_image = isset($img_row['ea_image']) ? $img_row['ea_image'] : '';
}

$ea_image_action = isset($_POST['ea_image_action']) ? $_POST['ea_image_action'] : '';

if ($ea_image_action === '__DELETE__') {
    // 이미지 삭제
    if ($old_image) {
        $old_file = G5_PATH . $old_image;
        if (file_exists($old_file)) @unlink($old_file);
    }
    sql_query("UPDATE {$g5['mg_expedition_area_table']} SET ea_image = '' WHERE ea_id = {$ea_id}");
}
elseif (isset($_FILES['ea_image_file']) && $_FILES['ea_image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['ea_image_file'];
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed_ext) && $file['size'] <= mg_upload_max_file()) {
        // 기존 파일 삭제
        if ($old_image) {
            $old_file = G5_PATH . $old_image;
            if (file_exists($old_file)) @unlink($old_file);
        }

        $new_filename = 'ea_' . $ea_id . '_' . date('Ymd_His') . '.' . $ext;
        $target_path = $upload_dir . '/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            @chmod($target_path, 0644);
            $new_url = '/data/expedition/' . $new_filename;
            sql_query("UPDATE {$g5['mg_expedition_area_table']} SET ea_image = '".sql_real_escape_string($new_url)."' WHERE ea_id = {$ea_id}");
        }
    }
}

// 드롭 테이블: 기존 삭제 후 재삽입
sql_query("DELETE FROM {$g5['mg_expedition_drop_table']} WHERE ea_id = {$ea_id}");

$drop_mt_ids = isset($_POST['drop_mt_id']) ? $_POST['drop_mt_id'] : array();
$drop_mins = isset($_POST['drop_min']) ? $_POST['drop_min'] : array();
$drop_maxs = isset($_POST['drop_max']) ? $_POST['drop_max'] : array();
$drop_chances = isset($_POST['drop_chance']) ? $_POST['drop_chance'] : array();
$drop_rares = isset($_POST['drop_rare']) ? $_POST['drop_rare'] : array();

foreach ($drop_mt_ids as $i => $mt_id) {
    $mt_id = (int)$mt_id;
    if ($mt_id < 1) continue;

    $min = max(0, (int)($drop_mins[$i] ?? 1));
    $max = max($min, (int)($drop_maxs[$i] ?? 1));
    $chance = max(1, min(100, (int)($drop_chances[$i] ?? 100)));
    $rare = isset($drop_rares[$i]) ? 1 : 0;

    sql_query("INSERT INTO {$g5['mg_expedition_drop_table']}
               (ea_id, mt_id, ed_min, ed_max, ed_chance, ed_is_rare)
               VALUES ({$ea_id}, {$mt_id}, {$min}, {$max}, {$chance}, {$rare})");
}

// 이벤트 매칭: 기존 삭제 후 재삽입
sql_query("DELETE FROM {$g5['mg_expedition_event_area_table']} WHERE ea_id = {$ea_id}");

$evt_ee_ids = isset($_POST['evt_ee_id']) ? $_POST['evt_ee_id'] : array();
$evt_chances = isset($_POST['evt_chance']) ? $_POST['evt_chance'] : array();

foreach ($evt_ee_ids as $i => $evt_ee_id) {
    $evt_ee_id = (int)$evt_ee_id;
    if ($evt_ee_id < 1) continue;

    $evt_chance = max(1, min(100, (int)($evt_chances[$i] ?? 10)));

    sql_query("INSERT INTO {$g5['mg_expedition_event_area_table']}
               (ea_id, ee_id, eea_chance)
               VALUES ({$ea_id}, {$evt_ee_id}, {$evt_chance})");
}

alert(($w === 'u' ? '수정' : '등록').'되었습니다.', $redirect_url);
