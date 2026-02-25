<?php
/**
 * Morgan Edition - 보상 설정 저장
 */

$sub_menu = "800570";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// ======================================
// 게시판별 보상 설정 (AJAX)
// ======================================
if ($mode == 'board_reward') {
    header('Content-Type: application/json');

    $bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
    if (!$bo_table) {
        echo json_encode(['success' => false, 'message' => '게시판을 선택해주세요.']);
        exit;
    }

    // 게시판 존재 확인
    $board = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
    if (!$board['bo_table']) {
        echo json_encode(['success' => false, 'message' => '존재하지 않는 게시판입니다.']);
        exit;
    }

    $br_mode = isset($_POST['br_mode']) ? $_POST['br_mode'] : 'off';
    if (!in_array($br_mode, array('auto', 'request', 'off'))) $br_mode = 'off';

    $br_point = max(0, (int)($_POST['br_point'] ?? 0));
    $br_material_use = ($_POST['br_material_use'] ?? '0') == '1' ? 1 : 0;
    $br_material_chance = max(0, min(100, (int)($_POST['br_material_chance'] ?? 30)));
    // 글 작성 재료 보상 (JSON)
    $br_material_list = isset($_POST['br_material_list']) ? $_POST['br_material_list'] : '';
    // 댓글 작성 재료 보상 (JSON)
    $br_material_comment = isset($_POST['br_material_comment']) ? $_POST['br_material_comment'] : '';
    $br_daily_limit = max(0, (int)($_POST['br_daily_limit'] ?? 0));
    $br_like_use = ($_POST['br_like_use'] ?? '1') == '1' ? 1 : 0;
    $br_dice_use = ($_POST['br_dice_use'] ?? '0') == '1' ? 1 : 0;
    $br_dice_once = ($_POST['br_dice_once'] ?? '1') == '1' ? 1 : 0;
    $br_dice_max = max(1, (int)($_POST['br_dice_max'] ?? 100));

    // JSON 유효성 체크 (글 작성 재료)
    if ($br_material_list) {
        $decoded = json_decode($br_material_list, true);
        if (!is_array($decoded)) $br_material_list = '';
    }
    // JSON 유효성 체크 (댓글 재료)
    if ($br_material_comment) {
        $decoded_c = json_decode($br_material_comment, true);
        if (!is_array($decoded_c)) $br_material_comment = '';
    }

    $bo_table_esc = sql_real_escape_string($bo_table);
    $br_material_list_esc = sql_real_escape_string($br_material_list);
    $br_material_comment_esc = sql_real_escape_string($br_material_comment);

    $sql = "INSERT INTO {$g5['mg_board_reward_table']}
            (bo_table, br_mode, br_point, br_bonus_500, br_bonus_1000, br_bonus_image,
             br_material_use, br_material_chance, br_material_list, br_material_comment, br_daily_limit, br_like_use,
             br_dice_use, br_dice_once, br_dice_max)
            VALUES
            ('{$bo_table_esc}', '{$br_mode}', {$br_point}, 0, 0, 0,
             {$br_material_use}, {$br_material_chance}, '{$br_material_list_esc}', '{$br_material_comment_esc}', {$br_daily_limit}, {$br_like_use},
             {$br_dice_use}, {$br_dice_once}, {$br_dice_max})
            ON DUPLICATE KEY UPDATE
            br_mode = '{$br_mode}',
            br_point = {$br_point},
            br_bonus_500 = 0,
            br_bonus_1000 = 0,
            br_bonus_image = 0,
            br_material_use = {$br_material_use},
            br_material_chance = {$br_material_chance},
            br_material_list = '{$br_material_list_esc}',
            br_material_comment = '{$br_material_comment_esc}',
            br_daily_limit = {$br_daily_limit},
            br_like_use = {$br_like_use},
            br_dice_use = {$br_dice_use},
            br_dice_once = {$br_dice_once},
            br_dice_max = {$br_dice_max}";

    sql_query($sql);

    echo json_encode(['success' => true, 'message' => '저장되었습니다.']);
    exit;
}

// ======================================
// 활동 보상 전역 설정
// ======================================
if ($mode == 'activity') {
    $keys = array(
        'rp_create_cost',
        'rp_reply_batch_count',
        'rp_reply_batch_point',
        'rp_complete_point',
        'rp_complete_min_mutual',
        'rp_auto_complete_days',
        'like_daily_limit',
        'like_giver_point',
        'like_receiver_point',
    );

    foreach ($keys as $key) {
        $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';
        mg_set_config($key, $value);
    }

    goto_url('./reward.php?tab=activity');
}

// ======================================
// 역극 보상 회수 (AJAX)
// ======================================
if ($mode == 'revoke_completion') {
    header('Content-Type: application/json');

    $rc_id = isset($_POST['rc_id']) ? (int)$_POST['rc_id'] : 0;
    if (!$rc_id) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        exit;
    }

    $rc = sql_fetch("SELECT * FROM {$g5['mg_rp_completion_table']} WHERE rc_id = {$rc_id}");
    if (!$rc || !$rc['rc_id']) {
        echo json_encode(['success' => false, 'message' => '완결 기록을 찾을 수 없습니다.']);
        exit;
    }

    if ($rc['rc_status'] == 'revoked') {
        echo json_encode(['success' => false, 'message' => '이미 회수된 보상입니다.']);
        exit;
    }

    if (!$rc['rc_rewarded'] || (int)$rc['rc_point'] <= 0) {
        echo json_encode(['success' => false, 'message' => '회수할 보상이 없습니다.']);
        exit;
    }

    // 포인트 차감
    $point = (int)$rc['rc_point'];
    insert_point($rc['mb_id'], -$point, '역극 완결 보상 회수 (관리자)', 'mg_rp_completion', $rc['rt_id'], '회수');

    // 상태 변경
    sql_query("UPDATE {$g5['mg_rp_completion_table']} SET rc_status = 'revoked' WHERE rc_id = {$rc_id}");

    echo json_encode(['success' => true, 'message' => "{$point}P를 회수했습니다."]);
    exit;
}

