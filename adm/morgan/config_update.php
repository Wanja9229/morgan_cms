<?php
/**
 * Morgan Edition - 기본 설정 저장
 */

$sub_menu = "800100";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

/**
 * 배경 이미지 리사이즈 함수
 * @param string $source 원본 파일 경로
 * @param string $dest 저장할 파일 경로
 * @param int $max_width 최대 너비 (기본 1920)
 * @param int $quality JPEG 품질 (기본 85)
 * @return bool 성공 여부
 */
function mg_resize_background_image($source, $dest, $max_width = 1920, $quality = 85) {
    // 이미지 정보 가져오기
    $info = @getimagesize($source);
    if (!$info) return false;

    $width = $info[0];
    $height = $info[1];
    $type = $info[2];

    // 원본 이미지 로드
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_img = @imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $src_img = @imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $src_img = @imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $src_img = @imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    if (!$src_img) return false;

    // 리사이즈 필요 여부 확인
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = (int)($height * ($max_width / $width));

        // 새 이미지 생성
        $dst_img = imagecreatetruecolor($new_width, $new_height);

        // 리샘플링
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        imagedestroy($src_img);
        $src_img = $dst_img;
    }

    // JPEG로 저장
    $result = imagejpeg($src_img, $dest, $quality);
    imagedestroy($src_img);

    return $result;
}

// 저장할 설정 항목
$config_keys = array(
    'site_name',
    'login_point',
    'attendance_point',
    'character_create_point',
    'max_characters',
    'show_main_character',
    'use_side',
    'use_class',
    // 역극 설정
    'rp_use',
    'rp_require_reply',
    'rp_max_member_default',
    'rp_max_member_limit',
    'rp_content_min',
    // 이모티콘 설정
    'emoticon_use',
    'emoticon_creator_use',
    'emoticon_commission_rate',
    'emoticon_min_count',
    'emoticon_max_count',
    'emoticon_image_max_size',
    'emoticon_image_size',
    // 보안 설정 (reCAPTCHA)
    'recaptcha_site_key',
    'recaptcha_secret_key',
    'captcha_register',
    'captcha_write',
    'captcha_comment',
    // 디자인 설정
    'color_accent',
    'color_button',
    'color_border',
    'color_bg_primary',
    'color_bg_secondary',
    'bg_opacity'
);

foreach ($config_keys as $key) {
    $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';

    // 기존 값이 있는지 확인
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_config_table']} WHERE cf_key = '$key'";
    $row = sql_fetch($sql);

    $cnt = isset($row['cnt']) ? (int)$row['cnt'] : 0;
    if ($cnt > 0) {
        // 업데이트
        $sql = "UPDATE {$g5['mg_config_table']} SET cf_value = '".sql_escape_string($value)."' WHERE cf_key = '".sql_escape_string($key)."'";
    } else {
        // 삽입
        $sql = "INSERT INTO {$g5['mg_config_table']} (cf_key, cf_value) VALUES ('".sql_escape_string($key)."', '".sql_escape_string($value)."')";
    }
    sql_query($sql);
}

// 업로드 디렉토리
$upload_dir = G5_DATA_PATH.'/morgan';
$upload_url = G5_DATA_URL.'/morgan';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
    @chmod($upload_dir, 0755);
}

// --- 로고 이미지 처리 ---
$sql = "SELECT cf_value FROM {$g5['mg_config_table']} WHERE cf_key = 'site_logo'";
$logo_row = sql_fetch($sql);
$old_logo = isset($logo_row['cf_value']) ? $logo_row['cf_value'] : '';

if (isset($_POST['site_logo_action']) && $_POST['site_logo_action'] === '__DELETE__') {
    if ($old_logo) {
        $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_logo);
        if (file_exists($old_file)) @unlink($old_file);
    }
    sql_query("DELETE FROM {$g5['mg_config_table']} WHERE cf_key = 'site_logo'");
}
elseif (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['site_logo'];
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed_ext) && $file['size'] <= 2 * 1024 * 1024) {
        if ($old_logo) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_logo);
            if (file_exists($old_file)) @unlink($old_file);
        }

        $new_filename = 'logo_' . date('Ymd_His') . '.' . $ext;
        $target_path = $upload_dir . '/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            @chmod($target_path, 0644);
            $new_url = $upload_url . '/' . $new_filename;

            $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_config_table']} WHERE cf_key = 'site_logo'";
            $row = sql_fetch($sql);
            $cnt = isset($row['cnt']) ? (int)$row['cnt'] : 0;

            if ($cnt > 0) {
                sql_query("UPDATE {$g5['mg_config_table']} SET cf_value = '".sql_escape_string($new_url)."' WHERE cf_key = 'site_logo'");
            } else {
                sql_query("INSERT INTO {$g5['mg_config_table']} (cf_key, cf_value) VALUES ('site_logo', '".sql_escape_string($new_url)."')");
            }
        }
    }
}

