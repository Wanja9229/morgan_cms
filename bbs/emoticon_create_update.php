<?php
/**
 * Morgan Edition - 이모티콘 셋 제작 처리
 */

include_once('./_common.php');

if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

if (!mg_config('emoticon_use', '1') || !mg_config('emoticon_creator_use', '1')) {
    alert('이모티콘 제작 기능이 비활성화되어 있습니다.');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$es_id = isset($_POST['es_id']) ? (int)$_POST['es_id'] : 0;

// === 판매 토글 (AJAX) ===
if ($action === 'toggle_sale') {
    $es_use = isset($_POST['es_use']) ? (int)$_POST['es_use'] : 0;
    if ($es_id <= 0) exit;
    $set = mg_get_emoticon_set($es_id);
    if (!$set || $set['es_creator_id'] !== $member['mb_id'] || $set['es_status'] !== 'approved') exit;
    sql_query("UPDATE {$g5['mg_emoticon_set_table']} SET es_use = {$es_use} WHERE es_id = {$es_id}");
    exit;
}

$es_name = isset($_POST['es_name']) ? trim($_POST['es_name']) : '';
$es_desc = isset($_POST['es_desc']) ? trim($_POST['es_desc']) : '';
$es_price = isset($_POST['es_price']) ? max(0, (int)$_POST['es_price']) : 0;

// 업로드 디렉토리
if (!is_dir(MG_EMOTICON_PATH)) {
    @mkdir(MG_EMOTICON_PATH, 0755, true);
}

if (!$es_name) {
    alert('셋 이름을 입력해주세요.');
}

// === 신규 등록 ===
if ($action === 'insert') {
    // 등록권 확인
    $reg_check = mg_can_create_emoticon($member['mb_id']);
    if (!$reg_check['can']) {
        alert('이모티콘 등록권이 필요합니다.');
    }

    $data = array(
        'es_name' => $es_name,
        'es_desc' => $es_desc,
        'es_price' => $es_price,
        'es_preview' => '',
    );
    $es_id = mg_create_emoticon_set($member['mb_id'], $data);

    if (!$es_id) {
        alert('이모티콘 셋 등록에 실패했습니다.');
    }
}
// === 수정 ===
elseif ($action === 'update') {
    if ($es_id <= 0) {
        alert('잘못된 접근입니다.');
    }

    $set = mg_get_emoticon_set($es_id);
    if (!$set || $set['es_creator_id'] !== $member['mb_id']) {
        alert('접근 권한이 없습니다.');
    }
    if (!in_array($set['es_status'], array('draft', 'rejected'))) {
        alert('수정할 수 없는 상태입니다.');
    }

    $es_name_esc = sql_real_escape_string($es_name);
    $es_desc_esc = sql_real_escape_string($es_desc);
    sql_query("UPDATE {$g5['mg_emoticon_set_table']}
               SET es_name = '{$es_name_esc}',
                   es_desc = '{$es_desc_esc}',
                   es_price = {$es_price}
               WHERE es_id = {$es_id} AND es_creator_id = '".sql_real_escape_string($member['mb_id'])."'");
}
// === 심사 요청 ===
elseif ($action === 'submit') {
    if ($es_id <= 0) {
        alert('잘못된 접근입니다.');
    }

    $set = mg_get_emoticon_set($es_id);
    if (!$set || $set['es_creator_id'] !== $member['mb_id']) {
        alert('접근 권한이 없습니다.');
    }

    // draft 상태에서 처음 심사 요청 시 등록권 소모
    if ($set['es_status'] === 'draft') {
        if (!mg_consume_emoticon_reg($member['mb_id'])) {
            alert('이모티콘 등록권 소모에 실패했습니다.');
        }
    }

    $result = mg_submit_emoticon_set($es_id);
    if ($result['success']) {
        goto_url(G5_BBS_URL.'/inventory.php?tab=emoticon');
    } else {
        alert($result['message']);
    }
    exit;
}
else {
    alert('잘못된 접근입니다.');
}

// 셋 디렉토리
$set_dir = MG_EMOTICON_PATH . '/' . $es_id;
if (!is_dir($set_dir)) {
    @mkdir($set_dir, 0755, true);
}
$set_url = MG_EMOTICON_URL . '/' . $es_id;

// === 미리보기 이미지 처리 ===
if (isset($_FILES['es_preview_file']) && $_FILES['es_preview_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['es_preview_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (in_array($ext, $allowed) && $file['size'] <= mg_upload_max_file()) {
        $old_set = mg_get_emoticon_set($es_id);
        if ($old_set && $old_set['es_preview']) {
            $old_file = str_replace(MG_EMOTICON_URL, MG_EMOTICON_PATH, $old_set['es_preview']);
            if (file_exists($old_file)) @unlink($old_file);
        }

        $new_name = 'preview_' . time() . '.' . $ext;
        $target = $set_dir . '/' . $new_name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            @chmod($target, 0644);
            $preview_url = $set_url . '/' . $new_name;
            sql_query("UPDATE {$g5['mg_emoticon_set_table']}
                       SET es_preview = '".sql_real_escape_string($preview_url)."'
                       WHERE es_id = {$es_id}");
        }
    }
}

// === 기존 이모티콘 코드 업데이트 ===
if (isset($_POST['em_codes']) && is_array($_POST['em_codes'])) {
    foreach ($_POST['em_codes'] as $em_id => $code) {
        $em_id = (int)$em_id;
        $code = trim($code);
        if ($em_id > 0 && $code) {
            $code_esc = sql_real_escape_string($code);
            sql_query("UPDATE {$g5['mg_emoticon_table']}
                       SET em_code = '{$code_esc}'
                       WHERE em_id = {$em_id} AND es_id = {$es_id}");
        }
    }
}

// === 이모티콘 삭제 ===
$delete_em_ids = isset($_POST['delete_em_ids']) ? $_POST['delete_em_ids'] : '';
if ($delete_em_ids) {
    $del_ids = array_map('intval', explode(',', $delete_em_ids));
    foreach ($del_ids as $del_id) {
        if ($del_id > 0) {
            $em = sql_fetch("SELECT em_image FROM {$g5['mg_emoticon_table']} WHERE em_id = {$del_id} AND es_id = {$es_id}");
            if ($em && $em['em_image']) {
                $file = str_replace(MG_EMOTICON_URL, MG_EMOTICON_PATH, $em['em_image']);
                if (file_exists($file)) @unlink($file);
            }
            sql_query("DELETE FROM {$g5['mg_emoticon_table']} WHERE em_id = {$del_id} AND es_id = {$es_id}");
        }
    }
}

// === 새 이모티콘 업로드 ===
if (isset($_FILES['emoticon_files'])) {
    $files = $_FILES['emoticon_files'];
    $new_codes = isset($_POST['new_em_codes']) ? $_POST['new_em_codes'] : array();
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $max_file_size = mg_upload_max_icon();
    $max_count = (int)mg_config('emoticon_max_count', 30);

    // 현재 이모티콘 수 확인
    $current_count = (int)sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_emoticon_table']} WHERE es_id = {$es_id}")['cnt'];

    $file_count = is_array($files['name']) ? count($files['name']) : 0;
    $order_row = sql_fetch("SELECT COALESCE(MAX(em_order),0)+1 as next_order FROM {$g5['mg_emoticon_table']} WHERE es_id = {$es_id}");
    $order = (int)$order_row['next_order'];

    for ($i = 0; $i < $file_count; $i++) {
        if ($current_count >= $max_count) break;
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) continue;
        if ($files['size'][$i] > $max_file_size) continue;

        $new_name = $es_id . '_' . time() . '_' . $i . '.' . $ext;
        $target = $set_dir . '/' . $new_name;

        if (move_uploaded_file($files['tmp_name'][$i], $target)) {
            @chmod($target, 0644);
            $image_url = $set_url . '/' . $new_name;

            // 코드 결정
            $base = pathinfo($files['name'][$i], PATHINFO_FILENAME);
            $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base);
            $code = isset($new_codes[$i]) && trim($new_codes[$i]) ? trim($new_codes[$i]) : ':' . strtolower($base) . ':';

            if (mg_emoticon_code_exists($code)) {
                $code = ':' . strtolower($base) . '_' . $i . ':';
            }

            mg_add_emoticon($es_id, $code, $image_url, $order);
            $order++;
            $current_count++;
        }
    }
}

goto_url(G5_BBS_URL.'/emoticon_create.php?es_id=' . $es_id);