// ======================================
// 보상 유형 저장 (AJAX)
// ======================================
if ($mode == 'reward_type_save') {
    header('Content-Type: application/json');

    $bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
    $rwt_name = isset($_POST['rwt_name']) ? trim($_POST['rwt_name']) : '';
    $rwt_point = max(0, (int)($_POST['rwt_point'] ?? 0));
    $rwt_desc = isset($_POST['rwt_desc']) ? trim($_POST['rwt_desc']) : '';

    if (!$rwt_name) {
        echo json_encode(['success' => false, 'message' => '유형 이름을 입력해주세요.']);
        exit;
    }

    $bo_esc = $bo_table ? "'".sql_real_escape_string($bo_table)."'" : 'NULL';
    $name_esc = sql_real_escape_string($rwt_name);
    $desc_esc = sql_real_escape_string($rwt_desc);

    // 정렬 순서: 마지막 + 1
    $last = sql_fetch("SELECT MAX(rwt_order) as max_order FROM {$g5['mg_reward_type_table']}");
    $order = ((int)($last['max_order'] ?? 0)) + 1;

    sql_query("INSERT INTO {$g5['mg_reward_type_table']}
        (bo_table, rwt_name, rwt_point, rwt_desc, rwt_order)
        VALUES ({$bo_esc}, '{$name_esc}', {$rwt_point}, '{$desc_esc}', {$order})");

    $rwt_id = sql_insert_id();
    echo json_encode(['success' => true, 'message' => '추가되었습니다.', 'rwt_id' => $rwt_id]);
    exit;
}

// ======================================
// 보상 유형 삭제 (AJAX)
// ======================================
if ($mode == 'reward_type_delete') {
    header('Content-Type: application/json');

    $rwt_id = isset($_POST['rwt_id']) ? (int)$_POST['rwt_id'] : 0;
    if (!$rwt_id) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        exit;
    }

    // pending 큐 확인
    $pending = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_reward_queue_table']}
        WHERE rwt_id = {$rwt_id} AND rq_status = 'pending'");
    if ((int)$pending['cnt'] > 0) {
        echo json_encode(['success' => false, 'message' => '대기 중인 요청이 있어 삭제할 수 없습니다. ('.$pending['cnt'].'건)']);
        exit;
    }

    sql_query("DELETE FROM {$g5['mg_reward_type_table']} WHERE rwt_id = {$rwt_id}");
    echo json_encode(['success' => true, 'message' => '삭제되었습니다.']);
    exit;
}

// ======================================
// 보상 승인 (AJAX)
// ======================================
if ($mode == 'approve_reward') {
    header('Content-Type: application/json');

    $rq_id = isset($_POST['rq_id']) ? (int)$_POST['rq_id'] : 0;
    if (!$rq_id) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        exit;
    }

    $options = array();
    if (isset($_POST['override_rwt_id']) && $_POST['override_rwt_id'] !== '') {
        $options['rwt_id'] = (int)$_POST['override_rwt_id'];
    }
    if (isset($_POST['override_point']) && $_POST['override_point'] !== '') {
        $options['point'] = (int)$_POST['override_point'];
    }
    if (isset($_POST['admin_note']) && trim($_POST['admin_note']) !== '') {
        $options['note'] = trim($_POST['admin_note']);
    }

    $result = mg_approve_reward($rq_id, $member['mb_id'], $options);
    echo json_encode($result);
    exit;
}

// ======================================
// 보상 반려 (AJAX)
// ======================================
if ($mode == 'reject_reward') {
    header('Content-Type: application/json');

    $rq_id = isset($_POST['rq_id']) ? (int)$_POST['rq_id'] : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
    if (!$rq_id) {
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        exit;
    }

    $result = mg_reject_reward($rq_id, $member['mb_id'], $reason);
    echo json_encode($result);
    exit;
}

// ======================================
// 일괄 승인 (AJAX)
// ======================================
if ($mode == 'batch_approve') {
    header('Content-Type: application/json');

    $rq_ids = isset($_POST['rq_ids']) ? $_POST['rq_ids'] : array();
    if (!is_array($rq_ids) || empty($rq_ids)) {
        echo json_encode(['success' => false, 'message' => '선택된 항목이 없습니다.']);
        exit;
    }

    $approved = 0;
    $failed = 0;
    foreach ($rq_ids as $rq_id) {
        $result = mg_approve_reward((int)$rq_id, $member['mb_id']);
        if ($result['success']) {
            $approved++;
        } else {
            $failed++;
        }
    }

    echo json_encode(['success' => $approved > 0, 'message' => "{$approved}건 승인, {$failed}건 실패"]);
    exit;
}

// 알 수 없는 mode
goto_url('./reward.php');
