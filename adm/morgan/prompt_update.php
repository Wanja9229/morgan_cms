<?php
/**
 * Morgan Edition - 미션 관리 처리
 */

$sub_menu = "801500";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    goto_url('./prompt.php');
}

$mode = isset($_POST['mode']) ? trim($_POST['mode']) : '';

// ==========================================
// 미션 추가
// ==========================================
if ($mode == 'add') {
    $bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
    $pm_title = isset($_POST['pm_title']) ? trim($_POST['pm_title']) : '';
    $pm_content = isset($_POST['pm_content']) ? trim($_POST['pm_content']) : '';
    $pm_cycle = isset($_POST['pm_cycle']) ? trim($_POST['pm_cycle']) : 'weekly';
    $pm_mode = isset($_POST['pm_mode']) ? trim($_POST['pm_mode']) : 'review';
    $pm_start_date = isset($_POST['pm_start_date']) ? trim($_POST['pm_start_date']) : '';
    $pm_end_date = isset($_POST['pm_end_date']) ? trim($_POST['pm_end_date']) : '';
    $pm_point = max(0, (int)($_POST['pm_point'] ?? 0));
    $pm_bonus_point = max(0, (int)($_POST['pm_bonus_point'] ?? 0));
    $pm_bonus_count = max(0, (int)($_POST['pm_bonus_count'] ?? 0));
    $pm_material_id = (int)($_POST['pm_material_id'] ?? 0);
    $pm_material_qty = max(0, (int)($_POST['pm_material_qty'] ?? 0));
    $pm_min_chars = max(0, (int)($_POST['pm_min_chars'] ?? 0));
    $pm_max_entry = max(1, (int)($_POST['pm_max_entry'] ?? 1));
    $pm_tags = isset($_POST['pm_tags']) ? trim($_POST['pm_tags']) : '';
    $pm_status = isset($_POST['pm_status']) ? trim($_POST['pm_status']) : 'draft';

    // 유효성 검사
    if (!$pm_title) {
        alert('미션 제목을 입력해주세요.', './prompt.php?mode=edit&pm_id=0');
    }
    if (!$bo_table) {
        alert('대상 게시판을 선택해주세요.', './prompt.php?mode=edit&pm_id=0');
    }

    // enum 유효성
    if (!in_array($pm_cycle, array('weekly', 'monthly', 'event'))) $pm_cycle = 'weekly';
    if (!in_array($pm_mode, array('auto', 'review', 'vote'))) $pm_mode = 'review';
    if (!in_array($pm_status, array('draft', 'active'))) $pm_status = 'draft';

    // 날짜 처리
    $start_sql = $pm_start_date ? "'".sql_real_escape_string($pm_start_date)."'" : 'NULL';
    $end_sql = $pm_end_date ? "'".sql_real_escape_string($pm_end_date)."'" : 'NULL';

    // 배너 이미지 업로드
    $pm_banner = '';
    if (isset($_FILES['pm_banner']) && $_FILES['pm_banner']['error'] == UPLOAD_ERR_OK) {
        $upload = mg_upload_prompt_banner($_FILES['pm_banner'], 0);
        if ($upload['success']) {
            $pm_banner = $upload['url'];
        } else {
            alert($upload['message'], './prompt.php?mode=edit&pm_id=0');
        }
    }

    $bo_esc = sql_real_escape_string($bo_table);
    $title_esc = sql_real_escape_string($pm_title);
    $content_esc = sql_real_escape_string($pm_content);
    $tags_esc = sql_real_escape_string($pm_tags);
    $banner_esc = sql_real_escape_string($pm_banner);
    $admin_id = sql_real_escape_string($member['mb_id']);
    $material_id_sql = $pm_material_id > 0 ? $pm_material_id : 'NULL';

    sql_query("INSERT INTO {$g5['mg_prompt_table']}
        (bo_table, pm_title, pm_content, pm_cycle, pm_mode, pm_start_date, pm_end_date,
         pm_point, pm_bonus_point, pm_bonus_count, pm_material_id, pm_material_qty,
         pm_min_chars, pm_max_entry, pm_banner, pm_tags, pm_status, pm_admin_id, pm_created)
        VALUES
        ('{$bo_esc}', '{$title_esc}', '{$content_esc}', '{$pm_cycle}', '{$pm_mode}',
         {$start_sql}, {$end_sql},
         {$pm_point}, {$pm_bonus_point}, {$pm_bonus_count}, {$material_id_sql}, {$pm_material_qty},
         {$pm_min_chars}, {$pm_max_entry}, '{$banner_esc}', '{$tags_esc}', '{$pm_status}', '{$admin_id}', NOW())");

    goto_url('./prompt.php');
}

// ==========================================
// 미션 수정
// ==========================================
if ($mode == 'edit') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    $existing = sql_fetch("SELECT * FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");
    if (!$existing || !$existing['pm_id']) {
        alert('미션을 찾을 수 없습니다.', './prompt.php');
    }

    $bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
    $pm_title = isset($_POST['pm_title']) ? trim($_POST['pm_title']) : '';
    $pm_content = isset($_POST['pm_content']) ? trim($_POST['pm_content']) : '';
    $pm_cycle = isset($_POST['pm_cycle']) ? trim($_POST['pm_cycle']) : 'weekly';
    $pm_mode = isset($_POST['pm_mode']) ? trim($_POST['pm_mode']) : 'review';
    $pm_start_date = isset($_POST['pm_start_date']) ? trim($_POST['pm_start_date']) : '';
    $pm_end_date = isset($_POST['pm_end_date']) ? trim($_POST['pm_end_date']) : '';
    $pm_point = max(0, (int)($_POST['pm_point'] ?? 0));
    $pm_bonus_point = max(0, (int)($_POST['pm_bonus_point'] ?? 0));
    $pm_bonus_count = max(0, (int)($_POST['pm_bonus_count'] ?? 0));
    $pm_material_id = (int)($_POST['pm_material_id'] ?? 0);
    $pm_material_qty = max(0, (int)($_POST['pm_material_qty'] ?? 0));
    $pm_min_chars = max(0, (int)($_POST['pm_min_chars'] ?? 0));
    $pm_max_entry = max(1, (int)($_POST['pm_max_entry'] ?? 1));
    $pm_tags = isset($_POST['pm_tags']) ? trim($_POST['pm_tags']) : '';
    $pm_status = isset($_POST['pm_status']) ? trim($_POST['pm_status']) : 'draft';

    // 유효성 검사
    if (!$pm_title) {
        alert('미션 제목을 입력해주세요.', './prompt.php?mode=edit&pm_id='.$pm_id);
    }
    if (!$bo_table) {
        alert('대상 게시판을 선택해주세요.', './prompt.php?mode=edit&pm_id='.$pm_id);
    }

    // enum 유효성
    if (!in_array($pm_cycle, array('weekly', 'monthly', 'event'))) $pm_cycle = 'weekly';
    if (!in_array($pm_mode, array('auto', 'review', 'vote'))) $pm_mode = 'review';
    if (!in_array($pm_status, array('draft', 'active'))) $pm_status = $existing['pm_status'];

    // 날짜 처리
    $start_sql = $pm_start_date ? "'".sql_real_escape_string($pm_start_date)."'" : 'NULL';
    $end_sql = $pm_end_date ? "'".sql_real_escape_string($pm_end_date)."'" : 'NULL';

    // 배너 이미지 처리
    $pm_banner = $existing['pm_banner'];

    // 기존 배너 삭제 요청
    if (isset($_POST['remove_banner']) && $_POST['remove_banner'] == '1') {
        if ($existing['pm_banner']) {
            $old_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $existing['pm_banner']);
            if (defined('MG_PROMPT_IMAGE_URL')) {
                $old_path = str_replace(MG_PROMPT_IMAGE_URL, (defined('MG_PROMPT_IMAGE_PATH') ? MG_PROMPT_IMAGE_PATH : ''), $existing['pm_banner']);
            }
            if ($old_path && file_exists($old_path)) {
                @unlink($old_path);
            }
        }
        $pm_banner = '';
    }

    // 새 배너 업로드
    if (isset($_FILES['pm_banner']) && $_FILES['pm_banner']['error'] == UPLOAD_ERR_OK) {
        // 기존 이미지 삭제
        if ($existing['pm_banner']) {
            $old_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $existing['pm_banner']);
            if (defined('MG_PROMPT_IMAGE_URL')) {
                $old_path = str_replace(MG_PROMPT_IMAGE_URL, (defined('MG_PROMPT_IMAGE_PATH') ? MG_PROMPT_IMAGE_PATH : ''), $existing['pm_banner']);
            }
            if ($old_path && file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $upload = mg_upload_prompt_banner($_FILES['pm_banner'], $pm_id);
        if ($upload['success']) {
            $pm_banner = $upload['url'];
        } else {
            alert($upload['message'], './prompt.php?mode=edit&pm_id='.$pm_id);
        }
    }

    $bo_esc = sql_real_escape_string($bo_table);
    $title_esc = sql_real_escape_string($pm_title);
    $content_esc = sql_real_escape_string($pm_content);
    $tags_esc = sql_real_escape_string($pm_tags);
    $banner_esc = sql_real_escape_string($pm_banner);
    $material_id_sql = $pm_material_id > 0 ? $pm_material_id : 'NULL';

    sql_query("UPDATE {$g5['mg_prompt_table']} SET
        bo_table = '{$bo_esc}',
        pm_title = '{$title_esc}',
        pm_content = '{$content_esc}',
        pm_cycle = '{$pm_cycle}',
        pm_mode = '{$pm_mode}',
        pm_start_date = {$start_sql},
        pm_end_date = {$end_sql},
        pm_point = {$pm_point},
        pm_bonus_point = {$pm_bonus_point},
        pm_bonus_count = {$pm_bonus_count},
        pm_material_id = {$material_id_sql},
        pm_material_qty = {$pm_material_qty},
        pm_min_chars = {$pm_min_chars},
        pm_max_entry = {$pm_max_entry},
        pm_banner = '{$banner_esc}',
        pm_tags = '{$tags_esc}',
        pm_status = '{$pm_status}'
        WHERE pm_id = {$pm_id}");

    goto_url('./prompt.php');
}

// ==========================================
// 미션 삭제
// ==========================================
if ($mode == 'delete') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    // 배너 이미지 삭제
    $pm = sql_fetch("SELECT pm_banner FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");
    if ($pm && $pm['pm_banner']) {
        $img_path = '';
        if (defined('MG_PROMPT_IMAGE_URL') && defined('MG_PROMPT_IMAGE_PATH')) {
            $img_path = str_replace(MG_PROMPT_IMAGE_URL, MG_PROMPT_IMAGE_PATH, $pm['pm_banner']);
        } else {
            $img_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $pm['pm_banner']);
        }
        if ($img_path && file_exists($img_path)) {
            @unlink($img_path);
        }
    }

    // 관련 엔트리 삭제
    sql_query("DELETE FROM {$g5['mg_prompt_entry_table']} WHERE pm_id = {$pm_id}");

    // 미션 삭제
    sql_query("DELETE FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");

    goto_url('./prompt.php');
}

