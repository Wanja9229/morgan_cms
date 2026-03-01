<?php
/**
 * Morgan Edition - 위젯 이미지 업로드 처리
 */

require_once __DIR__.'/../_common.php';

header('Content-Type: application/json');

if ($is_admin != 'super') {
    die(json_encode(['success' => false, 'message' => '권한이 없습니다.']));
}

// 업로드 디렉토리
$upload_dir = MG_WIDGET_DATA_PATH;
$upload_url = MG_WIDGET_DATA_URL;

// 디렉토리 생성
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0755, true)) {
        die(json_encode(['success' => false, 'message' => '업로드 폴더를 생성할 수 없습니다: ' . $upload_dir]));
    }
}

// 쓰기 권한 확인
if (!is_writable($upload_dir)) {
    die(json_encode(['success' => false, 'message' => '업로드 폴더에 쓰기 권한이 없습니다.']));
}

// 파일 업로드 처리
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = array(
        UPLOAD_ERR_INI_SIZE => '파일이 너무 큽니다.',
        UPLOAD_ERR_FORM_SIZE => '파일이 너무 큽니다.',
        UPLOAD_ERR_PARTIAL => '파일이 일부만 업로드되었습니다.',
        UPLOAD_ERR_NO_FILE => '파일이 선택되지 않았습니다.',
        UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
        UPLOAD_ERR_CANT_WRITE => '파일을 저장할 수 없습니다.',
    );
    $error_code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $error_messages[$error_code] ?? '업로드 오류가 발생했습니다.';
    die(json_encode(['success' => false, 'message' => $message]));
}

$file = $_FILES['image'];

// 허용 확장자
$allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    die(json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다. (jpg, png, gif, webp, svg)']));
}

// 파일 크기 제한
if ($file['size'] > mg_upload_max_file()) {
    die(json_encode(['success' => false, 'message' => '파일 크기가 너무 큽니다.']));
}

// MIME 타입 확인
$allowed_mime = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml');
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_mime)) {
    die(json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다.']));
}

// 고유 파일명 생성
$new_filename = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
$target_path = $upload_dir . '/' . $new_filename;

// 파일 이동
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    die(json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']));
}

// 성공 응답
echo json_encode([
    'success' => true,
    'url' => $upload_url . '/' . $new_filename,
    'filename' => $new_filename
]);
