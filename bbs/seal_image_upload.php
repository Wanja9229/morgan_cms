<?php
/**
 * Morgan Edition - 인장 이미지 업로드 (AJAX)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

if (!mg_config('seal_enable', 1)) {
    echo json_encode(array('success' => false, 'message' => '인장 시스템이 비활성화되어 있습니다.'));
    exit;
}

$mb_id = $member['mb_id'];
$mb_esc = sql_real_escape_string($mb_id);

// 이미지 삭제 요청
if (isset($_POST['remove_image'])) {
    $seal = sql_fetch("SELECT seal_image FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
    if ($seal && !empty($seal['seal_image']) && strpos($seal['seal_image'], 'http') !== 0) {
        $old_path = MG_SEAL_IMAGE_PATH . '/' . $seal['seal_image'];
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }
    sql_query("UPDATE {$g5['mg_seal_table']} SET seal_image = '', seal_update = NOW() WHERE mb_id = '{$mb_esc}'");
    echo json_encode(array('success' => true, 'message' => '이미지가 삭제되었습니다.'));
    exit;
}

// 이미지 업로드
if (!mg_config('seal_image_upload', 1)) {
    echo json_encode(array('success' => false, 'message' => '이미지 업로드가 비활성화되어 있습니다.'));
    exit;
}

if (!isset($_FILES['seal_image']) || $_FILES['seal_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('success' => false, 'message' => '파일이 선택되지 않았습니다.'));
    exit;
}

// 기존 이미지 삭제
$seal = sql_fetch("SELECT seal_image FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
if ($seal && !empty($seal['seal_image']) && strpos($seal['seal_image'], 'http') !== 0) {
    $old_path = MG_SEAL_IMAGE_PATH . '/' . $seal['seal_image'];
    if (file_exists($old_path)) {
        @unlink($old_path);
    }
}

// 업로드
$result = mg_upload_seal_image($_FILES['seal_image'], $mb_id);
if (!$result['success']) {
    echo json_encode($result);
    exit;
}

// DB 저장 (인장 레코드가 없으면 생성)
sql_query("INSERT INTO {$g5['mg_seal_table']} (mb_id, seal_image, seal_update)
    VALUES ('{$mb_esc}', '".sql_real_escape_string($result['filename'])."', NOW())
    ON DUPLICATE KEY UPDATE seal_image = '".sql_real_escape_string($result['filename'])."', seal_update = NOW()");

$url = MG_SEAL_IMAGE_URL . '/' . $result['filename'];
echo json_encode(array('success' => true, 'url' => $url, 'message' => '이미지가 업로드되었습니다.'));
