<?php
/**
 * Morgan Edition - 히든 이벤트 관리 처리
 */
$sub_menu = '801410';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'w');
include_once(G5_PATH.'/plugin/morgan/morgan.php');
check_admin_token();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$redirect = './hidden_event.php';

switch ($action) {
    // ─── 이벤트 추가 ───
    case 'add_event':
        $title = trim($_POST['title'] ?? '');
        if (!$title) alert('제목을 입력하세요.');

        $reward_type = in_array($_POST['reward_type'] ?? '', array('point','material')) ? $_POST['reward_type'] : 'point';
        $reward_id = ($reward_type === 'material') ? (int)($_POST['reward_id'] ?? 0) : 0;
        $reward_amount = max(1, (int)($_POST['reward_amount'] ?? 100));
        $probability = max(0.01, min(100, (float)($_POST['probability'] ?? 5)));
        $daily_limit = max(1, (int)($_POST['daily_limit'] ?? 1));
        $daily_total = max(1, (int)($_POST['daily_total'] ?? 5));
        $active_from = trim($_POST['active_from'] ?? '') ?: null;
        $active_until = trim($_POST['active_until'] ?? '') ?: null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // 이미지 업로드
        $image_path = mg_handle_icon_upload('event_image', 'event', 'he');
        if (!$image_path) alert('이벤트 이미지를 업로드하세요.');

        $title_esc = sql_real_escape_string($title);
        $image_esc = sql_real_escape_string($image_path);
        $from_sql = $active_from ? "'" . sql_real_escape_string($active_from) . "'" : 'NULL';
        $until_sql = $active_until ? "'" . sql_real_escape_string($active_until) . "'" : 'NULL';

        sql_query("INSERT INTO {$g5['mg_hidden_event_table']}
            (title, image_path, reward_type, reward_id, reward_amount, probability, daily_limit, daily_total, active_from, active_until, is_active)
            VALUES ('{$title_esc}', '{$image_esc}', '{$reward_type}', {$reward_id}, {$reward_amount}, {$probability}, {$daily_limit}, {$daily_total}, {$from_sql}, {$until_sql}, {$is_active})");

        $redirect = './hidden_event.php?tab=list';
        break;

    // ─── 이벤트 수정 ───
    case 'edit_event':
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id <= 0) alert('잘못된 요청입니다.');

        $old = sql_fetch("SELECT * FROM {$g5['mg_hidden_event_table']} WHERE event_id = {$event_id}");
        if (!$old) alert('이벤트를 찾을 수 없습니다.');

        $title = trim($_POST['title'] ?? '');
        if (!$title) alert('제목을 입력하세요.');

        $reward_type = in_array($_POST['reward_type'] ?? '', array('point','material')) ? $_POST['reward_type'] : 'point';
        $reward_id = ($reward_type === 'material') ? (int)($_POST['reward_id'] ?? 0) : 0;
        $reward_amount = max(1, (int)($_POST['reward_amount'] ?? 100));
        $probability = max(0.01, min(100, (float)($_POST['probability'] ?? 5)));
        $daily_limit = max(1, (int)($_POST['daily_limit'] ?? 1));
        $daily_total = max(1, (int)($_POST['daily_total'] ?? 5));
        $active_from = trim($_POST['active_from'] ?? '') ?: null;
        $active_until = trim($_POST['active_until'] ?? '') ?: null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // 이미지: 새 업로드 있으면 교체, 없으면 유지
        $image_path = mg_handle_icon_upload('event_image', 'event', 'he');
        if ($image_path) {
            // 기존 이미지 삭제
            if ($old['image_path']) {
                $old_file = G5_PATH . $old['image_path'];
                if (file_exists($old_file)) @unlink($old_file);
            }
        } else {
            $image_path = $old['image_path'];
        }

        $title_esc = sql_real_escape_string($title);
        $image_esc = sql_real_escape_string($image_path);
        $from_sql = $active_from ? "'" . sql_real_escape_string($active_from) . "'" : 'NULL';
        $until_sql = $active_until ? "'" . sql_real_escape_string($active_until) . "'" : 'NULL';

        sql_query("UPDATE {$g5['mg_hidden_event_table']} SET
            title = '{$title_esc}',
            image_path = '{$image_esc}',
            reward_type = '{$reward_type}',
            reward_id = {$reward_id},
            reward_amount = {$reward_amount},
            probability = {$probability},
            daily_limit = {$daily_limit},
            daily_total = {$daily_total},
            active_from = {$from_sql},
            active_until = {$until_sql},
            is_active = {$is_active}
            WHERE event_id = {$event_id}");

        $redirect = './hidden_event.php?tab=list';
        break;

    // ─── 삭제 ───
    case 'delete_event':
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id > 0) {
            $ev = sql_fetch("SELECT image_path FROM {$g5['mg_hidden_event_table']} WHERE event_id = {$event_id}");
            if ($ev && $ev['image_path']) {
                $f = G5_PATH . $ev['image_path'];
                if (file_exists($f)) @unlink($f);
            }
            sql_query("DELETE FROM {$g5['mg_hidden_event_table']} WHERE event_id = {$event_id}");
        }
        $redirect = './hidden_event.php?tab=list';
        break;

    // ─── 활성 토글 ───
    case 'toggle_event':
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id > 0) {
            sql_query("UPDATE {$g5['mg_hidden_event_table']} SET is_active = 1 - is_active WHERE event_id = {$event_id}");
        }
        $redirect = './hidden_event.php?tab=list';
        break;

    default:
        alert('잘못된 요청입니다.');
}

goto_url($redirect);