// ==========================================
// 미션 종료
// ==========================================
if ($mode == 'close') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    mg_prompt_close($pm_id);
    goto_url('./prompt.php');
}

// ==========================================
// 일괄 승인
// ==========================================
if ($mode == 'approve') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    $pe_ids = isset($_POST['pe_id']) ? $_POST['pe_id'] : array();

    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }
    if (!is_array($pe_ids) || empty($pe_ids)) {
        alert('항목을 선택해주세요.', './prompt.php?mode=review&pm_id='.$pm_id);
    }

    $count = 0;
    foreach ($pe_ids as $pe_id) {
        $pe_id = (int)$pe_id;
        if ($pe_id > 0) {
            $result = mg_prompt_approve($pe_id, $member['mb_id']);
            if ($result) $count++;
        }
    }

    goto_url('./prompt.php?mode=review&pm_id='.$pm_id);
}

// ==========================================
// 선택 선정작 지정
// ==========================================
if ($mode == 'bonus') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    $pe_ids = isset($_POST['pe_id']) ? $_POST['pe_id'] : array();

    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }
    if (!is_array($pe_ids) || empty($pe_ids)) {
        alert('항목을 선택해주세요.', './prompt.php?mode=review&pm_id='.$pm_id);
    }

    foreach ($pe_ids as $pe_id) {
        $pe_id = (int)$pe_id;
        if ($pe_id > 0) {
            sql_query("UPDATE {$g5['mg_prompt_entry_table']} SET pe_is_bonus = 1 WHERE pe_id = {$pe_id}");
        }
    }

    goto_url('./prompt.php?mode=review&pm_id='.$pm_id);
}

