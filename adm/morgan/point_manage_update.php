<?php
/**
 * Morgan Edition - 포인트 지급/차감 처리
 */

$sub_menu = "800550";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// 재화 설정 저장
if ($mode == 'settings') {
    $point_name = isset($_POST['point_name']) ? trim($_POST['point_name']) : 'G';
    $point_unit = isset($_POST['point_unit']) ? trim($_POST['point_unit']) : '';

    // 빈 값이면 기본값
    if (!$point_name) {
        $point_name = 'G';
    }

    mg_config_set('point_name', $point_name);
    mg_config_set('point_unit', $point_unit);

    goto_url('./point_manage.php?tab=settings');
}

// 포인트 지급/차감 (AJAX)
if ($mode == 'give_ajax') {
    header('Content-Type: application/json');

    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
    $po_point = isset($_POST['po_point']) ? (int)$_POST['po_point'] : 0;
    $po_content = isset($_POST['po_content']) ? trim($_POST['po_content']) : '';

    // 유효성 검사
    if (!$mb_id) {
        echo json_encode(['success' => false, 'message' => '회원을 선택해주세요.']);
        exit;
    }

    if ($po_point == 0) {
        echo json_encode(['success' => false, 'message' => '포인트를 입력해주세요.']);
        exit;
    }

    if (!$po_content) {
        $po_content = '관리자 지급';
    }

    // 회원 존재 확인
    $mb = get_member($mb_id);
    if (!$mb['mb_id']) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 회원입니다.']);
        exit;
    }

    // 차감 시 보유 포인트 확인
    if ($po_point < 0 && ($mb['mb_point'] + $po_point) < 0) {
        echo json_encode(['success' => false, 'message' => '회원의 보유 포인트가 부족합니다. (현재: ' . number_format($mb['mb_point']) . ')']);
        exit;
    }

    // 포인트 지급/차감
    $po_content_final = '[관리자] ' . $po_content;
    insert_point($mb_id, $po_point, $po_content_final, 'admin', $member['mb_id'], '관리자 수동');

    // 새 포인트 조회
    $new_mb = get_member($mb_id);
    $new_point = number_format($new_mb['mb_point']);

    $action = $po_point > 0 ? '지급' : '차감';
    echo json_encode([
        'success' => true,
        'message' => $mb['mb_nick'] . ' 회원에게 ' . number_format($po_point) . ' 포인트가 ' . $action . '되었습니다.',
        'new_point' => $new_point
    ]);
    exit;
}

// 포인트 지급/차감 (일반 폼)
if ($mode == 'give') {
    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
    $po_point = isset($_POST['po_point']) ? (int)$_POST['po_point'] : 0;
    $po_content = isset($_POST['po_content']) ? trim($_POST['po_content']) : '';

    // 유효성 검사
    if (!$mb_id) {
        alert('회원을 선택해주세요.');
        exit;
    }

    if ($po_point == 0) {
        alert('포인트를 입력해주세요.');
        exit;
    }

    if (!$po_content) {
        alert('내용을 입력해주세요.');
        exit;
    }

    // 회원 존재 확인
    $mb = get_member($mb_id);
    if (!$mb['mb_id']) {
        alert('존재하지 않는 회원입니다.');
        exit;
    }

    // 차감 시 보유 포인트 확인
    if ($po_point < 0 && ($mb['mb_point'] + $po_point) < 0) {
        alert('회원의 보유 포인트가 부족합니다.\\n현재 보유: ' . number_format($mb['mb_point']) . 'P');
        exit;
    }

    // 포인트 지급/차감 (그누보드 함수 사용)
    $po_content_final = '[관리자] ' . $po_content;
    insert_point($mb_id, $po_point, $po_content_final, 'admin', $member['mb_id'], '관리자 수동');

    // 로그
    $action = $po_point > 0 ? '지급' : '차감';
    $log_msg = "관리자 포인트 $action: {$mb_id}에게 " . number_format($po_point) . "P ($po_content)";

    alert_close("포인트가 " . ($po_point > 0 ? '지급' : '차감') . "되었습니다.\\n\\n회원: {$mb['mb_nick']} ({$mb_id})\\n포인트: " . number_format($po_point) . "P\\n내용: $po_content");
}

goto_url('./point_manage.php');
