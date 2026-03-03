<?php
/**
 * Morgan Super Admin — 독립 진입점
 *
 * gnuboard common.php를 사용하지 않는 독립 진입점.
 * 마스터 DB(mg_master)에 직접 연결하여 슈퍼 관리자 기능을 제공한다.
 */

// 디버그 (bootstrap 단계에서 500 에러 진단용)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// config.php가 _GNUBOARD_ 정의 + 경로 상수 설정
$g5_path['path'] = realpath(__DIR__ . '/../../');
if (!$g5_path['path']) {
    die('[SA] realpath failed: ' . __DIR__ . '/../../');
}
include_once($g5_path['path'] . '/config.php');

// ============================================================
// admin 서브도메인 접근 제한 — 비인가 경로에서는 존재 자체를 숨김
// ============================================================

$_sa_host = $_SERVER['HTTP_HOST'] ?? '';
// 포트 제거 (예: admin.moonveil.org:8080 → admin.moonveil.org)
$_sa_host = strtolower(preg_replace('/:\d+$/', '', $_sa_host));

if (strpos($_sa_host, 'admin.') !== 0) {
    http_response_code(404);
    // 일반 404와 구분 불가능하게 최소한의 응답
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
    exit;
}
unset($_sa_host);

// ============================================================
// 마스터 DB 설정 로드
// ============================================================

$_master_cfg = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_master_cfg)) {
    http_response_code(503);
    die('<!DOCTYPE html><html><body style="background:#1e1f22;color:#ef4444;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0"><div style="text-align:center"><h1>Setup Required</h1><p>dbconfig_master.php 파일이 없습니다.</p></div></body></html>');
}
include_once($_master_cfg);
unset($_master_cfg);

// ============================================================
// 마스터 DB 연결
// ============================================================

$_SA_LINK = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
if (!$_SA_LINK) {
    http_response_code(503);
    error_log('[Super Admin] Master DB connection failed: ' . mysqli_connect_error());
    die('<!DOCTYPE html><html><body style="background:#1e1f22;color:#ef4444;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0"><div style="text-align:center"><h1>Database Error</h1><p>마스터 DB에 연결할 수 없습니다.</p></div></body></html>');
}
mysqli_set_charset($_SA_LINK, 'utf8mb4');

// ============================================================
// 세션
// ============================================================

session_name('MG_SUPER_ADMIN');
session_start();

// ============================================================
// DB 헬퍼 함수
// ============================================================

function sa_query($sql) {
    global $_SA_LINK;
    $result = mysqli_query($_SA_LINK, $sql);
    if ($result === false) {
        error_log('[Super Admin] SQL Error: ' . mysqli_error($_SA_LINK) . ' / SQL: ' . $sql);
    }
    return $result;
}

function sa_fetch($result) {
    if (!$result) return false;
    return mysqli_fetch_assoc($result);
}

function sa_fetch_all($result) {
    if (!$result) return array();
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function sa_escape($str) {
    global $_SA_LINK;
    return mysqli_real_escape_string($_SA_LINK, (string)$str);
}

function sa_num_rows($result) {
    if (!$result) return 0;
    return mysqli_num_rows($result);
}

function sa_insert_id() {
    global $_SA_LINK;
    return mysqli_insert_id($_SA_LINK);
}

function sa_affected_rows() {
    global $_SA_LINK;
    return mysqli_affected_rows($_SA_LINK);
}

// ============================================================
// 인증 함수
// ============================================================

function sa_is_logged_in() {
    return !empty($_SESSION['sa_id']) && !empty($_SESSION['sa_username']);
}

function sa_check_auth() {
    if (!sa_is_logged_in()) {
        header('Location: ' . sa_url('login.php'));
        exit;
    }
}

function sa_get_admin() {
    if (!sa_is_logged_in()) return null;
    return array(
        'id'       => $_SESSION['sa_id'],
        'username' => $_SESSION['sa_username'],
        'email'    => $_SESSION['sa_email'] ?? '',
    );
}

function sa_login($admin_row) {
    $_SESSION['sa_id']       = (int)$admin_row['id'];
    $_SESSION['sa_username'] = $admin_row['username'];
    $_SESSION['sa_email']    = $admin_row['email'];
    session_regenerate_id(true);

    // last_login 갱신
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    sa_query("UPDATE super_admins SET last_login_at = NOW(), last_login_ip = '" . sa_escape($ip) . "' WHERE id = " . (int)$admin_row['id']);
}

function sa_logout() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ============================================================
// CSRF 토큰
// ============================================================

function sa_token() {
    if (empty($_SESSION['sa_csrf_token'])) {
        $_SESSION['sa_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['sa_csrf_token'];
}

function sa_token_field() {
    return '<input type="hidden" name="_token" value="' . sa_token() . '">';
}

function sa_verify_token() {
    $token = $_POST['_token'] ?? '';
    if (empty($token) || !hash_equals(sa_token(), $token)) {
        http_response_code(403);
        die('CSRF 토큰 검증 실패');
    }
}

// ============================================================
// 유틸리티
// ============================================================

function sa_url($path = '') {
    // tenant_bootstrap의 require()를 거치면 SCRIPT_NAME이 루트가 되므로 고정 경로 사용
    return '/adm/super/' . ltrim($path, '/');
}

function sa_alert($msg, $url = '') {
    echo '<script>';
    echo 'alert(' . json_encode($msg) . ');';
    if ($url) {
        echo 'location.href=' . json_encode($url) . ';';
    } else {
        echo 'history.back();';
    }
    echo '</script>';
    exit;
}

function sa_redirect($url, $msg = '') {
    if ($msg) {
        $_SESSION['sa_flash_msg'] = $msg;
    }
    header('Location: ' . $url);
    exit;
}

function sa_flash() {
    if (!empty($_SESSION['sa_flash_msg'])) {
        $msg = $_SESSION['sa_flash_msg'];
        unset($_SESSION['sa_flash_msg']);
        return $msg;
    }
    return '';
}

function sa_h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// 페이지 제목 (레이아웃에서 사용)
$sa_page_title = '';
