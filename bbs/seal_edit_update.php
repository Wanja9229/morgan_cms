<?php
/**
 * Morgan Edition - 인장 저장 처리 (AJAX)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

if (!mg_config('seal_enable', 1)) {
    echo json_encode(array('success' => false, 'message' => '인장 시스템이 비활성화되어 있습니다.'));
    exit;
}

$mb_id = $member['mb_id'];
$mb_esc = sql_real_escape_string($mb_id);

// 입력값 처리
$seal_use = ($_POST['seal_use'] ?? '1') == '1' ? 1 : 0;

$tagline_max = (int)mg_config('seal_tagline_max', 50);
$content_max = (int)mg_config('seal_content_max', 300);

$seal_tagline = mg_sanitize_seal_text($_POST['seal_tagline'] ?? '', $tagline_max);
$seal_content = mg_sanitize_seal_text($_POST['seal_content'] ?? '', $content_max);

$seal_link = '';
$seal_link_text = '';
if (mg_config('seal_link_allow', 1)) {
    $seal_link = trim($_POST['seal_link'] ?? '');
    if ($seal_link && !preg_match('/^https?:\/\//', $seal_link)) {
        $seal_link = '';
    }
    $seal_link = mb_substr($seal_link, 0, 500);
    $seal_link_text = mg_sanitize_seal_text($_POST['seal_link_text'] ?? '', 50);
}

$seal_text_color = trim($_POST['seal_text_color'] ?? '');
if ($seal_text_color && !preg_match('/^#[0-9a-fA-F]{6}$/', $seal_text_color)) {
    $seal_text_color = '';
}

// 저장 (INSERT ... ON DUPLICATE KEY UPDATE)
$sql = "INSERT INTO {$g5['mg_seal_table']}
    (mb_id, seal_use, seal_tagline, seal_content, seal_link, seal_link_text, seal_text_color, seal_update)
    VALUES (
        '{$mb_esc}',
        {$seal_use},
        '".sql_real_escape_string($seal_tagline)."',
        '".sql_real_escape_string($seal_content)."',
        '".sql_real_escape_string($seal_link)."',
        '".sql_real_escape_string($seal_link_text)."',
        '".sql_real_escape_string($seal_text_color)."',
        NOW()
    )
    ON DUPLICATE KEY UPDATE
        seal_use = {$seal_use},
        seal_tagline = '".sql_real_escape_string($seal_tagline)."',
        seal_content = '".sql_real_escape_string($seal_content)."',
        seal_link = '".sql_real_escape_string($seal_link)."',
        seal_link_text = '".sql_real_escape_string($seal_link_text)."',
        seal_text_color = '".sql_real_escape_string($seal_text_color)."',
        seal_update = NOW()";

sql_query($sql);

// 대표 캐릭터 변경
$main_ch_id = isset($_POST['main_ch_id']) ? (int)$_POST['main_ch_id'] : 0;
if ($main_ch_id > 0) {
    // 본인 소유 & 승인된 캐릭터인지 확인
    $ch_check = sql_fetch("SELECT ch_id, ch_main FROM {$g5['mg_character_table']}
                           WHERE ch_id = {$main_ch_id} AND mb_id = '{$mb_esc}' AND ch_state = 'approved'");
    if ($ch_check['ch_id'] && !$ch_check['ch_main']) {
        // 기존 대표 해제 → 새 대표 설정
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_main = 0 WHERE mb_id = '{$mb_esc}'");
        sql_query("UPDATE {$g5['mg_character_table']} SET ch_main = 1 WHERE ch_id = {$main_ch_id}");
    }
}

// 칭호 변경
$title_si_id = isset($_POST['title_si_id']) ? (int)$_POST['title_si_id'] : -1;
if ($title_si_id >= 0) {
    if ($title_si_id === 0) {
        // 칭호 해제
        sql_query("DELETE FROM {$g5['mg_item_active_table']} WHERE mb_id = '{$mb_esc}' AND ia_type = 'title'");
    } else {
        // 보유 확인
        $own_check = sql_fetch("SELECT v.iv_id FROM {$g5['mg_inventory_table']} v
                                JOIN {$g5['mg_shop_item_table']} i ON v.si_id = i.si_id
                                WHERE v.mb_id = '{$mb_esc}' AND v.si_id = {$title_si_id} AND i.si_type = 'title'");
        if ($own_check['iv_id']) {
            // 기존 칭호 해제 → 새 칭호 적용
            sql_query("DELETE FROM {$g5['mg_item_active_table']} WHERE mb_id = '{$mb_esc}' AND ia_type = 'title'");
            sql_query("INSERT INTO {$g5['mg_item_active_table']} (mb_id, si_id, ia_type) VALUES ('{$mb_esc}', {$title_si_id}, 'title')");
        }
    }
}

echo json_encode(array('success' => true, 'message' => '인장이 저장되었습니다.'));
