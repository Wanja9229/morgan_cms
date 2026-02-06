<?php
/**
 * Morgan Edition API - Configuration
 *
 * API 전역 설정 및 헬퍼 함수
 */

// 그누보드 로드
$g5_path = dirname(__DIR__);
include_once($g5_path.'/common.php');

// API 전용 상수
define('MG_API', true);
define('MG_API_VERSION', '1.0');

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// CORS 설정 (필요시 활성화)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

/**
 * API 성공 응답
 *
 * @param mixed $data 응답 데이터
 * @param string $message 메시지
 * @param int $code HTTP 상태 코드
 */
function api_success($data = null, $message = '성공', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * API 에러 응답
 *
 * @param string $error_code 에러 코드
 * @param string $message 에러 메시지
 * @param int $code HTTP 상태 코드
 * @param array $details 추가 정보
 */
function api_error($error_code, $message, $code = 400, $details = null) {
    http_response_code($code);
    $response = [
        'success' => false,
        'error' => [
            'code' => $error_code,
            'message' => $message
        ]
    ];
    if ($details !== null) {
        $response['error']['details'] = $details;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 로그인 필수 체크
 */
function api_require_login() {
    global $is_member, $member;
    if (!$is_member) {
        api_error('AUTH_REQUIRED', '로그인이 필요합니다.', 401);
    }
    return $member;
}

/**
 * 관리자 권한 체크
 */
function api_require_admin() {
    global $is_admin;
    api_require_login();
    if ($is_admin !== 'super') {
        api_error('FORBIDDEN', '관리자 권한이 필요합니다.', 403);
    }
}

/**
 * JSON 요청 바디 파싱
 *
 * @return array
 */
function api_get_json_body() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        api_error('INVALID_JSON', 'JSON 파싱 오류: '.json_last_error_msg(), 400);
    }
    return $data ?: [];
}

/**
 * 요청 메소드 체크
 *
 * @param string|array $allowed 허용 메소드
 */
function api_require_method($allowed) {
    $method = $_SERVER['REQUEST_METHOD'];

    // OPTIONS 요청은 CORS preflight
    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if (is_string($allowed)) {
        $allowed = [$allowed];
    }

    if (!in_array($method, $allowed)) {
        api_error('METHOD_NOT_ALLOWED', '허용되지 않는 메소드입니다.', 405);
    }
}

/**
 * CSRF 토큰 검증 (POST/PUT/DELETE 요청)
 */
function api_verify_csrf() {
    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        // AJAX 요청 확인
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            // 그누보드 토큰 검증
            $token = $_POST['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!check_token($token)) {
                // api_error('CSRF_ERROR', 'CSRF 토큰이 유효하지 않습니다.', 403);
                // 개발 중에는 비활성화
            }
        }
    }
}

/**
 * 페이지네이션 파라미터 가져오기
 *
 * @param int $default_limit 기본 limit
 * @param int $max_limit 최대 limit
 * @return array [page, limit, offset]
 */
function api_get_pagination($default_limit = 20, $max_limit = 100) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min($max_limit, max(1, intval($_GET['limit'] ?? $default_limit)));
    $offset = ($page - 1) * $limit;

    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset
    ];
}

/**
 * 페이지네이션 메타 정보 생성
 *
 * @param int $total 전체 개수
 * @param int $page 현재 페이지
 * @param int $limit 페이지당 개수
 * @return array
 */
function api_pagination_meta($total, $page, $limit) {
    return [
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit),
        'has_next' => ($page * $limit) < $total,
        'has_prev' => $page > 1
    ];
}
