<?php
/**
 * Morgan Onboarding — 처리 로직
 *
 * tenant_onboard.php에서 POST 수신 → 검증 → TenantManager::provision()
 * common.php 미로드 상태에서 독립 실행.
 */

if (!defined('_GNUBOARD_')) exit;

// ============================================================
// 마스터 DB 연결 (슈퍼 관리자 패턴 재사용)
// ============================================================

$_master_cfg = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_master_cfg)) {
    _ob_json_error('서비스 설정이 완료되지 않았습니다.');
}
include_once($_master_cfg);

$_OB_LINK = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
if (!$_OB_LINK) {
    error_log('[Onboard] Master DB connection failed: ' . mysqli_connect_error());
    _ob_json_error('서버 오류가 발생했습니다.');
}
mysqli_set_charset($_OB_LINK, 'utf8mb4');

// ============================================================
// 세션 (CSRF + Rate Limit)
// ============================================================

session_name('MG_ONBOARD');
session_start();

// ============================================================
// 액션 분기
// ============================================================

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'check_subdomain':
        _ob_action_check_subdomain();
        break;

    case 'create':
        _ob_action_create();
        break;

    default:
        _ob_json_error('잘못된 요청');
}

// ============================================================
// 액션: 서브도메인 중복 체크 (AJAX)
// ============================================================

function _ob_action_check_subdomain() {
    global $_OB_LINK;

    $subdomain = isset($_GET['subdomain']) ? trim($_GET['subdomain']) : '';
    $subdomain = strtolower(preg_replace('/[^a-z0-9-]/', '', $subdomain));

    if (!$subdomain || strlen($subdomain) < 2 || strlen($subdomain) > 63) {
        _ob_json_response(['available' => false, 'message' => '2~63자의 영소문자, 숫자, 하이픈만 사용 가능합니다.']);
    }

    // 예약어 체크
    $reserved = ['admin', 'www', 'api', 'mail', 'ftp', 'ns1', 'ns2', 'test', 'demo', 'dev', 'staging', 'app', 'cdn', 'static', 'status', 'help', 'support'];
    if (in_array($subdomain, $reserved)) {
        _ob_json_response(['available' => false, 'message' => '이 서브도메인은 사용할 수 없습니다.']);
    }

    // DB 중복 체크
    $escaped = mysqli_real_escape_string($_OB_LINK, $subdomain);
    $result = mysqli_query($_OB_LINK, "SELECT id FROM tenants WHERE subdomain = '{$escaped}' LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        _ob_json_response(['available' => false, 'message' => '이미 사용 중인 서브도메인입니다.']);
    }

    _ob_json_response(['available' => true, 'message' => '사용 가능합니다.']);
}

// ============================================================
// 액션: 테넌트 생성
// ============================================================

