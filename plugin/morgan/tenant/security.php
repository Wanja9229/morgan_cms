<?php
/**
 * Morgan Multitenant Security Helpers
 *
 * 경로 검증, 리소스 쿼터, CORS, 에러 로그 분리 등
 * 보안 관련 유틸리티 함수 모음.
 *
 * 로드 시점: tenant_bootstrap.php에서 테넌트 컨텍스트 설정 후
 */

if (!defined('_GNUBOARD_')) exit;

// ======================================================
// 1. 경로 검증
// ======================================================

/**
 * 스토리지 경로 검증
 *
 * LocalStorage/R2Storage에서 파일 조작 전 호출하여
 * path traversal 및 잘못된 경로를 차단한다.
 *
 * @param string $path data/ 이하 상대경로
 * @return string 정규화된 안전한 경로
 * @throws InvalidArgumentException 위험한 경로 감지 시
 */
function mg_validate_storage_path($path) {
    // null byte 차단
    if (strpos($path, "\0") !== false) {
        throw new InvalidArgumentException('[MG Security] Null byte in path');
    }

    // 빈 경로 차단
    $path = trim($path);
    if ($path === '') {
        throw new InvalidArgumentException('[MG Security] Empty path');
    }

    // 절대경로 차단 (Unix: /, Windows: C:\, \\)
    if (preg_match('#^(/|[a-zA-Z]:\\\\|\\\\\\\\)#', $path)) {
        throw new InvalidArgumentException('[MG Security] Absolute path not allowed');
    }

    // 상위 디렉토리 탐색 차단 (../ 또는 ..\)
    // 정규화 전에 원본 경로에서 먼저 체크
    if (preg_match('#(^|/|\\\\)\.\.(/|\\\\|$)#', $path)) {
        throw new InvalidArgumentException('[MG Security] Directory traversal not allowed');
    }

    // 경로 정규화: 백슬래시 → 슬래시, 중복 슬래시 제거
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/{2,}#', '/', $path);

    // 선행 슬래시 제거 (상대 경로로 통일)
    $path = ltrim($path, '/');

    // 정규화 후 다시 .. 체크 (인코딩 우회 방지)
    $parts = explode('/', $path);
    foreach ($parts as $part) {
        if ($part === '..') {
            throw new InvalidArgumentException('[MG Security] Directory traversal not allowed');
        }
    }

    return $path;
}

/**
 * 로컬 파일시스템 경로가 basePath 내부인지 검증
 *
 * realpath 기반으로 심볼릭 링크 탈출도 방지한다.
 *
 * @param string $fullPath 검증할 전체 경로
 * @param string $basePath 허용된 기준 경로
 * @return bool
 */
function mg_verify_path_within_base($fullPath, $basePath) {
    // 디렉토리가 아직 없으면 가장 가까운 존재하는 부모를 확인
    $checkPath = $fullPath;
    while (!file_exists($checkPath) && $checkPath !== dirname($checkPath)) {
        $checkPath = dirname($checkPath);
    }

    $realCheck = realpath($checkPath);
    $realBase  = realpath($basePath);

    if ($realCheck === false || $realBase === false) {
        return false;
    }

    // 정규화: Windows 호환
    $realCheck = str_replace('\\', '/', $realCheck);
    $realBase  = str_replace('\\', '/', $realBase);

    return strpos($realCheck, $realBase) === 0;
}

// ======================================================
// 2. 리소스 쿼터
// ======================================================

/**
 * 테넌트 스토리지 쿼터 확인
 *
 * @param int $additional_bytes 추가할 파일 크기 (바이트)
 * @return bool true = 여유 있음, false = 초과
 */
function mg_check_storage_quota($additional_bytes = 0) {
    if (!defined('MG_MULTITENANT_ENABLED') || !MG_MULTITENANT_ENABLED) {
        return true; // 단일 테넌트면 제한 없음
    }

    $tenant = isset($GLOBALS['mg_tenant']) ? $GLOBALS['mg_tenant'] : null;
    if (!$tenant) {
        return true; // 테넌트 정보 없으면 통과
    }

    $max_mb = isset($tenant['max_storage_mb']) ? (int)$tenant['max_storage_mb'] : 1024;
    if ($max_mb <= 0) {
        return true; // 0 = 무제한
    }

    // 캐시된 사용량 확인 (storage_used_mb)
    $used_mb = isset($tenant['storage_used_mb']) ? (float)$tenant['storage_used_mb'] : 0;

    // 추가 바이트를 MB로 변환
    $additional_mb = $additional_bytes / (1024 * 1024);

    return ($used_mb + $additional_mb) <= $max_mb;
}

