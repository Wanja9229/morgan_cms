<?php
/**
 * Morgan Tenant Bootstrap
 *
 * 멀티테넌트 모드에서 서브도메인으로부터 테넌트를 식별하고
 * DB 연결 상수를 선행 정의한다.
 *
 * 로드 시점: common.php에서 dbconfig.php 로드 직전
 * 전제조건: config.php 로드 완료 (G5_DATA_PATH, G5_PLUGIN_PATH 사용 가능)
 * 제약사항: common.lib.php 미로드 상태 (sql_* 함수 사용 불가, raw mysqli 사용)
 */

if (!defined('_GNUBOARD_')) exit;

// ======================================================
// 1. 멀티테넌트 활성화 확인
// ======================================================

$_mg_master_config = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_mg_master_config)) {
    define('MG_MULTITENANT_ENABLED', false);
    return;
}

include_once($_mg_master_config);
unset($_mg_master_config);

// 필수 상수 검증
if (!defined('MG_MASTER_DB_HOST') || !defined('MG_MASTER_DB_NAME') || !defined('MG_TENANT_BASE_DOMAIN')) {
    define('MG_MULTITENANT_ENABLED', false);
    return;
}

define('MG_MULTITENANT_ENABLED', true);

// ======================================================
// 2. 서브도메인 추출
// ======================================================

$_mg_subdomain = _mg_extract_subdomain();

// ======================================================
// 3. 특수 서브도메인 처리
// ======================================================

if ($_mg_subdomain === null) {
    // bare domain 또는 www → 기본 테넌트로 폴백
    if (defined('MG_DEFAULT_TENANT_SUBDOMAIN') && MG_DEFAULT_TENANT_SUBDOMAIN) {
        $_mg_subdomain = MG_DEFAULT_TENANT_SUBDOMAIN;
    } else {
        _mg_show_landing_page();
        exit;
    }
}

if ($_mg_subdomain === 'admin') {
    // 슈퍼 관리자 패널 (MT-3에서 UI 구현)
    define('MG_TENANT_ID', 0);
    define('MG_TENANT_SUBDOMAIN', 'admin');
    define('MG_IS_SUPER_ADMIN_PANEL', true);
    define('G5_MYSQL_HOST', MG_MASTER_DB_HOST);
    define('G5_MYSQL_USER', MG_MASTER_DB_USER);
    define('G5_MYSQL_PASSWORD', MG_MASTER_DB_PASS);
    define('G5_MYSQL_DB', MG_MASTER_DB_NAME);
    define('G5_MYSQL_SET_MODE', true);
    return;
}

// ======================================================
// 4. 테넌트 조회
// ======================================================

$_mg_tenant = _mg_resolve_tenant($_mg_subdomain);

if (!$_mg_tenant || (isset($_mg_tenant['status']) && $_mg_tenant['status'] === 'not_found')) {
    _mg_show_tenant_not_found($_mg_subdomain);
    exit;
}

if ($_mg_tenant['status'] === 'suspended') {
    _mg_show_tenant_suspended();
    exit;
}

if ($_mg_tenant['status'] !== 'active') {
    _mg_show_tenant_not_found($_mg_subdomain);
    exit;
}

// ======================================================
// 5. DB 상수 선행 정의
// ======================================================

$_mg_db_host = !empty($_mg_tenant['db_host']) ? $_mg_tenant['db_host'] : MG_MASTER_DB_HOST;
define('G5_MYSQL_HOST', $_mg_db_host);
define('G5_MYSQL_USER', $_mg_tenant['db_user']);
define('G5_MYSQL_PASSWORD', $_mg_tenant['db_pass']);
define('G5_MYSQL_DB', $_mg_tenant['db_name']);
define('G5_MYSQL_SET_MODE', true);

// ======================================================
// 6. 테넌트 컨텍스트
// ======================================================

define('MG_TENANT_ID', (int)$_mg_tenant['id']);
define('MG_TENANT_SUBDOMAIN', $_mg_tenant['subdomain']);
$GLOBALS['mg_tenant'] = $_mg_tenant;

// ======================================================
// 7. 세션 격리
// ======================================================

$_mg_session_suffix = strtoupper(preg_replace('/[^a-z0-9]/i', '', $_mg_tenant['subdomain']));
define('G5_SESSION_NAME', 'MG_' . $_mg_session_suffix);

// 쿠키 도메인 격리
define('G5_COOKIE_DOMAIN', '.' . $_mg_tenant['subdomain'] . '.' . MG_TENANT_BASE_DOMAIN);

// 정리
unset($_mg_subdomain, $_mg_tenant, $_mg_db_host, $_mg_session_suffix);


// ========================================================
// 내부 함수
// ========================================================

/**
 * HTTP_HOST에서 서브도메인 추출
 *
 * @return string|null 서브도메인 또는 null (bare domain / 개발환경)
 */