// ==========================================
// 보상 일괄 지급 (승인된 엔트리 대상)
// ==========================================
if ($mode == 'reward_all') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    $prompt = mg_get_prompt($pm_id);
    if (!$prompt || !$prompt['pm_id']) {
        alert('미션을 찾을 수 없습니다.', './prompt.php');
    }

    // 승인 상태의 엔트리만 보상 대상
    $sql = "SELECT pe_id, pe_is_bonus FROM {$g5['mg_prompt_entry_table']}
        WHERE pm_id = {$pm_id} AND pe_status = 'approved'";
    $result = sql_query($sql);
    $count = 0;
    while ($row = sql_fetch_array($result)) {
        $is_bonus = (int)$row['pe_is_bonus'] ? true : false;
        $ret = mg_prompt_give_reward($pm_id, (int)$row['pe_id'], $is_bonus);
        if ($ret) $count++;
    }

    goto_url('./prompt.php?mode=review&pm_id='.$pm_id);
}

// ==========================================
// 개별 반려 (POST fallback, AJAX는 prompt.php에서 처리)
// ==========================================
if ($mode == 'reject') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    $pe_id = (int)($_POST['pe_id'] ?? 0);
    $memo = isset($_POST['memo']) ? trim($_POST['memo']) : '';

    if (!$pe_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    mg_prompt_reject($pe_id, $member['mb_id'], $memo);

    if ($pm_id) {
        goto_url('./prompt.php?mode=review&pm_id='.$pm_id);
    }
    goto_url('./prompt.php');
}

// ==========================================
// 투표 정산 (vote 모드)
// ==========================================
if ($mode == 'vote_settle') {
    $pm_id = (int)($_POST['pm_id'] ?? 0);
    if (!$pm_id) {
        alert('잘못된 요청입니다.', './prompt.php');
    }

    $prompt = mg_get_prompt($pm_id);
    if (!$prompt || $prompt['pm_mode'] !== 'vote') {
        alert('투표 모드 미션만 정산 가능합니다.', './prompt.php');
    }

    $count = mg_prompt_vote_settle($pm_id);
    goto_url('./prompt.php?mode=review&pm_id='.$pm_id);
}

// 알 수 없는 mode
goto_url('./prompt.php');
