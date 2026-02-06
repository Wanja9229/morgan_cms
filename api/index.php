<?php
/**
 * Morgan Edition API - Router
 *
 * 모든 API 요청의 진입점
 *
 * URL 구조:
 * /api/                    -> 이 파일
 * /api/member/login        -> /api/member/login.php
 * /api/board/list          -> /api/board/list.php
 * /api/character/1         -> /api/character/view.php?id=1
 */

// API 설정 로드
require_once __DIR__.'/config.php';

// 요청 경로 파싱
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api';

// 쿼리스트링 제거
$path = parse_url($request_uri, PHP_URL_PATH);

// base_path 제거
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// 앞뒤 슬래시 정리
$path = trim($path, '/');

// 빈 경로면 API 정보 반환
if (empty($path)) {
    api_success([
        'name' => 'Morgan Edition API',
        'version' => MG_API_VERSION,
        'endpoints' => [
            'member' => [
                'POST /api/member/login' => '로그인',
                'POST /api/member/logout' => '로그아웃',
                'POST /api/member/register' => '회원가입',
                'GET /api/member/me' => '내 정보',
                'PUT /api/member/me' => '정보 수정',
            ],
            'board' => [
                'GET /api/board/{table}/list' => '게시글 목록',
                'GET /api/board/{table}/view/{id}' => '게시글 상세',
                'POST /api/board/{table}/write' => '게시글 작성',
                'PUT /api/board/{table}/update/{id}' => '게시글 수정',
                'DELETE /api/board/{table}/delete/{id}' => '게시글 삭제',
            ],
            'character' => [
                'GET /api/character/list' => '내 캐릭터 목록',
                'GET /api/character/{id}' => '캐릭터 상세',
                'POST /api/character/create' => '캐릭터 생성',
                'PUT /api/character/{id}' => '캐릭터 수정',
                'DELETE /api/character/{id}' => '캐릭터 삭제',
            ],
        ]
    ], 'Morgan Edition API');
    exit;
}

// 경로를 세그먼트로 분리
$segments = explode('/', $path);
$resource = $segments[0] ?? '';
$action = $segments[1] ?? 'index';
$id = $segments[2] ?? null;

// 허용된 리소스
$allowed_resources = ['member', 'board', 'character', 'common'];

if (!in_array($resource, $allowed_resources)) {
    api_error('NOT_FOUND', '존재하지 않는 API 엔드포인트입니다.', 404);
}

// 파일 경로 결정
$api_file = __DIR__.'/'.$resource.'/'.$action.'.php';

// 파일이 없으면 404
if (!file_exists($api_file)) {
    api_error('NOT_FOUND', '존재하지 않는 API 엔드포인트입니다: '.$resource.'/'.$action, 404);
}

// ID가 있으면 전역 변수로 설정
if ($id !== null) {
    $GLOBALS['api_id'] = $id;
    $_GET['id'] = $id;
}

// API 파일 실행
require_once $api_file;
