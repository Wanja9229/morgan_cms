<?php
/**
 * Morgan Edition - 타임라인 저장 처리 (시대 + 이벤트)
 */

$sub_menu = "800175";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    goto_url('./lore_timeline.php');
}

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// ==========================================
// 시대 추가
// ==========================================
if ($mode == 'era_add') {
    $le_name = sql_real_escape_string(trim($_POST['le_name']));
    $le_period = sql_real_escape_string(trim($_POST['le_period']));
    $le_desc = sql_real_escape_string(trim($_POST['le_desc'] ?? ''));
    $le_order = (int)$_POST['le_order'];
    $le_use = isset($_POST['le_use']) ? 1 : 0;

    if (!$le_name) {
        alert('시대명을 입력해주세요.', './lore_timeline.php');
    }

    sql_query("INSERT INTO {$g5['mg_lore_era_table']} (le_name, le_period, le_desc, le_order, le_use) VALUES ('{$le_name}', '{$le_period}', '{$le_desc}', {$le_order}, {$le_use})");
    goto_url('./lore_timeline.php');
}

// ==========================================
// 시대 수정
// ==========================================
if ($mode == 'era_edit') {
    $le_id = (int)$_POST['le_id'];
    $le_name = sql_real_escape_string(trim($_POST['le_name']));
    $le_period = sql_real_escape_string(trim($_POST['le_period']));
    $le_desc = sql_real_escape_string(trim($_POST['le_desc'] ?? ''));
    $le_order = (int)$_POST['le_order'];
    $le_use = isset($_POST['le_use']) ? 1 : 0;

    if (!$le_id) {
        alert('잘못된 요청입니다.', './lore_timeline.php');
    }
    if (!$le_name) {
        alert('시대명을 입력해주세요.', './lore_timeline.php');
    }

    sql_query("UPDATE {$g5['mg_lore_era_table']} SET le_name = '{$le_name}', le_period = '{$le_period}', le_desc = '{$le_desc}', le_order = {$le_order}, le_use = {$le_use} WHERE le_id = {$le_id}");
    goto_url('./lore_timeline.php');
}

// ==========================================
// 시대 삭제
// ==========================================
if ($mode == 'era_delete') {
    $le_id = (int)$_POST['le_id'];
    if (!$le_id) {
        alert('잘못된 요청입니다.', './lore_timeline.php');
    }

    // 이벤트가 있는지 확인
    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_lore_event_table']} WHERE le_id = {$le_id}");
    if ((int)$cnt['cnt'] > 0) {
        alert('이벤트가 존재하는 시대는 삭제할 수 없습니다. 이벤트를 먼저 삭제해주세요.', './lore_timeline.php');
    }

    sql_query("DELETE FROM {$g5['mg_lore_era_table']} WHERE le_id = {$le_id}");
    goto_url('./lore_timeline.php');
}

// ==========================================
// 이벤트 추가
// ==========================================
if ($mode == 'event_add') {
    $le_id = (int)$_POST['le_id'];
    $la_id = (int)($_POST['la_id'] ?? 0);
    $lv_year = sql_real_escape_string(trim($_POST['lv_year']));
    $lv_title = sql_real_escape_string(trim($_POST['lv_title']));
    $lv_content = sql_real_escape_string(trim($_POST['lv_content']));
    $lv_order = (int)$_POST['lv_order'];
    $lv_is_major = isset($_POST['lv_is_major']) ? 1 : 0;
    $lv_use = isset($_POST['lv_use']) ? 1 : 0;

    if (!$le_id) {
        alert('시대를 선택해주세요.', './lore_timeline.php');
    }
    if (!$lv_title) {
        alert('이벤트 제목을 입력해주세요.', './lore_timeline.php');
    }

    // 이미지 처리
    $lv_image = '';
    if (isset($_FILES['lv_image']) && $_FILES['lv_image']['error'] == UPLOAD_ERR_OK) {
        $upload = mg_upload_lore_image($_FILES['lv_image'], 'event', 0);
        if ($upload['success']) {
            $lv_image = $upload['url'];
        }
    }
    $lv_image_esc = sql_real_escape_string($lv_image);

    sql_query("INSERT INTO {$g5['mg_lore_event_table']}
        (le_id, la_id, lv_year, lv_title, lv_content, lv_image, lv_is_major, lv_order, lv_use)
        VALUES
        ({$le_id}, {$la_id}, '{$lv_year}', '{$lv_title}', '{$lv_content}', '{$lv_image_esc}', {$lv_is_major}, {$lv_order}, {$lv_use})");

    goto_url('./lore_timeline.php');
}

// ==========================================
// 이벤트 수정
// ==========================================
if ($mode == 'event_edit') {
    $lv_id = (int)$_POST['lv_id'];
    $le_id = (int)$_POST['le_id'];
    $la_id = (int)($_POST['la_id'] ?? 0);
    $lv_year = sql_real_escape_string(trim($_POST['lv_year']));
    $lv_title = sql_real_escape_string(trim($_POST['lv_title']));
    $lv_content = sql_real_escape_string(trim($_POST['lv_content']));
    $lv_order = (int)$_POST['lv_order'];
    $lv_is_major = isset($_POST['lv_is_major']) ? 1 : 0;
    $lv_use = isset($_POST['lv_use']) ? 1 : 0;

    if (!$lv_id) {
        alert('잘못된 요청입니다.', './lore_timeline.php');
    }
    if (!$lv_title) {
        alert('이벤트 제목을 입력해주세요.', './lore_timeline.php');
    }

    // 기존 이벤트 조회
    $existing = sql_fetch("SELECT * FROM {$g5['mg_lore_event_table']} WHERE lv_id = {$lv_id}");
    if (!$existing['lv_id']) {
        alert('이벤트를 찾을 수 없습니다.', './lore_timeline.php');
    }

    // 이미지 처리
    $lv_image = isset($_POST['lv_image_url']) ? trim($_POST['lv_image_url']) : $existing['lv_image'];

    // 새 파일 업로드가 있으면 교체
    if (isset($_FILES['lv_image']) && $_FILES['lv_image']['error'] == UPLOAD_ERR_OK) {
        $upload = mg_upload_lore_image($_FILES['lv_image'], 'event', $lv_id);
        if ($upload['success']) {
            // 기존 이미지 파일 삭제
            if ($existing['lv_image']) {
                $old_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $existing['lv_image']);
                if (file_exists($old_path)) @unlink($old_path);
            }
            $lv_image = $upload['url'];
        }
    }

    // lv_image_url이 비어있으면 이미지 제거로 판단
    if (isset($_POST['lv_image_url']) && $_POST['lv_image_url'] === '' && (!isset($_FILES['lv_image']) || $_FILES['lv_image']['error'] != UPLOAD_ERR_OK)) {
        // 기존 이미지 파일 삭제
        if ($existing['lv_image']) {
            $old_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $existing['lv_image']);
            if (file_exists($old_path)) @unlink($old_path);
        }
        $lv_image = '';
    }

    $lv_image_esc = sql_real_escape_string($lv_image);

    sql_query("UPDATE {$g5['mg_lore_event_table']} SET
        le_id = {$le_id},
        la_id = {$la_id},
        lv_year = '{$lv_year}',
        lv_title = '{$lv_title}',
        lv_content = '{$lv_content}',
        lv_image = '{$lv_image_esc}',
        lv_is_major = {$lv_is_major},
        lv_order = {$lv_order},
        lv_use = {$lv_use}
        WHERE lv_id = {$lv_id}");

    goto_url('./lore_timeline.php');
}

