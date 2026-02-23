<?php
require_once("config.php");
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!function_exists('ft_nonce_is_valid')) {
    include_once('../editor.lib.php');
}

header('Content-Type: application/json; charset=utf-8');

// Nonce 검증 (ed_nonce JS 변수 → GET 파라미터로 전달됨)
$get_nonce = isset($_GET['_nonce']) ? $_GET['_nonce'] : '';

if (!$get_nonce || !ft_nonce_is_valid($get_nonce, 'toastui')) {
    echo json_encode(array('error' => 'Unauthorized'));
    exit;
}

// 파일 업로드 검증
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('error' => 'No file uploaded'));
    exit;
}

$tempfile = $_FILES['file']['tmp_name'];
$filename = $_FILES['file']['name'];
$filesize = $_FILES['file']['size'];

// 확장자 검증
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$allowed_ext = array('jpg', 'jpeg', 'gif', 'png', 'webp');

if (!in_array($ext, $allowed_ext)) {
    echo json_encode(array('error' => 'Invalid file type'));
    exit;
}

// 파일 크기 제한
if ($filesize > mg_upload_max_file()) {
    echo json_encode(array('error' => 'File too large'));
    exit;
}

// 이미지 파일인지 확인
$imginfo = @getimagesize($tempfile);
if (!$imginfo) {
    echo json_encode(array('error' => 'Invalid image file'));
    exit;
}

// 저장 파일명 생성: 년월일시분초_랜덤8자.확장자
$save_name = date('YmdHis', G5_SERVER_TIME) . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . '.' . $ext;
$savefile = SAVE_DIR . '/' . $save_name;

if (!move_uploaded_file($tempfile, $savefile)) {
    echo json_encode(array('error' => 'Upload failed'));
    exit;
}

// 이미지 재처리 검증
if (TOASTUI_UPLOAD_IMG_CHECK) {
    $valid = @getimagesize($savefile);
    if (!$valid) {
        @unlink($savefile);
        echo json_encode(array('error' => 'Invalid image'));
        exit;
    }
}

try {
    if (defined('G5_FILE_PERMISSION')) {
        chmod($savefile, G5_FILE_PERMISSION);
    }
} catch (Exception $e) {
}

$file_url = SAVE_URL . '/' . $save_name;

echo json_encode(array(
    'url' => $file_url,
    'fileName' => $save_name,
    'fileSize' => $filesize
));