function _mg_extract_subdomain() {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    // 포트 제거
    $host = preg_replace('/:[0-9]+$/', '', $host);
    $host = strtolower(trim($host));

    $base = strtolower(MG_TENANT_BASE_DOMAIN);

    // 로컬 개발 환경 (localhost, IP)
    if ($host === 'localhost' || preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', $host)) {
        if (isset($_GET['_tenant'])) {
            return preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['_tenant']));
        }
        if (isset($_COOKIE['_mg_dev_tenant'])) {
            return preg_replace('/[^a-z0-9-]/', '', strtolower($_COOKIE['_mg_dev_tenant']));
        }
        return null;
    }

    // bare domain
    if ($host === $base) return null;

    // www = bare domain
    if ($host === 'www.' . $base) return null;

    // {subdomain}.{base} 패턴
    $suffix = '.' . $base;
    if (substr($host, -strlen($suffix)) !== $suffix) {
        // base domain과 매칭 안 됨 → 커스텀 도메인 (MT-3)
        return null;
    }

    $subdomain = substr($host, 0, -strlen($suffix));

    // 유효성: 영소문자, 숫자, 하이픈 / 1~63자
    if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $subdomain)) {
        return null;
    }

    return $subdomain;
}

/**
 * 테넌트 정보 조회 (파일 캐시 우선)
 *
 * @param string $subdomain
 * @return array|false 테넌트 정보 배열 또는 false
 */
function _mg_resolve_tenant($subdomain) {
    // 파일 캐시 확인
    $cache_dir = G5_DATA_PATH . '/cache/tenant';
    $cache_file = $cache_dir . '/' . $subdomain . '.json';

    if (file_exists($cache_file)) {
        $cache_age = time() - filemtime($cache_file);
        // 네거티브 캐시: 1분, 정상 캐시: 5분
        $cached = @json_decode(file_get_contents($cache_file), true);
        if ($cached) {
            $ttl = (!empty($cached['_negative'])) ? 60 : 300;
            if ($cache_age < $ttl) {
                if (!empty($cached['_negative'])) {
                    return false;
                }
                return $cached;
            }
        }
    }

    // 마스터 DB 조회
    $link = @mysqli_connect(
        MG_MASTER_DB_HOST,
        MG_MASTER_DB_USER,
        MG_MASTER_DB_PASS,
        MG_MASTER_DB_NAME
    );

    if (!$link) {
        error_log('[Morgan MT] Master DB connection failed: ' . mysqli_connect_error());
        return false;
    }

    mysqli_set_charset($link, 'utf8mb4');

    $sub_escaped = mysqli_real_escape_string($link, $subdomain);
    $result = mysqli_query($link,
        "SELECT * FROM tenants WHERE subdomain = '{$sub_escaped}' AND status != 'deleted' LIMIT 1"
    );

    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_close($link);
        // 네거티브 캐시
        _mg_write_tenant_cache($cache_dir, $cache_file, ['_negative' => true, 'status' => 'not_found']);
        return false;
    }

    $tenant = mysqli_fetch_assoc($result);
    mysqli_close($link);

    // 캐시 저장
    _mg_write_tenant_cache($cache_dir, $cache_file, $tenant);

    return $tenant;
}

/**
 * 테넌트 캐시 파일 저장
 */
function _mg_write_tenant_cache($dir, $file, $data) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    // .htaccess 보호
    $htaccess = $dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Deny from all\n");
    }
    @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * 테넌트 미존재 페이지
 */
function _mg_show_tenant_not_found($subdomain) {
    http_response_code(404);
    $sub = htmlspecialchars($subdomain ?? '');
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><title>Not Found</title>';
    echo '<style>body{background:#1e1f22;color:#f2f3f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}';
    echo '.box{text-align:center} h1{color:#f59f0a;margin-bottom:0.5rem} p{color:#949ba4}</style></head>';
    echo '<body><div class="box"><h1>404</h1>';
    echo '<p>' . ($sub ? "'{$sub}' 커뮤니티를 찾을 수 없습니다." : '커뮤니티를 찾을 수 없습니다.') . '</p>';
    echo '</div></body></html>';
}

/**
 * 테넌트 정지 페이지
 */
function _mg_show_tenant_suspended() {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><title>서비스 정지</title>';
    echo '<style>body{background:#1e1f22;color:#f2f3f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}';
    echo '.box{text-align:center} h1{color:#ef4444;margin-bottom:0.5rem} p{color:#949ba4}</style></head>';
    echo '<body><div class="box"><h1>서비스 정지</h1>';
    echo '<p>이 커뮤니티는 일시적으로 정지되었습니다.</p>';
    echo '<p>관리자에게 문의해 주세요.</p>';
    echo '</div></body></html>';
}

/**
 * 랜딩 페이지 (bare domain)
 */
function _mg_show_landing_page() {
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><title>Morgan CMS</title>';
    echo '<style>body{background:#1e1f22;color:#f2f3f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}';
    echo '.box{text-align:center} h1{color:#f59f0a;margin-bottom:0.5rem} p{color:#949ba4;line-height:1.6}</style></head>';
    echo '<body><div class="box"><h1>Morgan Edition CMS</h1>';
    echo '<p>서브도메인을 통해 접속해 주세요.</p>';
    echo '</div></body></html>';
}