// --- 배경 이미지 처리 ---

// 디버깅: 파일 업로드 정보 확인
$debug_log = G5_DATA_PATH.'/morgan_upload_debug.txt';
$debug_info = date('Y-m-d H:i:s') . "\n";
$debug_info .= "FILES: " . print_r($_FILES, true) . "\n";
$debug_info .= "upload_dir: {$upload_dir}\n";
$debug_info .= "is_dir: " . (is_dir($upload_dir) ? 'yes' : 'no') . "\n";
$debug_info .= "is_writable: " . (is_writable(G5_DATA_PATH) ? 'yes' : 'no') . "\n";
file_put_contents($debug_log, $debug_info, FILE_APPEND);

// 기존 배경 이미지 URL 가져오기
$sql = "SELECT cf_value FROM {$g5['mg_config_table']} WHERE cf_key = 'bg_image'";
$bg_row = sql_fetch($sql);
$old_bg_image = isset($bg_row['cf_value']) ? $bg_row['cf_value'] : '';

// 삭제 요청인 경우
if (isset($_POST['bg_image_url']) && $_POST['bg_image_url'] === '__DELETE__') {
    // 기존 파일 삭제
    if ($old_bg_image) {
        $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_bg_image);
        if (file_exists($old_file)) {
            @unlink($old_file);
        }
    }
    // DB에서 삭제
    sql_query("DELETE FROM {$g5['mg_config_table']} WHERE cf_key = 'bg_image'");
}
// 새 이미지 업로드인 경우
elseif (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['bg_image'];
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed_ext) && $file['size'] <= 10 * 1024 * 1024) {
        // 기존 파일 삭제
        if ($old_bg_image) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_bg_image);
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        // 새 파일 저장 (리사이즈 후 JPEG로 저장)
        $new_filename = 'bg_' . date('Ymd_His') . '.jpg';
        $target_path = $upload_dir . '/' . $new_filename;

        file_put_contents($debug_log, "Trying to process: {$file['tmp_name']} -> {$target_path}\n", FILE_APPEND);

        // 이미지 리사이즈 (최대 1920px, JPEG 85% 품질)
        $resized = mg_resize_background_image($file['tmp_name'], $target_path, 1920, 85);

        if ($resized) {
            @chmod($target_path, 0644);
            $new_url = $upload_url . '/' . $new_filename;
            $new_size = filesize($target_path);
            file_put_contents($debug_log, "Resize success! URL: {$new_url}, Size: " . round($new_size/1024) . "KB\n", FILE_APPEND);

            // DB 저장
            $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_config_table']} WHERE cf_key = 'bg_image'";
            $row = sql_fetch($sql);
            $cnt = isset($row['cnt']) ? (int)$row['cnt'] : 0;

            if ($cnt > 0) {
                sql_query("UPDATE {$g5['mg_config_table']} SET cf_value = '".sql_escape_string($new_url)."' WHERE cf_key = 'bg_image'");
            } else {
                sql_query("INSERT INTO {$g5['mg_config_table']} (cf_key, cf_value) VALUES ('bg_image', '".sql_escape_string($new_url)."')");
            }
            file_put_contents($debug_log, "DB saved!\n", FILE_APPEND);
        } else {
            file_put_contents($debug_log, "Resize failed!\n", FILE_APPEND);
        }
    } else {
        file_put_contents($debug_log, "File validation failed. ext={$ext}, size={$file['size']}\n", FILE_APPEND);
    }
} else {
    file_put_contents($debug_log, "No file uploaded or error. Error code: " . ($_FILES['bg_image']['error'] ?? 'no file') . "\n", FILE_APPEND);
}

goto_url('./config.php');
