<?php
/**
 * Morgan Edition - 세계관 지도 설정/지역 저장 처리
 */

$sub_menu = '800178';
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    goto_url('./lore_map.php');
}

$mode = isset($_POST['mode']) ? $_POST['mode'] : 'settings';

// 업로드 디렉토리
$upload_dir = G5_DATA_PATH.'/morgan';
$upload_url = G5_DATA_URL.'/morgan';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
    @chmod($upload_dir, 0755);
}

// ==========================================
// 지도 설정 저장
// ==========================================
if ($mode == 'settings') {
    // 페이지 설명
    if (isset($_POST['lore_map_desc'])) {
        mg_set_config('lore_map_desc', trim($_POST['lore_map_desc']));
    }

    // 맵 이미지 처리
    $old_map_image = mg_config('expedition_map_image', '');
    $map_image_action = isset($_POST['map_image_action']) ? $_POST['map_image_action'] : '';

    if ($map_image_action === '__DELETE__') {
        if ($old_map_image) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_map_image);
            if (file_exists($old_file)) @unlink($old_file);
        }
        mg_set_config('expedition_map_image', '');
    }
    elseif (isset($_FILES['map_image_file']) && $_FILES['map_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['map_image_file'];
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext) && $file['size'] <= 10 * 1024 * 1024) {
            if ($old_map_image) {
                $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_map_image);
                if (file_exists($old_file)) @unlink($old_file);
            }

            $new_filename = 'expedition_map_' . date('Ymd_His') . '.jpg';
            $target_path = $upload_dir . '/' . $new_filename;

            // 리사이즈 (최대 2560px)
            $resized = false;
            $info = @getimagesize($file['tmp_name']);
            if ($info) {
                $width = $info[0];
                $height = $info[1];
                $type = $info[2];

                $src_img = null;
                switch ($type) {
                    case IMAGETYPE_JPEG: $src_img = @imagecreatefromjpeg($file['tmp_name']); break;
                    case IMAGETYPE_PNG:  $src_img = @imagecreatefrompng($file['tmp_name']); break;
                    case IMAGETYPE_GIF:  $src_img = @imagecreatefromgif($file['tmp_name']); break;
                    case IMAGETYPE_WEBP: $src_img = @imagecreatefromwebp($file['tmp_name']); break;
                }

                if ($src_img) {
                    $max_w = 2560;
                    if ($width > $max_w) {
                        $new_w = $max_w;
                        $new_h = (int)($height * ($max_w / $width));
                        $dst = imagecreatetruecolor($new_w, $new_h);
                        imagecopyresampled($dst, $src_img, 0, 0, 0, 0, $new_w, $new_h, $width, $height);
                        imagedestroy($src_img);
                        $src_img = $dst;
                    }
                    $resized = imagejpeg($src_img, $target_path, 90);
                    imagedestroy($src_img);
                }
            }

            if ($resized) {
                @chmod($target_path, 0644);
                $new_url = $upload_url . '/' . $new_filename;
                mg_set_config('expedition_map_image', $new_url);
            }
        }
    }

    goto_url('./lore_map.php?tab=settings');
}

// ==========================================
// 지역 추가
// ==========================================
if ($mode == 'region_add') {
    $mr_name = sql_real_escape_string(trim($_POST['mr_name'] ?? ''));
    $mr_desc = sql_real_escape_string(trim($_POST['mr_desc'] ?? ''));
    $mr_use = isset($_POST['mr_use']) ? 1 : 0;

    if (!$mr_name) {
        alert('지역명을 입력해주세요.', './lore_map.php?tab=regions');
    }

    // 순서: 마지막에 추가
    $last = sql_fetch("SELECT MAX(mr_order) as mx FROM {$g5['mg_map_region_table']}");
    $mr_order = ((int)($last['mx'] ?? 0)) + 1;

    sql_query("INSERT INTO {$g5['mg_map_region_table']} (mr_name, mr_desc, mr_order, mr_use) VALUES ('{$mr_name}', '{$mr_desc}', {$mr_order}, {$mr_use})");
    $mr_id = sql_insert_id();

    // 이미지 업로드
    if ($mr_id && isset($_FILES['mr_image_file']) && $_FILES['mr_image_file']['error'] === UPLOAD_ERR_OK) {
        $img_url = _upload_region_image($_FILES['mr_image_file'], $mr_id);
        if ($img_url) {
            sql_query("UPDATE {$g5['mg_map_region_table']} SET mr_image = '" . sql_real_escape_string($img_url) . "' WHERE mr_id = {$mr_id}");
        }
    }

    goto_url('./lore_map.php?tab=regions');
}

