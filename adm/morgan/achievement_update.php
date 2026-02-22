<?php
/**
 * Morgan Edition - 업적 관리 처리
 */

$sub_menu = "801200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// ======================================
// 업적 저장 (추가/수정)
// ======================================
if ($mode == 'save') {
    header('Content-Type: application/json');

    $ac_id = (int)($_POST['ac_id'] ?? 0);
    $ac_name = isset($_POST['ac_name']) ? trim($_POST['ac_name']) : '';
    $ac_desc = isset($_POST['ac_desc']) ? trim($_POST['ac_desc']) : '';
    $ac_icon = isset($_POST['ac_icon']) ? trim($_POST['ac_icon']) : '';

    // 아이콘 파일 업로드 처리
    $uploaded = mg_handle_icon_upload('ac_icon_file', 'achievement', 'ac');
    if ($uploaded !== null) {
        $ac_icon = $uploaded;
    } elseif (isset($_POST['del_ac_icon']) && $_POST['del_ac_icon'] == '1') {
        $ac_icon = '';
    }

    $ac_category = isset($_POST['ac_category']) ? trim($_POST['ac_category']) : 'activity';
    $ac_type = isset($_POST['ac_type']) ? trim($_POST['ac_type']) : 'progressive';
    $ac_condition = isset($_POST['ac_condition']) ? trim($_POST['ac_condition']) : '{}';
    $ac_reward = isset($_POST['ac_reward']) ? trim($_POST['ac_reward']) : '{}';
    $ac_rarity = isset($_POST['ac_rarity']) ? trim($_POST['ac_rarity']) : 'common';
    $ac_order = max(0, (int)($_POST['ac_order'] ?? 0));
    $ac_use = ($_POST['ac_use'] ?? '1') == '1' ? 1 : 0;
    $ac_hidden = ($_POST['ac_hidden'] ?? '0') == '1' ? 1 : 0;

    if (!$ac_name) {
        echo json_encode(array('success' => false, 'message' => '업적 이름을 입력해주세요.'));
        exit;
    }

    if (!in_array($ac_type, array('progressive', 'onetime'))) $ac_type = 'progressive';

    $valid_rarities = array('common', 'uncommon', 'rare', 'epic', 'legendary');
    if (!in_array($ac_rarity, $valid_rarities)) $ac_rarity = 'common';

    // JSON 유효성 체크
    if (json_decode($ac_condition) === null) $ac_condition = '{}';
    if (json_decode($ac_reward) === null) $ac_reward = '{}';

    $name_esc = sql_real_escape_string($ac_name);
    $desc_esc = sql_real_escape_string($ac_desc);
    $icon_esc = sql_real_escape_string($ac_icon);
    $cat_esc = sql_real_escape_string($ac_category);
    $cond_esc = sql_real_escape_string($ac_condition);
    $reward_esc = sql_real_escape_string($ac_reward);

    if ($ac_id > 0) {
        // 수정
        $exists = sql_fetch("SELECT ac_id FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");
        if (!$exists['ac_id']) {
            echo json_encode(array('success' => false, 'message' => '업적을 찾을 수 없습니다.'));
            exit;
        }

        sql_query("UPDATE {$g5['mg_achievement_table']} SET
            ac_name = '{$name_esc}',
            ac_desc = '{$desc_esc}',
            ac_icon = '{$icon_esc}',
            ac_category = '{$cat_esc}',
            ac_type = '{$ac_type}',
            ac_condition = '{$cond_esc}',
            ac_reward = '{$reward_esc}',
            ac_rarity = '{$ac_rarity}',
            ac_order = {$ac_order},
            ac_use = {$ac_use},
            ac_hidden = {$ac_hidden}
            WHERE ac_id = {$ac_id}");

        echo json_encode(array('success' => true, 'message' => '수정되었습니다.', 'ac_id' => $ac_id));
    } else {
        // 추가
        sql_query("INSERT INTO {$g5['mg_achievement_table']}
            (ac_name, ac_desc, ac_icon, ac_category, ac_type, ac_condition, ac_reward, ac_rarity, ac_order, ac_use, ac_hidden)
            VALUES
            ('{$name_esc}', '{$desc_esc}', '{$icon_esc}', '{$cat_esc}', '{$ac_type}', '{$cond_esc}', '{$reward_esc}', '{$ac_rarity}', {$ac_order}, {$ac_use}, {$ac_hidden})");

        $new_id = sql_insert_id();
        echo json_encode(array('success' => true, 'message' => '추가되었습니다.', 'ac_id' => $new_id));
    }
    exit;
}

// ======================================
// 업적 삭제
// ======================================
if ($mode == 'delete') {
    header('Content-Type: application/json');

    $ac_id = (int)($_POST['ac_id'] ?? 0);
    if (!$ac_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
        exit;
    }

    // 단계 삭제
    sql_query("DELETE FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = {$ac_id}");
    // 유저 달성 기록 삭제
    sql_query("DELETE FROM {$g5['mg_user_achievement_table']} WHERE ac_id = {$ac_id}");
    // 쇼케이스에서 제거 (slot 값이 이 업적인 경우 NULL로)
    for ($i = 1; $i <= 5; $i++) {
        sql_query("UPDATE {$g5['mg_user_achievement_display_table']} SET slot_{$i} = NULL WHERE slot_{$i} = {$ac_id}");
    }
    // 업적 삭제
    sql_query("DELETE FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");

    echo json_encode(array('success' => true, 'message' => '삭제되었습니다.'));
    exit;
}

// ======================================
// 업적 토글 (활성/비활성)
// ======================================
if ($mode == 'toggle') {
    header('Content-Type: application/json');

    $ac_id = (int)($_POST['ac_id'] ?? 0);
    $ac_use = ($_POST['ac_use'] ?? '1') == '1' ? 1 : 0;

    if (!$ac_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
        exit;
    }

    sql_query("UPDATE {$g5['mg_achievement_table']} SET ac_use = {$ac_use} WHERE ac_id = {$ac_id}");
    echo json_encode(array('success' => true, 'message' => ($ac_use ? '활성화' : '비활성화') . '되었습니다.'));
    exit;
}

// ======================================
// 단계 저장 (추가/수정)
// ======================================
if ($mode == 'save_tier') {
    header('Content-Type: application/json');

    $ac_id = (int)($_POST['ac_id'] ?? 0);
    $at_id = (int)($_POST['at_id'] ?? 0);
    $at_level = max(1, (int)($_POST['at_level'] ?? 1));
    $at_name = isset($_POST['at_name']) ? trim($_POST['at_name']) : '';
    $at_target = max(1, (int)($_POST['at_target'] ?? 1));
    $at_icon = isset($_POST['at_icon']) ? trim($_POST['at_icon']) : '';

    // 단계 아이콘 파일 업로드 처리
    $uploaded = mg_handle_icon_upload('at_icon_file', 'achievement', 'at');
    if ($uploaded !== null) {
        $at_icon = $uploaded;
    }

    $at_reward = isset($_POST['at_reward']) ? trim($_POST['at_reward']) : '{}';

    if (!$ac_id) {
        echo json_encode(array('success' => false, 'message' => '업적을 찾을 수 없습니다.'));
        exit;
    }
    if (!$at_name) {
        echo json_encode(array('success' => false, 'message' => '단계명을 입력해주세요.'));
        exit;
    }

    if (json_decode($at_reward) === null) $at_reward = '{}';

    $name_esc = sql_real_escape_string($at_name);
    $icon_esc = sql_real_escape_string($at_icon);
    $reward_esc = sql_real_escape_string($at_reward);

    if ($at_id > 0) {
        // 수정
        sql_query("UPDATE {$g5['mg_achievement_tier_table']} SET
            at_level = {$at_level},
            at_name = '{$name_esc}',
            at_target = {$at_target},
            at_icon = '{$icon_esc}',
            at_reward = '{$reward_esc}'
            WHERE at_id = {$at_id} AND ac_id = {$ac_id}");

        echo json_encode(array('success' => true, 'message' => '수정되었습니다.'));
    } else {
        // 추가
        sql_query("INSERT INTO {$g5['mg_achievement_tier_table']}
            (ac_id, at_level, at_name, at_target, at_icon, at_reward)
            VALUES ({$ac_id}, {$at_level}, '{$name_esc}', {$at_target}, '{$icon_esc}', '{$reward_esc}')");

        echo json_encode(array('success' => true, 'message' => '추가되었습니다.'));
    }
    exit;
}

// ======================================
// 단계 삭제
// ======================================
if ($mode == 'delete_tier') {
    header('Content-Type: application/json');

    $at_id = (int)($_POST['at_id'] ?? 0);
    $ac_id = (int)($_POST['ac_id'] ?? 0);

    if (!$at_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
        exit;
    }

    sql_query("DELETE FROM {$g5['mg_achievement_tier_table']} WHERE at_id = {$at_id}");
    echo json_encode(array('success' => true, 'message' => '삭제되었습니다.'));
    exit;
}

// ======================================
// 수동 부여
// ======================================
if ($mode == 'grant') {
    header('Content-Type: application/json');

    $ac_id = (int)($_POST['ac_id'] ?? 0);
    $mb_ids = isset($_POST['mb_ids']) ? $_POST['mb_ids'] : array();
    $memo = isset($_POST['memo']) ? trim($_POST['memo']) : '';
    $give_reward = ($_POST['give_reward'] ?? '0') == '1';

    if (!$ac_id) {
        echo json_encode(array('success' => false, 'message' => '업적을 선택해주세요.'));
        exit;
    }
    if (!is_array($mb_ids) || empty($mb_ids)) {
        echo json_encode(array('success' => false, 'message' => '대상 회원을 선택해주세요.'));
        exit;
    }

    $result = mg_grant_achievement($mb_ids, $ac_id, $member['mb_id'], $memo, $give_reward);
    echo json_encode($result);
    exit;
}

// ======================================
// 업적 회수
// ======================================
if ($mode == 'revoke') {
    header('Content-Type: application/json');

    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
    $ac_id = (int)($_POST['ac_id'] ?? 0);

    if (!$mb_id || !$ac_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
        exit;
    }

    $result = mg_revoke_achievement($mb_id, $ac_id, $member['mb_id']);
    echo json_encode($result);
    exit;
}

// 알 수 없는 mode
goto_url('./achievement.php');
