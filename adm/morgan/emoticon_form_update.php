<?php
/**
 * Morgan Edition - 이모티콘 셋 저장 처리 (관리자)
 */

$sub_menu = "800950";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';

// === 일괄 삭제 ===
if ($action === 'bulk_delete') {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();
    foreach ($chk as $es_id) {
        mg_delete_emoticon_set((int)$es_id);
    }
    goto_url('./emoticon_list.php');
    exit;
}

// 업로드 디렉토리 확인
if (!is_dir(MG_EMOTICON_PATH)) {
    @mkdir(MG_EMOTICON_PATH, 0755, true);
    @chmod(MG_EMOTICON_PATH, 0755);
}

$es_id = isset($_POST['es_id']) ? (int)$_POST['es_id'] : 0;
$es_name = isset($_POST['es_name']) ? trim($_POST['es_name']) : '';
$es_desc = isset($_POST['es_desc']) ? trim($_POST['es_desc']) : '';
$es_price = isset($_POST['es_price']) ? (int)$_POST['es_price'] : 0;
$es_order = isset($_POST['es_order']) ? (int)$_POST['es_order'] : 0;
$es_use = isset($_POST['es_use']) ? (int)$_POST['es_use'] : 1;

if (!$es_name) {
    alert('셋 이름을 입력해주세요.');
    exit;
}

// === 신규 등록 ===
if ($action === 'insert') {
    $data = array(
        'es_name' => $es_name,
        'es_desc' => $es_desc,
        'es_price' => $es_price,
        'es_preview' => '',
    );
    $es_id = mg_create_emoticon_set(null, $data);

    if (!$es_id) {
        alert('이모티콘 셋 등록에 실패했습니다.');
        exit;
    }

    // 추가 필드 업데이트
    sql_query("UPDATE {$g5['mg_emoticon_set_table']}
               SET es_order = {$es_order}, es_use = {$es_use}
               WHERE es_id = {$es_id}");
}
// === 수정 ===
elseif ($action === 'update') {
    if ($es_id <= 0) {
        alert('잘못된 접근입니다.');
        exit;
    }

    $es_name_esc = sql_real_escape_string($es_name);
    $es_desc_esc = sql_real_escape_string($es_desc);
    sql_query("UPDATE {$g5['mg_emoticon_set_table']}
               SET es_name = '{$es_name_esc}',
                   es_desc = '{$es_desc_esc}',
                   es_price = {$es_price},
                   es_order = {$es_order},
                   es_use = {$es_use}
               WHERE es_id = {$es_id}");
}
else {
    alert('잘못된 접근입니다.');
    exit;
}

// 셋 디렉토리
$set_dir = MG_EMOTICON_PATH . '/' . $es_id;
if (!is_dir($set_dir)) {
    @mkdir($set_dir, 0755, true);
    @chmod($set_dir, 0755);
}
$set_url = MG_EMOTICON_URL . '/' . $es_id;

// === 미리보기 이미지 처리 ===
if (isset($_FILES['es_preview_file']) && $_FILES['es_preview_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['es_preview_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (in_array($ext, $allowed) && $file['size'] <= 1024 * 1024) {
        // 기존 미리보기 삭제
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
        $cv = mg_validate_emoticon_code($code);
        if ($em_id > 0 && $cv['valid']) {
            $code_esc = sql_real_escape_string($cv['code']);
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
    $max_size = (int)mg_config('emoticon_image_max_size', 512) * 1024;

    // $_FILES 배열 정규화
    $file_count = is_array($files['name']) ? count($files['name']) : 0;
    $order = (int)sql_fetch("SELECT COALESCE(MAX(em_order),0)+1 as next_order FROM {$g5['mg_emoticon_table']} WHERE es_id = {$es_id}")['next_order'];

    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) continue;
        if ($files['size'][$i] > $max_size) continue;

        $new_name = $es_id . '_' . time() . '_' . $i . '.' . $ext;
        $target = $set_dir . '/' . $new_name;

        if (move_uploaded_file($files['tmp_name'][$i], $target)) {
            @chmod($target, 0644);
            $image_url = $set_url . '/' . $new_name;

            // 코드 결정 + 포맷 검증
            $raw_code = '';
            if (isset($new_codes[$i]) && trim($new_codes[$i])) {
                $raw_code = trim($new_codes[$i]);
            } else {
                $base = pathinfo($files['name'][$i], PATHINFO_FILENAME);
                $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base);
                $raw_code = ':' . strtolower($base) . ':';
            }

            $cv = mg_validate_emoticon_code($raw_code);
            $code = $cv['valid'] ? $cv['code'] : ':' . strtolower($base) . ':';

            // 코드 중복 확인 및 자동 수정 (접미사 증가하며 미사용 코드 탐색)
            if (mg_emoticon_code_exists($code)) {
                $suffix = $i;
                do {
                    $code = ':' . strtolower($base) . '_' . $suffix . ':';
                    $suffix++;
                } while (mg_emoticon_code_exists($code) && $suffix < 999);
            }

            mg_add_emoticon($es_id, $code, $image_url, $order);
            $order++;
        }
    }
}

goto_url('./emoticon_form.php?es_id=' . $es_id);
