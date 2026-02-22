<?php
/**
 * Morgan Edition - 회원 존재 확인 (AJAX)
 */

include_once('./_common.php');

header('Content-Type: application/json');

if ($is_guest) {
    echo json_encode(['exists' => false]);
    exit;
}

$mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';

if (!$mb_id) {
    echo json_encode(['exists' => false]);
    exit;
}

$mb = get_member($mb_id);
if ($mb['mb_id']) {
    echo json_encode(['exists' => true, 'mb_nick' => $mb['mb_nick']]);
} else {
    echo json_encode(['exists' => false]);
}
