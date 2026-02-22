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

// 레이아웃 JSON 검증
$seal_layout = '';
$raw_layout = trim($_POST['seal_layout'] ?? '');
// GnuBoard가 $_POST에 addslashes 적용 → JSON 복원을 위해 stripslashes
$raw_layout = stripslashes($raw_layout);
if ($raw_layout) {
    $parsed = json_decode($raw_layout, true);
    if (is_array($parsed) && isset($parsed['elements']) && is_array($parsed['elements'])) {
        $valid_types = array('character','nickname','tagline','text','image','link','trophy','sticker');
        $clean = array();
        foreach ($parsed['elements'] as $el) {
            if (!isset($el['type']) || !in_array($el['type'], $valid_types)) continue;
            $item = array(
                'type' => $el['type'],
                'x' => max(0, min(15, (int)($el['x'] ?? 0))),
                'y' => max(0, min(5, (int)($el['y'] ?? 0))),
                'w' => max(1, min(16, (int)($el['w'] ?? 1))),
                'h' => max(1, min(6, (int)($el['h'] ?? 1))),
            );
            if ($el['type'] === 'trophy' && isset($el['slot'])) {
                $item['slot'] = max(1, min(10, (int)$el['slot']));
            }
            if ($el['type'] === 'sticker' && isset($el['item_id'])) {
                $item['item_id'] = (int)$el['item_id'];
            }
            // 요소별 스타일 (Phase B)
            if (isset($el['style']) && is_array($el['style'])) {
                $allowed_style_keys = array('color', 'bgColor', 'borderColor');
                $style = array();
                foreach ($allowed_style_keys as $sk) {
                    if (!empty($el['style'][$sk]) && preg_match('/^#[0-9a-fA-F]{6}$/', $el['style'][$sk])) {
                        $style[$sk] = $el['style'][$sk];
                    }
                }
                // 텍스트 정렬
                if (!empty($el['style']['align']) && in_array($el['style']['align'], array('left', 'center', 'right'))) {
                    if ($el['style']['align'] !== 'center') {
                        $style['align'] = $el['style']['align'];
                    }
                }
                if ($style) $item['style'] = $style;
            }
            $clean[] = $item;
        }
        $seal_layout = json_encode(array('elements' => $clean), JSON_UNESCAPED_UNICODE);
    }
}

// 저장 (INSERT ... ON DUPLICATE KEY UPDATE)
$sql = "INSERT INTO {$g5['mg_seal_table']}
    (mb_id, seal_use, seal_tagline, seal_content, seal_link, seal_link_text, seal_text_color, seal_layout, seal_update)
    VALUES (
        '{$mb_esc}',
        {$seal_use},
        '".sql_real_escape_string($seal_tagline)."',
        '".sql_real_escape_string($seal_content)."',
        '".sql_real_escape_string($seal_link)."',
        '".sql_real_escape_string($seal_link_text)."',
        '".sql_real_escape_string($seal_text_color)."',
        '".sql_real_escape_string($seal_layout)."',
        NOW()
    )
    ON DUPLICATE KEY UPDATE
        seal_use = {$seal_use},
        seal_tagline = '".sql_real_escape_string($seal_tagline)."',
        seal_content = '".sql_real_escape_string($seal_content)."',
        seal_link = '".sql_real_escape_string($seal_link)."',
        seal_link_text = '".sql_real_escape_string($seal_link_text)."',
        seal_text_color = '".sql_real_escape_string($seal_text_color)."',
        seal_layout = '".sql_real_escape_string($seal_layout)."',
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

echo json_encode(array('success' => true, 'message' => '인장이 저장되었습니다.'));
