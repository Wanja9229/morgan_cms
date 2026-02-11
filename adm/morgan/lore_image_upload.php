<?php
/**
 * Morgan Edition - 위키 이미지 AJAX 업로드
 */

require_once __DIR__.'/../_common.php';

header('Content-Type: application/json');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 최고관리자 체크
if ($is_admin != 'super') {
    die(json_encode(array('success' => false, 'message' => '권한이 없습니다.')));
}

// 파일 확인
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = array(
        UPLOAD_ERR_INI_SIZE   => '파일이 너무 큽니다 (서버 제한).',
        UPLOAD_ERR_FORM_SIZE  => '파일이 너무 큽니다.',
        UPLOAD_ERR_PARTIAL    => '파일이 일부만 업로드되었습니다.',
        UPLOAD_ERR_NO_FILE    => '파일이 선택되지 않았습니다.',
        UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
        UPLOAD_ERR_CANT_WRITE => '파일을 저장할 수 없습니다.',
    );
    $error_code = isset($_FILES['file']) ? $_FILES['file']['error'] : UPLOAD_ERR_NO_FILE;
    $message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : '업로드 오류가 발생했습니다.';
    die(json_encode(array('success' => false, 'message' => $message)));
}

// 타입 파라미터
$type = isset($_POST['type']) ? trim($_POST['type']) : 'article';

// 허용 타입
$allowed_types = array('article_thumb', 'section', 'event');
if (!in_array($type, $allowed_types)) {
    $type = 'article';
}

// 타입을 디렉토리 이름으로 매핑
$type_map = array(
    'article_thumb' => 'article',
    'section' => 'section',
    'event' => 'event',
);
$upload_type = isset($type_map[$type]) ? $type_map[$type] : 'article';

// mg_upload_lore_image 함수 호출
$result = mg_upload_lore_image($_FILES['file'], $upload_type, 0);

echo json_encode($result);
