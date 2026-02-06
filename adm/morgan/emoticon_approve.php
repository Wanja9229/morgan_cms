<?php
/**
 * Morgan Edition - 이모티콘 승인/반려 처리
 */

$sub_menu = "800950";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$es_id = isset($_POST['es_id']) ? (int)$_POST['es_id'] : 0;
$approve_action = isset($_POST['approve_action']) ? $_POST['approve_action'] : '';
$reject_reason = isset($_POST['reject_reason']) ? trim($_POST['reject_reason']) : '';

if ($es_id <= 0) {
    alert('잘못된 접근입니다.');
    exit;
}

if ($approve_action === 'approve') {
    $result = mg_approve_emoticon_set($es_id);
} elseif ($approve_action === 'reject') {
    $result = mg_reject_emoticon_set($es_id, $reject_reason);
} else {
    alert('잘못된 요청입니다.');
    exit;
}

if ($result['success']) {
    goto_url('./emoticon_list.php?status=pending');
} else {
    alert($result['message']);
}