function _ob_action_create() {
    global $_OB_LINK;

    // CSRF 검증
    $token = isset($_POST['_token']) ? $_POST['_token'] : '';
    if (empty($token) || empty($_SESSION['ob_csrf_token']) || !hash_equals($_SESSION['ob_csrf_token'], $token)) {
        _ob_json_error('보안 토큰 검증에 실패했습니다. 페이지를 새로고침해주세요.');
    }

    // Rate Limit: IP당 1시간에 3회
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $ip_escaped = mysqli_real_escape_string($_OB_LINK, $ip);
    $result = mysqli_query($_OB_LINK,
        "SELECT COUNT(*) as cnt FROM onboard_rate_limit WHERE ip_address = '{$ip_escaped}' AND attempted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $row = $result ? mysqli_fetch_assoc($result) : null;
    if ($row && (int)$row['cnt'] >= 3) {
        _ob_json_error('너무 많은 요청이 감지되었습니다. 잠시 후 다시 시도해주세요.');
    }

    // Rate Limit 기록
    mysqli_query($_OB_LINK, "INSERT INTO onboard_rate_limit (ip_address) VALUES ('{$ip_escaped}')");

    // Honeypot 봇 방지
    $honeypot = isset($_POST['website']) ? $_POST['website'] : '';
    if ($honeypot !== '') {
        _ob_json_error('잘못된 요청입니다.');
    }

    // 입력 검증
    $subdomain  = strtolower(trim($_POST['subdomain'] ?? ''));
    $name       = trim($_POST['name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminId    = trim($_POST['admin_id'] ?? '');
    $adminPass  = $_POST['admin_pass'] ?? '';

    $errors = [];

    // 서브도메인 검증
    $subdomain = preg_replace('/[^a-z0-9-]/', '', $subdomain);
    if (strlen($subdomain) < 2 || strlen($subdomain) > 63) {
        $errors[] = '서브도메인은 2~63자여야 합니다.';
    }
    if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $subdomain)) {
        $errors[] = '서브도메인 형식이 올바르지 않습니다.';
    }
    $reserved = ['admin', 'www', 'api', 'mail', 'ftp', 'ns1', 'ns2', 'test', 'demo', 'dev', 'staging', 'app', 'cdn', 'static', 'status', 'help', 'support'];
    if (in_array($subdomain, $reserved)) {
        $errors[] = '이 서브도메인은 예약되어 있습니다.';
    }

    // 이름 검증
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = '커뮤니티 이름은 2~100자여야 합니다.';
    }

    // 이메일 검증
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '올바른 이메일을 입력해주세요.';
    }

    // 관리자 ID 검증
    if (!preg_match('/^[a-z][a-z0-9_]{2,19}$/', $adminId)) {
        $errors[] = '관리자 ID는 영소문자로 시작, 3~20자(영문/숫자/밑줄)여야 합니다.';
    }

    // 비밀번호 검증
    if (strlen($adminPass) < 8) {
        $errors[] = '비밀번호는 8자 이상이어야 합니다.';
    }

    if (!empty($errors)) {
        _ob_json_error(implode("\n", $errors));
    }

    // TenantManager로 프로비저닝
    require_once(G5_PLUGIN_PATH . '/morgan/tenant/TenantManager.php');
    $tm = new TenantManager($_OB_LINK);

    $result = $tm->provision($subdomain, $name, $adminEmail, $adminId, 'free');

    if ($result === false) {
        $tmErrors = $tm->getErrors();
        _ob_json_error(implode("\n", $tmErrors));
    }

    // 관리자 비밀번호 설정 (TenantManager가 랜덤 비밀번호 생성하므로 사용자 지정으로 교체)
    _ob_set_admin_password($result, $adminId, $adminPass);

    // 테넌트 캐시 무효화
    $cache_file = G5_DATA_PATH . '/cache/tenant/' . $subdomain . '.json';
    if (file_exists($cache_file)) {
        @unlink($cache_file);
    }

    // Rate Limit 오래된 레코드 정리 (24시간 이상)
    mysqli_query($_OB_LINK, "DELETE FROM onboard_rate_limit WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

    // 성공 응답
    $base_domain = defined('MG_TENANT_BASE_DOMAIN') ? MG_TENANT_BASE_DOMAIN : 'example.com';
    $tenant_url = 'https://' . $subdomain . '.' . $base_domain;

    _ob_json_response([
        'success' => true,
        'tenant_url' => $tenant_url,
        'admin_id' => $adminId,
        'message' => '커뮤니티가 생성되었습니다!'
    ]);
}

// ============================================================
// 관리자 비밀번호 교체 (유저 지정 비밀번호로)
// ============================================================

function _ob_set_admin_password($provision_result, $adminId, $adminPass) {
    if (!isset($provision_result['tenant_id'])) return;

    // 테넌트 DB 정보 조회
    global $_OB_LINK;
    $tid = (int)$provision_result['tenant_id'];
    $res = mysqli_query($_OB_LINK, "SELECT db_host, db_name, db_user, db_pass FROM tenants WHERE id = {$tid}");
    $tenant = $res ? mysqli_fetch_assoc($res) : null;
    if (!$tenant) return;

    $host = !empty($tenant['db_host']) ? $tenant['db_host'] : MG_MASTER_DB_HOST;
    $link = @mysqli_connect($host, $tenant['db_user'], $tenant['db_pass'], $tenant['db_name']);
    if (!$link) return;

    mysqli_set_charset($link, 'utf8mb4');

    // PBKDF2 해시 생성 (gnuboard 호환)
    $salt = bin2hex(random_bytes(16));
    $iterations = 12000;
    $hash = base64_encode(hash_pbkdf2('sha256', $adminPass, $salt, $iterations, 32, true));
    $password_hash = "sha256:{$iterations}:{$salt}:{$hash}";

    $admin_escaped = mysqli_real_escape_string($link, $adminId);
    $pass_escaped = mysqli_real_escape_string($link, $password_hash);

    mysqli_query($link, "UPDATE g5_member SET mb_password = '{$pass_escaped}' WHERE mb_id = '{$admin_escaped}'");
    mysqli_close($link);
}

// ============================================================
// 응답 헬퍼
// ============================================================

function _ob_json_response($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function _ob_json_error($message) {
    _ob_json_response(['success' => false, 'error' => $message]);
}