// ==========================================
// 지역 수정
// ==========================================
if ($mode == 'region_edit') {
    $mr_id = (int)($_POST['mr_id'] ?? 0);
    $mr_name = sql_real_escape_string(trim($_POST['mr_name'] ?? ''));
    $mr_desc = sql_real_escape_string(trim($_POST['mr_desc'] ?? ''));
    $mr_use = isset($_POST['mr_use']) ? 1 : 0;

    if (!$mr_id) alert('잘못된 요청입니다.', './lore_map.php?tab=regions');
    if (!$mr_name) alert('지역명을 입력해주세요.', './lore_map.php?tab=regions');

    $existing = sql_fetch("SELECT * FROM {$g5['mg_map_region_table']} WHERE mr_id = {$mr_id}");
    if (!$existing['mr_id']) alert('지역을 찾을 수 없습니다.', './lore_map.php?tab=regions');

    $mr_image = $existing['mr_image'] ?? '';

    // 이미지 삭제 요청
    $img_action = isset($_POST['mr_image_action']) ? $_POST['mr_image_action'] : '';
    if ($img_action === '__DELETE__') {
        _delete_region_image($mr_image);
        $mr_image = '';
    }

    // 새 이미지 업로드
    if (isset($_FILES['mr_image_file']) && $_FILES['mr_image_file']['error'] === UPLOAD_ERR_OK) {
        _delete_region_image($mr_image);
        $new_url = _upload_region_image($_FILES['mr_image_file'], $mr_id);
        if ($new_url) $mr_image = $new_url;
    }

    $mr_image_esc = sql_real_escape_string($mr_image);
    sql_query("UPDATE {$g5['mg_map_region_table']} SET mr_name = '{$mr_name}', mr_desc = '{$mr_desc}', mr_image = '{$mr_image_esc}', mr_use = {$mr_use} WHERE mr_id = {$mr_id}");

    goto_url('./lore_map.php?tab=regions');
}

// ==========================================
// 지역 삭제
// ==========================================
if ($mode == 'region_delete') {
    $mr_id = (int)($_POST['mr_id'] ?? 0);
    if (!$mr_id) alert('잘못된 요청입니다.', './lore_map.php?tab=regions');

    $existing = sql_fetch("SELECT mr_image FROM {$g5['mg_map_region_table']} WHERE mr_id = {$mr_id}");
    if ($existing['mr_image'] ?? '') {
        _delete_region_image($existing['mr_image']);
    }

    sql_query("DELETE FROM {$g5['mg_map_region_table']} WHERE mr_id = {$mr_id}");
    goto_url('./lore_map.php?tab=regions');
}

// ==========================================
// 좌표 설정 (AJAX)
// ==========================================
if ($mode == 'region_set_coords') {
    header('Content-Type: application/json; charset=utf-8');
    $mr_id = (int)($_POST['mr_id'] ?? 0);
    $mr_map_x = (float)($_POST['mr_map_x'] ?? 0);
    $mr_map_y = (float)($_POST['mr_map_y'] ?? 0);

    if (!$mr_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
        exit;
    }

    $mr_marker_style = isset($_POST['mr_marker_style']) ? trim($_POST['mr_marker_style']) : 'pin';
    if (!in_array($mr_marker_style, array('pin', 'circle', 'diamond', 'flag'))) {
        $mr_marker_style = 'pin';
    }
    $mr_marker_style_esc = sql_real_escape_string($mr_marker_style);

    sql_query("UPDATE {$g5['mg_map_region_table']} SET mr_map_x = {$mr_map_x}, mr_map_y = {$mr_map_y}, mr_marker_style = '{$mr_marker_style_esc}' WHERE mr_id = {$mr_id}");
    echo json_encode(array('success' => true));
    exit;
}

// ==========================================
// 좌표 제거 (AJAX)
// ==========================================
if ($mode == 'region_remove_coords') {
    header('Content-Type: application/json; charset=utf-8');
    $mr_id = (int)($_POST['mr_id'] ?? 0);

    if (!$mr_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
        exit;
    }

    sql_query("UPDATE {$g5['mg_map_region_table']} SET mr_map_x = NULL, mr_map_y = NULL WHERE mr_id = {$mr_id}");
    echo json_encode(array('success' => true));
    exit;
}

// 알 수 없는 mode
goto_url('./lore_map.php');

// ==========================================
// 내부 헬퍼
// ==========================================
function _upload_region_image($file, $mr_id) {
    $upload_dir = G5_DATA_PATH.'/morgan';
    $upload_url = G5_DATA_URL.'/morgan';

    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext) || $file['size'] > 2 * 1024 * 1024) {
        return '';
    }

    $filename = 'map_region_' . $mr_id . '_' . date('Ymd_His') . '.' . $ext;
    $target = $upload_dir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        @chmod($target, 0644);
        return $upload_url . '/' . $filename;
    }

    return '';
}

function _delete_region_image($url) {
    if (!$url) return;
    $path = str_replace(G5_DATA_URL, G5_DATA_PATH, $url);
    if (file_exists($path)) @unlink($path);
}
