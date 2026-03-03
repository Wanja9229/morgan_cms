<?php
/**
 * Morgan Health Check Endpoint
 *
 * URL: https://{bare-domain}/_health
 * tenant_bootstrap.php에서 라우팅.
 *
 * Bearer 토큰 인증 (MG_HEALTH_TOKEN 상수).
 * 인증 없이도 기본 상태만 반환 (세부 정보는 인증 필요).
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// 마스터 DB 설정 로드 확인
$_master_cfg = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_master_cfg)) {
    echo json_encode(['status' => 'error', 'message' => 'Not configured'], JSON_UNESCAPED_UNICODE);
    exit;
}
include_once($_master_cfg);

// 기본 응답
$response = [
    'status'    => 'ok',
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'version'   => defined('MG_VERSION') ? MG_VERSION : 'unknown',
];

// Bearer 토큰 인증 체크
$authenticated = false;
if (defined('MG_HEALTH_TOKEN') && MG_HEALTH_TOKEN) {
    $auth_header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    if (!$auth_header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    }

    if ($auth_header && preg_match('/^Bearer\s+(.+)$/i', $auth_header, $m)) {
        if (hash_equals(MG_HEALTH_TOKEN, $m[1])) {
            $authenticated = true;
        }
    }
}

// 인증된 경우: 상세 정보 추가
if ($authenticated) {
    // 마스터 DB 연결 체크
    $link = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
    if ($link) {
        $response['master_db'] = 'ok';

        // 테넌트 통계
        $result = mysqli_query($link, "SELECT
            COUNT(*) as total,
            SUM(status='active') as active,
            SUM(status='suspended') as suspended
            FROM tenants WHERE status != 'deleted'");
        if ($result) {
            $stats = mysqli_fetch_assoc($result);
            $response['tenants'] = [
                'total'     => (int)$stats['total'],
                'active'    => (int)$stats['active'],
                'suspended' => (int)$stats['suspended'],
            ];
        }

        mysqli_close($link);
    } else {
        $response['status'] = 'degraded';
        $response['master_db'] = 'error';
        $response['master_db_error'] = mysqli_connect_error();
    }

    // 디스크 사용량
    $data_path = G5_DATA_PATH;
    if (function_exists('disk_free_space') && is_dir($data_path)) {
        $free = @disk_free_space($data_path);
        $total = @disk_total_space($data_path);
        if ($free !== false && $total !== false) {
            $response['disk'] = [
                'free_gb'  => round($free / (1024 * 1024 * 1024), 2),
                'total_gb' => round($total / (1024 * 1024 * 1024), 2),
                'usage_pct' => round((1 - $free / $total) * 100, 1),
            ];
        }
    }

    // PHP 정보
    $response['php'] = PHP_VERSION;
    $response['uptime'] = @file_get_contents('/proc/uptime') ?: null;
    if ($response['uptime']) {
        $response['uptime'] = (float)explode(' ', $response['uptime'])[0];
    }
} else {
    // 미인증: 마스터 DB 연결만 간단히 체크
    $link = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
    if ($link) {
        $response['master_db'] = 'ok';
        mysqli_close($link);
    } else {
        $response['status'] = 'degraded';
        $response['master_db'] = 'error';
    }
}

http_response_code($response['status'] === 'ok' ? 200 : 503);
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
