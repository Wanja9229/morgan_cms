<?php
/**
 * Morgan Edition - R2 연결 테스트 (AJAX)
 */

$sub_menu = "800102";
require_once __DIR__.'/../_common.php';

header('Content-Type: application/json; charset=utf-8');

if ($is_admin != 'super') {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// POST에서 직접 값 가져오기 (아직 저장 전일 수 있으므로)
$accountId = isset($_POST['mg_r2_account_id']) ? trim($_POST['mg_r2_account_id']) : '';
$accessKey = isset($_POST['mg_r2_access_key_id']) ? trim($_POST['mg_r2_access_key_id']) : '';
$secretKey = isset($_POST['mg_r2_secret_access_key']) ? trim($_POST['mg_r2_secret_access_key']) : '';
$bucket    = isset($_POST['mg_r2_bucket_name']) ? trim($_POST['mg_r2_bucket_name']) : '';
$endpoint  = isset($_POST['mg_r2_endpoint']) ? trim($_POST['mg_r2_endpoint']) : '';

if (!$accountId || !$accessKey || !$secretKey || !$bucket) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 모두 입력해주세요.']);
    exit;
}

if (!$endpoint) {
    $endpoint = "https://{$accountId}.r2.cloudflarestorage.com";
}

require_once(G5_PLUGIN_PATH.'/morgan/storage/S3Signature.php');

$signer = new MG_S3Signature($accessKey, $secretKey, 'auto', 's3');

// HeadBucket 요청
$url = rtrim($endpoint, '/') . '/' . $bucket;
$signedHeaders = $signer->signRequest('HEAD', $url);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_CUSTOMREQUEST  => 'HEAD',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_NOBODY         => true,
]);

$curlHeaders = [];
foreach ($signedHeaders as $k => $v) {
    $curlHeaders[] = "{$k}: {$v}";
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($httpCode === 200) {
    echo json_encode(['success' => true, 'message' => "연결 성공! 버킷 '{$bucket}'에 접근 가능합니다."]);
} elseif ($httpCode === 0) {
    echo json_encode(['success' => false, 'message' => '서버에 연결할 수 없습니다. Endpoint URL을 확인해주세요. (' . $error . ')']);
} elseif ($httpCode === 403) {
    echo json_encode(['success' => false, 'message' => '인증 실패 (HTTP 403). Access Key / Secret Key를 확인해주세요.']);
} elseif ($httpCode === 404) {
    echo json_encode(['success' => false, 'message' => "버킷 '{$bucket}'을 찾을 수 없습니다 (HTTP 404)."]);
} else {
    echo json_encode(['success' => false, 'message' => "HTTP {$httpCode} 오류가 발생했습니다."]);
}
