<?php
/**
 * Morgan Edition API - Test Endpoint
 *
 * GET /api/common/test
 *
 * API 연결 테스트용
 */

if (!defined('MG_API')) exit;

api_require_method('GET');

api_success([
    'timestamp' => date('Y-m-d H:i:s'),
    'server_time' => time(),
    'php_version' => PHP_VERSION,
    'is_member' => $is_member ? true : false,
    'is_admin' => $is_admin ? true : false,
], 'API 연결 성공');
