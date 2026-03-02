<?php
/**
 * Morgan Super Admin — 로그아웃
 */

$g5_path['path'] = realpath(__DIR__ . '/../../');
include_once($g5_path['path'] . '/config.php');
define('G5_IS_SUPER_ADMIN', true);

session_name('MG_SUPER_ADMIN');
session_start();

$_SESSION = array();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

header('Location: ./login.php');
exit;