/**
 * 스토리지 사용량 갱신 (업로드/삭제 후 호출)
 *
 * @param int $delta_bytes 변경된 바이트 (+업로드, -삭제)
 */
function mg_update_storage_usage($delta_bytes) {
    if (!defined('MG_MULTITENANT_ENABLED') || !MG_MULTITENANT_ENABLED) {
        return;
    }

    if (!defined('MG_TENANT_ID') || MG_TENANT_ID <= 0) {
        return;
    }

    $delta_mb = $delta_bytes / (1024 * 1024);

    // 마스터 DB에 직접 업데이트
    $link = _mg_get_master_db_link();
    if (!$link) return;

    $tenant_id = (int)MG_TENANT_ID;
    if ($delta_mb >= 0) {
        $sql = "UPDATE tenants SET storage_used_mb = storage_used_mb + {$delta_mb} WHERE id = {$tenant_id}";
    } else {
        $abs_mb = abs($delta_mb);
        $sql = "UPDATE tenants SET storage_used_mb = GREATEST(0, storage_used_mb - {$abs_mb}) WHERE id = {$tenant_id}";
    }

    mysqli_query($link, $sql);
    mysqli_close($link);

    // 글로벌 캐시 갱신
    if (isset($GLOBALS['mg_tenant'])) {
        $current = (float)($GLOBALS['mg_tenant']['storage_used_mb'] ?? 0);
        $GLOBALS['mg_tenant']['storage_used_mb'] = max(0, $current + ($delta_bytes / (1024 * 1024)));
    }
}

/**
 * 테넌트 회원 수 상한 확인
 *
 * @return bool true = 여유 있음, false = 초과
 */
function mg_check_member_quota() {
    if (!defined('MG_MULTITENANT_ENABLED') || !MG_MULTITENANT_ENABLED) {
        return true;
    }

    $tenant = isset($GLOBALS['mg_tenant']) ? $GLOBALS['mg_tenant'] : null;
    if (!$tenant) {
        return true;
    }

    $max_members = isset($tenant['max_members']) ? (int)$tenant['max_members'] : 100;
    if ($max_members <= 0) {
        return true; // 0 = 무제한
    }

    // 현재 회원 수 (테넌트 DB)
    $result = sql_query("SELECT COUNT(*) as cnt FROM g5_member WHERE mb_leave_date = ''");
    if ($result) {
        $row = sql_fetch_array($result);
        $current = (int)$row['cnt'];
        return $current < $max_members;
    }

    return true;
}

// ======================================================
// 3. CORS
// ======================================================

/**
 * 멀티테넌트 CORS 헤더 설정
 *
 * 해당 테넌트의 도메인만 허용한다.
 */
function mg_set_cors_headers() {
    if (!defined('MG_MULTITENANT_ENABLED') || !MG_MULTITENANT_ENABLED) {
        return;
    }

    if (!defined('MG_TENANT_SUBDOMAIN') || !defined('MG_TENANT_BASE_DOMAIN')) {
        return;
    }

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (!$origin) return;

    // 허용할 Origin 목록 생성
    $allowed_origins = [];
    $sub = MG_TENANT_SUBDOMAIN;
    $base = MG_TENANT_BASE_DOMAIN;

    // HTTPS + HTTP 둘 다 허용 (개발/프로덕션)
    $allowed_origins[] = "https://{$sub}.{$base}";
    $allowed_origins[] = "http://{$sub}.{$base}";

    // localhost 개발 환경
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if (strpos($host, 'localhost') !== false || preg_match('/^[0-9]/', $host)) {
        $allowed_origins[] = $origin; // 개발 환경에서는 모든 origin 허용
    }

    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
        header("Vary: Origin");

        // OPTIONS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header("Access-Control-Max-Age: 86400");
            http_response_code(204);
            exit;
        }
    }
}

// ======================================================
// 4. 마스터 DB 헬퍼
// ======================================================

/**
 * 마스터 DB 연결 획득
 *
 * @return mysqli|false
 */
function _mg_get_master_db_link() {
    if (!defined('MG_MASTER_DB_HOST') || !defined('MG_MASTER_DB_USER')) {
        return false;
    }

    $link = @mysqli_connect(
        MG_MASTER_DB_HOST,
        MG_MASTER_DB_USER,
        MG_MASTER_DB_PASS,
        MG_MASTER_DB_NAME
    );

    if ($link) {
        mysqli_set_charset($link, 'utf8mb4');
    }

    return $link;
}