// ==========================================
// 이벤트 삭제
// ==========================================
if ($mode == 'event_delete') {
    $lv_id = (int)$_POST['lv_id'];
    if (!$lv_id) {
        alert('잘못된 요청입니다.', './lore_timeline.php');
    }

    // 이미지 파일 삭제
    $ev = sql_fetch("SELECT lv_image FROM {$g5['mg_lore_event_table']} WHERE lv_id = {$lv_id}");
    if ($ev['lv_image']) {
        $img_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $ev['lv_image']);
        if (file_exists($img_path)) @unlink($img_path);
    }

    sql_query("DELETE FROM {$g5['mg_lore_event_table']} WHERE lv_id = {$lv_id}");
    goto_url('./lore_timeline.php');
}

// ==========================================
// 이벤트 순서 변경 (AJAX)
// ==========================================
if ($mode == 'event_reorder') {
    header('Content-Type: application/json; charset=utf-8');
    $le_id = (int)($_POST['le_id'] ?? 0);
    $order = isset($_POST['order']) ? $_POST['order'] : array();
    if (!is_array($order) || empty($order)) {
        echo json_encode(array('success' => false, 'message' => '순서 데이터가 없습니다.'));
        exit;
    }
    foreach ($order as $i => $lv_id) {
        $lv_id = (int)$lv_id;
        if ($lv_id > 0) {
            $sql = "UPDATE {$g5['mg_lore_event_table']} SET lv_order = {$i}";
            if ($le_id > 0) $sql .= ", le_id = {$le_id}";
            $sql .= " WHERE lv_id = {$lv_id}";
            sql_query($sql);
        }
    }
    echo json_encode(array('success' => true));
    exit;
}

// ==========================================
// 시대 순서 변경 (AJAX)
// ==========================================
if ($mode == 'era_reorder') {
    header('Content-Type: application/json; charset=utf-8');
    $order = isset($_POST['order']) ? $_POST['order'] : array();
    if (!is_array($order) || empty($order)) {
        echo json_encode(array('success' => false, 'message' => '순서 데이터가 없습니다.'));
        exit;
    }
    foreach ($order as $i => $le_id) {
        $le_id = (int)$le_id;
        if ($le_id > 0) {
            sql_query("UPDATE {$g5['mg_lore_era_table']} SET le_order = {$i} WHERE le_id = {$le_id}");
        }
    }
    echo json_encode(array('success' => true));
    exit;
}

// ==========================================
// 페이지 설명 저장 (AJAX)
// ==========================================
if ($mode == 'update_desc') {
    header('Content-Type: application/json; charset=utf-8');
    $desc = isset($_POST['lore_timeline_desc']) ? trim($_POST['lore_timeline_desc']) : '';
    mg_set_config('lore_timeline_desc', $desc);
    echo json_encode(array('success' => true));
    exit;
}

// 알 수 없는 mode
goto_url('./lore_timeline.php');
