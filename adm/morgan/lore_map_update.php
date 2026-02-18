<?php
/**
 * Morgan Edition - 세계관 지도 설정 저장
 */

$sub_menu = '800178';
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// --- 마커 스타일 저장 ---
$marker_style = isset($_POST['map_marker_style']) ? trim($_POST['map_marker_style']) : 'pin';
if (!in_array($marker_style, array('pin', 'circle', 'diamond', 'flag'))) {
    $marker_style = 'pin';
}
mg_set_config('map_marker_style', $marker_style);

// --- 맵 이미지 처리 ---
$upload_dir = G5_DATA_PATH.'/morgan';
$upload_url = G5_DATA_URL.'/morgan';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
    @chmod($upload_dir, 0755);
}

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
        // 기존 파일 삭제
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

goto_url('./lore_map.php');
